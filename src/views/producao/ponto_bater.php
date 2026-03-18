<?php
// Dados disponíveis:
// $apontamento - apontamento de hoje (ou null)
// $config - configuração global
// $horario_usuario - horário individual do funcionário
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card" style="border:1px solid #333;">
                <div class="card-body p-4 p-md-5 text-center">
                    
                    <!-- Cabeçalho -->
                    <h1 class="mb-1" style="color:var(--primary-color);">⏱️ Bater Ponto</h1>
                    <p class="text-muted mb-4">Registre sua entrada/saída</p>

                    <!-- Horário de trabalho do funcionário -->
                    <?php if (!empty($horario_usuario)): ?>
                    <div class="p-2 rounded mb-3" style="font-size:13px; background:#252525; border:1px solid #444;">
                        <strong style="color:var(--primary-color);">📅 Seu horário:</strong>
                        <?= substr($horario_usuario['horario_entrada_1'], 0, 5) ?>–<?= substr($horario_usuario['horario_saida_1'], 0, 5) ?>
                        /
                        <?= substr($horario_usuario['horario_entrada_2'], 0, 5) ?>–<?= substr($horario_usuario['horario_saida_2'], 0, 5) ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Status Online/Offline -->
                    <div class="alert alert-info mb-4" id="status-box">
                        <span id="status-icon">🟢</span>
                        <span id="status-text"> ONLINE</span>
                    </div>
                    
                    <!-- Informações de hoje -->
                    <?php if ($apontamento): ?>
                        <div class="p-3 rounded mb-4" style="background:#252525; border:1px solid #444;">
                            <h5 style="color:var(--primary-color);">Hoje (<?php echo date('d/m/Y'); ?>)</h5>
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted">Entrada 1</small>
                                    <p class="fw-bold mb-0"><?php echo $apontamento['hora_entrada_1'] ?? '---'; ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Saída 1</small>
                                    <p class="fw-bold mb-0"><?php echo $apontamento['hora_saida_1'] ?? '---'; ?></p>
                                </div>
                            </div>
                            
                            <hr style="border-color:#444; margin:10px 0;">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted">Entrada 2</small>
                                    <p class="fw-bold mb-0"><?php echo $apontamento['hora_entrada_2'] ?? '---'; ?></p>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Saída 2</small>
                                    <p class="fw-bold mb-0"><?php echo $apontamento['hora_saida_2'] ?? '---'; ?></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">
                            Nenhum ponto registrado hoje
                        </div>
                    <?php endif; ?>
                    
                    <!-- Botão Principal -->
                    <button id="btn-bater-ponto" class="btn btn-success btn-lg w-100 mb-3" style="font-size: 22px; padding: 18px; border-radius:10px; font-weight:bold;">
                        ✅ BATER PONTO
                    </button>
                    
                    <!-- Indicador de Sincronização -->
                    <div id="sync-indicator" class="text-muted small d-none">
                        <span class="spinner-border spinner-border-sm"></span> <span id="sync-text">Sincronizando...</span>
                    </div>

                    <!-- Preview da Câmera -->
                    <div id="camera-container" class="mt-3 d-none">
                        <video id="video-preview" class="rounded" width="320" height="240" autoplay playsinline muted style="border:1px solid #444;"></video>
                        <p class="text-muted small mt-1">Câmera ativa para registro de ponto</p>
                    </div>
                    
                </div>
            </div>
            
            <!-- Links rápidos -->
            <div class="mt-4 text-center d-flex justify-content-center gap-2 flex-wrap">
                <a href="index.php?rota=meu_ponto" class="btn btn-outline-primary btn-sm">📋 Meu Ponto</a>
                <a href="index.php?rota=saldo_horas" class="btn btn-outline-info btn-sm">⏳ Saldo de Horas</a>
                <a href="index.php?rota=solicitar_alteracao_ponto" class="btn btn-outline-warning btn-sm">✏️ Solicitar Alteração</a>
            </div>

            <!-- Tabela de Últimos Pontos (aparece após bater) -->
            <div id="tabela-pontos" class="mt-4 d-none">
                <h5 class="text-center mb-3" style="color:var(--primary-color);">📋 Últimos Registros</h5>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered text-center" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Entrada 1</th>
                                <th>Saída 1</th>
                                <th>Entrada 2</th>
                                <th>Saída 2</th>
                                <th>Horas</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-pontos"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<span class="d-none" data-usuario-id="<?php echo (int)($_SESSION['user_id'] ?? 0); ?>"></span>

