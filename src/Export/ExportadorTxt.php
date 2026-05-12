<?php

declare(strict_types=1);

namespace App\Export;

use App\Report\ConteudoRelatorio;

final class ExportadorTxt implements ExportadorInterface
{
    public function exportar(ConteudoRelatorio $conteudo): string
    {
        $larguras = $this->calcularLarguras($conteudo->cabecalhos, $conteudo->linhas);
        $separador = $this->montarSeparador($larguras);

        $linhas   = [];
        $linhas[] = $conteudo->titulo;
        $linhas[] = 'Gerado em: ' . $conteudo->geradoEm->format('d/m/Y H:i:s');
        $linhas[] = '';
        $linhas[] = $separador;
        $linhas[] = $this->montarLinha($conteudo->cabecalhos, $larguras);
        $linhas[] = $separador;

        foreach ($conteudo->linhas as $linha) {
            $linhas[] = $this->montarLinha($linha, $larguras);
        }
        $linhas[] = $separador;

        return implode("\n", $linhas) . "\n";
    }

    public function extensao(): string
    {
        return 'txt';
    }

    public function mimeType(): string
    {
        return 'text/plain; charset=UTF-8';
    }

    /**
     * @param list<string>       $cabecalhos
     * @param list<list<string>> $linhas
     *
     * @return list<int>
     */
    private function calcularLarguras(array $cabecalhos, array $linhas): array
    {
        $larguras = [];
        foreach ($cabecalhos as $i => $coluna) {
            $larguras[$i] = mb_strlen($coluna);
        }
        foreach ($linhas as $linha) {
            foreach ($linha as $i => $celula) {
                $tamanho = mb_strlen($celula);
                if ($tamanho > ($larguras[$i] ?? 0)) {
                    $larguras[$i] = $tamanho;
                }
            }
        }

        return array_values($larguras);
    }

    /**
     * @param list<int> $larguras
     */
    private function montarSeparador(array $larguras): string
    {
        $partes = array_map(static fn (int $l): string => str_repeat('-', $l + 2), $larguras);

        return '+' . implode('+', $partes) . '+';
    }

    /**
     * @param list<string> $valores
     * @param list<int>    $larguras
     */
    private function montarLinha(array $valores, array $larguras): string
    {
        $partes = [];
        foreach ($valores as $i => $valor) {
            $largura  = $larguras[$i] ?? mb_strlen($valor);
            $padding  = $largura - mb_strlen($valor);
            $partes[] = ' ' . $valor . str_repeat(' ', max(0, $padding)) . ' ';
        }

        return '|' . implode('|', $partes) . '|';
    }
}
