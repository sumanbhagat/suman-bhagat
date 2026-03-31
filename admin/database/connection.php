<?php
/**
 * Robust Database Connection Class
 * Handles all database operations with proper error handling and auto-recovery
 */
class Database {
    private static $instance = null;
    private $connection;
    private $host = 'localhost';
    private $db_name = 'portfolio';
    private $username = 'root';
    private $password = ''; // Change for production
    private $charset = 'utf8mb4';
    private $connected = false;

    private function __construct() {
        $this->connect();
    }

    /**
     * Establish database connection with auto-recovery
     */
    private function connect() {
        try {
            // Step 1: Connect to MySQL server without database
            $dsn = "mysql:host={$this->host};charset={$this->charset}";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 10,
                PDO::ATTR_PERSISTENT => false
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
            
            // Step 2: Create database if not exists
            $this->connection->exec("CREATE DATABASE IF NOT EXISTS {$this->db_name} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Step 3: Connect to the specific database
            $this->connection->exec("USE {$this->db_name}");
            $this->connection->exec("SET NAMES {$this->charset} COLLATE utf8mb4_unicode_ci");
            
            $this->connected = true;
            
        } catch (PDOException $e) {
            $this->connected = false;
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Database connection failed. Please ensure MySQL is running in XAMPP. Error: " . $e->getMessage());
        }
    }

    /**
     * Get database instance (singleton pattern)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get PDO connection
     */
    public function getConnection() {
        if (!$this->connected) {
            $this->connect();
        }
        return $this->connection;
    }

    /**
     * Check if connected
     */
    public function isConnected() {
        return $this->connected;
    }

    /**
     * Reconnect if needed
     */
    public function reconnect() {
        $this->connected = false;
        $this->connect();
    }

    /**
     * Execute query with error handling
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Database query failed: " . $e->getMessage());
        }
    }

    /**
     * Execute insert/update/delete
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->getConnection()->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            error_log("Execute failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Database execute failed: " . $e->getMessage());
        }
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId() {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->getConnection()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit() {
        return $this->getConnection()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->getConnection()->rollback();
    }

    /**
     * Check if table exists
     */
    public function tableExists($tableName) {
        try {
            $stmt = $this->getConnection()->prepare("SHOW TABLES LIKE ?");
            $stmt->execute([$tableName]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Create table if not exists
     */
    public function createTableIfNotExists($sql) {
        try {
            $this->getConnection()->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Table creation failed: " . $e->getMessage());
            return false;
        }
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

/**
 * Global database function for easy access
 */
function getDB() {
    return Database::getInstance()->getConnection();
}
?>
