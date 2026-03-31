<?php
/**
 * API Blog Handler for Vercel
 * Handles blog posts in serverless environment
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
            // Get blog posts
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $per_page = isset($_GET['per_page']) ? min(20, max(1, (int)$_GET['per_page'])) : 6;
            $offset = ($page - 1) * $per_page;
            
            $category_filter = $_GET['category'] ?? '';
            $search = $_GET['search'] ?? '';
            
            // Build query
            $where_conditions = ["status = 'published'"];
            $params = [];
            
            if (!empty($category_filter)) {
                $where_conditions[] = "category = ?";
                $params[] = $category_filter;
            }
            
            if (!empty($search)) {
                $where_conditions[] = "(title LIKE ? OR content LIKE ?)";
                $params[] = "%{$search}%";
                $params[] = "%{$search}%";
            }
            
            $where_clause = implode(' AND ', $where_conditions);
            
            // Get total count
            $count_stmt = $db->prepare("SELECT COUNT(*) FROM blog_posts WHERE {$where_clause}");
            $count_stmt->execute($params);
            $total_posts = $count_stmt->fetchColumn();
            
            // Get posts
            $sql = "
                SELECT id, title, slug, excerpt, content, category, featured_image, 
                       created_at, updated_at, author
                FROM blog_posts 
                WHERE {$where_clause}
                ORDER BY created_at DESC 
                LIMIT ? OFFSET ?
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([...$params, $per_page, $offset]);
            $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format dates and add URLs
            foreach ($posts as &$post) {
                $post['created_at'] = date('M j, Y', strtotime($post['created_at']));
                $post['updated_at'] = date('M j, Y', strtotime($post['updated_at']));
                $post['url'] = "/blog/{$post['slug']}";
                $post['reading_time'] = ceil(str_word_count(strip_tags($post['content'])) / 200);
            }
            
            echo json_encode([
                'posts' => $posts,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $per_page,
                    'total_posts' => $total_posts,
                    'total_pages' => ceil($total_posts / $per_page)
                ]
            ]);
            break;
            
        case 'POST':
            // Create new blog post (admin only)
            // Add authentication check here
            $input = json_decode(file_get_contents('php://input'), true);
            
            $required_fields = ['title', 'content', 'category'];
            foreach ($required_fields as $field) {
                if (empty($input[$field])) {
                    http_response_code(400);
                    echo json_encode(['error' => ucfirst($field) . ' is required']);
                    exit;
                }
            }
            
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $input['title'])));
            $excerpt = substr(strip_tags($input['content']), 0, 150) . '...';
            
            $stmt = $db->prepare("
                INSERT INTO blog_posts (title, slug, content, excerpt, category, featured_image, author, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'published', NOW(), NOW())
            ");
            
            $stmt->execute([
                $input['title'],
                $slug,
                $input['content'],
                $excerpt,
                $input['category'],
                $input['featured_image'] ?? null,
                $input['author'] ?? 'Admin'
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Blog post created successfully',
                'post_id' => $db->lastInsertId()
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
