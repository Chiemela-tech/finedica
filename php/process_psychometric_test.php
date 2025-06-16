<?php
// psychometric_test_api.php

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit;
}

$email = $_SESSION['user_email'];

// Collect answers
$categories = ['Money Avoidance','Money Worship','Money Status','Money Vigilance'];
$user_answers = [];
foreach ($categories as $cat) {
    if (!isset($_POST[$cat]) || !is_array($_POST[$cat]) || count($_POST[$cat]) != 5) {
        echo json_encode(['status' => 'error', 'message' => "Incomplete answers for $cat"]);
        exit;
    }
    $user_answers[$cat] = array_map('intval', $_POST[$cat]);
}

// Prepare data for Python
$input = [
    'email' => $email,
    'answers' => $user_answers
];

// Call Python script
$python = "C:\\Python311\\python.exe"; // or 'python' depending on your environment
$script = realpath(__DIR__ . "\\..\\python\\psychometric_test.py");
$input_json = escapeshellarg(json_encode($input));

$cmd = "$python $script $input_json";
$output = shell_exec($cmd);

if (!$output) {
    echo json_encode(['status' => 'error', 'message' => 'Python script failed']);
    exit;
}

$data = json_decode($output, true);

if (!$data || !isset($data['scores'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Python output']);
    exit;
}

// Store in DB
$host = 'localhost';
$dbname = 'user_reg_db';
$username = 'root';
$password = 'finedica';

try {
    $pdo = new PDO("mysql:host=$host;port=3306;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert each category score
    foreach ($data['scores'] as $cat => $score) {
        $stmt = $pdo->prepare("INSERT INTO psychometric_test_responses (email, category, score, dominant_belief, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $email,
            $cat,
            $score,
            $data['top_category'],
            $data['description']
        ]);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $e->getMessage()]);
    exit;
}

echo json_encode([
    'status' => 'ok',
    'scores' => $data['scores'],
    'top_category' => $data['top_category'],
    'description' => $data['description']
]);
