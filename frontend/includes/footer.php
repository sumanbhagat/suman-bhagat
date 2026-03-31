<?php
// Get site settings if not already loaded
if (!isset($site_settings)) {
    require_once 'settings.php';
    $site_settings = getSiteSettings();
}
$site_author = $site_settings['author_name'] ?? 'Suman Kumar Bhagat';
?>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo htmlspecialchars($site_author); ?></h3>
                    <p><?php echo htmlspecialchars($site_settings['site_description'] ?? 'Creating amazing digital experiences through design and development.'); ?></p>
                    <div class="social-links">
                        <?php if (!empty($site_settings['social_linkedin'])): ?>
                        <a href="<?php echo htmlspecialchars($site_settings['social_linkedin']); ?>" target="_blank" class="social-link" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_github'])): ?>
                        <a href="<?php echo htmlspecialchars($site_settings['social_github']); ?>" target="_blank" class="social-link" aria-label="GitHub">
                            <i class="fab fa-github"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_twitter'])): ?>
                        <a href="<?php echo htmlspecialchars($site_settings['social_twitter']); ?>" target="_blank" class="social-link" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($site_settings['social_instagram'])): ?>
                        <a href="<?php echo htmlspecialchars($site_settings['social_instagram']); ?>" target="_blank" class="social-link" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="<?php echo url('index'); ?>">Home</a></li>
                        <li><a href="<?php echo url('about'); ?>">About Me</a></li>
                        <li><a href="<?php echo url('portfolio'); ?>">Portfolio</a></li>
                        <li><a href="<?php echo url('blog'); ?>">Blog</a></li>
                        <li><a href="<?php echo url('contact'); ?>">Contact</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4>Contact Info</h4>
                    <ul class="contact-info">
                        <li><i class="fas fa-envelope"></i> <a href="<?php echo htmlspecialchars(getContactInfo('email')['link']); ?>" class="contact-link"><?php echo htmlspecialchars(getContactInfo('email')['display']); ?></a></li>
                        <li><i class="fas fa-phone"></i> <a href="<?php echo htmlspecialchars(getContactInfo('phone')['link']); ?>" class="contact-link"><?php echo htmlspecialchars(getContactInfo('phone')['display']); ?></a></li>
                        <li><i class="fas fa-map-marker-alt"></i> <a href="<?php echo htmlspecialchars(getContactInfo('address')['link']); ?>" target="_blank" class="contact-link"><?php echo nl2br(htmlspecialchars(getContactInfo('address')['display'])); ?></a></li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p><?php echo htmlspecialchars($site_settings['footer_copyright'] ?? ('&copy; ' . date('Y') . ' ' . ($site_settings['author_name'] ?? $site_author) . '. All rights reserved.')); ?></p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
