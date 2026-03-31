<?php include 'includes/header.php'; 

// Load about data from database
$about_data = getAboutData();
?>

<!-- About Hero -->
<section class="hero" style="min-height: 60vh;">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1><?php echo htmlspecialchars($about_data['title']); ?></h1>
                <p class="subtitle">Passionate Developer & Lifelong Learner</p>
                <p><?php echo htmlspecialchars($about_data['content']); ?></p>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="about" style="padding-top: 0;">
    <div class="container">
        <div class="about-content">
            <div class="about-image">
                <?php if (!empty($about_data['profile_image'])): ?>
                <img src="<?php echo htmlspecialchars($about_data['profile_image']); ?>" alt="About Me" onerror="this.src='https://via.placeholder.com/500x600/6366f1/ffffff?text=About+Me'">
                <?php else: ?>
                <img src="assets/images/placeholder-about.svg" alt="About Me">
                <?php endif; ?>
            </div>
            
            <div class="about-text">
                <h3>My Story</h3>
                <p>Hello! I'm <?php echo htmlspecialchars($site_author); ?>, a passionate full-stack developer with expertise in creating amazing digital experiences.</p>
                
                <p>I believe in the power of technology to transform businesses and improve lives. Whether it's crafting a beautiful user interface or architecting complex backend systems, I approach every project with creativity, dedication, and attention to detail.</p>
                
                <p>When I'm not coding, you'll find me exploring new technologies, contributing to open-source projects, or enjoying outdoor activities. I'm always eager to learn and grow, both personally and professionally.</p>
                
                <div class="skills">
                    <h4>Technical Skills</h4>
                    <div class="skill-tags">
                        <?php foreach ($about_data['skills'] as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Education & Experience -->
<section class="highlights" style="padding-top: 50px;">
    <div class="container">
        <div class="section-header">
            <h2>Education & <span>Experience</span></h2>
            <p>My academic background and professional journey</p>
        </div>
        
        <div class="highlights-grid" style="grid-template-columns: repeat(2, 1fr);">
            <!-- Education -->
            <div class="highlight-card" style="text-align: left;">
                <i class="fas fa-graduation-cap"></i>
                <h3>Education</h3>
                
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 5px;">Master of Computer Science</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 10px;">Stanford University | 2018 - 2020</p>
                    <p>Specialized in Artificial Intelligence and Software Engineering. Graduated with honors.</p>
                </div>
                
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 5px;">Bachelor of Science in IT</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 10px;">MIT | 2014 - 2018</p>
                    <p>Dean's List recipient. Active member of the Computer Science Society.</p>
                </div>
            </div>
            
            <!-- Work Experience -->
            <div class="highlight-card" style="text-align: left;">
                <i class="fas fa-briefcase"></i>
                <h3>Work Experience</h3>
                
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 5px;">Senior Full Stack Developer</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 10px;">Tech Solutions Inc. | 2022 - Present</p>
                    <p>Leading a team of 5 developers. Delivered 20+ successful projects for Fortune 500 clients.</p>
                </div>
                
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 5px;">Web Developer</h4>
                    <p style="color: var(--text-light); font-size: 0.9rem; margin-bottom: 10px;">Digital Agency XYZ | 2020 - 2022</p>
                    <p>Developed responsive websites and e-commerce solutions. Increased client satisfaction by 40%.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Interests & Hobbies -->
<section class="featured-portfolio" style="padding: 80px 0;">
    <div class="container">
        <div class="section-header">
            <h2>Interests & <span>Hobbies</span></h2>
            <p>What I enjoy outside of coding</p>
        </div>
        
        <div class="highlights-grid">
            <div class="highlight-card">
                <i class="fas fa-book"></i>
                <h3>Reading</h3>
                <p>Tech blogs, science fiction, and self-improvement books. Currently reading "Clean Architecture".</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-hiking"></i>
                <h3>Hiking</h3>
                <p>Exploring nature trails and mountains. Completed 3 half-marathons last year.</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-camera"></i>
                <h3>Photography</h3>
                <p>Capturing moments and landscapes. Check out my gallery for some of my work!</p>
            </div>
            
            <div class="highlight-card">
                <i class="fas fa-gamepad"></i>
                <h3>Gaming</h3>
                <p>Strategy and puzzle games. Love the challenge of problem-solving in different contexts.</p>
            </div>
        </div>
    </div>
</section>

<?php include '../backend/admin/includes/functions.php'; ?>
<?php include 'includes/footer.php'; ?>
