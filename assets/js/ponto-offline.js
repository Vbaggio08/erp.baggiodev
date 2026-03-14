/**
 * Ponto Offline Manager - FASE 4
 * Path: assets/js/ponto-offline.js
 * 
 * Gerencia:
 * - Batidas offline com sync automático
 * - Geolocalização (latitude/longitude)
 * - Captura de foto pela câmera
 * - Status online/offline
 * - Sincronização inteligente
 */

class PontoOfflineManager {
    constructor() {
        this.online = navigator.onLine;
        this.usuario_id = null;
        this.numero_batida = 1;
        this.swRegistration = null;

        this.init();
    }

    /**
     * Inicializar manager
     */
    async init() {
        console.log('[Ponto] Inicializando Offline Manager');

        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            try {
                this.swRegistration = await navigator.serviceWorker.register('sw.js', {
                    scope: '/'
                });
                console.log('[Ponto] Service Worker registrado');

                // Listeners de mensagem do SW
                navigator.serviceWorker.addEventListener('message', (event) => {
                    this.handleSWMessage(event.data);
                });
            } catch (error) {
                console.error('[Ponto] Erro ao registrar SW:', error);
            }
        }

        // Ouvir eventos de conexão
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());

        // Inicializar IndexedDB
        await indexedDBManager.init();

        // Obter dados da sessão
        this.usuario_id = document.querySelector('[data-usuario-id]')?.dataset.usuarioId;

        console.log('[Ponto] Manager pronto');
    }

    /**
     * Bater ponto com captura opcional de foto/geo
     * 
     * @param {string} tipo 'entrada' ou 'saida'
     * @param {boolean} capturarFoto Se deve capturar foto
     */
    async baterPonto(tipo, capturarFoto = true) {
        try {
            console.log(`[Ponto] Iniciando ${tipo}...`);

            // 1. Obter geolocalização
            const geo = await this.obterGeolocalizacao();

            // 2. Capturar foto se solicitado
            let foto = null;
            if (capturarFoto) {
                foto = await this.capturarFoto();
            }

            // 3. Preparar dados da batida
            const dados = {
                usuario_id: this.usuario_id,
                tipo: tipo,
                numero_batida: this.numero_batida,
                timestamp: new Date().toISOString(),
                latitude: geo.latitude,
                longitude: geo.longitude,
                precisao: geo.precisao,
                foto: foto
            };

            // 4. Tentar enviar para servidor
            if (this.online) {
                return await this.enviarPontoServidor(dados);
            } else {
                // 5. Se offline, salvar no IndexedDB
                return await this.salvarPontoOffline(dados);
            }
        } catch (error) {
            console.error('[Ponto] Erro ao bater ponto:', error);
            throw error;
        }
    }

    /**
     * Obter geolocalização do dispositivo
     * 
     * @returns {Promise<Object>} {latitude, longitude, precisao}
     */
    async obterGeollocalizacao() {
        return new Promise((resolve, reject) => {
            if (!navigator.geolocation) {
                console.warn('[Ponto] Geolocation não disponível');
                resolve({ latitude: null, longitude: null, precisao: null });
                return;
            }

            const opcoes = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.getCurrentPosition(
                (posicao) => {
                    const { latitude, longitude, accuracy } = posicao.coords;
                    console.log('[Ponto] Geolocalização obtida:', latitude, longitude);

                    resolve({
                        latitude: latitude,
                        longitude: longitude,
                        precisao: accuracy
                    });
                },
                (erro) => {
                    console.warn('[Ponto] Erro de geolocalização:', erro);
                    resolve({ latitude: null, longitude: null, precisao: null });
                },
                opcoes
            );
        });
    }

    /**
     * Capturar foto pela câmera
     * 
     * @returns {Promise<Blob|null>}
     */
    async capturarFoto() {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'environment', // Câmera traseira
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                },
                audio: false
            });

            // Criar video element
            const video = document.createElement('video');
            video.srcObject = stream;
            video.play();

            // Aguardar carregamento
            await new Promise(resolve => {
                video.onloadedmetadata = resolve;
            });

            // Canvas para captura
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;

            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0);

            // Parar stream
            stream.getTracks().forEach(track => track.stop());

            // Converter para Blob
            return await new Promise(resolve => {
                canvas.toBlob(blob => {
                    console.log('[Ponto] Foto capturada:', blob.size, 'bytes');
                    resolve(blob);
                }, 'image/jpeg', 0.8);
            });
        } catch (error) {
            console.error('[Ponto] Erro ao capturar foto:', error);
            return null;
        }
    }

    /**
     * Enviar ponto para servidor
     * 
     * @param {Object} dados
     */
    async enviarPontoServidor(dados) {
        try {
            console.log('[Ponto] Enviando ponto para servidor');

            const formData = new FormData();
            formData.append('tipo', dados.tipo);
            formData.append('numero_batida', dados.numero_batida);
            formData.append('geo_lat', dados.latitude);
            formData.append('geo_lng', dados.longitude);
            formData.append('geo_precisao', dados.precisao);

            if (dados.foto) {
                formData.append('foto', dados.foto, 'ponto.jpg');
            }

            const response = await fetch('/index.php?rota=bater_ponto_ajax', {
                method: 'POST',
                body: formData
            });

            const resultado = await response.json();

            if (resultado.status === 'sucesso') {
                console.log('[Ponto] Ponto enviado com sucesso');
                this.numero_batida++;
                return resultado;
            } else {
                throw new Error(resultado.mensagem);
            }
        } catch (error) {
            console.error('[Ponto] Erro ao enviar ponto:', error);
            throw error;
        }
    }

    /**
     * Salvar ponto offline no IndexedDB
     * 
     * @param {Object} dados
     */
    async salvarPontoOffline(dados) {
        try {
            console.log('[Ponto] Salvando ponto offline');

            // Salvar batida
            const batida_id = await indexedDBManager.salvarBatida({
                usuario_id: dados.usuario_id,
                tipo: dados.tipo,
                numero_batida: dados.numero_batida,
                latitude: dados.latitude,
                longitude: dados.longitude,
                precisao: dados.precisao
            });

            // Salvar foto se houver
            if (dados.foto) {
                await indexedDBManager.salvarFoto(batida_id, dados.foto);
            }

            this.numero_batida++;

            // Atualizar UI
            this.atualizarStatusOffline();

            return {
                status: 'offline',
                mensagem: 'Ponto salvo offline. Sincronizará quando conectado.',
                batida_id: batida_id
            };
        } catch (error) {
            console.error('[Ponto] Erro ao salvar offline:', error);
            await indexedDBManager.registrarErro(null, error.message);
            throw error;
        }
    }

    /**
     * Sincronizar batidas pendentes
     */
    async sincronizar() {
        try {
            console.log('[Ponto] Iniciando sincronização');

            const pendentes = await indexedDBManager.obterPendentes();

            if (pendentes.length === 0) {
                console.log('[Ponto] Nenhuma batida pendente');
                return { sincronizadas: 0, erros: 0 };
            }

            console.log('[Ponto] Sincronizando', pendentes.length, 'batidas');

            let sincronizadas = 0;
            let erros = 0;

            for (const batida of pendentes) {
                try {
                    // Obter foto se houver
                    const foto = await indexedDBManager.obterFoto(batida.id);

                    // Enviar para servidor
                    const formData = new FormData();
                    formData.append('tipo', batida.tipo);
                    formData.append('numero_batida', batida.numero_batida);
                    formData.append('geo_lat', batida.latitude);
                    formData.append('geo_lng', batida.longitude);
                    formData.append('geo_precisao', batida.precisao);
                    formData.append('sync_offline', 'true');

                    if (foto) {
                        formData.append('foto', foto, 'ponto.jpg');
                    }

                    const response = await fetch('/index.php?rota=bater_ponto_ajax', {
                        method: 'POST',
                        body: formData
                    });

                    const resultado = await response.json();

                    if (resultado.status === 'sucesso') {
                        await indexedDBManager.marcarSincronizada(batida.id);
                        sincronizadas++;
                        console.log('[Ponto] Batida sincronizada:', batida.id);
                    } else {
                        erros++;
                        await indexedDBManager.registrarErro(batida.id, resultado.mensagem);
                    }
                } catch (error) {
                    erros++;
                    await indexedDBManager.registrarErro(batida.id, error.message);
                    console.error('[Ponto] Erro ao sincronizar batida:', error);
                }
            }

            // Limpar antigos
            await indexedDBManager.limparAntigos(30);

            // Atualizar UI
            this.atualizarStatusSincronizacao(sincronizadas, erros);

            console.log('[Ponto] Sincronização completa:',
                sincronizadas, 'ok,', erros, 'erros');

            return { sincronizadas, erros };
        } catch (error) {
            console.error('[Ponto] Erro geral na sincronização:', error);
            throw error;
        }
    }

    /**
     * Conectado à internet
     */
    handleOnline() {
        console.log('[Ponto] Online');
        this.online = true;
        this.mostrarMensagem('Conectado à internet', 'success');

        // Sincronizar batidas pendentes
        this.sincronizar().catch(err => {
            console.error('Erro ao sincronizar:', err);
        });
    }

    /**
     * Desconectado da internet
     */
    handleOffline() {
        console.log('[Ponto] Offline');
        this.online = false;
        this.mostrarMensagem('Sem conexão. Pontos serão salvos e sincronizados depois.', 'warning');
        this.atualizarStatusOffline();
    }

    /**
     * Mensagem do Service Worker
     */
    handleSWMessage(data) {
        console.log('[Ponto] Mensagem do SW:', data.type);

        switch (data.type) {
            case 'ONLINE_STATUS':
                if (data.status === 'online') {
                    this.handleOnline();
                } else {
                    this.handleOffline();
                }
                break;
            case 'SYNC_PONTO_RESULT':
                this.mostrarMensagem(
                    'Sincronização: ' + (data.status === 'sucesso' ? '✓' : '✗'),
                    data.status === 'sucesso' ? 'success' : 'danger'
                );
                break;
        }
    }

    /**
     * Atualizar UI com status offline
     */
    atualizarStatusOffline() {
        const contador = document.querySelector('[data-pendentes-offline]');
        if (contador) {
            indexedDBManager.contarPendentes().then(count => {
                contador.textContent = count;
                contador.style.display = count > 0 ? 'inline' : 'none';
            });
        }
    }

    /**
     * Atualizar status de sincronização
     */
    atualizarStatusSincronizacao(sincronizadas, erros) {
        const elemento = document.querySelector('[data-sync-status]');
        if (elemento) {
            if (erros === 0) {
                elemento.innerHTML = `<span class="badge badge-success">✓ ${sincronizadas} sincronizadas</span>`;
            } else {
                elemento.innerHTML = `<span class="badge badge-warning">${sincronizadas} ok, ${erros} erros</span>`;
            }
        }
    }

    /**
     * Mostrar mensagem ao usuário
     */
    mostrarMensagem(texto, tipo = 'info') {
        console.log('[Ponto] Mensagem:', tipo, texto);

        // Usando toastr se disponível
        if (typeof toastr !== 'undefined') {
            toastr[tipo](texto);
        }
        // Fallback simples
        else {
            const container = document.querySelector('[data-mensagens]');
            if (container) {
                const alerta = document.createElement('div');
                alerta.className = `alert alert-${tipo} alert-dismissible fade show`;
                alerta.innerHTML = `${texto} <button type="button" class="close" data-dismiss="alert">×</button>`;
                container.appendChild(alerta);

                setTimeout(() => {
                    alerta.remove();
                }, 5000);
            }
        }
    }

    /**
     * Obter estatísticas
     */
    async obterEstatisticas() {
        return await indexedDBManager.obterEstatisticas();
    }
}

// Instância global
const pontoOffline = new PontoOfflineManager();

// Sincronizar a cada 5 minutos se online
setInterval(() => {
    if (pontoOffline.online && document.hidden === false) {
        pontoOffline.sincronizar().catch(err => console.error(err));
    }
}, 5 * 60 * 1000);

// Export para uso em outros scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PontoOfflineManager;
}
