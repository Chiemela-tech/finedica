<?php
session_start();

if (!isset($_SESSION['submitted_stage']) || !isset($_SESSION['submitted_responses'])) {
    header('Location: futureself.php');
    exit;
}

$stage = $_SESSION['submitted_stage'];
$responses = $_SESSION['submitted_responses'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Your Responses</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/expenditurestyle.css">
    <link rel="stylesheet" href="../css/futureselfstyle.css">
    <link rel="stylesheet" href="future_self_style.css">
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
                <li><a href="../php/logout.php" style="font-size: 14px; color:rgb(7, 249, 168)">Logout</a></li>
            </ul>
        </nav>
    </header>
    <?php $progressStep = 2; include '../php/progressbar.php'; ?>
    <main>
        <div class="banner success-banner">Thank you! Your responses have been saved successfully.</div>
        <div class="review-container card">
            <h3><span class="icon">📝</span> Review Your Responses</h3>
            <div class="stage-selected"><strong>Stage of Life:</strong> <?php echo htmlspecialchars($stage); ?></div>
            <?php foreach ($responses as $category => $questions): ?>
                <div class="category-container">
                    <h4 class="category-title"><?php echo htmlspecialchars($category); ?></h4>
                    <ul class="qa-list">
                        <?php foreach ($questions as $question => $response): ?>
                            <?php
                            // Hide '- Other' if empty or if main question is not answered as 'Other'
                            if (strpos($question, '- Other') !== false) {
                                $main_question = trim(str_replace(' - Other', '', $question));
                                $main_answer = $questions[$main_question] ?? '';
                                if ($response === '' || (is_string($main_answer) && strtolower($main_answer) !== 'other')) continue;
                            }
                            // Hide main question if answered as 'Other' and specific answer is filled
                            if (isset($questions[$question . ' - Other']) && ((is_string($response) && strtolower($response) === 'other') || (is_array($response) && in_array('Other', $response))) && $questions[$question . ' - Other'] !== '') continue;
                            // Show multi-answer as comma-separated
                            if (is_array($response)) {
                                $response = implode(', ', $response);
                            }
                            if ($response === '') continue;
                            ?>
                            <li>
                                <span class="question-text"><?php echo htmlspecialchars($question); ?></span>
                                <span class="answer-text"><?php echo htmlspecialchars($response); ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
            <form action="face_image.php" method="GET" class="next-form nav-buttons-row">
                <button type="button" class="nav-btn nav-btn-left" onclick="window.location.href='../psychometric_test/psychometric_test.php';return false;">Back</button>
                <button type="submit" class="nav-btn nav-btn-right">Next</button>
            </form>
        </div>
    </main>
    <link rel="stylesheet" href="../css/faceimagestyle.css">
</body>
</html>