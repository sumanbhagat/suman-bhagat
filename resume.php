<?php include 'includes/header.php'; 

// Load resume data from database
$resume_data = getResumeData();
?>

<!-- Resume Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="container">
        <div class="hero-content">
            <?php if (!empty($resume_data['hero_image'])): ?>
            <div class="hero-image" style="text-align: center; margin-bottom: 30px;">
                <img src="<?php echo htmlspecialchars($resume_data['hero_image']); ?>" alt="Resume Hero" style="max-width: 100%; height: auto; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            </div>
            <?php endif; ?>
            <div class="hero-text">
                <h1><?php echo htmlspecialchars($resume_data['hero_title']); ?></h1>
                <p class="subtitle"><?php echo htmlspecialchars($resume_data['hero_subtitle']); ?></p>
                <p><?php echo htmlspecialchars($resume_data['hero_bio']); ?></p>
                <div class="hero-buttons" style="margin-top: 30px;">
                    <?php if (!empty($resume_data['resume_file'])): ?>
                    <a href="<?php echo htmlspecialchars($resume_data['resume_file']); ?>" class="btn btn-primary" download><i class="fas fa-download"></i> Download PDF</a>
                    <?php endif; ?>
                    <a href="contact.php" class="btn btn-secondary">Hire Me</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Resume Content -->
<section class="resume-section" style="padding-top: 0;">
    <div class="container">
        <div class="resume-content">
            <!-- Sidebar -->
            <div class="resume-sidebar">
                <div style="text-align: center; margin-bottom: 30px;">
                    <?php if (!empty($resume_data['profile_photo'])): ?>
                    <img src="<?php echo htmlspecialchars($resume_data['profile_photo']); ?>" alt="<?php echo htmlspecialchars($site_settings['author_name']); ?>" style="width: 150px; height: 150px; border-radius: 50%; border: 4px solid white; margin-bottom: 15px;" onerror="this.src='https://via.placeholder.com/150x150/ffffff/6366f1?text=<?php echo urlencode(substr($site_settings['author_name'], 0, 2)); ?>'">
                    <?php else: ?>
                    <img src="assets/images/profile.jpg" alt="<?php echo htmlspecialchars($site_settings['author_name']); ?>" style="width: 150px; height: 150px; border-radius: 50%; border: 4px solid white; margin-bottom: 15px;" onerror="this.src='https://via.placeholder.com/150x150/ffffff/6366f1?text=<?php echo urlencode(substr($site_settings['author_name'], 0, 2)); ?>'">
                    <?php endif; ?>
                    <h2 style="font-size: 1.5rem;"><?php echo htmlspecialchars($site_settings['author_name']); ?></h2>
                    <p style="opacity: 0.8;">Full Stack Developer</p>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <h4 style="margin-bottom: 15px; color: var(--accent-color);"><i class="fas fa-address-card"></i> Contact</h4>
                    <p style="font-size: 0.9rem; margin-bottom: 10px;"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($site_settings['author_email'] ?? AUTHOR_EMAIL); ?></p>
                    <p style="font-size: 0.9rem; margin-bottom: 10px;"><i class="fas fa-phone"></i> <?php echo htmlspecialchars($site_settings['author_phone'] ?? AUTHOR_PHONE); ?></p>
                    <p style="font-size: 0.9rem;"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($site_settings['contact_address'] ?? 'San Francisco, CA'); ?></p>
                </div>
                
                <div style="margin-bottom: 30px;">
                    <h4 style="margin-bottom: 15px; color: var(--accent-color);"><i class="fas fa-share-alt"></i> Social</h4>
                    <div style="display: flex; gap: 10px;">
                        <?php if (!empty($site_settings['social_linkedin'])): ?>
                            <a href="<?php echo htmlspecialchars($site_settings['social_linkedin']); ?>" style="color: white; font-size: 1.2rem;" target="_blank"><i class="fab fa-linkedin"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_github'])): ?>
                            <a href="<?php echo htmlspecialchars($site_settings['social_github']); ?>" style="color: white; font-size: 1.2rem;" target="_blank"><i class="fab fa-github"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_twitter'])): ?>
                            <a href="<?php echo htmlspecialchars($site_settings['social_twitter']); ?>" style="color: white; font-size: 1.2rem;" target="_blank"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_instagram'])): ?>
                            <a href="<?php echo htmlspecialchars($site_settings['social_instagram']); ?>" style="color: white; font-size: 1.2rem;" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 15px; color: var(--accent-color);"><i class="fas fa-certificate"></i> Certifications</h4>
                    <?php foreach ($resume_data['certifications'] as $cert): ?>
                    <p style="font-size: 0.85rem; margin-bottom: 8px;"><i class="fas fa-check-circle" style="color: var(--accent-color);"></i> <?php echo htmlspecialchars($cert); ?></p>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="resume-main">
                <!-- Summary -->
                <div class="resume-section">
                    <h3><i class="fas fa-user"></i> Professional Summary</h3>
                    <p style="color: var(--text-light); line-height: 1.8;">Results-driven Full Stack Developer with 5+ years of experience in designing, developing, and deploying scalable web applications. Proven expertise in modern JavaScript frameworks, PHP ecosystems, and cloud infrastructure. Passionate about clean code, user experience, and continuous learning. Track record of leading development teams and delivering projects on time and within budget.</p>
                </div>
                
                <!-- Experience -->
                <div class="resume-section">
                    <h3><i class="fas fa-briefcase"></i> Work Experience</h3>
                    <?php 
                    $about_data = getAboutData();
                    foreach ($about_data['experience'] as $job): 
                    ?>
                    <div class="resume-item">
                        <h4><?php echo htmlspecialchars($job['title']); ?></h4>
                        <p class="date" style="color: var(--primary-color); font-weight: 500;"><?php echo htmlspecialchars($job['company']); ?> | <?php echo htmlspecialchars($job['period']); ?></p>
                        <p style="color: var(--text-light);"><?php echo htmlspecialchars($job['description']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Education -->
                <div class="resume-section">
                    <h3><i class="fas fa-graduation-cap"></i> Education</h3>
                    <?php foreach ($about_data['education'] as $edu): ?>
                    <div class="resume-item">
                        <h4><?php echo htmlspecialchars($edu['degree']); ?></h4>
                        <p class="date" style="color: var(--primary-color); font-weight: 500;"><?php echo htmlspecialchars($edu['institution']); ?> | <?php echo htmlspecialchars($edu['period']); ?></p>
                        <p style="color: var(--text-light);"><?php echo htmlspecialchars($edu['description']); ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Skills -->
                <div class="resume-section">
                    <h3><i class="fas fa-tools"></i> Technical Skills</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        <?php foreach ($about_data['skills'] as $skill): ?>
                        <span style="background: var(--bg-light); padding: 8px 15px; border-radius: 20px; font-size: 0.9rem; color: var(--text-dark); border: 1px solid var(--border-color);"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Languages -->
                <div class="resume-section">
                    <h3><i class="fas fa-language"></i> Languages</h3>
                    <div style="display: flex; gap: 30px;">
                        <div>
                            <p><strong>English</strong> - Native</p>
                        </div>
                        <div>
                            <p><strong>Spanish</strong> - Conversational</p>
                        </div>
                        <div>
                            <p><strong>French</strong> - Basic</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
