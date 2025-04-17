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

// Initialize classes
$notificationObj = new Notification();
$groupObj = new PrayerGroup();

// Get group ID from query string if available
$group_id = $_GET['group_id'] ?? 0;

// Validate group ID if provided
if ($group_id) {
    $group = $groupObj->getGroupById($group_id);
    if (!$group) {
        setFlashMessage('danger', 'Group not found');
        redirect('dashboard.php');
    }
    
    // Check if user is the leader of this group
    if (!$groupObj->isGroupLeader($group_id, $user_id)) {
        setFlashMessage('danger', 'You are not authorized to create notifications for this group');
        redirect('view_group.php?id=' . $group_id);
    }
}

// Get user's groups where they are the leader
if (!$group_id) {
    $user_groups = $groupObj->getUserGroups($user_id);
    $leader_groups = array_filter($user_groups, function($g) use ($user_id) {
        return $g['leader_id'] == $user_id;
    });
    
    if (empty($leader_groups)) {
        setFlashMessage('danger', 'You are not a leader of any group');
        redirect('dashboard.php');
    }
}

// Process form submission
$error = '';
$success = false;
if (isPostRequest()) {
    $notification_group_id = $_POST['group_id'] ?? 0;
    $prayer_time = $_POST['prayer_time'] ?? '';
    $message = $_POST['message'] ?? '';
    
    if (empty($notification_group_id) || empty($prayer_time)) {
        $error = 'Group and prayer time are required';
    } else {
        // Create notification
        $result = $notificationObj->createNotification($notification_group_id, $user_id, $prayer_time, $message);
        
        if ($result['success']) {
            setFlashMessage('success', 'Prayer notification created successfully!');
            redirect('view_group.php?id=' . $notification_group_id);
        } else {
            $error = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Prayer - Prayer Group Management</title>
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
            <div class="card" style="max-width: 600px; margin: 0 auto;">
                <div class="card-header">
                    <h2 class="card-title">Schedule Prayer Time</h2>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form action="create_notification.php" method="POST">
                    <div class="form-group">
                        <label for="group_id">Prayer Group</label>
                        <?php if ($group_id): ?>
                        <input type="hidden" name="group_id" value="<?php echo $group_id; ?>">
                        <input type="text" value="<?php echo htmlspecialchars($group['group_name']); ?>" readonly>
                        <?php else: ?>
                        <select id="group_id" name="group_id" required>
                            <option value="">Select a group</option>
                            <?php foreach ($leader_groups as $g): ?>
                            <option value="<?php echo $g['group_id']; ?>"><?php echo htmlspecialchars($g['group_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="prayer_time">Prayer Time</label>
                        <input type="datetime-local" id="prayer_time" name="prayer_time" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message (Optional)</label>
                        <textarea id="message" name="message" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Send Notification</button>
                        <a href="<?php echo $group_id ? 'view_group.php?id=' . $group_id : 'dashboard.php'; ?>" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
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
