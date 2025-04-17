<?php
// Notification management class
class Notification {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Create a new prayer notification
    public function createNotification($group_id, $sender_id, $prayer_time, $message) {
        $stmt = $this->db->prepare('INSERT INTO notifications (group_id, sender_id, prayer_time, message) 
                                   VALUES (:group_id, :sender_id, :prayer_time, :message)');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $stmt->bindValue(':sender_id', $sender_id, SQLITE3_INTEGER);
        $stmt->bindValue(':prayer_time', $prayer_time, SQLITE3_TEXT);
        $stmt->bindValue(':message', $message, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            $notification_id = $this->db->lastInsertRowID();
            
            // Create pending responses for all group members
            $this->createPendingResponses($notification_id, $group_id);
            
            return ['success' => true, 'notification_id' => $notification_id];
        } else {
            return ['success' => false, 'message' => 'Failed to create notification'];
        }
    }
    
    // Create pending responses for all group members
    private function createPendingResponses($notification_id, $group_id) {
        // Get all members of the group
        $stmt = $this->db->prepare('SELECT user_id FROM group_members WHERE group_id = :group_id');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        while ($member = $result->fetchArray(SQLITE3_ASSOC)) {
            $insert = $this->db->prepare('INSERT INTO notification_responses (notification_id, user_id, response_status) 
                                         VALUES (:notification_id, :user_id, :status)');
            $insert->bindValue(':notification_id', $notification_id, SQLITE3_INTEGER);
            $insert->bindValue(':user_id', $member['user_id'], SQLITE3_INTEGER);
            $insert->bindValue(':status', 'pending', SQLITE3_TEXT);
            $insert->execute();
        }
    }
    
    // Get notification by ID
    public function getNotificationById($notification_id) {
        $stmt = $this->db->prepare('SELECT n.*, u.username as sender_name, g.group_name
                                   FROM notifications n
                                   JOIN users u ON n.sender_id = u.user_id
                                   JOIN prayer_groups g ON n.group_id = g.group_id
                                   WHERE n.notification_id = :notification_id');
        $stmt->bindValue(':notification_id', $notification_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    
    // Get notifications for a specific group
    public function getGroupNotifications($group_id) {
        $stmt = $this->db->prepare('SELECT n.*, u.username as sender_name
                                   FROM notifications n
                                   JOIN users u ON n.sender_id = u.user_id
                                   WHERE n.group_id = :group_id
                                   ORDER BY n.prayer_time DESC');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $notifications = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }
    
    // Get notifications for a specific user (from all groups they belong to)
    public function getUserNotifications($user_id) {
        $stmt = $this->db->prepare('SELECT n.*, u.username as sender_name, g.group_name,
                                   nr.response_status, nr.response_id
                                   FROM notifications n
                                   JOIN users u ON n.sender_id = u.user_id
                                   JOIN prayer_groups g ON n.group_id = g.group_id
                                   JOIN notification_responses nr ON n.notification_id = nr.notification_id
                                   WHERE nr.user_id = :user_id
                                   ORDER BY n.prayer_time DESC');
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $notifications = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }
    
    // Get upcoming notifications for a user (where prayer_time is in the future)
    public function getUpcomingNotifications($user_id) {
        $stmt = $this->db->prepare('SELECT n.*, u.username as sender_name, g.group_name,
                                   nr.response_status, nr.response_id
                                   FROM notifications n
                                   JOIN users u ON n.sender_id = u.user_id
                                   JOIN prayer_groups g ON n.group_id = g.group_id
                                   JOIN notification_responses nr ON n.notification_id = nr.notification_id
                                   WHERE nr.user_id = :user_id AND n.prayer_time > CURRENT_TIMESTAMP
                                   ORDER BY n.prayer_time ASC');
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $notifications = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $notifications[] = $row;
        }
        
        return $notifications;
    }
    
    // Update user response to a notification
    public function updateResponse($response_id, $status) {
        $stmt = $this->db->prepare('UPDATE notification_responses 
                                   SET response_status = :status, response_time = CURRENT_TIMESTAMP
                                   WHERE response_id = :response_id');
        $stmt->bindValue(':status', $status, SQLITE3_TEXT);
        $stmt->bindValue(':response_id', $response_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to update response'];
        }
    }
    
    // Get responses for a notification
    public function getNotificationResponses($notification_id) {
        $stmt = $this->db->prepare('SELECT nr.*, u.username, u.full_name
                                   FROM notification_responses nr
                                   JOIN users u ON nr.user_id = u.user_id
                                   WHERE nr.notification_id = :notification_id
                                   ORDER BY nr.response_status, nr.response_time');
        $stmt->bindValue(':notification_id', $notification_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $responses = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $responses[] = $row;
        }
        
        return $responses;
    }
    
    // Delete a notification
    public function deleteNotification($notification_id) {
        $stmt = $this->db->prepare('DELETE FROM notifications WHERE notification_id = :notification_id');
        $stmt->bindValue(':notification_id', $notification_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to delete notification'];
        }
    }
}
?>
