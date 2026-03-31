<?php
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    include 'portfolio-create.php';
} else {
    header('Location: portfolio.php');
    exit;
}
?>
