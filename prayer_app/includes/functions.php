<?php
// Session management and utility functions

// Start session if not already started
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Get current user ID
function getCurrentUserId() {
    startSession();
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current user data
function getCurrentUser() {
    startSession();
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

// Set user session data
function setUserSession($user) {
    startSession();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['user'] = $user;
}

// Clear user session data
function clearUserSession() {
    startSession();
    unset($_SESSION['user_id']);
    unset($_SESSION['user']);
    session_destroy();
}

// Redirect to a URL
function redirect($url) {
    header("Location: $url");
    exit;
}

// Display flash message
function setFlashMessage($type, $message) {
    startSession();
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Get and clear flash message
function getFlashMessage() {
    startSession();
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Format date/time
function formatDateTime($datetime) {
    $date = new DateTime($datetime);
    return $date->format('M j, Y g:i A');
}

// Sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Check if request is POST
function isPostRequest() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Get base URL
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $script = dirname($_SERVER['SCRIPT_NAME']);
    return "$protocol://$host$script";
}
?>
