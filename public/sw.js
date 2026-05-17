// const CACHE_NAME = 'checkin-v1';
// const urlsToCache = [
//   '/',
//   '/login',
//   '/manifest.json',
//   '/images/icons/icon-192x192.png',
//   // Add other assets you want to cache
// ];

// self.addEventListener('install', (event) => {
//   event.waitUntil(
//     caches.open(CACHE_NAME)
//       .then((cache) => {
//         return cache.addAll(urlsToCache);
//       })
//   );
// });

// self.addEventListener('fetch', function(event) {
//     event.respondWith(
//         caches.match(event.request).then(function(response) {
//             return response || fetch(event.request);
//         })
//     );
// });

// self.addEventListener('fetch', (event) => {
//   event.respondWith(
//     caches.match(event.request)
//       .then((response) => {
//         if (response) {
//           return response;
//         }
//         return fetch(event.request)
//           .then((response) => {
//             if (!response || response.status !== 200 || response.type !== 'basic') {
//               return response;
//             }
//             const responseToCache = response.clone();
//             caches.open(CACHE_NAME)
//               .then((cache) => {
//                 cache.put(event.request, responseToCache);
//               });
//             return response;
//           });
//       })
//   );
// });
