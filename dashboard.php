<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/user.php';
require_once 'includes/prayer_group.php';
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

// Initialize classes
$groupObj = new PrayerGroup();
$notificationObj = new Notification();

// Get user's groups
$user_groups = $groupObj->getUserGroups($user_id);

// Get upcoming notifications
$upcoming_notifications = $notificationObj->getUpcomingNotifications($user_id);

// Get notification settings
$notification_enabled = $_SESSION['notification_enabled'] ?? 1;
$sound_enabled = $_SESSION['sound_enabled'] ?? 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Prayer Group Management</title>
    <link rel="stylesheet" href="css/styles.css">
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
            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
            <div class="alert alert-<?php echo $flash['type']; ?>">
                <?php echo $flash['message']; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Welcome, <?php echo htmlspecialchars($user_data['full_name']); ?>!</h2>
                </div>
                <p>This is your prayer group management dashboard. From here, you can create or join prayer groups, manage your notifications, and connect with others for prayer times.</p>
            </div>
            
            <div class="row" style="display: flex; flex-wrap: wrap; margin: 0 -15px;">
                <div class="col" style="flex: 1; padding: 0 15px; min-width: 300px;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Your Prayer Groups</h3>
                        </div>
                        
                        <?php if (empty($user_groups)): ?>
                        <p>You are not a member of any prayer groups yet.</p>
                        <p><a href="groups.php" class="btn">Browse Groups</a> or <a href="create_group.php" class="btn">Create New Group</a></p>
                        <?php else: ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach ($user_groups as $group): ?>
                            <li style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee;">
                                <h4><a href="view_group.php?id=<?php echo $group['group_id']; ?>"><?php echo htmlspecialchars($group['group_name']); ?></a></h4>
                                <div class="group-meta">
                                    <span>Leader: <?php echo htmlspecialchars($group['leader_name']); ?></span> | 
                                    <span><?php echo $group['member_count']; ?> members</span>
                                </div>
                                <?php if ($group['leader_id'] == $user_id): ?>
                                <a href="create_notification.php?group_id=<?php echo $group['group_id']; ?>" class="btn btn-success" style="font-size: 0.875rem; padding: 0.5rem 1rem;">Notify Group</a>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <p><a href="groups.php" class="btn">View All Groups</a></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col" style="flex: 1; padding: 0 15px; min-width: 300px;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Upcoming Prayers</h3>
                        </div>
                        
                        <?php if (empty($upcoming_notifications)): ?>
                        <p>No upcoming prayer notifications.</p>
                        <?php else: ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach ($upcoming_notifications as $notification): ?>
                            <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                <h4><?php echo htmlspecialchars($notification['group_name']); ?> Prayer</h4>
                                <div class="notification-meta">
                                    <p><strong>Time:</strong> <?php echo formatDateTime($notification['prayer_time']); ?></p>
                                    <p><strong>From:</strong> <?php echo htmlspecialchars($notification['sender_name']); ?></p>
                                    <?php if (!empty($notification['message'])): ?>
                                    <p><strong>Message:</strong> <?php echo htmlspecialchars($notification['message']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="notification-actions">
                                    <?php if ($notification['response_status'] == 'pending'): ?>
                                    <a href="#" class="btn btn-success notification-response-btn" data-response-id="<?php echo $notification['response_id']; ?>" data-status="joining">Join</a>
                                    <a href="#" class="btn btn-secondary notification-response-btn" data-response-id="<?php echo $notification['response_id']; ?>" data-status="declined">Decline</a>
                                    <?php else: ?>
                                    <span class="badge" style="padding: 0.5rem; border-radius: 4px; background-color: <?php echo $notification['response_status'] == 'joining' ? '#4caf50' : '#757575'; ?>; color: white;">
                                        <?php echo ucfirst($notification['response_status']); ?>
                                    </span>
                                    <a href="#" class="btn btn-secondary notification-response-btn" data-response-id="<?php echo $notification['response_id']; ?>" data-status="<?php echo $notification['response_status'] == 'joining' ? 'declined' : 'joining'; ?>">
                                        Change to <?php echo $notification['response_status'] == 'joining' ? 'Decline' : 'Join'; ?>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <p><a href="notifications.php" class="btn">View All Notifications</a></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="card" style="margin-top: 20px;">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <a href="create_group.php" class="btn">Create New Group</a>
                    <a href="groups.php" class="btn">Join a Group</a>
                    <?php if (!empty($user_groups)): ?>
                    <a href="create_notification.php" class="btn">Schedule Prayer</a>
                    <?php endif; ?>
                    <a href="notification_settings.php" class="btn">Notification Settings</a>
                </div>
            </div>
            
            <!-- Hidden element to store upcoming prayers data for JavaScript -->
            <div id="upcoming-prayers-data" data-prayers='<?php echo json_encode($upcoming_notifications); ?>' style="display: none;"></div>
            
            <!-- Hidden element to store current user ID -->
            <input type="hidden" id="current-user-id" value="<?php echo $user_id; ?>">
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
</body>
</html>
