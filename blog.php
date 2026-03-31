<?php include 'includes/header.php'; ?>

<?php
// Get published blog posts using universal database helper
$blog_posts = getBlogPosts(10);
?>

<!-- Blog Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><?php echo htmlspecialchars($site_settings['blog_title'] ?? 'My Blog'); ?></h1>
                <p class="subtitle"><?php echo htmlspecialchars($site_settings['blog_subtitle'] ?? 'Thoughts, Ideas & Knowledge Sharing'); ?></p>
                <p><?php echo htmlspecialchars($site_settings['blog_description'] ?? 'Welcome to my digital notebook where I share insights about web development, design, technology trends, and lessons learned from my journey.'); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- Blog Content -->
<section class="blog-section" style="padding-top: 0;">
    <div class="container">
        <!-- Blog Grid -->
        <div class="blog-grid">
            <?php foreach ($blog_posts as $post): ?>
            <article class="blog-card">
                <div class="blog-image">
                    <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo $post['title']; ?>">
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['created_at'])); ?></span>
                        <span><i class="far fa-clock"></i> <?php echo $post['read_time'] ?? '5 min read'; ?></span>
                    </div>
                    <span style="display: inline-block; background: var(--bg-light); color: var(--primary-color); padding: 4px 12px; border-radius: 15px; font-size: 0.8rem; margin-bottom: 10px;"><?php echo $post['category']; ?></span>
                    <h3><?php echo $post['title']; ?></h3>
                    <p><?php echo $post['excerpt']; ?></p>
                    <a href="#" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Newsletter Section -->
<section style="background: var(--bg-light); padding: 80px 0;">
    <div class="container">
        <div style="max-width: 600px; margin: 0 auto; text-align: center;">
            <h2 style="font-size: 2rem; margin-bottom: 15px;">Subscribe to My Newsletter</h2>
            <p style="color: var(--text-light); margin-bottom: 30px;">Get the latest articles, tutorials, and insights delivered straight to your inbox.</p>
            
            <form style="display: flex; gap: 10px; flex-wrap: wrap; justify-content: center;">
                <input type="email" placeholder="Enter your email" style="flex: 1; min-width: 250px; padding: 14px 20px; border: 2px solid #e2e8f0; border-radius: 50px; font-size: 1rem;">
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </form>
            <p style="font-size: 0.9rem; color: var(--text-light); margin-top: 15px;"><i class="fas fa-lock"></i> No spam, unsubscribe anytime.</p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
