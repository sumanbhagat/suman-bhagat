<?php include 'includes/header.php'; 

// Load hero slides from database
require_once 'admin/database/connection.php';
$db = getDB();

$stmt = $db->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY slide_order ASC, id ASC");
$slides = $stmt->fetchAll();

// Load site settings for this page
$site_settings = getSiteSettings();
$site_author = $site_settings['author_name'] ?? 'Suman Kumar Bhagat';
?>

<!-- Hero Slider Section -->
<section class="hero-slider">
    <?php foreach ($slides as $index => $slide): ?>
    <!-- Slide <?php echo $index + 1; ?> -->
    <div class="slide slide-<?php echo $index + 1; ?> <?php echo $index === 0 ? 'active' : ''; ?>">
        <?php if (!empty($slide['image_path'])): ?>
        <div class="slide-bg" style="background-image: url('<?php echo htmlspecialchars($slide['image_path']); ?>');"></div>
        <?php else: ?>
        <div class="slide-bg"></div>
        <?php endif; ?>
        <div class="slide-overlay"></div>
        <div class="container">
            <div class="slide-content">
                <h1><?php echo htmlspecialchars(str_replace('{name}', $site_author, $slide['title'])); ?></h1>
                <p class="subtitle"><?php echo htmlspecialchars($slide['subtitle']); ?></p>
                <p><?php echo htmlspecialchars($slide['description']); ?></p>
                <div class="hero-buttons">
                    <?php if (!empty($slide['button1_text'])): ?>
                    <a href="<?php echo htmlspecialchars($slide['button1_url']); ?>" class="btn btn-primary"><?php echo htmlspecialchars($slide['button1_text']); ?></a>
                    <?php endif; ?>
                    <?php if (!empty($slide['button2_text'])): ?>
                    <a href="<?php echo htmlspecialchars($slide['button2_url']); ?>" class="btn btn-secondary"><?php echo htmlspecialchars($slide['button2_text']); ?></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
    
    <!-- Slider Navigation -->
    <div class="slider-nav">
        <button class="slider-prev" onclick="changeSlide(-1)">
            <i class="fas fa-chevron-left"></i>
        </button>
        <div class="slider-dots">
            <?php foreach ($slides as $index => $slide): ?>
            <span class="dot <?php echo $index === 0 ? 'active' : ''; ?>" onclick="goToSlide(<?php echo $index; ?>)"></span>
            <?php endforeach; ?>
        </div>
        <button class="slider-next" onclick="changeSlide(1)">
            <i class="fas fa-chevron-right"></i>
        </button>
    </div>
</section>

<!-- About Section -->
<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-text">
                <h2>About Me</h2>
                <p>I'm a passionate full-stack developer with expertise in creating beautiful, functional web applications. I combine technical skills with creative design to deliver exceptional digital experiences.</p>
                <p>With years of experience in modern web technologies, I help businesses and individuals bring their ideas to life through clean code and stunning design.</p>
                <div class="about-buttons">
                    <a href="about.php" class="btn btn-primary">Learn More</a>
                    <a href="portfolio.php" class="btn btn-secondary">View Work</a>
                </div>
            </div>
            <div class="about-image">
                <img src="assets/images/about.jpg" alt="About Me" onerror="this.src='https://via.placeholder.com/400x400/6366f1/ffffff?text=About+Me'">
            </div>
        </div>
    </div>
</section>

<!-- Include Slider CSS -->
<link rel="stylesheet" href="assets/css/slider.css">

<!-- JavaScript for Slider -->
<script>
let currentSlide = 0;
const slides = document.querySelectorAll('.slide');
const dots = document.querySelectorAll('.dot');
const totalSlides = slides.length;

console.log('Total slides found:', totalSlides);

function showSlide(index) {
    if (index >= 0 && index < totalSlides) {
        slides.forEach(slide => slide.classList.remove('active'));
        dots.forEach(dot => dot.classList.remove('active'));
        
        slides[index].classList.add('active');
        dots[index].classList.add('active');
        currentSlide = index;
        
        console.log('Showing slide:', index + 1);
    }
}

function changeSlide(direction) {
    currentSlide += direction;
    if (currentSlide >= totalSlides) currentSlide = 0;
    if (currentSlide < 0) currentSlide = totalSlides - 1;
    showSlide(currentSlide);
}

function goToSlide(index) {
    showSlide(index);
}

// Initialize slider
document.addEventListener('DOMContentLoaded', function() {
    console.log('Slider initialized with', totalSlides, 'slides');
    
    if (totalSlides > 0) {
        showSlide(0); // Show first slide
    }
});

// Auto-advance slider (only if more than 1 slide)
if (totalSlides > 1) {
    setInterval(() => {
        console.log('Auto-advancing to next slide');
        changeSlide(1);
    }, 5000);
}

// Keyboard navigation
document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowLeft') changeSlide(-1);
    if (e.key === 'ArrowRight') changeSlide(1);
});
</script>

<!-- Highlights Section -->
<section class="highlights">
    <div class="container">
        <div class="section-header">
            <h2>What I <span>Do</span></h2>
            <p>Bringing ideas to life through code and creativity</p>
        </div>
        
        <div class="highlights-grid">
            <div class="highlight-card">
                <i class="fas fa-code"></i>
                <h3>Web Development</h3>
                <p>Building responsive, fast, and secure websites using modern technologies and best practices.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-palette"></i>
                <h3>UI/UX Design</h3>
                <p>Creating intuitive and visually appealing user interfaces that enhance user experience.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-mobile-alt"></i>
                <h3>Mobile Apps</h3>
                <p>Developing cross-platform mobile applications that work seamlessly on all devices.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-rocket"></i>
                <h3>Digital Strategy</h3>
                <p>Helping businesses grow with effective digital solutions and marketing strategies.</p>
            </div>
        </div>
    </div>
