// Service Worker for Push Notifications
const CACHE_NAME = 'lhp-management-v1';

// Files to cache
const urlsToCache = [
  '/',
  '/assets/css/style.css',
  '/pages/dashboard.php',
  '/pages/lhp-list.php'
];

// Install Service Worker
self.addEventListener('install', event => {
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

// Activate Service Worker
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
});

// Handle Push Notifications
self.addEventListener('push', event => {
  const options = {
    body: event.data.text(),
    icon: '/assets/images/notification-icon.png',
    badge: '/assets/images/badge-icon.png',
    vibrate: [100, 50, 100],
    data: {
      dateOfArrival: Date.now(),
      primaryKey: 1
    },
    actions: [
      {
        action: 'view',
        title: 'Lihat Dokumen'
      },
      {
        action: 'close',
        title: 'Tutup'
      }
    ]
  };

  event.waitUntil(
    self.registration.showNotification('LHP Management System', options)
  );
});

// Handle Notification Click
self.addEventListener('notificationclick', event => {
  event.notification.close();

  if (event.action === 'view') {
    // Open the document view page
    event.waitUntil(
      clients.openWindow('/pages/lhp-list.php')
    );
  }
});

// Check for document expiration
setInterval(() => {
  fetch('/notifications/check-expiry.php')
    .then(response => response.json())
    .then(data => {
      if (data.expiring_documents && data.expiring_documents.length > 0) {
        data.expiring_documents.forEach(doc => {
          self.registration.showNotification('Dokumen Akan Kadaluarsa', {
            body: `${doc.title} akan kadaluarsa dalam ${doc.days_remaining} hari`,
            icon: '/assets/images/notification-icon.png',
            badge: '/assets/images/badge-icon.png',
            vibrate: [100, 50, 100],
            data: {
              documentId: doc.id
            },
            actions: [
              {
                action: 'view',
                title: 'Lihat Dokumen'
              }
            ]
          });
        });
      }
    })
    .catch(error => console.error('Error checking document expiry:', error));
}, 86400000); // Check once per day

// Fetch event - Network first, then cache
self.addEventListener('fetch', event => {
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Clone the response before using it
        const responseToCache = response.clone();
        
        // Update cache
        caches.open(CACHE_NAME)
          .then(cache => {
            cache.put(event.request, responseToCache);
          });

        return response;
      })
      .catch(() => {
        // If network fails, try to get from cache
        return caches.match(event.request);
      })
  );
});
