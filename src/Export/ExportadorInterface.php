<?php

declare(strict_types=1);

namespace App\Export;

use App\Report\ConteudoRelatorio;

interface ExportadorInterface
{
    public function exportar(ConteudoRelatorio $conteudo): string;

    public function extensao(): string;

    public function mimeType(): string;
}
