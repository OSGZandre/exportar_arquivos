<?php

declare(strict_types=1);

namespace App\Controller;

use App\Export\ExportadorRegistry;
use App\Report\RelatorioAbstrato;
use App\Report\RelatorioFuncionarios;
use App\Report\RelatorioVendas;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RelatorioController extends AbstractController
{
    /**
     * @var array<string, array{label: string, class: class-string<RelatorioAbstrato>}>
     */
    private const RELATORIOS = [
        'vendas' => [
            'label' => 'Relatório de Vendas',
            'class' => RelatorioVendas::class,
        ],
        'funcionarios' => [
            'label' => 'Relatório de Funcionários',
            'class' => RelatorioFuncionarios::class,
        ],
    ];

    public function __construct(
        private readonly ExportadorRegistry $registry,
    ) {
    }

    #[Route('/relatorio', name: 'relatorio_form', methods: ['GET'])]
    public function form(): Response
    {
        $relatorios = [];
        foreach (self::RELATORIOS as $chave => $config) {
            $relatorios[$chave] = $config['label'];
        }

        return $this->render('relatorio/form.html.twig', [
            'relatorios' => $relatorios,
            'formatos'   => $this->registry->formatosDisponiveis(),
        ]);
    }

    #[Route('/relatorio/exportar', name: 'relatorio_exportar', methods: ['POST'])]
    public function exportar(Request $request): Response
    {
        $tipo    = (string) $request->request->get('tipo', '');
        $formato = (string) $request->request->get('formato', '');

        if (!isset(self::RELATORIOS[$tipo])) {
            throw $this->createNotFoundException(sprintf('Tipo de relatório "%s" não suportado.', $tipo));
        }

        $exportador = $this->registry->obter($formato);
        $classe     = self::RELATORIOS[$tipo]['class'];

        /** @var RelatorioAbstrato $relatorio */
        $relatorio = new $classe($exportador);
        $arquivo   = $relatorio->gerar();

        return new Response(
            $arquivo->conteudo,
            Response::HTTP_OK,
            [
                'Content-Type'        => $arquivo->mimeType,
                'Content-Disposition' => sprintf('attachment; filename="%s"', $arquivo->nomeArquivo),
            ],
        );
    }
}
