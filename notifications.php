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

// Initialize classes
$notificationObj = new Notification();

// Get all user notifications
$notifications = $notificationObj->getUserNotifications($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - Prayer Group Management</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="navbar">
                <div class="logo">Prayer Group Management</div>
                <div class="menu-toggle">☰</div>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="groups.php">Prayer Groups</a></li>
                    <li><a href="notifications.php">Notifications</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <main>
        <div class="container">
            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Your Prayer Notifications</h2>
                </div>
                
                <?php if (empty($notifications)): ?>
                <p>You don't have any prayer notifications yet.</p>
                <?php else: ?>
                <div class="notification-list">
                    <?php foreach ($notifications as $notification): ?>
                    <div class="notification-card">
                        <h3><?php echo htmlspecialchars($notification['group_name']); ?> Prayer</h3>
                        <div class="notification-meta">
                            <p><strong>Time:</strong> <?php echo formatDateTime($notification['prayer_time']); ?></p>
                            <p><strong>From:</strong> <?php echo htmlspecialchars($notification['sender_name']); ?></p>
                            <?php if (!empty($notification['message'])): ?>
                            <p><strong>Message:</strong> <?php echo htmlspecialchars($notification['message']); ?></p>
                            <?php endif; ?>
                            <p><strong>Status:</strong> 
                                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; background-color: <?php echo $notification['response_status'] == 'joining' ? '#4caf50' : ($notification['response_status'] == 'declined' ? '#757575' : '#f9a825'); ?>; color: white;">
                                    <?php echo ucfirst($notification['response_status']); ?>
                                </span>
                            </p>
                        </div>
                        
                        <?php if (strtotime($notification['prayer_time']) > time()): ?>
                        <div class="notification-actions">
                            <?php if ($notification['response_status'] != 'joining'): ?>
                            <a href="#" class="btn btn-success notification-response-btn" data-response-id="<?php echo $notification['response_id']; ?>" data-status="joining">Join</a>
                            <?php endif; ?>
                            
                            <?php if ($notification['response_status'] != 'declined'): ?>
                            <a href="#" class="btn btn-secondary notification-response-btn" data-response-id="<?php echo $notification['response_id']; ?>" data-status="declined">Decline</a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <footer>
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Prayer Group Management. All rights reserved.</p>
        </div>
    </footer>
    
    <script src="js/scripts.js"></script>
</body>
</html>
