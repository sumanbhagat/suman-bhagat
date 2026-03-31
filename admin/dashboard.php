<?php
require_once 'includes/auth.php';
$auth->requireAuth();

$user = $auth->getCurrentUser();
$db = getDB();

// Get dashboard statistics
$stats = [];

// Blog posts count
$stmt = $db->query("SELECT COUNT(*) FROM blog_posts");
$stats['blog_posts'] = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM blog_posts WHERE status = 'published'");
$stats['published_posts'] = $stmt->fetchColumn();

// Portfolio projects count
$stmt = $db->query("SELECT COUNT(*) FROM portfolio_projects");
$stats['portfolio_projects'] = $stmt->fetchColumn();

// Contact messages
$stmt = $db->query("SELECT COUNT(*) FROM contact_messages WHERE is_read = 0");
$stats['unread_messages'] = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM contact_messages");
$stats['total_messages'] = $stmt->fetchColumn();

// Gallery images
$stmt = $db->query("SELECT COUNT(*) FROM gallery_images");
$stats['gallery_images'] = $stmt->fetchColumn();

// Recent activity
$stmt = $db->prepare("SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC LIMIT 10");
$stmt->execute();
$recent_activity = $stmt->fetchAll();

// Recent contact messages
$stmt = $db->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$recent_messages = $stmt->fetchAll();

