<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'error'=>'Not logged in']); exit; }
require_once '../php/db_connect.php';
$user_id = $_SESSION['user_id'];
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
    $stmt = $pdo->prepare("INSERT INTO expenditure (
        user_id, salary, dividends, state_pension, pension, benefits, other_income,
        gas, electric, water, council_tax, phone, internet, mobile_phone, food, other_home,
        petrol, car_tax, car_insurance, maintenance, public_transport, other_travel,
        social, holidays, gym, clothing, other_misc,
        nursery, childcare, school_fees, uni_costs, child_maintenance, other_children,
        life, critical_illness, income_protection, buildings, contents, other_insurance,
        pension_ded, student_loan, childcare_ded, travel_ded, sharesave, other_deductions
    ) VALUES (
        :user_id, :salary, :dividends, :state_pension, :pension, :benefits, :other_income,
        :gas, :electric, :water, :council_tax, :phone, :internet, :mobile_phone, :food, :other_home,
        :petrol, :car_tax, :car_insurance, :maintenance, :public_transport, :other_travel,
        :social, :holidays, :gym, :clothing, :other_misc,
        :nursery, :childcare, :school_fees, :uni_costs, :child_maintenance, :other_children,
        :life, :critical_illness, :income_protection, :buildings, :contents, :other_insurance,
        :pension_ded, :student_loan, :childcare_ded, :travel_ded, :sharesave, :other_deductions
    )");
    $stmt->execute([
        ':user_id' => $user_id,
        ':salary' => $data['salary'],
        ':dividends' => $data['dividends'],
        ':state_pension' => $data['statePension'],
        ':pension' => $data['pension'],
        ':benefits' => $data['benefits'],
        ':other_income' => $data['otherIncome'],
        ':gas' => $data['gas'],
        ':electric' => $data['electric'],
        ':water' => $data['water'],
        ':council_tax' => $data['councilTax'],
        ':phone' => $data['phone'],
        ':internet' => $data['internet'],
        ':mobile_phone' => $data['mobilePhone'],
        ':food' => $data['food'],
        ':other_home' => $data['otherHome'],
        ':petrol' => $data['petrol'],
        ':car_tax' => $data['carTax'],
        ':car_insurance' => $data['carInsurance'],
        ':maintenance' => $data['maintenance'],
        ':public_transport' => $data['publicTransport'],
        ':other_travel' => $data['otherTravel'],
        ':social' => $data['social'],
        ':holidays' => $data['holidays'],
        ':gym' => $data['gym'],
        ':clothing' => $data['clothing'],
        ':other_misc' => $data['otherMisc'],
        ':nursery' => $data['nursery'],
        ':childcare' => $data['childcare'],
        ':school_fees' => $data['schoolFees'],
        ':uni_costs' => $data['uniCosts'],
        ':child_maintenance' => $data['childMaintenance'],
        ':other_children' => $data['otherChildren'],
        ':life' => $data['life'],
        ':critical_illness' => $data['criticalIllness'],
        ':income_protection' => $data['incomeProtection'],
        ':buildings' => $data['buildings'],
        ':contents' => $data['contents'],
        ':other_insurance' => $data['otherInsurance'],
        ':pension_ded' => $data['pensionDed'],
        ':student_loan' => $data['studentLoan'],
        ':childcare_ded' => $data['childcareDed'],
        ':travel_ded' => $data['travelDed'],
        ':sharesave' => $data['sharesave'],
        ':other_deductions' => $data['otherDeductions']
    ]);
    echo json_encode(['success'=>true]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'error'=>$e->getMessage()]);
}
