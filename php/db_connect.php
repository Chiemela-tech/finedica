<?php
$host = 'localhost';
$dbname = 'user_reg_db';
$username = 'root';
$password = 'finedica';

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
