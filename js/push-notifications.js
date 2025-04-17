// Web Push Notification functionality for Prayer Group Management Website

// VAPID keys for Web Push
const applicationServerPublicKey = 'BLceySgRWmlwMO_3bVpJUuJaWx0YfO6vQkpNrZBFxb6-xCXy47j6SgKVYwXUkqBGyGHlsQDN1fObHKLhGDmi9pM';

// Check if browser supports notifications and service workers
function checkPushSupport() {
  if (!('serviceWorker' in navigator)) {
    console.log('Service Workers are not supported by this browser');
    return false;
  }

  if (!('PushManager' in window)) {
    console.log('Push notifications are not supported by this browser');
    return false;
  }

  return true;
}

// Register service worker
async function registerServiceWorker() {
  try {
    const registration = await navigator.serviceWorker.register('/service-worker.js');
    console.log('Service Worker registered successfully with scope:', registration.scope);
    return registration;
  } catch (error) {
    console.error('Service Worker registration failed:', error);
    throw error;
  }
}

// Convert base64 string to Uint8Array for the applicationServerKey
function urlB64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/\-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

// Subscribe to push notifications
async function subscribeToPushNotifications(registration) {
  try {
    let subscription = await registration.pushManager.getSubscription();
    
    // If already subscribed, return the subscription
    if (subscription) {
      console.log('User is already subscribed to push notifications');
      return subscription;
    }
    
    // Otherwise, create a new subscription
    const applicationServerKey = urlB64ToUint8Array(applicationServerPublicKey);
    
    subscription = await registration.pushManager.subscribe({
      userVisibleOnly: true,
      applicationServerKey: applicationServerKey
    });
    
    console.log('User is now subscribed to push notifications');
    
    // Send the subscription to the server
    await saveSubscriptionToServer(subscription);
    
    return subscription;
  } catch (error) {
    console.error('Failed to subscribe to push notifications:', error);
    throw error;
  }
}

// Save subscription to server
async function saveSubscriptionToServer(subscription) {
  try {
    const response = await fetch('/api/save-subscription.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        subscription: subscription,
        user_id: getCurrentUserId()
      })
    });
    
    if (!response.ok) {
      throw new Error('Failed to save subscription to server');
    }
    
    console.log('Subscription saved to server successfully');
    return true;
  } catch (error) {
    console.error('Error saving subscription to server:', error);
    throw error;
  }
}

// Get current user ID from the page
function getCurrentUserId() {
  const userIdElement = document.getElementById('current-user-id');
  return userIdElement ? userIdElement.value : null;
}

// Request notification permission
async function requestNotificationPermission() {
  try {
    const permission = await Notification.requestPermission();
    
    // Update permission status display
    updatePermissionStatus(permission);
    
    if (permission !== 'granted') {
      throw new Error('Notification permission not granted');
    }
    
    return permission;
  } catch (error) {
    console.error('Error requesting notification permission:', error);
    throw error;
  }
}

// Update permission status display
function updatePermissionStatus(permission) {
  const permissionStatus = document.getElementById('notification-permission-status');
  const notificationToggle = document.getElementById('notification-toggle');
  
  if (permissionStatus) {
    if (permission === 'granted') {
      permissionStatus.textContent = 'Notifications enabled';
      permissionStatus.className = 'status-enabled';
    } else {
      permissionStatus.textContent = 'Notifications disabled';
      permissionStatus.className = 'status-disabled';
    }
  }
  
  if (notificationToggle) {
    notificationToggle.checked = (permission === 'granted');
  }
}

