<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'user_reg_db';
$username = 'root';
$password = 'finedica';

try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=user_reg_db", 'root', 'finedica');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Ensure the user is logged in
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$email = $_SESSION['user_email'];

// Fetch the avatar path for the given email
$stmt = $pdo->prepare("SELECT image_path FROM avatars WHERE email = :email LIMIT 1");
$stmt->bindParam(':email', $email);
$stmt->execute();
$avatarPath = $stmt->fetchColumn();

// Construct web-accessible URL for the avatar
$baseUrl = "http://localhost/finedica/";
if ($avatarPath) {
    $avatarPath = $baseUrl . "avatars/" . basename($avatarPath);
}

// Return the avatar path as a JSON response
if ($avatarPath) {
    echo json_encode([
        'status' => 'ok',
        'avatar_path' => $avatarPath
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No avatar found for this user']);
}
exit;
