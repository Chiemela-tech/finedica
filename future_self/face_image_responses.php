<?php
session_start();

// Check login
if (!isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

$userEmail = $_SESSION['user_email'];
$userName = $_SESSION['user_name'];

// Database connection
$host = 'localhost';
$dbname = 'user_reg_db';
$username = 'root';
$password = 'finedica';

try {
    $pdo = new PDO("mysql:host=localhost;port=3307;dbname=user_reg_db", 'root', 'finedica');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT face_image_url FROM face_image_responses WHERE email = :email ORDER BY id DESC LIMIT 1");
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();
    $faceImageUrl = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if avatar exists for this user and get its path
$avatarPath = null;
try {
    $stmt = $pdo->prepare("SELECT image_path FROM avatars WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();
    $avatarPath = $stmt->fetchColumn();
} catch (PDOException $e) {
    $avatarPath = null;
}

// Only show generate button if no avatar exists, otherwise show re-generate button
$showGenerate = !$avatarPath;
$showRegenerate = (bool)$avatarPath;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review Your Face Image</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/progressbar.css">
    <link rel="stylesheet" href="face_image_style.css">
    <link rel="stylesheet" href="../css/futureselfstyle.css">
    <script>
        const userEmail = "<?php echo $_SESSION['user_email']; ?>";
    </script>
    <script>
        window.userEmail = "<?php echo $_SESSION['user_email']; ?>";
    </script>
    <script src="../js/avatar.js"></script>
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
                <li><a href="../php/ethics_policy.php">AI Ethics Policy</a></li>
                <li><a href="../generate_avatar/avatar_frontpage.php">Avatar</a></li>
                <li><a href="../chatbot/chatbot.php">Chatbot</a></li>
                <li><a href="../php/logout.php">Logout <?php echo htmlspecialchars($userName); ?></a></li>
            </ul>
        </nav>
    </header>
    <?php $progressStep = 3; include '../php/progressbar.php'; ?>
    <main>
        <div class="futureself-hero" style="display: flex; justify-content: center; align-items: center;">
            <h1 style="text-align: center;"><span class="icon">🖼️</span> Review Your Face & Avatar</h1>
        </div>
        <div class="futureself-avatar-card card" style="max-width: 700px; justify-content: center; margin: 32px auto; text-align: center; padding: 32px 24px;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 40px; flex-wrap: wrap;">
                <div style="flex:1; min-width:260px; max-width:340px; display:flex; flex-direction:column; align-items:center;">
                    <h3 style="margin-bottom: 12px;">Uploaded Face Image</h3>
                    <div class="image-preview" style="width: 320px; height: 320px; background: #f8f8f8; border-radius: 20px; box-shadow: 0 4px 24px rgba(33,150,243,0.13); border: 3px solid #2196f3; display: flex; align-items: center; justify-content: center; margin-bottom: 0; overflow: hidden;">
                        <?php if ($faceImageUrl): ?>
                            <?php
                            // If the URL does not start with http, https, or /, prepend /finedica/uploads/
                            $imgSrc = (preg_match('/^(https?:\/\/|\/)/', $faceImageUrl))
                                ? $faceImageUrl
                                : '/finedica/uploads/' . ltrim($faceImageUrl, '/');
                            ?>
                            <img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="Uploaded Face Image" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 16px; box-shadow: none; border: none; background: transparent; display: block; margin: auto;" />
                        <?php else: ?>
                            <p>No face image uploaded.</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($faceImageUrl): ?>
                        <button id="reupload-face-btn" class="futureself-btn" style="margin-top: 18px;">Re-upload Face Image</button>
                    <?php endif; ?>
                </div>
                <div style="flex:1; min-width:260px; max-width:340px; display:flex; flex-direction:column; align-items:center;">
                    <h3 style="margin-bottom: 12px;">Generated Avatar</h3>
                    <div id="avatar-preview" style="width: 320px; height: 320px; background: #f8f8f8; border-radius: 20px; box-shadow: 0 4px 24px rgba(33,150,243,0.13); border: 3px solid #2196f3; display: flex; align-items: center; justify-content: center; margin-bottom: 0; overflow: hidden;">
                        <?php if ($avatarPath): ?>
                            <img src="/finedica/avatars/<?php echo htmlspecialchars($avatarPath); ?>?t=<?php echo time(); ?>" alt="Generated Avatar" style="max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 16px; box-shadow: none; border: none; background: transparent; display: block; margin: auto;" />
                        <?php else: ?>
                            <p>No avatar generated yet.</p>
                        <?php endif; ?>
                    </div>
                    <?php if ($showGenerate): ?>
                        <button id="generate-avatar-btn" class="futureself-btn" style="margin-top: 18px;">Generate Avatar</button>
                    <?php endif; ?>
                    <?php if ($showRegenerate): ?>
                        <button id="regenerate-avatar-btn" class="futureself-btn" style="margin-top: 18px;">Re-generate Avatar</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="nav-buttons-row" style="margin-top: 32px;">
                <button id="back-btn" class="nav-btn nav-btn-left" type="button">Back</button>
                <button id="next-btn" class="nav-btn nav-btn-right" type="button" <?php if (!$avatarPath) echo 'disabled style="opacity:0.5;cursor:not-allowed;"'; ?>>Next</button>
            </div>
        </div>
    </main>
    <link rel="stylesheet" href="../css/faceimagestyle.css">
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const userEmail = window.userEmail;
            // Navigation buttons
            document.getElementById('back-btn').onclick = function() {
                window.location.href = 'futureself_responses.php';
            };
            const nextBtn = document.getElementById('next-btn');
            nextBtn.onclick = function() {
                if (!nextBtn.disabled) {
                    window.location.href = '../chatbot/chatbot.php';
                }
            };
            // Generate Avatar button logic
            const genBtn = document.getElementById('generate-avatar-btn');
            if (genBtn) {
                genBtn.onclick = function () {
                    genBtn.disabled = true;
                    genBtn.textContent = 'Generating...';
                    fetch('../generate_avatar/generate_avatar.php', {
                        method: 'POST',
                        body: JSON.stringify({ email: userEmail }),
                        headers: { 'Content-Type': 'application/json' },
                    })
                        .then((res) => res.json())
                        .then((data) => {
                            if (data.status === 'ok') {
                                document.getElementById('avatar-preview').innerHTML = `<img src="${data.avatar_path}?t=${Date.now()}" alt="Generated Avatar" style="max-width:320px; max-height:320px; border-radius:16px; box-shadow:0 2px 16px rgba(33,150,243,0.16);">`;
                                genBtn.style.display = 'none';
                                // Enable Next button after avatar is generated
                                nextBtn.disabled = false;
                                nextBtn.style.opacity = '1';
                                nextBtn.style.cursor = 'pointer';
                                // Show re-generate button after generation
                                let regenBtn = document.getElementById('regenerate-avatar-btn');
                                if (!regenBtn) {
                                    regenBtn = document.createElement('button');
                                    regenBtn.id = 'regenerate-avatar-btn';
                                    regenBtn.textContent = 'Re-generate Avatar';
                                    regenBtn.className = 'action-btn';
                                    document.querySelector('.avatar-section').insertBefore(regenBtn, document.getElementById('navigation-buttons'));
                                }
                                regenBtn.onclick = function() {
                                    if (confirm('To re-generate your avatar, you must re-take the Future Self test. Do you want to proceed?')) {
                                        if (confirm('This will delete your previous Future Self responses and avatar. Do you consent to proceed?')) {
                                            fetch('regenerate_avatar_cleanup.php', {
                                                method: 'POST',
                                                headers: { 'Content-Type': 'application/json' },
                                                body: JSON.stringify({ email: userEmail })
                                            })
                                            .then(res => res.json())
                                            .then(data => {
                                                if (data.status === 'ok') {
                                                    window.location.href = 'futureself.php';
                                                } else {
                                                    alert('Error: ' + data.message);
                                                }
                                            })
                                            .catch(() => {
                                                alert('An unexpected error occurred. Please try again.');
                                            });
                                        }
                                    }
                                };
                                regenBtn.style.display = 'inline-block';
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(() => {
                            alert('An unexpected error occurred. Please try again later.');
                        })
                        .finally(() => {
                            genBtn.disabled = false;
                            genBtn.textContent = 'Generate Avatar';
                        });
                };
            }
            // Re-generate Avatar button logic
            const regenBtn = document.getElementById('regenerate-avatar-btn');
            if (regenBtn) {
                regenBtn.onclick = function() {
                    if (confirm('To re-generate your avatar, you must re-take the Future Self test. Do you want to proceed?')) {
                        if (confirm('This will delete your previous Future Self responses and avatar. Do you consent to proceed?')) {
                            fetch('../generate_avatar/regenerate_avatar_cleanup.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({ email: userEmail })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'ok') {
                                    window.location.href = 'futureself.php';
                                } else {
                                    alert('Error: ' + data.message);
                                }
                            })
                            .catch(() => {
                                alert('An unexpected error occurred. Please try again.');
                            });
                        }
                    }
                };
            }
            // Re-upload Face Image button logic
            const reuploadBtn = document.getElementById('reupload-face-btn');
            if (reuploadBtn) {
                reuploadBtn.onclick = function() {
                    if (confirm('Are you sure you want to re-upload your face image?')) {
                        window.location.href = 'face_image.php?reupload=1';
                    }
                };
            }
        });
    </script>
</body>
</html>
