<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/user.php';

// Start session
startSession();

// Check if user is already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

// Process login form submission
$error = '';
if (isPostRequest()) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Attempt to login
        $user = new User();
        $result = $user->login($username, $password);
        
        if ($result['success']) {
            // Set user session
            setUserSession($result['user']);
            
            // Redirect to dashboard
            redirect('dashboard.php');
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
    <title>Login - Prayer Group Management</title>
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
            <div class="card" style="max-width: 500px; margin: 0 auto;">
                <div class="card-header">
                    <h2 class="card-title">Login</h2>
                </div>
                
                <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn">Login</button>
                    </div>
                    
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
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
