<?php
/**
 * API Database Connection for Vercel
 * Handles database connections in serverless environment
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Database configuration for Vercel
function getDatabaseConnection() {
    // For Vercel, you'll need to use an external database service
    // like PlanetScale, Supabase, or MongoDB Atlas
    $db_url = $_ENV['DATABASE_URL'] ?? '';
    
    if (empty($db_url)) {
        // Fallback for local development
        $host = 'localhost';
        $dbname = 'portfolio';
        $username = 'root';
        $password = '';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    } else {
        // Parse DATABASE_URL for production
        try {
            $pdo = new PDO($db_url);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
}

// API endpoint handler
try {
    $method = $_SERVER['REQUEST_METHOD'];
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $path_parts = explode('/', trim($path, '/'));
    
    // Route the request
    switch ($path_parts[1] ?? '') {
        case 'test':
            // Test database connection
            $db = getDatabaseConnection();
            echo json_encode(['status' => 'success', 'message' => 'Database connected successfully']);
            break;
            
        case 'site-settings':
            // Get site settings
            $db = getDatabaseConnection();
            $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            echo json_encode($settings);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
