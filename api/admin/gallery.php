<?php
require_once 'includes/auth.php';
require_once 'database/connection.php';
require_once 'includes/file-uploader.php';

$auth->requireAuth();
$db = getDB();
$uploader = new FileUploader('../uploads/');

$success_message = '';
$error_message = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['images'])) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security validation failed';
    } else {
        $uploads = $uploader->uploadMultiple($_FILES['images'], 'images');
        $success_count = 0;
        
        foreach ($uploads as $upload) {
            if ($upload['success']) {
                // Save to database
                $stmt = $db->prepare("INSERT INTO gallery_images (title, file_path, thumbnail_path, category) VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    sanitizeInput($_POST['title'] ?? ''),
                    $upload['relative_path'],
                    $upload['thumbnail_path'],
                    sanitizeInput($_POST['category'] ?? 'General')
                ]);
                $success_count++;
            }
        }
        
        if ($success_count > 0) {
            $success_message = "$success_count image(s) uploaded successfully";
            logActivity($_SESSION['user_id'], 'upload', 'gallery_images');
        }
        if ($success_count < count($uploads)) {
            $error_message = "Failed to upload " . (count($uploads) - $success_count) . " file(s)";
        }
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (validateCSRFToken($_GET['csrf_token'] ?? '')) {
        $stmt = $db->prepare("SELECT file_path FROM gallery_images WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Delete file
            $uploader->delete($image['file_path']);
            
            // Delete from database
            $stmt = $db->prepare("DELETE FROM gallery_images WHERE id = ?");
            $stmt->execute([$_GET['delete']]);
            
            logActivity($_SESSION['user_id'], 'delete', 'gallery_images', $_GET['delete']);
            $success_message = 'Image deleted successfully';
        }
    }
}

// Get images
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$category_filter = $_GET['category'] ?? '';
$where = $category_filter ? "WHERE category = ?" : '';

$stmt = $db->prepare("SELECT * FROM gallery_images $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
if ($category_filter) {
    $stmt->execute([$category_filter, $per_page, $offset]);
} else {
    $stmt->execute([$per_page, $offset]);
}
$images = $stmt->fetchAll();

// Get total count
$stmt = $db->query("SELECT COUNT(*) FROM gallery_images");
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

// Get categories
$stmt = $db->query("SELECT DISTINCT category FROM gallery_images ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Gallery Management - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f5f7fa; color: #333; }
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1a1a2e; color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { font-size: 1.3rem; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-menu { list-style: none; padding: 20px 0; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #a0a0a0; text-decoration: none; transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.05); color: white; border-left: 3px solid #667eea; }
        .main-content { flex: 1; margin-left: 260px; padding: 20px; }
        .admin-header { background: white; padding: 15px 25px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .logout-btn { background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; }
        
        .upload-form { background: white; padding: 25px; border-radius: 10px; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-row { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group select { padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 6px; }
        .btn-submit { background: #667eea; color: white; padding: 10px 25px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-submit:hover { background: #5a67d8; }
        
        .gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        .gallery-item { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); position: relative; }
        .gallery-item img { width: 100%; height: 150px; object-fit: cover; }
        .gallery-info { padding: 15px; }
        .gallery-info p { font-size: 0.85rem; color: #6b7280; margin-bottom: 5px; }
        .gallery-info .category { background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px; font-size: 0.75rem; display: inline-block; }
        .gallery-actions { display: flex; gap: 5px; margin-top: 10px; }
        .btn-delete { background: #ef4444; color: white; padding: 5px 10px; border-radius: 4px; text-decoration: none; font-size: 0.8rem; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        
        .filters { display: flex; gap: 10px; margin-bottom: 20px; }
        .filters a { padding: 8px 16px; border-radius: 6px; text-decoration: none; color: #374151; background: white; }
        .filters a.active { background: #667eea; color: white; }
        
        .pagination { display: flex; gap: 5px; justify-content: center; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #333; }
        .pagination .current { background: #667eea; color: white; }
        
        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; height: auto; }
            .main-content { margin-left: 0; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-header"><h2><i class="fas fa-shield-alt"></i> Admin Panel</h2></div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link"><i class="fas fa-blog"></i> Blog Posts</a></li>
                <li class="nav-item"><a href="portfolio.php" class="nav-link"><i class="fas fa-briefcase"></i> Portfolio</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link active"><i class="fas fa-images"></i> Gallery</a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <header class="admin-header">
                <h1>Gallery Management</h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="upload-form">
                <h3 style="margin-bottom: 15px;"><i class="fas fa-cloud-upload-alt"></i> Upload Images</h3>
                <form method="POST" enctype="multipart/form-data">
                    <?php echo getCSRFTokenField(); ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Select Images</label>
                            <input type="file" name="images[]" multiple accept="image/*" required>
                        </div>
                        <div class="form-group">
                            <label>Title (optional)</label>
                            <input type="text" name="title" placeholder="Image title">
                        </div>
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category">
                                <option value="General">General</option>
                                <option value="Travel">Travel</option>
                                <option value="Events">Events</option>
                                <option value="Nature">Nature</option>
                                <option value="Architecture">Architecture</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn-submit"><i class="fas fa-upload"></i> Upload</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="filters">
                <a href="?" class="<?php echo empty($category_filter) ? 'active' : ''; ?>">All</a>
                <?php foreach ($categories as $cat): ?>
                <a href="?category=<?php echo urlencode($cat); ?>" class="<?php echo $category_filter === $cat ? 'active' : ''; ?>"><?php echo $cat; ?></a>
                <?php endforeach; ?>
            </div>
            
            <div class="gallery-grid">
                <?php foreach ($images as $image): ?>
                <div class="gallery-item">
                    <img src="<?php echo '../' . $image['file_path']; ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
                    <div class="gallery-info">
                        <p><?php echo $image['title'] ?: 'Untitled'; ?></p>
                        <span class="category"><?php echo $image['category']; ?></span>
                        <div class="gallery-actions">
                            <a href="?delete=<?php echo $image['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn-delete" onclick="return confirm('Delete this image?');"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($images)): ?>
            <div style="text-align: center; padding: 60px; color: #6b7280;">
                <i class="fas fa-images" style="font-size: 4rem; margin-bottom: 20px; display: block;"></i>
                <p>No images uploaded yet</p>
            </div>
            <?php endif; ?>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                <a href="?page=<?php echo $i; ?>&category=<?php echo urlencode($category_filter); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
