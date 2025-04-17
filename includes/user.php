<?php
// User authentication and management class
class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    // Register a new user
    public function register($username, $password, $email, $full_name) {
        // Check if username or email already exists
        $check = $this->db->prepare('SELECT user_id FROM users WHERE username = :username OR email = :email');
        $check->bindValue(':username', $username, SQLITE3_TEXT);
        $check->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $check->execute();
        
        if ($result->fetchArray()) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert the new user
        $stmt = $this->db->prepare('INSERT INTO users (username, password, email, full_name) 
                                   VALUES (:username, :password, :email, :full_name)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':full_name', $full_name, SQLITE3_TEXT);
        
        if ($stmt->execute()) {
            return ['success' => true, 'user_id' => $this->db->lastInsertRowID()];
        } else {
            return ['success' => false, 'message' => 'Registration failed'];
        }
    }
    
    // Login a user
    public function login($username, $password) {
        $stmt = $this->db->prepare('SELECT user_id, username, password, full_name FROM users WHERE username = :username');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        if ($user = $result->fetchArray(SQLITE3_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                // Update last login time
                $update = $this->db->prepare('UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE user_id = :user_id');
                $update->bindValue(':user_id', $user['user_id'], SQLITE3_INTEGER);
                $update->execute();
                
                // Remove password from user array before returning
                unset($user['password']);
                return ['success' => true, 'user' => $user];
            }
        }
        
        return ['success' => false, 'message' => 'Invalid username or password'];
    }
    
    // Get user by ID
    public function getUserById($user_id) {
        $stmt = $this->db->prepare('SELECT user_id, username, email, full_name, created_at, last_login 
                                   FROM users WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        return $result->fetchArray(SQLITE3_ASSOC);
    }
    
    // Update user profile
    public function updateProfile($user_id, $email, $full_name) {
        $stmt = $this->db->prepare('UPDATE users SET email = :email, full_name = :full_name 
                                   WHERE user_id = :user_id');
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':full_name', $full_name, SQLITE3_TEXT);
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        
        if ($stmt->execute()) {
            return ['success' => true];
        } else {
            return ['success' => false, 'message' => 'Profile update failed'];
        }
    }
    
    // Change password
    public function changePassword($user_id, $current_password, $new_password) {
        // Verify current password
        $stmt = $this->db->prepare('SELECT password FROM users WHERE user_id = :user_id');
        $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        if ($user = $result->fetchArray(SQLITE3_ASSOC)) {
            if (password_verify($current_password, $user['password'])) {
                // Update password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update = $this->db->prepare('UPDATE users SET password = :password WHERE user_id = :user_id');
                $update->bindValue(':password', $hashed_password, SQLITE3_TEXT);
                $update->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
                
                if ($update->execute()) {
                    return ['success' => true];
                } else {
                    return ['success' => false, 'message' => 'Password update failed'];
                }
            }
        }
        
        return ['success' => false, 'message' => 'Current password is incorrect'];
    }
    
    // Get all users
    public function getAllUsers() {
        $result = $this->db->query('SELECT user_id, username, email, full_name, created_at, last_login FROM users');
        $users = [];
        
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $users[] = $row;
        }
        
        return $users;
    }
}
?>
