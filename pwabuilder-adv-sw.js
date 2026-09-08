const CACHE_NAME = 'ilikesci-cache-v13';
const urlsToCache = [
  'landing.html',
  'login.html',
  'signup.html',
  'index.html',
  'assessment.html',
  'materials.html',
  'lessons.html',
  'students.html',
  'games.html',
  'multimedia.html',
  'scoreboard.html',
  'records.html',
  'profile.html',
  'admin.html',
  'tv_display.html',
  'index-tablet.html',
  'dashboard.html',
  'layout.html',
  'styles.css',
  'app.js',
  'manifest.json',
  'presenter.html',
  'student_app.html'
];

// Install event - Cache core assets
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache.map(url => new Request(url, {cache: 'reload'})))
          .catch(error => {
            console.error('Failed to cache some resources:', error);
            // Continue even if some resources fail to cache
            return Promise.resolve();
          });
      })
  );
  self.skipWaiting();
});

// Activate event - Clean up old caches
self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName);
          }
        })
      );
    })
  );
  self.clients.claim();
});

// Fetch event - Strategy depends on resource type:
// - PHP APIs: Network-only (never cache dynamic data)
// - HTML pages: Network-first (always try fresh, fall back to cache for offline)
// - Static assets (CSS/JS/images): Stale-while-revalidate (fast from cache, update in background)
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);

  // Never cache PHP API responses — always fetch fresh from server
  if (url.pathname.endsWith('.php')) {
    event.respondWith(
      fetch(event.request).catch(() => {
        return new Response(JSON.stringify({status: 'error', message: 'Offline — cannot reach server'}), {
          headers: {'Content-Type': 'application/json'}
        });
      })
    );
    return;
  }

  // HTML pages: Network-first strategy
  // Always try the server first so edits show immediately.
  // Only fall back to cache when offline.
  if (event.request.mode === 'navigate' || url.pathname.endsWith('.html')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // Got a fresh response — update the cache and return it
          if (response && response.status === 200) {
            const responseToCache = response.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseToCache);
            });
          }
          return response;
        })
        .catch(() => {
          // Network failed — serve from cache (offline support)
          return caches.match(event.request).then(cached => {
            return cached || caches.match('landing.html');
          });
        })
    );
    return;
  }

  // Static assets (CSS, JS, images, fonts): Stale-while-revalidate
  // Serve instantly from cache for speed, but fetch a fresh copy in the background
  // so the next load always has the latest version.
  event.respondWith(
    caches.match(event.request).then(cachedResponse => {
      const fetchPromise = fetch(event.request).then(networkResponse => {
        if (networkResponse && networkResponse.status === 200 && networkResponse.type === 'basic') {
          const responseToCache = networkResponse.clone();
          caches.open(CACHE_NAME).then(cache => {
            if (event.request.method === 'GET') {
              cache.put(event.request, responseToCache);
            }
          });
        }
        return networkResponse;
      }).catch(() => cachedResponse);

      // Return cached version immediately if available, otherwise wait for network
      return cachedResponse || fetchPromise;
    })
  );
});
