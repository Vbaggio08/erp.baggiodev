/**
 * IndexedDB Manager - FASE 4
 * Path: assets/js/indexeddb.js
 * 
 * Gerencia armazenamento local offline:
 * - Batidas de ponto pendentes
 * - Fotos em blob
 * - Dados de geolocalização
 * - Configurações do app
 * 
 * API simples:
 * - IndexedDBManager.init()
 * - IndexedDBManager.salvarBatida(dados)
 * - IndexedDBManager.obterPendentes()
 * - IndexedDBManager.limparSincronizadas()
 */

class IndexedDBManager {
  constructor() {
    this.dbName = 'RipfireDB';
    this.dbVersion = 1;
    this.db = null;
    this.stores = {
      batidas: 'batidas',
      fotos: 'fotos',
      config: 'config',
      erros: 'erros'
    };
  }

  /**
   * Inicializar banco IndexedDB
   */
  async init() {
    return new Promise((resolve, reject) => {
      const request = indexedDB.open(this.dbName, this.dbVersion);

      request.onerror = () => {
        console.error('Erro ao abrir IndexedDB:', request.error);
        reject(request.error);
      };

      request.onsuccess = () => {
        this.db = request.result;
        console.log('[IndexedDB] Banco inicializado');
        resolve();
      };

      request.onupgradeneeded = (event) => {
        const db = event.target.result;
        
        // Object Store: Batidas de Ponto
        if (!db.objectStoreNames.contains(this.stores.batidas)) {
          const batidaStore = db.createObjectStore(this.stores.batidas, {
            keyPath: 'id',
            autoIncrement: true
          });
          batidaStore.createIndex('timestamp', 'timestamp', { unique: false });
          batidaStore.createIndex('usuario_id', 'usuario_id', { unique: false });
          batidaStore.createIndex('status', 'status', { unique: false });
          console.log('[IndexedDB] Object Store "batidas" criada');
        }

        // Object Store: Fotos
        if (!db.objectStoreNames.contains(this.stores.fotos)) {
          const fotoStore = db.createObjectStore(this.stores.fotos, {
            keyPath: 'id',
            autoIncrement: true
          });
          fotoStore.createIndex('batida_id', 'batida_id', { unique: false });
          fotoStore.createIndex('timestamp', 'timestamp', { unique: false });
          console.log('[IndexedDB] Object Store "fotos" criada');
        }

        // Object Store: Configuração
        if (!db.objectStoreNames.contains(this.stores.config)) {
          db.createObjectStore(this.stores.config, { keyPath: 'chave' });
          console.log('[IndexedDB] Object Store "config" criada');
        }

        // Object Store: Erros de Sincronização
        if (!db.objectStoreNames.contains(this.stores.erros)) {
          const erroStore = db.createObjectStore(this.stores.erros, {
            keyPath: 'id',
            autoIncrement: true
          });
          erroStore.createIndex('timestamp', 'timestamp', { unique: false });
          console.log('[IndexedDB] Object Store "erros" criada');
        }
      };
    });
  }

