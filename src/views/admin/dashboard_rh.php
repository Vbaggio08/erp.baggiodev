<?php
/**
 * Dashboard RH - FASE 5
 * View para gestão de RH com métricas consolidadas, horas extras, faltas
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php require_once __DIR__ . '/../geral/header.php'; ?>

<div class="container-fluid p-4">
    <!-- Header do Dashboard -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="fas fa-chart-bar"></i> Dashboard RH
            </h2>
            <small class="text-muted">Gerenciamento de ponto, horas extras e conformidade</small>
        </div>
        <div class="col-md-4 text-right">
            <div class="row">
                <div class="col-md-6">
                    <select id="filtro-mes" class="form-control form-control-sm" onchange="atualizarDashboardRH()">
                        <?php
                            for ($i = 0; $i < 12; $i++) {
                                $data = new DateTime("-$i months");
                                $selecionado = $i === 0 ? 'selected' : '';
                                echo '<option value="' . $data->format('m/Y') . '" ' . $selecionado . '>' . 
                                     $data->format('F/Y') . '</option>';
                            }
                        ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <button class="btn btn-sm btn-primary w-100" onclick="atualizarDashboardRH()">
                        <i class="fas fa-sync"></i> Atualizar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Principais -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1 small">Usuários Ativos</div>
                    <div class="h3 mb-0" id="kpi-usuarios-ativos">--</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1 small">Com Apontamento</div>
                    <div class="h3 mb-0" id="kpi-usuarios-apontamento">--</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1 small">Extras Pendentes</div>
                    <div class="h3 mb-0" id="kpi-extras-pendentes">--</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-danger text-uppercase mb-1 small">Extras a Pagar</div>
                    <div class="h3 mb-0" id="kpi-extras-pagar">--</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total de Horas Trabalhadas -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Horas Trabalhadas - Total do Período</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="h2 text-primary" id="total-horas-trabalhadas">--</div>
                            <small class="text-muted">horas totalizadas</small>
                        </div>
                        <div class="col-md-9">
                            <canvas id="chart-horas-trabalhadas-linha"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 10 Usuários com Horas Extras -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between">
                    <h5 class="mb-0">Top 10 - Horas Extras</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="exportarTopExtras()">
                        <i class="fas fa-download"></i> Exportar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr class="bg-light">
                                    <th>Usuário</th>
                                    <th class="text-right">Pendentes</th>
                                    <th class="text-right">Aprovadas</th>
                                    <th class="text-right">Pago</th>
                                </tr>
                            </thead>
                            <tbody id="top-extras-table">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usuários com Faltas -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between">
                    <h5 class="mb-0">Usuários com Faltas Detectadas</h5>
                    <span class="badge badge-danger" id="badge-faltas">0</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr class="bg-light">
                                    <th>Usuário</th>
                                    <th class="text-right">Email</th>
                                    <th class="text-right">Dias Presentes</th>
                                </tr>
                            </thead>
                            <tbody id="faltas-table">
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Atividade Offline (últimas 7 dias) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between">
                    <h5 class="mb-0">Atividade Offline - Últimos 7 Dias</h5>
                    <small class="text-muted" id="total-usuarios-offline">Carregando...</small>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr class="bg-light">
                                    <th>Usuário</th>
                                    <th class="text-center">Dias com Offline</th>
                                    <th class="text-center">Horas Sincronizadas</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="offline-table">
                                <tr>
                                    <td colspan="4" class="text-center text-muted">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos Analíticos -->
    <div class="row mb-4">
        <!-- Distribuição de Status de Horas Extras -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Status das Horas Extras</h5>
                </div>
                <div class="card-body">
                    <canvas id="chart-status-extras"></canvas>
                </div>
            </div>
        </div>

        <!-- Distribuição de Usuários por Saldo -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Distribuição de Saldos</h5>
                </div>
                <div class="card-body">
                    <canvas id="chart-distribuicao-saldos"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Relatórios -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="fas fa-file-alt"></i> Relatórios Disponíveis
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-block" onclick="gerarRelatorioPonto()">
                                <i class="fas fa-file-pdf"></i><br>
                                Ponto Consolidado
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-block" onclick="gerarRelatorioExtras()">
                                <i class="fas fa-file-pdf"></i><br>
                                Horas Extras
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-block" onclick="gerarRelatorioDSR()">
                                <i class="fas fa-file-pdf"></i><br>
                                DSR / Descanso
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-primary btn-block" onclick="gerarRelatorioCLT()">
                                <i class="fas fa-file-pdf"></i><br>
                                Conformidade CLT
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: none;
        border-radius: 8px;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1) !important;
    }

    .border-left-primary { border-left: 4px solid #667cea !important; }
    .border-left-success { border-left: 4px solid #48bb78 !important; }
    .border-left-warning { border-left: 4px solid #fcd34d !important; }
    .border-left-danger { border-left: 4px solid #f56565 !important; }

    .text-primary { color: #667cea !important; }
    .text-success { color: #48bb78 !important; }
    .text-warning { color: #ed8936 !important; }
    .text-danger { color: #f56565 !important; }

    .bg-light { background-color: #f8f9fa !important; }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .btn-outline-primary {
        color: #667cea;
        border-color: #667cea;
    }

    .btn-outline-primary:hover {
        background-color: #667cea;
        border-color: #667cea;
    }

    .badge {
        padding: 0.5rem 0.75rem;
        font-size: 0.85rem;
    }

    .badge-danger { background-color: #f56565 !important; }
    .badge-warning { background-color: #ed8936 !important; }
    .badge-success { background-color: #48bb78 !important; }

    /* ===== ESTILOS DE CORES HARMONIOSAS ===== */
    
    /* Border-left color styles - Padrão consistente */
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    /* Card enhancements - Estilo sólido e profissional */
    .card {
        border: 0;
        border-radius: 8px;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12) !important;
    }

    .card-header {
        border-bottom: 1px solid #e9ecef;
        background-color: #f8f9fa !important;
        font-weight: 500;
        color: #333;
    }

    /* Table improvements - Melhor legibilidade */
    .table thead tr {
        background-color: #f8f9fa;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.85rem;
        color: #495057;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Bootstrap icons and text styling */
    .text-primary { color: #007bff !important; }
    .text-success { color: #28a745 !important; }
    .text-warning { color: #856404 !important; }
    .text-danger { color: #dc3545 !important; }
    .text-info { color: #17a2b8 !important; }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .col-md-3 {
            margin-bottom: 15px;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    let charts = {};

    document.addEventListener('DOMContentLoaded', function() {
        atualizarDashboardRH();
    });

    function atualizarDashboardRH() {
        const mesAno = document.getElementById('filtro-mes').value;
        const [mes, ano] = mesAno.split('/');

        fetch(`<?php echo $base_url ?? ''; ?>index.php?rota=dashboard_rh_json&mes=${mes}&ano=${ano}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sucesso') {
                    preencherKPIs(data.dados.totais);
                    preencherTopExtras(data.dados.top_horas_extras);
                    preencherFaltas(data.dados.usuarios_com_faltas);
                    preencherOffline(data.dados.usuarios_com_offline);
                    renderizarGraficos();
                }
            });
    }

    function preencherKPIs(totais) {
        document.getElementById('kpi-usuarios-ativos').textContent = totais.usuarios_ativos;
        document.getElementById('kpi-usuarios-apontamento').textContent = totais.usuarios_com_apontamento;
        document.getElementById('kpi-extras-pendentes').textContent = totais.extras_pendentes;
        document.getElementById('kpi-extras-pagar').textContent = totais.extras_pagar;
        document.getElementById('total-horas-trabalhadas').textContent = 
            totais.horas_trabalhadas_total.toFixed(1).replace('.', ',');
    }

    function preencherTopExtras(dados) {
        let html = '';
        dados.forEach(item => {
            html += `<tr>
                <td><strong>${item.nome}</strong></td>
                <td class="text-right">${item.pendentes.toFixed(1)}</td>
                <td class="text-right">${item.aprovado_nao_pago.toFixed(1)}</td>
                <td class="text-right">${item.pago.toFixed(1)}</td>
            </tr>`;
        });
        document.getElementById('top-extras-table').innerHTML = html || '<tr><td colspan="4" class="text-center text-muted">Nenhum dado</td></tr>';
    }

    function preencherFaltas(dados) {
        let html = '';
        dados.forEach(item => {
            html += `<tr>
                <td><strong>${item.nome}</strong></td>
                <td class="text-right"><a href="mailto:${item.email}">${item.email}</a></td>
                <td class="text-right"><span class="badge badge-warning">${item.dias_com_apontamento}</span></td>
            </tr>`;
        });
        document.getElementById('faltas-table').innerHTML = html || '<tr><td colspan="3" class="text-center text-muted">Nenhum dado</td></tr>';
        document.getElementById('badge-faltas').textContent = dados.length;
    }

    function preencherOffline(dados) {
        let html = '';
        dados.forEach(item => {
            const badge = item.dias_offline > 5 ? '<span class="badge badge-danger">Crítico</span>' :
                         item.dias_offline > 2 ? '<span class="badge badge-warning">Moderado</span>' :
                         '<span class="badge badge-success">Normal</span>';
            
            html += `<tr>
                <td><strong>${item.nome}</strong></td>
                <td class="text-center">${item.dias_offline}</td>
                <td class="text-center">${item.horas_sincronizadas.toFixed(1)}h</td>
                <td class="text-center">${badge}</td>
            </tr>`;
        });
        document.getElementById('offline-table').innerHTML = html || '<tr><td colspan="4" class="text-center text-muted">Nenhum dado</td></tr>';
        document.getElementById('total-usuarios-offline').textContent = `${dados.length} usuário(s) detectado(s)`;
    }

    function renderizarGraficos() {
        // Gráfico de status de extras (pizza)
        const ctxStatus = document.getElementById('chart-status-extras');
        if (ctxStatus && charts['status-extras']) charts['status-extras'].destroy();
        
        if (ctxStatus) {
            charts['status-extras'] = new Chart(ctxStatus, {
                type: 'doughnut',
                data: {
                    labels: ['Pendente', 'Aprovado (a pagar)', 'Pago', 'Rejeitado'],
                    datasets: [{
                        data: [15, 8, 25, 3],
                        backgroundColor: ['#fcd34d', '#667cea', '#48bb78', '#f56565']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        }

        // Gráfico de distribuição de saldos (barra)
        const ctxSaldos = document.getElementById('chart-distribuicao-saldos');
        if (ctxSaldos && charts['distribuicao-saldos']) charts['distribuicao-saldos'].destroy();
        
        if (ctxSaldos) {
            charts['distribuicao-saldos'] = new Chart(ctxSaldos, {
                type: 'bar',
                data: {
                    labels: ['< -10h', '-10 a 0h', '0 a +10h', '> +10h'],
                    datasets: [{
                        label: 'Número de Usuários',
                        data: [5, 12, 28, 8],
                        backgroundColor: ['#f56565', '#fcd34d', '#48bb78', '#667cea'],
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } }
                }
            });
        }
    }

    function gerarRelatorioPonto() {
        alert('[DESENVOLVIMENTO] Gerar relatório consolidado de ponto em PDF/Excel');
    }

    function gerarRelatorioExtras() {
        alert('[DESENVOLVIMENTO] Gerar relatório de horas extras');
    }

    function gerarRelatorioDSR() {
        alert('[DESENVOLVIMENTO] Gerar relatório de DSR e descansos');
    }

    function gerarRelatorioCLT() {
        alert('[DESENVOLVIMENTO] Gerar relatório de conformidade CLT');
    }

    function exportarTopExtras() {
        alert('[DESENVOLVIMENTO] Exportar top 10 horas extras');
    }
</script>

<?php require_once __DIR__ . '/../geral/footer.php'; ?>
