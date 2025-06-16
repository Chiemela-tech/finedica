<!-- filepath: c:\xampp\htdocs\2020FC\src\php\futureself.php -->
<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

$userEmail = $_SESSION['user_email'];
$userName = $_SESSION['user_name'];

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$host = '127.0.0.1';
$dbname = 'user_reg_db'; // Use the user registration database
$username = 'root';
$password = 'finedica';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user already has an uploaded image
$stmt = $pdo->prepare("SELECT face_image_url FROM face_image_responses WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $userEmail]);
$existing_image = $stmt->fetch(PDO::FETCH_ASSOC);

// If user wants to re-upload, allow by checking for a query param
if (isset($_GET['reupload']) && $_GET['reupload'] == '1') {
    // Delete previous image if exists
    if ($existing_image && isset($existing_image['face_image_url']) && file_exists($existing_image['face_image_url'])) {
        unlink($existing_image['face_image_url']);
        // Remove from DB
        $stmt = $pdo->prepare("DELETE FROM face_image_responses WHERE email = :email");
        $stmt->execute([':email' => $userEmail]);
        $existing_image = false;
    }
    unset($_SESSION['uploaded_image']);
} else if ($existing_image && !isset($_POST['delete'])) {
    header('Location: face_image_responses.php');
    exit;
}

// Handle the image upload
if (isset($_POST['upload'])) {
    if (isset($_FILES['face_image']) && $_FILES['face_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        $fileName = $userEmail . '.png'; // Rename the file to "email.png"
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the uploads directory
        if (!move_uploaded_file($_FILES['face_image']['tmp_name'], $filePath)) {
            die("Error uploading the file.");
        }

        // Store the file path in the session for preview
        $_SESSION['uploaded_image'] = $filePath;
        // Removed: echo "<div class='success-message'>Image loaded successfully! You can now preview it before Upload. To upload, please click the 'Submit' button below.</div>";
    } else {
        echo "<div class='error-message'>Error uploading the image. Please try again.</div>";
    }
}

// Handle the final submission
if (isset($_POST['submit'])) {
    if (isset($_SESSION['uploaded_image'])) {
        $imageUrl = $_SESSION['uploaded_image'];

        // Insert the image URL into the database
        $stmt = $pdo->prepare("INSERT INTO face_image_responses (email, face_image_url) VALUES (:email, :face_image_url)");
        $stmt->bindValue(':email', $userEmail, PDO::PARAM_STR);
        $stmt->bindValue(':face_image_url', $imageUrl, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // Clear the session variable and redirect
            unset($_SESSION['uploaded_image']);
            header('Location: face_image_responses.php');
            exit;
        } else {
            echo "<div class='error-message'>Error saving the image URL to the database.</div>";
        }
    } else {
        echo "<div class='error-message'>No image uploaded to submit.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>20:20 FC - Upload Your Face Image</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/expenditurestyle.css">
    <link rel="stylesheet" href="../css/futureselfstyle.css">
    <link rel="stylesheet" href="../css/progressbar.css">
    <link rel="stylesheet" href="face_image_style.css">

</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>20:20 FC - FINEDICA</h1>
                <p>Expert Financial Coaching</p>
            </div>
            <ul>
                <li><a href="../php/home.php">Home</a></li>
                <li><a href="../php/questionnaire.php">Questionnaire</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="avatar.php">Avatar</a></li>
                <li><a href="../chatbot/chatbot.php">Chatbot</a></li>
                <li><a href="../php/logout.php" style="font-size: 14px; color:rgb(7, 249, 168)">Logout <?php echo htmlspecialchars($userName); ?></a></li>
            </ul>
        </nav>
    </header>
    <?php $progressStep = 3; include '../php/progressbar.php'; ?>
    <main>
        <div class="futureself-hero">
            <h1><span class="icon">🖼️</span> Upload & Preview Your Face Image</h1>
        </div>
        <div class="futureself-avatar-card card" style="max-width: 700px; margin: 32px auto; text-align: center; padding: 32px 24px;">
            <div style="display: flex; justify-content: center; align-items: flex-start; gap: 40px; flex-wrap: wrap;">
                <div style="flex:1; min-width:260px; max-width:340px; display:flex; flex-direction:column; align-items:center;">
                    <h2 style="margin-bottom: 18px; color: #2196f3; font-size: 1.3em;">Step 1: Upload Your Face Image</h2>
                    <form action="face_image.php" method="POST" enctype="multipart/form-data" id="upload-form" style="width:100%;">
                        <input type="file" name="face_image" accept="image/*" required style="margin-bottom: 18px; width: 100%;">
                        <button type="submit" name="upload" class="futureself-btn" style="width: 100%;">Upload</button>
                    </form>
                </div>
                <div style="flex:1; min-width:260px; max-width:340px; display:flex; flex-direction:column; align-items:center;">
                    <h2 style="margin-bottom: 18px; color: #2196f3; font-size: 1.3em;">Step 2: Preview Your Image</h2>
                    <div class="preview-area card" style="width: 320px; height: 320px; background: #f8f8f8; border-radius: 20px; box-shadow: 0 4px 24px rgba(33,150,243,0.13); border: 3px solid #2196f3; display: flex; align-items: center; justify-content: center; margin-bottom: 18px; overflow: hidden;">
                        <?php if (isset($_SESSION['uploaded_image'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['uploaded_image']); ?>" alt="Uploaded Face Image" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 16px; display: block; margin: auto; background: transparent;" />
                        <?php else: ?>
                            <p class="avatar-info-text">No image uploaded yet. Please upload your face image to preview.</p>
                        <?php endif; ?>
                    </div>
                    <?php if (isset($_SESSION['uploaded_image'])): ?>
                        <form action="face_image.php" method="POST" id="submit-form" style="width:100%;">
                            <button type="submit" name="submit" class="futureself-btn" style="width: 100%;">Submit</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
            <div class="nav-buttons-row" style="margin-top: 32px;">
                <button id="back-btn" class="nav-btn nav-btn-left" onclick="window.history.back();return false;">Back</button>
                <button id="next-btn" class="nav-btn nav-btn-right">Next</button>
            </div>
        </div>
    </main>
    <link rel="stylesheet" href="face_image_style.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('next-btn').onclick = function() {
                <?php if (isset($_SESSION['uploaded_image'])): ?>
                    window.location.href = 'face_image_responses.php';
                <?php else: ?>
                    window.location.href = 'face_image.php';
                <?php endif; ?>
                return false;
            };
        });
    </script>
</body>
</html>