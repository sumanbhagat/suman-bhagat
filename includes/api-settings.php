<?php
/**
 * Frontend API Settings Helper for Vercel
 * Loads site settings from API endpoints for frontend pages
 */

// Global settings cache
$GLOBALS['site_settings'] = null;

/**
 * Get all site settings from API
 * @return array
 */
function getSiteSettings() {
    if ($GLOBALS['site_settings'] !== null) {
        return $GLOBALS['site_settings'];
    }
    
    $settings = [];
    
    try {
        // Try to get settings from API
        $api_url = '/api/database/site-settings';
        
        // Use cURL to fetch from API
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $api_settings = json_decode($response, true);
            if (is_array($api_settings)) {
                $settings = $api_settings;
            }
        }
    } catch (Exception $e) {
        // API not available, use defaults
    }
    
    // Set defaults if not present
    $defaults = [
        'site_title' => $_ENV['SITE_NAME'] ?? 'My Portfolio',
        'author_name' => $_ENV['AUTHOR_NAME'] ?? 'John Doe',
        'author_email' => $_ENV['AUTHOR_EMAIL'] ?? 'john.doe@example.com',
        'author_phone' => $_ENV['AUTHOR_PHONE'] ?? '+1 (555) 123-4567',
        'site_description' => 'Professional portfolio website',
        'site_keywords' => 'portfolio, web developer, designer',
        'social_links' => json_encode([
            'linkedin' => 'https://linkedin.com/in/johndoe',
            'github' => 'https://github.com/johndoe',
            'twitter' => 'https://twitter.com/johndoe'
        ]),
        'theme_color' => '#6366f1',
        'secondary_color' => '#ec4899'
    ];
    
    // Merge with defaults
    foreach ($defaults as $key => $value) {
        if (!isset($settings[$key])) {
            $settings[$key] = $value;
        }
    }
    
    // Cache the settings
    $GLOBALS['site_settings'] = $settings;
    
    return $settings;
}

/**
 * Get hero slides from API
 * @return array
 */
function getHeroSlides() {
    try {
        $api_url = '/api/database/hero-slides';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $slides = json_decode($response, true);
            if (is_array($slides)) {
                return $slides;
            }
        }
    } catch (Exception $e) {
        // API not available
    }
    
    // Return default slide
    return [
        [
            'id' => 1,
            'title' => 'Welcome to My Portfolio',
            'subtitle' => 'Web Developer & Designer',
            'description' => 'Creating beautiful and functional web experiences',
            'button1_text' => 'View My Work',
            'button1_url' => '/portfolio',
            'button2_text' => 'Contact Me',
            'button2_url' => '/contact',
            'image_path' => null
        ]
    ];
}

/**
 * Get blog posts from API
 * @param int $page
 * @param string $category
 * @return array
 */
function getBlogPosts($page = 1, $category = '') {
    try {
        $api_url = "/api/blog?page={$page}";
        if (!empty($category)) {
            $api_url .= "&category=" . urlencode($category);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['posts'])) {
                return $result;
            }
        }
    } catch (Exception $e) {
        // API not available
    }
    
    return [
        'posts' => [],
        'pagination' => [
            'current_page' => 1,
            'per_page' => 6,
            'total_posts' => 0,
            'total_pages' => 0
        ]
    ];
}

/**
 * Get portfolio projects from API
 * @param string $category
 * @return array
 */
function getPortfolioProjects($category = '') {
    try {
        $api_url = "/api/portfolio";
        if (!empty($category)) {
            $api_url .= "?category=" . urlencode($category);
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200 && $response) {
            $result = json_decode($response, true);
            if (isset($result['projects'])) {
                return $result['projects'];
            }
        }
    } catch (Exception $e) {
        // API not available
    }
    
    return [];
}
?>
