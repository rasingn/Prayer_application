<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Start session
startSession();

// Redirect to dashboard if logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prayer Group Management</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="navbar">
                <div class="logo">Prayer Group Management</div>
                <div class="menu-toggle">☰</div>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Register</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <main>
        <div class="container">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Welcome to Prayer Group Management</h2>
                </div>
                <p>This application helps you organize and join group prayers with your colleagues.</p>
                <p>With Prayer Group Management, you can:</p>
                <ul style="margin-left: 20px; list-style-type: disc;">
                    <li>Create prayer groups</li>
                    <li>Join existing prayer groups</li>
                    <li>Schedule prayer times</li>
                    <li>Notify group members when you're going to pray</li>
                    <li>Respond to prayer notifications</li>
                </ul>
                <div style="margin-top: 20px;">
                    <a href="register.php" class="btn">Register Now</a>
                    <a href="login.php" class="btn btn-secondary">Login</a>
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
