<?php
session_start();
header('Content-Type: application/json');

// Validate session
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Session expired']);
    exit;
}

// Process answers
$categories = ['Money Avoidance', 'Money Worship', 'Money Status', 'Money Vigilance'];
$user_answers = [];

foreach ($categories as $cat) {
    if (!isset($_POST[$cat]) || empty($_POST[$cat])) {
        error_log("Missing category: $cat");
        echo json_encode(['status' => 'error', 'message' => "Missing answers for $cat"]);
        exit;
    }

    // Accept both array and comma-separated string
    if (is_array($_POST[$cat])) {
        $answers = array_map('intval', $_POST[$cat]);
    } else {
        $answers = array_map('intval', explode(',', $_POST[$cat]));
    }

    if (count($answers) !== 5) {
        error_log("Invalid answer count for $cat: " . count($answers));
        echo json_encode(['status' => 'error', 'message' => "Incomplete answers for $cat"]);
        exit;
    }

    $user_answers[$cat] = $answers;
}

// Rest of database and Python integration remains the same

$input = json_encode([
    'email' => $email,
    'answers' => $user_answers
]);

$output = shell_exec('python3 psychometric_test.py ' . escapeshellarg($input) . ' 2>&1');

if (!$output) {
    echo json_encode(['status' => 'error', 'message' => 'Python script execution failed']);
    exit;
}

$data = json_decode(trim($output), true);

if (!$data || !isset($data['scores'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid script output: ' . $output]);
    exit;
}

try {
    $pdo = new PDO('mysql:host=localhost;port=3307;dbname=user_reg_db;charset=utf8', 'root', 'finedica');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach ($data['scores'] as $cat => $score) {
        $stmt = $pdo->prepare("INSERT INTO psychometric_test_responses 
            (email, category, score, dominant_belief, description)
            VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $email,
            $cat,
            $score,
            $data['top_category'],
            $data['description']
        ]);
    }

    echo json_encode([
        'status' => 'ok',
        'scores' => $data['scores'],
        'top_category' => $data['top_category'],
        'description' => $data['description']
    ]);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
