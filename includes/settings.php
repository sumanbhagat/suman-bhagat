<?php
/**
 * Frontend Settings Helper - Universal Database Integration
 * Loads site settings from database for all frontend pages
 */

// Include universal database helper
require_once __DIR__ . '/database-helper.php';

/**
 * Get all site settings as an associative array
 * @return array
 */
function getSiteSettings() {
    return getSiteSettingsSafe();
}

/**
 * Get specific setting value
 * @param string $key
 * @param string $default
 * @return string
 */
function getSetting($key, $default = '') {
    $settings = getSiteSettings();
    return $settings[$key] ?? $default;
}

/**
 * Get hero slides for homepage
 * @return array
 */
function getHeroSlides() {
    return getHeroSlidesSafe();
}

/**
 * Get about page data
 * @return array
 */
function getAboutData() {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            // Try to get about data from database
            $stmt = $pdo->query("SELECT * FROM about_data WHERE is_active = 1 LIMIT 1");
            $data = $stmt->fetch();
            
            if ($data) {
                return $data;
            }
        } catch (PDOException $e) {
            error_log("About data query error: " . $e->getMessage());
        }
    }
    
    // Return default about data
    return [
        'profile_image' => 'assets/images/placeholder-about.svg',
        'about_text' => 'I\'m a passionate full-stack developer with expertise in creating beautiful, functional web applications. I combine technical skills with creative design to deliver exceptional digital experiences.',
        'skills' => 'PHP, MySQL, JavaScript, HTML5, CSS3, React, Node.js',
        'experience' => '5+ years',
        'projects_completed' => '50+',
        'happy_clients' => '30+'
    ];
}

/**
 * Get resume data
 * @return array
 */
function getResumeData() {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM resume_data WHERE is_active = 1 LIMIT 1");
            $data = $stmt->fetch();
            
            if ($data) {
                return $data;
            }
        } catch (PDOException $e) {
            error_log("Resume data query error: " . $e->getMessage());
        }
    }
    
    // Return default resume data
    return [
        'full_name' => 'Suman Kumar Bhagat',
        'title' => 'Full Stack Developer',
        'email' => 'suman@example.com',
        'phone' => '+1 (555) 123-4567',
        'location' => 'Your City, Country',
        'summary' => 'Experienced full-stack developer with expertise in modern web technologies and a passion for creating exceptional digital experiences.',
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
                'school' => 'University Name',
                'period' => '2016 - 2020'
            ]
        ],
        'skills' => [
            'PHP', 'MySQL', 'JavaScript', 'React', 'Node.js', 'HTML5', 'CSS3', 'Git'
        ]
    ];
}

/**
 * Get blog posts
 * @param int $limit
 * @return array
 */
function getBlogPosts($limit = 6) {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT ?");
            $stmt->execute([$limit]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Blog posts query error: " . $e->getMessage());
        }
    }
    
    // Return default blog posts
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
 * Get portfolio projects
 * @return array
 */
function getPortfolioProjects() {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM portfolio_projects WHERE status = 'active' ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Portfolio projects query error: " . $e->getMessage());
        }
    }
    
    // Return default projects
    return [
        [
            'id' => 1,
            'title' => 'Portfolio Website',
            'slug' => 'portfolio-website',
            'description' => 'A responsive portfolio website built with PHP, MySQL, and modern CSS/JavaScript.',
            'featured_image' => 'assets/images/placeholder-project.svg',
            'technologies' => json_encode(['PHP', 'MySQL', 'HTML5', 'CSS3', 'JavaScript']),
            'created_at' => date('Y-m-d H:i:s')
        ]
    ];
}

/**
 * Get gallery images
 * @param string $category
 * @return array
 */
function getGalleryImages($category = '') {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            if ($category) {
                $stmt = $pdo->prepare("SELECT * FROM gallery_images WHERE category = ? AND is_active = 1 ORDER BY created_at DESC");
                $stmt->execute([$category]);
            } else {
                $stmt = $pdo->query("SELECT * FROM gallery_images WHERE is_active = 1 ORDER BY created_at DESC");
            }
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Gallery images query error: " . $e->getMessage());
        }
    }
    
    // Return default gallery images
    return [
        [
            'id' => 1,
            'title' => 'Sample Image',
            'description' => 'A sample gallery image',
            'image_path' => 'assets/images/placeholder-project.svg',
            'category' => 'general'
        ]
    ];
}

/**
 * Save contact message
 * @param array $data
 * @return bool
 */
function saveContactMessage($data) {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
            return $stmt->execute([
                $data['name'],
                $data['email'],
                $data['subject'],
                $data['message']
            ]);
        } catch (PDOException $e) {
            error_log("Contact message save error: " . $e->getMessage());
            return false;
        }
    }
    
    return false;
}

/**
 * Get contact messages
 * @return array
 */
function getContactMessages() {
    $pdo = getDatabaseConnection();
    
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Contact messages query error: " . $e->getMessage());
        }
    }
    
    return [];
}
?>
