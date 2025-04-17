<?php
// Include necessary files
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/notification.php';

// Start session
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get current user
$user_id = getCurrentUserId();

// Check if form was submitted
if (!isPostRequest() || !isset($_POST['response_id']) || !isset($_POST['status'])) {
    setFlashMessage('danger', 'Invalid request');
    redirect('notifications.php');
}

// Get form data
$response_id = $_POST['response_id'];
$status = $_POST['status'];

// Validate status
if ($status !== 'joining' && $status !== 'declined') {
    setFlashMessage('danger', 'Invalid response status');
    redirect('notifications.php');
}

// Initialize notification object
$notificationObj = new Notification();

// Update response
$result = $notificationObj->updateResponse($response_id, $status);

if ($result['success']) {
    setFlashMessage('success', 'Your response has been updated to: ' . ucfirst($status));
} else {
    setFlashMessage('danger', $result['message']);
}

// Redirect back to notifications page
redirect('notifications.php');
?>
