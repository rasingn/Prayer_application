<?php
// API endpoint to send a test push notification
header('Content-Type: application/json');

// Include necessary files
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/user.php';

// Include web-push library
require_once '../vendor/autoload.php';

use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

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
if (!isset($data['user_id'])) {
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

// Get user's subscription
$stmt = $db->prepare("SELECT subscription FROM push_subscriptions WHERE user_id = ?");
$stmt->execute([$user_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No subscription found for this user']);
    exit;
}

// VAPID keys
$vapidKeys = [
    'publicKey' => 'BLceySgRWmlwMO_3bVpJUuJaWx0YfO6vQkpNrZBFxb6-xCXy47j6SgKVYwXUkqBGyGHlsQDN1fObHKLhGDmi9pM',
    'privateKey' => 'YOUR_PRIVATE_KEY_HERE', // Replace with your actual private key
];

// Authentication details
$auth = [
    'VAPID' => [
        'subject' => 'mailto:admin@prayerapp.com',
        'publicKey' => $vapidKeys['publicKey'],
        'privateKey' => $vapidKeys['privateKey'],
    ],
];

// Create WebPush instance
$webPush = new WebPush($auth);

// Parse subscription
$subscription = Subscription::create(json_decode($result['subscription'], true));

// Notification payload
$payload = json_encode([
    'title' => 'Test Prayer Notification',
    'body' => 'This is a test notification from Prayer Group Management',
    'icon' => '/assets/notification-icon.png',
    'url' => '/dashboard.php',
]);

// Send notification
$report = $webPush->sendOneNotification($subscription, $payload);

// Check result
if ($report->isSuccess()) {
    echo json_encode(['success' => true, 'message' => 'Test notification sent successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send test notification: ' . $report->getReason()]);
}
