<?php
require_once 'includes/auth.php';
require_once 'database/connection.php';

$auth->requireAuth();
$db = getDB();

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    if (validateCSRFToken($_GET['csrf_token'] ?? '')) {
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->execute([$_GET['delete']]);
        logActivity($_SESSION['user_id'], 'delete', 'contact_messages', $_GET['delete']);
    }
}

// Handle mark as read
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    if (validateCSRFToken($_GET['csrf_token'] ?? '')) {
        $stmt = $db->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$_GET['read']]);
    }
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$where = '1=1';
if ($filter === 'unread') $where = 'is_read = 0';
if ($filter === 'read') $where = 'is_read = 1';

// Get messages
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 15;
$offset = ($page - 1) * $per_page;

$stmt = $db->prepare("SELECT * FROM contact_messages WHERE $where ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->execute([$per_page, $offset]);
$messages = $stmt->fetchAll();

// Get total
$stmt = $db->query("SELECT COUNT(*) as total, COALESCE(SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END), 0) as unread FROM contact_messages");
$stats = $stmt->fetch();
$total = $stats['total'] ?? 0;
$unread = $stats['unread'] ?? 0;
$total_pages = ceil($total / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Messages - Admin</title>
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
        .logout-btn { background: #ef4444; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; }
        
        .filters { display: flex; gap: 10px; margin-bottom: 20px; }
        .filters a { padding: 8px 16px; border-radius: 6px; text-decoration: none; color: #374151; background: white; }
        .filters a.active { background: #667eea; color: white; }
        .unread-badge { background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; }
        
        .message-list { background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .message-item { padding: 20px; border-bottom: 1px solid #e5e7eb; }
        .message-item:last-child { border-bottom: none; }
        .message-item.unread { background: #eff6ff; border-left: 4px solid #667eea; }
        .message-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px; }
        .message-sender { display: flex; align-items: center; gap: 10px; }
        .sender-avatar { width: 40px; height: 40px; background: #667eea; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; }
        .sender-info h4 { font-size: 1rem; }
        .sender-info p { font-size: 0.85rem; color: #6b7280; }
        .message-meta { text-align: right; }
        .message-meta .time { font-size: 0.85rem; color: #6b7280; }
        .message-subject { font-weight: 600; color: #374151; margin-bottom: 8px; }
        .message-preview { color: #6b7280; line-height: 1.5; }
        .message-actions { display: flex; gap: 10px; margin-top: 15px; }
        .btn-action { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85rem; }
        .btn-view { background: #dbeafe; color: #1e40af; }
        .btn-delete { background: #fee2e2; color: #991b1b; }
        .btn-read { background: #d1fae5; color: #065f46; }
        
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
        <aside class="sidebar">
            <div class="sidebar-header"><h2><i class="fas fa-shield-alt"></i> Admin Panel</h2></div>
            <ul class="nav-menu">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link"><i class="fas fa-blog"></i> Blog Posts</a></li>
                <li class="nav-item"><a href="portfolio.php" class="nav-link"><i class="fas fa-briefcase"></i> Portfolio</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link"><i class="fas fa-images"></i> Gallery</a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link active"><i class="fas fa-envelope"></i> Messages <?php if ($unread > 0): ?><span class="unread-badge"><?php echo $unread; ?></span><?php endif; ?></a></li>
                <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <header class="admin-header">
                <h1>Contact Messages</h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            
            <div class="filters">
                <a href="?filter=all" class="<?php echo $filter === 'all' ? 'active' : ''; ?>">All (<?php echo $total; ?>)</a>
                <a href="?filter=unread" class="<?php echo $filter === 'unread' ? 'active' : ''; ?>">Unread (<?php echo $unread; ?>)</a>
                <a href="?filter=read" class="<?php echo $filter === 'read' ? 'active' : ''; ?>">Read</a>
            </div>
            
            <div class="message-list">
                <?php foreach ($messages as $msg): ?>
                <div class="message-item <?php echo $msg['is_read'] ? '' : 'unread'; ?>">
                    <div class="message-header">
                        <div class="message-sender">
                            <div class="sender-avatar"><?php echo strtoupper(substr($msg['name'], 0, 1)); ?></div>
                            <div class="sender-info">
                                <h4><?php echo htmlspecialchars($msg['name']); ?></h4>
                                <p><?php echo htmlspecialchars($msg['email']); ?> • <?php echo htmlspecialchars($msg['ip_address'] ?? 'Unknown IP'); ?></p>
                            </div>
                        </div>
                        <div class="message-meta">
                            <div class="time"><?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?></div>
                        </div>
                    </div>
                    <div class="message-subject"><?php echo htmlspecialchars($msg['subject']); ?></div>
                    <div class="message-preview"><?php echo nl2br(htmlspecialchars(substr($msg['message'], 0, 200))); ?><?php echo strlen($msg['message']) > 200 ? '...' : ''; ?></div>
                    <div class="message-actions">
                        <?php if (!$msg['is_read']): ?>
                        <a href="?read=<?php echo $msg['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn-action btn-read"><i class="fas fa-check"></i> Mark as Read</a>
                        <?php endif; ?>
                        <a href="mailto:<?php echo $msg['email']; ?>?subject=Re: <?php echo urlencode($msg['subject']); ?>" class="btn-action btn-view"><i class="fas fa-reply"></i> Reply</a>
                        <a href="?delete=<?php echo $msg['id']; ?>&csrf_token=<?php echo generateCSRFToken(); ?>" class="btn-action btn-delete" onclick="return confirm('Delete this message?');"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <?php if (empty($messages)): ?>
                <div style="text-align: center; padding: 60px; color: #6b7280;">
                    <i class="fas fa-inbox" style="font-size: 4rem; margin-bottom: 20px; display: block;"></i>
                    <p>No messages found</p>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == $page): ?>
                <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                <a href="?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
                <?php endfor; ?>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
