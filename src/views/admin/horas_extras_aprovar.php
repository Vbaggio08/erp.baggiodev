<?php
/**
 * View: Aprovação de Horas Extras
 * Path: src/views/admin/horas_extras_aprovar.php
 * 
 * Interface para RH/Gerentes:
 * - Visualizar fila de horas extras pendentes
 * - Aprovar com observações
 * - Rejeitar com motivo
 * - Visualizar histórico de aprovações
 * - Gerar relatório mensal
 * 
 * Requer: $_SESSION['role'] em ['RH', 'gerente', 'admin']
 */

// Validar autenticação e autorização
if (empty($_SESSION['usuario_id']) || !in_array($_SESSION['role'] ?? '', ['RH', 'gerente', 'admin'])) {
    header('Location: /login');
    exit;
}

// Dados simulados (em produção viriam do controlador/API)
$pendentes = [];
$filtro_usuario = $_GET['usuario_id'] ?? '';
$filtro_mes = $_GET['mes'] ?? date('Y-m');

// Exemplo de dados para demonstração
if (empty($pendentes)) {
    $pendentes = [
        [
            'id' => 1,
            'usuario_id' => 1,
            'nome_usuario' => 'João Silva',
            'email' => 'joao@empresa.com',
            'data_referencia' => '2026-03-15',
            'horas_extras' => 2.5,
            'tipo' => '50',
            'motivo' => 'Projeto crítico de produção',
            'status' => 'pendente',
            'criado_em' => '2026-03-15 18:30:00'
        ],
        [
            'id' => 2,
            'usuario_id' => 2,
            'nome_usuario' => 'Maria Santos',
            'email' => 'maria@empresa.com',
            'data_referencia' => '2026-03-18',
            'horas_extras' => 1.0,
            'tipo' => '100',
            'motivo' => 'Atendimento de cliente urgente',
            'status' => 'pendente',
            'criado_em' => '2026-03-18 21:00:00'
        ]
    ];
}
?>

