<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userName = $_SESSION['user_name'];
$user_id = $_SESSION['user_id'];

require_once '../php/db_connect.php';

$stmt = $pdo->prepare("SELECT * FROM expenditure WHERE user_id = :user_id ORDER BY created_at DESC, id DESC LIMIT 1");
$stmt->execute([':user_id' => $user_id]);
$expenditure = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>20:20 FC - FINEDICA</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/expenditurestyle.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                <li><a href="../php/logout.php" style="font-size: 14px; color:rgb(7, 249, 168)">Logout <?php echo htmlspecialchars($userName);?></a></li>
            </ul>
        </nav>  
    </header>
    <main>
        <h1>Monthly Income & Expenditure Tracker</h1>
        <form id="budgetForm">
            <h2>Income</h2>
            <div class="section">
                <label>Salary: <input type="number" name="salary" placeholder="Net Monthly Salary" value="<?php echo isset($expenditure['salary']) ? htmlspecialchars($expenditure['salary']) : '';?>" /></label>
                <label>Dividends: <input type="number" name="dividends" value="<?php echo isset($expenditure['dividends']) ? htmlspecialchars($expenditure['dividends']) : '';?>" /></label>
                <label>State Pension: <input type="number" name="statePension" value="<?php echo isset($expenditure['statePension']) ? htmlspecialchars($expenditure['statePension']) : '';?>" /></label>
                <label>Pension: <input type="number" name="pension" value="<?php echo isset($expenditure['pension']) ? htmlspecialchars($expenditure['pension']) : '';?>" /></label>
                <label>Benefits: <input type="number" name="benefits" value="<?php echo isset($expenditure['benefits']) ? htmlspecialchars($expenditure['benefits']) : '';?>" /></label>
                <label>Other: <input type="number" name="otherIncome" value="<?php echo isset($expenditure['otherIncome']) ? htmlspecialchars($expenditure['otherIncome']) : '';?>" /></label>
            </div>
            <h2>Home Expenses</h2>
            <div class="section">
                <label>Gas: <input type="number" name="gas" value="<?php echo isset($expenditure['gas']) ? htmlspecialchars($expenditure['gas']) : '';?>" /></label>
                <label>Electric: <input type="number" name="electric" value="<?php echo isset($expenditure['electric']) ? htmlspecialchars($expenditure['electric']) : '';?>" /></label>
                <label>Water: <input type="number" name="water" value="<?php echo isset($expenditure['water']) ? htmlspecialchars($expenditure['water']) : '';?>" /></label>
                <label>Council Tax: <input type="number" name="councilTax" value="<?php echo isset($expenditure['councilTax']) ? htmlspecialchars($expenditure['councilTax']) : '';?>" /></label>
                <label>Phone: <input type="number" name="phone" value="<?php echo isset($expenditure['phone']) ? htmlspecialchars($expenditure['phone']) : '';?>" /></label>
                <label>Internet: <input type="number" name="internet" value="<?php echo isset($expenditure['internet']) ? htmlspecialchars($expenditure['internet']) : '';?>" /></label>
                <label>Mobile: <input type="number" name="mobilePhone" value="<?php echo isset($expenditure['mobilePhone']) ? htmlspecialchars($expenditure['mobilePhone']) : '';?>" /></label>
                <label>Food: <input type="number" name="food" value="<?php echo isset($expenditure['food']) ? htmlspecialchars($expenditure['food']) : '';?>" /></label>
                <label>Others: <input type="number" name="otherHome" value="<?php echo isset($expenditure['otherHome']) ? htmlspecialchars($expenditure['otherHome']) : '';?>" /></label>
            </div>
            <h2>Travel Expenses</h2>
            <div class="section">
                <label>Petrol: <input type="number" name="petrol" value="<?php echo isset($expenditure['petrol']) ? htmlspecialchars($expenditure['petrol']) : '';?>" /></label>
                <label>Car Tax: <input type="number" name="carTax" value="<?php echo isset($expenditure['carTax']) ? htmlspecialchars($expenditure['carTax']) : '';?>" /></label>
                <label>Insurance: <input type="number" name="carInsurance" value="<?php echo isset($expenditure['carInsurance']) ? htmlspecialchars($expenditure['carInsurance']) : '';?>" /></label>
                <label>Maintenance: <input type="number" name="maintenance" value="<?php echo isset($expenditure['maintenance']) ? htmlspecialchars($expenditure['maintenance']) : '';?>" /></label>
                <label>Public Transport: <input type="number" name="publicTransport" value="<?php echo isset($expenditure['publicTransport']) ? htmlspecialchars($expenditure['publicTransport']) : '';?>" /></label>
                <label>Others: <input type="number" name="otherTravel" value="<?php echo isset($expenditure['otherTravel']) ? htmlspecialchars($expenditure['otherTravel']) : '';?>" /></label>
            </div>
            <!-- Add similar sections for Miscellaneous, Children, Insurance, Pay Slip Deductions -->
            <button type="submit">Calculate</button>
        </form>
        <div id="results"></div>
        <canvas id="expenseChart" width="400" height="400"></canvas>
    </main>
    <script src="expenditure_script.js"></script>
</body>
</html>
