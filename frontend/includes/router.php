<?php
/**
 * URL Router & Helper
 * Provides clean URL generation and routing
 */

/**
 * Generate clean URL (removes .php extension)
 * @param string $page Page name (e.g., 'blog', 'about')
 * @param array $params Query parameters
 * @return string Clean URL
 */
function url($page, $params = []) {
    // Remove .php if present
    $page = str_replace('.php', '', $page);
    
    // Build base URL
    $base = getBaseUrl();
    $url = $base . $page;
    
    // Add query parameters
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    return $url;
}

/**
 * Get base URL of the site
 * @return string Base URL with trailing slash
 */
function getBaseUrl() {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = dirname($_SERVER['SCRIPT_NAME'] ?? '/');
    
    // Handle subdirectory installation
    if ($script === '/' || $script === '\\') {
        $path = '/';
    } else {
        $path = rtrim(str_replace('\\', '/', $script), '/') . '/';
    }
    
    return $protocol . '://' . $host . $path;
}

/**
 * Get current page URL
 * @return string Current URL
 */
function currentUrl() {
    return getBaseUrl() . ltrim($_SERVER['REQUEST_URI'] ?? '/', '/');
}

/**
 * Check if URL rewriting is enabled
 * @return bool
 */
function isUrlRewritingEnabled() {
    // Check if .htaccess exists and mod_rewrite is available
    $htaccessPath = __DIR__ . '/../.htaccess';
    return file_exists($htaccessPath) && function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules());
}

/**
 * Redirect to URL
 * @param string $url
 * @param int $code HTTP status code
 */
function redirect($url, $code = 302) {
    http_response_code($code);
    header("Location: $url");
    exit();
}

/**
 * Asset URL helper
 * @param string $path Path to asset (e.g., 'css/style.css')
 * @return string Full asset URL
 */
function asset($path) {
    return getBaseUrl() . 'assets/' . ltrim($path, '/');
}

/**
 * Admin URL helper
 * @param string $page Admin page
 * @return string Admin URL
 */
function adminUrl($page = '') {
    $base = getBaseUrl() . 'admin/';
    return $page ? $base . $page : $base;
}

/**
 * Upload URL helper
 * @param string $path
 * @return string Upload URL
 */
function uploadUrl($path) {
    return getBaseUrl() . 'uploads/' . ltrim($path, '/');
}

/**
 * Get page name from URL
 * @return string Current page name
 */
function getCurrentPage() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    $filename = basename($path);
    
    // Remove .php extension
    return str_replace('.php', '', $filename) ?: 'index';
}

/**
 * Check if current page is active
 * @param string $page
 * @return bool
 */
function isPage($page) {
    return getCurrentPage() === $page;
}

/**
 * Active class helper for navigation
 * @param string $page
 * @return string 'active' or empty string
 */
function activeClass($page) {
    return isPage($page) ? 'active' : '';
}
