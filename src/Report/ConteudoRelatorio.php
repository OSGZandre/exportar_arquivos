<?php

declare(strict_types=1);

namespace App\Report;

final class ConteudoRelatorio
{
    /**
     * @param list<string>       $cabecalhos
     * @param list<list<string>> $linhas
     */
    public function __construct(
        public readonly string $titulo,
        public readonly array $cabecalhos,
        public readonly array $linhas,
        public readonly \DateTimeImmutable $geradoEm,
    ) {
    }
}
