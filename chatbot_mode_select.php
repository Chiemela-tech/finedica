<?php
session_start();
if (!isset($_SESSION['user_email'])) {
    header('Location: ../php/index.php');
    exit;
}
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Choose Chatbot Response Mode</title>
    <link rel="stylesheet" href="../css/main.css">
    <style>
        body { background: #f4f8fb; }
        .mode-select-container {
            max-width: 500px;
            margin: 80px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 32px rgba(33,150,243,0.13);
            padding: 40px 32px 32px 32px;
            text-align: center;
        }
        .mode-btn {
            display: block;
            width: 100%;
            margin: 24px 0;
            padding: 28px 0;
            font-size: 1.3rem;
            font-weight: 600;
            border-radius: 14px;
            border: none;
            background: linear-gradient(90deg, #2196f3 0%, #21cbf3 100%);
            color: #fff;
            box-shadow: 0 2px 16px rgba(33,150,243,0.16);
            cursor: pointer;
            transition: transform 0.1s, box-shadow 0.1s;
        }
        .mode-btn:hover {
            transform: scale(1.04);
            box-shadow: 0 4px 24px rgba(33,150,243,0.22);
        }
        .mode-btn.secondary {
            background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%);
            color: #222;
        }
        .mode-title {
            font-size: 2rem;
            margin-bottom: 18px;
            color: #2196f3;
        }
        .mode-desc {
            color: #444;
            margin-bottom: 32px;
        }
    </style>
</head>
<body>
    <div class="mode-select-container">
        <div class="mode-title">How do you want your chatbot answers?</div>
        <div class="mode-desc">Hi <?php echo htmlspecialchars($userName); ?>! Please choose your preferred response style for the chatbot. You can change this later.</div>
        <button class="mode-btn" id="quick-btn">I like to get short-quick responses</button>
        <button class="mode-btn secondary" id="long-btn">I like to get long answers (takes time to response)</button>
    </div>
    <script>
        document.getElementById('quick-btn').onclick = function() {
            sessionStorage.setItem('chatbot_mode', 'quick');
            window.location.href = '../chatbot/chatbot.php';
        };
        document.getElementById('long-btn').onclick = function() {
            sessionStorage.setItem('chatbot_mode', 'long');
            window.location.href = '../chatbot/chatbot.php';
        };
    </script>
</body>
</html>
