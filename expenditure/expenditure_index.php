<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$userName = $_SESSION['user_name'];
require_once '../php/db_connect.php'; // Use the central db_connect.php for DB connection

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = $_POST['user_id'];
    $salary = $_POST['salary'];
    $dividends = $_POST['dividends'];
    $statePension = $_POST['statePension'];
    $pension = $_POST['pension'];
    $benefits = $_POST['benefits'];
    $otherIncome = $_POST['otherIncome'];
    $gas = $_POST['gas'];
    $electric = $_POST['electric'];
    $water = $_POST['water'];
    $councilTax = $_POST['councilTax'];
    $phone = $_POST['phone'];
    $internet = $_POST['internet'];
    $mobilePhone = $_POST['mobilePhone'];
    $food = $_POST['food'];
    $otherHome = $_POST['otherHome'];
    $petrol = $_POST['petrol'];
    $carTax = $_POST['carTax'];
    $carInsurance = $_POST['carInsurance'];
    $maintenance = $_POST['maintenance'];
    $publicTransport = $_POST['publicTransport'];
    $otherTravel = $_POST['otherTravel'];
    $social = $_POST['social'];
    $holidays = $_POST['holidays'];
    $gym = $_POST['gym'];
    $clothing = $_POST['clothing'];
    $otherMisc = $_POST['otherMisc'];
    $nursery = $_POST['nursery'];
    $childcare = $_POST['childcare'];
    $schoolFees = $_POST['schoolFees'];
    $uniCosts = $_POST['uniCosts'];
    $childMaintenance = $_POST['childMaintenance'];
    $otherChildren = $_POST['otherChildren'];
    $life = $_POST['life'];
    $criticalIllness = $_POST['criticalIllness'];
    $incomeProtection = $_POST['incomeProtection'];
    $buildings = $_POST['buildings'];
    $contents = $_POST['contents'];
    $otherInsurance = $_POST['otherInsurance'];
    $pensionDed = $_POST['pensionDed'];
    $studentLoan = $_POST['studentLoan'];
    $childcareDed = $_POST['childcareDed'];
    $travelDed = $_POST['travelDed'];
    $sharesave = $_POST['sharesave'];
    $otherDeductions = $_POST['otherDeductions'];

    // Insert or update expenditure data in the database
    $stmt = $conn->prepare("REPLACE INTO expenditure (user_id, salary, dividends, state_pension, pension, benefits, other_income, gas, electric, water, council_tax, phone, internet, mobile_phone, food, other_home, petrol, car_tax, car_insurance, maintenance, public_transport, other_travel, social, holidays, gym, clothing, other_misc, nursery, childcare, school_fees, uni_costs, child_maintenance, other_children, life, critical_illness, income_protection, buildings, contents, other_insurance, pension_ded, student_loan, childcare_ded, travel_ded, sharesave, other_deductions) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iddddddddddddddddddddddddddddddddddddddddddddddddd", $userId, $salary, $dividends, $statePension, $pension, $benefits, $otherIncome, $gas, $electric, $water, $councilTax, $phone, $internet, $mobilePhone, $food, $otherHome, $petrol, $carTax, $carInsurance, $maintenance, $publicTransport, $otherTravel, $social, $holidays, $gym, $clothing, $otherMisc, $nursery, $childcare, $schoolFees, $uniCosts, $childMaintenance, $otherChildren, $life, $criticalIllness, $incomeProtection, $buildings, $contents, $otherInsurance, $pensionDed, $studentLoan, $childcareDed, $travelDed, $sharesave, $otherDeductions);

    if ($stmt->execute()) {
        echo "Expenditure data saved successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
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
                <li><a href="#contact">Contact</a></li>
                <li><a href="../php/avatar_frontpage.php">Avatar</a></li>
                <li><a href="../php/chatbot.php">Chatbot</a></li>
                <li><a href="../php/logout.php" style="font-size: 14px; color:rgb(7, 249, 168)">Logout <?php echo htmlspecialchars($userName);?></a></li>
            </ul>
        </nav>  
    </header>
    <main class="expenditure-main">
        <section class="expenditure-hero">
            <h1>Monthly Income & Expenditure Tracker</h1>
            <p class="expenditure-subtitle">Easily track, visualize, and optimize your monthly budget. Enter your details below and get instant insights!</p>
        </section>
        <div class="expenditure-flex-container">
            <form id="budgetForm" class="expenditure-form card" action="../php/save_expenditure.php" method="post">
                <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                <h2>Income</h2>
                <div class="section">
                    <label>Salary: <input type="number" name="salary" placeholder="Net Monthly Salary" /></label>
                    <label>Dividends: <input type="number" name="dividends" /></label>
                    <label>State Pension: <input type="number" name="statePension" /></label>
                    <label>Pension: <input type="number" name="pension" /></label>
                    <label>Benefits: <input type="number" name="benefits" /></label>
                    <label>Other: <input type="number" name="otherIncome" /></label>
                </div>
                <h2>Home Expenses</h2>
                <div class="section">
                    <label>Gas: <input type="number" name="gas" /></label>
                    <label>Electric: <input type="number" name="electric" /></label>
                    <label>Water: <input type="number" name="water" /></label>
                    <label>Council Tax: <input type="number" name="councilTax" /></label>
                    <label>Phone: <input type="number" name="phone" /></label>
                    <label>Internet: <input type="number" name="internet" /></label>
                    <label>Mobile: <input type="number" name="mobilePhone" /></label>
                    <label>Food: <input type="number" name="food" /></label>
                    <label>Others: <input type="number" name="otherHome" /></label>
                </div>
                <h2>Travel Expenses</h2>
                <div class="section">
                    <label>Petrol: <input type="number" name="petrol" /></label>
                    <label>Car Tax: <input type="number" name="carTax" /></label>
                    <label>Insurance: <input type="number" name="carInsurance" /></label>
                    <label>Maintenance: <input type="number" name="maintenance" /></label>
                    <label>Public Transport: <input type="number" name="publicTransport" /></label>
                    <label>Others: <input type="number" name="otherTravel" /></label>
                </div>
                <!-- Miscellaneous -->
                <h2>Miscellaneous</h2>
                <div class="section">
                    <label>Social: <input type="number" name="social" /></label>
                    <label>Holidays: <input type="number" name="holidays" /></label>
                    <label>Gym: <input type="number" name="gym" /></label>
                    <label>Clothing: <input type="number" name="clothing" /></label>
                    <label>Other: <input type="number" name="otherMisc" /></label>
                </div>
                <!-- Children -->
                <h2>Children</h2>
                <div class="section">
                    <label>Nursery: <input type="number" name="nursery" /></label>
                    <label>Childcare: <input type="number" name="childcare" /></label>
                    <label>School Fees: <input type="number" name="schoolFees" /></label>
                    <label>University Costs: <input type="number" name="uniCosts" /></label>
                    <label>Child Maintenance: <input type="number" name="childMaintenance" /></label>
                    <label>Other: <input type="number" name="otherChildren" /></label>
                </div>
                <!-- Insurance -->
                <h2>Insurance</h2>
                <div class="section">
                    <label>Life: <input type="number" name="life" /></label>
                    <label>Critical Illness: <input type="number" name="criticalIllness" /></label>
                    <label>Income Protection: <input type="number" name="incomeProtection" /></label>
                    <label>Buildings: <input type="number" name="buildings" /></label>
                    <label>Contents: <input type="number" name="contents" /></label>
                    <label>Other: <input type="number" name="otherInsurance" /></label>
                </div>
                <!-- Pay Slip Deductions -->
                <h2>Pay Slip Deductions</h2>
                <div class="section">
                    <label>Pension: <input type="number" name="pensionDed" /></label>
                    <label>Student Loan: <input type="number" name="studentLoan" /></label>
                    <label>Childcare: <input type="number" name="childcareDed" /></label>
                    <label>Travel: <input type="number" name="travelDed" /></label>
                    <label>Sharesave: <input type="number" name="sharesave" /></label>
                    <label>Other: <input type="number" name="otherDeductions" /></label>
                </div>
                <button type="submit" class="expenditure-submit-btn">Save</button>
            </form>
            <div class="expenditure-results-panel card">
                <div id="results"></div>
                <canvas id="expenseChart" width="340" height="340" style="max-width:340px; margin: 0 auto; display: block;"></canvas>
            </div>
        </div>
    </main>
    <script src="expenditure_script.js"></script>
</body>
</html>
