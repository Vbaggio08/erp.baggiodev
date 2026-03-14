<?php

namespace Src\Models;

use TCPDF;

/**
 * GeradorRelatorioPDF - FASE 5
 * 
 * Gera relatórios em PDF:
 * - Relatório mensal de ponto por usuário
 * - Relatório de horas extras (RH)
 * - Relatório de DSR (RH)
 * - Recibos de ponto
 * 
 * Usa TCPDF para compatibilidade e performance
 */
class GeradorRelatorioPDF
{
    private $pdf = null;
    private $empresa_nome = 'Ripfire ERP';
    private $empresa_cnpj = '';

    public function __construct()
    {
        // Carregar TCPDF se não disponível
        if (!class_exists('TCPDF')) {
            // Assumir que está instalado via composer
            // ou incluir manualmente se necessário
        }

        $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_PAGE_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Configurações básicas
        $this->pdf->SetCreator('Ripfire ERP');
        $this->pdf->SetAuthor('Ripfire ERP');
        $this->pdf->SetAutoPageBreak(true, 10);
        $this->pdf->SetFont('helvetica', '', 10);
    }

    /**
     * Gerar relatório mensal de ponto do usuário
     */
    public function gerarRelatorioPonto(
        string $usuario_nome,
        string $mes_ano,
        array $dados_ponto,
        array $apontamentos = []
    ): string {
        $this->pdf->AddPage();
        
        // Header
        $this->adicionarHeader('Relatório de Ponto Eletrônico');
        
        // Informações básicas
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 10, 'Período: ' . $mes_ano, 0, 1);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 10, 'Funcionário: ' . $usuario_nome, 0, 1);
        $this->pdf->Cell(0, 10, 'Data de Emissão: ' . date('d/m/Y H:i'), 0, 1);
        $this->pdf->Ln(5);

        // Sumário
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetFillColor(102, 126, 234);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Cell(0, 8, 'RESUMO DO PERÍODO', 0, 1, 'C', true);
        $this->pdf->SetTextColor(0, 0, 0);

        // Tabela de resumo
        $this->pdf->SetFont('helvetica', '', 9);
        $resumo = [
            'Dias Trabalhados' => $dados_ponto['dias_trabalhados'] ?? 0,
            'Dias Úteis' => $dados_ponto['dias_uteis'] ?? 0,
            'Faltas' => $dados_ponto['faltas'] ?? 0,
            'Atestados' => $dados_ponto['atestados'] ?? 0,
            'Horas Trabalhadas' => number_format($dados_ponto['horas_trabalhadas'] ?? 0, 2, ',', '.'),
            'Horas Esperadas' => number_format($dados_ponto['horas_esperadas'] ?? 0, 2, ',', '.'),
            'Horas Extras Aprovadas' => number_format($dados_ponto['horas_extras_aprovadas'] ?? 0, 2, ',', '.'),
            'Saldo Final' => number_format($dados_ponto['saldo_final'] ?? 0, 2, ',', '.')
        ];

        $col_width = 85;
        foreach ($resumo as $label => $valor) {
            $this->pdf->Cell($col_width, 7, $label . ':', 1, 0, 'L');
            $this->pdf->Cell($col_width, 7, (string)$valor, 1, 1, 'R');
        }

        $this->pdf->Ln(5);

        // Tabela de apontamentos
        if (!empty($apontamentos)) {
            $this->pdf->SetFont('helvetica', 'B', 11);
            $this->pdf->SetFillColor(102, 126, 234);
            $this->pdf->SetTextColor(255, 255, 255);
            $this->pdf->Cell(0, 8, 'APONTAMENTOS DETALHADOS', 0, 1, 'C', true);

            $this->pdf->SetTextColor(0, 0, 0);
            $this->pdf->SetFont('helvetica', 'B', 8);
            $this->adicionarLinhaTabela(
                ['Data', 'Entrada 1', 'Saída 1', 'Entrada 2', 'Saída 2', 'Total', 'Status'],
                [25, 20, 20, 20, 20, 20, 35]
            );

            $this->pdf->SetFont('helvetica', '', 8);
            foreach ($apontamentos as $apt) {
                $this->adicionarLinhaTabela(
                    [
                        date('d/m/Y', strtotime($apt['data'] ?? '')),
                        $apt['hora_entrada_1'] ?? '-',
                        $apt['hora_saida_1'] ?? '-',
                        $apt['hora_entrada_2'] ?? '-',
                        $apt['hora_saida_2'] ?? '-',
                        number_format($apt['total_horas'] ?? 0, 2, ',', '.'),
                        $apt['status'] ?? 'Normal'
                    ],
                    [25, 20, 20, 20, 20, 20, 35]
                );
            }
        }

        $this->pdf->Ln(10);
        $this->adicionarFooter();

        return $this->exportar("relatorio_ponto_$mes_ano.pdf");
    }

    /**
     * Gerar relatório de horas extras (RH)
     */
    public function gerarRelatorioHorasExtras(
        string $mes_ano,
        array $dados_consolidados
    ): string {
        $this->pdf->AddPage('L'); // Landscape
        
        $this->adicionarHeader('Relatório de Horas Extras');
        
        $this->pdf->SetFont('helvetica', 'B', 12);
        $this->pdf->Cell(0, 10, 'Período: ' . $mes_ano, 0, 1);
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 10, 'Data de Emissão: ' . date('d/m/Y H:i'), 0, 1);
        $this->pdf->Ln(5);

        // Tabela principal
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(102, 126, 234);
        $this->pdf->SetTextColor(255, 255, 255);
        
        $this->adicionarLinhaTabela(
            ['Usuário', 'Email', 'Pendente', 'Aprovado', 'Rejeitado', 'Pago', 'Total', 'Valor (Est.)'],
            [40, 50, 20, 20, 20, 20, 20, 30]
        );

        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('helvetica', '', 9);

        $total_horas = 0;
        foreach ($dados_consolidados as $linha) {
            $total_horas += $linha['total_horas'];
            
            $valor_est = $linha['total_horas'] * 50; // Assumir valor/hora base
            
            $this->adicionarLinhaTabela(
                [
                    $linha['nome'],
                    $linha['email'],
                    number_format($linha['pendente'], 1, ',', '.'),
                    number_format($linha['aprovado'], 1, ',', '.'),
                    number_format($linha['rejeitado'], 1, ',', '.'),
                    number_format($linha['pago'], 1, ',', '.'),
                    number_format($linha['total_horas'], 1, ',', '.'),
                    'R$ ' . number_format($valor_est, 2, ',', '.')
                ],
                [40, 50, 20, 20, 20, 20, 20, 30]
            );
        }

        // Total
        $this->pdf->SetFont('helvetica', 'B', 9);
        $this->pdf->SetFillColor(200, 200, 200);
        $this->adicionarLinhaTabela(
            ['TOTAL', '', '', '', '', '', number_format($total_horas, 1, ',', '.'), ''],
            [40, 50, 20, 20, 20, 20, 20, 30],
            true
        );

        $this->pdf->Ln(10);
        $this->adicionarFooter();

        return $this->exportar("relatorio_horas_extras_$mes_ano.pdf");
    }

    /**
     * Gerar recibo de ponto
     */
    public function gerarReciboPonto(
        string $usuario_nome,
        string $data_ponto,
        array $dados_batida
    ): string {
        $this->pdf->AddPage();
        
        // Logo e cabeçalho
        $this->pdf->SetFont('helvetica', 'B', 16);
        $this->pdf->Cell(0, 15, 'RECIBO DE PONTO', 0, 1, 'C');
        
        $this->pdf->SetFont('helvetica', '', 10);
        $this->pdf->Cell(0, 10, $this->empresa_nome, 0, 1, 'C');
        
        if ($this->empresa_cnpj) {
            $this->pdf->Cell(0, 5, 'CNPJ: ' . $this->empresa_cnpj, 0, 1, 'C');
        }

        $this->pdf->Ln(5);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->Line(10, $this->pdf->GetY(), 200, $this->pdf->GetY());
        $this->pdf->Ln(5);

        // Informações
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(50, 8, 'Funcionário:', 0, 0);
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(0, 8, $usuario_nome, 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(50, 8, 'Data:', 0, 0);
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(0, 8, date('d/m/Y', strtotime($data_ponto)), 0, 1);

        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->Cell(50, 8, 'Emissão:', 0, 0);
        $this->pdf->SetFont('helvetica', '', 11);
        $this->pdf->Cell(0, 8, date('d/m/Y H:i:s'), 0, 1);

        $this->pdf->Ln(5);

        // Tabela de batidas
        $this->pdf->SetFont('helvetica', 'B', 11);
        $this->pdf->SetFillColor(102, 126, 234);
        $this->pdf->SetTextColor(255, 255, 255);
        $this->pdf->Cell(0, 10, 'BATIDAS DO DIA', 0, 1, 'C', true);

        $this->pdf->SetTextColor(0, 0, 0);
        $this->pdf->SetFont('helvetica', 'B', 10);
        $this->adicionarLinhaTabela(
            ['Tipo', 'Hora', 'Localização', 'Comprovada'],
            [40, 40, 90, 20]
        );

        $this->pdf->SetFont('helvetica', '', 10);
        foreach ($dados_batida as $batida) {
            $geo = $batida['latitude'] && $batida['longitude'] 
                ? "{$batida['latitude']}, {$batida['longitude']}"
                : 'Não registrada';
            
            $this->adicionarLinhaTabela(
                [
                    ucfirst($batida['tipo']),
                    date('H:i:s', strtotime($batida['timestamp'])),
                    $geo,
                    $batida['conferida'] ? '✓' : ''
                ],
                [40, 40, 90, 20]
            );
        }

        $this->pdf->Ln(10);
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->MultiCell(0, 5, 
            'Este é um recibo automático gerado pelo sistema Ripfire ERP. ' .
            'Para mais informações ou contestações, entre em contato com o departamento de RH.'
        );

        return $this->exportar("recibo_ponto_" . date('Ymd_His') . ".pdf");
    }

    /**
     * Adicionar header aos documentos
     */
    private function adicionarHeader(string $titulo)
    {
        $this->pdf->SetFont('helvetica', 'B', 14);
        $this->pdf->Cell(0, 12, $titulo, 0, 1, 'C');
        $this->pdf->SetFont('helvetica', '', 9);
        $this->pdf->Cell(0, 6, $this->empresa_nome, 0, 1, 'C');
        $this->pdf->Ln(3);
    }

    /**
     * Adicionar footer aos documentos
     */
    private function adicionarFooter()
    {
        $this->pdf->SetFont('helvetica', '', 8);
        $this->pdf->SetTextColor(150, 150, 150);
        $this->pdf->Cell(0, 5, 
            'Documento gerado em ' . date('d/m/Y H:i') . ' | Página ' . $this->pdf->getPage(),
            0, 1, 'C'
        );
    }

    /**
     * Adicionar linha em tabela
     */
    private function adicionarLinhaTabela(
        array $colunas,
        array $larguras,
        bool $destacar = false
    ) {
        if ($destacar) {
            $this->pdf->SetFillColor(200, 200, 200);
        }

        foreach ($colunas as $i => $coluna) {
            $this->pdf->Cell($larguras[$i], 7, (string)$coluna, 1, 0, 'C', $destacar);
        }
        
        $this->pdf->Ln();
        
        if ($destacar) {
            $this->pdf->SetFillColor(255, 255, 255);
        }
    }

    /**
     * Exportar PDF e retornar caminho ou conteúdo
     */
    private function exportar(string $nome_arquivo): string
    {
        $caminho = __DIR__ . '/../../assets/uploads/relatorios/' . $nome_arquivo;
        
        // Criar diretório se não existir
        $dir = dirname($caminho);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Salvar arquivo
        $this->pdf->Output($caminho, 'F');

        return $caminho;
    }

    /**
     * Obter conteúdo PDF para download direto
     */
    public function download(string $nome_arquivo): string
    {
        return $this->pdf->Output($nome_arquivo, 'S');
    }

    /**
     * Obter instância do TCPDF para customizações
     */
    public function getPDF(): TCPDF
    {
        return $this->pdf;
    }

    /**
     * Definir dados da empresa
     */
    public function setEmpresa(string $nome, string $cnpj = '')
    {
        $this->empresa_nome = $nome;
        $this->empresa_cnpj = $cnpj;
    }
}
