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
            $title = sanitizeInput($_POST['title'] ?? '');
            $content = sanitizeInput($_POST['content'] ?? '');
            $skills = json_encode($_POST['skills'] ?? []);
            $experience = json_encode($_POST['experience'] ?? []);
            $education = json_encode($_POST['education'] ?? []);
            
            // Handle profile image upload
            $profile_image = $_POST['current_profile_image'] ?? '';
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/images/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_name = 'profile_' . time() . '_' . basename($_FILES['profile_image']['name']);
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    $profile_image = 'uploads/images/' . $file_name;
                }
            }
            
            $stmt = $db->prepare("UPDATE about_me SET title = ?, content = ?, skills_json = ?, experience_json = ?, education_json = ?, profile_image = ? WHERE id = 1");
            $stmt->execute([$title, $content, $skills, $experience, $education, $profile_image]);
            
            logActivity($_SESSION['user_id'], 'update', 'about_me');
            $success_message = 'About Me page updated successfully!';
        } catch (Exception $e) {
            $error_message = 'Error updating page: ' . $e->getMessage();
        }
    }
}

// Get current data
$stmt = $db->prepare("SELECT * FROM about_me WHERE id = 1");
$stmt->execute();
$about_data = $stmt->fetch(PDO::FETCH_ASSOC);

