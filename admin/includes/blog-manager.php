<?php
/**
 * Blog Manager Class
 * Handles all CRUD operations for blog posts
 */
require_once __DIR__ . '/../database/connection.php';

class BlogManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get all blog posts with optional filtering
     */
    public function getPosts($filters = [], $page = 1, $per_page = 10) {
        $where = ['1=1'];
        $params = [];
        
        if (!empty($filters['status'])) {
            $where[] = 'status = ?';
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['category'])) {
            $where[] = 'category = ?';
            $params[] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = '(title LIKE ? OR content LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM blog_posts WHERE $where_clause";
        $stmt = $this->db->prepare($count_sql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get posts
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT bp.*, u.full_name as author_name 
                FROM blog_posts bp 
                LEFT JOIN users u ON bp.author_id = u.id 
                WHERE $where_clause 
                ORDER BY bp.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($params, [$per_page, $offset]));
        $posts = $stmt->fetchAll();
        
        return [
            'posts' => $posts,
            'total' => $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page
        ];
    }
    
    /**
     * Get single post by ID
     */
    public function getPost($id) {
        $stmt = $this->db->prepare("SELECT bp.*, u.full_name as author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get post by slug
     */
    public function getPostBySlug($slug) {
        $stmt = $this->db->prepare("SELECT bp.*, u.full_name as author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.slug = ? AND bp.status = 'published'");
        $stmt->execute([$slug]);
        return $stmt->fetch();
    }
    
    /**
     * Create new blog post
     */
    public function createPost($data) {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        // Check for duplicate slug
        $stmt = $this->db->prepare("SELECT id FROM blog_posts WHERE slug = ?");
        $stmt->execute([$data['slug']]);
        if ($stmt->fetch()) {
            $data['slug'] .= '-' . time();
        }
        
        $sql = "INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, category, author_id, status, published_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['excerpt'] ?? null,
            $data['content'],
            $data['featured_image'] ?? null,
            $data['category'] ?? 'General',
            $data['author_id'],
            $data['status'] ?? 'draft',
            $data['status'] === 'published' ? date('Y-m-d H:i:s') : null
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update existing post
     */
    public function updatePost($id, $data) {
        $fields = [];
        $values = [];
        
        $allowed_fields = ['title', 'slug', 'excerpt', 'content', 'featured_image', 'category', 'status'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        // Update published_at if status changed to published
        if (isset($data['status']) && $data['status'] === 'published') {
            $fields[] = "published_at = IF(published_at IS NULL, NOW(), published_at)";
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE blog_posts SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Delete post
     */
    public function deletePost($id) {
        $stmt = $this->db->prepare("DELETE FROM blog_posts WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get all categories
     */
    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM blog_posts ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Increment view count
     */
    public function incrementViews($id) {
        $stmt = $this->db->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Generate URL-friendly slug
     */
    private function generateSlug($title) {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9-]+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
}
?>
