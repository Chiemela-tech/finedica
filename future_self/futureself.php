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
    $pdo = new PDO("mysql:host=localhost;port=3306;dbname=user_reg_db", 'root', 'finedica');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user already has a future self response
$stmt = $pdo->prepare("SELECT stage, category, question, response FROM future_self_responses WHERE email = :email");
$stmt->execute([':email' => $userEmail]);
$existing_responses = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stage = $_POST['stage'];
    $responses = $_POST['responses'];

    $stmt = $pdo->prepare("INSERT INTO future_self_responses (email, stage, category, question, response) VALUES (:email, :stage, :category, :question, :response)");

    foreach ($responses as $category => $questions) {
        foreach ($questions as $question => $response) {
            // --- FIX: For multi-answer (array) questions, replace 'Other' with the specific answer if provided ---
            if (is_array($response)) {
                $other_key = $question . ' - Other';
                if (isset($questions[$other_key]) && $questions[$other_key] !== '') {
                    $response = array_map(function($v) use ($questions, $other_key) {
                        return $v === 'Other' ? $questions[$other_key] : $v;
                    }, $response);
                }
                $response = serialize($response); // Always store as serialized array
            }
            // --- END FIX ---
            $stmt->bindValue(':email', $userEmail, PDO::PARAM_STR);
            $stmt->bindValue(':stage', $stage, PDO::PARAM_STR);
            $stmt->bindValue(':category', $category, PDO::PARAM_STR);
            $stmt->bindValue(':question', $question, PDO::PARAM_STR);
            $stmt->bindValue(':response', $response, PDO::PARAM_STR);

            // Execute the query and check for errors
            if (!$stmt->execute()) {
                echo "<div class='error-message'>Error inserting data: " . htmlspecialchars(implode(", ", $stmt->errorInfo())) . "</div>";
                exit;
            }
        }
    }

    // Store the submitted data in the session for review
    $_SESSION['submitted_stage'] = $stage;
    $_SESSION['submitted_responses'] = $responses;

    // Redirect to the review page
    header('Location: futureself_responses.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>20:20 FC - FINEDICA</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/expenditurestyle.css">
    <link rel="stylesheet" href="../css/futureselfstyle.css">
    <link rel="stylesheet" href="../css/progressbar.css">
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
                <li><a href="../php/ethics_policy.php">AI Ethics Policy</a></li>
                <li><a href="../generate_avatar/avatar_frontpage.php">Avatar</a></li>
                <li><a href="../chatbot/chatbot.php">Chatbot</a></li>
                <li><a href="../php/logout.php" style="font-size: 14px; color:rgb(7, 249, 168)">Logout <?php echo htmlspecialchars($userName); ?></a></li>
            </ul>
        </nav>
    </header>
    <?php $progressStep = 2; include '../php/progressbar.php'; ?>
    <main>
        <div class="futureself-hero">
            <h1><span class="icon">🌱 Future Self</h1>
            <p class="subtitle">Select a stage of life and answer the questions to envision your future.</p>  
        </div>

        <div class="futureself-intro enhanced-intro-card">
            <div class="intro-icon">🌟</div>
            <div class="intro-content">
                <h2>Envision Your Future Self</h2>
                <p>These questions help FINEDICA construct an avatar of your future self when you have achieved your most relevant financial goal.</p>
                <ul>
                    <li>If you are trying to buy your first home, your future self will be the age at which you wish to get on the property ladder.</li>
                    <li>If you are planning retirement, then your future self will be at your desired retirement age.</li>
                </ul>
                <p>Before answering, think carefully about your responses. The more detail you put into nailing your future self, the more your brain will buy into it. Be realistic: for example, if you are buying your first home at 30, you may not be earning £100,000 a year, but you may have a lot of money in a savings vehicle like a lifetime ISA.</p>
            </div>
        </div>

        <?php if ($existing_responses && count($existing_responses) > 0): ?>
            <div class="future-self-results card">
                <h2><span class="icon">📋</span> Your Previous Future Self Responses</h2>
                <?php
                $stage = $existing_responses[0]['stage'] ?? '';
                $grouped = [];
                foreach ($existing_responses as $resp) {
                    $question = $resp['question'];
                    $response = $resp['response'];
                    $decoded = @unserialize($response);
                    if ($decoded !== false && is_array($decoded)) {
                        $response = $decoded;
                    } elseif (is_string($response) && ($jsonDecoded = json_decode($response, true)) && is_array($jsonDecoded)) {
                        $response = $jsonDecoded;
                    }
                    $grouped[$resp['category']][$question] = $response;
                }
                ?>
                <div class="stage-selected"><strong>Stage of Life:</strong> <?php echo htmlspecialchars($stage); ?></div>
                <?php foreach ($grouped as $category => $qas): ?>
                    <div class="category-container">
                        <h4 class="category-title"><?php echo htmlspecialchars($category); ?></h4>
                        <ul class="qa-list">
                            <?php foreach ($qas as $question => $response): ?>
                                <?php
                                // Hide '- Other' if empty or if main question is not answered as 'Other'
                                if (strpos($question, '- Other') !== false) {
                                    $main_question = trim(str_replace(' - Other', '', $question));
                                    $main_answer = $qas[$main_question] ?? '';
                                    if ($response === '' || (is_string($main_answer) && strtolower($main_answer) !== 'other')) continue;
                                }
                                // Hide main question if answered as 'Other' and specific answer is filled
                                if (isset($qas[$question . ' - Other']) && ((is_string($response) && strtolower($response) === 'other') || (is_array($response) && in_array('Other', $response))) && $qas[$question . ' - Other'] !== '') continue;
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
                <div class="nav-buttons-row">
                    <button id="back-to-psychometric" class="nav-btn nav-btn-left" onclick="window.location.href='../psychometric_test/psychometric_test.php';return false;">Back</button>
                    <button id="next-category" class="nav-btn nav-btn-right" onclick="window.location.href='face_image.php';">Next</button>
                </div>
            </div>
        <?php else: ?>
        <form action="futureself.php" method="POST" class="futureself-form card" id="futureself-form" autocomplete="off">
            <fieldset id="category-0" class="category-section" style="display:block;">
                <legend><span class="icon">🎯</span> Select a Stage of Life</legend>
                <label><input type="radio" name="stage" value="Buying your first home" required> Age 18-30: Buying your first home</label><br>
                <label><input type="radio" name="stage" value="Becoming a Parent" required> Age 25-35: Becoming a Parent</label><br>
                <label><input type="radio" name="stage" value="Planning Retirement" required> Age 35-50: Planning Retirement</label><br>
                <label><input type="radio" name="stage" value="Retirement" required> Age 60+: Retirement</label><br>
                <label><input type="radio" name="stage" value="General Financial Coaching" required> General Financial Coaching</label>
            </fieldset>
            <fieldset id="category-1" class="category-section" style="display:none;">
                <legend>Physicality</legend>
                <label>1. How old will you be when you achieve your financial goal?</label><br>
                <select name="responses[Physicality][1. How old will you be when you achieve your financial goal?]" required>
                    <option value="">Select...</option>
                    <option>25 to 30</option>
                    <option>31 to 35</option>
                    <option>36 to 40</option>
                    <option>41 to 45</option>
                    <option>46 to 50</option>
                    <option>51 to 55</option>
                    <option>56 to 60</option>
                    <option>61 to 65</option>
                    <option>66 to 70</option>
                    <option>70 plus</option>
                </select><br><br>
                <label>2. What colour will your hair be?</label><br>
                <select name="responses[Physicality][2. What colour will your hair be?]" class="hair-colour-select" required>
                    <option value="">Select...</option>
                    <option>Black/Brown</option>
                    <option>Red</option>
                    <option>Blonde</option>
                    <option>Grey</option>
                    <option>No Hair (Bold)</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Physicality][2. What colour will your hair be? - Other]" class="hair-colour-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>3. How will your posture exude confidence and satisfaction in achieving a major financial milestone?</label><br>
                <select name="responses[Physicality][3. How will your posture exude confidence and satisfaction in achieving a major financial milestone?]" class="posture-select" required>
                    <option value="">Select...</option>
                    <option>More relaxed</option>
                    <option>More confident</option>
                    <option>Act with more purpose</option>
                    <option>More jovial</option>
                    <option>No change</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Physicality][3. How will your posture exude confidence and satisfaction in achieving a major financial milestone? - Other]" class="posture-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>4. How will you dress?</label><br>
                <select name="responses[Physicality][4. How will you dress?]" class="dress-select" required>
                    <option value="">Select...</option>
                    <option>Business attire</option>
                    <option>Smart casual</option>
                    <option>Casual</option>
                    <option>Sportswear</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Physicality][4. How will you dress? - Other]" class="dress-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>5. What standard of clothes will you buy?</label><br>
                <select name="responses[Physicality][5. What standard of clothes will you buy?]" required>
                    <option value="">Select...</option>
                    <option>Designer labels</option>
                    <option>High street labels</option>
                    <option>Non-branded clothes</option>
                </select><br><br>
                <label>6. Do you exercise?</label><br>
                <select name="responses[Physicality][6. Do you exercise?]" required>
                    <option value="">Select...</option>
                    <option>Gym</option>
                    <option>Workout at home</option>
                    <option>I don’t exercise</option>
                </select><br><br>
            </fieldset>
            <fieldset id="category-2" class="category-section" style="display:none;">
                <legend>Income and Personal Finances</legend>
                <label>1. How much money will you earn?</label><br>
                <select name="responses[Income and Personal Finances][1. How much money will you earn?]" class="income-earn-select" required>
                    <option value="">Select...</option>
                    <option>£15,0000 to 25,000</option>
                    <option>£25,001 to £35,000</option>
                    <option>£35,001 to £40,000</option>
                    <option>£40,001 to £45,000</option>
                    <option>£45,0001 to £50,000</option>
                    <option>£50,000 plus</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Income and Personal Finances][1. How much money will you earn? - Other]" class="income-earn-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>2. What is your most valuable financial asset (house, etc)?</label><br>
                <select name="responses[Income and Personal Finances][2. What is your most valuable financial asset (house, etc)?]" class="asset-select" required>
                    <option value="">Select...</option>
                    <option>House</option>
                    <option>Investments (e.g ISAs)</option>
                    <option>Pension</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Income and Personal Finances][2. What is your most valuable financial asset (house, etc)? - Other]" class="asset-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>3. Will you have a mortgage?</label><br>
                <select name="responses[Income and Personal Finances][3. Will you have a mortgage?]" required>
                    <option value="">Select...</option>
                    <option>Yes</option>
                    <option>No</option>
                    <option>Don’t know</option>
                </select><br><br>
                <label>4. What investments will you have?</label><br>
                <select name="responses[Income and Personal Finances][4. What investments will you have?]" required>
                    <option value="">Select...</option>
                    <option>Individual Savings Accounts (ISAs)</option>
                    <option>Cash savings plans</option>
                    <option>No savings</option>
                </select><br><br>
                <label>5. How will you have saved?</label><br>
                <select name="responses[Income and Personal Finances][5. How will you have saved?]" class="saved-select" required>
                    <option value="">Select...</option>
                    <option>£0 to £5,000</option>
                    <option>£5,001 to £10,000</option>
                    <option>£10,001 to £15,000</option>
                    <option>£15,001 to £20,000</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Income and Personal Finances][5. How will you have saved? - Other]" class="saved-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>6. How much disposable income will you have at this time?</label><br>
                <select name="responses[Income and Personal Finances][6. How much disposable income will you have at this time?]" required>
                    <option value="">Select...</option>
                    <option>Up to 10% of your income</option>
                    <option>Up to 20% of your income</option>
                    <option>Up to 30% of your income</option>
                    <option>30% above</option>
                </select><br><br>
            </fieldset>
            <fieldset id="category-3" class="category-section" style="display:none;">
                <legend>Emotional/Spiritual Values</legend>
                <label>1. What will achieving this goal do for you as a person? For instance, if you sacrifice to get on the property ladder, will you have a sense of pride and achievement?</label><br>
                <select name="responses[Emotional/Spiritual Values][1. What will achieving this goal do for you as a person? For instance, if you sacrifice to get on the property ladder, will you have a sense of pride and achievement?]" required>
                    <option value="">Select...</option>
                    <option>Feel more secure</option>
                    <option>Feel accomplished</option>
                    <option>Negative feelings, perhaps you don’t like the sacrifice?</option>
                    <option>Feel responsible</option>
                </select><br><br>
                <label>2. What are your core values (pick three)?</label><br>
                <div class="checkbox-group" data-min="3">
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Love"> Love</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Charity"> Charity</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Gratitude"> Gratitude</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Resilience"> Resilience</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Honesty"> Honesty</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Ambition"> Ambition</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Honour"> Honour</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Sacrifice"> Sacrifice</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Community"> Community</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Spirituality"> Spirituality</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Faith"> Faith</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Kindness"> Kindness</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)?][]" value="Other" class="core-values-other-checkbox"> Other (please specify)</label>
                    <input type="text" name="responses[Emotional/Spiritual Values][2. What are your core values (pick three)? - Other]" class="core-values-other" style="display:none;" placeholder="Please specify" />
                </div><br>
                <label>3. How will you keep yourself emotionally balanced and healthy (pick three)?</label><br>
                <div class="checkbox-group" data-min="3">
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][3. How will you keep yourself emotionally balanced and healthy (pick three)?][]" value="Faith"> Faith</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][3. How will you keep yourself emotionally balanced and healthy (pick three)?][]" value="Meditation"> Meditation</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][3. How will you keep yourself emotionally balanced and healthy (pick three)?][]" value="Exercise"> Exercise</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][3. How will you keep yourself emotionally balanced and healthy (pick three)?][]" value="Support from friends and family"> Support from friends and family</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][3. How will you keep yourself emotionally balanced and healthy (pick three)?][]" value="Other" class="balanced-other-checkbox"> Other (please specify)</label>
                    <input type="text" name="responses[Emotional/Spiritual Values][3. How will you keep yourself emotionally balanced and healthy (pick three)? - Other]" class="balanced-other" style="display:none;" placeholder="Please specify" />
                </div><br>
                <label>4. How do your core values relate to your financial goal (pick two)?</label><br>
                <div class="checkbox-group" data-min="2">
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][4. How do your core values relate to your financial goal (pick two)?][]" value="Help you stay grounded"> Help you stay grounded</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][4. How do your core values relate to your financial goal (pick two)?][]" value="Help you stay resilient"> Help you stay resilient</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][4. How do your core values relate to your financial goal (pick two)?][]" value="Help you dream big for the future"> Help you dream big for the future</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][4. How do your core values relate to your financial goal (pick two)?][]" value="Help you stay on track"> Help you stay on track</label>
                    <label><input type="checkbox" name="responses[Emotional/Spiritual Values][4. How do your core values relate to your financial goal (pick two)?][]" value="Other" class="core-values-relate-other-checkbox"> Other (please specify)</label>
                    <input type="text" name="responses[Emotional/Spiritual Values][4. How do your core values relate to your financial goal (pick two)? - Other]" class="core-values-relate-other" style="display:none;" placeholder="Please specify" />
                </div><br>
            </fieldset>
            <fieldset id="category-4" class="category-section" style="display:none;">
                <legend>LifeStyle</legend>
                <label>1. What will your hobbies and interests be?</label><br>
                <select name="responses[LifeStyle][1. What will your hobbies and interests be?]" class="hobbies-select" required>
                    <option value="">Select...</option>
                    <option>Sport</option>
                    <option>Exercise</option>
                    <option>Reading</option>
                    <option>Gaming</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[LifeStyle][1. What will your hobbies and interests be? - Other]" class="hobbies-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>2. How many holidays a year will you go on?</label><br>
                <select name="responses[LifeStyle][2. How many holidays a year will you go on?]" required>
                    <option value="">Select...</option>
                    <option>One</option>
                    <option>Two</option>
                    <option>Three</option>
                    <option>Three plus</option>
                </select><br><br>
                <label>3. What will you have to sacrifice to achieve your financial goals?</label><br>
                <select name="responses[LifeStyle][3. What will you have to sacrifice to achieve your financial goals?]" class="sacrifice-select" required>
                    <option value="">Select...</option>
                    <option>Make sacrifices in your leisure spending</option>
                    <option>Holidays</option>
                    <option>Alcohol or cigarettes</option>
                    <option>Leisure time (i.e. through working extra hours)</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[LifeStyle][3. What will you have to sacrifice to achieve your financial goals? - Other]" class="sacrifice-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>4. How can you reframe these sacrifices?</label><br>
                <select name="responses[LifeStyle][4. How can you reframe these sacrifices?]" class="reframe-select" required>
                    <option value="">Select...</option>
                    <option>I am investing in my future self</option>
                    <option>I am achieving something meaningful</option>
                    <option>I am building financial security</option>
                    <option>I am building financial security for my family</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[LifeStyle][4. How can you reframe these sacrifices? - Other]" class="reframe-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>5. How do you balance your lifestyle against achieving and maintaining your financial goals?</label><br>
                <select name="responses[LifeStyle][5. How do you balance your lifestyle against achieving and maintaining your financial goals?]" required>
                    <option value="">Select...</option>
                    <option>Budgeting</option>
                    <option>Journaling</option>
                    <option>Using an app</option>
                    <option>Automating a set amount to save each month</option>
                    <option>Accountability partner</option>
                </select><br><br>
            </fieldset>
            <fieldset id="category-5" class="category-section" style="display:none;">
                <legend>Profession</legend>
                <label>1. What do you do for work?</label><br>
                <input type="text" name="responses[Profession][1. What do you do for work?]" required placeholder="Your answer"><br><br>
                <label>2. How much do you earn?</label><br>
                <select name="responses[Profession][2. How much do you earn?]" required>
                    <option value="">Select...</option>
                    <option>£15,000 to £25,000</option>
                    <option>£25,001 to 35,000</option>
                    <option>£35,001 to £45,000</option>
                    <option>£45,001 to £55,000</option>
                    <option>£55,000 above</option>
                </select><br><br>
                <label>3. Have you gained a promotion to achieve your financial goals?</label><br>
                <select name="responses[Profession][3. Have you gained a promotion to achieve your financial goals?]" required>
                    <option value="">Select...</option>
                    <option>Yes</option>
                    <option>No</option>
                </select><br><br>
                <label>4. What has your career trajectory been?</label><br>
                <select name="responses[Profession][4. What has your career trajectory been?]" required>
                    <option value="">Select...</option>
                    <option>Steady</option>
                    <option>Not changed</option>
                    <option>Drastically improved</option>
                </select><br><br>
                <label>5. How did you motivate yourself to stay the course to achieve your financial goals?</label><br>
                <select name="responses[Profession][5. How did you motivate yourself to stay the course to achieve your financial goals?]" required>
                    <option value="">Select...</option>
                    <option>Visualisation</option>
                    <option>Financial accountability partner</option>
                    <option>Savings apps</option>
                    <option>Budgeting apps</option>
                    <option>Financial coaching/advice</option>
                </select><br><br>
                <label>6. How did you maintain a good work-life balance?</label><br>
                <input type="text" name="responses[Profession][6. How did you maintain a good work-life balance?]" required placeholder="Your answer"><br><br>
            </fieldset>
            <fieldset id="category-6" class="category-section" style="display:none;">
                <legend>Relationships</legend>
                <label>1. Do you have family?</label><br>
                <select name="responses[Relationships][1. Do you have family?]" required>
                    <option value="">Select...</option>
                    <option>Yes</option>
                    <option>No</option>
                    <option>Maybe</option>
                </select><br><br>
                <label>2. Who are your friends?</label><br>
                <select name="responses[Relationships][2. Who are your friends?]" class="friends-select" required>
                    <option value="">Select...</option>
                    <option>Work friends</option>
                    <option>Same core friendship group</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Relationships][2. Who are your friends? - Other]" class="friends-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>3. What do you do with your friends?</label><br>
                <select name="responses[Relationships][3. What do you do with your friends?]" class="friends-do-select" required>
                    <option value="">Select...</option>
                    <option>Sports</option>
                    <option>Socialising</option>
                    <option>Practice your faith</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Relationships][3. What do you do with your friends? - Other]" class="friends-do-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>4. Do your relationships support you in achieving your financial goals?</label><br>
                <select name="responses[Relationships][4. Do your relationships support you in achieving your financial goals?]" required>
                    <option value="">Select...</option>
                    <option>Yes</option>
                    <option>No</option>
                    <option>Not sure</option>
                </select><br><br>
                <label>5. What financial boundaries will you need to set and maintain to achieve your financial goals?</label><br>
                <select name="responses[Relationships][5. What financial boundaries will you need to set and maintain to achieve your financial goals?]" class="boundaries-select" required>
                    <option value="">Select...</option>
                    <option>Limiting time people who encourage you to spend too much money, or damage your financial well-being</option>
                    <option>Letting go of relationships which stop you becoming your future self</option>
                    <option>Practice having tough conversations</option>
                    <option value="Other">Other (please specify)</option>
                </select>
                <input type="text" name="responses[Relationships][5. What financial boundaries will you need to set and maintain to achieve your financial goals? - Other]" class="boundaries-other" style="display:none;" placeholder="Please specify" /><br><br>
                <label>6. What type of people do you associate with?</label><br>
                <input type="text" name="responses[Relationships][6. What type of people do you associate with?]" required placeholder="Your answer"><br><br>
            </fieldset>
            <fieldset id="review-section" class="category-section" style="display:none;">
                <legend>Review Your Answers</legend>
                <div id="review-content"></div>
                <div class="nav-buttons-row">
                    <button type="button" id="edit-answers" class="nav-btn">Edit Answers</button>
                    <button type="submit" class="submit-btn" disabled>Confirm & Submit</button>
                </div>
            </fieldset>
            <div class="nav-buttons-row">
                <button type="button" id="prev-category" class="nav-btn nav-btn-left" style="display:none;">Back</button>
                <button type="button" id="next-category" class="nav-btn nav-btn-right">Next</button>
                <button type="button" id="review-btn" class="nav-btn nav-btn-right" style="display:none;">Review Answers</button>
            </div>
        </form>
        <div id="success-message" class="banner success-banner" style="display:none; margin-top:20px;">Thank you! Your responses have been saved successfully.</div>
        <?php endif; ?>
    </main>
<link rel="stylesheet" href="future_self_style.css">
<script src="../js/future_self_script.js"></script>
</body>
</html>