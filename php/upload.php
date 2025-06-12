<?php
session_start();
header('Content-Type: application/json');

// Check if the user is authenticated
if (!isset($_SESSION['user_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

// Configure paths
$uploadDir = '../../uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

// Check if a file was uploaded
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['status' => 'error', 'message' => 'File upload error']);
    exit;
}

$file = $_FILES['file'];
$fileType = mime_content_type($file['tmp_name']);
$fileName = basename($file['name']);
$uploadPath = $uploadDir . uniqid() . '_' . $fileName;

// Validate file type
if (!in_array($fileType, $allowedTypes)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid file type']);
    exit;
}

// Move the uploaded file to the uploads directory
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Save the file path to the database
    $userEmail = $_SESSION['user_email'];
    $db = new PDO('mysql:host=localhost;port=3307;dbname=user_reg_db', 'root', '');
    $stmt = $db->prepare("INSERT INTO face_image_responses (user_email, image_path) VALUES (:email, :path)");
    $stmt->bindParam(':email', $userEmail);
    $stmt->bindParam(':path', $uploadPath);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'File uploaded successfully', 'file_path' => $uploadPath]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file']);
}
?>