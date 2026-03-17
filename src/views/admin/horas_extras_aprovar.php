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
 * Requer: $_SESSION['user_nivel'] em ['rh', 'gerente', 'admin']
 */

// Validar autenticação e autorização
if (empty($_SESSION['user_id']) || !in_array($_SESSION['user_nivel'] ?? '', ['rh', 'gerente', 'admin'])) {
    header('Location: /login');
    exit;
}

// Dados simulados (em produção viriam do controlador/API)
$pendentes = [];
$filtro_usuario = $_GET['usuario_id'] ?? '';
$filtro_mes = $_GET['mes'] ?? date('Y-m');
$base_url = $base_url ?? '';
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" onclick="confirmarRejeitar()">
                    <i class="fas fa-times"></i> Rejeitar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Relatório -->
<div class="modal fade" id="modalRelatorio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Relatório Mensal de Horas Extras</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
    background: #252525;
    border-radius: 4px;
    flex: 1;
}
</style>

<script>
const baseUrl = '<?php echo $base_url; ?>';
let ID_APROVACAO = null;
let ID_REJEICAO = null;

// Carregar dados reais via AJAX ao abrir a página
document.addEventListener('DOMContentLoaded', carregarPendentes);

function carregarPendentes() {
    const usuario_id = document.getElementById('filtro_usuario').value;
    const mes = document.getElementById('filtro_mes').value;
    const status = document.getElementById('filtro_status').value;

    const params = new URLSearchParams();
    if (usuario_id) params.append('usuario_id', usuario_id);
    if (mes) params.append('mes', mes);

    fetch(baseUrl + 'index.php?rota=horas_extras_pendentes&' + params.toString())
        .then(r => r.json())
        .then(data => {
            if (data.sucesso && data.dados) {
                renderizarTabela(data.dados);
                document.getElementById('qtd_pendentes').innerHTML = '<i class="fas fa-hourglass-half"></i> ' + data.total;
                document.getElementById('badge_pendentes').textContent = data.total;
            }
        })
        .catch(err => console.error('Erro ao carregar pendentes:', err));
}

