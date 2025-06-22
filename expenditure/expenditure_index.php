<?php
session_start();

if (!isset($_SESSION['user_email'])) {
    header('Location: index.php');
    exit;
}

$userEmail = $_SESSION['user_email'];
$userName = $_SESSION['user_name'];
require_once '../php/db_connect.php';

// Fetch the latest expenditure record for this user by email
$stmt = $pdo->prepare("SELECT * FROM expenditure WHERE email = :email LIMIT 1");
$stmt->execute([':email' => $userEmail]);
$expenditure = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>20:20 FC - FINEDICA</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="expenditure_style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
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
            <?php if ($expenditure): ?>
                <div id="expenditureSummary" class="expenditure-form card">
                    <h2>Your Last Submitted Expenditure</h2>
                    <div id="expenditureSummaryCategories">
                    <?php
                    $categories = [
                        'Income' => ['salary','dividends','state_pension','pension','benefits','other_income'],
                        'Home Expenses' => ['gas','electric','water','council_tax','phone','internet','mobile_phone','food','other_home'],
                        'Travel Expenses' => ['petrol','car_tax','car_insurance','maintenance','public_transport','other_travel'],
                        'Miscellaneous' => ['social','holidays','gym','clothing','other_misc'],
                        'Children' => ['nursery','childcare','school_fees','uni_costs','child_maintenance','other_children'],
                        'Insurance' => ['life','critical_illness','income_protection','buildings','contents','other_insurance'],
                        'Pay Slip Deductions' => ['pension_ded','student_loan','childcare_ded','travel_ded','sharesave','other_deductions']
                    ];
                    foreach ($categories as $catName => $fields): ?>
                        <div class="summary-category-block">
                            <h4 style="color:#2a5d84; margin-top:18px;"> <?php echo $catName; ?> </h4>
                            <ul style="list-style:none;padding-left:0;">
                            <?php foreach ($fields as $field):
                                if (!isset($expenditure[$field])) continue;
                                $label = ucwords(str_replace(['_', 'Ded'], [' ', ' Deduction'], $field));
                                $value = $expenditure[$field];
                            ?>
                                <li style="margin-bottom:8px;"><b><?php echo $label; ?>:</b> <span class='review-answer'><?php echo htmlspecialchars($value); ?></span></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                    </div>
                    <button id="editExpenditureBtn" class="expenditure-submit-btn">Edit</button>
                </div>
            <?php endif; ?>
            <form id="expenditureForm" class="expenditure-form card" method="post" style="<?php echo $expenditure ? 'display:none;' : ''; ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($userEmail); ?>">
                <h2>Income</h2>
                <div class="section">
                    <label>Salary: <input type="number" name="salary" value="<?php echo $expenditure['salary'] ?? ''; ?>" placeholder="Net Monthly Salary" /></label>
                    <label>Dividends: <input type="number" name="dividends" value="<?php echo $expenditure['dividends'] ?? ''; ?>" /></label>
                    <label>State Pension: <input type="number" name="statePension" value="<?php echo $expenditure['state_pension'] ?? ''; ?>" /></label>
                    <label>Pension: <input type="number" name="pension" value="<?php echo $expenditure['pension'] ?? ''; ?>" /></label>
                    <label>Benefits: <input type="number" name="benefits" value="<?php echo $expenditure['benefits'] ?? ''; ?>" /></label>
                    <label>Other: <input type="number" name="otherIncome" value="<?php echo $expenditure['other_income'] ?? ''; ?>" /></label>
                </div>
                <h2>Home Expenses</h2>
                <div class="section">
                    <label>Gas: <input type="number" name="gas" value="<?php echo $expenditure['gas'] ?? ''; ?>" /></label>
                    <label>Electric: <input type="number" name="electric" value="<?php echo $expenditure['electric'] ?? ''; ?>" /></label>
                    <label>Water: <input type="number" name="water" value="<?php echo $expenditure['water'] ?? ''; ?>" /></label>
                    <label>Council Tax: <input type="number" name="councilTax" value="<?php echo $expenditure['council_tax'] ?? ''; ?>" /></label>
                    <label>Phone: <input type="number" name="phone" value="<?php echo $expenditure['phone'] ?? ''; ?>" /></label>
                    <label>Internet: <input type="number" name="internet" value="<?php echo $expenditure['internet'] ?? ''; ?>" /></label>
                    <label>Mobile: <input type="number" name="mobilePhone" value="<?php echo $expenditure['mobile_phone'] ?? ''; ?>" /></label>
                    <label>Food: <input type="number" name="food" value="<?php echo $expenditure['food'] ?? ''; ?>" /></label>
                    <label>Others: <input type="number" name="otherHome" value="<?php echo $expenditure['other_home'] ?? ''; ?>" /></label>
                </div>
                <h2>Travel Expenses</h2>
                <div class="section">
                    <label>Petrol: <input type="number" name="petrol" value="<?php echo $expenditure['petrol'] ?? ''; ?>" /></label>
                    <label>Car Tax: <input type="number" name="carTax" value="<?php echo $expenditure['car_tax'] ?? ''; ?>" /></label>
                    <label>Insurance: <input type="number" name="carInsurance" value="<?php echo $expenditure['car_insurance'] ?? ''; ?>" /></label>
                    <label>Maintenance: <input type="number" name="maintenance" value="<?php echo $expenditure['maintenance'] ?? ''; ?>" /></label>
                    <label>Public Transport: <input type="number" name="publicTransport" value="<?php echo $expenditure['public_transport'] ?? ''; ?>" /></label>
                    <label>Others: <input type="number" name="otherTravel" value="<?php echo $expenditure['other_travel'] ?? ''; ?>" /></label>
                </div>
                <!-- Miscellaneous -->
                <h2>Miscellaneous</h2>
                <div class="section">
                    <label>Social: <input type="number" name="social" value="<?php echo $expenditure['social'] ?? ''; ?>" /></label>
                    <label>Holidays: <input type="number" name="holidays" value="<?php echo $expenditure['holidays'] ?? ''; ?>" /></label>
                    <label>Gym: <input type="number" name="gym" value="<?php echo $expenditure['gym'] ?? ''; ?>" /></label>
                    <label>Clothing: <input type="number" name="clothing" value="<?php echo $expenditure['clothing'] ?? ''; ?>" /></label>
                    <label>Other: <input type="number" name="otherMisc" value="<?php echo $expenditure['other_misc'] ?? ''; ?>" /></label>
                </div>
                <!-- Children -->
                <h2>Children</h2>
                <div class="section">
                    <label>Nursery: <input type="number" name="nursery" value="<?php echo $expenditure['nursery'] ?? ''; ?>" /></label>
                    <label>Childcare: <input type="number" name="childcare" value="<?php echo $expenditure['childcare'] ?? ''; ?>" /></label>
                    <label>School Fees: <input type="number" name="schoolFees" value="<?php echo $expenditure['school_fees'] ?? ''; ?>" /></label>
                    <label>University Costs: <input type="number" name="uniCosts" value="<?php echo $expenditure['uni_costs'] ?? ''; ?>" /></label>
                    <label>Child Maintenance: <input type="number" name="childMaintenance" value="<?php echo $expenditure['child_maintenance'] ?? ''; ?>" /></label>
                    <label>Other: <input type="number" name="otherChildren" value="<?php echo $expenditure['other_children'] ?? ''; ?>" /></label>
                </div>
                <!-- Insurance -->
                <h2>Insurance</h2>
                <div class="section">
                    <label>Life: <input type="number" name="life" value="<?php echo $expenditure['life'] ?? ''; ?>" /></label>
                    <label>Critical Illness: <input type="number" name="criticalIllness" value="<?php echo $expenditure['critical_illness'] ?? ''; ?>" /></label>
                    <label>Income Protection: <input type="number" name="incomeProtection" value="<?php echo $expenditure['income_protection'] ?? ''; ?>" /></label>
                    <label>Buildings: <input type="number" name="buildings" value="<?php echo $expenditure['buildings'] ?? ''; ?>" /></label>
                    <label>Contents: <input type="number" name="contents" value="<?php echo $expenditure['contents'] ?? ''; ?>" /></label>
                    <label>Other: <input type="number" name="otherInsurance" value="<?php echo $expenditure['other_insurance'] ?? ''; ?>" /></label>
                </div>
                <!-- Pay Slip Deductions -->
                <h2>Pay Slip Deductions</h2>
                <div class="section">
                    <label>Pension: <input type="number" name="pensionDed" value="<?php echo $expenditure['pension_ded'] ?? ''; ?>" /></label>
                    <label>Student Loan: <input type="number" name="studentLoan" value="<?php echo $expenditure['student_loan'] ?? ''; ?>" /></label>
                    <label>Childcare: <input type="number" name="childcareDed" value="<?php echo $expenditure['childcare_ded'] ?? ''; ?>" /></label>
                    <label>Travel: <input type="number" name="travelDed" value="<?php echo $expenditure['travel_ded'] ?? ''; ?>" /></label>
                    <label>Sharesave: <input type="number" name="sharesave" value="<?php echo $expenditure['sharesave'] ?? ''; ?>" /></label>
                    <label>Other: <input type="number" name="otherDeductions" value="<?php echo $expenditure['other_deductions'] ?? ''; ?>" /></label>
                </div>
                <button type="button" id="reviewBtn" class="expenditure-submit-btn">Review</button>
            </form>
            <div id="reviewPanel" style="display:none;" class="card">
                <h3>Review Your Answers</h3>
                <div id="reviewList"></div>
                <button id="editBtn" type="button">Edit</button>
                <button id="submitBtn" type="button" class="expenditure-submit-btn">Submit</button>
            </div>
            <div class="expenditure-results-panel card">
                <div id="resultMsg"></div>
                <div id="results"></div>
                <canvas id="expenseChart" width="340" height="340" style="max-width:340px; margin: 0 auto; display: block;"></canvas>
                <div style="margin-top: 24px; display: flex; gap: 20px; justify-content: center;">
                    <a href="../php/questionnaire.php"><button class="expenditure-submit-btn">Go to Main Menu</button></a>
                    <a href="../chatbot/chatbot.php"><button class="expenditure-submit-btn">Start Advice</button></a>
                </div>
            </div>
        </div>
    </main>
    <script src="expenditure_script.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var editBtn = document.getElementById('editExpenditureBtn');
        if (editBtn) {
            editBtn.onclick = function() {
                document.getElementById('expenditureSummary').style.display = 'none';
                document.getElementById('expenditureForm').style.display = '';
            };
        }
    });
    </script>
</body>
</html>
