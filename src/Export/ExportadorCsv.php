<?php

declare(strict_types=1);

namespace App\Export;

use App\Report\ConteudoRelatorio;

final class ExportadorCsv implements ExportadorInterface
{
    public function exportar(ConteudoRelatorio $conteudo): string
    {
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('Não foi possível abrir stream temporário para CSV.');
        }

        fputcsv($stream, [$conteudo->titulo]);
        fputcsv($stream, ['Gerado em', $conteudo->geradoEm->format('d/m/Y H:i:s')]);
        fputcsv($stream, []);
        fputcsv($stream, $conteudo->cabecalhos);

        foreach ($conteudo->linhas as $linha) {
            fputcsv($stream, $linha);
        }

        rewind($stream);
        $csv = stream_get_contents($stream);
        fclose($stream);

        return $csv === false ? '' : $csv;
    }

    public function extensao(): string
    {
        return 'csv';
    }

    public function mimeType(): string
    {
        return 'text/csv; charset=UTF-8';
    }
}
