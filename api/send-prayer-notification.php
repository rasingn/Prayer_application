<?php
// API endpoint to send push notification when a prayer is scheduled
header('Content-Type: application/json');

// Include necessary files
require_once '../includes/database.php';
require_once '../includes/functions.php';
require_once '../includes/user.php';
require_once '../includes/notification.php';

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
if (!isset($data['notification_id']) || !isset($data['group_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Get notification details
$notification_id = $data['notification_id'];
$group_id = $data['group_id'];

// Connect to database
$db = getDatabaseConnection();

// Initialize notification object
$notificationObj = new Notification();
$notification = $notificationObj->getNotificationById($notification_id);

if (!$notification) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Notification not found']);
    exit;
}

// Get group members
$stmt = $db->prepare("SELECT user_id FROM group_members WHERE group_id = ?");
$stmt->execute([$group_id]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($members)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'No group members found']);
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

// Prepare notification payload
$notificationPayload = [
    'title' => $notification['group_name'] . ' Prayer',
    'body' => 'A new prayer has been scheduled by ' . $notification['sender_name'] . ' for ' . formatDateTime($notification['prayer_time']),
    'icon' => '/assets/notification-icon.png',
    'url' => '/view_notification.php?id=' . $notification_id,
    'notificationId' => $notification_id
];

if (!empty($notification['message'])) {
    $notificationPayload['body'] .= "\n" . $notification['message'];
}

$payload = json_encode($notificationPayload);

// Track success and failures
$successCount = 0;
$failureCount = 0;
$failureReasons = [];

// Send notification to each member
foreach ($members as $member) {
    // Skip sender
    if ($member['user_id'] == $notification['sender_id']) {
        continue;
    }
    
    // Get member's subscription
    $stmt = $db->prepare("SELECT subscription FROM push_subscriptions WHERE user_id = ?");
    $stmt->execute([$member['user_id']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$result) {
        // No subscription for this user
        continue;
    }
    
    // Parse subscription
    $subscription = Subscription::create(json_decode($result['subscription'], true));
    
    // Send notification
    $report = $webPush->sendOneNotification($subscription, $payload);
    
    // Track result
    if ($report->isSuccess()) {
        $successCount++;
    } else {
        $failureCount++;
        $failureReasons[] = $report->getReason();
    }
}

// Return results
echo json_encode([
    'success' => true,
    'message' => 'Push notifications processed',
    'stats' => [
        'success' => $successCount,
        'failure' => $failureCount,
        'reasons' => $failureReasons
    ]
]);
