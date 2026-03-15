<?php
/**
 * Dashboard de Ponto - FASE 5
 * View para funcionários acompanharem seu saldo, horas extras e DSR
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<?php require_once __DIR__ . '/../geral/header.php'; ?>

<div class="container-fluid p-4">
    <!-- Header do Dashboard -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h2>
                <i class="fas fa-chart-line"></i> Meu Dashboard de Ponto
            </h2>
        </div>
        <div class="col-md-6 text-right">
            <button class="btn btn-sm btn-outline-primary" onclick="atualizarDados()">
                <i class="fas fa-sync"></i> Atualizar
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportarPDF()">
                <i class="fas fa-file-pdf"></i> Exportar
            </button>
        </div>
    </div>

    <!-- Alertas de Anomalias -->
    <div id="anomalias-container"></div>

    <!-- Cards de Métricas Principais -->
    <div class="row mb-4">
        <!-- Horas Hoje -->
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-primary text-uppercase mb-1">Horas de Hoje</div>
                    <div class="h3 mb-0">
                        <span id="horas-hoje">--</span> h
                    </div>
                    <small class="text-muted">Até o momento</small>
                </div>
            </div>
        </div>

        <!-- Saldo Mês -->
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-success text-uppercase mb-1">Saldo do Mês</div>
                    <div class="h3 mb-0" id="saldo-mes">-- h</div>
                    <small class="text-muted">Acumulado</small>
                </div>
            </div>
        </div>

        <!-- Horas Extras Pendentes -->
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-warning text-uppercase mb-1">Extras Pendentes</div>
                    <div class="h3 mb-0" id="extras-pendentes">0</div>
                    <small class="text-muted">Aguardando aprovação</small>
                </div>
            </div>
        </div>

        <!-- Horas Extras a Pagar -->
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-info text-uppercase mb-1">Extras a Pagar</div>
                    <div class="h3 mb-0" id="extras-pagar">0</div>
                    <small class="text-muted">Já aprovadas</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Próximos Eventos -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Próximos Eventos</h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <!-- Próximo DSR -->
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-bed text-primary"></i> Próximo DSR
                                    </h6>
                                    <p class="mb-0 small text-muted" id="proximo-dsr">Carregando...</p>
                                </div>
                                <span class="badge badge-primary" id="dias-para-dsr"></span>
                            </div>
                        </div>

                        <!-- Próximo Feriado -->
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-calendar text-danger"></i> Próximo Feriado
                                    </h6>
                                    <p class="mb-0 small text-muted" id="proximo-feriado">Carregando...</p>
                                </div>
                                <span class="badge badge-danger" id="dias-para-feriado"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações Legais -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Informações de Conformidade</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info small">
                        <strong>Lei 605/49:</strong> Você tem direito a DSR (descanso semanal remunerado) conforme lei.
                    </div>
                    <div class="alert alert-info small">
                        <strong>Lei 10.820/2003:</strong> Horas extras devem ser autorizadas previamente.
                    </div>
                    <div class="alert alert-info small">
                        <strong>CLT:</strong> Jornada máxima: 8h/dia. Hora extra até 2h/dia.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos -->
    <div class="row mb-4">
        <!-- Gráfico de Horas (últimos 30 dias) -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Horas Trabalhadas (Últimos 30 dias)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chart-horas-30dias"></canvas>
                </div>
            </div>
        </div>

        <!-- Gráfico Saldo (últimos 6 meses) -->
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Saldo de Horas (Últimos 6 meses)</h5>
                </div>
                <div class="card-body">
                    <canvas id="chart-saldo-6meses"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Histórico Recente -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Apontamentos Recentes (7 dias)</h5>
                    <button class="btn btn-sm btn-outline-primary" onclick="abrirHistorico()">
                        Ver Completo
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Entrada 1</th>
                                    <th>Saída 1</th>
                                    <th>Entrada 2</th>
                                    <th>Saída 2</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="historico-recente">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Carregando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Exportação -->
<div class="modal fade" id="modalExportacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Exportar Relatório</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Período</label>
                    <select id="exportacao-mes" class="form-control">
                        <option value="">Selecione o mês...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Formato</label>
                    <select id="exportacao-formato" class="form-control">
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                        <option value="json">JSON</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="realizarExportacao()">
                    Exportar
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .card {
        border: 0;
        border-radius: 8px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.12) !important;
    }

    .card-header {
        background-color: #f8f9fa !important;
        border-bottom: 1px solid #e9ecef;
        font-weight: 500;
    }

    /* Cores harmoniosas e sólidas - Padrão Bootstrap consistente */
    .border-left-primary {
        border-left: 4px solid #007bff !important;
    }

    .border-left-success {
        border-left: 4px solid #28a745 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-info {
        border-left: 4px solid #17a2b8 !important;
    }

    .border-left-danger {
        border-left: 4px solid #dc3545 !important;
    }

    .text-primary { color: #007bff !important; }
    .text-success { color: #28a745 !important; }
    .text-warning { color: #856404 !important; }
    .text-info { color: #17a2b8 !important; }
    .text-danger { color: #dc3545 !important; }

    .badge {
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .badge-primary { background-color: #007bff !important; }
    .badge-success { background-color: #28a745 !important; }
    .badge-warning { background-color: #ffc107 !important; color: #000 !important; }
    .badge-info { background-color: #17a2b8 !important; }
    .badge-danger { background-color: #dc3545 !important; }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }

    .list-group-item {
        border: none;
        border-bottom: 1px solid #e3e6f0;
    }

    .list-group-item:last-child {
        border-bottom: none;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
    let chartHoras30 = null;
    let chartSaldo6 = null;

    // Carregar dados ao abrir a página
    document.addEventListener('DOMContentLoaded', function() {
        atualizarDados();
        carregarMesesExportacao();
    });

    // Atualizar todos os dados
    function atualizarDados() {
        fetch('<?php echo $base_url ?? ''; ?>index.php?rota=dashboard_ponto_json')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sucesso') {
                    preencherMetricas(data.dados.metricas);
                    preencherProximosEventos(data.dados.proximos_eventos);
                    preencherAnomalias(data.dados.anomalias);
                    carregarGraficos();
                    carregarHistoricoRecente();
                }
            })
            .catch(error => console.error('Erro ao carregar dados:', error));
    }

    function preencherMetricas(metricas) {
        document.getElementById('horas-hoje').textContent = metricas.horas_hoje.toFixed(2).replace('.', ',');
        document.getElementById('saldo-mes').textContent = metricas.saldo_mes.toFixed(2).replace('.', ',') + ' h';
        document.getElementById('extras-pendentes').textContent = metricas.extras_pendentes;
        document.getElementById('extras-pagar').textContent = metricas.extras_pagar;
    }

    function preencherProximosEventos(eventos) {
        if (eventos.dsr) {
            const dataStr = eventos.dsr.split(' ')[0];
            const dias = Math.ceil((new Date(dataStr) - new Date()) / (1000 * 60 * 60 * 24));
            document.getElementById('proximo-dsr').textContent = `${dataStr}`;
            document.getElementById('dias-para-dsr').textContent = `${dias} dias`;
        } else {
            document.getElementById('proximo-dsr').textContent = 'Sem DSR agendado';
        }

        if (eventos.feriado) {
            const dias = Math.ceil((new Date(eventos.feriado.data) - new Date()) / (1000 * 60 * 60 * 24));
            document.getElementById('proximo-feriado').textContent = 
                `${eventos.feriado.data} - ${eventos.feriado.descricao}`;
            document.getElementById('dias-para-feriado').textContent = `${dias} dias`;
        } else {
            document.getElementById('proximo-feriado').textContent = 'Sem feriados próximos';
        }
    }

    function preencherAnomalias(anomalias) {
        const container = document.getElementById('anomalias-container');
        container.innerHTML = '';

        if (anomalias && anomalias.length > 0) {
            anomalias.forEach(anomalia => {
                const classe = anomalia.tipo === 'critico' ? 'alert-danger' : 'alert-warning';
                container.innerHTML += `<div class="alert ${classe} alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle"></i> ${anomalia.mensagem}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>`;
            });
        }
    }

    function carregarGraficos() {
        // Gráfico de horas (últimos 30 dias)
        fetch('<?php echo $base_url ?? ''; ?>index.php?rota=dashboard_graficos_json')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sucesso') {
                    renderizarGraficoHoras(data.dados.grafico_horas_30dias);
                }
            });

        // Gráfico de saldo (últimos 6 meses)
        fetch('<?php echo $base_url ?? ''; ?>index.php?rota=dashboard_saldo_json')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'sucesso') {
                    renderizarGraficoSaldo(data.dados.grafico_saldo_6meses);
                }
            });
    }

    function renderizarGraficoHoras(dados) {
        const ctx = document.getElementById('chart-horas-30dias');
        if (!ctx) return;

        if (chartHoras30) chartHoras30.destroy();

        chartHoras30 = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dados.labels,
                datasets: [{
                    label: 'Horas Trabalhadas',
                    data: dados.datasets[0].data,
                    borderColor: '#667cea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#667cea'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: function(value) { return value + 'h'; } }
                    }
                }
            }
        });
    }

    function renderizarGraficoSaldo(dados) {
        const ctx = document.getElementById('chart-saldo-6meses');
        if (!ctx) return;

        if (chartSaldo6) chartSaldo6.destroy();

        chartSaldo6 = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: dados.labels,
                datasets: [{
                    label: 'Saldo de Horas',
                    data: dados.datasets[0].data,
                    backgroundColor: dados.datasets[0].backgroundColor,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            callback: function(value) { 
                                return (value >= 0 ? '+' : '') + value + 'h'; 
                            } 
                        }
                    }
                }
            }
        });
    }

    function carregarHistoricoRecente() {
        fetch('<?php echo $base_url ?? ''; ?>index.php?rota=meu_ponto_json')
            .then(response => response.json())
            .then(data => {
                if (data && data.length > 0) {
                    let html = '';
                    data.slice(0, 7).forEach(apt => {
                        const status = apt.conferida ? '<span class="badge badge-success">Conferido</span>' : 
                                      aptincompleta ? '<span class="badge badge-warning">Incompleto</span>' : 
                                      '<span class="badge badge-secondary">Normal</span>';
                        
                        html += `<tr>
                            <td>${apt.data}</td>
                            <td>${apt.hora_entrada_1 || '-'}</td>
                            <td>${apt.hora_saida_1 || '-'}</td>
                            <td>${apt.hora_entrada_2 || '-'}</td>
                            <td>${apt.hora_saida_2 || '-'}</td>
                            <td>${apt.total_horas}</td>
                            <td>${status}</td>
                        </tr>`;
                    });
                    document.getElementById('historico-recente').innerHTML = html;
                }
            });
    }

    function abrirHistorico() {
        window.location.href = '<?php echo $base_url ?? ''; ?>index.php?rota=meu_ponto';
    }

    function carregarMesesExportacao() {
        const select = document.getElementById('exportacao-mes');
        const hoje = new Date();
        
        for (let i = 0; i < 12; i++) {
            const data = new Date(hoje.getFullYear(), hoje.getMonth() - i, 1);
            const valor = data.getFullYear() + '-' + String(data.getMonth() + 1).padStart(2, '0');
            const label = data.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
            select.innerHTML += `<option value="${valor}">${label}</option>`;
        }
    }

    function exportarPDF() {
        document.getElementById('modalExportacao').classList.add('show');
        document.getElementById('modalExportacao').style.display = 'block';
    }

    function realizarExportacao() {
        const mes = document.getElementById('exportacao-mes').value;
        const formato = document.getElementById('exportacao-formato').value;

        if (!mes) {
            alert('Selecione um mês');
            return;
        }

        const url = `<?php echo $base_url ?? ''; ?>index.php?rota=exportar_ponto&mes_ano=${mes}&formato=${formato}`;
        window.open(url, '_blank');
        
        document.getElementById('modalExportacao').classList.remove('show');
        document.getElementById('modalExportacao').style.display = 'none';
    }
</script>

<?php require_once __DIR__ . '/../geral/footer.php'; ?>
