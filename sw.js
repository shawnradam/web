const CACHE_NAME = 'shawn-radam-v2';
const ASSETS = [
  './',
  'assets/style.css',
  'assets/app.js',
  'manifest.json',
  'assets/menu/home-mega-menu.png',
  'assets/menu/properties-mega-menu.png',
  'assets/menu/loans-financing-mega-menu.png',
  'assets/menu/land-lot-mega-menu.png',
  'assets/menu/blog-mega-menu.png',
  'assets/menu/contact-mega-menu.png',
  'plugins/koperasi-loan-calculator/koperasi-calculator.css',
  'plugins/koperasi-loan-calculator/koperasi-calculator.js',
  'plugins/property-calculator/property-calculator.css',
  'plugins/property-calculator/property-calculator.js'
];

self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => cache.addAll(ASSETS.map(asset => new URL(asset, self.registration.scope).toString())))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', event => {
  event.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(
        keys.map(key => {
          if (key !== CACHE_NAME) {
            return caches.delete(key);
          }
          return null;
        })
      );
    }).then(() => self.clients.claim())
  );
});

self.addEventListener('fetch', event => {
  if (
    event.request.method !== 'GET' ||
    !event.request.url.startsWith(self.location.origin)
  ) {
    return;
  }

  event.respondWith(
    fetch(event.request)
      .then(response => {
        if (response && response.status === 200 && response.type === 'basic') {
          const responseToCache = response.clone();
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseToCache);
          });
        }
        return response;
      })
      .catch(() => caches.match(event.request))
  );
});
