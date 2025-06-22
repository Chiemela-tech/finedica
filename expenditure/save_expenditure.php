<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_email'])) { echo json_encode(['success'=>false,'error'=>'Not logged in']); exit; }
require_once '../php/db_connect.php';
$userEmail = $_SESSION['user_email'];
// Fetch user_id for this email
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $userEmail]);
$user_id = $stmt->fetchColumn();
if (!$user_id) {
    echo json_encode(['success'=>false,'error'=>'User not found in users table']);
    exit;
}
$fields = [
    'salary','dividends','statePension','pension','benefits','otherIncome',
    'gas','electric','water','councilTax','phone','internet','mobilePhone','food','otherHome',
    'petrol','carTax','carInsurance','maintenance','publicTransport','otherTravel',
    'social','holidays','gym','clothing','otherMisc',
    'nursery','childcare','schoolFees','uniCosts','childMaintenance','otherChildren',
    'life','criticalIllness','incomeProtection','buildings','contents','otherInsurance',
    'pensionDed','studentLoan','childcareDed','travelDed','sharesave','otherDeductions'
];
$data = [];
foreach ($fields as $f) { $data[$f] = $_POST[$f] ?? 0; }
try {
    // Check if record exists for this email
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM expenditure WHERE email = :email");
    $stmt->execute([':email' => $userEmail]);
    $exists = $stmt->fetchColumn() > 0;
    if ($exists) {
        // Update
        $set = [];
        foreach ($fields as $f) {
            $col = preg_replace('/([A-Z])/', '_$1', $f); // camelCase to snake_case
            $col = strtolower($col);
            $set[] = "$col = :$f";
        }
        $sql = "UPDATE expenditure SET user_id = :user_id, ".implode(",", $set)." WHERE email = :email";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":user_id", $user_id);
        foreach ($fields as $f) $stmt->bindValue(":$f", $data[$f]);
        $stmt->bindValue(":email", $userEmail);
        $stmt->execute();
    } else {
        // Insert
        $columns = implode(",", array_map(function($f){ return preg_replace('/([A-Z])/', '_$1', $f); }, $fields));
        $columns = strtolower($columns);
        $placeholders = ":".implode(",:", $fields);
        $sql = "INSERT INTO expenditure (user_id, email, $columns) VALUES (:user_id, :email, $placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":user_id", $user_id);
        $stmt->bindValue(":email", $userEmail);
        foreach ($fields as $f) $stmt->bindValue(":$f", $data[$f]);
        $stmt->execute();
    }
    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
