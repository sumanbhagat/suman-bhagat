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
        try {
            $hero_title = sanitizeInput($_POST['hero_title'] ?? '');
            $hero_subtitle = sanitizeInput($_POST['hero_subtitle'] ?? '');
            $hero_bio = sanitizeInput($_POST['hero_bio'] ?? '');
            $certifications = json_encode($_POST['certifications'] ?? []);
            
            // Handle resume file upload
            $resume_file = $_POST['current_resume_file'] ?? '';
            if (isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/files/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = 'resume_' . time() . '_' . basename($_FILES['resume_file']['name']);
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['resume_file']['tmp_name'], $upload_path)) {
                    $resume_file = 'uploads/files/' . $file_name;
                }
            }
            
            // Handle hero image upload
            $hero_image = $_POST['current_hero_image'] ?? '';
            if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = 'hero_' . time() . '_' . basename($_FILES['hero_image']['name']);
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $upload_path)) {
                    $hero_image = 'uploads/images/' . $file_name;
                }
            }
            
            // Handle profile photo upload
            $profile_photo = $_POST['current_profile_photo'] ?? '';
            if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = 'profile_' . time() . '_' . basename($_FILES['profile_photo']['name']);
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                    $profile_photo = 'uploads/images/' . $file_name;
                }
            }
            
            $stmt = $db->prepare("UPDATE resume_content SET hero_title = ?, hero_subtitle = ?, hero_bio = ?, certifications_json = ?, resume_file = ?, hero_image = ?, profile_photo = ? WHERE id = 1");
            $stmt->execute([$hero_title, $hero_subtitle, $hero_bio, $certifications, $resume_file, $hero_image, $profile_photo]);
            
            logActivity($_SESSION['user_id'], 'update', 'resume_content');
            $success_message = 'Resume page updated successfully!';
        } catch (Exception $e) {
            $error_message = 'Error updating page: ' . $e->getMessage();
        }
    }
}

// Get current data
$stmt = $db->prepare("SELECT * FROM resume_content WHERE id = 1");
$stmt->execute();
$resume_data = $stmt->fetch(PDO::FETCH_ASSOC);

