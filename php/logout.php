<!-- filepath: c:\xampp\htdocs\2020FC\src\php\logout.php -->
<?php
session_start();
session_destroy();
header('Location: index.php');
exit;
?>