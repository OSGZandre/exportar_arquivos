<?php

declare(strict_types=1);

namespace App\Report;

final class RelatorioFuncionarios extends RelatorioAbstrato
{
    protected function buscarDados(): array
    {
        return [
            ['nome' => 'Danilo', 'cargo' => 'Gerente', 'salario' => 15000.00],
            ['nome' => 'Léo', 'cargo' => 'Analista de Sistemas',     'salario' => 10000.00],
            ['nome' => 'Bernardo', 'cargo' => 'Dev Senior', 'salario' => 8000.00],
            ['nome' => 'Adilio', 'cargo' => 'Dev Pleno',   'salario' => 5000.00],
            ['nome' => 'André', 'cargo' => 'Dev Junior',   'salario' => 2500.00],
        ];
    }

    protected function montarConteudo(array $dados): ConteudoRelatorio
    {
        $linhas = [];
        foreach ($dados as $item) {
            $linhas[] = [
                (string) $item['nome'],
                (string) $item['cargo'],
                number_format((float) $item['salario'], 2, ',', '.'),
            ];
        }

        return new ConteudoRelatorio(
            titulo:     'Relatório de Funcionários',
            cabecalhos: ['Nome', 'Cargo', 'Salário (R$)'],
            linhas:     $linhas,
            geradoEm:   new \DateTimeImmutable(),
        );
    }

    protected function nomeBase(): string
    {
        return 'relatorio_funcionarios_' . date('Ymd_His');
    }
}
