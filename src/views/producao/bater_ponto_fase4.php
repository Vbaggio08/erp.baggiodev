<?php
/**
 * View: Bater Ponto - FASE 4 (Offline Support)
 * Path: src/views/producao/bater_ponto_fase4.php
 * 
 * Interface moderna para bater ponto com:
 * - Suporte completo offline com IndexedDB
 * - Geolocalização integrada
 * - Captura de foto pela câmera
 * - Sync automático quando online
 * - Status visual offline/online
 */

// Validar autenticação
if (empty($_SESSION['usuario_id'])) {
    header('Location: /login');
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
?>

<div class="container-fluid mt-4">
    <!-- Status Online/Offline -->
    <div class="position-fixed" style="top: 20px; right: 20px; z-index: 1000;">
        <div id="status-indicador" class="badge badge-success p-2">
            <i class="fas fa-wifi"></i> Online
        </div>
    </div>

    <!-- Mensagens -->
    <div data-mensagens class="mb-3"></div>

    <!-- Pendentes Offline -->
    <div id="pendentes-offline" style="display:none;" class="alert alert-warning alert-dismissible">
        <i class="fas fa-hourglass-half"></i>
        Você tem <strong data-pendentes-offline>0</strong> batidas aguardando sincronização.
        <button type="button" class="btn btn-sm btn-primary float-right" onclick="pontoOffline.sincronizar()">
            <i class="fas fa-sync"></i> Sincronizar Agora
        </button>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2>
                <i class="fas fa-clock"></i> Sistema de Ponto
            </h2>
            <p class="text-muted">
                Olá, <strong><?php echo htmlspecialchars($usuario_nome); ?></strong>
            </p>
        </div>
        <div class="col-md-4 text-right">
            <button class="btn btn-info btn-sm" id="btn-historico" onclick="abrirHistorico()">
                <i class="fas fa-history"></i> Histórico
            </button>
            <button class="btn btn-secondary btn-sm" id="btn-config" onclick="abrirConfiguracao()">
                <i class="fas fa-cog"></i> Config
            </button>
        </div>
    </div>

    <!-- Card Principal: Bater Ponto -->
    <div class="row">
        <div class="col-md-8 offset-md-2 mb-4">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-hand-paper"></i> Registrar Presença
                    </h5>
                </div>
                <div class="card-body p-5 text-center">
                    <!-- Hora Atual -->
                    <div class="mb-4">
                        <h1 id="hora-atual" style="font-size: 3.5rem; font-weight: bold; color: #2c3e50;">
                            <span id="hora">00</span>:<span id="minuto">00</span>:<span id="segundo">00</span>
                        </h1>
                        <p class="text-muted" id="data-atual">
                            <span id="dia-semana"></span>, <span id="data"></span>
                        </p>
                    </div>

                    <!-- Tipo de Batida -->
                    <div class="mb-5">
                        <div class="btn-group" role="group">
                            <button class="btn btn-lg btn-outline-success" onclick="definirTipo('entrada')" id="btn-entrada">
                                <i class="fas fa-sign-in-alt"></i>
                                <br><strong>ENTRADA</strong>
                            </button>
                            <button class="btn btn-lg btn-outline-danger" onclick="definirTipo('saida')" id="btn-saida">
                                <i class="fas fa-sign-out-alt"></i>
                                <br><strong>SAÍDA</strong>
                            </button>
                        </div>
                    </div>

                    <!-- Opções -->
                    <div class="custom-control custom-checkbox mb-4">
                        <input type="checkbox" class="custom-control-input" id="capturar-foto" checked>
                        <label class="custom-control-label" for="capturar-foto">
                            <i class="fas fa-camera"></i> Capturar Foto
                        </label>
                    </div>

                    <!-- Botão Confirmar -->
                    <button class="btn btn-primary btn-lg btn-block mb-3"
                            id="btn-confirmar"
                            onclick="confirmarBatida()"
                            disabled>
                        <i class="fas fa-check-circle"></i>
                        <span id="texto-botao">Selecione Entrada ou Saída</span>
                    </button>

                    <!-- Info Geolocalização -->
                    <div id="geo-info" class="alert alert-info alert-sm" style="display:none;">
                        <small>
                            <i class="fas fa-map-marker-alt"></i>
                            Localização: <span id="geo-coords"></span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cards de Status -->
    <div class="row mt-5">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h5 id="tempo-trabalhado" class="text-primary">0h 00m</h5>
                    <small class="text-muted">Tempo de Hoje</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h5 id="saldo-mes" class="text-success">+2h 30m</h5>
                    <small class="text-muted">Saldo do Mês</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h5 data-pendentes-offline class="text-warning" style="display:none;">0</h5>
                    <h5 class="text-warning" style="display:none;">Pendentes Offline</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h5 data-sync-status class="text-info"></h5>
                    <small class="text-muted">Sincronização</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Câmera -->
<div class="modal fade" id="modalCamera" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Capturar Foto</h5>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body text-center">
                <video id="video-camera" width="100%" style="max-height: 400px; background: #000;"></video>
                <div class="mt-3">
                    <button class="btn btn-primary" onclick="capturarFoto()">
                        <i class="fas fa-camera"></i> Capturar
                    </button>
                    <button class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Histórico -->
<div class="modal fade" id="modalHistorico" tabindex="-1" style="max-width: 90%;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Histórico de Batidas</h5>
                <button type="button" class="close" data-dismiss="modal">×</button>
            </div>
            <div class="modal-body">
                <div id="conteudo-historico" class="table-responsive">
                    <p class="text-muted">Carregando...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- scripts -->
<script src="assets/js/indexeddb.js"></script>
<script src="assets/js/ponto-offline.js" data-usuario-id="<?php echo $usuario_id; ?>"></script>

<script>
let tipo_selecionado = null;

// Atualizar relógio
function atualizarRelogio() {
    const agora = new Date();
    
    document.getElementById('hora').textContent = String(agora.getHours()).padStart(2, '0');
    document.getElementById('minuto').textContent = String(agora.getMinutes()).padStart(2, '0');
    document.getElementById('segundo').textContent = String(agora.getSeconds()).padStart(2, '0');

    // Data
    const dias = ['domingo', 'segunda', 'terça', 'quarta', 'quinta', 'sexta', 'sábado'];
    document.getElementById('dia-semana').textContent = dias[agora.getDay()];
    document.getElementById('data').textContent = agora.toLocaleDateString('pt-BR');
}

// Definir tipo de batida
function definirTipo(tipo) {
    tipo_selecionado = tipo;
    
    // Atualizar UI
    document.getElementById('btn-entrada').classList.remove('active');
    document.getElementById('btn-saida').classList.remove('active');
    
    if (tipo === 'entrada') {
        document.getElementById('btn-entrada').classList.add('active', 'btn-success');
        document.getElementById('btn-entrada').classList.remove('btn-outline-success');
        document.getElementById('texto-botao').textContent = 'Confirmar ENTRADA';
    } else {
        document.getElementById('btn-saida').classList.add('active', 'btn-danger');
        document.getElementById('btn-saida').classList.remove('btn-outline-danger');
        document.getElementById('texto-botao').textContent = 'Confirmar SAÍDA';
    }
    
    document.getElementById('btn-confirmar').disabled = false;
}

// Confirmar batida
async function confirmarBatida() {
    if (!tipo_selecionado) {
        alert('Selecione Entrada ou Saída');
        return;
    }

    try {
        document.getElementById('btn-confirmar').disabled = true;
        
        const capturarFoto = document.getElementById('capturar-foto').checked;
        const resultado = await pontoOffline.baterPonto(tipo_selecionado, capturarFoto);

        // Mostrar sucesso
        Swal.fire({
            icon: 'success',
            title: 'Ponto Registrado!',
            text: resultado.mensagem,
            timer: 2000
        });

        // Resetar
        tipo_selecionado = null;
        document.getElementById('btn-entrada').classList.remove('active', 'btn-success');
        document.getElementById('btn-entrada').classList.add('btn-outline-success');
        document.getElementById('btn-saida').classList.remove('active', 'btn-danger');
        document.getElementById('btn-saida').classList.add('btn-outline-danger');
        document.getElementById('btn-confirmar').disabled = true;
        document.getElementById('texto-botao').textContent = 'Selecione Entrada ou Saída';
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erro!',
            text: error.message
        });
    } finally {
        document.getElementById('btn-confirmar').disabled = false;
    }
}

