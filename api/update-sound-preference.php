<?php
// API endpoint to update user's sound preference
header('Content-Type: application/json');

// Include necessary files
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/user.php';

// Start session
startSession();

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data from request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Validate data
if (!isset($data['user_id']) || !isset($data['sound_enabled'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Get current user ID
$user_id = getCurrentUserId();

// Verify user ID matches
if ($user_id != $data['user_id']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'User ID mismatch']);
    exit;
}

// Update session preference
$_SESSION['sound_enabled'] = $data['sound_enabled'] ? 1 : 0;

// Return success
echo json_encode(['success' => true, 'message' => 'Sound preference updated successfully']);
