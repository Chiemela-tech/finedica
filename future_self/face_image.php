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
$host = 'localhost';
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
}

// Handle the image upload
if (isset($_POST['upload'])) {
    if (isset($_FILES['face_image']) && $_FILES['face_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = realpath(__DIR__ . '/../uploads');
        if ($uploadDir === false) {
            die("Error: Uploads directory does not exist.");
        }
        if (!is_writable($uploadDir)) {
            die("Error: Uploads directory is not writable.");
        }
        $fileName = $userEmail . '.png';
        $filePath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
        $webPath = '/finedica/uploads/' . $fileName;

        if (!move_uploaded_file($_FILES['face_image']['tmp_name'], $filePath)) {
            die("Error uploading the file.");
        }

        // Always store the web-accessible path in the session for preview
        $_SESSION['uploaded_image'] = $webPath;
    } else {
        echo "<div class='error-message'>Error uploading the image. Please try again.</div>";
    }
}

// Handle the final submission
if (isset($_POST['submit'])) {
    // Always use the correct web path for this user
    $imageUrl = '/finedica/uploads/' . $userEmail . '.png';

    // Always clean up any old DB values for this user
    $pdo->prepare("DELETE FROM face_image_responses WHERE email = :email")->execute([':email' => $userEmail]);

    // Always insert the correct web path into the database
    $stmt = $pdo->prepare("INSERT INTO face_image_responses (email, face_image_url) VALUES (:email, :face_image_url)");
    $stmt->bindValue(':email', $userEmail, PDO::PARAM_STR);
    $stmt->bindValue(':face_image_url', $imageUrl, PDO::PARAM_STR);
    $stmt->execute();

    // Clear the session variable and redirect
    unset($_SESSION['uploaded_image']);
    header('Location: face_image_responses.php');
    exit;
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
            <?php if (!$existing_image): ?>
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
                            <img src="<?php echo htmlspecialchars($_SESSION['uploaded_image']); ?>" alt="Uploaded Face Image" style="width: 100%; height: 100%; object-fit: cover; border-radius: 16px; display: block; margin: auto; background: transparent;" />
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
            <?php else: ?>
            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center;">
                <h2 style="margin-bottom: 18px; color: #2196f3; font-size: 1.3em;">Your Uploaded Face Image</h2>
                <div class="preview-area card" style="width: 320px; height: 320px; background: #f8f8f8; border-radius: 20px; box-shadow: 0 4px 24px rgba(33,150,243,0.13); border: 3px solid #2196f3; display: flex; align-items: center; justify-content: center; margin-bottom: 18px; overflow: hidden;">
                    <img src="<?php echo htmlspecialchars($existing_image['face_image_url']); ?>" alt="Uploaded Face Image" style="width: 100%; height: 100%; object-fit: cover; border-radius: 16px; display: block; margin: auto; background: transparent;" />
                </div>
                <form action="face_image.php" method="GET" style="width:100%; margin-top: 18px;">
                    <button type="submit" name="reupload" value="1" class="futureself-btn" style="width: 100%;">Re-upload Face Image</button>
                </form>
            </div>
            <?php endif; ?>
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