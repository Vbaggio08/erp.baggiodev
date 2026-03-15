<?php

namespace Src\Models;

/**
 * GeradorRelatorioPDF/CSV - FASE 5
 * 
 * Gera relatórios em múltiplos formatos:
 * - Relatório mensal de ponto por usuário (HTML/CSV)
 * - Alternativa leve sem dependências externas
 * - HTML pode ser impresso como PDF do navegador (Ctrl+P)
 */
class GeradorRelatorioPDF
{
    private $empresa_nome = 'Ripfire ERP';
    private $empresa_cnpj = '';
    private $formato = 'html';

    public function __construct($formato = 'html')
    {
        $this->formato = $formato;
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
        if ($this->formato === 'csv') {
            return $this->gerarCSVPonto($usuario_nome, $mes_ano, $dados_ponto, $apontamentos);
        }
        return $this->gerarHTMLPonto($usuario_nome, $mes_ano, $dados_ponto, $apontamentos);
    }

    /**
     * Gerar relatório em HTML (pode ser impresso como PDF via navegador)
     */
    private function gerarHTMLPonto(
        string $usuario_nome,
        string $mes_ano,
        array $dados_ponto,
        array $apontamentos = []
    ): string {
        $data_emissao = date('d/m/Y H:i');
        $mes_texto = $this->formatarMes($mes_ano);

        $html = <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Ponto - {$mes_ano}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; color: #333; background: #f5f5f5; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; background: white; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #007bff; padding-bottom: 20px; }
        .header h1 { color: #1e90ff; font-size: 28px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-item { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; border-radius: 4px; }
        .info-item label { font-weight: bold; color: #495057; font-size: 12px; text-transform: uppercase; }
        .info-item value { font-size: 18px; color: #1e90ff; font-weight: bold; display: block; margin-top: 5px; }
        .section { margin-bottom: 30px; }
        .section-title { background: #007bff; color: white; padding: 12px 15px; font-size: 14px; font-weight: bold; text-transform: uppercase; border-radius: 4px 4px 0 0; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background: #f8f9fa; padding: 12px; text-align: left; font-weight: bold; border: 1px solid #dee2e6; font-size: 12px; color: #495057; }
        td { padding: 10px 12px; border: 1px solid #dee2e6; font-size: 13px; }
        tr:nth-child(even) { background: #f8f9fa; }
        tr:hover { background: #e9ecef; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #dee2e6; font-size: 12px; color: #666; }
        .print-info { text-align: center; color: #666; font-size: 12px; margin-bottom: 20px; }
        @media print { body { background: white; padding: 0; } .container { box-shadow: none; } .print-info { display: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="print-info">💡 Para salvar como PDF: Use Ctrl+P (ou Cmd+P no Mac)</div>
        <div class="header">
            <h1>📊 Relatório de Ponto Eletrônico</h1>
            <p>{$this->empresa_nome}</p>
        </div>
        <div class="info-grid">
            <div class="info-item"><label>Funcionário</label><value>{$usuario_nome}</value></div>
            <div class="info-item"><label>Período</label><value>{$mes_texto}</value></div>
            <div class="info-item"><label>Data de Emissão</label><value>{$data_emissao}</value></div>
            <div class="info-item"><label>Status</label><value style="color: #28a745;">✓ Processado</value></div>
        </div>
        <div class="section">
            <div class="section-title">Resumo do Período</div>
            <table>
                <tr>
                    <th>Dias Trabalhados</th><th>Dias Úteis</th><th>Faltas</th><th>Atestados</th>
                    <th>Horas Trabalhadas</th><th>Horas Esperadas</th><th>Saldo Final</th>
                </tr>
                <tr>
                    <td class="text-center">{$dados_ponto['dias_trabalhados'] ?? 0}</td>
                    <td class="text-center">{$dados_ponto['dias_uteis'] ?? 0}</td>
                    <td class="text-center">{$dados_ponto['faltas'] ?? 0}</td>
                    <td class="text-center">{$dados_ponto['atestados'] ?? 0}</td>
                    <td class="text-right">{$this->formatarHoras($dados_ponto['horas_trabalhadas'] ?? 0)}</td>
                    <td class="text-right">{$this->formatarHoras($dados_ponto['horas_esperadas'] ?? 0)}</td>
                    <td class="text-right" style="font-weight:bold;background:#e7f3ff;">{$this->formatarHoras($dados_ponto['saldo_final'] ?? 0)}</td>
                </tr>
            </table>
        </div>
HTML;

        if (!empty($apontamentos)) {
            $html .= '<div class="section"><div class="section-title">Apontamentos Detalhados</div><table><thead><tr>';
            $html .= '<th>Data</th><th class="text-center">Entrada 1</th><th class="text-center">Saída 1</th>';
            $html .= '<th class="text-center">Entrada 2</th><th class="text-center">Saída 2</th><th class="text-right">Total</th>';
            $html .= '<th class="text-center">Status</th></tr></thead><tbody>';

            foreach ($apontamentos as $apt) {
                $data = date('d/m/Y', strtotime($apt['data'] ?? $apt['data_apontamento'] ?? ''));
                $total = $this->formatarHoras($apt['total_horas'] ?? 0);
                $status = $apt['status'] ?? 'Normal';
                $html .= "<tr><td><strong>$data</strong></td>";
                $html .= "<td class=\"text-center\">{$apt['hora_entrada_1'] ?? '-'}</td>";
                $html .= "<td class=\"text-center\">{$apt['hora_saida_1'] ?? '-'}</td>";
                $html .= "<td class=\"text-center\">{$apt['hora_entrada_2'] ?? '-'}</td>";
                $html .= "<td class=\"text-center\">{$apt['hora_saida_2'] ?? '-'}</td>";
                $html .= "<td class=\"text-right\"><strong>$total</strong></td>";
                $html .= "<td class=\"text-center\"><span style=\"background:#e7f3ff;padding:3px 8px;border-radius:3px;font-size:11px;\">$status</span></td></tr>";
            }
            $html .= '</tbody></table></div>';
        }

        $html .= '<div class="footer"><p>Documento gerado automaticamente - ' . date('d/m/Y H:i') . '</p></div></div></body></html>';

        return $this->salvarArquivo($html, "relatorio_ponto_$mes_ano", 'html');
    }

    /**
     * Gerar relatório em CSV
     */
    private function gerarCSVPonto(
        string $usuario_nome,
        string $mes_ano,
        array $dados_ponto,
        array $apontamentos = []
    ): string {
        $csv = "Relatório de Ponto Eletrônico\n";
        $csv .= "{$this->empresa_nome}\n\n";
        $csv .= "Funcionário,{$usuario_nome}\n";
        $csv .= "Período,{$mes_ano}\n";
        $csv .= "Data de Emissão," . date('d/m/Y H:i') . "\n\n";
        $csv .= "RESUMO DO PERÍODO\n";
        $csv .= "Dias Trabalhados,{$dados_ponto['dias_trabalhados']}\n";
        $csv .= "Dias Úteis,{$dados_ponto['dias_uteis']}\n";
        $csv .= "Faltas,{$dados_ponto['faltas']}\n";
        $csv .= "Atestados,{$dados_ponto['atestados']}\n";
        $csv .= "Horas Trabalhadas,{$dados_ponto['horas_trabalhadas']}\n";
        $csv .= "Horas Esperadas,{$dados_ponto['horas_esperadas']}\n";
        $csv .= "Saldo Final,{$dados_ponto['saldo_final']}\n\n";

        if (!empty($apontamentos)) {
            $csv .= "APONTAMENTOS DETALHADOS\n";
            $csv .= "Data,Entrada 1,Saída 1,Entrada 2,Saída 2,Total Horas,Status\n";
            foreach ($apontamentos as $apt) {
                $data = date('d/m/Y', strtotime($apt['data'] ?? $apt['data_apontamento'] ?? ''));
                $csv .= "\"{$data}\",\"{$apt['hora_entrada_1'] ?? '-'}\",\"{$apt['hora_saida_1'] ?? '-'}\",";
                $csv .= "\"{$apt['hora_entrada_2'] ?? '-'}\",\"{$apt['hora_saida_2'] ?? '-'}\",";
                $csv .= "{$apt['total_horas']},\"{$apt['status'] ?? 'Normal'}\"\n";
            }
        }

        return $this->salvarArquivo($csv, "relatorio_ponto_$mes_ano", 'csv');
    }

    /**
     * Formatar horas
     */
    private function formatarHoras($horas): string {
        $h = floor($horas);
        $m = round(($horas - $h) * 60);
        return sprintf('%02dh %02dm', $h, $m);
    }

    /**
     * Formatar mês
     */
    private function formatarMes($mes_ano): string {
        $meses = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho',
                  'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];
        $parts = explode('-', $mes_ano);
        return ucfirst($meses[$parts[1] - 1]) . ' de ' . $parts[0];
    }

    /**
     * Salvar arquivo
     */
    private function salvarArquivo($conteudo, $nome, $extensao): string {
        $dir = sys_get_temp_dir();
        $caminho = $dir . '/' . $nome . '.' . $extensao;
        file_put_contents($caminho, $conteudo);
        return $caminho;
    }

    public function gerarRelatorioHorasExtras(string $mes_ano, array $dados): string {
        return $this->salvarArquivo("Relatório em desenvolvimento", "relatorio_extras_$mes_ano", 'txt');
    }

    public function gerarReciboPonto(string $usuario_nome, string $data_ponto, array $dados): string {
        return $this->salvarArquivo("Recibo em desenvolvimento", "recibo_ponto_$data_ponto", 'txt');
    }
}