function renderizarTabela(pendentes) {
    const tbody = document.querySelector('.table-hover tbody');
    if (!tbody) return;
    if (pendentes.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted p-4">Nenhuma hora extra pendente</td></tr>';
        return;
    }
    let html = '';
    pendentes.forEach(p => {
        const data = p.data_referencia ? new Date(p.data_referencia + 'T00:00:00').toLocaleDateString('pt-BR') : '-';
        const tipoBadge = p.tipo === '100' ? 'badge-danger' : 'badge-warning';
        html += `<tr id="row_${p.id}">
            <td><strong>${data}</strong></td>
            <td><strong>${p.nome_usuario || 'N/A'}</strong><br><small class="text-muted">${p.email || ''}</small></td>
            <td><span class="badge badge-info">${parseFloat(p.horas_extras).toFixed(1)}h</span></td>
            <td><span class="badge ${tipoBadge}">+${p.tipo}%</span></td>
            <td><small>${(p.motivo || '').substring(0, 50)}</small></td>
            <td>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-success" onclick="abrirAprovar(${p.id}, '${(p.nome_usuario || '').replace(/'/g, "\\'")}', ${p.horas_extras})" title="Aprovar"><i class="fas fa-check"></i></button>
                    <button class="btn btn-outline-danger" onclick="abrirRejeitar(${p.id}, '${(p.nome_usuario || '').replace(/'/g, "\\'")}' )" title="Rejeitar"><i class="fas fa-times"></i></button>
                </div>
            </td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function aplicarFiltros() {
    carregarPendentes();
}

function limparFiltros() {
    document.getElementById('filtro_usuario').value = '';
    document.getElementById('filtro_mes').value = new Date().toISOString().slice(0, 7);
    document.getElementById('filtro_status').value = 'pendente';
    carregarPendentes();
}

function abrirAprovar(id, nome, horas) {
    ID_APROVACAO = id;
    document.getElementById('modal_usuario_nome').innerText = nome;
    document.getElementById('modal_horas').innerText = horas;
    document.getElementById('obs_aprovar').value = '';
    new bootstrap.Modal(document.getElementById('modalAprovar')).show();
}

function confirmarAprovar() {
    const obs = document.getElementById('obs_aprovar').value;
    fetch(baseUrl + 'index.php?rota=horas_extras_aprovar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: ID_APROVACAO, observacao: obs })
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            alert('✅ Hora extra aprovada com sucesso!');
            bootstrap.Modal.getInstance(document.getElementById('modalAprovar')).hide();
            carregarPendentes();
        } else {
            alert('❌ Erro: ' + (data.erro || 'Falha ao aprovar'));
        }
    })
    .catch(() => alert('Erro de conexão'));
}

function abrirRejeitar(id, nome) {
    ID_REJEICAO = id;
    document.getElementById('modal_usuario_rejeicao').innerText = nome;
    document.getElementById('motivo_rejeicao').value = '';
    new bootstrap.Modal(document.getElementById('modalRejeitar')).show();
}

function confirmarRejeitar() {
    const motivo = document.getElementById('motivo_rejeicao').value;
    if (motivo.length < 10) {
        alert('⚠️ Motivo deve ter pelo menos 10 caracteres');
        return;
    }
    fetch(baseUrl + 'index.php?rota=horas_extras_rejeitar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: ID_REJEICAO, motivo: motivo })
    })
    .then(r => r.json())
    .then(data => {
        if (data.sucesso) {
            alert('✅ Hora extra rejeitada.');
            bootstrap.Modal.getInstance(document.getElementById('modalRejeitar')).hide();
            carregarPendentes();
        } else {
            alert('❌ Erro: ' + (data.erro || 'Falha ao rejeitar'));
        }
    })
    .catch(() => alert('Erro de conexão'));
}

function abrirRelatorio() {
    document.getElementById('relatorio_mes').value = new Date().toISOString().slice(0, 7);
    new bootstrap.Modal(document.getElementById('modalRelatorio')).show();
}

function gerarRelatorio() {
    const mes = document.getElementById('relatorio_mes').value;
    fetch(baseUrl + 'index.php?rota=horas_extras_relatorio&mes=' + mes)
        .then(r => r.json())
        .then(data => {
            if (data.sucesso && data.por_usuario) {
                let html = '';
                data.por_usuario.forEach(u => {
                    html += `<tr>
                        <td>${u.nome}</td>
                        <td><small>${u.email || ''}</small></td>
                        <td class="text-right">${(u.pendente || 0).toFixed(1)}</td>
                        <td class="text-right"><strong>${(u.aprovado || 0).toFixed(1)}</strong></td>
                        <td class="text-right">${(u.rejeitado || 0).toFixed(1)}</td>
                        <td class="text-right">${(u.pago || 0).toFixed(1)}</td>
                        <td class="text-right"><strong>${(u.total_horas || 0).toFixed(1)}</strong></td>
                    </tr>`;
                });
                document.getElementById('tbody_relatorio').innerHTML = html;
                document.getElementById('conteudo_relatorio').style.display = 'block';
            }
        });
}

function exportarRelatorioExcel() {
    const linhas = Array.from(document.querySelectorAll('#tbody_relatorio tr'));
    if (linhas.length === 0) {
        alert('Gere o relatório antes de exportar.');
        return;
    }

    let csv = 'Usuario,Email,Pendente,Aprovado,Rejeitado,Pago,Total\n';
    linhas.forEach((tr) => {
        const cols = Array.from(tr.querySelectorAll('td')).map((td) => {
            const txt = (td.innerText || '').replace(/\s+/g, ' ').trim();
            return '"' + txt.replace(/"/g, '""') + '"';
        });
        csv += cols.join(',') + '\n';
    });

    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'relatorio_horas_extras.csv';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

function verMotivoCompleto(id) {
    alert('Detalhes do motivo serão carregados via API');
}
</script>
