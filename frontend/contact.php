<?php include '../backend/config.php'; ?>
<?php include 'includes/header.php'; 

// Load site settings for contact page
$site_settings = getSiteSettings();
?>

<?php
require_once '../backend/admin/database/connection.php';
require_once '../backend/admin/includes/security.php';

$db = getDB();
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $message = sanitizeInput($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error_message = 'Please fill in all required fields.';
    } elseif (!validateEmail($email)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        $ip_address = getClientIP();
        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message, ip_address, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())");
        
        if ($stmt->execute([$name, $email, $subject, $message, $ip_address])) {
            $success_message = 'Thank you for your message! We will get back to you soon.';
        } else {
            $error_message = 'Sorry, something went wrong. Please try again later.';
        }
    }
}
?>

<!-- Contact Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>Get In <span>Touch</span></h1>
                <p class="subtitle">Let's Start a Conversation</p>
                <p>Have a project in mind or just want to say hello? I'd love to hear from you. Fill out the form below or reach out through any of my social channels.</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Content -->
<section class="contact-section" style="padding-top: 0;">
    <div class="container">
        <div class="contact-content">
            <!-- Contact Info -->
            <div class="contact-info-card">
                <h3 style="margin-bottom: 30px;">Contact Information</h3>
                
                <div class="contact-info-item">
                    <i class="fas fa-envelope"></i>
                    <div>
                        <h4>Email</h4>
                        <p><a href="<?php echo htmlspecialchars(getContactInfo('email')['link']); ?>" class="contact-link"><?php echo htmlspecialchars(getContactInfo('email')['display']); ?></a></p>
                    </div>
                </div>
                
                <div class="contact-info-item">
                    <i class="fas fa-phone"></i>
                    <div>
                        <h4>Phone</h4>
                        <p><a href="<?php echo htmlspecialchars(getContactInfo('phone')['link']); ?>" class="contact-link"><?php echo htmlspecialchars(getContactInfo('phone')['display']); ?></a></p>
                    </div>
                </div>
                
                <div class="contact-info-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div>
                        <h4>Location</h4>
                        <p><a href="<?php echo htmlspecialchars(getContactInfo('address')['link']); ?>" target="_blank" class="contact-link"><?php echo nl2br(htmlspecialchars(getContactInfo('address')['display'])); ?></a></p>
                    </div>
                </div>
                
                <div class="contact-info-item">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h4>Working Hours</h4>
                        <p>Mon - Fri: 9:00 AM - 6:00 PM<br>Sat - Sun: Available for urgent matters</p>
                    </div>
                </div>
                
                <div style="margin-top: 40px;">
                    <h4 style="margin-bottom: 20px;">Follow Me</h4>
                    <div class="social-links" style="justify-content: flex-start;">
                        <?php if (!empty($site_settings['social_linkedin'])): ?>
                        <a href="<?php echo htmlspecialchars($site_settings['social_linkedin']); ?>" target="_blank" class="social-link" style="background: var(--primary-color);">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_github'])): ?>
                        <a href="<?php echo htmlspecialchars($site_settings['social_github']); ?>" target="_blank" class="social-link" style="background: var(--bg-dark);">
                            <i class="fab fa-github"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($site_settings['social_twitter']); ?>" target="_blank" class="social-link" style="background: #1da1f2;">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($site_settings['social_instagram']); ?>" target="_blank" class="social-link" style="background: #e4405f;">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form">
                <h3 style="margin-bottom: 30px;">Send Me a Message</h3>
                
                <?php if ($success_message): ?>
                <div style="background: #10b981; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                <div style="background: #ef4444; color: white; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <form id="contactForm" method="POST" action="">
                    <div class="form-group">
                        <label for="name">Your Name *</label>
                        <input type="text" id="name" name="name" placeholder="your name" required value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Your Email *</label>
                        <input type="email" id="email" name="email" placeholder="address@example.com" required value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject *</label>
                        <input type="text" id="subject" name="subject" placeholder="Project Inquiry" required value="<?php echo isset($subject) ? htmlspecialchars($subject) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message *</label>
                        <textarea id="message" name="message" placeholder="Tell me about your project..." required><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Map Section (Placeholder) -->
<section style="background: var(--bg-light); padding: 0;">
    <div style="height: 400px; background: linear-gradient(135deg, #e0e7ff, #c7d2fe); display: flex; align-items: center; justify-content: center;">
        <div style="text-align: center;">
            <i class="fas fa-map-marked-alt" style="font-size: 4rem; color: var(--primary-color); margin-bottom: 20px;"></i>
            <h3>San Francisco, California</h3>
            <p style="color: var(--text-light);">Available for remote work worldwide</p>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section style="padding: 80px 0;">
    <div class="container">
        <div class="section-header">
            <h2>Frequently Asked <span>Questions</span></h2>
            <p>Quick answers to common questions</p>
        </div>
        
        <div style="max-width: 800px; margin: 0 auto;">
            <div style="background: var(--bg-white); padding: 25px; border-radius: var(--radius); margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="margin-bottom: 10px;"><i class="fas fa-question-circle" style="color: var(--primary-color); margin-right: 10px;"></i> What is your typical project timeline?</h4>
                <p style="color: var(--text-light);">Project timelines vary based on complexity. A simple website typically takes 2-4 weeks, while complex web applications may take 2-3 months. I'll provide a detailed timeline during our initial consultation.</p>
            </div>
            
            <div style="background: var(--bg-white); padding: 25px; border-radius: var(--radius); margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="margin-bottom: 10px;"><i class="fas fa-question-circle" style="color: var(--primary-color); margin-right: 10px;"></i> Do you work with international clients?</h4>
                <p style="color: var(--text-light);">Absolutely! I work with clients from all over the world. I'm comfortable with different time zones and use tools like Slack, Zoom, and email to ensure smooth communication.</p>
            </div>
            
            <div style="background: var(--bg-white); padding: 25px; border-radius: var(--radius); margin-bottom: 20px; box-shadow: var(--shadow);">
                <h4 style="margin-bottom: 10px;"><i class="fas fa-question-circle" style="color: var(--primary-color); margin-right: 10px;"></i> What technologies do you specialize in?</h4>
                <p style="color: var(--text-light);">I specialize in PHP, JavaScript (React, Vue, Node.js), Python, and modern web technologies. I'm also experienced with cloud services like AWS and various databases.</p>
            </div>
            
            <div style="background: var(--bg-white); padding: 25px; border-radius: var(--radius); box-shadow: var(--shadow);">
                <h4 style="margin-bottom: 10px;"><i class="fas fa-question-circle" style="color: var(--primary-color); margin-right: 10px;"></i> Do you offer ongoing maintenance?</h4>
                <p style="color: var(--text-light);">Yes! I offer monthly maintenance packages that include security updates, backups, performance optimization, and minor content updates to keep your site running smoothly.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
