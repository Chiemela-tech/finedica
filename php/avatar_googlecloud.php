<?php
// config.php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

// Configuration constants
const CHUNK_SIZE = 1024 * 1024; // 1MB chunks
const MAX_FILE_SIZE = 50 * 1024 * 1024; // 50MB limit
const UPLOAD_DIR = __DIR__ . '/../uploads/';
const DRIVE_FILE_SCOPE = Google_Service_Drive::DRIVE_FILE;

// Initialize Google Client
function initializeGoogleClient() {
    $client = new Google_Client();
    $client->setAuthConfig(__DIR__ . '/credentials.json');
    $client->addScope(DRIVE_FILE_SCOPE);
    return $client;
}

// Enhanced upload function with resumable uploads
function uploadToGoogleDrive($filePath, $folderId) {
    if (!file_exists($filePath)) {
        throw new InvalidArgumentException('File does not exist');
    }
    
    if (filesize($filePath) > MAX_FILE_SIZE) {
        throw new InvalidArgumentException('File size exceeds maximum limit');
    }
    
    $client = initializeGoogleClient();
    $service = new Google_Service_Drive($client);
    
    $fileMetadata = new Google_Service_Drive_DriveFile([
        'name' => basename($filePath),
        'parents' => [$folderId]
    ]);
    
    $media = new Google_Http_MediaFileUpload(
        $client,
        $service,
        mime_content_type($filePath),
        null,
        true,
        CHUNK_SIZE
    );
    
    $media->setRetryTimeout(600); // 10 minute timeout
    $media->setChunkSize(CHUNK_SIZE);
    
    $file = new Google_Service_Drive_DriveFile();
    $status = false;
    $progress = 0;
    
    while (!$status && $progress < 1) {
        try {
            $status = $media->nextChunk($file, $progress);
            $progress = $media->getProgress();
            
            if ($progress < 1) {
                error_log("Uploading... {$progress}%");
            }
        } catch (Exception $e) {
            error_log('Upload failed: ' . $e->getMessage());
            sleep(2); // Wait before retrying
        }
    }
    
    return $file->getWebViewLink();
}

// Database connection function
function getDatabaseConnection() {
    try {
        $pdo = new PDO('mysql:host=localhost;port=3307;dbname=user_reg_db', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        error_log('Database connection failed: ' . $e->getMessage());
        throw $e;
    }
}

// index.php
require_once 'config.php';
if (!isset($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}
$userName = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>20:20 FC - Avatar Generator</title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>20:20 FC - FINEDICA</h1>
                <p>Expert Financial Coaching</p>
            </div>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="questionnaire.php">Questionnaire</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="avatar.php">Avatar</a></li>
                <li><a href="chatbot.php">Chatbot</a></li>
                <li><a href="logout.php" style="font-size: 18px; color: Yellow">Logout <?php echo htmlspecialchars($userName);?></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <!-- Your website content goes here -->
        <!-- src/html/avatar.html -->
        <div class="avatar-section">
            <div class="upload-area">
                <h2>Upload Your Face</h2>
                <input type="file" id="faceUpload" accept="image/*">
                <div class="preview">
                    <!-- Frame for uploaded face image -->
                    <div class="image-frame">
                        <canvas id="faceCanvas"></canvas>
                    </div>
                </div>
                <button id="uploadBtn">Upload Face</button>
            </div>
            <div class="avatar-options">
                <h2>Customize Your Avatar</h2>
                <label for="hairColor">Hair Color:</label>
                <input type="color" id="hairColor" value="#000000">
                <label for="eyeColor">Eye Color:</label>
                <input type="color" id="eyeColor" value="#000000">
                <label for="skinTone">Skin Tone:</label>
                <input type="color" id="skinTone" value="#ffcc99">
                <label for="clothingColor">Clothing Color:</label>
                <input type="color" id="clothingColor" value="#0000ff">
                <button id="generateAvatarBtn">Generate Avatar</button>
            </div>
            <div class="avatar-preview">
                <h2>Your Generated Avatar</h2>
                <!-- Frame for generated avatar -->
                <div class="image-frame">
                    <div id="avatarContainer"></div>
                </div>
            </div>
        </div>
    </main>
    <script src="../js/avatar.js"></script>
</body>
</html>

