<?php 
require_once 'api-settings.php';
require_once 'router.php';

// Load site settings from API
$site_settings = getSiteSettings();
$site_title = $site_settings['site_title'] ?? 'My Portfolio';
$site_author = $site_settings['author_name'] ?? 'John Doe';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($site_settings['site_description'] ?? ''); ?>">
    <title><?php echo htmlspecialchars($site_author); ?> - <?php echo htmlspecialchars($site_title); ?></title>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <a href="<?php echo url('index'); ?>" class="logo">
                <span class="logo-text"><?php echo htmlspecialchars($site_author); ?></span>
            </a>
            
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <span class="hamburger"></span>
            </button>
            
            <ul class="nav-menu" id="navMenu">
                <li><a href="<?php echo url('index'); ?>" class="nav-link <?php echo activeClass('index'); ?>">Home</a></li>
                <li><a href="<?php echo url('about'); ?>" class="nav-link <?php echo activeClass('about'); ?>">About Me</a></li>
                <li><a href="<?php echo url('portfolio'); ?>" class="nav-link <?php echo activeClass('portfolio'); ?>">Portfolio</a></li>
                <li><a href="<?php echo url('blog'); ?>" class="nav-link <?php echo activeClass('blog'); ?>">Blog</a></li>
                <li><a href="<?php echo url('gallery'); ?>" class="nav-link <?php echo activeClass('gallery'); ?>">Gallery</a></li>
                <li><a href="<?php echo url('resume'); ?>" class="nav-link <?php echo activeClass('resume'); ?>">Resume</a></li>
                <li><a href="<?php echo url('contact'); ?>" class="nav-link <?php echo activeClass('contact'); ?>">Contact</a></li>
            </ul>
        </div>
    </nav>

    <main>
