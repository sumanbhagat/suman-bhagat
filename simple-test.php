<?php
// Simple test page - no dependencies, maximum compatibility
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><title>Simple Test</title></head><body>";
echo "<h1>Simple Portfolio Test</h1>";

// Test 1: Basic PHP
echo "<h2>1. Basic PHP Test</h2>";
echo "<p style='color: green;'>✅ PHP is working (version: " . PHP_VERSION . ")</p>";

// Test 2: File includes
echo "<h2>2. File Include Test</h2>";
if (file_exists('includes/simple-settings.php')) {
    include 'includes/simple-settings.php';
    echo "<p style='color: green;'>✅ simple-settings.php loaded</p>";
} else {
    echo "<p style='color: red;'>❌ simple-settings.php not found</p>";
}

// Test 3: Database functions
echo "<h2>3. Database Functions Test</h2>";
try {
    $settings = getSiteSettings();
    echo "<p style='color: green;'>✅ getSiteSettings() works</p>";
    echo "<p>Site Title: " . htmlspecialchars($settings['site_title']) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getSiteSettings() failed: " . $e->getMessage() . "</p>";
}

try {
    $slides = getHeroSlides();
    echo "<p style='color: green;'>✅ getHeroSlides() works</p>";
    echo "<p>Slides found: " . count($slides) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getHeroSlides() failed: " . $e->getMessage() . "</p>";
}

try {
    $resume = getResumeData();
    echo "<p style='color: green;'>✅ getResumeData() works</p>";
    echo "<p>Name: " . htmlspecialchars($resume['full_name']) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ getResumeData() failed: " . $e->getMessage() . "</p>";
}

// Test 4: Sample content
echo "<h2>4. Sample Content Display</h2>";

try {
    $slides = getHeroSlides();
    if (!empty($slides)) {
        $slide = $slides[0];
        echo "<div style='border: 1px solid #ccc; padding: 20px; margin: 10px 0;'>";
        echo "<h3>" . htmlspecialchars($slide['title']) . "</h3>";
        echo "<p>" . htmlspecialchars($slide['description']) . "</p>";
        echo "<a href='" . htmlspecialchars($slide['button1_url']) . "'>" . htmlspecialchars($slide['button1_text']) . "</a>";
        echo "</div>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Content display failed: " . $e->getMessage() . "</p>";
}

// Test 5: Links to main pages
echo "<h2>5. Page Links Test</h2>";
echo "<ul>";
echo "<li><a href='index.php'>Homepage</a></li>";
echo "<li><a href='about.php'>About Page</a></li>";
echo "<li><a href='blog.php'>Blog Page</a></li>";
echo "<li><a href='portfolio.php'>Portfolio Page</a></li>";
echo "<li><a href='resume.php'>Resume Page</a></li>";
echo "<li><a href='contact.php'>Contact Page</a></li>";
echo "</ul>";

echo "<h2>6. Debug Tools</h2>";
echo "<ul>";
echo "<li><a href='debug-all-pages.php'>Complete Debug Report</a></li>";
echo "<li><a href='complete-setup.php'>Complete Database Setup</a></li>";
echo "<li><a href='test-mysql.php'>MySQL Connection Test</a></li>";
echo "</ul>";

echo "<p style='color: blue; font-weight: bold;'>If this page works, the basic functionality is working!</p>";
echo "</body></html>";
?>
