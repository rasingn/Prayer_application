<?php
// Database initialization script

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database file path
$db_path = __DIR__ . '/prayer_app.db';

try {
    // Create a new SQLite database connection
    $db = new SQLite3($db_path);
    
    // Enable foreign keys
    $db->exec('PRAGMA foreign_keys = ON;');
    
    // Read the schema SQL file
    $schema_sql = file_get_contents(__DIR__ . '/schema.sql');
    
    // Execute the schema SQL to create tables
    $result = $db->exec($schema_sql);
    
    if ($result) {
        echo "Database initialized successfully.\n";
        
        // Create a sample admin user for testing
        $username = 'admin';
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $email = 'admin@example.com';
        $full_name = 'Admin User';
        
        $stmt = $db->prepare('INSERT INTO users (username, password, email, full_name) 
                             VALUES (:username, :password, :email, :full_name)');
        $stmt->bindValue(':username', $username, SQLITE3_TEXT);
        $stmt->bindValue(':password', $password, SQLITE3_TEXT);
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $stmt->bindValue(':full_name', $full_name, SQLITE3_TEXT);
        $stmt->execute();
        
        echo "Sample admin user created.\n";
    } else {
        echo "Error initializing database.\n";
    }
    
    // Close the database connection
    $db->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