<script src="assets/js/indexeddb.js"></script>
<script src="assets/js/ponto-offline.js"></script>

<!-- Modal: Batida Próxima (Validação) -->
<div class="modal fade" id="modal-validacao" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:rgba(230,184,0,0.15); border-bottom:1px solid #444;">
                <h5 class="modal-title" style="color:var(--primary-color);">⚠️ Batida Muito Próxima</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>A última batida foi registrada há <strong id="minutos-decorridos"></strong> minutos.</p>
                <p class="text-muted">Última batida: <strong id="ultima-batida"></strong></p>
                <p>O que deseja fazer?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">❌ Cancelar</button>
                <button type="button" class="btn btn-success" id="btn-confirmar-saida">✅ Confirmar Saída</button>
            </div>
        </div>
    </div>
</div>

<script>
// ===== Estado do ponto =====
const apontamento = <?php echo json_encode($apontamento ?: null); ?>;
const config = <?php echo json_encode($config ?: ['quantidade_batidas' => 2, 'usar_geolocalizacao' => 0]); ?>;
const baseUrl = '<?php echo $base_url ?? ''; ?>';

let geoData = { lat: null, lng: null, precisao: null };
let fotoBase64 = null;
let deviceId = null;
let streamVideo = null;

// ===== Determinar próxima batida =====
function determinarProximaBatida() {
    if (!apontamento) return { tipo: 'entrada', numero: 1 };
    
    for (let i = 1; i <= 3; i++) {
        const ent = apontamento['hora_entrada_' + i];
        const sai = apontamento['hora_saida_' + i];
        if (!ent) return { tipo: 'entrada', numero: i };
        if (!sai) return { tipo: 'saida', numero: i };
    }
    return { tipo: 'completo', numero: 0 }; // Todas as batidas feitas
}

function atualizarBotao() {
    const prox = determinarProximaBatida();
    const btn = document.getElementById('btn-bater-ponto');
    if (prox.tipo === 'completo') {
        btn.textContent = '✅ Todas as batidas registradas';
        btn.disabled = true;
        btn.classList.replace('btn-success', 'btn-secondary');
    } else if (prox.tipo === 'entrada') {
        btn.textContent = '🟢 Registrar Entrada ' + prox.numero;
        btn.classList.replace('btn-danger', 'btn-success');
    } else {
        btn.textContent = '🔴 Registrar Saída ' + prox.numero;
        btn.classList.replace('btn-success', 'btn-danger');
    }
}

// ===== Device Fingerprint =====
function gerarDeviceId() {
    function obterSeedDispositivo() {
        const chave = 'erp_device_seed_v1';
        let seed = localStorage.getItem(chave);
        if (!seed) {
            if (window.crypto && typeof window.crypto.randomUUID === 'function') {
                seed = window.crypto.randomUUID();
            } else {
                seed = 'seed-' + Date.now() + '-' + Math.random().toString(36).slice(2, 10);
            }
            localStorage.setItem(chave, seed);
        }
        return seed;
    }

    function hashTexto(texto) {
        let hash = 2166136261;
        for (let i = 0; i < texto.length; i++) {
            hash ^= texto.charCodeAt(i);
            hash += (hash << 1) + (hash << 4) + (hash << 7) + (hash << 8) + (hash << 24);
        }
        return (hash >>> 0).toString(16).padStart(8, '0');
    }

    try {
        const seed = obterSeedDispositivo();
        const screenInfo = (window.screen ? (window.screen.width + 'x' + window.screen.height) : 'sem-tela');
        const bruto = [
            seed,
            navigator.userAgent || '',
            navigator.platform || '',
            navigator.language || '',
            String(navigator.hardwareConcurrency || ''),
            screenInfo
        ].join('|');

        deviceId = 'dev-' + hashTexto(bruto) + '-' + seed.slice(0, 8);
    } catch (e) {
        deviceId = 'fallback-' + navigator.userAgent.slice(0, 20);
    }
}

// ===== Geolocalização =====
function obterGeolocalizacao() {
    return new Promise((resolve) => {
        if (!navigator.geolocation) { resolve(false); return; }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                geoData.lat = pos.coords.latitude;
                geoData.lng = pos.coords.longitude;
                geoData.precisao = Math.round(pos.coords.accuracy);
                resolve(true);
            },
            () => resolve(false),
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    });
}

