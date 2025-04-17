// Service Worker for Push Notifications
// This file will handle push events and display notifications

// Files to cache for offline use
const CACHE_NAME = 'prayer-app-cache-v1';
const urlsToCache = [
  '/',
  '/index.php',
  '/css/styles.css',
  '/js/scripts.js',
  '/js/notifications.js',
  '/assets/notification-sound.mp3',
  '/assets/notification-icon.png'
];

// Install event - cache assets
self.addEventListener('install', event => {
  console.log('Service Worker installing...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker caching app shell...');
        return cache.addAll(urlsToCache);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('Service Worker activating...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.filter(cacheName => {
          return cacheName !== CACHE_NAME;
        }).map(cacheName => {
          return caches.delete(cacheName);
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch event - serve cached content when offline
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Cache hit - return response
        if (response) {
          return response;
        }
        return fetch(event.request);
      })
  );
});

// Push event - handle incoming push messages
self.addEventListener('push', event => {
  console.log('Push message received:', event);
  
  let notificationData = {};
  
  if (event.data) {
    try {
      notificationData = event.data.json();
    } catch (e) {
      console.error('Error parsing push data:', e);
      notificationData = {
        title: 'New Prayer Notification',
        body: 'You have a new prayer notification',
        icon: '/assets/notification-icon.png'
      };
    }
  } else {
    notificationData = {
      title: 'New Prayer Notification',
      body: 'You have a new prayer notification',
      icon: '/assets/notification-icon.png'
    };
  }
  
  const title = notificationData.title || 'Prayer Notification';
  const options = {
    body: notificationData.body || 'You have a new prayer notification',
    icon: notificationData.icon || '/assets/notification-icon.png',
    badge: '/assets/notification-icon.png',
    silent: true, // Make notification silent so we can play custom sound
    vibrate: [100, 50, 100],
    data: {
      url: notificationData.url || '/',
      dateOfArrival: Date.now(),
      primaryKey: 1,
      notificationId: notificationData.notificationId || null
    },
    actions: [
      {
        action: 'join',
        title: 'Join Prayer',
        icon: '/assets/join-icon.png'
      },
      {
        action: 'decline',
        title: 'Decline',
        icon: '/assets/decline-icon.png'
      }
    ]
  };
  
  // Notify all clients about the push event to play custom sound
  const notifyAllClients = async () => {
    const clients = await self.clients.matchAll({
      includeUncontrolled: true,
      type: 'window'
    });
    
    // Send message to all clients
    clients.forEach(client => {
      client.postMessage({
        type: 'PUSH_RECEIVED',
        notification: notificationData
      });
    });
  };
  
  event.waitUntil(
    Promise.all([
      self.registration.showNotification(title, options),
      notifyAllClients()
    ])
  );
});

// Notification click event - handle user interaction with notification
self.addEventListener('notificationclick', event => {
  console.log('Notification click received:', event);
  
  event.notification.close();
  
  // Handle notification action clicks
  if (event.action === 'join') {
    // Handle join action
    const responseUrl = '/update_response.php?status=joining&notification_id=' + 
      (event.notification.data.notificationId || '');
    
    event.waitUntil(
      clients.openWindow(responseUrl)
    );
  } else if (event.action === 'decline') {
    // Handle decline action
    const responseUrl = '/update_response.php?status=declined&notification_id=' + 
      (event.notification.data.notificationId || '');
    
    event.waitUntil(
      clients.openWindow(responseUrl)
    );
  } else {
    // Default action when notification body is clicked
    const urlToOpen = new URL(event.notification.data.url, self.location.origin).href;
    
    event.waitUntil(
      clients.matchAll({
        type: 'window',
        includeUncontrolled: true
      })
      .then(windowClients => {
        // Check if there is already a window/tab open with the target URL
        for (let i = 0; i < windowClients.length; i++) {
          const client = windowClients[i];
          // If so, focus it
          if (client.url === urlToOpen && 'focus' in client) {
            return client.focus();
          }
        }
        // If not, open a new window/tab
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
    );
  }
});

// Push subscription change event
self.addEventListener('pushsubscriptionchange', event => {
  console.log('Push subscription changed:', event);
  
  event.waitUntil(
    self.registration.pushManager.subscribe(event.oldSubscription.options)
      .then(subscription => {
        console.log('New subscription:', subscription);
        // Send new subscription to server
        return fetch('/api/save-subscription.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(subscription)
        });
      })
  );
});
