<?php

declare(strict_types=1);

namespace App\Report;

final class RelatorioVendas extends RelatorioAbstrato
{
    protected function buscarDados(): array
    {
        return [
            ['produto' => 'Notebook Dell Inspiron', 'quantidade' => 12, 'valor_unitario' => 4599.90],
            ['produto' => 'Mouse Logitech MX',      'quantidade' => 45, 'valor_unitario' => 389.00],
            ['produto' => 'Teclado Mecânico K70',   'quantidade' => 18, 'valor_unitario' => 899.50],
            ['produto' => 'Monitor LG 27"',         'quantidade' => 7,  'valor_unitario' => 1899.00],
            ['produto' => 'Webcam Logitech C920',   'quantidade' => 23, 'valor_unitario' => 549.90],
        ];
    }

    protected function montarConteudo(array $dados): ConteudoRelatorio
    {
        $linhas = [];
        foreach ($dados as $item) {
            $total    = (float) $item['quantidade'] * (float) $item['valor_unitario'];
            $linhas[] = [
                (string) $item['produto'],
                (string) $item['quantidade'],
                number_format((float) $item['valor_unitario'], 2, ',', '.'),
                number_format($total, 2, ',', '.'),
            ];
        }

        return new ConteudoRelatorio(
            titulo:     'Relatório de Vendas',
            cabecalhos: ['Produto', 'Quantidade', 'Valor Unitário (R$)', 'Total (R$)'],
            linhas:     $linhas,
            geradoEm:   new \DateTimeImmutable(),
        );
    }

    protected function nomeBase(): string
    {
        return 'relatorio_vendas_' . date('Ymd_His');
    }
}
