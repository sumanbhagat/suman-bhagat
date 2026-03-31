<?php
/**
 * Performance Optimization Helper
 * Provides caching and performance features for fast URL routing
 */

/**
 * Simple page cache
 */
class PageCache {
    private $cache_dir;
    private $enabled;
    private $ttl; // Time to live in seconds
    
    public function __construct($ttl = 3600) {
        $this->cache_dir = __DIR__ . '/../cache/';
        $this->enabled = true;
        $this->ttl = $ttl;
        
        // Create cache directory if it doesn't exist
        if (!is_dir($this->cache_dir)) {
            mkdir($this->cache_dir, 0755, true);
        }
    }
    
    /**
     * Get cache key for current page
     */
    private function getCacheKey() {
        return md5($_SERVER['REQUEST_URI'] . ($_SESSION['user_id'] ?? 'guest'));
    }
    
    /**
     * Get cache file path
     */
    private function getCacheFile($key) {
        return $this->cache_dir . $key . '.html';
    }
    
    /**
     * Check if cache exists and is valid
     */
    public function has($key = null) {
        if (!$this->enabled) return false;
        
        $key = $key ?: $this->getCacheKey();
        $file = $this->getCacheFile($key);
        
        if (!file_exists($file)) return false;
        
        // Check if cache is expired
        if (time() - filemtime($file) > $this->ttl) {
            unlink($file);
            return false;
        }
        
        return true;
    }
    
    /**
     * Get cached content
     */
    public function get($key = null) {
        $key = $key ?: $this->getCacheKey();
        $file = $this->getCacheFile($key);
        
        if ($this->has($key)) {
            return file_get_contents($file);
        }
        
        return false;
    }
    
    /**
     * Save content to cache
     */
    public function save($content, $key = null) {
        if (!$this->enabled) return false;
        
        $key = $key ?: $this->getCacheKey();
        $file = $this->getCacheFile($key);
        
        // Add cache timestamp comment
        $content .= "\n<!-- Cached: " . date('Y-m-d H:i:s') . " -->";
        
        file_put_contents($file, $content);
        return true;
    }
    
    /**
     * Clear all cache
     */
    public function clear() {
        $files = glob($this->cache_dir . '*.html');
        foreach ($files as $file) {
            unlink($file);
        }
        return true;
    }
    
    /**
     * Start output buffering for caching
     */
    public function start() {
        if ($this->has()) {
            echo $this->get();
            exit;
        }
        
        ob_start();
    }
    
    /**
     * End output buffering and save cache
     */
    public function end() {
        $content = ob_get_clean();
        $this->save($content);
        echo $content;
    }
}

/**
 * Database query cache (simple in-memory for single request)
 */
class QueryCache {
    private static $cache = [];
    
    /**
     * Get cached query result
     */
    public static function get($key) {
        return self::$cache[$key] ?? null;
    }
    
    /**
     * Set cached query result
     */
    public static function set($key, $value) {
        self::$cache[$key] = $value;
    }
    
    /**
     * Check if key exists
     */
    public static function has($key) {
        return isset(self::$cache[$key]);
    }
    
    /**
     * Clear cache
     */
    public static function clear() {
        self::$cache = [];
    }
}

/**
 * Minify HTML output
 * @param string $html
 * @return string Minified HTML
 */
function minifyHTML($html) {
    // Remove comments (except IE conditional comments)
    $html = preg_replace('/<!--[^\[](?!.*?<\!\[endif\]).*?-->/s', '', $html);
    
    // Remove whitespace between tags
    $html = preg_replace('/>\s+</', '><', $html);
    
    // Remove multiple spaces
    $html = preg_replace('/\s{2,}/', ' ', $html);
    
    return trim($html);
}

/**
 * Preload critical assets hint
 * @param array $assets Array of asset URLs
 */
function preloadAssets($assets) {
    foreach ($assets as $asset) {
        $type = pathinfo($asset, PATHINFO_EXTENSION);
        $as = ($type === 'css') ? 'style' : (($type === 'js') ? 'script' : 'image');
        header("Link: <{$asset}>; rel=preload; as={$as}", false);
    }
}

/**
 * Lazy loading placeholder image
 * @return string Base64 encoded 1x1 transparent GIF
 */
function lazyLoadPlaceholder() {
    return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
}
