<?php
session_start();

// Configuration file for the portfolio website

// Site Configuration
define('SITE_NAME', 'My Portfolio');
define('SITE_URL', 'http://localhost/suman%20portfolio');
define('AUTHOR_NAME', 'John Doe');
define('AUTHOR_EMAIL', 'john.doe@example.com');
define('AUTHOR_PHONE', '+1 (555) 123-4567');

// Social Media Links
$social_links = [
    'linkedin' => 'https://linkedin.com/in/johndoe',
    'github' => 'https://github.com/johndoe',
    'twitter' => 'https://twitter.com/johndoe',
    'facebook' => 'https://facebook.com/johndoe',
    'instagram' => 'https://instagram.com/johndoe'
];

// Database connection (optional - for blog/comments)
// $db_host = 'localhost';
// $db_user = 'root';
// $db_pass = '';
// $db_name = 'portfolio';

// Helper Functions
function get_active_page() {
    return basename($_SERVER['PHP_SELF'], '.php');
}

function is_active($page) {
    return get_active_page() === $page ? 'active' : '';
}

function sanitize_input($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}
?>
