<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/user.php';
require_once 'includes/prayer_group.php';
require_once 'includes/notification.php';

// Include web-push library
require_once 'vendor/autoload.php';

// Start session
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user_id = getCurrentUserId();

// Initialize classes
$groupObj = new PrayerGroup();

// Process form submission
$success = false;
$error = '';

if (isPostRequest()) {
    // Get form data
    $group_id = $_POST['group_id'] ?? '';
    $prayer_time = $_POST['prayer_time'] ?? '';
    $message = $_POST['message'] ?? '';
    
    // Validate data
    if (empty($group_id)) {
        $error = 'Please select a prayer group';
    } elseif (empty($prayer_time)) {
        $error = 'Please specify a prayer time';
    } else {
        // Check if user is a member of the group
        $isMember = $groupObj->isGroupMember($group_id, $user_id);
        
        if (!$isMember) {
            $error = 'You are not a member of this group';
        } else {
            // Create notification
            $notificationObj = new Notification();
            $result = $notificationObj->createNotification($group_id, $user_id, $prayer_time, $message);
            
            if ($result['success']) {
                // Send push notifications to group members
                sendPushNotificationsToGroupMembers($result['notification_id'], $group_id);
                
                setFlashMessage('success', 'Prayer notification created successfully');
                redirect('dashboard.php');
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get user's groups
$user_groups = $groupObj->getUserGroups($user_id);

// Function to send push notifications to group members
function sendPushNotificationsToGroupMembers($notification_id, $group_id) {
    // Make API call to send push notifications
    $apiUrl = 'api/send-prayer-notification.php';
    $data = [
        'notification_id' => $notification_id,
        'group_id' => $group_id
    ];
    
    // Use cURL to make the request
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Log the response
    error_log("Push notification API response: " . $response);
    
    return ($httpCode >= 200 && $httpCode < 300);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Prayer Notification - Prayer Group Management</title>
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
                    <li><a href="notification_settings.php">Notification Settings</a></li>
                    <li><a href="profile.php">Profile</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <main>
        <div class="container">
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Create Prayer Notification</h2>
                </div>
                
                <?php if (empty($user_groups)): ?>
                <p>You are not a member of any prayer groups yet.</p>
                <p><a href="groups.php" class="btn">Browse Groups</a> or <a href="create_group.php" class="btn">Create New Group</a></p>
                <?php else: ?>
                <form action="create_notification.php" method="POST">
                    <div class="form-group">
                        <label for="group_id">Prayer Group</label>
                        <select name="group_id" id="group_id" class="form-control" required>
                            <option value="">Select a group</option>
                            <?php foreach ($user_groups as $group): ?>
                            <option value="<?php echo $group['group_id']; ?>" <?php echo (isset($_GET['group_id']) && $_GET['group_id'] == $group['group_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($group['group_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="prayer_time">Prayer Time</label>
                        <input type="datetime-local" name="prayer_time" id="prayer_time" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message (Optional)</label>
                        <textarea name="message" id="message" class="form-control" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Create Notification</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
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
    <script src="js/push-notifications.js"></script>
    <script src="js/notification-sound.js"></script>
</body>
</html>
