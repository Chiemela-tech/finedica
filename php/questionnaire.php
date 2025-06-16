<!-- src/html/index.html -->
<!-- filepath: c:\xampp\htdocs\2020FC\src\php\dashboard.php -->
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userName = $_SESSION['user_name'];

// Check if user has completed psychometric test
$pdo = new PDO("mysql:host=localhost;port=3306;dbname=user_reg_db", 'root', 'finedica');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$userEmail = $_SESSION['user_email'];
$psychometric = $pdo->prepare("SELECT 1 FROM psychometric_test_responses WHERE email = :email LIMIT 1");
$psychometric->execute([':email' => $userEmail]);
$has_psychometric = $psychometric->fetchColumn();
$future_self = $pdo->prepare("SELECT 1 FROM future_self_responses WHERE email = :email LIMIT 1");
$future_self->execute([':email' => $userEmail]);
$has_futureself = $future_self->fetchColumn();

// Determine progress for button states and ticks
$psychometric_done = isset($_SESSION['psychometric_test_completed']) && $_SESSION['psychometric_test_completed'];
$futureself_done = isset($_SESSION['futureself_completed']) && $_SESSION['futureself_completed'];

// Only enable future self if psychometric is done
$enable_futureself = $psychometric_done;
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>20:20 FC - FINEDICA</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/faceimagestyle.css">
    <link rel="stylesheet" href="../css/progressbar.css">
    <link rel="stylesheet" href="../css/questionnairestyle.css">
    <style>
        body {
            background: linear-gradient(120deg, #e0f7fa 0%, #f1f8e9 100%);
            font-family: 'Segoe UI', Arial, sans-serif;
        }
        .welcome-intro-container {
            background: rgba(16, 134, 230, 0.1);
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(33,150,243,0.10);
            padding: 36px 28px 32px 28px;
            margin: 12px auto 24px auto;
            max-width: 700px;
            text-align: center;
        }
        .welcome-intro-container h1 {
            color: #2196f3;
            margin-bottom: 0.2em;
            margin-top: 0.02em;

        }
        .welcome-intro-container h2 {
            color: #388e3c;
            margin-bottom: 0.8em;
        }
        .welcome-intro-container p {
            font-size: 1.15em;
            color: #444;
            margin-bottom: 0;
        }
        .setup-container {
            background: #f1f8e9;
            border-radius: 14px;
            box-shadow: 0 4px 24px rgba(33,150,243,0.10);
            padding: 32px 24px;
            margin: 24px auto 24px auto;
            max-width: 700px;
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            justify-content: space-between;
        }
        .setup-container > div {
            flex: 1 1 180px;
            min-width: 180px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(33,150,243,0.06);
            margin: 8px 0;
            text-align: center;
            padding: 18px 8px;
            transition: box-shadow 0.2s, transform 0.2s;
        }
        .setup-container > div:hover {
            box-shadow: 0 6px 24px rgba(33,150,243,0.13);
            transform: translateY(-2px) scale(1.03);
        }
        .setup-container button {
            background: linear-gradient(90deg, #21f336 0%, #2196f3 100%);
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 16px 0;
            font-size: 1.1em;
            font-weight: bold;
            width: 100%;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(33,150,243,0.08);
            transition: background 0.2s, transform 0.2s;
        }
        .setup-container button:hover {
            background: linear-gradient(90deg, #2196f3 0%, #21f336 100%);
            color: #fff;
            transform: translateY(-2px) scale(1.04);
        }
        .setup-container h1 {
            font-size: 1.1em;
            margin: 0;
        }
        @media (max-width: 900px) {
            .setup-container {
                flex-direction: column;
                gap: 0;
            }
            .setup-container > div {
                min-width: unset;
            }
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <h1>20:20 FC - FINEDICA</h1>
                <p>Expert Financial Coaching</p>
            </div>
            <ul>
                <li><a href="home.php">Home</a></li>
                <li><a href="questionnaire.php">Questionnaire</a></li>
                <li><a href="#contact">Contact</a></li>
                <li><a href="../generate_avatar/avatar_frontpage.php">Avatar</a></li>
                <li><a href="../chatbot/chatbot.php">Chatbot</a></li>
                <li><a href="logout.php" style="font-size: 14px; color:rgb(7, 249, 168)">Logout <?php echo htmlspecialchars($userName);?></a></li>
            </ul>
        </nav>  
    </header>
    <?php $progressStep = 1; include 'progressbar.php'; ?>
    <main>
        <div class="welcome-intro-container">
            <h1>Welcome, <?php echo htmlspecialchars($userName); ?>!</h1>
            <h2>You are now logged in.</h2>
            <p>To get started, please complete the psychometric test. Once you finish, you’ll unlock your future self, upload your image, and create your personalized avatar.</p><br>
            <h3> Let’s begin your financial coaching journey!</h3>
        </div>
        <div class="setup-container">    
            <div class="profile-setup">
                <a href="../psychometric_test/psychometric_test.php">
                    <button onclick="startQuestionnaire()"><h1><?php echo $has_psychometric ? 'See Psychometric Test Results' : 'Take Test'; ?></h1></button>
                </a>
            </div>
            <div class="futureself-setup">
                <a href="../future_self/futureself.php">
                <button onclick="startFutureSelf()"><h1><?php echo $has_futureself ? 'See Future Self Test Results' : 'Future Self'; ?></h1></button>
                </a>
            </div>
            <div class="avatar-setup">
                <a href="../future_self/face_image_responses.php">
                <button onclick="uploadAvatar()"><h1>Make Avatar</h1></button>
                </a>
            </div>
            <div class="chatbot-setup">
                <a href="../chatbot/chatbot.php">
                    <button onclick="startChatbot()"><h1>Start Advice</h1></button>
                </a>
            </div>
            <div class="expenditure-setup">
                <a href="../expenditure/expenditure_index.php">
                    <button onclick="startExpenditure()"><h1>Track Expenditure</h1></button>
                </a>
            </div>
        </div>
        <div class="questionnaire-btns">
            <a href="../psychometric_test/psychometric_test.php" class="questionnaire-btn <?php echo $psychometric_done ? 'completed' : ''; ?>" <?php if ($psychometric_done) { ?>title="Completed"<?php } ?>>
                <?php if ($psychometric_done): ?>
                    <span class="tick-icon">✔</span>
                <?php endif; ?>
                Psychometric Test
            </a>
            <a href="../future_self/futureself.php" class="questionnaire-btn <?php echo $enable_futureself ? '' : 'disabled'; ?> <?php echo $futureself_done ? 'completed' : ''; ?>" <?php if (!$enable_futureself) { ?>tabindex="-1" aria-disabled="true"<?php } ?>>
                <?php if ($futureself_done): ?>
                    <span class="tick-icon">✔</span>
                <?php endif; ?>
                Future Self
            </a>
        </div>
    </main>
    <script src="../js/main.js"></script>
</body>
</html>