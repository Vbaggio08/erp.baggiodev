const CACHE_NAME = 'erp-ripfire-cache-v1';
const urlsToCache = [
  '/',
  'index.php',
  'assets/estilo.css',
  'assets/img/logo_rip.png'
];

// Evento de Instalação: abre o cache e armazena os arquivos principais da aplicação
self.addEventListener('install', function(event) {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(function(cache) {
        console.log('Cache aberto');
        return cache.addAll(urlsToCache);
      })
  );
});

// Evento de Fetch: intercepta as requisições e responde com o cache se disponível
self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.match(event.request)
      .then(function(response) {
        // Se o recurso estiver no cache, retorna ele
        if (response) {
          return response;
        }
        // Caso contrário, faz a requisição à rede
        return fetch(event.request);
      }
    )
  );
});
