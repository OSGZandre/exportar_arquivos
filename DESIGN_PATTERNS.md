# Mini Exportador de Relatórios — Explicação do sistema

## O que o sistema faz

É um **mini exportador de relatórios corporativos**. O usuário acessa uma página, escolhe **qual relatório** quer (Vendas ou Funcionários) e **em qual formato** quer baixar (HTML, CSV ou TXT). O sistema gera o arquivo e devolve como download.

### Fluxo do usuário

1. `GET /relatorio` — abre um formulário com dois `<select>`: tipo de relatório e formato.
2. Usuário escolhe, por exemplo, "Vendas" + "CSV", e clica em "Gerar e baixar".
3. `POST /relatorio/exportar` — o controller monta o relatório, escolhe a estratégia de exportação correspondente, gera o arquivo e devolve com `Content-Disposition: attachment` (o navegador baixa).

### Fluxo interno (toda vez que um relatório é gerado)

Sempre passa pelos **3 mesmos passos**, nesta ordem:

1. **Buscar dados** — hoje é mock fixo dentro de cada classe; num projeto real seria um repositório/banco.
2. **Montar conteúdo** — transforma os dados crus num DTO padronizado: título, cabeçalhos de coluna, linhas, data de geração.
3. **Exportar** — pega esse DTO e produz uma string no formato escolhido (HTML, CSV ou TXT), com extensão de arquivo e mime type corretos.

A peça-chave do sistema é justamente que esses **3 passos sempre acontecem na mesma ordem** (passo 3 separado dos outros porque o formato muda), e essa separação é o que permite aplicar os dois padrões.

---

## Como o Template Method foi aplicado

O **Template Method** serve para fixar o **algoritmo** (a sequência de passos) numa superclasse, deixando as subclasses preencherem apenas o que muda entre cada variação. Aqui ele garante que **todo relatório, qualquer que seja, sempre executa buscar → montar → exportar nessa ordem**.

A "casca" está em `src/Report/RelatorioAbstrato.php`:

```php
final public function gerar(): ArquivoExportado
{
    $dados    = $this->buscarDados();
    $conteudo = $this->montarConteudo($dados);
    $binario  = $this->exportador->exportar($conteudo);

    return new ArquivoExportado(
        conteudo:    $binario,
        nomeArquivo: $this->nomeBase() . '.' . $this->exportador->extensao(),
        mimeType:    $this->exportador->mimeType(),
    );
}

abstract protected function buscarDados(): array;
abstract protected function montarConteudo(array $dados): ConteudoRelatorio;
abstract protected function nomeBase(): string;
```

Dois detalhes importantes:

- **`gerar()` é `final`** — nenhuma subclasse consegue sobrescrever o método. Isso **trava o algoritmo**. Um `RelatorioVendas` nunca vai conseguir reordenar os passos ou pular um.
- **Os "buracos" são `abstract protected`** — `buscarDados()`, `montarConteudo()`, `nomeBase()`. Cada subclasse é **obrigada** a implementar, e só pode mexer nessas partes.

As subclasses concretas preenchem só os buracos. Por exemplo, `RelatorioVendas`:

```php
final class RelatorioVendas extends RelatorioAbstrato
{
    protected function buscarDados(): array
    {
        return [
            ['produto' => 'Notebook Dell Inspiron', 'quantidade' => 12, 'valor_unitario' => 4599.90],
            ['produto' => 'Mouse Logitech MX',      'quantidade' => 45, 'valor_unitario' => 389.00],
            // ...
        ];
    }

    protected function montarConteudo(array $dados): ConteudoRelatorio
    {
        // converte dados crus em ConteudoRelatorio
    }

    protected function nomeBase(): string
    {
        return 'relatorio_vendas_' . date('Ymd_His');
    }
}
```

E `RelatorioFuncionarios` faz o mesmo, mas com dados, cabeçalhos e nome diferentes. **Nenhuma delas sabe como chamar `gerar()`** — esse fluxo é só da classe-mãe. As subclasses só dizem **"isto são meus dados, isto é como eu monto, este é meu nome base"**.

**Onde está a herança** `RelatorioVendas extends RelatorioAbstrato` e `RelatorioFuncionarios extends RelatorioAbstrato`. O fluxo é herdado; só os hooks são especializados.

---

## Como o Strategy foi aplicado

Enquanto o Template Method resolve "qual relatório", o **Strategy** resolve "em qual formato exportar". Cada formato é uma **classe independente** que implementa a mesma interface, e o relatório recebe uma delas por **composição** (não por herança).

O contrato está em `src/Export/ExportadorInterface.php`:

```php
interface ExportadorInterface
{
    public function exportar(ConteudoRelatorio $conteudo): string;

    public function extensao(): string;

    public function mimeType(): string;
}
```

