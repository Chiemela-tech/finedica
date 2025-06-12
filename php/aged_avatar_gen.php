<?php
session_start();
// Database connection
$host = 'localhost';
$dbname = 'user_reg_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=3307;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed']);
    exit;
}

// Read input
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid or missing email']);
    exit;
}

$email = $data['email'];
// Get face image URL
$stmt = $pdo->prepare("SELECT face_image_url FROM face_image_responses WHERE email = :email ORDER BY id DESC LIMIT 1");
$stmt->bindParam(':email', $email);
$stmt->execute();
$faceImageUrl = $stmt->fetchColumn();
if (!$faceImageUrl) {
    echo json_encode(['status' => 'error', 'message' => 'No face image found']);
    exit;
}

// Prepare paths
$source_image = realpath(__DIR__ . "/../uploads/" . basename($faceImageUrl));
$avatar_output_dir = realpath(__DIR__ . "/../avatars/");
$avatar_output = $avatar_output_dir . "/avatar_" . $email . ".png";

// Validate paths
if (!$source_image || !$avatar_output_dir || !file_exists($source_image)) {
    echo json_encode(['status' => 'error', 'message' => 'Source image file not found or invalid paths']);
    exit;
}

// Build and run Python command
$python = "C:\\Python311\\python.exe";
$script = realpath(__DIR__ . "\\..\\python\\aged_avatar_gen.py");
$cmd = "\"$python\" \"$script\" \"$avatar_output\"";

$descriptors = [
    0 => ['pipe', 'r'],  // stdin
    1 => ['pipe', 'w'],  // stdout
    2 => ['pipe', 'w']   // stderr
];

try {
    $process = proc_open($cmd, $descriptors, $pipes);
    if (!is_resource($process)) {
        error_log("Failed to execute Python script: $cmd");
        echo json_encode(['status' => 'error', 'message' => 'Failed to execute Python script']);
        exit;
    }

    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    
    foreach ($pipes as $pipe) {
        fclose($pipe);
    }
    
    $returnCode = proc_close($process);
    
    if ($returnCode !== 0) {
        error_log("Python script error: $stderr");
        echo json_encode([
            'status' => 'error',
            'message' => 'Python script execution failed',
            'debug' => ['stderr' => $stderr, 'stdout' => $stdout]
        ]);
        exit;
    }
    
    // Check if avatar was created
    if (!file_exists($avatar_output)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Avatar file not found after script execution',
            'debug' => ['stdout' => $stdout, 'stderr' => $stderr]
        ]);
        exit;
    }
    
    // Save avatar path to the database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO avatar (email, avatar_path)
            VALUES (:email, :avatar_path)
            ON DUPLICATE KEY UPDATE avatar_path = :avatar_path
        ");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':avatar_path', $avatar_output);
        $stmt->execute();
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Failed to save avatar to database']);
        exit;
    }
    
    // Return success response
    $avatar_web_path = "/2020FC/src/avatars/" . basename($avatar_output);
    echo json_encode(['status' => 'ok', 'avatar_path' => $avatar_web_path]);
    exit;
} catch (Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Unexpected error occurred']);
    exit;
}
?>