// Initialize push notifications
async function initializePushNotifications() {
  if (!checkPushSupport()) {
    console.log('Push notifications not supported');
    return false;
  }
  
  try {
    // Register service worker
    const registration = await registerServiceWorker();
    
    // Request permission
    const permission = await requestNotificationPermission();
    
    if (permission === 'granted') {
      // Subscribe to push notifications
      const subscription = await subscribeToPushNotifications(registration);
      
      // Store subscription in localStorage for reference
      localStorage.setItem('pushSubscription', JSON.stringify(subscription));
      
      return true;
    }
    
    return false;
  } catch (error) {
    console.error('Failed to initialize push notifications:', error);
    return false;
  }
}

// Test push notification
async function sendTestPushNotification() {
  try {
    const response = await fetch('/api/send-test-notification.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        user_id: getCurrentUserId()
      })
    });
    
    if (!response.ok) {
      throw new Error('Failed to send test notification');
    }
    
    console.log('Test notification sent successfully');
    return true;
  } catch (error) {
    console.error('Error sending test notification:', error);
    throw error;
  }
}

// Toggle notification sound
function toggleNotificationSound(enable) {
  localStorage.setItem('soundEnabled', enable);
  
  // Update sound toggle status
  const soundToggle = document.getElementById('sound-toggle');
  if (soundToggle) {
    soundToggle.checked = enable;
  }
  
  // Update sound status text
  const soundStatus = document.getElementById('sound-status');
  if (soundStatus) {
    soundStatus.textContent = enable ? 'Sound enabled' : 'Sound disabled';
    soundStatus.className = enable ? 'status-enabled' : 'status-disabled';
  }
  
  // Send sound preference to server
  fetch('/api/update-sound-preference.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      user_id: getCurrentUserId(),
      sound_enabled: enable
    })
  }).catch(error => {
    console.error('Error updating sound preference:', error);
  });
}

// Initialize notification settings
function initNotificationSettings() {
  // Set initial notification permission status
  const permissionStatus = document.getElementById('notification-permission-status');
  const notificationToggle = document.getElementById('notification-toggle');
  
  if (permissionStatus && notificationToggle) {
    updatePermissionStatus(Notification.permission);
    
    // Add event listener for notification toggle
    notificationToggle.addEventListener('change', function() {
      if (this.checked) {
        initializePushNotifications();
      } else {
        // Unsubscribe from push notifications
        navigator.serviceWorker.ready.then(registration => {
          registration.pushManager.getSubscription().then(subscription => {
            if (subscription) {
              subscription.unsubscribe().then(() => {
                console.log('User unsubscribed from push notifications');
                updatePermissionStatus('denied');
              });
            }
          });
        });
      }
    });
  }
  
  // Set initial sound status
  const soundEnabled = localStorage.getItem('soundEnabled') === 'true';
  const soundStatus = document.getElementById('sound-status');
  const soundToggle = document.getElementById('sound-toggle');
  
  if (soundStatus && soundToggle) {
    soundStatus.textContent = soundEnabled ? 'Sound enabled' : 'Sound disabled';
    soundStatus.className = soundEnabled ? 'status-enabled' : 'status-disabled';
    soundToggle.checked = soundEnabled;
    
    // Add event listener for sound toggle
    soundToggle.addEventListener('change', function() {
      toggleNotificationSound(this.checked);
    });
  }
  
  // Add event listener for test notification button
  const testButton = document.getElementById('test-notification-btn');
  if (testButton) {
    testButton.addEventListener('click', function() {
      if (Notification.permission === 'granted') {
        sendTestPushNotification();
      } else {
        initializePushNotifications();
      }
    });
  }
}

// Document ready function
document.addEventListener('DOMContentLoaded', function() {
  // Initialize notification settings if on settings page
  if (document.getElementById('notification-settings')) {
    initNotificationSettings();
  }
  
  // Set default sound setting if not set
  if (localStorage.getItem('soundEnabled') === null) {
    localStorage.setItem('soundEnabled', 'true');
  }
  
  // Check if we need to initialize push notifications
  const shouldInitialize = document.body.dataset.initPushNotifications === 'true';
  if (shouldInitialize) {
    initializePushNotifications();
  }
});
