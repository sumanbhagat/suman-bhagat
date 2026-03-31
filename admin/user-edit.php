<?php
require_once 'includes/auth.php';
require_once 'database/connection.php';

$auth->requireAuth();
$db = getDB();

$user_id = isset($_GET['id']) && is_numeric($_GET['id']) ? $_GET['id'] : $_SESSION['user_id'];

// Only admins can edit other users
if ($user_id != $_SESSION['user_id'] && !$auth->isAdmin()) {
    secureRedirect('users.php');
}

// Get user data
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    secureRedirect('users.php');
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security validation failed';
    } else {
        $action = $_POST['action'] ?? 'profile';
        
        if ($action === 'profile') {
            $full_name = sanitizeInput($_POST['full_name'] ?? '');
            $email = sanitizeInput($_POST['email'] ?? '');
            
            if (empty($full_name) || !validateEmail($email)) {
                $error_message = 'Please fill in all fields correctly';
            } else {
                $stmt = $db->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                if ($stmt->execute([$full_name, $email, $user_id])) {
                    logActivity($_SESSION['user_id'], 'profile_update', 'users', $user_id);
                    $success_message = 'Profile updated successfully';
                    // Refresh user data
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                }
            }
        } elseif ($action === 'password') {
            $current = $_POST['current_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            
            // For admin editing other users, don't require current password
            if ($user_id != $_SESSION['user_id'] && $auth->isAdmin()) {
                if (strlen($new) < 8) {
                    $error_message = 'Password must be at least 8 characters';
                } elseif ($new !== $confirm) {
                    $error_message = 'Passwords do not match';
                } else {
                    $hash = hashPassword($new);
                    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    if ($stmt->execute([$hash, $user_id])) {
                        logActivity($_SESSION['user_id'], 'password_change', 'users', $user_id);
                        $success_message = 'Password updated successfully';
                    }
                }
            } else {
                // Verify current password
                $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $current_hash = $stmt->fetchColumn();
                
                if (!verifyPassword($current, $current_hash)) {
                    $error_message = 'Current password is incorrect';
                } elseif (strlen($new) < 8) {
                    $error_message = 'Password must be at least 8 characters';
                } elseif ($new !== $confirm) {
                    $error_message = 'Passwords do not match';
                } else {
                    $hash = hashPassword($new);
                    $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    if ($stmt->execute([$hash, $user_id])) {
                        logActivity($_SESSION['user_id'], 'password_change', 'users', $user_id);
                        $success_message = 'Password changed successfully';
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Admin</title>
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
        .tabs { display: flex; gap: 10px; margin-bottom: 20px; }
        .tab { padding: 10px 20px; background: white; border-radius: 6px; text-decoration: none; color: #374151; }
        .tab.active { background: #667eea; color: white; }
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); max-width: 600px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .btn-submit { background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; }
        .btn-submit:hover { background: #5a67d8; }
        .btn-cancel { background: #e5e7eb; color: #374151; padding: 12px 30px; border-radius: 6px; text-decoration: none; margin-left: 10px; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
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
                <li class="nav-item"><a href="gallery.php" class="nav-link"><i class="fas fa-images"></i> Gallery</a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link active"><i class="fas fa-users"></i> Users</a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <header class="admin-header">
                <h1>Edit User: <?php echo htmlspecialchars($user['username']); ?></h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="tabs">
                <a href="?id=<?php echo $user_id; ?>&tab=profile" class="tab <?php echo ($_GET['tab'] ?? 'profile') === 'profile' ? 'active' : ''; ?>">Profile</a>
                <a href="?id=<?php echo $user_id; ?>&tab=password" class="tab <?php echo ($_GET['tab'] ?? '') === 'password' ? 'active' : ''; ?>">Password</a>
            </div>
            
            <div class="form-container">
                <?php if (($_GET['tab'] ?? 'profile') === 'profile'): ?>
                <form method="POST">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="action" value="profile">
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                        <a href="users.php" class="btn-cancel">Back</a>
                    </div>
                </form>
                
                <?php else: ?>
                <form method="POST">
                    <?php echo getCSRFTokenField(); ?>
                    <input type="hidden" name="action" value="password">
                    
                    <?php if ($user_id == $_SESSION['user_id']): ?>
                    <div class="form-group">
                        <label>Current Password *</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>New Password *</label>
                        <input type="password" name="new_password" required minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label>Confirm New Password *</label>
                        <input type="password" name="confirm_password" required minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn-submit"><i class="fas fa-key"></i> Change Password</button>
                        <a href="users.php" class="btn-cancel">Back</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>
