<?php
session_start();
include_once 'db_config.php'; // Database configuration
include_once 'google_drive_upload.php'; // Google Drive API integration

if (isset($_POST['upload_face_image'])) {
    $user_id = $_SESSION['user_id'];  // Assuming user is logged in
    $face_image = $_FILES['face_image'];

    if ($face_image['error'] == 0) {
        // Create a folder for storing uploaded files temporarily
        $upload_dir = '../uploads/';
        $file_name = uniqid('face_', true) . '.' . pathinfo($face_image['name'], PATHINFO_EXTENSION);
        $upload_path = $upload_dir . $file_name;

        if (move_uploaded_file($face_image['tmp_name'], $upload_path)) {
            // Upload to Google Drive
            $drive_url = uploadToGoogleDrive($upload_path);
            
            // Save the URL to the database
            $stmt = $pdo->prepare("UPDATE users SET face_image = ? WHERE id = ?");
            $stmt->execute([$drive_url, $user_id]);
            
            $_SESSION['face_image'] = $drive_url;  // Store the image URL in session
            
            // Generate the avatar
            $avatar_url = generateAvatar($drive_url); // This function should generate an avatar based on the face image
            $_SESSION['avatar_url'] = $avatar_url;

            // Save avatar URL to the database
            $stmt = $pdo->prepare("UPDATE users SET avatar_image = ? WHERE id = ?");
            $stmt->execute([$avatar_url, $user_id]);

            header("Location: avatar.php");
            exit();
        } else {
            echo "Error uploading image.";
        }
    }
}

// Avatar generation logic
function generateAvatar($faceImageUrl) {
    // Logic to generate an avatar based on face image
    // You can use an avatar generation API or create a custom solution
    return 'https://youravatargenerationurl.com/avatar.png';
}

?>
