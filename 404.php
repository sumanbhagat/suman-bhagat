<?php include 'includes/header.php'; ?>

<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="hero-content">
            <h1>404 - Page Not Found</h1>
            <p class="subtitle">Oops! The page you're looking for doesn't exist.</p>
            <p>Let's get you back to where you belong.</p>
            <div style="margin-top: 30px;">
                <a href="index.php" class="btn btn-primary">Go Home</a>
                <a href="portfolio.php" class="btn btn-secondary">View Portfolio</a>
            </div>
        </div>
    </div>
</section>

<section class="highlights">
    <div class="container">
        <div class="section-header">
            <h2>Popular <span>Pages</span></h2>
            <p>Maybe you were looking for one of these?</p>
        </div>
        
        <div class="highlights-grid">
            <div class="highlight-card">
                <i class="fas fa-user"></i>
                <h3><a href="about.php" style="color: inherit; text-decoration: none;">About Me</a></h3>
                <p>Learn more about my background and skills.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-briefcase"></i>
                <h3><a href="portfolio.php" style="color: inherit; text-decoration: none;">Portfolio</a></h3>
                <p>Check out my recent projects and work.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-blog"></i>
                <h3><a href="blog.php" style="color: inherit; text-decoration: none;">Blog</a></h3>
                <p>Read my latest thoughts and articles.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-envelope"></i>
                <h3><a href="contact.php" style="color: inherit; text-decoration: none;">Contact</a></h3>
                <p>Get in touch with me for any inquiries.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
