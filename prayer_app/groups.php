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

// Get all groups
$all_groups = $groupObj->getAllGroups();

// Process join group request
if (isPostRequest() && isset($_POST['join_group'])) {
    $group_id = $_POST['group_id'] ?? 0;
    
    if ($group_id) {
        $result = $groupObj->addMember($group_id, $user_id);
        
        if ($result['success']) {
            setFlashMessage('success', 'You have successfully joined the group.');
        } else {
            setFlashMessage('danger', $result['message']);
        }
        
        redirect('groups.php');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prayer Groups - Prayer Group Management</title>
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
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h2 class="card-title">Prayer Groups</h2>
                    <a href="create_group.php" class="btn">Create New Group</a>
                </div>
                
                <?php if (empty($all_groups)): ?>
                <p>No prayer groups available yet. Be the first to create one!</p>
                <?php else: ?>
                <div class="group-list">
                    <?php foreach ($all_groups as $group): ?>
                    <div class="group-card">
                        <h3><?php echo htmlspecialchars($group['group_name']); ?></h3>
                        <div class="group-meta">
                            <p><strong>Leader:</strong> <?php echo htmlspecialchars($group['leader_name']); ?></p>
                            <p><strong>Members:</strong> <?php echo $group['member_count']; ?></p>
                            <p><strong>Created:</strong> <?php echo formatDateTime($group['created_at']); ?></p>
                        </div>
                        <?php if (!empty($group['description'])): ?>
                        <p><?php echo htmlspecialchars($group['description']); ?></p>
                        <?php endif; ?>
                        
                        <div style="margin-top: 15px;">
                            <a href="view_group.php?id=<?php echo $group['group_id']; ?>" class="btn">View Details</a>
                            
                            <?php if (!$groupObj->isGroupMember($group['group_id'], $user_id)): ?>
                            <form action="groups.php" method="POST" style="display: inline;">
                                <input type="hidden" name="group_id" value="<?php echo $group['group_id']; ?>">
                                <button type="submit" name="join_group" class="btn btn-success">Join Group</button>
                            </form>
                            <?php endif; ?>
                        </div>
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
