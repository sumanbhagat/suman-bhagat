<?php
// Database setup script
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Database Setup</h1>";

try {
    // Connect to MySQL without database
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Connected to MySQL server</p>";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ Database 'portfolio' created/verified</p>";
    
    // Switch to portfolio database
    $pdo->exec("USE portfolio");
    
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
    echo "<p style='color: green;'>✅ Database tables created/verified</p>";
    
    // Insert default data
    $default_settings = [
        ['site_title', 'My Portfolio'],
        ['site_description', 'Professional portfolio website'],
        ['author_name', 'Suman Kumar Bhagat'],
        ['author_email', 'suman@example.com'],
        ['site_url', 'http://localhost/suman%20portfolio/']
    ];
    
    foreach ($default_settings as $setting) {
        $stmt = $pdo->prepare("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute($setting);
    }
    echo "<p style='color: green;'>✅ Default settings inserted</p>";
    
    // Create default admin user (password: admin123)
    $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute(['admin', 'admin@portfolio.com', $admin_password, 'Administrator', 'admin']);
    echo "<p style='color: green;'>✅ Default admin user created (username: admin, password: admin123)</p>";
    
    // Insert sample hero slide
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
    echo "<p style='color: green;'>✅ Sample hero slide created</p>";
    
    echo "<h2>Setup Complete!</h2>";
    echo "<p style='color: green; font-weight: bold;'>✅ Database is ready for use!</p>";
    echo "<p><strong>Admin Login:</strong></p>";
    echo "<ul>";
    echo "<li>Username: <code>admin</code></li>";
    echo "<li>Password: <code>admin123</code></li>";
    echo "</ul>";
    echo "<p><a href='admin/'>Go to Admin Panel</a> | <a href='/'>Visit Site</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure MySQL is running in XAMPP.</p>";
}
?>
