<?php
// Test MySQL connection
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>MySQL Connection Test</h1>";

// Test 1: Check if MySQL extension is available
echo "<h2>PHP MySQL Extensions</h2>";
if (extension_loaded('pdo')) {
    echo "<p style='color: green;'>✅ PDO extension loaded</p>";
} else {
    echo "<p style='color: red;'>❌ PDO extension not loaded</p>";
}

if (extension_loaded('pdo_mysql')) {
    echo "<p style='color: green;'>✅ PDO MySQL extension loaded</p>";
} else {
    echo "<p style='color: red;'>❌ PDO MySQL extension not loaded</p>";
}

// Test 2: Try to connect to MySQL server
echo "<h2>MySQL Server Connection</h2>";
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✅ Connected to MySQL server successfully</p>";
    
    // Get MySQL version
    $stmt = $pdo->query("SELECT VERSION() as version");
    $result = $stmt->fetch();
    echo "<p><strong>MySQL Version:</strong> " . $result['version'] . "</p>";
    
    // List databases
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll();
    echo "<p><strong>Available Databases:</strong></p><ul>";
    foreach ($databases as $db) {
        $color = $db['Database'] === 'portfolio' ? 'green' : 'blue';
        echo "<li style='color: $color;'>" . $db['Database'] . "</li>";
    }
    echo "</ul>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Cannot connect to MySQL server</p>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>Solutions:</h3>";
    echo "<ul>";
    echo "<li>Make sure MySQL is running in XAMPP Control Panel</li>";
    echo "<li>Check if MySQL service is started (should be green)</li>";
    echo "<li>Try restarting MySQL service</li>";
    echo "<li>Check if another MySQL instance is running on port 3306</li>";
    echo "</ul>";
}

// Test 3: Check if portfolio database exists and can be used
echo "<h2>Portfolio Database Test</h2>";
try {
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS portfolio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<p style='color: green;'>✅ Database 'portfolio' created/verified</p>";
    
    // Connect to portfolio database
    $pdo->exec("USE portfolio");
    echo "<p style='color: green;'>✅ Connected to portfolio database</p>";
    
    // Create basic tables if they don't exist
    $tables = [
        "CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) UNIQUE NOT NULL,
            setting_value TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )",
        "CREATE TABLE IF NOT EXISTS hero_slides (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            subtitle VARCHAR(255),
            description TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            slide_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )"
    ];
    
    foreach ($tables as $sql) {
        $pdo->exec($sql);
    }
    echo "<p style='color: green;'>✅ Database tables created/verified</p>";
    
    // Insert default data
    $pdo->exec("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('site_title', 'My Portfolio')");
    $pdo->exec("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES ('author_name', 'Suman Kumar Bhagat')");
    echo "<p style='color: green;'>✅ Default data inserted</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database setup failed: " . $e->getMessage() . "</p>";
}

echo "<h2>Next Steps</h2>";
echo "<p>If all tests pass ✅, try these URLs:</p>";
echo "<ul>";
echo "<li><a href='test.html'>Test HTML Page</a></li>";
echo "<li><a href='test.php'>Test PHP Page</a></li>";
echo "<li><a href='/'>Main Portfolio Site</a></li>";
echo "<li><a href='admin/'>Admin Panel</a></li>";
echo "</ul>";

echo "<p><strong>Default Admin Login (after setup):</strong></p>";
echo "<ul>";
echo "<li>Username: <code>admin</code></li>";
echo "<li>Password: <code>admin123</code></li>";
echo "</ul>";
?>
