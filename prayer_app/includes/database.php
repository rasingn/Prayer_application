<?php
// Database connection class
class Database {
    private static $instance = null;
    private $db;
    
    private function __construct() {
        $db_path = __DIR__ . '/../db/prayer_app.db';
        $this->db = new SQLite3($db_path);
        $this->db->exec('PRAGMA foreign_keys = ON;');
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->db;
    }
    
    public function close() {
        if ($this->db) {
            $this->db->close();
        }
    }
}
?>
