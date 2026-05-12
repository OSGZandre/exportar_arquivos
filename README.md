# Projeto Symfony - Design Patterns

## Sobre

Projeto simples desenvolvido para estudo de Design Patterns, com foco em Template Method e Strategy.

Mini Exportador de Relatórios: gera relatórios corporativos com fluxo fixo e permite trocar o formato de exportação (HTML, CSV, TXT) sem alterar a lógica principal.

## Implementação

* Template Method: define o fluxo padrão da geração do relatório (`buscarDados` → `montarConteudo` → `exportar`) em uma classe abstrata, permitindo que subclasses alterem apenas partes específicas do processo.
* Strategy: troca dinamicamente o formato de exportação (HTML, CSV, TXT). Cada formato possui sua própria estratégia, injetada por composição no relatório.

## Estrutura

* `src/Report/RelatorioAbstrato.php` — classe abstrata com o Template Method (`gerar()` final).
* `src/Report/RelatorioVendas.php` e `src/Report/RelatorioFuncionarios.php` — relatórios concretos.
* `src/Export/ExportadorInterface.php` — contrato da Strategy.
* `src/Export/ExportadorHtml.php`, `ExportadorCsv.php`, `ExportadorTxt.php` — implementações de exportação.
* `src/Export/ExportadorRegistry.php` — map de formato → strategy (evita `if`/`switch` por formato).
* `src/Controller/RelatorioController.php` — formulário e download.

## Tecnologias

* PHP
* Symfony
* Composer

## Execução
* É necessário a instalação do PHP, Symfony, e composer. 
```bash
symfony check:requirements
```
```bash
composer update
```
```bash
symfony serve
```

Acesse:

```
http://127.0.0.1:8000/relatorio
```

## Objetivo

Aplicar de forma prática e simples os conceitos dos padrões Template Method e Strategy.
