<?php
require_once 'includes/auth.php';
require_once 'includes/blog-manager.php';
require_once 'includes/file-uploader.php';

$auth->requireAuth();

$blogManager = new BlogManager();
$fileUploader = new FileUploader();

$success_message = '';
$error_message = '';

// Handle delete action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!validateCSRFToken($_GET['csrf_token'] ?? '')) {
        $error_message = 'Security validation failed';
    } else {
        if ($blogManager->deletePost($_GET['delete'])) {
            logActivity($_SESSION['user_id'], 'delete', 'blog_posts', $_GET['delete']);
            $success_message = 'Blog post deleted successfully';
        } else {
            $error_message = 'Failed to delete blog post';
        }
    }
}

// Get filter parameters
$filters = [
    'status' => $_GET['status'] ?? '',
    'category' => $_GET['category'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Get posts
$result = $blogManager->getPosts($filters, $page, 10);
$posts = $result['posts'];
$total_pages = $result['pages'];
$current_page = $result['current_page'];
$total_posts = $result['total'];

// Get categories for filter
$categories = $blogManager->getCategories();

$page_title = 'Blog Posts';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin</title>
    <style>
        /* Reuse admin styles from dashboard */
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
        .logout-btn:hover { background: #dc2626; }
        
        /* Content specific styles */
        .filters { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; display: flex; gap: 15px; flex-wrap: wrap; align-items: center; }
        .filters input, .filters select { padding: 10px 15px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 0.95rem; }
        .filters button { background: #667eea; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; }
        .btn-primary { background: #667eea; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; display: inline-block; }
        .btn-primary:hover { background: #5a67d8; }
        .btn-danger { background: #ef4444; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; }
        .btn-danger:hover { background: #dc2626; }
        .btn-edit { background: #f59e0b; color: white; padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; }
        .btn-edit:hover { background: #d97706; }
        
        .table-container { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; font-size: 0.9rem; }
        tr:hover { background: #f9fafb; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; }
        .status-published { background: #d1fae5; color: #059669; }
        .status-draft { background: #fee2e2; color: #dc2626; }
        .status-archived { background: #e5e7eb; color: #6b7280; }
        .post-thumbnail { width: 60px; height: 40px; object-fit: cover; border-radius: 4px; }
        .actions { display: flex; gap: 8px; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        
        .pagination { display: flex; gap: 5px; justify-content: center; margin-top: 20px; }
        .pagination a, .pagination span { padding: 8px 12px; border-radius: 6px; text-decoration: none; color: #333; }
        .pagination a:hover { background: #e5e7eb; }
        .pagination .current { background: #667eea; color: white; }
        
        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; height: auto; }
            .main-content { margin-left: 0; }
            .admin-layout { flex-direction: column; }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
            </div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a href="about-edit.php" class="nav-link"><i class="fas fa-user"></i> About Me</a></li>
                <li class="nav-item"><a href="resume-edit.php" class="nav-link"><i class="fas fa-file-alt"></i> Resume</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link active"><i class="fas fa-blog"></i> Blog Posts</a></li>
                <li class="nav-item"><a href="portfolio.php" class="nav-link"><i class="fas fa-briefcase"></i> Portfolio</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link"><i class="fas fa-images"></i> Gallery</a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <header class="admin-header">
                <h1>Blog Posts Management</h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <!-- Filters -->
            <div class="filters">
                <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <input type="text" name="search" placeholder="Search posts..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="published" <?php echo ($_GET['status'] ?? '') === 'published' ? 'selected' : ''; ?>>Published</option>
                        <option value="draft" <?php echo ($_GET['status'] ?? '') === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        <option value="archived" <?php echo ($_GET['status'] ?? '') === 'archived' ? 'selected' : ''; ?>>Archived</option>
                    </select>
                    
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($_GET['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button type="submit"><i class="fas fa-filter"></i> Filter</button>
                </form>
                
                <a href="blog-create.php" class="btn-primary"><i class="fas fa-plus"></i> New Post</a>
            </div>
            
            <!-- Posts Table -->
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="80">Image</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Views</th>
                            <th width="120">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td>
                                <?php if ($post['featured_image']): ?>
                                <img src="<?php echo '../' . $post['featured_image']; ?>" alt="" class="post-thumbnail" onerror="this.src='https://via.placeholder.com/60x40'">
                                <?php else: ?>
                                <div style="width: 60px; height: 40px; background: #e5e7eb; border-radius: 4px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($post['title']); ?></strong></td>
                            <td><?php echo $post['category']; ?></td>
                            <td><?php echo $post['author_name']; ?></td>
                            <td><span class="status-badge status-<?php echo $post['status']; ?>"><?php echo ucfirst($post['status']); ?></span></td>
                            <td><?php echo date('M d, Y', strtotime($post['created_at'])); ?></td>
                            <td><?php echo number_format($post['views']); ?></td>
                            <td class="actions">
                                <a href="blog-edit.php?id=<?php echo $post['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo $post['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn-danger" onclick="return confirm('Are you sure you want to delete this post?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 40px; color: #6b7280;">
                                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                                No posts found. <a href="blog-create.php">Create your first post</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($current_page > 1): ?>
                <a href="?page=<?php echo $current_page - 1; ?>&<?php echo http_build_query($filters); ?>"><i class="fas fa-chevron-left"></i></a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $current_page): ?>
                <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($current_page < $total_pages): ?>
                <a href="?page=<?php echo $current_page + 1; ?>&<?php echo http_build_query($filters); ?>"><i class="fas fa-chevron-right"></i></a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
            <p style="text-align: center; color: #6b7280; margin-top: 20px;">Showing <?php echo count($posts); ?> of <?php echo $total_posts; ?> posts</p>
        </main>
    </div>
</body>
</html>
