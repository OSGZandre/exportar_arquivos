<?php

declare(strict_types=1);

namespace App\Report;

final class ArquivoExportado
{
    public function __construct(
        public readonly string $conteudo,
        public readonly string $nomeArquivo,
        public readonly string $mimeType,
    ) {
    }
}
