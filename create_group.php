<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/user.php';
require_once 'includes/prayer_group.php';

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
$error = '';
$success = false;
if (isPostRequest()) {
    $group_name = $_POST['group_name'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($group_name)) {
        $error = 'Group name is required';
    } else {
        // Create new group
        $result = $groupObj->createGroup($group_name, $description, $user_id);
        
        if ($result['success']) {
            setFlashMessage('success', 'Prayer group created successfully!');
            redirect('view_group.php?id=' . $result['group_id']);
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
    <title>Create Prayer Group - Prayer Group Management</title>
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
                    <h2 class="card-title">Create New Prayer Group</h2>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form action="create_group.php" method="POST">
                    <div class="form-group">
                        <label for="group_name">Group Name</label>
                        <input type="text" id="group_name" name="group_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description (Optional)</label>
                        <textarea id="description" name="description" rows="4"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Create Group</button>
                        <a href="groups.php" class="btn btn-secondary">Cancel</a>
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
