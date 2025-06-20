<?php
session_start();
// Check if the user is logged in
if (!isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}
$userName = $_SESSION['user_name'];
$userEmail = $_SESSION['user_email'];

// Database connection
$host = 'localhost';
$dbname = 'user_reg_db'; // <-- Added this line
$username = 'root';
$password = 'finedica';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fetch the avatar path for the logged-in user
    $stmt = $pdo->prepare("SELECT image_path FROM avatars WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();
    $avatarPath = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("SELECT face_image_url FROM face_image_responses WHERE email = :email ORDER BY id DESC LIMIT 1");
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();
    $faceImageUrl = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Fetch user's gender from the users table
$userGender = null;
try {
    $stmt = $pdo->prepare("SELECT gender FROM users WHERE email = :email LIMIT 1");
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();
    $userGender = $stmt->fetchColumn();
} catch (PDOException $e) {
    $userGender = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot - 20:20 FC</title>
    <link rel="stylesheet" href="../chatbot/main.css">
    <link rel="stylesheet" href="../chatbot/chatbotstyle.css">
    <link rel="stylesheet" href="../css/progressbar.css">
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
                <li><a href="chatbot.php">Chatbot</a></li>
                <li><a href="../php/logout.php">Logout <?php echo htmlspecialchars($userName); ?></a></li>
            </ul>
        </nav>
    </header>
    <?php $progressStep = 5; include '../php/progressbar.php'; ?>
    <main>
        <div class="layout-container">
        <h4>Hi..<?php echo htmlspecialchars($userName); ?>, I am your Future Self </h4>
        </div>    
        <!-- Chatbot Mode Toggle -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 16px; align-items: center;">
            <span style="margin-right: 16px; font-weight: 500; color: #2196f3;">Select your preferred chat mode</span>
            <div id="chatbotModeToggle" style="display: flex; gap: 0; align-items: center;">
                <button id="quickModeBtn" class="mode-btn">Short-Quick <span id="quickModeIcon" style="margin-left:6px;vertical-align:middle;"></span></button>
                <button id="longModeBtn" class="mode-btn">Long-Late <span id="longModeIcon" style="margin-left:6px;vertical-align:middle;"></span></button>
            </div>
        </div>
        <!-- Avatar Full-Size Section -->
        <div class="avatar-fullsize">
         
                <div id="avatarContainer">
                    <?php if ($avatarPath): ?>
                        <img src="../avatars/<?php echo htmlspecialchars($avatarPath); ?>" alt="Generated Avatar">
                    <?php else: ?>
                        <p>No avatar generated yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
            <!-- Chatbot Section -->
         <div class="chatbot-widget">
                <div class="chat-messages">
                    <div class="message bot">
                        <h3>Hi <?php echo htmlspecialchars($userName); ?>,</h3>
                        <h4>Let's talk! You can ask me anything you like.</h4>
                    </div>
                    <div class="chat-history" id="chatHistory">
                        <!-- Chat history will be displayed here -->
                    </div>
                    <div class="input-area">
                        <input type="text" id="userInput" placeholder="Type your message...">
                        <button onclick="sendMessage()">Send</button>
                    </div>
                </div>
            </div>   
        </div>
    </main>
    <style>
        .mode-btn {
            padding: 10px 24px;
            font-size: 1rem;
            border: none;
            border-radius: 8px 0 0 8px;
            background: #e3eafc;
            color: #2196f3;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s, color 0.2s;
        }
        .mode-btn:last-child {
            border-radius: 0 8px 8px 0;
            border-left: 1px solid #c3d3ee;
        }
        .mode-btn.selected {
            background: #2196f3;
            color: #fff;
            box-shadow: 0 2px 8px rgba(33,150,243,0.13);
        }
    </style>
    <script>
        const userEmail = "<?php echo $_SESSION['user_email']; ?>";
        const switchSvg = `<svg width='38' height='22' viewBox='0 0 38 22' fill='none' xmlns='http://www.w3.org/2000/svg' style='vertical-align:middle;'><rect x='1' y='1' width='36' height='20' rx='10' fill='#1fa038' stroke='#1fa038' stroke-width='2'/><circle cx='11' cy='11' r='8' fill='#6ee087'/><polyline points='8,12 11,15 16,8' fill='none' stroke='white' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'/></svg>`;
        // Chatbot Mode Toggle Logic
        function setChatbotMode(mode) {
            sessionStorage.setItem('chatbot_mode', mode);
            updateModeButtons();
        }
        function updateModeButtons() {
            const mode = sessionStorage.getItem('chatbot_mode') || 'quick';
            const quickBtn = document.getElementById('quickModeBtn');
            const longBtn = document.getElementById('longModeBtn');
            const quickIcon = document.getElementById('quickModeIcon');
            const longIcon = document.getElementById('longModeIcon');
            if (mode === 'quick') {
                quickBtn.classList.add('selected');
                longBtn.classList.remove('selected');
                quickIcon.innerHTML = switchSvg;
                longIcon.innerHTML = '';
            } else {
                quickBtn.classList.remove('selected');
                longBtn.classList.add('selected');
                quickIcon.innerHTML = '';
                longIcon.innerHTML = switchSvg;
            }
        }
        document.addEventListener('DOMContentLoaded', function () {
            // Fetch and Display Avatar on Page Load
            fetch('../php/get_avatar.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                console.log('Response:', data);
                // Display the generated avatar
                if (data.status === 'ok' && data.avatar_path) {
                    document.getElementById('avatarContainer').innerHTML = `
                        <img src="${data.avatar_path}?t=${new Date().getTime()}" alt="Generated Avatar">
                    `;
                } else {
                    document.getElementById('avatarContainer').innerHTML = `
                        <p>No avatar generated yet.</p>
                    `;
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                document.getElementById('avatarContainer').innerHTML = `
                    <p>Error loading avatar. Please try again later.</p>
                `;
            });
            updateModeButtons();
            document.getElementById('quickModeBtn').onclick = function() { setChatbotMode('quick'); };
            document.getElementById('longModeBtn').onclick = function() { setChatbotMode('long'); };
        });

        // Chatbot Functionality
        function getChatbotApiUrl() {
            const mode = sessionStorage.getItem('chatbot_mode');
            if (mode === 'quick') {
                return 'http://35.232.121.220:5003/chat'; // chatbotquick.py
            } else {
                return 'http://35.232.121.220:5002/chat'; // chatbot.py
            }
        }
        function sendMessage() {
            const userInput = document.getElementById('userInput').value.trim();
            if (!userInput) return;

            const chatHistory = document.getElementById('chatHistory');
            const userMessage = `<div class="message user">${userInput}</div>`;
            chatHistory.innerHTML += userMessage;

            // Send the message to the correct Flask API, including gender and email
            fetch(getChatbotApiUrl(), { 
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    message: userInput, 
                    gender: "<?php echo $userGender; ?>",
                    email: userEmail
                })
            })
            .then(res => res.json())
            .then(data => {
                let botMessage = `<div class="message bot">${data.response}</div>`;
                if (data.image) {
                    botMessage += `<div class='message bot'><img src="../chatbot/${data.image}?t=${Date.now()}" alt="Generated Avatar" style="max-width:200px;max-height:200px;border-radius:10px;margin-top:8px;"></div>`;
                }
                chatHistory.innerHTML += botMessage;
                chatHistory.scrollTop = chatHistory.scrollHeight;
            })
            .catch(err => {
                console.error('Error:', err);
                const errorMessage = `<div class="message bot">Sorry, something went wrong. Please try again later.</div>`;
                chatHistory.innerHTML += errorMessage;
            });

            document.getElementById('userInput').value = '';
        }

        // Enable sending message with Enter key (robust, prevents double send)
        let isSending = false;
        document.getElementById('userInput').addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && this.value.trim() !== '' && !isSending) {
                event.preventDefault();
                isSending = true;
                sendMessage();
                setTimeout(() => { isSending = false; }, 500); // Prevent double send
            } else if (event.key === 'Enter' && this.value.trim() === '') {
                // Always reset isSending if input is empty
                isSending = false;
            }
        });
        document.getElementById('userInput').addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && this.value.trim() !== '') {
                event.preventDefault();
                sendMessage();
            }
        });
    </script>
</body>
</html>
