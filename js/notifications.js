// Web notification functionality for Prayer Group Management Website

// Check if browser supports notifications
function checkNotificationSupport() {
    if (!("Notification" in window)) {
        return false;
    }
    return true;
}

// Request notification permission
function requestNotificationPermission() {
    if (!checkNotificationSupport()) {
        console.log("This browser does not support desktop notification");
        return;
    }
    
    Notification.requestPermission().then(function(permission) {
        if (permission === "granted") {
            // Store permission in localStorage
            localStorage.setItem('notificationPermission', 'granted');
            
            // Show success message
            const permissionStatus = document.getElementById('notification-permission-status');
            if (permissionStatus) {
                permissionStatus.textContent = 'Notifications enabled';
                permissionStatus.className = 'status-enabled';
            }
            
            // Update toggle switch if exists
            const notificationToggle = document.getElementById('notification-toggle');
            if (notificationToggle) {
                notificationToggle.checked = true;
            }
            
            // Show a test notification
            showTestNotification();
        } else {
            // Store permission in localStorage
            localStorage.setItem('notificationPermission', permission);
            
            // Show denied/default message
            const permissionStatus = document.getElementById('notification-permission-status');
            if (permissionStatus) {
                permissionStatus.textContent = 'Notifications disabled';
                permissionStatus.className = 'status-disabled';
            }
            
            // Update toggle switch if exists
            const notificationToggle = document.getElementById('notification-toggle');
            if (notificationToggle) {
                notificationToggle.checked = false;
            }
        }
    });
}

// Show a test notification
function showTestNotification() {
    if (Notification.permission === "granted") {
        const notification = new Notification("Prayer Group Management", {
            body: "Notifications are now enabled. You will be notified before upcoming prayers.",
            icon: "/prayer_app/assets/notification-icon.png"
        });
        
        // Play notification sound
        playNotificationSound();
        
        // Close notification after 5 seconds
        setTimeout(function() {
            notification.close();
        }, 5000);
    }
}

// Show prayer notification
function showPrayerNotification(groupName, prayerTime, timeUntil) {
    if (Notification.permission === "granted") {
        let timeMessage = "";
        if (timeUntil <= 5) {
            timeMessage = `starting in ${timeUntil} minutes!`;
        } else {
            timeMessage = `will start in ${timeUntil} minutes.`;
        }
        
        const notification = new Notification(`${groupName} Prayer`, {
            body: `Prayer ${timeMessage}`,
            icon: "/prayer_app/assets/notification-icon.png"
        });
        
        // Play notification sound
        playNotificationSound();
        
        // Close notification after 30 seconds
        setTimeout(function() {
            notification.close();
        }, 30000);
    }
}

// Play notification sound
function playNotificationSound() {
    const soundEnabled = localStorage.getItem('soundEnabled') === 'true';
    if (soundEnabled) {
        const audio = new Audio('/prayer_app/assets/notification-sound.mp3');
        audio.play().catch(e => console.log('Error playing sound:', e));
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
    
    // Play test sound if enabled
    if (enable) {
        playNotificationSound();
    }
}

// Check for upcoming prayers and show notifications
function checkUpcomingPrayers() {
    if (Notification.permission !== "granted") {
        return;
    }
    
    // Get upcoming prayers from data attribute
    const upcomingPrayersElement = document.getElementById('upcoming-prayers-data');
    if (!upcomingPrayersElement) {
        return;
    }
    
    try {
        const upcomingPrayers = JSON.parse(upcomingPrayersElement.getAttribute('data-prayers'));
        const now = new Date();
        
        upcomingPrayers.forEach(prayer => {
            const prayerTime = new Date(prayer.prayer_time);
            const timeDiff = (prayerTime - now) / (1000 * 60); // Time difference in minutes
            
            // Show notification at 10 minutes and 5 minutes before prayer
            if (timeDiff > 0 && timeDiff <= 10.1 && timeDiff >= 9.9) {
                showPrayerNotification(prayer.group_name, prayer.prayer_time, 10);
            } else if (timeDiff > 0 && timeDiff <= 5.1 && timeDiff >= 4.9) {
                showPrayerNotification(prayer.group_name, prayer.prayer_time, 5);
            }
        });
    } catch (e) {
        console.error('Error parsing upcoming prayers:', e);
    }
}

// Initialize notification settings
function initNotificationSettings() {
    // Set initial notification permission status
    const permissionStatus = document.getElementById('notification-permission-status');
    const notificationToggle = document.getElementById('notification-toggle');
    
    if (permissionStatus && notificationToggle) {
        if (Notification.permission === "granted") {
            permissionStatus.textContent = 'Notifications enabled';
            permissionStatus.className = 'status-enabled';
            notificationToggle.checked = true;
        } else {
            permissionStatus.textContent = 'Notifications disabled';
            permissionStatus.className = 'status-disabled';
            notificationToggle.checked = false;
        }
    }
    
    // Set initial sound status
    const soundEnabled = localStorage.getItem('soundEnabled') === 'true';
    const soundStatus = document.getElementById('sound-status');
    const soundToggle = document.getElementById('sound-toggle');
    
    if (soundStatus && soundToggle) {
        soundStatus.textContent = soundEnabled ? 'Sound enabled' : 'Sound disabled';
        soundStatus.className = soundEnabled ? 'status-enabled' : 'status-disabled';
        soundToggle.checked = soundEnabled;
    }
    
    // Add event listeners
    if (notificationToggle) {
        notificationToggle.addEventListener('change', function() {
            if (this.checked) {
                requestNotificationPermission();
            } else {
                localStorage.setItem('notificationPermission', 'denied');
                permissionStatus.textContent = 'Notifications disabled';
                permissionStatus.className = 'status-disabled';
            }
        });
    }
    
    if (soundToggle) {
        soundToggle.addEventListener('change', function() {
            toggleNotificationSound(this.checked);
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
    
    // Check for upcoming prayers every minute
    checkUpcomingPrayers();
    setInterval(checkUpcomingPrayers, 60000);
});
