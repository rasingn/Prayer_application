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

// Check if group ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('danger', 'Invalid group ID');
    redirect('groups.php');
}

$group_id = $_GET['id'];

// Initialize classes
$groupObj = new PrayerGroup();
$notificationObj = new Notification();

// Get group details
$group = $groupObj->getGroupById($group_id);
if (!$group) {
    setFlashMessage('danger', 'Group not found');
    redirect('groups.php');
}

// Check if user is a member of this group
$is_member = $groupObj->isGroupMember($group_id, $user_id);
$is_leader = $groupObj->isGroupLeader($group_id, $user_id);

// Get group members
$members = $groupObj->getGroupMembers($group_id);

// Get group notifications
$notifications = $notificationObj->getGroupNotifications($group_id);

// Process leave group request
if (isPostRequest() && isset($_POST['leave_group']) && $is_member && !$is_leader) {
    $result = $groupObj->removeMember($group_id, $user_id);
    
    if ($result['success']) {
        setFlashMessage('success', 'You have left the group.');
        redirect('groups.php');
    } else {
        setFlashMessage('danger', $result['message']);
        redirect('view_group.php?id=' . $group_id);
    }
}

// Process delete group request
if (isPostRequest() && isset($_POST['delete_group']) && $is_leader) {
    $result = $groupObj->deleteGroup($group_id);
    
    if ($result['success']) {
        setFlashMessage('success', 'Group has been deleted.');
        redirect('groups.php');
    } else {
        setFlashMessage('danger', $result['message']);
        redirect('view_group.php?id=' . $group_id);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($group['group_name']); ?> - Prayer Group Management</title>
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
                    <h2 class="card-title"><?php echo htmlspecialchars($group['group_name']); ?></h2>
                    <?php if ($is_leader): ?>
                    <a href="create_notification.php?group_id=<?php echo $group_id; ?>" class="btn btn-success">Schedule Prayer</a>
                    <?php endif; ?>
                </div>
                
                <div class="group-meta">
                    <p><strong>Leader:</strong> <?php echo htmlspecialchars($group['leader_name']); ?></p>
                    <p><strong>Members:</strong> <?php echo $group['member_count']; ?></p>
                    <p><strong>Created:</strong> <?php echo formatDateTime($group['created_at']); ?></p>
                </div>
                
                <?php if (!empty($group['description'])): ?>
                <div style="margin: 15px 0;">
                    <h3>Description</h3>
                    <p><?php echo htmlspecialchars($group['description']); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($is_member): ?>
                <div style="margin: 20px 0;">
                    <?php if (!$is_leader): ?>
                    <form action="view_group.php?id=<?php echo $group_id; ?>" method="POST" style="display: inline;">
                        <button type="submit" name="leave_group" class="btn btn-danger" data-confirm="Are you sure you want to leave this group?">Leave Group</button>
                    </form>
                    <?php else: ?>
                    <a href="edit_group.php?id=<?php echo $group_id; ?>" class="btn">Edit Group</a>
                    <form action="view_group.php?id=<?php echo $group_id; ?>" method="POST" style="display: inline; margin-left: 10px;">
                        <button type="submit" name="delete_group" class="btn btn-danger" data-confirm="Are you sure you want to delete this group? This action cannot be undone.">Delete Group</button>
                    </form>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="row" style="display: flex; flex-wrap: wrap; margin: 20px -15px 0;">
                <div class="col" style="flex: 1; padding: 0 15px; min-width: 300px;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Group Members</h3>
                        </div>
                        
                        <?php if (empty($members)): ?>
                        <p>No members in this group.</p>
                        <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Joined</th>
                                    <?php if ($is_leader): ?>
                                    <th>Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $member): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($member['full_name']); ?>
                                        <?php if ($member['user_id'] == $group['leader_id']): ?>
                                        <span style="color: #3f51b5; font-weight: bold;"> (Leader)</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDateTime($member['joined_at']); ?></td>
                                    <?php if ($is_leader && $member['user_id'] != $user_id): ?>
                                    <td>
                                        <a href="remove_member.php?group_id=<?php echo $group_id; ?>&user_id=<?php echo $member['user_id']; ?>" class="btn btn-danger" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;" data-confirm="Are you sure you want to remove this member?">Remove</a>
                                    </td>
                                    <?php elseif ($is_leader): ?>
                                    <td>-</td>
                                    <?php endif; ?>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col" style="flex: 1; padding: 0 15px; min-width: 300px;">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Notifications</h3>
                        </div>
                        
                        <?php if (empty($notifications)): ?>
                        <p>No notifications for this group yet.</p>
                        <?php if ($is_leader): ?>
                        <p><a href="create_notification.php?group_id=<?php echo $group_id; ?>" class="btn">Schedule Prayer</a></p>
                        <?php endif; ?>
                        <?php else: ?>
                        <ul style="list-style: none; padding: 0;">
                            <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                            <li style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                <div class="notification-meta">
                                    <p><strong>Time:</strong> <?php echo formatDateTime($notification['prayer_time']); ?></p>
                                    <p><strong>From:</strong> <?php echo htmlspecialchars($notification['sender_name']); ?></p>
                                    <?php if (!empty($notification['message'])): ?>
                                    <p><strong>Message:</strong> <?php echo htmlspecialchars($notification['message']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <?php if ($is_leader && $notification['sender_id'] == $user_id): ?>
                                <a href="view_notification.php?id=<?php echo $notification['notification_id']; ?>" class="btn" style="font-size: 0.875rem; padding: 0.5rem 1rem;">View Responses</a>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if (count($notifications) > 5): ?>
                        <p><a href="group_notifications.php?id=<?php echo $group_id; ?>" class="btn">View All Notifications</a></p>
                        <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
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
