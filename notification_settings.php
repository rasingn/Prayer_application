<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/user.php';
require_once 'includes/notification.php';

// Start session
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user_id = getCurrentUserId();
$user_data = getCurrentUser();

// Initialize notification object
$notificationObj = new Notification();

// Get upcoming notifications for the user
$upcoming_notifications = $notificationObj->getUpcomingNotifications($user_id);

// Process form submission
$success = false;
$error = '';
if (isPostRequest()) {
    $notification_enabled = isset($_POST['notification_enabled']) ? 1 : 0;
    $sound_enabled = isset($_POST['sound_enabled']) ? 1 : 0;
    
    // Update user preferences in session for now
    // In a production environment, these would be stored in the database
    $_SESSION['notification_enabled'] = $notification_enabled;
    $_SESSION['sound_enabled'] = $sound_enabled;
    
    $success = true;
}

// Get current settings
$notification_enabled = $_SESSION['notification_enabled'] ?? 1;
$sound_enabled = $_SESSION['sound_enabled'] ?? 1;

// Generate VAPID public key
$vapidPublicKey = 'BLceySgRWmlwMO_3bVpJUuJaWx0YfO6vQkpNrZBFxb6-xCXy47j6SgKVYwXUkqBGyGHlsQDN1fObHKLhGDmi9pM';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification Settings - Prayer Group Management</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #3f51b5;
        }
        
        input:focus + .slider {
            box-shadow: 0 0 1px #3f51b5;
        }
        
        input:checked + .slider:before {
            transform: translateX(26px);
        }
        
        .status-enabled {
            color: #4caf50;
            font-weight: bold;
        }
        
        .status-disabled {
            color: #757575;
        }
    </style>
</head>
<body data-init-push-notifications="true">
    <header>
        <div class="container">
            <div class="navbar">
                <div class="logo">Prayer Group Management</div>
                <div class="menu-toggle">☰</div>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="groups.php">Prayer Groups</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="notification_settings.php">Notification Settings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <main>
        <div class="container">
            <?php if ($success): ?>
            <div class="alert alert-success">
                Notification settings updated successfully!
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Notification Settings</h2>
                </div>
                
                <div id="notification-settings">
                    <form action="notification_settings.php" method="POST">
                        <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                            <div>
                                <h3>Push Notifications</h3>
                                <p>Receive instant notifications when prayers are scheduled</p>
                                <p id="notification-permission-status" class="<?php echo $notification_enabled ? 'status-enabled' : 'status-disabled'; ?>">
                                    Checking permission...
                                </p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="notification-toggle" name="notification_enabled" <?php echo $notification_enabled ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="form-group" style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
                            <div>
                                <h3>Notification Sound</h3>
                                <p>Play sound when notifications appear</p>
                                <p id="sound-status" class="<?php echo $sound_enabled ? 'status-enabled' : 'status-disabled'; ?>">
                                    <?php echo $sound_enabled ? 'Sound enabled' : 'Sound disabled'; ?>
                                </p>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" id="sound-toggle" name="sound_enabled" <?php echo $sound_enabled ? 'checked' : ''; ?>>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="form-group">
                            <h3>How Push Notifications Work</h3>
                            <p>Push notifications allow you to receive instant alerts when:</p>
                            <ul>
                                <li>A group leader schedules a new prayer time</li>
                                <li>A prayer time is about to begin</li>
                            </ul>
                            <p>These notifications will appear even when your browser is closed or you're not actively using the website.</p>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn">Save Settings</button>
                            <button type="button" id="test-notification-btn" class="btn btn-secondary">Test Notification</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Hidden element to store upcoming prayers data for JavaScript -->
            <div id="upcoming-prayers-data" data-prayers='<?php echo json_encode($upcoming_notifications); ?>' style="display: none;"></div>
            
            <!-- Hidden element to store current user ID -->
            <input type="hidden" id="current-user-id" value="<?php echo $user_id; ?>">
            
            <!-- Hidden element to store VAPID public key -->
            <input type="hidden" id="vapid-public-key" value="<?php echo $vapidPublicKey; ?>">
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Prayer Group Management. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="js/scripts.js"></script>
    <script src="js/push-notifications.js"></script>
    <script src="js/notification-sound.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize notification settings
            initNotificationSettings();
        });
    </script>
</body>
</html>