Três strategies independentes implementam esse contrato:

- `ExportadorCsv` — usa `fputcsv` num stream em memória; retorna `'csv'` / `'text/csv; charset=UTF-8'`.
- `ExportadorTxt` — monta tabela ASCII com `str_pad`; retorna `'txt'` / `'text/plain; charset=UTF-8'`.
- `ExportadorHtml` — delega ao Twig (`templates/relatorio/export.html.twig`); retorna `'html'` / `'text/html; charset=UTF-8'`.

**Onde está a composição (requisito do enunciado):** o `RelatorioAbstrato` **recebe** a strategy no construtor, em vez de criar ela ou herdar dela:

```php
public function __construct(
    private readonly ExportadorInterface $exportador,
) {
}
```

E a usa via `$this->exportador->exportar(...)`. O relatório **não sabe** se é CSV ou HTML — só conhece a interface. Por isso a mesma classe `RelatorioVendas` consegue ser exportada em qualquer formato sem alteração de código.

### Eliminando `if pdf / if csv / if html`

Esse foi um requisito explícito. A escolha do formato é resolvida com um **mapa**, não com condicional. Veja `src/Export/ExportadorRegistry.php`:

```php
public function __construct(
    ExportadorHtml $html,
    ExportadorCsv $csv,
    ExportadorTxt $txt,
) {
    $this->map = [
        'html' => $html,
        'csv'  => $csv,
        'txt'  => $txt,
    ];
}

public function obter(string $formato): ExportadorInterface
{
    $chave = strtolower(trim($formato));

    if (!isset($this->map[$chave])) {
        throw new \InvalidArgumentException(/* ... */);
    }

    return $this->map[$chave];
}
```

O `if` que existe ali é só para **validar entrada inválida** (formato inexistente), não para decidir comportamento por formato. A decisão é um **lookup em array por chave**: `$this->map[$chave]`. Adicionar um formato novo (ex: JSON) seria **adicionar uma classe e uma linha no map** — zero alteração no controller, nos relatórios ou em qualquer outro lugar do código.

---

## Como tudo se conecta no controller

`src/Controller/RelatorioController.php` é só o "cabeamento". Ele:

1. Pega o nome do formato vindo do form e pergunta ao registry: "me dá a strategy desse formato".
2. Pega o tipo de relatório (mapa de tipo → classe, também sem `if`/`switch` por tipo).
3. Instancia o relatório passando a strategy.
4. Chama `gerar()` (Template Method) e devolve o `ArquivoExportado` como `Response`.

```php
public function exportar(Request $request): Response
{
    $tipo    = (string) $request->request->get('tipo', '');
    $formato = (string) $request->request->get('formato', '');

    if (!isset(self::RELATORIOS[$tipo])) {
        throw $this->createNotFoundException(/* ... */);
    }

    $exportador = $this->registry->obter($formato);
    $classe     = self::RELATORIOS[$tipo]['class'];

    /** @var RelatorioAbstrato $relatorio */
    $relatorio = new $classe($exportador);
    $arquivo   = $relatorio->gerar();

    return new Response(
        $arquivo->conteudo,
        Response::HTTP_OK,
        [
            'Content-Type'        => $arquivo->mimeType,
            'Content-Disposition' => sprintf('attachment; filename="%s"', $arquivo->nomeArquivo),
        ],
    );
}
```

---

## Resumo conceitual

| Pergunta                                                                       | Padrão                        | Onde está                                            |
| ------------------------------------------------------------------------------ | ----------------------------- | ---------------------------------------------------- |
| "Qual a sequência fixa de passos para gerar qualquer relatório?"               | **Template Method** (herança) | `RelatorioAbstrato::gerar()` final + hooks abstratos |
| "Como variar o conteúdo entre tipos de relatório?"                             | Hooks do Template Method      | `RelatorioVendas`, `RelatorioFuncionarios`           |
| "Como trocar o formato de saída em tempo de execução, sem mexer no relatório?" | **Strategy** (composição)     | `ExportadorInterface` + 3 implementações             |
| "Como escolher a strategy correta sem `if`/`switch` por formato?"              | Map (registry)                | `ExportadorRegistry`                                 |

**Por que os dois padrões juntos:** eles atacam **eixos de variação diferentes** sem se atrapalhar.

- O Template Method varia **no eixo "tipo de relatório"** (vendas vs funcionários) → herança.
- O Strategy varia **no eixo "formato de saída"** (html vs csv vs txt) → composição.

Adicionar um relatório novo não toca nos exporters. Adicionar um formato novo não toca nos relatórios. **2 relatórios × 3 formatos = 6 combinações funcionando, com apenas 5 classes "variantes" criadas** (em vez de 6 classes monolíticas que misturariam tudo).