  /**
   * Salvar batida de ponto offline
   * 
   * @param {Object} dados - {usuario_id, tipo, numero_batida, lat, lng, precisao, timestamp}
   * @returns {Promise<number>} ID da batida
   */
  async salvarBatida(dados) {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([this.stores.batidas], 'readwrite');
      const store = transaction.objectStore(this.stores.batidas);

      const batida = {
        usuario_id: dados.usuario_id,
        tipo: dados.tipo, // 'entrada' ou 'saida'
        numero_batida: dados.numero_batida,
        timestamp: new Date().toISOString(),
        latitude: dados.latitude,
        longitude: dados.longitude,
        precisao: dados.precisao,
        status: 'pendente', // pendente, sincronizado, erro
        sincronizacao_tentativas: 0
      };

      const request = store.add(batida);

      request.onsuccess = () => {
        console.log('[IndexedDB] Batida salva com ID:', request.result);
        resolve(request.result);
      };

      request.onerror = () => {
        console.error('Erro ao salvar batida:', request.error);
        reject(request.error);
      };
    });
  }

  /**
   * Salvar foto de batida
   * 
   * @param {Object} dados - {batida_id, blob, tipo}
   */
  async salvarFoto(batida_id, blob, tipo = 'jpeg') {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([this.stores.fotos], 'readwrite');
      const store = transaction.objectStore(this.stores.fotos);

      const foto = {
        batida_id: batida_id,
        blob: blob,
        tipo: tipo,
        tamanho: blob.size,
        timestamp: new Date().toISOString()
      };

      const request = store.add(foto);

      request.onsuccess = () => {
        console.log('[IndexedDB] Foto salva com ID:', request.result);
        resolve(request.result);
      };

      request.onerror = () => {
        reject(request.error);
      };
    });
  }

  /**
   * Obter batidas pendentes de sincronização
   * 
   * @returns {Promise<Array>} Array de batidas
   */
  async obterPendentes() {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([this.stores.batidas], 'readonly');
      const store = transaction.objectStore(this.stores.batidas);
      const index = store.index('status');
      const request = index.getAll('pendente');

      request.onsuccess = () => {
        console.log('[IndexedDB] Batidas pendentes:', request.result.length);
        resolve(request.result);
      };

      request.onerror = () => {
        reject(request.error);
      };
    });
  }

  /**
   * Marcar batida como sincronizada
   * 
   * @param {number} batida_id
   */
  async marcarSincronizada(batida_id) {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([this.stores.batidas], 'readwrite');
      const store = transaction.objectStore(this.stores.batidas);
      const request = store.get(batida_id);

      request.onsuccess = () => {
        const batida = request.result;
        if (batida) {
          batida.status = 'sincronizado';
          batida.timestamp_sync = new Date().toISOString();
          
          const updateRequest = store.put(batida);
          updateRequest.onsuccess = () => {
            console.log('[IndexedDB] Batida marcada como sincronizada:', batida_id);
            resolve();
          };
          updateRequest.onerror = () => reject(updateRequest.error);
        } else {
          reject(new Error('Batida não encontrada'));
        }
      };

      request.onerror = () => reject(request.error);
    });
  }

  /**
   * Obter foto de batida
   * 
   * @param {number} batida_id
   * @returns {Promise<Blob|null>}
   */
  async obterFoto(batida_id) {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([this.stores.fotos], 'readonly');
      const store = transaction.objectStore(this.stores.fotos);
      const index = store.index('batida_id');
      const request = index.get(batida_id);

      request.onsuccess = () => {
        const foto = request.result;
        resolve(foto ? foto.blob : null);
      };

      request.onerror = () => reject(request.error);
    });
  }

  /**
   * Contar batidas pendentes
   * 
   * @returns {Promise<number>}
   */
  async contarPendentes() {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([this.stores.batidas], 'readonly');
      const store = transaction.objectStore(this.stores.batidas);
      const index = store.index('status');
      const request = index.count('pendente');

      request.onsuccess = () => {
        resolve(request.result);
      };

      request.onerror = () => reject(request.error);
    });
  }

  /**
   * Limpar batidas sincronizadas (mais antigas que N dias)
   * 
   * @param {number} dias
   */
  async limparAntigos(dias = 30) {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([this.stores.batidas], 'readwrite');
      const store = transaction.objectStore(this.stores.batidas);
      
      // Obtém todas as batidas sincronizadas
      const index = store.index('status');
      const request = index.getAll('sincronizado');

      request.onsuccess = () => {
        const batidas = request.result;
        const dateLimite = new Date();
        dateLimite.setDate(dateLimite.getDate() - dias);

        let deletados = 0;
        batidas.forEach(batida => {
          if (new Date(batida.timestamp_sync) < dateLimite) {
            store.delete(batida.id);
            deletados++;
          }
        });

        console.log('[IndexedDB] Deletados', deletados, 'registros antigos');
        resolve(deletados);
      };

      request.onerror = () => reject(request.error);
    });
  }

  /**
   * Registrar erro de sincronização
   * 
   * @param {number} batida_id
   * @param {string} mensagem
   */
  async registrarErro(batida_id, mensagem) {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction([this.stores.erros], 'readwrite');
      const store = transaction.objectStore(this.stores.erros);

      const erro = {
        batida_id: batida_id,
        mensagem: mensagem,
        timestamp: new Date().toISOString()
      };

      const request = store.add(erro);

      request.onsuccess = () => {
        console.log('[IndexedDB] Erro registrado');
        resolve();
      };

      request.onerror = () => reject(request.error);
    });
  }

  /**
   * Obter estatísticas do banco
   */
  async obterEstatisticas() {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(Object.values(this.stores), 'readonly');
      
      const stats = {};

      Object.values(this.stores).forEach(storeName => {
        const store = transaction.objectStore(storeName);
        const request = store.count();

        request.onsuccess = () => {
          stats[storeName] = request.result;
        };
      });

      transaction.oncomplete = () => {
        console.log('[IndexedDB] Estatísticas:', stats);
        resolve(stats);
      };

      transaction.onerror = () => reject(transaction.error);
    });
  }

  /**
   * Limpar todo o banco IndexedDB
   */
  async limparTudo() {
    if (!this.db) await this.init();

    return new Promise((resolve, reject) => {
      const transaction = this.db.transaction(Object.values(this.stores), 'readwrite');

      Object.values(this.stores).forEach(storeName => {
        transaction.objectStore(storeName).clear();
      });

      transaction.oncomplete = () => {
        console.log('[IndexedDB] Banco completamente limpo');
        resolve();
      };

      transaction.onerror = () => reject(transaction.error);
    });
  }
}

// Instância global
const indexedDBManager = new IndexedDBManager();

// Auto-inicializar quando disponível
if (typeof IndexedDB !== 'undefined' || typeof indexedDB !== 'undefined') {
  document.addEventListener('DOMContentLoaded', async () => {
    try {
      await indexedDBManager.init();
      console.log('[IndexedDB] Sistema pronto para uso');
    } catch (error) {
      console.error('[IndexedDB] Erro na inicialização:', error);
    }
  });
}
