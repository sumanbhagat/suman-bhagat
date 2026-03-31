<?php
/**
 * Universal Database Helper - Works across ALL pages
 * Provides consistent database connection and error handling
 */

// Global database connection and settings
$GLOBALS['db_connection'] = null;
$GLOBALS['db_settings'] = null;
$GLOBALS['db_error'] = null;

/**
 * Get database connection with comprehensive error handling
 * @return PDO|null Database connection or null on failure
 */
function getDatabaseConnection() {
    global $db_connection, $db_error;
    
    // Return existing connection if available
    if ($db_connection !== null) {
        return $db_connection;
    }
    
    try {
        // Step 1: Connect to MySQL server
        $pdo = new PDO('mysql:host=localhost', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => 5
        ]);
        
        // Step 2: Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Step 3: Connect to portfolio database
        $pdo->exec("USE portfolio");
        $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Step 4: Create essential tables if they don't exist
        createEssentialTables($pdo);
        
        // Step 5: Insert default data if needed
        insertDefaultData($pdo);
        
        $db_connection = $pdo;
        return $pdo;
        
    } catch (PDOException $e) {
        $db_error = "Database connection failed: " . $e->getMessage();
        error_log($db_error);
        return null;
    }
}

/**
 * Create essential database tables
 */
function createEssentialTables($pdo) {
    $tables = [
        // Site Settings
        "CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Hero Slides
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
        
        // Users
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
        )",
        
        // Blog Posts
        "CREATE TABLE IF NOT EXISTS blog_posts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            content TEXT,
            excerpt TEXT,
            featured_image VARCHAR(500),
            category VARCHAR(100),
            status ENUM('draft', 'published') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Portfolio Projects
        "CREATE TABLE IF NOT EXISTS portfolio_projects (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            description TEXT,
            featured_image VARCHAR(500),
            technologies JSON,
            status ENUM('draft', 'active') DEFAULT 'draft',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )",
        
        // Gallery Images
        "CREATE TABLE IF NOT EXISTS gallery_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255),
            description TEXT,
            image_path VARCHAR(500) NOT NULL,
            category VARCHAR(100),
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Contact Messages
        "CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL,
            subject VARCHAR(255),
            message TEXT NOT NULL,
            status ENUM('new', 'read') DEFAULT 'new',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($tables as $sql) {
        try {
            $pdo->exec($sql);
        } catch (PDOException $e) {
            error_log("Table creation warning: " . $e->getMessage());
        }
    }
}

/**
 * Insert default data
 */
function insertDefaultData($pdo) {
    try {
        // Check if settings table is empty
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM site_settings");
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            // Insert default settings
            $defaultSettings = [
                ['site_title', 'My Portfolio'],
                ['site_description', 'Professional portfolio website'],
                ['author_name', 'Suman Kumar Bhagat'],
                ['author_email', 'suman@example.com'],
                ['author_phone', '+1 (555) 123-4567'],
                ['site_url', 'http://localhost/suman%20portfolio/']
            ];
            
            foreach ($defaultSettings as $setting) {
                $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
                $stmt->execute($setting);
            }
        }
        
        // Check if hero slides table is empty
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM hero_slides WHERE is_active = 1");
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            // Insert default hero slide
            $stmt = $pdo->prepare("INSERT INTO hero_slides (title, subtitle, description, button1_text, button1_url, button2_text, button2_url, slide_order, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                'Welcome to My Portfolio',
                'Full Stack Developer & Designer',
                'Creating beautiful and functional web experiences with modern technologies.',
                'View My Work',
                'portfolio',
                'Contact Me',
                'contact',
                1,
                true
            ]);
        }
        
        // Check if admin user exists
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $count = $stmt->fetch()['count'];
        
        if ($count == 0) {
            // Create admin user
            $adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(['admin', 'admin@portfolio.com', $adminPassword, 'Administrator', 'admin']);
        }
        
    } catch (PDOException $e) {
        error_log("Default data insertion warning: " . $e->getMessage());
    }
}

/**
 * Get site settings with fallback
 */
function getSiteSettingsSafe() {
    global $db_settings;
    
    if ($db_settings !== null) {
        return $db_settings;
    }
    
    $settings = [];
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (PDOException $e) {
            error_log("Settings query error: " . $e->getMessage());
        }
    }
    
    // Always provide defaults
    $defaults = [
        'site_title' => 'My Portfolio',
        'site_description' => 'Professional portfolio website',
        'author_name' => 'Suman Kumar Bhagat',
        'author_email' => 'suman@example.com',
        'author_phone' => '+1 (555) 123-4567',
        'site_url' => 'http://localhost/suman%20portfolio/'
    ];
    
    $db_settings = array_merge($defaults, $settings);
    return $db_settings;
}

/**
 * Get hero slides with fallback
 */
function getHeroSlidesSafe() {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY slide_order ASC, id ASC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Hero slides query error: " . $e->getMessage());
        }
    }
    
    // Return default slide
    return [
        [
            'title' => 'Welcome to My Portfolio',
            'subtitle' => 'Full Stack Developer & Designer',
            'description' => 'Creating beautiful and functional web experiences with modern technologies.',
            'button1_text' => 'View My Work',
            'button1_url' => 'portfolio',
            'button2_text' => 'Contact Me',
            'button2_url' => 'contact',
            'image_path' => ''
        ]
    ];
}

/**
 * Execute database query safely
 */
function executeQuery($sql, $params = []) {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query execution error: " . $e->getMessage());
            return false;
        }
    }
    
    return false;
}

/**
 * Get database error message
 */
function getDatabaseError() {
    global $db_error;
    return $db_error;
}

/**
 * Check if database is available
 */
function isDatabaseAvailable() {
    return getDatabaseConnection() !== null;
}

/**
 * Initialize database for all pages
 */
function initializeDatabaseForAllPages() {
    return getDatabaseConnection() !== null;
}
?>
