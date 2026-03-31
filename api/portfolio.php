<?php
/**
 * API Portfolio Handler for Vercel
 * Handles portfolio projects in serverless environment
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once 'database.php';

try {
    $method = $_SERVER['REQUEST_METHOD'];
    $db = getDatabaseConnection();
    
    switch ($method) {
        case 'GET':
            // Get portfolio projects
            $category_filter = $_GET['category'] ?? '';
            $featured = isset($_GET['featured']) ? filter_var($_GET['featured'], FILTER_VALIDATE_BOOLEAN) : null;
            
            // Build query
            $where_conditions = ["status = 'active'"];
            $params = [];
            
            if (!empty($category_filter)) {
                $where_conditions[] = "JSON_CONTAINS(technologies, ?)";
                $params[] = json_encode($category_filter);
            }
            
            if ($featured !== null) {
                $where_conditions[] = "featured = ?";
                $params[] = $featured ? 1 : 0;
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            $sql = "
                SELECT id, title, slug, description, featured_image, technologies, 
                       project_url, github_url, featured, display_order, created_at
                FROM portfolio_projects 
                WHERE {$where_clause}
                ORDER BY display_order ASC, created_at DESC
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Parse technologies JSON and format data
            foreach ($projects as &$project) {
                $project['technologies'] = json_decode($project['technologies'], true) ?? [];
                $project['created_at'] = date('M j, Y', strtotime($project['created_at']));
                $project['url'] = "/portfolio/{$project['slug']}";
                
                // Add fallback images if needed
                if (empty($project['featured_image'])) {
                    $project['featured_image'] = "https://via.placeholder.com/400x250/6366f1/ffffff?text=" . urlencode($project['title']);
                }
            }
            
            echo json_encode([
                'projects' => $projects,
                'total' => count($projects)
            ]);
            break;
            
        case 'POST':
            // Create new portfolio project (admin only)
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required_fields = ['title', 'description', 'technologies'];
            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => ucfirst($field) . ' is required']);
                    exit;
                }
            }
            
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title'])));
            
            $stmt = $db->prepare("
                INSERT INTO portfolio_projects (title, slug, description, featured_image, technologies, 
                                              project_url, github_url, featured, display_order, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())
            ");
            
            $stmt->execute([
                $input['title'],
                $slug,
                $input['description'],
                $input['featured_image'] ?? null,
                json_encode($input['technologies']),
                $input['project_url'] ?? null,
                $input['github_url'] ?? null,
                $input['featured'] ?? false,
                $input['display_order'] ?? 999
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Project created successfully',
                'project_id' => $db->lastInsertId()
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
