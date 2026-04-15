const CACHE = 'w9cafe-v1';
const STATIC_ASSETS = [
  '/customer/menu',
  '/manifest.json',
];

// ── Install: pre-cache static assets ──────────────────────────────────────
self.addEventListener('install', e => {
  e.waitUntil(
    caches.open(CACHE)
      .then(c => c.addAll(STATIC_ASSETS).catch(() => {}))
  );
  self.skipWaiting();
});

// ── Activate: prune old caches ─────────────────────────────────────────────
self.addEventListener('activate', e => {
  e.waitUntil(
    caches.keys().then(keys =>
      Promise.all(keys.filter(k => k !== CACHE).map(k => caches.delete(k)))
    )
  );
  self.clients.claim();
});

// ── Fetch: strategy per request type ─────────────────────────────────────
self.addEventListener('fetch', e => {
  const { request } = e;
  const url = new URL(request.url);

  // Skip: non-GET, API calls, payment, webhook, hot-reload
  if (request.method !== 'GET') return;
  if (url.pathname.startsWith('/api/')) return;
  if (url.pathname.includes('payment')) return;
  if (url.pathname.includes('hot')) return;

  // Vite build assets (hashed filenames) → cache-first
  if (url.pathname.startsWith('/build/')) {
    e.respondWith(
      caches.match(request).then(cached => {
        if (cached) return cached;
        return fetch(request).then(res => {
          const clone = res.clone();
          caches.open(CACHE).then(c => c.put(request, clone));
          return res;
        });
      })
    );
    return;
  }

  // Icons / manifest → cache-first
  if (url.pathname.startsWith('/icons/') || url.pathname === '/manifest.json') {
    e.respondWith(
      caches.match(request).then(r => r || fetch(request))
    );
    return;
  }

  // Navigation (HTML pages) → network-first, fallback cache
  if (request.destination === 'document' || request.headers.get('Accept')?.includes('text/html')) {
    e.respondWith(
      fetch(request)
        .then(res => {
          const clone = res.clone();
          caches.open(CACHE).then(c => c.put(request, clone));
          return res;
        })
        .catch(() => caches.match(request))
    );
    return;
  }
});
