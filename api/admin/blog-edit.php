<?php
// Edit blog post - redirects to create page with ID
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    include 'blog-create.php';
} else {
    header('Location: blog.php');
    exit;
}
?>
