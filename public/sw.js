// ═══════════════════════════════════════════════════════════════════
// 81-IDUM — SERVICE WORKER (Optimized PWA & Offline Strategy)
// ═══════════════════════════════════════════════════════════════════

const CACHE_NAME = '81-idum-v2.0.0';
const OFFLINE_URL = '/offline.html';

// Critical static shell assets to pre-cache on install
const STATIC_ASSETS = [
  '/',
  '/offline.html',
  '/temp/css/style.css',
  '/temp/css/site-refresh.css',
  '/temp/js/public-layout.js',
  '/temp/img/logo.webp',
  '/temp/img/favicon-32.png',
  '/temp/img/favicon-180.png',
  'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700&display=swap',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css'
];

// Public pages safe for offline reading
const CACHED_PAGES = [
  '/',
  '/courses',
  '/teacher',
  '/contact',
  '/about',
  '/calendar',
  '/privacy-policy',
  '/terms'
];

// URLs that must NEVER be cached (Network Only)
const NEVER_CACHE_PATTERNS = [
  /\/chat(\/|$)/,
  /\/ai(\/|$)/,
  /\/api(\/|$)/,
  /\/admin(\/|$)/,
  /\/login(\/|$)/,
  /\/register(\/|$)/,
  /\/logout(\/|$)/,
  /\/profile(\/|$)/,
  /\/exam\/session(\/|$)/,
  /\/password(\/|$)/,
  /[?&](after|poll|timestamp)=/
];

function isNeverCache(url) {
  return NEVER_CACHE_PATTERNS.some((pattern) => pattern.test(url));
}

// ═══════════════════════════════════════════════════════════════════
// INSTALL — Safely cache static shell
// ═══════════════════════════════════════════════════════════════════
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then(async (cache) => {
      const urls = [...STATIC_ASSETS, ...CACHED_PAGES];
      // Use map to avoid failure of one URL aborting the entire install
      await Promise.allSettled(
        urls.map((url) =>
          fetch(url, { cache: 'no-cache' })
            .then((res) => {
              if (res.ok) return cache.put(url, res);
            })
            .catch(() => null)
        )
      );
    }).then(() => self.skipWaiting())
  );
});

// ═══════════════════════════════════════════════════════════════════
// ACTIVATE — Delete old caches and take control
// ═══════════════════════════════════════════════════════════════════
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) =>
      Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      )
    ).then(() => self.clients.claim())
  );
});

// ═══════════════════════════════════════════════════════════════════
// FETCH — Smart Routing: Network-First for HTML, Stale-While-Revalidate for Assets, Network-Only for Chat/API
// ═══════════════════════════════════════════════════════════════════
self.addEventListener('fetch', (event) => {
  // Only handle GET requests with http/https
  if (event.request.method !== 'GET' || !event.request.url.startsWith('http')) {
    return;
  }

  const url = event.request.url;

  // 1. Dynamic / Sensitive endpoints: NETWORK ONLY (never cache)
  if (isNeverCache(url)) {
    return; // allow browser default network request
  }

  // 2. Navigation / HTML pages: NETWORK FIRST with offline fallback
  if (event.request.mode === 'navigate') {
    event.respondWith(
      (async () => {
        try {
          const networkResponse = await fetch(event.request);
          if (networkResponse.ok) {
            const cache = await caches.open(CACHE_NAME);
            cache.put(event.request, networkResponse.clone());
          }
          return networkResponse;
        } catch (err) {
          const cachedResponse = await caches.match(event.request);
          if (cachedResponse) {
            return cachedResponse;
          }
          const offlineResponse = await caches.match(OFFLINE_URL);
          if (offlineResponse) {
            return offlineResponse;
          }
          return new Response('Offline', { status: 503, statusText: 'Service Unavailable' });
        }
      })()
    );
    return;
  }

  // 3. Static assets (CSS, JS, images, fonts): STALE-WHILE-REVALIDATE
  event.respondWith(
    (async () => {
      const cachedResponse = await caches.match(event.request);
      const fetchPromise = fetch(event.request).then((networkResponse) => {
        if (networkResponse.ok) {
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, networkResponse.clone());
          });
        }
        return networkResponse;
      }).catch(() => null);

      return cachedResponse || (await fetchPromise) || new Response('', { status: 408 });
    })()
  );
});

// ═══════════════════════════════════════════════════════════════════
// MESSAGE — Skip waiting on user trigger
// ═══════════════════════════════════════════════════════════════════
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
});
