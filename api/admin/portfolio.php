<?php
require_once 'includes/auth.php';
require_once 'includes/portfolio-manager.php';
require_once 'includes/file-uploader.php';

$auth->requireAuth();

$portfolioManager = new PortfolioManager();

// Handle delete
$success_message = '';
$error_message = '';

if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (!validateCSRFToken($_GET['csrf_token'] ?? '')) {
        $error_message = 'Security validation failed';
    } else {
        if ($portfolioManager->deleteProject($_GET['delete'])) {
            logActivity($_SESSION['user_id'], 'delete', 'portfolio_projects', $_GET['delete']);
            $success_message = 'Project deleted successfully';
        } else {
            $error_message = 'Failed to delete project';
        }
    }
}

// Get projects
$filters = [
    'status' => $_GET['status'] ?? '',
    'category' => $_GET['category'] ?? '',
    'search' => $_GET['search'] ?? ''
];

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$result = $portfolioManager->getProjects($filters, $page, 10);
$projects = $result['projects'];
$total_pages = $result['pages'];
$total_projects = $result['total'];

$categories = $portfolioManager->getCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portfolio Projects - Admin</title>
    <style><?php include 'blog.php'; /* Reuse same styles, extracted below */ ?></style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="sidebar-header"><h2><i class="fas fa-shield-alt"></i> Admin Panel</h2></div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a href="about-edit.php" class="nav-link"><i class="fas fa-user"></i> About Me</a></li>
                <li class="nav-item"><a href="resume-edit.php" class="nav-link"><i class="fas fa-file-alt"></i> Resume</a></li>
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
                <h1>Portfolio Projects</h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="filters">
                <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <input type="text" name="search" placeholder="Search projects..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat; ?>" <?php echo ($_GET['category'] ?? '') === $cat ? 'selected' : ''; ?>><?php echo $cat; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit"><i class="fas fa-filter"></i> Filter</button>
                </form>
                <a href="portfolio-create.php" class="btn-primary"><i class="fas fa-plus"></i> New Project</a>
            </div>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr><th>Image</th><th>Title</th><th>Category</th><th>Status</th><th>Order</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($projects as $project): ?>
                        <tr>
                            <td>
                                <?php if ($project['featured_image']): ?>
                                <img src="<?php echo '../' . $project['featured_image']; ?>" alt="" class="post-thumbnail">
                                <?php else: ?>
                                <div style="width: 60px; height: 40px; background: #e5e7eb; border-radius: 4px;"></div>
                                <?php endif; ?>
                            </td>
                            <td><strong><?php echo htmlspecialchars($project['title']); ?></strong></td>
                            <td><?php echo $project['category']; ?></td>
                            <td><span class="status-badge status-<?php echo $project['status'] === 'active' ? 'published' : 'draft'; ?>"><?php echo ucfirst($project['status']); ?></span></td>
                            <td><?php echo $project['display_order']; ?></td>
                            <td class="actions">
                                <a href="portfolio-edit.php?id=<?php echo $project['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i></a>
                                <a href="?delete=<?php echo $project['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn-danger" onclick="return confirm('Delete this project?');"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($projects)): ?>
                        <tr><td colspan="6" style="text-align: center; padding: 40px; color: #6b7280;"><i class="fas fa-briefcase" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>No projects found. <a href="portfolio-create.php">Create your first project</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                <a href="?page=<?php echo $i; ?>&<?php echo http_build_query($filters); ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
            
            <p style="text-align: center; color: #6b7280; margin-top: 20px;">Showing <?php echo count($projects); ?> of <?php echo $total_projects; ?> projects</p>
        </main>
    </div>
</body>
</html>