$certifications = json_decode($resume_data['certifications_json'] ?? '[]', true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resume - SUMAN KUMAR BHAGAT</title>
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
        
        .form-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; }
        .form-group textarea { min-height: 100px; resize: vertical; }
        .btn-submit { background: #10b981; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        .btn-submit:hover { background: #059669; }
        .btn-add { background: #3b82f6; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; margin-bottom: 10px; }
        .dynamic-item { background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 10px; border: 1px solid #e2e8f0; }
        .dynamic-item input { margin-bottom: 8px; }
        .btn-remove { background: #ef4444; color: white; padding: 4px 8px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; float: right; }
        .file-upload { border: 2px dashed #d1d5db; padding: 20px; text-align: center; border-radius: 6px; }
        .file-upload:hover { border-color: #3b82f6; }
        .current-file { background: #f0f9ff; padding: 10px; border-radius: 4px; margin-top: 10px; }
        .alert { padding: 15px; border-radius: 6px; margin-bottom: 20px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #10b981; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #ef4444; }
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
                <li class="nav-item"><a href="resume-edit.php" class="nav-link active"><i class="fas fa-file-alt"></i> Resume</a></li>
                <li class="nav-item"><a href="portfolio.php" class="nav-link"><i class="fas fa-briefcase"></i> Portfolio</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link"><i class="fas fa-blog"></i> Blog Posts</a></li>
                <li class="nav-item"><a href="gallery.php" class="nav-link"><i class="fas fa-images"></i> Gallery</a></li>
                <li class="nav-item"><a href="messages.php" class="nav-link"><i class="fas fa-envelope"></i> Messages</a></li>
                <li class="nav-item"><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Users</a></li>
                <li class="nav-item"><a href="settings.php" class="nav-link"><i class="fas fa-cog"></i> Settings</a></li>
                <li class="nav-item"><a href="../index.php" class="nav-link" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a></li>
            </ul>
        </aside>
        
        <main class="main-content">
            <header class="admin-header">
                <h1>Edit Resume Page</h1>
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
                    <input type="hidden" name="current_resume_file" value="<?php echo htmlspecialchars($resume_data['resume_file'] ?? ''); ?>">
                    <input type="hidden" name="current_hero_image" value="<?php echo htmlspecialchars($resume_data['hero_image'] ?? ''); ?>">
                    <input type="hidden" name="current_profile_photo" value="<?php echo htmlspecialchars($resume_data['profile_photo'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label for="hero_title">Hero Title</label>
                        <input type="text" id="hero_title" name="hero_title" value="<?php echo htmlspecialchars($resume_data['hero_title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="hero_subtitle">Hero Subtitle</label>
                        <input type="text" id="hero_subtitle" name="hero_subtitle" value="<?php echo htmlspecialchars($resume_data['hero_subtitle'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="hero_bio">Hero Bio</label>
                        <textarea id="hero_bio" name="hero_bio"><?php echo htmlspecialchars($resume_data['hero_bio'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Profile Photo</label>
                        <div class="file-upload">
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*" style="display: none;" onchange="updateProfilePhoto(this)">
                            <label for="profile_photo" style="cursor: pointer;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #3b82f6;"></i>
                                <p>Click to upload photo or drag and drop</p>
                                <small>JPG, PNG, GIF files (Recommended: 150x150px)</small>
                            </label>
                        </div>
                        <?php if (!empty($resume_data['profile_photo'])): ?>
                        <div class="current-file">
                            <strong>Current photo:</strong><br>
                            <img src="../<?php echo htmlspecialchars($resume_data['profile_photo']); ?>" alt="Profile" style="max-width: 150px; height: auto; border-radius: 8px; margin-top: 10px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Hero Image</label>
                        <div class="file-upload">
                            <input type="file" id="hero_image" name="hero_image" accept="image/*" style="display: none;" onchange="updateHeroImage(this)">
                            <label for="hero_image" style="cursor: pointer;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #3b82f6;"></i>
                                <p>Click to upload image or drag and drop</p>
                                <small>JPG, PNG, GIF files</small>
                            </label>
                        </div>
                        <?php if (!empty($resume_data['hero_image'])): ?>
                        <div class="current-file">
                            <strong>Current image:</strong><br>
                            <img src="../<?php echo htmlspecialchars($resume_data['hero_image']); ?>" alt="Hero" style="max-width: 300px; height: auto; border-radius: 8px; margin-top: 10px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Resume PDF File</label>
                        <div class="file-upload">
                            <input type="file" id="resume_file" name="resume_file" accept=".pdf" style="display: none;" onchange="updateFileName(this)">
                            <label for="resume_file" style="cursor: pointer;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #3b82f6;"></i>
                                <p>Click to upload PDF or drag and drop</p>
                                <small>PDF files only</small>
                            </label>
                        </div>
                        <?php if (!empty($resume_data['resume_file'])): ?>
                        <div class="current-file">
                            <strong>Current file:</strong> <a href="../<?php echo htmlspecialchars($resume_data['resume_file']); ?>" target="_blank"><?php echo htmlspecialchars(basename($resume_data['resume_file'])); ?></a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Certifications</label>
                        <div id="certifications-container">
                            <?php foreach ($certifications as $index => $cert): ?>
                            <div class="dynamic-item">
                                <input type="text" name="certifications[]" value="<?php echo htmlspecialchars($cert); ?>" placeholder="Enter certification">
                                <button type="button" class="btn-remove" onclick="removeItem(this)">Remove</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addCertification()"><i class="fas fa-plus"></i> Add Certification</button>
                    </div>
                    
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        let certificationIndex = <?php echo count($certifications); ?>;
        
        function removeItem(button) {
            button.parentElement.remove();
        }
        
        function addCertification() {
            const container = document.getElementById('certifications-container');
            const div = document.createElement('div');
            div.className = 'dynamic-item';
            div.innerHTML = `
                <input type="text" name="certifications[]" placeholder="Enter certification">
                <button type="button" class="btn-remove" onclick="removeItem(this)">Remove</button>
            `;
            container.appendChild(div);
        }
        
        function updateFileName(input) {
            if (input.files.length > 0) {
                const label = input.nextElementSibling;
                label.innerHTML = `
                    <i class="fas fa-file-pdf" style="font-size: 2rem; color: #10b981;"></i>
                    <p>Selected: ${input.files[0].name}</p>
                    <small>Click to change file</small>
                `;
            }
        }
        
        function updateHeroImage(input) {
            if (input.files.length > 0) {
                const label = input.nextElementSibling;
                label.innerHTML = `
                    <i class="fas fa-file-image" style="font-size: 2rem; color: #10b981;"></i>
                    <p>Selected: ${input.files[0].name}</p>
                    <small>Click to change image</small>
                `;
            }
        }
        
        function updateProfilePhoto(input) {
            if (input.files.length > 0) {
                const label = input.nextElementSibling;
                label.innerHTML = `
                    <i class="fas fa-file-image" style="font-size: 2rem; color: #10b981;"></i>
                    <p>Selected: ${input.files[0].name}</p>
                    <small>Click to change photo</small>
                `;
            }
        }
    </script>
</body>
</html>
