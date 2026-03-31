<?php include 'includes/header.php'; ?>

<?php
require_once 'admin/database/connection.php';

$db = getDB();

// Get published blog posts
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$per_page = 6;
$offset = ($page - 1) * $per_page;

$category_filter = $_GET['category'] ?? '';

$sql = "SELECT bp.*, u.full_name as author_name FROM blog_posts bp LEFT JOIN users u ON bp.author_id = u.id WHERE bp.status = 'published'";
$params = [];

if ($category_filter) {
    $sql .= " AND bp.category = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY bp.published_at DESC LIMIT ? OFFSET ?";
$params[] = $per_page;
$params[] = $offset;

$stmt = $db->prepare($sql);
$stmt->execute($params);
$blog_posts = $stmt->fetchAll();

// Get total count for pagination
$count_sql = "SELECT COUNT(*) FROM blog_posts WHERE status = 'published'" . ($category_filter ? " AND category = ?" : '');
$stmt = $db->prepare($count_sql);
$stmt->execute($category_filter ? [$category_filter] : []);
$total_posts = $stmt->fetchColumn();
$total_pages = ceil($total_posts / $per_page);

// Get unique categories
$stmt = $db->query("SELECT DISTINCT category FROM blog_posts WHERE status = 'published' ORDER BY category");
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
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
        <!-- Categories -->
        <div style="text-align: center; margin-bottom: 40px;">
            <span class="skill-tag" style="cursor: pointer; display: inline-block; margin: 5px;">All</span>
            <?php foreach ($categories as $category): ?>
            <span class="skill-tag" style="cursor: pointer; display: inline-block; margin: 5px; background: var(--bg-light); color: var(--text-dark);"><?php echo $category; ?></span>
            <?php endforeach; ?>
        </div>
        
        <!-- Blog Grid -->
        <div class="blog-grid">
            <?php foreach ($blog_posts as $post): ?>
            <article class="blog-card">
                <div class="blog-image">
                    <img src="<?php echo $post['featured_image']; ?>" alt="<?php echo $post['title']; ?>" onerror="this.src='https://via.placeholder.com/400x200/6366f1/ffffff?text=<?php echo urlencode($post['title']); ?>'">
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> <?php echo date('M d, Y', strtotime($post['published_at'] ?? $post['created_at'])); ?></span>
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
        
        <!-- Pagination -->
        <div style="text-align: center; margin-top: 50px;">
            <a href="#" class="btn btn-secondary" style="margin: 0 5px;"><i class="fas fa-chevron-left"></i> Previous</a>
            <a href="#" class="btn btn-primary" style="margin: 0 5px;">1</a>
            <a href="#" class="btn btn-secondary" style="margin: 0 5px;">2</a>
            <a href="#" class="btn btn-secondary" style="margin: 0 5px;">3</a>
            <a href="#" class="btn btn-secondary" style="margin: 0 5px;">Next <i class="fas fa-chevron-right"></i></a>
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
