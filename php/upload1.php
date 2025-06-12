<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

$userEmail = $_SESSION['user_email']; // Use email as the user identifier

// Database connection
$conn = new mysqli('localhost', 'root', '', 'users'); // Update with your DB credentials

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profileImagePath = "../uploads/" . basename($_FILES['profileImage']['name']);
    $generatedAvatarPath = "../uploads/" . basename($_FILES['generatedAvatar']['name']);

    // Move uploaded files to the uploads directory
    move_uploaded_file($_FILES['profileImage']['tmp_name'], $profileImagePath);
    move_uploaded_file($_FILES['generatedAvatar']['tmp_name'], $generatedAvatarPath);

    // Update the database with the new image paths
    $sql = "UPDATE users SET profile_image_url = ?, generated_avatar_url = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $profileImagePath, $generatedAvatarPath, $userEmail);
    $stmt->execute();

    $stmt->close();
    $conn->close();

    header('Location: chatbot.php'); // Redirect back to the chatbot page
    exit;
}
require_once 'config.php';
if (!isset($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Handle face image upload
        if (isset($_FILES['faceImage'])) {
            error_log('File upload details: ' . print_r($_FILES['faceImage'], true));
            
            if ($_FILES['faceImage']['error'] !== UPLOAD_ERR_OK) {
                error_log('File upload error: ' . $_FILES['faceImage']['error']);
                echo json_encode(['success' => false, 'message' => 'File upload error.']);
                exit;
            }
            
            
            // Ensure upload directory exists
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0777, true);
            }
            
            $fileName = basename($_FILES['faceImage']['name']);
            $filePath = UPLOAD_DIR . $fileName;
            
            if (!move_uploaded_file($_FILES['faceImage']['tmp_name'], $filePath)) {
                error_log('Failed to move uploaded file.');
                echo json_encode(['success' => false, 'message' => 'Failed to upload face image.']);
                exit;
            }
            
            // Upload to Google Drive
            $googleDriveFolderId = '1_os5zlO3EgFXtIQGMTVG4b2f52oQGXfh';
            $faceImageUrl = uploadToGoogleDrive($filePath, $googleDriveFolderId);
            
            // Update database
            $pdo = getDatabaseConnection();
            $pdo->beginTransaction();
            try {
                $stmt = $pdo->prepare('UPDATE users SET face_image = ? WHERE email = ?');
                $stmt->execute([$faceImageUrl, $_SESSION['user_email']]);
                $pdo->commit();
                
                echo json_encode(['success' => true, 'faceImageUrl' => $faceImageUrl]);
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log('Database error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error.']);
            }
        }
        // Handle avatar upload
        elseif (isset($_POST['avatarData'])) {
            $avatarData = $_POST['avatarData'];
            $avatarFilePath = UPLOAD_DIR . $_SESSION['user_name'] . '_avatar.png';
            
            try {
                // Save avatar image
                file_put_contents($avatarFilePath, base64_decode($avatarData));
                error_log('Avatar image saved to: ' . $avatarFilePath);
                
                // Upload to Google Drive
                $googleDriveFolderId = '1eoBhHN9DUFImlFJpXlOcxWXZ7tCro0ku';
                $avatarImageUrl = uploadToGoogleDrive($avatarFilePath, $googleDriveFolderId);
                
                // Update database
                $pdo = getDatabaseConnection();
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('UPDATE users SET avatar_image = ? WHERE user_name = ?');
                    $stmt->execute([$avatarImageUrl, $_SESSION['user_name']]);
                    $pdo->commit();
                    
                    echo json_encode(['success' => true, 'avatarImageUrl' => $avatarImageUrl]);
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log('Database error: ' . $e->getMessage());
                    echo json_encode(['success' => false, 'message' => 'Database error.']);
                }
            } catch (Exception $e) {
                error_log('Avatar upload error: ' . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Avatar upload failed.']);
            }
        }
    } catch (Exception $e) {
        error_log('Upload process error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Upload process failed.']);
    }
}
?>
