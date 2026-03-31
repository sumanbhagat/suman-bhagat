<?php
require_once 'includes/auth.php';
require_once 'includes/portfolio-manager.php';
require_once 'includes/file-uploader.php';

$auth->requireAuth();

$portfolioManager = new PortfolioManager();
$fileUploader = new FileUploader();

$success_message = '';
$error_message = '';
$is_edit = isset($_GET['id']) && is_numeric($_GET['id']);
$project = $is_edit ? $portfolioManager->getProject($_GET['id']) : [];

if ($is_edit && !$project) {
    secureRedirect('portfolio.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security validation failed';
    } else {
        $data = [
            'title' => sanitizeInput($_POST['title'] ?? ''),
            'slug' => sanitizeInput($_POST['slug'] ?? ''),
            'description' => sanitizeInput($_POST['description'] ?? ''),
            'content' => $_POST['content'] ?? '',
            'category' => sanitizeInput($_POST['category'] ?? 'Web Development'),
            'project_url' => sanitizeInput($_POST['project_url'] ?? ''),
            'github_url' => sanitizeInput($_POST['github_url'] ?? ''),
            'status' => $_POST['status'] ?? 'active',
            'display_order' => (int)($_POST['display_order'] ?? 0),
            'technologies' => array_filter(array_map('trim', explode(',', $_POST['technologies'] ?? '')))
        ];
        
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $upload = $fileUploader->upload($_FILES['featured_image'], 'images');
            if ($upload['success']) {
                $data['featured_image'] = $upload['relative_path'];
            } else {
                $error_message = 'Image upload failed';
            }
        } elseif ($is_edit && !empty($project['featured_image'])) {
            $data['featured_image'] = $project['featured_image'];
        }
        
        if (empty($data['title']) || empty($data['description'])) {
            $error_message = 'Title and description are required';
        } elseif (empty($error_message)) {
            if ($is_edit) {
                if ($portfolioManager->updateProject($_GET['id'], $data)) {
                    logActivity($_SESSION['user_id'], 'update', 'portfolio_projects', $_GET['id']);
                    $success_message = 'Project updated!';
                    $project = $portfolioManager->getProject($_GET['id']);
                } else {
                    $error_message = 'Failed to update project';
                }
            } else {
                $project_id = $portfolioManager->createProject($data);
                if ($project_id) {
                    logActivity($_SESSION['user_id'], 'create', 'portfolio_projects', $project_id);
                    secureRedirect('portfolio.php');
                } else {
                    $error_message = 'Failed to create project';
                }
            }
        }
    }
}

$categories = $portfolioManager->getCategories();
$page_title = $is_edit ? 'Edit Project' : 'Create Project';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $page_title; ?> - Admin</title>
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
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 900px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .form-group textarea { min-height: 150px; resize: vertical; }
        .form-group small { color: #6b7280; font-size: 0.85rem; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .btn-submit { background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 6px; font-size: 1rem; cursor: pointer; }
        .btn-submit:hover { background: #5a67d8; }
        .btn-cancel { background: #e5e7eb; color: #374151; padding: 12px 30px; border: none; border-radius: 6px; font-size: 1rem; text-decoration: none; display: inline-block; margin-left: 10px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        .image-preview img { max-width: 200px; border-radius: 6px; margin-top: 10px; }
        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; height: auto; }
            .main-content { margin-left: 0; }
            .form-row { grid-template-columns: 1fr; }
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
                <li class="nav-item"><a href="portfolio.php" class="nav-link active"><i class="fas fa-briefcase"></i> Portfolio</a></li>
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
                <form method="POST" enctype="multipart/form-data">
                    <?php echo getCSRFTokenField(); ?>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Slug</label>
                            <input type="text" name="slug" value="<?php echo htmlspecialchars($project['slug'] ?? ''); ?>" placeholder="auto-generated">
                            <small>Leave empty to auto-generate</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Description *</label>
                        <textarea name="description" required placeholder="Short project description"><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Detailed Content</label>
                        <textarea name="content" rows="8" placeholder="Full project description, features, challenges..."><?php echo htmlspecialchars($project['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Category</label>
                            <select name="category">
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo ($project['category'] ?? 'Web Development') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                                <?php endforeach; ?>
                                <option value="Web Development" <?php echo ($project['category'] ?? 'Web Development') === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                                <option value="Mobile App" <?php echo ($project['category'] ?? '') === 'Mobile App' ? 'selected' : ''; ?>>Mobile App</option>
                                <option value="UI/UX Design" <?php echo ($project['category'] ?? '') === 'UI/UX Design' ? 'selected' : ''; ?>>UI/UX Design</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="active" <?php echo ($project['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($project['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Project URL</label>
                            <input type="url" name="project_url" value="<?php echo htmlspecialchars($project['project_url'] ?? ''); ?>" placeholder="https://...">
                        </div>
                        <div class="form-group">
                            <label>GitHub URL</label>
                            <input type="url" name="github_url" value="<?php echo htmlspecialchars($project['github_url'] ?? ''); ?>" placeholder="https://github.com/...">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Technologies (comma separated)</label>
                        <input type="text" name="technologies" value="<?php echo htmlspecialchars(implode(', ', $project['technologies'] ?? [])); ?>" placeholder="PHP, MySQL, JavaScript, React">
                        <small>Example: PHP, MySQL, JavaScript, React</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Featured Image</label>
                            <input type="file" name="featured_image" accept="image/*">
                            <?php if ($is_edit && !empty($project['featured_image'])): ?>
                            <div class="image-preview"><img src="<?php echo '../' . $project['featured_image']; ?>" alt="Current"></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Display Order</label>
                            <input type="number" name="display_order" value="<?php echo $project['display_order'] ?? 0; ?>" min="0">
                            <small>Lower numbers display first</small>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> <?php echo $is_edit ? 'Update' : 'Create'; ?></button>
                        <a href="portfolio.php" class="btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