</section>

<!-- Featured Portfolio Section -->
<section class="featured-portfolio">
    <div class="container">
        <div class="section-header">
            <h2>Featured <span>Projects</span></h2>
            <p>Some of my recent work</p>
        </div>
        
        <div class="portfolio-grid">
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <img src="assets/images/project1.jpg" alt="E-Commerce Platform" onerror="this.src='https://via.placeholder.com/400x250/6366f1/ffffff?text=E-Commerce+Platform'">
                    <div class="portfolio-overlay">
                        <a href="#" aria-label="View Project"><i class="fas fa-eye"></i></a>
                        <a href="#" aria-label="View Code"><i class="fas fa-code"></i></a>
                    </div>
                </div>
                <div class="portfolio-info">
                    <h3>E-Commerce Platform</h3>
                    <p>A full-featured online shopping platform with payment integration.</p>
                    <div class="portfolio-tags">
                        <span>PHP</span>
                        <span>MySQL</span>
                        <span>JavaScript</span>
                    </div>
                </div>
            </div>
            
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <img src="assets/images/project2.jpg" alt="Task Management App" onerror="this.src='https://via.placeholder.com/400x250/ec4899/ffffff?text=Task+Management'">
                    <div class="portfolio-overlay">
                        <a href="#" aria-label="View Project"><i class="fas fa-eye"></i></a>
                        <a href="#" aria-label="View Code"><i class="fas fa-code"></i></a>
                    </div>
                </div>
                <div class="portfolio-info">
                    <h3>Task Management App</h3>
                    <p>Collaborative project management tool for remote teams.</p>
                    <div class="portfolio-tags">
                        <span>React</span>
                        <span>Node.js</span>
                        <span>MongoDB</span>
                    </div>
                </div>
            </div>
            
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <img src="assets/images/project3.jpg" alt="Portfolio Website" onerror="this.src='https://via.placeholder.com/400x250/06b6d4/ffffff?text=Portfolio+Website'">
                    <div class="portfolio-overlay">
                        <a href="#" aria-label="View Project"><i class="fas fa-eye"></i></a>
                        <a href="#" aria-label="View Code"><i class="fas fa-code"></i></a>
                    </div>
                </div>
                <div class="portfolio-info">
                    <h3>Portfolio Website</h3>
                    <p>Creative portfolio website for a professional photographer.</p>
                    <div class="portfolio-tags">
                        <span>HTML/CSS</span>
                        <span>JavaScript</span>
                        <span>Animation</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 40px;">
            <a href="portfolio.php" class="btn btn-secondary">View All Projects</a>
        </div>
    </div>
</section>

<!-- Recent Blog Posts -->
<section class="recent-blog">
    <div class="container">
        <div class="section-header">
            <h2>Latest from <span>Blog</span></h2>
            <p>Thoughts, ideas, and knowledge sharing</p>
        </div>
        
        <div class="blog-grid">
            <article class="blog-card">
                <div class="blog-image">
                    <img src="assets/images/blog1.jpg" alt="Web Development Trends" onerror="this.src='https://via.placeholder.com/400x200/6366f1/ffffff?text=Web+Dev+Trends'">
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> March 10, 2026</span>
                        <span><i class="far fa-clock"></i> 5 min read</span>
                    </div>
                    <h3>Top Web Development Trends in 2026</h3>
                    <p>Exploring the latest technologies and frameworks shaping the future of web development.</p>
                    <a href="blog.php" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
            
            <article class="blog-card">
                <div class="blog-image">
                    <img src="assets/images/blog2.jpg" alt="Design Principles" onerror="this.src='https://via.placeholder.com/400x200/ec4899/ffffff?text=Design+Principles'">
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> March 5, 2026</span>
                        <span><i class="far fa-clock"></i> 4 min read</span>
                    </div>
                    <h3>Essential UI/UX Design Principles</h3>
                    <p>Key principles every designer should know to create user-friendly interfaces.</p>
                    <a href="blog.php" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
            
            <article class="blog-card">
                <div class="blog-image">
                    <img src="assets/images/blog3.jpg" alt="Coding Tips" onerror="this.src='https://via.placeholder.com/400x200/06b6d4/ffffff?text=Coding+Tips'">
                </div>
                <div class="blog-content">
                    <div class="blog-meta">
                        <span><i class="far fa-calendar"></i> February 28, 2026</span>
                        <span><i class="far fa-clock"></i> 6 min read</span>
                    </div>
                    <h3>10 Tips for Writing Clean Code</h3>
                    <p>Best practices to make your code more readable, maintainable, and efficient.</p>
                    <a href="blog.php" class="read-more">Read More <i class="fas fa-arrow-right"></i></a>
                </div>
            </article>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); padding: 80px 0; text-align: center; color: white;">
    <div class="container">
        <h2 style="font-size: 2.5rem; margin-bottom: 20px;">Let's Work Together</h2>
        <p style="font-size: 1.2rem; margin-bottom: 30px; opacity: 0.9;">Have a project in mind? I'd love to hear about it.</p>
        <a href="contact.php" class="btn" style="background: white; color: var(--primary-color);">Start a Conversation</a>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
