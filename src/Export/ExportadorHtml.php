<?php

declare(strict_types=1);

namespace App\Export;

use App\Report\ConteudoRelatorio;

final class ExportadorHtml implements ExportadorInterface
{
    public function exportar(ConteudoRelatorio $conteudo): string
    {
        $titulo   = htmlspecialchars($conteudo->titulo, ENT_QUOTES, 'UTF-8');
        $geradoEm = $conteudo->geradoEm->format('d/m/Y H:i:s');

        $thead = '';
        foreach ($conteudo->cabecalhos as $coluna) {
            $thead .= '<th>' . htmlspecialchars($coluna, ENT_QUOTES, 'UTF-8') . '</th>';
        }

        $tbody = '';
        foreach ($conteudo->linhas as $linha) {
            $tbody .= '<tr>';
            foreach ($linha as $celula) {
                $tbody .= '<td>' . htmlspecialchars($celula, ENT_QUOTES, 'UTF-8') . '</td>';
            }
            $tbody .= '</tr>';
        }

        return <<<HTML
            <!DOCTYPE html>
            <html lang="pt-BR">
            <head>
                <meta charset="UTF-8">
                <title>{$titulo}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 24px; color: #222; }
                    h1 { margin-bottom: 4px; }
                    p.meta { color: #666; margin-top: 0; }
                    table { border-collapse: collapse; width: 100%; margin-top: 16px; }
                    th, td { border: 1px solid #ccc; padding: 8px 12px; text-align: left; }
                    th { background: #f4f4f4; }
                    tr:nth-child(even) td { background: #fafafa; }
                </style>
            </head>
            <body>
                <h1>{$titulo}</h1>
                <p class="meta">Gerado em {$geradoEm}</p>
                <table>
                    <thead><tr>{$thead}</tr></thead>
                    <tbody>{$tbody}</tbody>
                </table>
            </body>
            </html>
            HTML;
    }

    public function extensao(): string
    {
        return 'html';
    }

    public function mimeType(): string
    {
        return 'text/html; charset=UTF-8';
    }
}
