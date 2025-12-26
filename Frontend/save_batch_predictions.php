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

if (!$data || !isset($data['predictions']) || !isset($data['batch_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit();
}

$batch_id = $data['batch_id'];
$predictions = $data['predictions'];
$saved_count = 0;

try {
    foreach ($predictions as $pred) {
        // Get values with defaults
        $name = isset($pred['name']) ? $pred['name'] : 'Unknown';
        $annual_income = isset($pred['annual_income']) ? floatval($pred['annual_income']) : 0.0;
        $dti = isset($pred['debt_to_income_ratio']) ? floatval($pred['debt_to_income_ratio']) : 0.0;
        $credit_score = isset($pred['credit_score']) ? intval($pred['credit_score']) : 0;
        $loan_amount = isset($pred['loan_amount']) ? floatval($pred['loan_amount']) : 0.0;
        $interest_rate = isset($pred['interest_rate']) ? floatval($pred['interest_rate']) : 0.0;
        $gender = isset($pred['gender']) ? $pred['gender'] : 'Unknown';
        $marital_status = isset($pred['marital_status']) ? $pred['marital_status'] : 'Unknown';
        $education_level = isset($pred['education_level']) ? $pred['education_level'] : 'Unknown';
        $employment_status = isset($pred['employment_status']) ? $pred['employment_status'] : 'Unknown';
        $loan_purpose = isset($pred['loan_purpose']) ? $pred['loan_purpose'] : 'Unknown';
        $grade_subgrade = isset($pred['grade_subgrade']) ? $pred['grade_subgrade'] : 'Unknown';
        $prediction = $pred['Prediction'];
        $prob_paid = floatval($pred['Probability_Paid_Back']);
        $prob_not_paid = isset($pred['Probability_Not_Paid_Back']) ? floatval($pred['Probability_Not_Paid_Back']) : (1.0 - $prob_paid);
        $confidence = max($prob_paid, $prob_not_paid);
        
        // Insert query
        $sql = "INSERT INTO predictions (
            user_id, applicant_name, annual_income, debt_to_income_ratio,
            credit_score, loan_amount, interest_rate, gender, marital_status,
            education_level, employment_status, loan_purpose, grade_subgrade,
            prediction, probability_paid_back, probability_not_paid_back,
            confidence, prediction_type, batch_id
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
            'batch',
            '" . mysqli_real_escape_string($con, $batch_id) . "'
        )";
        
        if (mysqli_query($con, $sql)) {
            $saved_count++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Saved {$saved_count} predictions",
        'saved_count' => $saved_count,
        'total_count' => count($predictions),
        'batch_id' => $batch_id
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

mysqli_close($con);
?>