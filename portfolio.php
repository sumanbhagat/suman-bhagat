<?php include 'includes/header.php'; ?>

<?php
// Get portfolio projects using universal database helper
$projects = getPortfolioProjects();
?>
$projects = $stmt->fetchAll();

<!-- Portfolio Hero -->
<section class="hero" style="min-height: 50vh;">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1>My Portfolio</h1>
                <p class="subtitle">Projects I've Worked On</p>
                <p>A collection of my recent work, showcasing my skills in web development, design, and problem-solving. Each project represents a unique challenge and learning experience.</p>
            </div>
        </div>
    </div>
</section>

<!-- Portfolio Grid -->
<section class="portfolio-section">
    <div class="container">
        <div class="portfolio-grid">
            <?php foreach ($projects as $project): ?>
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <img src="<?php echo $project['featured_image'] ?: 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($project['title']); ?>" onerror="this.src='https://via.placeholder.com/400x250/6366f1/ffffff?text=<?php echo urlencode($project['title']); ?>'">
                    <div class="portfolio-overlay">
                        <?php if ($project['project_url']): ?>
                        <a href="<?php echo $project['project_url']; ?>" target="_blank" aria-label="View Project"><i class="fas fa-eye"></i></a>
                        <?php endif; ?>
                        <?php if ($project['github_url']): ?>
                        <a href="<?php echo $project['github_url']; ?>" target="_blank" aria-label="View Code"><i class="fas fa-code"></i></a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="portfolio-info">
                    <h3><?php echo htmlspecialchars($project['title']); ?></h3>
                    <p><?php echo htmlspecialchars($project['description']); ?></p>
                    <div class="portfolio-tags">
                        <?php foreach ($project['technologies'] as $tech): ?>
                        <span><?php echo htmlspecialchars($tech); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php if (empty($projects)): ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 60px; color: #6b7280;">
                <i class="fas fa-briefcase" style="font-size: 4rem; margin-bottom: 20px; display: block;"></i>
                <p>No projects yet. Add some from the portfolio admin!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Achievements Section -->
<section class="highlights">
    <div class="container">
        <div class="section-header">
            <h2>Achievements & <span>Recognition</span></h2>
            <p>Milestones and awards along my journey</p>
        </div>
        
        <div class="highlights-grid">
            <div class="highlight-card">
                <i class="fas fa-trophy"></i>
                <h3>Best Web App 2025</h3>
                <p>Won first place in the Regional Web Development Competition for innovative design.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-certificate"></i>
                <h3>AWS Certified</h3>
                <p>Achieved AWS Solutions Architect certification, demonstrating cloud expertise.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-users"></i>
                <h3>500+ Happy Clients</h3>
                <p>Successfully delivered projects for clients across 25+ countries worldwide.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-star"></i>
                <h3>Top Rated Developer</h3>
                <p>Maintained 5-star rating on freelance platforms with 100% client satisfaction.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