// ===== Câmera =====
function iniciarCamera() {
    return new Promise((resolve) => {
        const videoEl = document.getElementById('video-preview');
        if (!videoEl || !navigator.mediaDevices) { resolve(false); return; }
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 320, height: 240 } })
            .then(stream => {
                streamVideo = stream;
                videoEl.srcObject = stream;
                videoEl.play();
                document.getElementById('camera-container').classList.remove('d-none');
                resolve(true);
            })
            .catch(() => resolve(false));
    });
}

function capturarFoto() {
    const videoEl = document.getElementById('video-preview');
    if (!videoEl || !videoEl.srcObject) return null;
    const canvas = document.createElement('canvas');
    canvas.width = 320;
    canvas.height = 240;
    canvas.getContext('2d').drawImage(videoEl, 0, 0, 320, 240);
    return canvas.toDataURL('image/jpeg', 0.6);
}

function pararCamera() {
    if (streamVideo) {
        streamVideo.getTracks().forEach(t => t.stop());
        streamVideo = null;
    }
    const container = document.getElementById('camera-container');
    if (container) container.classList.add('d-none');
}

function base64ParaBlob(base64Data) {
    if (!base64Data || typeof base64Data !== 'string' || base64Data.indexOf(',') === -1) {
        return null;
    }

    try {
        const partes = base64Data.split(',');
        const mime = (partes[0].match(/:(.*?);/) || [])[1] || 'image/jpeg';
        const binario = atob(partes[1]);
        const len = binario.length;
        const bytes = new Uint8Array(len);
        for (let i = 0; i < len; i++) {
            bytes[i] = binario.charCodeAt(i);
        }
        return new Blob([bytes], { type: mime });
    } catch (e) {
        return null;
    }
}

// ===== Bater Ponto =====
async function baterPonto(forcarConfirmacao = false) {
    const btn = document.getElementById('btn-bater-ponto');
    const prox = determinarProximaBatida();
    
    if (prox.tipo === 'completo') return;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';
    document.getElementById('sync-indicator').classList.remove('d-none');
    
    try {
        // Capturar geo se configurado
        if (config.usar_geolocalizacao) {
            document.getElementById('sync-text').textContent = 'Obtendo localização...';
            await obterGeolocalizacao();
        }
        
        // Capturar foto se câmera ativa
        fotoBase64 = capturarFoto();
        
        document.getElementById('sync-text').textContent = 'Registrando batida...';
        
        const formData = new FormData();
        formData.append('tipo', prox.tipo);
        formData.append('numero_batida', prox.numero);
        if (geoData.lat) formData.append('geo_lat', geoData.lat);
        if (geoData.lng) formData.append('geo_lng', geoData.lng);
        if (geoData.precisao) formData.append('geo_precisao', geoData.precisao);
        if (fotoBase64) formData.append('foto', fotoBase64);
        if (deviceId) formData.append('device_id', deviceId);
        if (forcarConfirmacao) formData.append('forcar', '1');
        
        const response = await fetch(baseUrl + 'index.php?rota=bater_ponto_ajax', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.status === 'validacao_requerida') {
            // Batida muito próxima — mostrar modal
            document.getElementById('minutos-decorridos').textContent = Math.round(data.minutos_decorridos);
            document.getElementById('ultima-batida').textContent = data.ultima_batida;
            const modal = new bootstrap.Modal(document.getElementById('modal-validacao'));
            modal.show();
            resetarBotao();
            return;
        }
        
        if (data.status === 'sucesso') {
            pararCamera();
            mostrarSucesso('✅ Ponto batido com sucesso! ' + (data.mensagem || ''));
            // Atualizar dados locais
            if (apontamento) {
                apontamento['hora_' + prox.tipo + '_' + prox.numero] = data.timestamp ? data.timestamp.split(' ')[1] : new Date().toTimeString().slice(0, 8);
            }
            document.getElementById('sync-indicator').classList.add('d-none');
            atualizarBotao();
            document.getElementById('btn-bater-ponto').disabled = false;
            carregarUltimosPontos();
        } else {
            mostrarErro(data.mensagem || 'Erro ao registrar batida');
            resetarBotao();
        }
    } catch (err) {
        if (!navigator.onLine && typeof pontoOffline !== 'undefined') {
            try {
                const fotoBlob = base64ParaBlob(fotoBase64);
                await pontoOffline.salvarPontoOffline({
                    usuario_id: <?php echo (int)($_SESSION['user_id'] ?? 0); ?>,
                    tipo: prox.tipo,
                    numero_batida: prox.numero,
                    latitude: geoData.lat,
                    longitude: geoData.lng,
                    precisao: geoData.precisao,
                    foto: fotoBlob,
                    device_id: deviceId
                });

                mostrarSucesso('Sem internet: ponto salvo offline e será sincronizado automaticamente.');
                document.getElementById('sync-indicator').classList.add('d-none');
                atualizarBotao();
                document.getElementById('btn-bater-ponto').disabled = false;
                return;
            } catch (offlineErr) {
                // Cai no erro padrão abaixo
            }
        }

        mostrarErro('Erro de conexão. Verifique sua internet.');
        resetarBotao();
    }
}

