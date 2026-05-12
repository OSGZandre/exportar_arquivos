<?php

declare(strict_types=1);

namespace App\Report;

use App\Export\ExportadorInterface;

abstract class RelatorioAbstrato
{
    public function __construct(
        private readonly ExportadorInterface $exportador,
    ) {
    }

    final public function gerar(): ArquivoExportado
    {
        $dados = $this->buscarDados();
        $conteudo = $this->montarConteudo($dados);
        $binario = $this->exportador->exportar($conteudo);

        return new ArquivoExportado(
            conteudo:    $binario,
            nomeArquivo: $this->nomeBase() . '.' . $this->exportador->extensao(),
            mimeType:    $this->exportador->mimeType(),
        );
    }

    /**
     * @return list<array<string, mixed>>
     */
    abstract protected function buscarDados(): array;

    /**
     * @param list<array<string, mixed>> $dados
     */
    abstract protected function montarConteudo(array $dados): ConteudoRelatorio;

    abstract protected function nomeBase(): string;
}
