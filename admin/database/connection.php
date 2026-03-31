<?php
/**
 * Database Connection Class
 * Provides secure database connectivity with PDO
 */
class Database {
    private static $instance = null;
    private $connection;
    private $host = 'localhost';
    private $db_name = 'portfolio';
    private $username = 'root';
    private $password = ''; // Change for production
    private $charset = 'utf8mb4';

    private function __construct() {
        try {
            // First try to connect without database to create it if needed
            $dsn = "mysql:host={$this->host};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // Connect to MySQL server
            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // Create database if not exists
            $this->connection->exec("CREATE DATABASE IF NOT EXISTS {$this->db_name} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Connect to the database
            $this->connection->exec("USE {$this->db_name}");
            $this->connection->exec("SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci");
            
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed. Please make sure MySQL is running in XAMPP. Error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Helper function to get database connection
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
?>
