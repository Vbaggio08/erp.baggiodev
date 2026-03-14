/**
 * Service Worker - FASE 4: Sincronização Offline
 * Path: sw.js
 * 
 * Funcionalidades:
 * - Cache de assets para offline
 * - Fila de batidas de ponto offline
 * - Sincronização automática quando online
 * - Notificações de status
 */

const CACHE_NAME = 'ripfire-v4.0';
const ASSETS_CACHE = 'ripfire-assets-v4';
const API_CACHE = 'ripfire-api-v4';

// Assets críticos para offline
const CRITICAL_ASSETS = [
  '/',
  'index.php',
  'assets/estilo.css',
  'manifest.json',
  'js/ponto-offline.js',
  'js/indexeddb.js'
];

/**
 * Instalação do Service Worker
 * Faz cache dos assets críticos
 */
self.addEventListener('install', event => {
  console.log('[SW] Instalando Service Worker v4');
  event.waitUntil(
    caches.open(CACHE_NAME).then(cache => {
      console.log('[SW] Cacheando assets críticos');
      return cache.addAll(CRITICAL_ASSETS).catch(err => {
        console.warn('[SW] Alguns assets não foram cacheados:', err);
      });
    })
  );
  self.skipWaiting();
});

/**
 * Ativação do Service Worker
 * Limpa caches antigos
 */
self.addEventListener('activate', event => {
  console.log('[SW] Ativando Service Worker v4');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME && 
              cacheName !== ASSETS_CACHE && 
              cacheName !== API_CACHE) {
            console.log('[SW] Deletando cache antigo:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

/**
 * Fetch Handler
 * Estratégia: Network First com fallback para Cache
 */
self.addEventListener('fetch', event => {
  const { request } = event;
  const url = new URL(request.url);

  // Ignora requisições não-GET
  if (request.method !== 'GET') {
    event.respondWith(fetch(request).catch(() => {
      return new Response('Offline - POST não suportado', { status: 503 });
    }));
    return;
  }

  // Estratégia: Network First para APIs
  if (request.url.includes('/ponto/') || request.url.includes('/api/')) {
    event.respondWith(networkFirst(request));
  }
  // Estratégia: Cache First para assets
  else if (request.url.includes('/assets/') || 
           request.url.includes('/js/') || 
           request.url.includes('/css/')) {
    event.respondWith(cacheFirst(request));
  }
  // Fallback: Network First
  else {
    event.respondWith(networkFirst(request));
  }
});

/**
 * Network First Strategy
 */
async function networkFirst(request) {
  try {
    const response = await fetch(request);
    
    if (!response.ok && response.status >= 400) {
      return response;
    }

    const cache = await caches.open(API_CACHE);
    cache.put(request, response.clone());
    
    return response;
  } catch (error) {
    console.log('[SW] Offline, tentando cache:', request.url);
    
    const cachedResponse = await caches.match(request);
    if (cachedResponse) {
      return cachedResponse;
    }

    return new Response('Offline - recurso não disponível', {
      status: 503,
      statusText: 'Service Unavailable'
    });
  }
}

/**
 * Cache First Strategy
 */
async function cacheFirst(request) {
  const cachedResponse = await caches.match(request);
  if (cachedResponse) {
    return cachedResponse;
  }

  try {
    const response = await fetch(request);
    const cache = await caches.open(ASSETS_CACHE);
    cache.put(request, response.clone());
    return response;
  } catch (error) {
    console.error('[SW] Erro ao buscar asset:', error);
    return new Response('Asset não disponível', { status: 404 });
  }
}

/**
 * Message Handler - Comunicação com página
 */
self.addEventListener('message', event => {
  const { type, data } = event.data;

  switch (type) {
    case 'SYNC_PONTO':
      handleSyncPonto(data, event);
      break;
    case 'CLEAR_CACHE':
      handleClearCache(event);
      break;
    case 'GET_CACHE_STATUS':
      handleGetCacheStatus(event);
      break;
    default:
      console.warn('[SW] Tipo desconhecido:', type);
  }
});

/**
 * Sincroniza batidas de ponto
 */
async function handleSyncPonto(data, event) {
  try {
    console.log('[SW] Sincronizando ponto');

    const response = await fetch('/index.php?rota=sincronizar_offline', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });

    const result = await response.json();
    
    event.ports[0].postMessage({
      type: 'SYNC_PONTO_RESULT',
      status: 'sucesso',
      data: result
    });

    if ('setAppBadge' in navigator) {
      navigator.setAppBadge(0);
    }
  } catch (error) {
    event.ports[0].postMessage({
      type: 'SYNC_PONTO_RESULT',
      status: 'erro',
      erro: error.message
    });
  }
}

/**
 * Limpa caches
 */
async function handleClearCache(event) {
  try {
    await caches.delete(API_CACHE);
    await caches.delete(ASSETS_CACHE);
    event.ports[0].postMessage({ type: 'CACHE_CLEARED', status: 'sucesso' });
  } catch (error) {
    event.ports[0].postMessage({ type: 'CACHE_CLEARED', status: 'erro' });
  }
}

/**
 * Status do cache
 */
async function handleGetCacheStatus(event) {
  try {
    const cacheNames = await caches.keys();
    const cacheData = {};

    for (let cacheName of cacheNames) {
      const cache = await caches.open(cacheName);
      const keys = await cache.keys();
      cacheData[cacheName] = keys.length;
    }

    event.ports[0].postMessage({ type: 'CACHE_STATUS', caches: cacheData });
  } catch (error) {
    event.ports[0].postMessage({ type: 'CACHE_STATUS', erro: error.message });
  }
}

/**
 * Sincronização em background
 */
self.addEventListener('online', () => {
  console.log('[SW] App online');
  self.clients.matchAll().then(clients => {
    clients.forEach(client => {
      client.postMessage({ type: 'ONLINE_STATUS', status: 'online' });
    });
  });
});

self.addEventListener('offline', () => {
  console.log('[SW] App offline');
  self.clients.matchAll().then(clients => {
    clients.forEach(client => {
      client.postMessage({ type: 'ONLINE_STATUS', status: 'offline' });
    });
  });
});