<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="fas fa-check-circle"></i> Aprovação de Horas Extras
            </h2>
            <small class="text-muted">Fila de pendências para aprovação da Gestão</small>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-info" onclick="abrirRelatorio()">
                <i class="fas fa-file-excel"></i> Relatório Mensal
            </button>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4 bg-light">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-3">
                    <label><small>Filtrar por Usuário</small></label>
                    <select class="form-control form-control-sm" id="filtro_usuario" onchange="aplicarFiltros()">
                        <option value="">-- Todos --</option>
                        <option value="1" <?php echo $filtro_usuario === '1' ? 'selected' : ''; ?>>João Silva</option>
                        <option value="2" <?php echo $filtro_usuario === '2' ? 'selected' : ''; ?>>Maria Santos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label><small>Mês/Ano</small></label>
                    <input type="month" class="form-control form-control-sm" id="filtro_mes" 
                           value="<?php echo $filtro_mes; ?>" onchange="aplicarFiltros()">
                </div>
                <div class="col-md-3">
                    <label><small>Status</small></label>
                    <select class="form-control form-control-sm" id="filtro_status" onchange="aplicarFiltros()">
                        <option value="pendente">Pendentes</option>
                        <option value="">Todos</option>
                        <option value="aprovado">Aprovados</option>
                        <option value="rejeitado">Rejeitados</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-sm btn-secondary btn-block" onclick="limparFiltros()">
                        <i class="fas fa-times"></i> Limpar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Estatísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h4 class="text-warning mb-0" id="qtd_pendentes">
                        <i class="fas fa-hourglass-half"></i> 2
                    </h4>
                    <small class="text-muted">Pendentes</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h4 class="text-success mb-0" id="qtd_aprovadas">
                        <i class="fas fa-check"></i> 15
                    </h4>
                    <small class="text-muted">Aprovadas (mês)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h4 class="text-danger mb-0" id="qtd_rejeitadas">
                        <i class="fas fa-times"></i> 2
                    </h4>
                    <small class="text-muted">Rejeitadas (mês)</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h4 class="text-info mb-0" id="total_horas">
                        <i class="fas fa-clock"></i> 31.5h
                    </h4>
                    <small class="text-muted">Total Aprovado</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Pendentes -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0">
                <i class="fas fa-list"></i> Fila de Pendências
                <span class="badge badge-warning float-right" id="badge_pendentes">2</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <?php if (empty($pendentes)): ?>
                <div class="alert alert-info m-3 mb-0">
                    <i class="fas fa-info-circle"></i>
                    Nenhuma hora extra pendente de aprovação no período selecionado.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th style="width: 15%">Data</th>
                                <th style="width: 20%">Usuário</th>
                                <th style="width: 10%">Horas</th>
                                <th style="width: 8%">Tipo</th>
                                <th style="width: 35%">Motivo</th>
                                <th style="width: 12%">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendentes as $pend): ?>
                                <tr id="row_<?php echo $pend['id']; ?>" class="align-middle">
                                    <td>
                                        <strong><?php echo date('d/m/Y', strtotime($pend['data_referencia'])); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo date('H:i', strtotime($pend['criado_em'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($pend['nome_usuario']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($pend['email']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-info" title="Horas Extras">
                                            <?php echo number_format($pend['horas_extras'], 1, ',', '.'); ?>h
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $pend['tipo'] === '100' ? 'badge-danger' : 'badge-warning'; ?>">
                                            +<?php echo $pend['tipo']; ?>%
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars(substr($pend['motivo'], 0, 50)); ?></small>
                                        <br>
                                        <button class="btn btn-link btn-sm p-0" onclick="verMotivoCompleto('<?php echo $pend['id']; ?>')">
                                            Ver completo →
                                        </button>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button class="btn btn-outline-success" 
                                                    onclick="abrirAprovar(<?php echo $pend['id']; ?>, '<?php echo htmlspecialchars($pend['nome_usuario']); ?>', <?php echo $pend['horas_extras']; ?>)"
                                                    title="Aprovar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-outline-danger" 
                                                    onclick="abrirRejeitar(<?php echo $pend['id']; ?>, '<?php echo htmlspecialchars($pend['nome_usuario']); ?>')"
                                                    title="Rejeitar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Histórico de Aprovações -->
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0">
                <i class="fas fa-history"></i> Histórico de Aprovações (Últimos 30 dias)
            </h6>
        </div>
        <div class="card-body">
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="mb-0">Aprovado</h6>
                        <small class="text-muted">João Silva - 2.5h em 14/03/2026</small>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker bg-danger"></div>
                    <div class="timeline-content">
                        <h6 class="mb-0">Rejeitado</h6>
                        <small class="text-muted">Pedro Costa - 1.0h em 10/03/2026 - Motivo: Não conferiu</small>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-marker bg-success"></div>
                    <div class="timeline-content">
                        <h6 class="mb-0">Aprovado</h6>
                        <small class="text-muted">Maria Santos - 1.5h em 08/03/2026</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Aprovar -->
<div class="modal fade" id="modalAprovar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Aprovar Hora Extra</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Usuário:</strong> <span id="modal_usuario_nome"></span></p>
                <p><strong>Horas:</strong> <span id="modal_horas"></span>h</p>
                <div class="form-group">
                    <label>Observação (opcional)</label>
                    <textarea class="form-control" id="obs_aprovar" rows="3" 
                              placeholder="Digite aqui qualquer observação sobre aprovação"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="confirmarAprovar()">
                    <i class="fas fa-check"></i> Aprovar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Rejeitar -->
<div class="modal fade" id="modalRejeitar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Rejeitar Hora Extra</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Usuário:</strong> <span id="modal_usuario_rejeicao"></span></p>
                <div class="form-group">
                    <label><strong>Motivo da Rejeição *</strong></label>
                    <textarea class="form-control" id="motivo_rejeicao" rows="3" 
                              placeholder="Explique o motivo da rejeição" required></textarea>
                    <small class="form-text text-muted">Mínimo 10 caracteres</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRejeitar()">
                    <i class="fas fa-times"></i> Rejeitar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Relatório -->
<div class="modal fade" id="modalRelatorio" tabindex="-1" style="max-width: 90%;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Relatório Mensal de Horas Extras</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="relatorio_mes">Mês/Ano</label>
                    <input type="month" class="form-control" id="relatorio_mes">
                </div>
                <div id="conteudo_relatorio" style="display:none;">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th>Usuário</th>
                                    <th>Email</th>
                                    <th class="text-right">Pendente</th>
                                    <th class="text-right">Aprovado</th>
                                    <th class="text-right">Rejeitado</th>
                                    <th class="text-right">Pago</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody id="tbody_relatorio">
                                <!-- Preenchido por JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" onclick="gerarRelatorio()">
                    <i class="fas fa-sync"></i> Gerar Relatório
                </button>
                <button class="btn btn-secondary" onclick="exportarRelatorioExcel()">
                    <i class="fas fa-download"></i> Excel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline-item {
    display: flex;
    margin-bottom: 20px;
}

.timeline-marker {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 20px;
    flex-shrink: 0;
}

.timeline-content {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 4px;
    flex: 1;
}
</style>

<script>
let ID_APROVACAO = null;
let ID_REJEICAO = null;

/**
 * Aplicar filtros e recarregar lista
 */
function aplicarFiltros() {
    const usuario_id = document.getElementById('filtro_usuario').value;
    const mes = document.getElementById('filtro_mes').value;
    const status = document.getElementById('filtro_status').value;

    const params = new URLSearchParams();
    if (usuario_id) params.append('usuario_id', usuario_id);
    if (mes) params.append('mes', mes);
    if (status) params.append('status', status);

    window.location.search = params.toString();
}

/**
 * Limpar filtros
 */
function limparFiltros() {
    document.getElementById('filtro_usuario').value = '';
    document.getElementById('filtro_mes').valueAsDate = new Date();
    document.getElementById('filtro_status').value = 'pendente';
    aplicarFiltros();
}

/**
 * Abrir modal de aprovação
 */
function abrirAprovar(id, nome, horas) {
    ID_APROVACAO = id;
    document.getElementById('modal_usuario_nome').innerText = nome;
    document.getElementById('modal_horas').innerText = horas;
    document.getElementById('obs_aprovar').value = '';
    $('#modalAprovar').modal('show');
}

/**
 * Confirmar aprovação
 */
function confirmarAprovar() {
    const obs = document.getElementById('obs_aprovar').value;

    $.ajax({
        url: '/api/horas-extras/aprovar',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            id: ID_APROVACAO,
            observacao: obs
        }),
        success: function(response) {
            Swal.fire('Aprovado!', 'Hora extra aprovada com sucesso', 'success');
            $('#modalAprovar').modal('hide');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            Swal.fire('Erro!', 'Não foi possível aprovar', 'error');
        }
    });
}

/**
 * Abrir modal de rejeição
 */
function abrirRejeitar(id, nome) {
    ID_REJEICAO = id;
    document.getElementById('modal_usuario_rejeicao').innerText = nome;
    document.getElementById('motivo_rejeicao').value = '';
    $('#modalRejeitar').modal('show');
}

/**
 * Confirmar rejeição
 */
function confirmarRejeitar() {
    const motivo = document.getElementById('motivo_rejeicao').value;

    if (motivo.length < 10) {
        Swal.fire('Aviso', 'Motivo deve ter pelo menos 10 caracteres', 'warning');
        return;
    }

    $.ajax({
        url: '/api/horas-extras/rejeitar',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            id: ID_REJEICAO,
            motivo: motivo
        }),
        success: function(response) {
            Swal.fire('Rejeitado!', 'Hora extra rejeitada e usuário notificado', 'success');
            $('#modalRejeitar').modal('hide');
            setTimeout(() => location.reload(), 1500);
        },
        error: function(xhr) {
            Swal.fire('Erro!', 'Não foi possível rejeitar', 'error');
        }
    });
}

/**
 * Abrir relatório
 */
function abrirRelatorio() {
    document.getElementById('relatorio_mes').valueAsDate = new Date();
    $('#modalRelatorio').modal('show');
}

/**
 * Gerar relatório
 */
function gerarRelatorio() {
    const mes = document.getElementById('relatorio_mes').value;
    
    $.ajax({
        url: `/api/horas-extras/relatorio?mes=${mes}`,
        success: function(response) {
            let html = '';
            response.por_usuario.forEach(u => {
                html += `<tr>
                    <td>${u.nome}</td>
                    <td><small>${u.email}</small></td>
                    <td class="text-right">${u.pendente.toFixed(1)}</td>
                    <td class="text-right"><strong>${u.aprovado.toFixed(1)}</strong></td>
                    <td class="text-right">${u.rejeitado.toFixed(1)}</td>
                    <td class="text-right">${u.pago.toFixed(1)}</td>
                    <td class="text-right"><strong>${u.total_horas.toFixed(1)}</strong></td>
                </tr>`;
            });
            document.getElementById('tbody_relatorio').innerHTML = html;
            document.getElementById('conteudo_relatorio').style.display = 'block';
        }
    });
}

/**
 * Exportar para Excel
 */
function exportarRelatorioExcel() {
    const mes = document.getElementById('relatorio_mes').value;
    window.location.href = `/api/horas-extras/relatorio/exportar?mes=${mes}&formato=xlsx`;
}

/**
 * Ver motivo completo
 */
function verMotivoCompleto(id) {
    // Implementar modal com motivo completo
    Swal.fire('Motivo Completo', 'Projeto crítico de produção que necessitou de horas adicionais', 'info');
}
</script>
