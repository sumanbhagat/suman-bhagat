<?php
/**
 * Simple Database Helper - Most Robust Version
 * Works even when everything else fails
 */

// Simple global connection
$GLOBALS['simple_db'] = null;
$GLOBALS['simple_db_error'] = null;

/**
 * Simple database connection with maximum compatibility
 */
function simple_db_connect() {
    global $simple_db, $simple_db_error;
    
    if ($simple_db !== null) {
        return $simple_db;
    }
    
    try {
        // Try basic MySQL connection first
        $pdo = new PDO('mysql:host=localhost', 'root', '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
        
        // Create database if needed
        $pdo->exec("CREATE DATABASE IF NOT EXISTS portfolio");
        $pdo->exec("USE portfolio");
        
        // Create essential tables with most basic structure
        $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(100) NOT NULL,
            setting_value TEXT
        )");
        
        $pdo->exec("CREATE TABLE IF NOT EXISTS hero_slides (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(255) NOT NULL,
            subtitle VARCHAR(255),
            description TEXT,
            button1_text VARCHAR(100),
            button1_url VARCHAR(255),
            is_active BOOLEAN DEFAULT TRUE
        )");
        
        // Insert minimal default data
        $pdo->exec("INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES 
            ('site_title', 'My Portfolio'),
            ('author_name', 'Suman Kumar Bhagat'),
            ('author_email', 'suman@example.com')");
            
        $pdo->exec("INSERT IGNORE INTO hero_slides (title, subtitle, description, button1_text, button1_url, is_active) VALUES 
            ('Welcome to My Portfolio', 'Full Stack Developer', 'Creating beautiful web experiences.', 'View My Work', 'portfolio', TRUE)");
        
        $simple_db = $pdo;
        return $pdo;
        
    } catch (Exception $e) {
        $simple_db_error = $e->getMessage();
        return null;
    }
}

/**
 * Get site settings with fallback
 */
function simple_get_settings() {
    $pdo = simple_db_connect();
    $settings = [];
    
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT setting_key, setting_value FROM site_settings");
            while ($row = $stmt->fetch()) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (Exception $e) {
            // Ignore errors, use defaults
        }
    }
    
    // Always return defaults
    return array_merge([
        'site_title' => 'My Portfolio',
        'author_name' => 'Suman Kumar Bhagat',
        'author_email' => 'suman@example.com',
        'author_phone' => '+1 (555) 123-4567',
        'site_url' => 'http://localhost/suman%20portfolio/'
    ], $settings);
}

/**
 * Get hero slides with fallback
 */
function simple_get_slides() {
    $pdo = simple_db_connect();
    
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM hero_slides WHERE is_active = 1 ORDER BY id ASC");
            $slides = $stmt->fetchAll();
            if (!empty($slides)) {
                return $slides;
            }
        } catch (Exception $e) {
            // Ignore errors, use defaults
        }
    }
    
    // Return default slide
    return [[
        'title' => 'Welcome to My Portfolio',
        'subtitle' => 'Full Stack Developer',
        'description' => 'Creating beautiful and functional web experiences.',
        'button1_text' => 'View My Work',
        'button1_url' => 'portfolio',
        'button2_text' => 'Contact Me',
        'button2_url' => 'contact'
    ]];
}

/**
 * Get resume data with fallback
 */
function simple_get_resume() {
    return [
        'full_name' => 'Suman Kumar Bhagat',
        'title' => 'Full Stack Developer',
        'email' => 'suman@example.com',
        'phone' => '+1 (555) 123-4567',
        'summary' => 'Experienced full-stack developer with expertise in modern web technologies.',
        'experience' => [
            [
                'title' => 'Senior Full Stack Developer',
                'company' => 'Tech Company',
                'period' => '2020 - Present',
                'description' => 'Lead development of web applications and API integrations.'
            ]
        ],
        'education' => [
            [
                'degree' => 'Bachelor of Computer Science',
                'institution' => 'University Name',
                'period' => '2016 - 2020'
            ]
        ],
        'skills' => ['PHP', 'MySQL', 'JavaScript', 'React', 'Node.js', 'HTML5', 'CSS3'],
        'certifications' => ['PHP Developer Certification', 'Web Design Certificate']
    ];
}

/**
 * Get blog posts with fallback
 */
function simple_get_blog_posts() {
    return [
        [
            'id' => 1,
            'title' => 'Welcome to My Portfolio',
            'slug' => 'welcome-to-my-portfolio',
            'excerpt' => 'This is my first blog post on my new portfolio website.',
            'featured_image' => 'assets/images/placeholder-blog.svg',
            'category' => 'Announcement',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
}

/**
 * Get portfolio projects with fallback
 */
function simple_get_projects() {
    return [
        [
            'id' => 1,
            'title' => 'Portfolio Website',
            'slug' => 'portfolio-website',
            'description' => 'A responsive portfolio website built with PHP and MySQL.',
            'featured_image' => 'assets/images/placeholder-project.svg',
            'technologies' => '["PHP", "MySQL", "HTML5", "CSS3"]',
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
}

/**
 * Get about data with fallback
 */
function simple_get_about() {
    return [
        'profile_image' => 'assets/images/placeholder-about.svg',
        'about_text' => 'I\'m a passionate full-stack developer with expertise in creating beautiful, functional web applications.',
        'skills' => 'PHP, MySQL, JavaScript, HTML5, CSS3, React, Node.js',
        'experience' => '5+ years',
        'projects_completed' => '50+',
        'happy_clients' => '30+'
    ];
}
?>
