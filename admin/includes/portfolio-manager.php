<?php
/**
 * Portfolio Manager Class
 * Handles CRUD operations for portfolio projects
 */
require_once __DIR__ . '/../database/connection.php';

class PortfolioManager {
    private $db;
    
    public function __construct() {
        $this->db = getDB();
    }
    
    /**
     * Get all projects with optional filtering
     */
    public function getProjects($filters = [], $page = 1, $per_page = 10) {
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
            $where[] = '(title LIKE ? OR description LIKE ?)';
            $search = '%' . $filters['search'] . '%';
            $params[] = $search;
            $params[] = $search;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Get total count
        $count_sql = "SELECT COUNT(*) FROM portfolio_projects WHERE $where_clause";
        $stmt = $this->db->prepare($count_sql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        
        // Get projects
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT * FROM portfolio_projects 
                WHERE $where_clause 
                ORDER BY display_order ASC, created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_merge($params, [$per_page, $offset]));
        $projects = $stmt->fetchAll();
        
        // Parse JSON fields
        foreach ($projects as &$project) {
            $project['technologies'] = json_decode($project['technologies'] ?? '[]', true);
            $project['gallery_images'] = json_decode($project['gallery_images'] ?? '[]', true);
        }
        
        return [
            'projects' => $projects,
            'total' => $total,
            'pages' => ceil($total / $per_page),
            'current_page' => $page
        ];
    }
    
    /**
     * Get single project by ID
     */
    public function getProject($id) {
        $stmt = $this->db->prepare("SELECT * FROM portfolio_projects WHERE id = ?");
        $stmt->execute([$id]);
        $project = $stmt->fetch();
        
        if ($project) {
            $project['technologies'] = json_decode($project['technologies'] ?? '[]', true);
            $project['gallery_images'] = json_decode($project['gallery_images'] ?? '[]', true);
        }
        
        return $project;
    }
    
    /**
     * Get project by slug
     */
    public function getProjectBySlug($slug) {
        $stmt = $this->db->prepare("SELECT * FROM portfolio_projects WHERE slug = ? AND status = 'active'");
        $stmt->execute([$slug]);
        $project = $stmt->fetch();
        
        if ($project) {
            $project['technologies'] = json_decode($project['technologies'] ?? '[]', true);
            $project['gallery_images'] = json_decode($project['gallery_images'] ?? '[]', true);
        }
        
        return $project;
    }
    
    /**
     * Create new project
     */
    public function createProject($data) {
        // Generate slug if not provided
        if (empty($data['slug'])) {
            $data['slug'] = $this->generateSlug($data['title']);
        }
        
        // Check for duplicate slug
        $stmt = $this->db->prepare("SELECT id FROM portfolio_projects WHERE slug = ?");
        $stmt->execute([$data['slug']]);
        if ($stmt->fetch()) {
            $data['slug'] .= '-' . time();
        }
        
        $sql = "INSERT INTO portfolio_projects (title, slug, description, content, featured_image, gallery_images, project_url, github_url, technologies, category, status, display_order) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $data['title'],
            $data['slug'],
            $data['description'],
            $data['content'] ?? null,
            $data['featured_image'] ?? null,
            json_encode($data['gallery_images'] ?? []),
            $data['project_url'] ?? null,
            $data['github_url'] ?? null,
            json_encode($data['technologies'] ?? []),
            $data['category'] ?? 'Web Development',
            $data['status'] ?? 'active',
            $data['display_order'] ?? 0
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Update existing project
     */
    public function updateProject($id, $data) {
        $fields = [];
        $values = [];
        
        $allowed_fields = ['title', 'slug', 'description', 'content', 'featured_image', 'project_url', 'github_url', 'category', 'status', 'display_order'];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $values[] = $data[$field];
            }
        }
        
        // Handle JSON fields
        if (isset($data['technologies'])) {
            $fields[] = "technologies = ?";
            $values[] = json_encode($data['technologies']);
        }
        
        if (isset($data['gallery_images'])) {
            $fields[] = "gallery_images = ?";
            $values[] = json_encode($data['gallery_images']);
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $id;
        $sql = "UPDATE portfolio_projects SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Delete project
     */
    public function deleteProject($id) {
        $stmt = $this->db->prepare("DELETE FROM portfolio_projects WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Get all categories
     */
    public function getCategories() {
        $stmt = $this->db->query("SELECT DISTINCT category FROM portfolio_projects ORDER BY category");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Update display order
     */
    public function updateOrder($id, $order) {
        $stmt = $this->db->prepare("UPDATE portfolio_projects SET display_order = ? WHERE id = ?");
        return $stmt->execute([$order, $id]);
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
