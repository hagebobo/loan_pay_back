<?php
session_start();
require_once 'connection.php';

// Set headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check authentication
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user'];

// Get POST data
$raw_data = file_get_contents('php://input');
$data = json_decode($raw_data, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

try {
    // Get values with defaults
    $name = isset($data['name']) ? $data['name'] : 'Unknown';
    $annual_income = isset($data['annual_income']) ? floatval($data['annual_income']) : 0.0;
    $dti = isset($data['debt_to_income_ratio']) ? floatval($data['debt_to_income_ratio']) : 0.0;
    $credit_score = isset($data['credit_score']) ? intval($data['credit_score']) : 0;
    $loan_amount = isset($data['loan_amount']) ? floatval($data['loan_amount']) : 0.0;
    $interest_rate = isset($data['interest_rate']) ? floatval($data['interest_rate']) : 0.0;
    $gender = isset($data['gender']) ? $data['gender'] : 'Unknown';
    $marital_status = isset($data['marital_status']) ? $data['marital_status'] : 'Unknown';
    $education_level = isset($data['education_level']) ? $data['education_level'] : 'Unknown';
    $employment_status = isset($data['employment_status']) ? $data['employment_status'] : 'Unknown';
    $loan_purpose = isset($data['loan_purpose']) ? $data['loan_purpose'] : 'Unknown';
    $grade_subgrade = isset($data['grade_subgrade']) ? $data['grade_subgrade'] : 'Unknown';
    $prediction = $data['prediction'];
    $prob_paid = floatval($data['probability_paid_back']);
    $prob_not_paid = floatval($data['probability_not_paid_back']);
    $confidence = floatval($data['confidence']);
    
    // Insert query
    $sql = "INSERT INTO predictions (
        user_id, applicant_name, annual_income, debt_to_income_ratio,
        credit_score, loan_amount, interest_rate, gender, marital_status,
        education_level, employment_status, loan_purpose, grade_subgrade,
        prediction, probability_paid_back, probability_not_paid_back,
        confidence, prediction_type
    ) VALUES (
        '$user_id',
        '" . mysqli_real_escape_string($con, $name) . "',
        $annual_income,
        $dti,
        $credit_score,
        $loan_amount,
        $interest_rate,
        '" . mysqli_real_escape_string($con, $gender) . "',
        '" . mysqli_real_escape_string($con, $marital_status) . "',
        '" . mysqli_real_escape_string($con, $education_level) . "',
        '" . mysqli_real_escape_string($con, $employment_status) . "',
        '" . mysqli_real_escape_string($con, $loan_purpose) . "',
        '" . mysqli_real_escape_string($con, $grade_subgrade) . "',
        '" . mysqli_real_escape_string($con, $prediction) . "',
        $prob_paid,
        $prob_not_paid,
        $confidence,
        'single'
    )";
    
    if (mysqli_query($con, $sql)) {
        echo json_encode([
            'success' => true,
            'message' => 'Prediction saved',
            'id' => mysqli_insert_id($con)
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . mysqli_error($con)
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($con);
?>