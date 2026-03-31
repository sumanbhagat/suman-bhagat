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
            $action = $_POST['action'] ?? '';
            
            if ($action === 'add_slide') {
                $title = sanitizeInput($_POST['title'] ?? '');
                $subtitle = sanitizeInput($_POST['subtitle'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $button1_text = sanitizeInput($_POST['button1_text'] ?? '');
                $button1_url = sanitizeInput($_POST['button1_url'] ?? '');
                $button2_text = sanitizeInput($_POST['button2_text'] ?? '');
                $button2_url = sanitizeInput($_POST['button2_url'] ?? '');
                $slide_order = (int)($_POST['slide_order'] ?? 0);
                
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_name = 'slide_' . time() . '_' . basename($_FILES['slide_image']['name']);
                    $upload_path = $upload_dir . $file_name;
                    
                    // Debug: Log upload attempt
                    error_log("Slider upload attempt: " . $upload_path);
                    
                    if (move_uploaded_file($_FILES['slide_image']['tmp_name'], $upload_path)) {
                        $image_path = 'uploads/images/' . $file_name;
                        error_log("Slider upload success: " . $image_path);
                    } else {
                        error_log("Slider upload failed: move_uploaded_file error");
                    }
                }
                
                $stmt = $db->prepare("INSERT INTO hero_slides (title, subtitle, description, image_path, button1_text, button1_url, button2_text, button2_url, slide_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $subtitle, $description, $image_path, $button1_text, $button1_url, $button2_text, $button2_url, $slide_order]);
                
                logActivity($_SESSION['user_id'], 'create', 'hero_slide');
                $success_message = 'Slide added successfully!';
            }
            
            elseif ($action === 'update_slide') {
                $slide_id = (int)($_POST['slide_id'] ?? 0);
                $title = sanitizeInput($_POST['title'] ?? '');
                $subtitle = sanitizeInput($_POST['subtitle'] ?? '');
                $description = sanitizeInput($_POST['description'] ?? '');
                $button1_text = sanitizeInput($_POST['button1_text'] ?? '');
                $button1_url = sanitizeInput($_POST['button1_url'] ?? '');
                $button2_text = sanitizeInput($_POST['button2_text'] ?? '');
                $button2_url = sanitizeInput($_POST['button2_url'] ?? '');
                $slide_order = (int)($_POST['slide_order'] ?? 0);
                
                // Handle image upload
                $image_path = $_POST['current_image'] ?? '';
                if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = '../uploads/images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_name = 'slide_' . time() . '_' . basename($_FILES['slide_image']['name']);
                    $upload_path = $upload_dir . $file_name;
                    
                    // Debug: Log upload attempt
                    error_log("Slider update attempt: " . $upload_path);
                    
                    if (move_uploaded_file($_FILES['slide_image']['tmp_name'], $upload_path)) {
                        $image_path = 'uploads/images/' . $file_name;
                        error_log("Slider update success: " . $image_path);
                    } else {
                        error_log("Slider update failed: move_uploaded_file error");
                    }
                }
                
                $stmt = $db->prepare("UPDATE hero_slides SET title = ?, subtitle = ?, description = ?, image_path = ?, button1_text = ?, button1_url = ?, button2_text = ?, button2_url = ?, slide_order = ? WHERE id = ?");
                $stmt->execute([$title, $subtitle, $description, $image_path, $button1_text, $button1_url, $button2_text, $button2_url, $slide_order, $slide_id]);
                
                logActivity($_SESSION['user_id'], 'update', 'hero_slide');
                $success_message = 'Slide updated successfully!';
            }
            
            elseif ($action === 'delete_slide') {
                $slide_id = (int)($_POST['slide_id'] ?? 0);
                $stmt = $db->prepare("DELETE FROM hero_slides WHERE id = ?");
                $stmt->execute([$slide_id]);
                
                logActivity($_SESSION['user_id'], 'delete', 'hero_slide');
                $success_message = 'Slide deleted successfully!';
            }
            
        } catch (Exception $e) {
            $error_message = 'Error: ' . $e->getMessage();
        }
    }
}

// Get all slides
$stmt = $db->query("SELECT * FROM hero_slides ORDER BY slide_order ASC, id ASC");
$slides = $stmt->fetchAll();

