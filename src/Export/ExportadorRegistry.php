<?php

declare(strict_types=1);

namespace App\Export;

final class ExportadorRegistry
{
    /**
     * @var array<string, ExportadorInterface>
     */
    private array $map;

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
            throw new \InvalidArgumentException(sprintf(
                'Formato de exportação "%s" não suportado. Disponíveis: %s.',
                $formato,
                implode(', ', $this->formatosDisponiveis()),
            ));
        }
        dd($this->map[$chave]);
        return $this->map[$chave];
    }

    /**
     * @return list<string>
     */
    public function formatosDisponiveis(): array
    {
        return array_keys($this->map);
    }
}
