<?php

declare(strict_types=1);

namespace App\Export;

use App\Report\ConteudoRelatorio;
use Twig\Environment;

final class ExportadorHtml implements ExportadorInterface
{
    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function exportar(ConteudoRelatorio $conteudo): string
    {
        return $this->twig->render('relatorio/export.html.twig', [
            'titulo'     => $conteudo->titulo,
            'cabecalhos' => $conteudo->cabecalhos,
            'linhas'     => $conteudo->linhas,
            'geradoEm'   => $conteudo->geradoEm->format('d/m/Y H:i:s'),
        ]);
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
