<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';
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

// Process view notification responses
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $notification_id = $_GET['id'];
    $notification = $notificationObj->getNotificationById($notification_id);
    
    if (!$notification) {
        setFlashMessage('danger', 'Notification not found');
        redirect('notifications.php');
    }
    
    $responses = $notificationObj->getNotificationResponses($notification_id);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Notification Responses - Prayer Group Management</title>
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
                    <h2 class="card-title">Notification Responses</h2>
                </div>
                
                <?php if (isset($notification)): ?>
                <div class="notification-details" style="margin-bottom: 20px;">
                    <h3><?php echo htmlspecialchars($notification['group_name']); ?> Prayer</h3>
                    <div class="notification-meta">
                        <p><strong>Time:</strong> <?php echo formatDateTime($notification['prayer_time']); ?></p>
                        <p><strong>From:</strong> <?php echo htmlspecialchars($notification['sender_name']); ?></p>
                        <?php if (!empty($notification['message'])): ?>
                        <p><strong>Message:</strong> <?php echo htmlspecialchars($notification['message']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <?php if (empty($responses)): ?>
                <p>No responses yet.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Member</th>
                            <th>Response</th>
                            <th>Response Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($responses as $response): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($response['full_name']); ?></td>
                            <td>
                                <span style="padding: 0.25rem 0.5rem; border-radius: 4px; background-color: <?php echo $response['response_status'] == 'joining' ? '#4caf50' : ($response['response_status'] == 'declined' ? '#757575' : '#f9a825'); ?>; color: white;">
                                    <?php echo ucfirst($response['response_status']); ?>
                                </span>
                            </td>
                            <td><?php echo $response['response_status'] != 'pending' ? formatDateTime($response['response_time']) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
                
                <div style="margin-top: 20px;">
                    <a href="view_group.php?id=<?php echo $notification['group_id']; ?>" class="btn">Back to Group</a>
                </div>
                <?php else: ?>
                <p>No notification specified.</p>
                <a href="notifications.php" class="btn">View All Notifications</a>
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
