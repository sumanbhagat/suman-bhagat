<?php
require_once 'includes/auth.php';
require_once 'database/connection.php';

$auth->requireAdmin();
$db = getDB();

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Security validation failed';
    } else {
        // Handle new setting creation
        if (isset($_POST['new_setting_key']) && !empty($_POST['new_setting_key'])) {
            $new_key = sanitizeInput($_POST['new_setting_key']);
            $new_value = sanitizeInput($_POST['new_setting_value'] ?? '');
            $new_type = sanitizeInput($_POST['new_setting_type'] ?? 'text');
            $new_desc = sanitizeInput($_POST['new_setting_desc'] ?? '');
            
            // Check if key already exists
            $stmt = $db->prepare("SELECT COUNT(*) FROM site_settings WHERE setting_key = ?");
            $stmt->execute([$new_key]);
            if ($stmt->fetchColumn() > 0) {
                $error_message = 'Setting key already exists!';
            } else {
                $stmt = $db->prepare("INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
                $stmt->execute([$new_key, $new_value, $new_type, $new_desc]);
                $success_message = 'New setting added successfully!';
            }
        }
        
        // Handle existing settings updates
        $updated = 0;
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $setting_key = substr($key, 8);
                $stmt = $db->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = ?");
                if ($stmt->execute([sanitizeInput($value), $setting_key])) {
                    $updated++;
                }
            }
        }
        
        if ($updated > 0 && empty($success_message)) {
            logActivity($_SESSION['user_id'], 'update', 'site_settings');
            $success_message = 'Settings updated successfully';
        }
    }
}

// Get all settings
$stmt = $db->query("SELECT * FROM site_settings ORDER BY setting_key");
$settings = $stmt->fetchAll();

// Group settings
$grouped = [];
foreach ($settings as $setting) {
    $prefix = explode('_', $setting['setting_key'])[0];
    $grouped[$prefix][] = $setting;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Settings - SUMAN KUMAR BHAGAT</title>
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
        
        .settings-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .setting-group { margin-bottom: 30px; }
        .setting-group h3 { margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e5e7eb; color: #667eea; text-transform: capitalize; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; }
        .form-group input:focus, .form-group textarea:focus { outline: none; border-color: #667eea; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .form-group small { color: #6b7280; font-size: 0.85rem; }
        .btn-submit { background: #667eea; color: white; padding: 12px 30px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        .btn-submit:hover { background: #5a67d8; }
        
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; }
        .alert-error { background: #fee2e2; color: #991b1b; }
        
        .btn-add { background: #10b981; color: white; padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; margin-bottom: 20px; }
        .btn-add:hover { background: #059669; }
        .add-setting-form { background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid #e5e7eb; }
        .add-setting-form h4 { margin-bottom: 15px; color: #374151; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        @media (max-width: 768px) { .form-row { grid-template-columns: 1fr; } }
    </style>
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
                <li class="nav-item"><a href="portfolio.php" class="nav-link"><i class="fas fa-briefcase"></i> Portfolio</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link"><i class="fas fa-images"></i> Gallery</a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link active"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <header class="admin-header">
                <h1>Site Settings</h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="settings-container">
                <!-- Add New Setting Form -->
                <div class="add-setting-form">
                    <h4><i class="fas fa-plus-circle"></i> Add New Setting</h4>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Setting Key (no spaces)</label>
                            <input type="text" name="new_setting_key" placeholder="e.g., custom_message" pattern="[a-z0-9_]+">
                        </div>
                        <div class="form-group">
                            <label>Setting Type</label>
                            <select name="new_setting_type" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px;">
                                <option value="text">Text</option>
                                <option value="textarea">Textarea (multi-line)</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Setting Value</label>
                        <input type="text" name="new_setting_value" placeholder="Enter value">
                    </div>
                    <div class="form-group">
                        <label>Description (optional)</label>
                        <input type="text" name="new_setting_desc" placeholder="What is this setting for?">
                    </div>
                </div>
                
                <form method="POST">
                    <?php echo getCSRFTokenField(); ?>
                    
                    <?php foreach ($grouped as $group => $items): ?>
                    <div class="setting-group">
                        <h3><?php echo ucfirst($group); ?> Settings</h3>
                        
                        <?php foreach ($items as $setting): ?>
                        <div class="form-group">
                            <label>
                                <?php echo ucwords(str_replace('_', ' ', $setting['setting_key'])); ?>
                                <small style="color: #6b7280; font-weight: normal;">(Key: <?php echo $setting['setting_key']; ?>)</small>
                            </label>
                            
                            <?php if ($setting['setting_type'] === 'textarea'): ?>
                            <textarea name="setting_<?php echo $setting['setting_key']; ?>" rows="3"><?php echo htmlspecialchars($setting['setting_value']); ?></textarea>
                            <?php else: ?>
                            <input type="text" name="setting_<?php echo $setting['setting_key']; ?>" value="<?php echo htmlspecialchars($setting['setting_value']); ?>">
                            <?php endif; ?>
                            
                            <?php if ($setting['description']): ?>
                            <small><?php echo $setting['description']; ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save All Settings</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
