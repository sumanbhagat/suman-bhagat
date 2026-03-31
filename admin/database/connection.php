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

/**
 * Check database connection status
 */
function isDatabaseConnected() {
    try {
        $db = Database::getInstance();
        return $db->isConnected();
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Initialize database with default tables and data
 */
function initializeDatabase() {
    try {
        $db = Database::getInstance();
        $pdo = $db->getConnection();
        
        // Create tables if they don't exist
        $tables = [
            "CREATE TABLE IF NOT EXISTS site_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS hero_slides (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                subtitle VARCHAR(255),
                description TEXT,
                image_path VARCHAR(500),
                button1_text VARCHAR(100),
                button1_url VARCHAR(500),
                button2_text VARCHAR(100),
                button2_url VARCHAR(500),
                slide_order INT DEFAULT 0,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )",
            
            "CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                full_name VARCHAR(100),
                role ENUM('admin', 'user') DEFAULT 'user',
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                last_login TIMESTAMP NULL
            )"
        ];
        
        foreach ($tables as $sql) {
            $pdo->exec($sql);
        }
        
        // Insert default data
        $defaultSettings = [
            ['site_title', 'My Portfolio'],
            ['author_name', 'Suman Kumar Bhagat'],
            ['author_email', 'suman@example.com'],
            ['site_url', 'http://localhost/suman%20portfolio/']
        ];
        
        foreach ($defaultSettings as $setting) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
            $stmt->execute($setting);
        }
        
        // Create default admin user
        $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@portfolio.com', $adminPassword, 'Administrator', 'admin']);
        
        // Create default hero slide
        $stmt = $pdo->prepare("INSERT IGNORE INTO hero_slides (title, subtitle, description, button1_text, button1_url, slide_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Welcome to My Portfolio',
            'Full Stack Developer & Designer',
            'Creating beautiful and functional web experiences with modern technologies.',
            'View My Work',
            'portfolio',
            1,
            true
        ]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Database initialization failed: " . $e->getMessage());
        return false;
    }
}
?>
