<?php

declare(strict_types=1);

namespace App\Report;

final class RelatorioFuncionarios extends RelatorioAbstrato
{
    protected function buscarDados(): array
    {
        return [
            ['nome' => 'Ana Souza',        'cargo' => 'Desenvolvedora Sênior', 'salario' => 12500.00],
            ['nome' => 'Bruno Lima',       'cargo' => 'Analista de Dados',     'salario' => 8900.00],
            ['nome' => 'Carla Mendes',     'cargo' => 'Tech Lead',             'salario' => 16800.00],
            ['nome' => 'Diego Ferreira',   'cargo' => 'Designer UX',           'salario' => 7800.00],
            ['nome' => 'Eduarda Ribeiro',  'cargo' => 'Gerente de Projetos',   'salario' => 14200.00],
            ['nome' => 'Felipe Castro',    'cargo' => 'Desenvolvedor Pleno',   'salario' => 9500.00],
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
