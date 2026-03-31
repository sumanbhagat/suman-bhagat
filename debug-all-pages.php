<?php
// Comprehensive debugging for all pages
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Complete Portfolio Debug Report</h1>";

// Test 1: Database Connection
echo "<h2>1. Database Connection Test</h2>";
try {
    require_once 'includes/database-helper.php';
    $pdo = getDatabaseConnection();
    if ($pdo) {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
        
        // Test tables
        $tables = ['site_settings', 'hero_slides', 'users', 'blog_posts', 'portfolio_projects'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM $table");
            $count = $stmt->fetch()['count'];
            echo "<p><strong>$table:</strong> $count records</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test 2: Settings Functions
echo "<h2>2. Settings Functions Test</h2>";
try {
    $settings = getSiteSettings();
    echo "<p style='color: green;'>✅ getSiteSettings() works</p>";
    echo "<p><strong>Site Title:</strong> " . ($settings['site_title'] ?? 'Not set') . "</p>";
    echo "<p><strong>Author Name:</strong> " . ($settings['author_name'] ?? 'Not set') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Settings error: " . $e->getMessage() . "</p>";
}

// Test 3: Hero Slides
echo "<h2>3. Hero Slides Test</h2>";
try {
    $slides = getHeroSlides();
    echo "<p style='color: green;'>✅ getHeroSlides() works</p>";
    echo "<p><strong>Slides found:</strong> " . count($slides) . "</p>";
    if (!empty($slides)) {
        echo "<p><strong>First slide title:</strong> " . $slides[0]['title'] . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Hero slides error: " . $e->getMessage() . "</p>";
}

// Test 4: Resume Data
echo "<h2>4. Resume Data Test</h2>";
try {
    $resume = getResumeData();
    echo "<p style='color: green;'>✅ getResumeData() works</p>";
    echo "<p><strong>Full Name:</strong> " . ($resume['full_name'] ?? 'Not set') . "</p>";
    echo "<p><strong>Title:</strong> " . ($resume['title'] ?? 'Not set') . "</p>";
    echo "<p><strong>Experience count:</strong> " . (isset($resume['experience']) ? count($resume['experience']) : 0) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Resume data error: " . $e->getMessage() . "</p>";
}

// Test 5: Blog Posts
echo "<h2>5. Blog Posts Test</h2>";
try {
    $posts = getBlogPosts();
    echo "<p style='color: green;'>✅ getBlogPosts() works</p>";
    echo "<p><strong>Posts found:</strong> " . count($posts) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Blog posts error: " . $e->getMessage() . "</p>";
}

// Test 6: Portfolio Projects
echo "<h2>6. Portfolio Projects Test</h2>";
try {
    $projects = getPortfolioProjects();
    echo "<p style='color: green;'>✅ getPortfolioProjects() works</p>";
    echo "<p><strong>Projects found:</strong> " . count($projects) . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Portfolio projects error: " . $e->getMessage() . "</p>";
}

// Test 7: File Structure
echo "<h2>7. File Structure Test</h2>";
$required_files = [
    'includes/header.php',
    'includes/footer.php',
    'includes/settings.php',
    'includes/database-helper.php',
    'index.php',
    'about.php',
    'blog.php',
    'portfolio.php',
    'resume.php',
    'contact.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file exists</p>";
    } else {
        echo "<p style='color: red;'>❌ $file missing</p>";
    }
}

// Test 8: PHP Errors in each page
echo "<h2>8. Page Error Tests</h2>";
$pages = ['index.php', 'about.php', 'blog.php', 'portfolio.php', 'resume.php', 'contact.php'];

foreach ($pages as $page) {
    echo "<h3>Testing $page</h3>";
    
    // Capture output and errors
    ob_start();
    $error_reporting = error_reporting(E_ALL);
    
    try {
        include $page;
        echo "<p style='color: green;'>✅ $page loads without fatal errors</p>";
    } catch (Error $e) {
        echo "<p style='color: red;'>❌ $page Error: " . $e->getMessage() . "</p>";
    } catch (Exception $e) {
        echo "<p style='color: orange;'>⚠️ $page Exception: " . $e->getMessage() . "</p>";
    }
    
    $output = ob_get_clean();
    error_reporting($error_reporting);
    
    if (empty($output)) {
        echo "<p style='color: orange;'>⚠️ $page produced no output</p>";
    }
}

// Test 9: Header/Footer
echo "<h2>9. Header/Footer Test</h2>";
try {
    ob_start();
    include 'includes/header.php';
    $header_output = ob_get_clean();
    echo "<p style='color: green;'>✅ Header loads</p>";
    
    ob_start();
    include 'includes/footer.php';
    $footer_output = ob_get_clean();
    echo "<p style='color: green;'>✅ Footer loads</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Header/Footer error: " . $e->getMessage() . "</p>";
}

echo "<h2>10. Quick Fixes</h2>";
echo "<ul>";
echo "<li><strong>If database fails:</strong> Run complete-setup.php</li>";
echo "<li><strong>If pages blank:</strong> Check PHP errors in XAMPP logs</li>";
echo "<li><strong>If functions missing:</strong> Check includes/database-helper.php</li>";
echo "<li><strong>If styling broken:</strong> Check CSS files</li>";
echo "</ul>";

echo "<h2>11. Test URLs</h2>";
echo "<ul>";
echo "<li><a href='test.html' target='_blank'>Test HTML Page</a></li>";
echo "<li><a href='test.php' target='_blank'>Test PHP Page</a></li>";
echo "<li><a href='complete-setup.php' target='_blank'>Complete Setup</a></li>";
echo "<li><a href='index.php' target='_blank'>Homepage</a></li>";
echo "<li><a href='about.php' target='_blank'>About Page</a></li>";
echo "<li><a href='blog.php' target='_blank'>Blog Page</a></li>";
echo "<li><a href='portfolio.php' target='_blank'>Portfolio Page</a></li>";
echo "<li><a href='resume.php' target='_blank'>Resume Page</a></li>";
echo "<li><a href='contact.php' target='_blank'>Contact Page</a></li>";
echo "</ul>";

echo "<p style='color: blue; font-weight: bold;'>Run this debug page to identify specific issues!</p>";
?>
