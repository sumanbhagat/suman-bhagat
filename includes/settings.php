<?php
/**
 * Frontend Settings Helper
 * Loads site settings from database for frontend pages
 */

// Global settings cache
$GLOBALS['site_settings'] = null;

/**
 * Get all site settings as an associative array
 * @return array
 */
function getSiteSettings() {
    if ($GLOBALS['site_settings'] !== null) {
        return $GLOBALS['site_settings'];
    }
    
    $settings = [];
    
    try {
        require_once __DIR__ . '/../admin/database/connection.php';
        $db = getDB();
        
        $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    } catch (Exception $e) {
        // Database not available, use defaults
    }
    
    // Set defaults if not present
    $defaults = [
        'site_title' => 'Portfolio',
        'site_description' => 'Welcome to my portfolio website',
        'site_author' => 'Suman Kumar Bhagat',
        'author_name' => 'Suman Kumar Bhagat',
        'author_email' => 'contact@example.com',
        'author_phone' => '',
        'contact_address' => '',
        'social_github' => '',
        'social_linkedin' => '',
        'social_twitter' => '',
        'social_instagram' => '',
        'footer_copyright' => date('Y') . ' All rights reserved.',
        'blog_title' => 'My Blog',
        'blog_subtitle' => 'Thoughts, Ideas & Knowledge Sharing',
        'blog_description' => 'Welcome to my digital notebook where I share insights about web development, design, technology trends, and lessons learned from my journey.'
    ];
    
    foreach ($defaults as $key => $value) {
        if (!isset($settings[$key]) || empty($settings[$key])) {
            $settings[$key] = $value;
        }
    }
    
    $GLOBALS['site_settings'] = $settings;
    return $settings;
}

/**
 * Get a specific setting value
 * @param string $key
 * @param string $default
 * @return string
 */
function getSetting($key, $default = '') {
    $settings = getSiteSettings();
    return $settings[$key] ?? $default;
}

/**
 * Get dynamic contact information with fallbacks
 * @param string $type
 * @return array
 */
function getContactInfo($type = '') {
    $settings = getSiteSettings();
    
    $contact = [
        'email' => [
            'value' => !empty($settings['author_email']) ? $settings['author_email'] : 'contact@example.com',
            'link' => 'mailto:' . (!empty($settings['author_email']) ? $settings['author_email'] : 'contact@example.com'),
            'display' => !empty($settings['author_email']) ? $settings['author_email'] : 'contact@example.com'
        ],
        'phone' => [
            'value' => !empty($settings['author_phone']) ? $settings['author_phone'] : '+1 (555) 123-4567',
            'link' => 'tel:' . preg_replace('/[^0-9+]/', '', !empty($settings['author_phone']) ? $settings['author_phone'] : '+15551234567'),
            'display' => !empty($settings['author_phone']) ? $settings['author_phone'] : '+1 (555) 123-4567'
        ],
        'address' => [
            'value' => !empty($settings['contact_address']) ? $settings['contact_address'] : "Gauradaha-2,Jhapa, Nepal",
            'link' => 'https://maps.google.com/?q=' . urlencode(!empty($settings['contact_address']) ? $settings['contact_address'] : 'San Francisco, CA'),
            'display' => !empty($settings['contact_address']) ? $settings['contact_address'] : "Gauradaha-2,Jhapa, Nepal"
        ]
    ];
    
    return $type ? ($contact[$type] ?? []) : $contact;
}

/**
 * Get About Me page data
 * @return array
 */
function getAboutData() {
    static $about_data = null;
    
    if ($about_data === null) {
        try {
            require_once __DIR__ . '/../admin/database/connection.php';
            $db = getDB();
            
            $stmt = $db->prepare("SELECT * FROM about_me WHERE id = 1");
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                $about_data = [
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'profile_image' => $data['profile_image'],
                    'skills' => json_decode($data['skills_json'] ?? '[]', true) ?? [],
                    'experience' => json_decode($data['experience_json'] ?? '[]', true) ?? [],
                    'education' => json_decode($data['education_json'] ?? '[]', true) ?? []
                ];
            }
        } catch (Exception $e) {
            // Fallback data
            $about_data = [
                'title' => 'About Me',
                'content' => 'I am a passionate Full Stack Developer with expertise in creating amazing digital experiences.',
                'profile_image' => '',
                'skills' => ['PHP', 'JavaScript', 'HTML/CSS', 'MySQL'],
                'experience' => [],
                'education' => []
            ];
        }
    }
    
    return $about_data;
}

/**
 * Get Resume page data
 * @return array
 */
function getResumeData() {
    static $resume_data = null;
    
    if ($resume_data === null) {
        try {
            require_once __DIR__ . '/../admin/database/connection.php';
            $db = getDB();
            
            $stmt = $db->prepare("SELECT * FROM resume_content WHERE id = 1");
            $stmt->execute();
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($data) {
                $resume_data = [
                    'hero_title' => $data['hero_title'],
                    'hero_subtitle' => $data['hero_subtitle'],
                    'hero_bio' => $data['hero_bio'],
                    'hero_image' => $data['hero_image'],
                    'profile_photo' => $data['profile_photo'],
                    'certifications' => json_decode($data['certifications_json'] ?? '[]', true) ?? [],
                    'resume_file' => $data['resume_file'] ?? ''
                ];
            }
        } catch (Exception $e) {
            // Fallback data
            $resume_data = [
                'hero_title' => 'My Resume',
                'hero_subtitle' => 'Professional Experience & Qualifications',
                'hero_bio' => 'Download my full resume or view my qualifications below.',
                'hero_image' => '',
                'profile_photo' => '',
                'certifications' => [],
                'resume_file' => ''
            ];
        }
    }
    
    return $resume_data;
}
