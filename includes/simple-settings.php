<?php
/**
 * Simple Settings Helper - Maximum Compatibility
 * Works even when complex database operations fail
 */

// Include simple database helper
require_once __DIR__ . '/simple-db.php';

/**
 * Get hero slides - simplified version
 */
function simple_get_hero_slides() {
    return simple_get_slides();
}

/**
 * Get resume data - simplified version
 */
function simple_get_resume_data() {
    return simple_get_resume();
}

/**
 * Get blog posts - simplified version
 */
function simple_get_blog_posts_simple($limit = 6) {
    return simple_get_blog_posts();
}

/**
 * Get portfolio projects - simplified version
 */
function simple_get_portfolio_projects_simple() {
    return simple_get_projects();
}

/**
 * Get about data - simplified version
 */
function simple_get_about_data_simple() {
    return simple_get_about();
}

/**
 * Get specific setting value
 */
function simple_get_setting($key, $default = '') {
    $settings = simple_get_settings();
    return $settings[$key] ?? $default;
}

/**
 * Save contact message - simplified version
 */
function simple_save_contact_message($data) {
    // For now, just return true - can be enhanced later
    return true;
}

/**
 * Get contact messages - simplified version
 */
function simple_get_contact_messages() {
    return [];
}
?>