$skills = json_decode($about_data['skills_json'] ?? '[]', true) ?? [];
$experience = json_decode($about_data['experience_json'] ?? '[]', true) ?? [];
$education = json_decode($about_data['education_json'] ?? '[]', true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit About Me - SUMAN KUMAR BHAGAT</title>
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
        .form-group textarea { min-height: 150px; resize: vertical; }
        .btn-submit { background: #10b981; color: white; padding: 12px 24px; border: none; border-radius: 6px; cursor: pointer; font-size: 1rem; }
        .btn-submit:hover { background: #059669; }
        .btn-add { background: #3b82f6; color: white; padding: 8px 16px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.9rem; margin-bottom: 10px; }
        .dynamic-item { background: #f8fafc; padding: 15px; border-radius: 6px; margin-bottom: 10px; border: 1px solid #e2e8f0; }
        .dynamic-item input { margin-bottom: 8px; }
        .btn-remove { background: #ef4444; color: white; padding: 4px 8px; border: none; border-radius: 4px; cursor: pointer; font-size: 0.8rem; float: right; }
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
                <li class="nav-item"><a href="about-edit.php" class="nav-link active"><i class="fas fa-user"></i> About Me</a></li>
                <li class="nav-item"><a href="resume-edit.php" class="nav-link"><i class="fas fa-file-alt"></i> Resume</a></li>
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
                <h1>Edit About Me Page</h1>
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
                    <input type="hidden" name="current_profile_image" value="<?php echo htmlspecialchars($about_data['profile_image'] ?? ''); ?>">
                    
                    <div class="form-group">
                        <label for="title">Page Title</label>
                        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($about_data['title'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">About Content</label>
                        <textarea id="content" name="content" required><?php echo htmlspecialchars($about_data['content'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Profile Image</label>
                        <div class="file-upload">
                            <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="updateProfileImage(this)">
                            <label for="profile_image" style="cursor: pointer;">
                                <i class="fas fa-cloud-upload-alt" style="font-size: 2rem; color: #3b82f6;"></i>
                                <p>Click to upload image or drag and drop</p>
                                <small>JPG, PNG, GIF files</small>
                            </label>
                        </div>
                        <?php if (!empty($about_data['profile_image'])): ?>
                        <div class="current-file">
                            <strong>Current image:</strong><br>
                            <img src="../<?php echo htmlspecialchars($about_data['profile_image']); ?>" alt="Profile" style="max-width: 200px; height: auto; border-radius: 8px; margin-top: 10px;">
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Skills</label>
                        <div id="skills-container">
                            <?php foreach ($skills as $index => $skill): ?>
                            <div class="dynamic-item">
                                <input type="text" name="skills[]" value="<?php echo htmlspecialchars($skill); ?>" placeholder="Enter skill">
                                <button type="button" class="btn-remove" onclick="removeItem(this)">Remove</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addSkill()"><i class="fas fa-plus"></i> Add Skill</button>
                    </div>
                    
                    <div class="form-group">
                        <label>Experience</label>
                        <div id="experience-container">
                            <?php foreach ($experience as $index => $exp): ?>
                            <div class="dynamic-item">
                                <input type="text" name="experience[<?php echo $index; ?>][title]" value="<?php echo htmlspecialchars($exp['title'] ?? ''); ?>" placeholder="Job Title">
                                <input type="text" name="experience[<?php echo $index; ?>][company]" value="<?php echo htmlspecialchars($exp['company'] ?? ''); ?>" placeholder="Company">
                                <input type="text" name="experience[<?php echo $index; ?>][period]" value="<?php echo htmlspecialchars($exp['period'] ?? ''); ?>" placeholder="Period">
                                <input type="text" name="experience[<?php echo $index; ?>][description]" value="<?php echo htmlspecialchars($exp['description'] ?? ''); ?>" placeholder="Description">
                                <button type="button" class="btn-remove" onclick="removeItem(this)">Remove</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addExperience()"><i class="fas fa-plus"></i> Add Experience</button>
                    </div>
                    
                    <div class="form-group">
                        <label>Education</label>
                        <div id="education-container">
                            <?php foreach ($education as $index => $edu): ?>
                            <div class="dynamic-item">
                                <input type="text" name="education[<?php echo $index; ?>][degree]" value="<?php echo htmlspecialchars($edu['degree'] ?? ''); ?>" placeholder="Degree">
                                <input type="text" name="education[<?php echo $index; ?>][institution]" value="<?php echo htmlspecialchars($edu['institution'] ?? ''); ?>" placeholder="Institution">
                                <input type="text" name="education[<?php echo $index; ?>][period]" value="<?php echo htmlspecialchars($edu['period'] ?? ''); ?>" placeholder="Period">
                                <input type="text" name="education[<?php echo $index; ?>][description]" value="<?php echo htmlspecialchars($edu['description'] ?? ''); ?>" placeholder="Description">
                                <button type="button" class="btn-remove" onclick="removeItem(this)">Remove</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn-add" onclick="addEducation()"><i class="fas fa-plus"></i> Add Education</button>
                    </div>
                    
                    <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Save Changes</button>
                </form>
            </div>
        </main>
    </div>
    
    <script>
        let skillIndex = <?php echo count($skills); ?>;
        let experienceIndex = <?php echo count($experience); ?>;
        let educationIndex = <?php echo count($education); ?>;
        
        function removeItem(button) {
            button.parentElement.remove();
        }
        
        function addSkill() {
            const container = document.getElementById('skills-container');
            const div = document.createElement('div');
            div.className = 'dynamic-item';
            div.innerHTML = `
                <input type="text" name="skills[]" placeholder="Enter skill">
                <button type="button" class="btn-remove" onclick="removeItem(this)">Remove</button>
            `;
            container.appendChild(div);
        }
        
        function addExperience() {
            const container = document.getElementById('experience-container');
            const div = document.createElement('div');
            div.className = 'dynamic-item';
            div.innerHTML = `
                <input type="text" name="experience[${experienceIndex}][title]" placeholder="Job Title">
                <input type="text" name="experience[${experienceIndex}][company]" placeholder="Company">
                <input type="text" name="experience[${experienceIndex}][period]" placeholder="Period">
                <input type="text" name="experience[${experienceIndex}][description]" placeholder="Description">
                <button type="button" class="btn-remove" onclick="removeItem(this)">Remove</button>
            `;
            container.appendChild(div);
            experienceIndex++;
        }
        
        function addEducation() {
            const container = document.getElementById('education-container');
            const div = document.createElement('div');
            div.className = 'dynamic-item';
            div.innerHTML = `
                <input type="text" name="education[${educationIndex}][degree]" placeholder="Degree">
                <input type="text" name="education[${educationIndex}][institution]" placeholder="Institution">
                <input type="text" name="education[${educationIndex}][period]" placeholder="Period">
                <input type="text" name="education[${educationIndex}][description]" placeholder="Description">
                <button type="button" class="btn-remove" onclick="removeItem(this)">Remove</button>
            `;
            container.appendChild(div);
            educationIndex++;
        }
        
        function updateProfileImage(input) {
            if (input.files.length > 0) {
                const label = input.nextElementSibling;
                label.innerHTML = `
                    <i class="fas fa-file-image" style="font-size: 2rem; color: #10b981;"></i>
                    <p>Selected: ${input.files[0].name}</p>
                    <small>Click to change image</small>
                `;
            }
        }
    </script>
</body>
</html>
