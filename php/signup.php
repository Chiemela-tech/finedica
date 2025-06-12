<?php
// Debugging: show all errors and log steps
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
file_put_contents(__DIR__ . '/debug_log.txt', "Signup called\n", FILE_APPEND);

require_once 'config.php';

header('Content-Type: application/json'); // Set the response type to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    file_put_contents(__DIR__ . '/debug_log.txt', "POST request received\n", FILE_APPEND);
    $conn = getDatabaseConnection();
    file_put_contents(__DIR__ . '/debug_log.txt', "DB connection OK\n", FILE_APPEND);

    // Get form data
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $dateOfBirth = $_POST['dateOfBirth'] ?? '';
    $employment = trim($_POST['employment'] ?? '');
    $email = trim($_POST['signup-email'] ?? '');
    $password = $_POST['signup-password'] ?? '';
    $confirmPassword = $_POST['signup-confirm-password'] ?? '';
    $gender = trim($_POST['gender'] ?? '');

    // Log received data (not passwords)
    file_put_contents(__DIR__ . '/debug_log.txt', "Received: $firstName, $lastName, $dateOfBirth, $employment, $email, $gender\n", FILE_APPEND);

    // Validate inputs
    if (empty($firstName) || empty($lastName) || empty($dateOfBirth) || empty($employment) || empty($email) || empty($password) || empty($confirmPassword) || empty($gender)) {
        file_put_contents(__DIR__ . '/debug_log.txt', "Validation failed: missing fields\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'All fields are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        file_put_contents(__DIR__ . '/debug_log.txt', "Validation failed: invalid email\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
        exit;
    }

    if ($password !== $confirmPassword) {
        file_put_contents(__DIR__ . '/debug_log.txt', "Validation failed: passwords do not match\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Passwords do not match.']);
        exit;
    }

    if (strlen($password) < 8) {
        file_put_contents(__DIR__ . '/debug_log.txt', "Validation failed: password too short\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Password must be at least 8 characters long.']);
        exit;
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Check if the email already exists
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        file_put_contents(__DIR__ . '/debug_log.txt', "Prepare failed: " . $conn->error . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        file_put_contents(__DIR__ . '/debug_log.txt', "Validation failed: email exists\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Email already exists in the database.']);
        exit;
    }

    // Insert the new user
    $query = "INSERT INTO users (first_name, last_name, date_of_birth, employment, email, gender, password)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        file_put_contents(__DIR__ . '/debug_log.txt', "Prepare failed (insert): " . $conn->error . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param('sssssss', $firstName, $lastName, $dateOfBirth, $employment, $email, $gender, $hashedPassword);

    if ($stmt->execute()) {
        file_put_contents(__DIR__ . '/debug_log.txt', "User registered successfully\n", FILE_APPEND);
        echo json_encode(['success' => true, 'message' => 'User registered successfully!']);
        exit;
    } else {
        file_put_contents(__DIR__ . '/debug_log.txt', "Insert failed: " . $stmt->error . "\n", FILE_APPEND);
        echo json_encode(['success' => false, 'error' => 'Error during INSERT: ' . $stmt->error]);
        exit;
    }

    $stmt->close();
    $conn->close();
} else {
    file_put_contents(__DIR__ . '/debug_log.txt', "Invalid request method\n", FILE_APPEND);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}
?>