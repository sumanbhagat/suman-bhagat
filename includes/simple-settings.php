<?php
/**
 * Simple Settings Helper - Maximum Compatibility
 * Works even when complex database operations fail
 */

// Include simple database helper
require_once __DIR__ . '/simple-db.php';

/**
 * Get site settings - simplified version
 */
function getSiteSettings() {
    return simple_get_settings();
}

/**
 * Get hero slides - simplified version
 */
function getHeroSlides() {
    return simple_get_slides();
}

/**
 * Get resume data - simplified version
 */
function getResumeData() {
    return simple_get_resume();
}

/**
 * Get blog posts - simplified version
 */
function getBlogPosts($limit = 6) {
    return simple_get_blog_posts();
}

/**
 * Get portfolio projects - simplified version
 */
function getPortfolioProjects() {
    return simple_get_projects();
}

/**
 * Get about data - simplified version
 */
function getAboutData() {
    return simple_get_about();
}

/**
 * Get specific setting value
 */
function getSetting($key, $default = '') {
    $settings = getSiteSettings();
    return $settings[$key] ?? $default;
}

/**
 * Save contact message - simplified version
 */
function saveContactMessage($data) {
    // For now, just return true - can be enhanced later
    return true;
}

/**
 * Get contact messages - simplified version
 */
function getContactMessages() {
    return [];
}
?>