$page_title = 'Hero Slider';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Admin Panel</title>
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
        
        .slides-container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .slide-item { border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin-bottom: 20px; }
        .slide-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .slide-preview { width: 100px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 0.9rem; }
        .form-group textarea { min-height: 60px; resize: vertical; }
        .btn { padding: 8px 16px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem; text-decoration: none; display: inline-block; }
        .btn-primary { background: #3b82f6; color: white; }
        .btn-success { background: #10b981; color: white; }
        .btn-danger { background: #ef4444; color: white; }
        .btn-sm { padding: 6px 12px; font-size: 0.8rem; }
        .file-upload { border: 2px dashed #d1d5db; padding: 15px; text-align: center; border-radius: 6px; }
        .file-upload:hover { border-color: #3b82f6; }
        .current-image { background: #f0f9ff; padding: 10px; border-radius: 4px; margin-top: 10px; }
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
                <li class="nav-item"><a href="resume-edit.php" class="nav-link"><i class="fas fa-file-alt"></i> Resume</a></li>
                <li class="nav-item"><a href="hero-slider.php" class="nav-link active"><i class="fas fa-images"></i> Hero Slider</a></li>
                <li class="nav-item"><a href="blog.php" class="nav-link"><i class="fas fa-blog"></i> Blog Posts</a></li>
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
                <h1>Hero Slider Management</h1>
                <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </header>
            
            <?php if ($success_message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="slides-container">
                <h2 style="margin-bottom: 20px;">Slides</h2>
                
                <?php foreach ($slides as $slide): ?>
                <div class="slide-item">
                    <form method="POST" enctype="multipart/form-data">
                        <?php echo getCSRFTokenField(); ?>
                        <input type="hidden" name="action" value="update_slide">
                        <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($slide['image_path']); ?>">
                        
                        <div class="slide-header">
                            <h3>Slide <?php echo $slide['slide_order']; ?></h3>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <?php if (!empty($slide['image_path'])): ?>
                                <img src="../<?php echo htmlspecialchars($slide['image_path']); ?>" alt="Slide" class="slide-preview">
                                <?php endif; ?>
                                <div>
                                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $slide['id']; ?>)">Delete</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Order</label>
                            <input type="number" name="slide_order" value="<?php echo $slide['slide_order']; ?>" min="1">
                        </div>
                        
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($slide['title']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Subtitle</label>
                            <input type="text" name="subtitle" value="<?php echo htmlspecialchars($slide['subtitle']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description"><?php echo htmlspecialchars($slide['description']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Slide Image</label>
                            <div class="file-upload">
                                <input type="file" id="slide_image_<?php echo $slide['id']; ?>" name="slide_image" accept="image/*" style="display: none;" onchange="updateSlideImage(this)">
                                <label for="slide_image_<?php echo $slide['id']; ?>" style="cursor: pointer;">
                                    <i class="fas fa-cloud-upload-alt" style="font-size: 1.5rem; color: #3b82f6;"></i>
                                    <p>Click to upload image</p>
                                    <small>JPG, PNG, GIF files</small>
                                </label>
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Button 1 Text</label>
                                <input type="text" name="button1_text" value="<?php echo htmlspecialchars($slide['button1_text']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Button 1 URL</label>
                                <input type="text" name="button1_url" value="<?php echo htmlspecialchars($slide['button1_url']); ?>">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                            <div class="form-group">
                                <label>Button 2 Text</label>
                                <input type="text" name="button2_text" value="<?php echo htmlspecialchars($slide['button2_text']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Button 2 URL</label>
                                <input type="text" name="button2_url" value="<?php echo htmlspecialchars($slide['button2_url']); ?>">
                            </div>
                        </div>
                    </form>
                </div>
                <?php endforeach; ?>
                
                <div style="margin-top: 30px; text-align: center;">
                    <button onclick="showAddForm()" class="btn btn-success"><i class="fas fa-plus"></i> Add New Slide</button>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function updateSlideImage(input) {
            if (input.files.length > 0) {
                const label = input.nextElementSibling;
                label.innerHTML = `
                    <i class="fas fa-file-image" style="font-size: 1.5rem; color: #10b981;"></i>
                    <p>Selected: ${input.files[0].name}</p>
                    <small>Click to change image</small>
                `;
                
                // Show file size for debugging
                console.log('File selected:', input.files[0].name);
                console.log('File size:', input.files[0].size);
                console.log('File type:', input.files[0].type);
            }
        }
        
        function confirmDelete(slideId) {
            if (confirm('Are you sure you want to delete this slide?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_slide">
                    <input type="hidden" name="slide_id" value="${slideId}">
                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Add click handler for file upload labels
        document.addEventListener('DOMContentLoaded', function() {
            const fileLabels = document.querySelectorAll('.file-upload label[for]');
            fileLabels.forEach(function(label) {
                label.addEventListener('click', function(e) {
                    e.preventDefault();
                    const inputId = this.getAttribute('for');
                    const fileInput = document.getElementById(inputId);
                    if (fileInput) {
                        fileInput.click();
                    }
                });
            });
        });
        
        function showAddForm() {
            // This could open a modal or redirect to add form
            alert('Add slide functionality can be implemented with a modal form');
        }
    </script>
</body>
</html>
