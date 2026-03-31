<?php
// Debug script to identify and fix project errors
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Project Debug Report</h1>";
echo "<h2>Environment Check</h2>";

// Check PHP version
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'mysqli', 'json'];
echo "<p><strong>PHP Extensions:</strong></p><ul>";
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅ OK" : "❌ Missing";
    echo "<li>$ext: $status</li>";
}
echo "</ul>";

// Check file structure
echo "<h2>File Structure Check</h2>";
$required_files = [
    'config.php' => 'Configuration file',
    'includes/header.php' => 'Header template',
    'includes/footer.php' => 'Footer template',
    'includes/settings.php' => 'Settings helper',
    'admin/database/connection.php' => 'Database connection',
    'admin/login.php' => 'Admin login',
    '.htaccess' => 'URL rewriting'
];

echo "<ul>";
foreach ($required_files as $file => $description) {
    $exists = file_exists($file) ? "✅ Found" : "❌ Missing";
    echo "<li>$file ($description): $exists</li>";
}
echo "</ul>";

// Check database connection
echo "<h2>Database Connection</h2>";
try {
    require_once 'admin/database/connection.php';
    $db = getDB();
    echo "<p style='color: green;'>✅ Database connection successful</p>";
    
    // Check if database exists
    $stmt = $db->query("SELECT DATABASE() as db_name");
    $result = $stmt->fetch();
    echo "<p><strong>Current Database:</strong> " . $result['db_name'] . "</p>";
    
    // Check tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll();
    echo "<p><strong>Tables Found:</strong> " . count($tables) . "</p>";
    echo "<ul>";
    foreach ($tables as $table) {
        echo "<li>" . $table[0] . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database Error: " . $e->getMessage() . "</p>";
}

// Check .htaccess
echo "<h2>.htaccess Check</h2>";
if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'RewriteEngine On') !== false) {
        echo "<p style='color: green;'>✅ URL rewriting enabled</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ URL rewriting not enabled</p>";
    }
    
    if (strpos($htaccess, 'suman%20portfolio') !== false) {
        echo "<p style='color: green;'>✅ Correct RewriteBase found</p>";
    } else {
        echo "<p style='color: red;'>❌ Incorrect RewriteBase</p>";
    }
} else {
    echo "<p style='color: red;'>❌ .htaccess file missing</p>";
}

// Check assets
echo "<h2>Assets Check</h2>";
$asset_dirs = ['assets/css', 'assets/js', 'assets/images'];
foreach ($asset_dirs as $dir) {
    if (is_dir($dir)) {
        $files = scandir($dir);
        $file_count = count($files) - 2; // Remove . and ..
        echo "<p><strong>$dir:</strong> $file_count files</p>";
    } else {
        echo "<p style='color: red;'>❌ $dir directory missing</p>";
    }
}

// Test main page loading
echo "<h2>Main Page Test</h2>";
try {
    ob_start();
    include 'includes/header.php';
    $header_output = ob_get_clean();
    echo "<p style='color: green;'>✅ Header loads successfully</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Header Error: " . $e->getMessage() . "</p>";
}

echo "<h2>Server Information</h2>";
echo "<p><strong>Server:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current URL:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "</p>";

echo "<h2>Quick Fixes</h2>";
echo "<ul>";
echo "<li><strong>If database fails:</strong> Import SQL files from admin/database/</li>";
echo "<li><strong>If files missing:</strong> Check file permissions and paths</li>";
echo "<li><strong>If URLs don't work:</strong> Restart Apache and check .htaccess</li>";
echo "<li><strong>If images broken:</strong> Check assets/ directory</li>";
echo "</ul>";

echo "<p><a href='test.html'>Test HTML Page</a> | <a href='test.php'>Test PHP Page</a> | <a href='/'>Main Site</a></p>";
?>
