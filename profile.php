<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/user.php';

// Start session
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user_id = getCurrentUserId();
$userObj = new User();
$user_data = $userObj->getUserById($user_id);

// Process form submission
$error = '';
$success = false;
if (isPostRequest()) {
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    
    if (empty($email) || empty($full_name)) {
        $error = 'Email and full name are required';
    } else {
        // Update profile
        $result = $userObj->updateProfile($user_id, $email, $full_name);
        
        if ($result['success']) {
            // Update session data
            $user_data = $userObj->getUserById($user_id);
            $_SESSION['user'] = [
                'user_id' => $user_data['user_id'],
                'username' => $user_data['username'],
                'full_name' => $user_data['full_name']
            ];
            
            $success = true;
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
    <title>Profile - Prayer Group Management</title>
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
                    <h2 class="card-title">Your Profile</h2>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="alert alert-success">
                    Profile updated successfully!
                </div>
                <?php endif; ?>
                
                <form action="profile.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($user_data['username']); ?>" readonly>
                        <small style="color: #757575;">Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Update Profile</button>
                    </div>
                </form>
                
                <div style="margin-top: 30px;">
                    <h3>Change Password</h3>
                    <p>To change your password, <a href="change_password.php">click here</a>.</p>
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
