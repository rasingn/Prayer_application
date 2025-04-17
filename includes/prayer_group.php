<?php
// Prayer Group management class
class PrayerGroup {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Create a new prayer group
    public function createGroup($group_name, $description, $leader_id) {
        $stmt = $this->db->prepare('INSERT INTO prayer_groups (group_name, description, leader_id) 
                                   VALUES (:group_name, :description, :leader_id)');
        $stmt->bindValue(':group_name', $group_name, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':leader_id', $leader_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            $group_id = $this->db->lastInsertRowID();
            
            // Add the leader as a member of the group
            $this->addMember($group_id, $leader_id);
            
            return ['success' => true, 'group_id' => $group_id];
        } else {
            return ['success' => false, 'message' => 'Failed to create group'];
        }
    }
    
    // Get group by ID
    public function getGroupById($group_id) {
        $stmt = $this->db->prepare('SELECT g.*, u.username as leader_name, 
                                   (SELECT COUNT(*) FROM group_members WHERE group_id = g.group_id) as member_count
                                   FROM prayer_groups g
                                   JOIN users u ON g.leader_id = u.user_id
                                   WHERE g.group_id = :group_id');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    
    // Get all groups
    public function getAllGroups() {
        $result = $this->db->query('SELECT g.*, u.username as leader_name, 
                                   (SELECT COUNT(*) FROM group_members WHERE group_id = g.group_id) as member_count
                                   FROM prayer_groups g
                                   JOIN users u ON g.leader_id = u.user_id
                                   ORDER BY g.created_at DESC');
        $groups = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $groups[] = $row;
        }
        
        return $groups;
    }
    
    // Get groups for a specific user (both as leader and member)
    public function getUserGroups($user_id) {
        $stmt = $this->db->prepare('SELECT g.*, u.username as leader_name, 
                                   (SELECT COUNT(*) FROM group_members WHERE group_id = g.group_id) as member_count
                                   FROM prayer_groups g
                                   JOIN users u ON g.leader_id = u.user_id
                                   WHERE g.group_id IN (
                                       SELECT group_id FROM group_members WHERE user_id = :user_id
                                   )
                                   ORDER BY g.created_at DESC');
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $groups = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $groups[] = $row;
        }
        
        return $groups;
    }
    
    // Update group details
    public function updateGroup($group_id, $group_name, $description) {
        $stmt = $this->db->prepare('UPDATE prayer_groups 
                                   SET group_name = :group_name, description = :description 
                                   WHERE group_id = :group_id');
        $stmt->bindValue(':group_name', $group_name, SQLITE3_TEXT);
        $stmt->bindValue(':description', $description, SQLITE3_TEXT);
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to update group'];
        }
    }
    
    // Delete a group
    public function deleteGroup($group_id) {
        $stmt = $this->db->prepare('DELETE FROM prayer_groups WHERE group_id = :group_id');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to delete group'];
        }
    }
    
    // Add a member to a group
    public function addMember($group_id, $user_id) {
        // Check if user is already a member
        $check = $this->db->prepare('SELECT membership_id FROM group_members 
                                    WHERE group_id = :group_id AND user_id = :user_id');
        $check->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $check->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $check->execute();
        
        if ($result->fetchArray()) {
            return ['success' => false, 'message' => 'User is already a member of this group'];
        }
        
        // Add the member
        $stmt = $this->db->prepare('INSERT INTO group_members (group_id, user_id) 
                                   VALUES (:group_id, :user_id)');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to add member'];
        }
    }
    
    // Remove a member from a group
    public function removeMember($group_id, $user_id) {
        $stmt = $this->db->prepare('DELETE FROM group_members 
                                   WHERE group_id = :group_id AND user_id = :user_id');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Failed to remove member'];
        }
    }
    
    // Get all members of a group
    public function getGroupMembers($group_id) {
        $stmt = $this->db->prepare('SELECT u.user_id, u.username, u.full_name, u.email, gm.joined_at
                                   FROM group_members gm
                                   JOIN users u ON gm.user_id = u.user_id
                                   WHERE gm.group_id = :group_id
                                   ORDER BY gm.joined_at');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $members = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $members[] = $row;
        }
        
        return $members;
    }
    
    // Check if user is a member of a group
    public function isGroupMember($group_id, $user_id) {
        $stmt = $this->db->prepare('SELECT membership_id FROM group_members 
                                   WHERE group_id = :group_id AND user_id = :user_id');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        return $result->fetchArray() ? true : false;
    }
    
    // Check if user is the leader of a group
    public function isGroupLeader($group_id, $user_id) {
        $stmt = $this->db->prepare('SELECT group_id FROM prayer_groups 
                                   WHERE group_id = :group_id AND leader_id = :user_id');
        $stmt->bindValue(':group_id', $group_id, SQLITE3_INTEGER);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        return $result->fetchArray() ? true : false;
    }
}
?>