// ===== Confirmar saída (modal de batida próxima) =====
document.getElementById('btn-confirmar-saida')?.addEventListener('click', async function() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('modal-validacao'));
    modal.hide();
    
    const formData = new FormData();
    formData.append('tipo_acao', 'confirmar_saida');
    if (geoData.lat) formData.append('geo_lat', geoData.lat);
    if (geoData.lng) formData.append('geo_lng', geoData.lng);
    if (geoData.precisao) formData.append('geo_precisao', geoData.precisao);
    if (fotoBase64) formData.append('foto', fotoBase64);
    if (deviceId) formData.append('device_id', deviceId);
    
    try {
        const response = await fetch(baseUrl + 'index.php?rota=confirmar_alteracao', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        if (data.status === 'saida_registrada') {
            mostrarSucesso('✅ Ponto batido com sucesso! ' + (data.mensagem || ''));
            carregarUltimosPontos();
        } else {
            mostrarErro(data.mensagem || 'Erro ao confirmar');
        }
    } catch (e) {
        mostrarErro('Erro de conexão');
    }
});

// ===== UI Helpers =====
function mostrarSucesso(msg) {
    const box = document.getElementById('status-box');
    box.className = 'alert alert-success mb-4';
    box.innerHTML = '<span>✅</span> ' + msg;
}

function mostrarErro(msg) {
    const box = document.getElementById('status-box');
    box.className = 'alert alert-danger mb-4';
    box.innerHTML = '<span>❌</span> ' + msg;
}

function resetarBotao() {
    document.getElementById('sync-indicator').classList.add('d-none');
    atualizarBotao();
    document.getElementById('btn-bater-ponto').disabled = false;
}

// ===== Carregar Últimos Pontos =====
async function carregarUltimosPontos() {
    try {
        const resp = await fetch(baseUrl + 'index.php?rota=meu_ponto_json');
        const pontos = await resp.json();
        const tbody = document.getElementById('tbody-pontos');
        const container = document.getElementById('tabela-pontos');
        if (!pontos || !pontos.length) return;

        const ultimos = pontos.slice(0, 5);
        tbody.innerHTML = '';
        ultimos.forEach(p => {
            const tr = document.createElement('tr');
            // Destacar a linha de hoje
            const hoje = new Date().toLocaleDateString('pt-BR');
            if (p.data === hoje) tr.classList.add('table-success');
            tr.innerHTML = `
                <td><strong>${p.data || '-'}</strong></td>
                <td>${(p.hora_entrada_1 || '-').substring(0, 5)}</td>
                <td>${(p.hora_saida_1 || '-').substring(0, 5)}</td>
                <td>${(p.hora_entrada_2 || '-').substring(0, 5)}</td>
                <td>${(p.hora_saida_2 || '-').substring(0, 5)}</td>
                <td>${p.total_horas ? Number(p.total_horas).toFixed(2).replace('.', ',') + 'h' : '-'}</td>
            `;
            tbody.appendChild(tr);
        });
        container.classList.remove('d-none');
    } catch(e) {
        // silencioso — tabela é complementar
    }
}

// ===== Inicialização =====
document.addEventListener('DOMContentLoaded', function() {
    gerarDeviceId();
    atualizarBotao();
    iniciarCamera();
    
    // Status online/offline
    function atualizarStatusRede() {
        const icon = document.getElementById('status-icon');
        const text = document.getElementById('status-text');
        if (navigator.onLine) {
            icon.textContent = '🟢';
            text.textContent = ' ONLINE';
            document.getElementById('status-box').className = 'alert alert-info mb-4';
        } else {
            icon.textContent = '🔴';
            text.textContent = ' OFFLINE';
            document.getElementById('status-box').className = 'alert alert-danger mb-4';
        }
    }
    window.addEventListener('online', atualizarStatusRede);
    window.addEventListener('offline', atualizarStatusRede);
    atualizarStatusRede();
    
    document.getElementById('btn-bater-ponto').addEventListener('click', () => baterPonto());
});
</script>