// Abrir histórico
function abrirHistorico() {
    $('#modalHistorico').modal('show');
    carregarHistorico();
}

// Carregar histórico
async function carregarHistorico() {
    try {
        const response = await fetch('/index.php?rota=meu_ponto_json');
        const data = await response.json();

        let html = '<table class="table table-sm"><thead class="bg-light"><tr>' +
                   '<th>Data/Hora</th><th>Tipo</th><th>Localização</th><th>Status</th></tr></thead><tbody>';

        data.apontamentos.forEach(apt => {
            html += `<tr>
                <td>${apt.data} ${apt.hora}</td>
                <td><span class="badge ${apt.tipo === 'entrada' ? 'badge-success' : 'badge-danger'}">${apt.tipo}</span></td>
                <td><small>${apt.latitude}, ${apt.longitude}</small></td>
                <td><span class="badge badge-info">${apt.status}</span></td>
            </tr>`;
        });

        html += '</tbody></table>';
        document.getElementById('conteudo-historico').innerHTML = html;
    } catch (error) {
        document.getElementById('conteudo-historico').innerHTML = 
            '<div class="alert alert-danger">Erro ao carregar histórico</div>';
    }
}

// Abrir configurações
function abrirConfiguracao() {
    Swal.fire({
        title: 'Configurações',
        html: `
            <div class="text-left">
                <label><input type="checkbox" id="notif-ativa" checked> Notificações ativas</label><br>
                <label><input type="checkbox" id="geo-ativa" checked> Geolocalização automática</label><br>
                <label><input type="checkbox" id="foto-auto" checked> Foto automática</label>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Salvar'
    });
}

// Capturar foto
function capturarFoto() {
    // Implementado em ponto-offline.js
    Swal.fire('Foto capturada!', '', 'success');
    $('#modalCamera').modal('hide');
}

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
    atualizarRelogio();
    setInterval(atualizarRelogio, 1000);

    // Indicador online/offline
    window.addEventListener('online', () => {
        document.getElementById('status-indicador').className = 'badge badge-success p-2';
        document.getElementById('status-indicador').innerHTML = '<i class="fas fa-wifi"></i> Online';
    });

    window.addEventListener('offline', () => {
        document.getElementById('status-indicador').className = 'badge badge-danger p-2';
        document.getElementById('status-indicador').innerHTML = '<i class="fas fa-wifi-slash"></i> Offline';
    });
});
</script>

<style>
#hora-atual {
    font-family: 'Courier New', monospace;
    letter-spacing: 5px;
}

.btn-group .btn.active {
    box-shadow: 0 0 20px rgba(0,0,0,0.3);
}

#status-indicador {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
</style>
