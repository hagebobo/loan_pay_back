<?php
session_start();
require_once 'connection.php';

echo "<html><head><title>Debug Info</title><style>
body { font-family: Arial; padding: 20px; background: #f5f5f5; }
.section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
h2 { color: #FF6B35; }
.success { color: #00C896; font-weight: bold; }
.error { color: #FF5757; font-weight: bold; }
pre { background: #f0f0f0; padding: 10px; border-radius: 4px; overflow-x: auto; }
</style></head><body>";

echo "<h1>🔍 Loan Payback System - Debug Info</h1>";

// 1. Session Check
echo "<div class='section'>";
echo "<h2>1. Session Information</h2>";
if (isset($_SESSION['user'])) {
    echo "<p class='success'>✅ Session Active</p>";
    echo "<p>User: <strong>" . htmlspecialchars($_SESSION['user']) . "</strong></p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
} else {
    echo "<p class='error'>❌ No Session Found</p>";
    echo "<p>You need to login first!</p>";
}
echo "</div>";

// 2. Database Connection
echo "<div class='section'>";
echo "<h2>2. Database Connection</h2>";
if ($con) {
    echo "<p class='success'>✅ Connected to Database</p>";
    echo "<p>Host: localhost</p>";
    echo "<p>Database: loanpayback</p>";
    
    // Check if table exists
    $result = mysqli_query($con, "SHOW TABLES LIKE 'predictions'");
    if (mysqli_num_rows($result) > 0) {
        echo "<p class='success'>✅ 'predictions' table exists</p>";
        
        // Get table structure
        $structure = mysqli_query($con, "DESCRIBE predictions");
        echo "<h3>Table Structure:</h3>";
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = mysqli_fetch_assoc($structure)) {
            echo "<tr>";
            echo "<td>" . $row['Field'] . "</td>";
            echo "<td>" . $row['Type'] . "</td>";
            echo "<td>" . $row['Null'] . "</td>";
            echo "<td>" . $row['Key'] . "</td>";
            echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Count records
        $count_result = mysqli_query($con, "SELECT COUNT(*) as count FROM predictions");
        $count = mysqli_fetch_assoc($count_result)['count'];
        echo "<p>Total records: <strong>$count</strong></p>";
        
        if ($count > 0) {
            echo "<h3>Recent Predictions:</h3>";
            $recent = mysqli_query($con, "SELECT * FROM predictions ORDER BY created_at DESC LIMIT 5");
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>User</th><th>Name</th><th>Loan Amount</th><th>Prediction</th><th>Date</th></tr>";
            while ($row = mysqli_fetch_assoc($recent)) {
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . $row['user_id'] . "</td>";
                echo "<td>" . $row['applicant_name'] . "</td>";
                echo "<td>" . number_format($row['loan_amount']) . "</td>";
                echo "<td>" . $row['prediction'] . "</td>";
                echo "<td>" . $row['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<p class='error'>❌ 'predictions' table does NOT exist!</p>";
        echo "<p>Run the SQL script: create_predictions_table.sql</p>";
    }
} else {
    echo "<p class='error'>❌ Database Connection Failed</p>";
    echo "<p>Error: " . mysqli_connect_error() . "</p>";
}
echo "</div>";

// 3. PHP Configuration
echo "<div class='section'>";
echo "<h2>3. PHP Configuration</h2>";
echo "<p>PHP Version: <strong>" . phpversion() . "</strong></p>";
echo "<p>Error Reporting: <strong>" . error_reporting() . "</strong></p>";
echo "<p>Display Errors: <strong>" . ini_get('display_errors') . "</strong></p>";
echo "<p>Log Errors: <strong>" . ini_get('log_errors') . "</strong></p>";
echo "<p>Error Log: <strong>" . ini_get('error_log') . "</strong></p>";
echo "</div>";

// 4. File Permissions
echo "<div class='section'>";
echo "<h2>4. File Check</h2>";
$files_to_check = [
    'connection.php',
    'save_prediction.php',
    'save_batch_predictions.php',
    'loanpayback.php'
];

foreach ($files_to_check as $file) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $file exists</p>";
        if (is_readable($file)) {
            echo "<p style='margin-left: 20px;'>Readable: Yes</p>";
        } else {
            echo "<p style='margin-left: 20px;' class='error'>Readable: No</p>";
        }
    } else {
        echo "<p class='error'>❌ $file NOT found</p>";
    }
}
echo "</div>";

// 5. Test Insert
echo "<div class='section'>";
echo "<h2>5. Test Database Insert</h2>";
if (isset($_GET['test_insert']) && $_GET['test_insert'] == 'yes') {
    if (isset($_SESSION['user'])) {
        $test_sql = "INSERT INTO predictions (
            user_id, applicant_name, annual_income, loan_amount, 
            prediction, confidence, prediction_type
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($con, $test_sql);
        $user = $_SESSION['user'];
        $name = "Test User " . date('H:i:s');
        $income = 50000.00;
        $loan = 10000.00;
        $pred = "Test Prediction";
        $conf = 0.85;
        $type = "single";
        
        mysqli_stmt_bind_param($stmt, 'ssddds s', $user, $name, $income, $loan, $pred, $conf, $type);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "<p class='success'>✅ Test insert successful!</p>";
            echo "<p>Insert ID: " . mysqli_insert_id($con) . "</p>";
        } else {
            echo "<p class='error'>❌ Test insert failed!</p>";
            echo "<p>Error: " . mysqli_stmt_error($stmt) . "</p>";
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "<p class='error'>Cannot test - no session!</p>";
    }
} else {
    echo "<p><a href='?test_insert=yes' style='background: #FF6B35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Click to Test Insert</a></p>";
}
echo "</div>";

// 6. Error Log
echo "<div class='section'>";
echo "<h2>6. Recent PHP Errors</h2>";
$error_log = ini_get('error_log');
if ($error_log && file_exists($error_log)) {
    $errors = file($error_log);
    $recent_errors = array_slice($errors, -20);
    echo "<pre>" . implode("", $recent_errors) . "</pre>";
} else {
    echo "<p>Error log not found or not configured</p>";
    echo "<p>Default location: C:\\xampp\\php\\logs\\php_error_log</p>";
}
echo "</div>";

echo "</body></html>";
?>