<?php
// Include necessary files
require_once 'includes/functions.php';

// Start session
startSession();

// Clear user session
clearUserSession();

// Redirect to login page
redirect('login.php');
?>
