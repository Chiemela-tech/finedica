<?php
require_once 'config.php';

header('Content-Type: application/json'); // Set the response type to JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getDatabaseConnection();

    // Get form data
    $email = trim($_POST['login-email'] ?? '');
    $password = $_POST['login-password'] ?? '';

    // Validate inputs
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Email and password are required.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
        exit;
    }

    // Check if the email exists
    $query = "SELECT id, password, first_name FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'No account found with this email.']);
        exit;
    }

    $user = $result->fetch_assoc();

    // Verify the password
    if (!password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'error' => 'Incorrect password.']);
        exit;
    }

    // Start a session and store user information
    session_start();
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'];
    // Store the email in the session
    $_SESSION['user_email'] = $email;

    echo json_encode(['success' => true, 'message' => 'Login successful!']);
    exit;
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}
?>