// Page title
$page_title = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            color: #333;
        }
        
        /* Sidebar */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background: #1a1a2e;
            color: white;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            font-size: 1.3rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .nav-menu {
            list-style: none;
            padding: 20px 0;
        }
        
        .nav-item {
            margin: 5px 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #a0a0a0;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .nav-link:hover,
        .nav-link.active {
            background: rgba(255,255,255,0.05);
            color: white;
            border-left: 3px solid #667eea;
        }
        
        .nav-link i {
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 20px;
        }
        
        /* Header */
        .admin-header {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .admin-header h1 {
            font-size: 1.5rem;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-info .name {
            font-weight: 600;
        }
        
        .user-info .role {
            font-size: 0.85rem;
            color: #666;
        }
        
        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: #dc2626;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stat-icon.blue { background: #dbeafe; color: #2563eb; }
        .stat-icon.green { background: #d1fae5; color: #059669; }
        .stat-icon.purple { background: #e9d5ff; color: #7c3aed; }
        .stat-icon.orange { background: #fed7aa; color: #ea580c; }
        .stat-icon.pink { background: #fbcfe8; color: #db2777; }
        
        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
        }
        
        .stat-info p {
            color: #666;
            font-size: 0.95rem;
        }
        
        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
        }
        
        .panel {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .panel-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .panel-header h3 {
            font-size: 1.1rem;
        }
        
        .view-all {
            color: #667eea;
            text-decoration: none;
            font-size: 0.9rem;
        }
        
        .panel-body {
            padding: 20px 25px;
        }
        
        /* Activity List */
        .activity-list {
            list-style: none;
        }
        
        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 36px;
            height: 36px;
            background: #f3f4f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        
        .activity-content p {
            font-size: 0.95rem;
            margin-bottom: 4px;
        }
        
        .activity-content .time {
            font-size: 0.8rem;
            color: #9ca3af;
        }
        
        /* Messages List */
        .message-item {
            padding: 15px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .message-header h4 {
            font-size: 0.95rem;
        }
        
        .message-header .time {
            font-size: 0.8rem;
            color: #9ca3af;
        }
        
        .message-preview {
            font-size: 0.9rem;
            color: #666;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .unread-badge {
            background: #ef4444;
            color: white;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: 5px;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-top: 25px;
        }
        
        .quick-action-btn {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: #333;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        
        .quick-action-btn i {
            font-size: 2rem;
            color: #667eea;
            margin-bottom: 10px;
            display: block;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .admin-layout {
                flex-direction: column;
            }
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
                <li class="nav-item">
                    <a href="dashboard.php" class="nav-link active">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="about-edit.php" class="nav-link">
                        <i class="fas fa-user"></i> About Me
                    </a>
                </li>
                <li class="nav-item">
                    <a href="resume-edit.php" class="nav-link">
                        <i class="fas fa-file-alt"></i> Resume
                    </a>
                </li>
                <li class="nav-item">
                    <a href="hero-slider.php" class="nav-link">
                        <i class="fas fa-images"></i> Hero Slider
                    </a>
                </li>
                <li class="nav-item">
                    <a href="blog.php" class="nav-link">
                        <i class="fas fa-blog"></i> Blog Posts
                    </a>
                </li>
                <li class="nav-item">
                    <a href="portfolio.php" class="nav-link">
                        <i class="fas fa-briefcase"></i> Portfolio
                    </a>
                </li>
                <li class="nav-item">
                    <a href="gallery.php" class="nav-link">
                        <i class="fas fa-images"></i> Gallery
                    </a>
                </li>
                <li class="nav-item">
                    <a href="messages.php" class="nav-link">
                        <i class="fas fa-envelope"></i> Messages
                        <?php if ($stats['unread_messages'] > 0): ?>
                        <span class="unread-badge"><?php echo $stats['unread_messages']; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="users.php" class="nav-link">
                        <i class="fas fa-users"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a href="settings.php" class="nav-link">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="../index.php" class="nav-link" target="_blank">
                        <i class="fas fa-external-link-alt"></i> View Website
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Header -->
            <header class="admin-header">
                <h1>Dashboard Overview</h1>
                <div class="user-menu">
                    <div class="user-info">
                        <div class="name"><?php echo $user['full_name']; ?></div>
                        <div class="role"><?php echo ucfirst($user['role']); ?></div>
                    </div>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </header>
            
            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fas fa-blog"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['blog_posts']; ?></h3>
                        <p><?php echo $stats['published_posts']; ?> Published</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['portfolio_projects']; ?></h3>
                        <p>Portfolio Projects</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_messages']; ?></h3>
                        <p><?php echo $stats['unread_messages']; ?> Unread</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-images"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['gallery_images']; ?></h3>
                        <p>Gallery Images</p>
                    </div>
                </div>
            </div>
            
            <!-- Content Grid -->
            <div class="content-grid">
                <!-- Recent Activity -->
                <div class="panel">
                    <div class="panel-header">
                        <h3><i class="fas fa-history"></i> Recent Activity</h3>
                        <a href="activity.php" class="view-all">View All</a>
                    </div>
                    <div class="panel-body">
                        <ul class="activity-list">
                            <?php foreach ($recent_activity as $activity): ?>
                            <li class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-<?php echo getActivityIcon($activity['action']); ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <p><?php echo formatActivity($activity); ?></p>
                                    <span class="time"><?php echo timeAgo($activity['created_at']); ?></span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
                
                <!-- Side Column -->
                <div>
                    <!-- Recent Messages -->
                    <div class="panel" style="margin-bottom: 25px;">
                        <div class="panel-header">
                            <h3><i class="fas fa-inbox"></i> Recent Messages</h3>
                            <a href="messages.php" class="view-all">View All</a>
                        </div>
                        <div class="panel-body">
                            <?php foreach ($recent_messages as $message): ?>
                            <div class="message-item">
                                <div class="message-header">
                                    <h4><?php echo $message['name']; ?></h4>
                                    <span class="time"><?php echo timeAgo($message['created_at']); ?></span>
                                </div>
                                <div class="message-preview"><?php echo $message['subject']; ?></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="quick-actions">
                        <a href="blog-create.php" class="quick-action-btn">
                            <i class="fas fa-plus-circle"></i>
                            <span>New Post</span>
                        </a>
                        <a href="portfolio-create.php" class="quick-action-btn">
                            <i class="fas fa-plus-square"></i>
                            <span>New Project</span>
                        </a>
                        <a href="gallery-upload.php" class="quick-action-btn">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Upload Image</span>
                        </a>
                        <a href="settings.php" class="quick-action-btn">
                            <i class="fas fa-sliders-h"></i>
                            <span>Settings</span>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

<?php
// Helper functions
function getActivityIcon($action) {
    $icons = [
        'login' => 'sign-in-alt',
        'logout' => 'sign-out-alt',
        'create' => 'plus',
        'update' => 'edit',
        'delete' => 'trash',
        'upload' => 'upload',
        'register' => 'user-plus',
        'profile_update' => 'user-edit',
        'password_change' => 'key'
    ];
    return $icons[$action] ?? 'circle';
}

function formatActivity($activity) {
    $user = $activity['username'] ?? 'System';
    $action = $activity['action'];
    $entity = $activity['entity_type'] ?? '';
    
    switch ($action) {
        case 'login':
            return "<strong>$user</strong> logged in";
        case 'logout':
            return "<strong>$user</strong> logged out";
        case 'create':
            return "<strong>$user</strong> created a new $entity";
        case 'update':
            return "<strong>$user</strong> updated a $entity";
        case 'delete':
            return "<strong>$user</strong> deleted a $entity";
        default:
            return "<strong>$user</strong> performed $action";
    }
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' min ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    
    return date('M d, Y', $time);
}
?>
