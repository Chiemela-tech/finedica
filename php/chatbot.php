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
$dbname = 'user_reg_db';
$username = 'root';
$password = 'finedica';

try {
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=user_reg_db", 'root', 'finedica');
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
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=user_reg_db", 'root', 'finedica');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->prepare("SELECT face_image_url FROM face_image_responses WHERE email = :email ORDER BY id DESC LIMIT 1");
    $stmt->bindParam(':email', $userEmail);
    $stmt->execute();
    $faceImageUrl = $stmt->fetchColumn();
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot - 20:20 FC</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/chatbotstyle.css">

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
                <li><a href="ethics_policy.php">AI Ethics Policy</a></li>
                <li><a href="..generate_avatar/avatar_frontpage.php">Avatar</a></li>
                <li><a href="../chatbot/chatbot.php">Chatbot</a></li>
                <li><a href="logout.php">Logout <?php echo htmlspecialchars($userName); ?></a></li>
            </ul>
        </nav>
    </header>
    <main>
        <div class="layout-container">
        <h4>Hi..<?php echo htmlspecialchars($userName); ?>, I am your Future Self </h4>
        </div>    
            <!-- Avatar Full-Size Section -->
            <div class="avatar-fullsize">
         
                <div id="avatarContainer">
                    <?php if ($avatarPath): ?>
                        <img src="<?php echo htmlspecialchars($avatarPath); ?>" alt="Generated Avatar">
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
    <script>
        const userEmail = "<?php echo $_SESSION['user_email']; ?>";
        // Fetch and Display Avatar on Page Load
        document.addEventListener('DOMContentLoaded', function () {
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
        });

        // Chatbot Functionality
        function sendMessage() {
            const userInput = document.getElementById('userInput').value.trim();
            if (!userInput) return;

            const chatHistory = document.getElementById('chatHistory');
            const userMessage = `<div class="message user">${userInput}</div>`;
            chatHistory.innerHTML += userMessage;

            // Send the message to the Flask API
            fetch('http://localhost/chat', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: userInput })
            })
            .then(res => res.json())
            .then(data => {
                const botMessage = `<div class="message bot">${data.response}</div>`;
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

        document.getElementById('userInput').addEventListener('keydown', function(event) {
            if (event.key === 'Enter' && this.value.trim() !== '') {
                event.preventDefault();
                sendMessage();
            }
        });
    </script>
</body>
</html>