<?php
try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=user_reg_db', 'root', 'finedica');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connection successful!";
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
}
?>