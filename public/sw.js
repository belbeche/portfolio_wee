/* Service worker : rend le site installable et prepare les notifications
 * web push. Strategie volontairement prudente : le reseau d'abord, aucune
 * page mise en cache de force, donc jamais de contenu perime. */

self.addEventListener('install', function () {
  self.skipWaiting();
});

self.addEventListener('activate', function (event) {
  event.waitUntil(self.clients.claim());
});

/* Les notifications push : le serveur enverra un JSON
 * { "title": "...", "body": "...", "url": "/..." } */
self.addEventListener('push', function (event) {
  var data = {};
  try { data = event.data ? event.data.json() : {}; } catch (e) {}
  var title = data.title || 'Walid Belbeche';
  event.waitUntil(self.registration.showNotification(title, {
    body: data.body || '',
    icon: '/medias/pwa/icon-192.png',
    badge: '/medias/pwa/icon-192.png',
    data: { url: data.url || '/' }
  }));
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  var url = (event.notification.data && event.notification.data.url) || '/';
  event.waitUntil(self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (list) {
    for (var i = 0; i < list.length; i++) {
      if (list[i].url.indexOf(url) !== -1 && 'focus' in list[i]) { return list[i].focus(); }
    }
    return self.clients.openWindow(url);
  }));
});
