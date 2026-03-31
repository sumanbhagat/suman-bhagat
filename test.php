<?php
// Simple test file to check PHP and database
echo "<h1>PHP Test Page</h1>";
echo "<p>PHP is working!</p>";
echo "<p>Current directory: " . __DIR__ . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_NAME'] . "</p>";
echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";

// Test database connection
try {
    require_once 'admin/database/connection.php';
    $db = getDB();
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Test if tables exist
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "<p>Tables found: " . count($tables) . "</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table[0] . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test file includes
echo "<h2>File Tests</h2>";
$files = ['config.php', 'includes/header.php', 'admin/login.php'];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}
?>
