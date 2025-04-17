<?php
// API endpoint to save push notification subscription
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
if (!isset($data['subscription']) || !isset($data['user_id'])) {
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

// Connect to database
$db = getDatabaseConnection();

// Check if subscription already exists for this user
$stmt = $db->prepare("SELECT id FROM push_subscriptions WHERE user_id = ?");
$stmt->execute([$user_id]);
$existing = $stmt->fetch(PDO::FETCH_ASSOC);

// Prepare subscription data
$subscription_json = json_encode($data['subscription']);
$created_at = date('Y-m-d H:i:s');

if ($existing) {
    // Update existing subscription
    $stmt = $db->prepare("UPDATE push_subscriptions SET subscription = ?, updated_at = ? WHERE user_id = ?");
    $result = $stmt->execute([$subscription_json, $created_at, $user_id]);
} else {
    // Insert new subscription
    $stmt = $db->prepare("INSERT INTO push_subscriptions (user_id, subscription, created_at, updated_at) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute([$user_id, $subscription_json, $created_at, $created_at]);
}

// Check result
if ($result) {
    echo json_encode(['success' => true, 'message' => 'Subscription saved successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to save subscription']);
}
