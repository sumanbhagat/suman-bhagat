<?php
require_once 'includes/auth.php';
require_once 'includes/blog-manager.php';
require_once 'includes/file-uploader.php';

$auth->requireAuth();

$blogManager = new BlogManager();
$fileUploader = new FileUploader();

$success_message = '';
$error_message = '';
$is_edit = false;
$post = [];

// Check if editing existing post
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit = true;
    $post = $blogManager->getPost($_GET['id']);
    
    if (!$post) {
        secureRedirect('blog.php');
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security validation failed';
    } else {
        $data = [
            'title' => sanitizeInput($_POST['title'] ?? ''),
            'slug' => sanitizeInput($_POST['slug'] ?? ''),
            'excerpt' => sanitizeInput($_POST['excerpt'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'category' => sanitizeInput($_POST['category'] ?? 'General'),
            'status' => $_POST['status'] ?? 'draft',
            'author_id' => $_SESSION['user_id']
        ];
        
        // Handle file upload
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload = $fileUploader->upload($_FILES['featured_image'], 'images');
            if ($upload['success']) {
                $data['featured_image'] = $upload['relative_path'];
            } else {
                $error_message = 'Image upload failed: ' . implode(', ', $upload['errors']);
            }
        } elseif ($is_edit && !empty($post['featured_image'])) {
            $data['featured_image'] = $post['featured_image'];
        }
        
        // Validation
        if (empty($data['title'])) {
            $error_message = 'Title is required';
        } elseif (empty($data['content'])) {
            $error_message = 'Content is required';
        } elseif (empty($error_message)) {
            if ($is_edit) {
                // Update existing post
                if ($blogManager->updatePost($_GET['id'], $data)) {
                    logActivity($_SESSION['user_id'], 'update', 'blog_posts', $_GET['id']);
                    $success_message = 'Blog post updated successfully!';
                    $post = $blogManager->getPost($_GET['id']); // Refresh data
                } else {
                    $error_message = 'Failed to update blog post';
                }
            } else {
                // Create new post
                $post_id = $blogManager->createPost($data);
                if ($post_id) {
                    logActivity($_SESSION['user_id'], 'create', 'blog_posts', $post_id);
                    $success_message = 'Blog post created successfully!';
                    secureRedirect('blog.php');
                } else {
                    $error_message = 'Failed to create blog post';
                }
            }
        }
    }
}

// Get categories
$categories = $blogManager->getCategories();
$page_title = $is_edit ? 'Edit Post' : 'Create Post';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background: #f5f7fa; color: #333; }
        .admin-layout { display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: #1a1a2e; color: white; position: fixed; height: 100vh; overflow-y: auto; }
        .sidebar-header { padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h2 { font-size: 1.3rem; background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .nav-menu { list-style: none; padding: 20px 0; }
        .nav-item { margin: 5px 0; }
        .nav-link { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #a0a0a0; text-decoration: none; transition: all 0.3s; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.05); color: white; border-left: 3px solid #667eea; }
        .main-content { flex: 1; margin-left: 260px; padding: 20px; }
        .admin-header { background: white; padding: 15px 25px; border-radius: 10px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .logout-btn { background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; }
        
        /* Form Styles */
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 900px; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #374151; }
        .form-group input[type="text"], .form-group select { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; }
        .form-group input[type="text"]:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .form-group textarea { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; resize: vertical; min-height: 400px; font-family: inherit; }
        .form-group small { color: #6b7280; font-size: 0.85rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        
        .image-preview { max-width: 300px; margin-top: 10px; }
        .image-preview img { max-width: 100%; border-radius: 6px; }
        
        .btn-submit { background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        .btn-submit:hover { background: #5a67d8; }
        .btn-cancel { background: #e5e7eb; color: #374151; padding: 12px 30px; border: none; border-radius: 6px; font-size: 1rem; text-decoration: none; display: inline-block; margin-left: 10px; }
        .btn-cancel:hover { background: #d1d5db; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        
        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; height: auto; }
            .main-content { margin-left: 0; }
            .admin-layout { flex-direction: column; }
            .form-row { grid-template-columns: 1fr; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link active"><i class="fas fa-blog"></i> Blog Posts</a></li>
                <li class="nav-item"><a href="portfolio.php" class="nav-link"><i class="fas fa-briefcase"></i> Portfolio</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link"><i class="fas fa-images"></i> Gallery</a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <header class="admin-header">
                <h1><?php echo $page_title; ?></h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <?php echo getCSRFTokenField(); ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="title">Title *</label>
                            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title'] ?? ''); ?>" required placeholder="Enter post title">
                        </div>
                        
                        <div class="form-group">
                            <label for="slug">Slug (URL)</label>
                            <input type="text" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug'] ?? ''); ?>" placeholder="auto-generated-if-empty">
                            <small>Leave empty to auto-generate from title</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="excerpt">Excerpt</label>
                        <input type="text" id="excerpt" name="excerpt" value="<?php echo htmlspecialchars($post['excerpt'] ?? ''); ?>" placeholder="Brief summary of the post (optional)">
                        <small>Used in blog listing. Auto-generated if empty.</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="category">Category</label>
                            <select id="category" name="category">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo ($post['category'] ?? 'General') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                                <option value="General" <?php echo ($post['category'] ?? 'General') === 'General' ? 'selected' : ''; ?>>General</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="draft" <?php echo ($post['status'] ?? 'draft') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="published" <?php echo ($post['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                                <option value="archived" <?php echo ($post['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="featured_image">Featured Image</label>
                        <input type="file" id="featured_image" name="featured_image" accept="image/*">
                        <small>Recommended size: 1200x630 pixels. Max 5MB.</small>
                        
                        <?php if ($is_edit && !empty($post['featured_image'])): ?>
                        <div class="image-preview">
                            <img src="<?php echo '../' . $post['featured_image']; ?>" alt="Current image">
                            <p><small>Current image</small></p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Content *</label>
                        <textarea id="content" name="content" required placeholder="Write your post content here..."><?php echo htmlspecialchars($post['content'] ?? ''); ?></textarea>
                        <small>Supports HTML formatting</small>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit">
                            <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update Post' : 'Create Post'; ?>
                        </button>
                        <a href="blog.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
