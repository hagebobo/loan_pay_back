<?php
// Enable error display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing save_batch_predictions.php</h1>";

session_start();

// Simulate being logged in
$_SESSION['user'] = 'testuser';

echo "<p>Session user: " . $_SESSION['user'] . "</p>";

require_once 'connection.php';

if ($con) {
    echo "<p style='color:green;'>✅ Database connected</p>";
} else {
    echo "<p style='color:red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
    exit();
}

// Simulate POST data
$test_data = [
    'batch_id' => 'test_batch_' . time(),
    'predictions' => [
        [
            'name' => 'Test User 1',
            'annual_income' => 50000,
            'debt_to_income_ratio' => 0.3,
            'credit_score' => 720,
            'loan_amount' => 10000,
            'interest_rate' => 5.5,
            'gender' => 'Male',
            'marital_status' => 'Single',
            'education_level' => "Bachelor's",
            'employment_status' => 'Employed',
            'loan_purpose' => 'Home',
            'grade_subgrade' => 'A1',
            'Prediction' => 'Will Pay Back',
            'Probability_Paid_Back' => 0.85,
            'Probability_Not_Paid_Back' => 0.15
        ]
    ]
];

echo "<h2>Test Data:</h2>";
echo "<pre>" . print_r($test_data, true) . "</pre>";

$user_id = $_SESSION['user'];
$batch_id = $test_data['batch_id'];
$predictions = $test_data['predictions'];

echo "<h2>Processing...</h2>";

foreach ($predictions as $pred) {
    $name = $pred['name'];
    $annual_income = floatval($pred['annual_income']);
    $dti = floatval($pred['debt_to_income_ratio']);
    $credit_score = intval($pred['credit_score']);
    $loan_amount = floatval($pred['loan_amount']);
    $interest_rate = floatval($pred['interest_rate']);
    $gender = $pred['gender'];
    $marital_status = $pred['marital_status'];
    $education_level = $pred['education_level'];
    $employment_status = $pred['employment_status'];
    $loan_purpose = $pred['loan_purpose'];
    $grade_subgrade = $pred['grade_subgrade'];
    $prediction = $pred['Prediction'];
    $prob_paid = floatval($pred['Probability_Paid_Back']);
    $prob_not_paid = floatval($pred['Probability_Not_Paid_Back']);
    $confidence = max($prob_paid, $prob_not_paid);
    
    echo "<p>Inserting: $name</p>";
    
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
    
    echo "<h3>SQL Query:</h3>";
    echo "<pre>$sql</pre>";
    
    if (mysqli_query($con, $sql)) {
        $id = mysqli_insert_id($con);
        echo "<p style='color:green;'>✅ Success! Insert ID: $id</p>";
    } else {
        echo "<p style='color:red;'>❌ Error: " . mysqli_error($con) . "</p>";
    }
}

mysqli_close($con);

echo "<h2>Done!</h2>";
echo "<p><a href='debug.php'>View Debug Info</a></p>";
?>