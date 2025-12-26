<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: index.php"); // login page
    exit();
}

$username = $_SESSION['user'];

// Include database connection
require_once 'connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Payback Prediction System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #FF6B35;
            --secondary: #004E89;
            --accent: #F7B801;
            --dark: #f8f9ff;
            --light: #FAFAFA;
            --success: #00C896;
            --danger: #FF5757;
            --gradient-1: linear-gradient(135deg, #FF6B35 0%, #F7B801 100%);
            --gradient-2: linear-gradient(135deg, #004E89 0%, #1A659E 100%);
            --shadow-soft: 0 8px 32px rgba(0, 0, 0, 0.08);
            --shadow-strong: 0 20px 60px rgba(0, 0, 0, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #e8f4f8 0%, #f0e7ff 50%, #ffe8f0 100%);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(ellipse at 20% 30%, rgba(255, 107, 53, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 70%, rgba(247, 184, 1, 0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 50%, rgba(0, 78, 137, 0.1) 0%, transparent 60%),
                radial-gradient(circle at 10% 80%, rgba(255, 107, 53, 0.08) 0%, transparent 40%),
                radial-gradient(circle at 90% 20%, rgba(0, 78, 137, 0.12) 0%, transparent 45%);
            animation: meshMove 20s ease-in-out infinite;
            z-index: 0;
        }

        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(255, 107, 53, 0.02) 2px, rgba(255, 107, 53, 0.02) 4px),
                repeating-linear-gradient(90deg, transparent, transparent 2px, rgba(0, 78, 137, 0.02) 2px, rgba(0, 78, 137, 0.02) 4px);
            z-index: 0;
            pointer-events: none;
        }

        @keyframes meshMove {
            0%, 100% {
                transform: translate(0, 0) scale(1);
            }
            33% {
                transform: translate(50px, -30px) scale(1.05);
            }
            66% {
                transform: translate(-30px, 40px) scale(0.98);
            }
        }

        .background-decoration {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.4;
            animation: float 15s ease-in-out infinite;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255, 107, 53, 0.3) 0%, transparent 70%);
            top: 10%;
            left: 5%;
            animation-delay: 0s;
        }

        .shape-2 {
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(247, 184, 1, 0.25) 0%, transparent 70%);
            bottom: 15%;
            right: 10%;
            animation-delay: 5s;
        }

        .shape-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(0, 78, 137, 0.25) 0%, transparent 70%);
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            animation-delay: 10s;
        }

        @keyframes float {
            0%, 100% {
                transform: translate(0, 0);
            }
            33% {
                transform: translate(30px, -50px);
            }
            66% {
                transform: translate(-40px, 30px);
            }
        }

        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: 
                linear-gradient(rgba(0, 78, 137, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 107, 53, 0.04) 1px, transparent 1px);
            background-size: 80px 80px;
            z-index: 0;
            pointer-events: none;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        header {
            text-align: center;
            margin-bottom: 60px;
            animation: fadeInDown 0.8s ease;
        }

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1 {
            font-family: 'Syne', sans-serif;
            font-size: clamp(2.5rem, 5vw, 4.5rem);
            font-weight: 800;
            background: var(--gradient-1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 16px;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .subtitle {
            font-size: 1.25rem;
            color: rgba(26, 26, 46, 0.7);
            font-weight: 400;
        }

        /* ===== NEW LAYOUT WITH LEFT SIDEBAR ===== */
        .main-layout {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }

        .tabs-wrapper {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 250px;
            border: 1px solid rgba(0, 78, 137, 0.15);
            box-shadow: 0 8px 32px rgba(0, 78, 137, 0.08);
            animation: fadeIn 0.8s ease 0.2s backwards;
            position: sticky;
            top: 100px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .tab {
            padding: 18px 24px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            color: rgba(26, 26, 46, 0.6);
            border-radius: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Syne', sans-serif;
            position: relative;
            overflow: hidden;
            text-align: left;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .tab::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-1);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .tab.active {
            color: white;
        }

        .tab.active::before {
            opacity: 1;
        }

        .tab span {
            position: relative;
            z-index: 1;
        }

        .tab-icon {
            font-size: 1.2rem;
            position: relative;
            z-index: 1;
        }

        .tab:hover:not(.active) {
            background: rgba(255, 107, 53, 0.08);
            color: rgba(26, 26, 46, 0.9);
        }

        .content-wrapper {
            flex: 1;
        }

        .content-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(30px);
            border-radius: 24px;
            padding: 50px;
            border: 1px solid rgba(0, 78, 137, 0.12);
            box-shadow: 0 20px 60px rgba(0, 78, 137, 0.12);
            animation: fadeInUp 0.8s ease 0.3s backwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .form-section {
            margin-bottom: 40px;
        }

        .section-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title::before {
            content: '';
            width: 4px;
            height: 28px;
            background: var(--gradient-1);
            border-radius: 2px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-group {
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: rgba(26, 26, 46, 0.85);
            font-weight: 500;
            font-size: 0.95rem;
            letter-spacing: 0.01em;
        }

        input,
        select {
            width: 100%;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid rgba(0, 78, 137, 0.15);
            border-radius: 12px;
            font-size: 1rem;
            color: #1a1a2e;
            font-family: 'DM Sans', sans-serif;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        input::placeholder {
            color: rgba(26, 26, 46, 0.4);
        }

        input:focus,
        select:focus {
            outline: none;
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(255, 107, 53, 0.1);
            transform: translateY(-2px);
        }

        select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg width='12' height='8' viewBox='0 0 12 8' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 1L6 6L11 1' stroke='%23FF6B35' stroke-width='2' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 20px center;
            padding-right: 50px;
        }

        select option {
            background: white;
            color: #1a1a2e;
        }

        .btn-primary {
            width: 100%;
            padding: 20px 40px;
            background: var(--gradient-1);
            color: white;
            border: none;
            border-radius: 14px;
            font-size: 1.125rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            box-shadow: 0 10px 30px rgba(255, 107, 53, 0.3);
            margin-top: 16px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, #F7B801 0%, #FF6B35 100%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .btn-primary:hover::before {
            opacity: 1;
        }

        .btn-primary span {
            position: relative;
            z-index: 1;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(255, 107, 53, 0.4);
        }

        .btn-primary:active {
            transform: translateY(-1px);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .result {
            margin-top: 40px;
            padding: 32px;
            border-radius: 16px;
            display: none;
            animation: slideIn 0.5s ease;
            border: 2px solid;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .result.will-pay {
            background: rgba(0, 200, 150, 0.1);
            border-color: var(--success);
        }

        .result.will-not-pay {
            background: rgba(255, 87, 87, 0.1);
            border-color: var(--danger);
        }

        .result h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 16px;
            color: #1a1a2e;
        }

        .result p {
            color: rgba(26, 26, 46, 0.8);
            font-size: 1.05rem;
            margin-bottom: 8px;
        }

        .file-upload {
            border: 3px dashed rgba(255, 107, 53, 0.4);
            padding: 60px 40px;
            text-align: center;
            border-radius: 20px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.6);
            position: relative;
            overflow: hidden;
        }

        .file-upload::before {
            content: '';
            position: absolute;
            inset: 0;
            background: var(--gradient-1);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .file-upload:hover::before {
            opacity: 0.05;
        }

        .file-upload:hover {
            border-color: var(--primary);
            transform: scale(1.02);
            background: rgba(255, 255, 255, 0.8);
        }

        .file-upload h3 {
            font-family: 'Syne', sans-serif;
            font-size: 1.75rem;
            color: #1a1a2e;
            margin-bottom: 12px;
            position: relative;
            z-index: 1;
        }

        .file-upload p {
            color: rgba(26, 26, 46, 0.6);
            font-size: 1.05rem;
            position: relative;
            z-index: 1;
        }

        .download-sample {
            margin-bottom: 32px;
        }

        .btn-secondary {
            padding: 16px 32px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary);
            border: 2px solid rgba(255, 107, 53, 0.3);
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: 'Syne', sans-serif;
        }

        .btn-secondary:hover {
            background: white;
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 107, 53, 0.2);
        }

        .batch-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin: 40px 0;
        }

        .summary-card {
            padding: 32px;
            border-radius: 16px;
            text-align: center;
            border: 2px solid;
            position: relative;
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
        }

        .summary-card::before {
            content: '';
            position: absolute;
            inset: 0;
            opacity: 0.08;
        }

        .summary-card:hover {
            transform: translateY(-5px);
        }

        .total-card {
            border-color: var(--secondary);
        }

        .total-card::before {
            background: var(--gradient-2);
        }

        .approved-card {
            border-color: var(--success);
        }

        .approved-card::before {
            background: var(--success);
        }

        .rejected-card {
            border-color: var(--danger);
        }

        .rejected-card::before {
            background: var(--danger);
        }

        .summary-card h4 {
            color: rgba(26, 26, 46, 0.7);
            font-size: 0.95rem;
            font-weight: 600;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            position: relative;
            z-index: 1;
        }

        .summary-card .value {
            font-family: 'Syne', sans-serif;
            font-size: 3rem;
            font-weight: 800;
            color: #1a1a2e;
            position: relative;
            z-index: 1;
        }

        .table-wrapper {
            overflow-x: auto;
            margin-top: 32px;
            border-radius: 16px;
            border: 1px solid rgba(0, 78, 137, 0.15);
            background: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.95rem;
        }

        th,
        td {
            padding: 18px 20px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 78, 137, 0.08);
        }

        th {
            background: rgba(255, 107, 53, 0.08);
            color: #1a1a2e;
            font-weight: 700;
            font-family: 'Syne', sans-serif;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.05em;
        }

        td {
            color: rgba(26, 26, 46, 0.85);
        }

        tbody tr {
            transition: background 0.2s;
        }

        tbody tr:hover {
            background: rgba(255, 107, 53, 0.03);
        }

        .prediction-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .prediction-badge.approved {
            background: rgba(0, 200, 150, 0.2);
            color: var(--success);
        }

        .prediction-badge.rejected {
            background: rgba(255, 87, 87, 0.2);
            color: var(--danger);
        }

        @media (max-width: 768px) {
            .main-layout {
                flex-direction: column;
            }

            .tabs-wrapper {
                position: relative;
                top: 0;
                width: 100%;
                min-width: auto;
            }

            .content-card {
                padding: 30px 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 2.5rem;
            }

            .batch-summary {
                grid-template-columns: 1fr;
            }
        }

        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }

        .loading.active {
            display: block;
        }

        .spinner {
            width: 60px;
            height: 60px;
            margin: 0 auto 20px;
            border: 4px solid rgba(255, 107, 53, 0.2);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            color: rgba(26, 26, 46, 0.7);
            font-size: 1.1rem;
            font-weight: 500;
        }

        /* ===== USER BAR ===== */
        .user-bar {
            position: fixed;
            top: 20px;
            right: 30px;
            display: flex;
            align-items: center;
            gap: 16px;
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(14px);
            padding: 12px 18px;
            border-radius: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            z-index: 9999;
            font-family: 'Syne', sans-serif;
        }

        .user-bar span {
            font-size: 0.95rem;
            color: #1a1a2e;
            font-weight: 600;
        }

        .logout-btn {
            background: linear-gradient(135deg, #ff5757, #ff6b35);
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 87, 87, 0.4);
        }

        .error-message {
            background: rgba(255, 87, 87, 0.1);
            border: 2px solid var(--danger);
            color: #1a1a2e;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            display: none;
        }

        .error-message.active {
            display: block;
            animation: slideIn 0.3s ease;
        }

        .error-message strong {
            color: var(--danger);
        }
    </style>
</head>
<body>
    <div class="user-bar">
        <span>👤 <?php echo htmlspecialchars($username); ?></span>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="background-decoration">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
        <div class="floating-shape shape-3"></div>
    </div>
    <div class="grid-overlay"></div>

    <div class="container">
        <header>
            <h1>Loan Prediction AI</h1>
            <p class="subtitle">Advanced Machine Learning for Credit Risk Assessment</p>
        </header>

        <div class="main-layout">
            <div class="tabs-wrapper">
                <button class="tab active" onclick="switchTab('single')">
                    <span class="tab-icon">📝</span>
                    <span>Single Prediction</span>
                </button>
                <button class="tab" onclick="switchTab('batch')">
                    <span class="tab-icon">📊</span>
                    <span>Batch Prediction</span>
                </button>
                <button class="tab" onclick="switchTab('history')">
                    <span class="tab-icon">📜</span>
                    <span>Prediction History</span>
                </button>
            </div>

            <div class="content-wrapper">
                <div class="content-card">
                    <!-- Single Prediction Tab -->
                    <div id="single" class="tab-content active">
                        <form id="singleForm">
                            <div class="form-section">
                                <h2 class="section-title">Personal Information</h2>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Full Name</label>
                                        <input type="text" name="name" required placeholder="Enter full name">
                                    </div>
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select name="gender" required>
                                            <option value="">Select gender</option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Marital Status</label>
                                        <select name="marital_status" required>
                                            <option value="">Select status</option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                            <option value="Divorced">Divorced</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Education Level</label>
                                        <select name="education_level" required>
                                            <option value="">Select education</option>
                                            <option value="High School">High School</option>
                                            <option value="Bachelor's">Bachelor's Degree</option>
                                            <option value="Master's">Master's Degree</option>
                                            <option value="PhD">PhD</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h2 class="section-title">Financial Details</h2>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Annual Income (RWF)</label>
                                        <input type="number" name="annual_income" required placeholder="500000">
                                    </div>
                                    <div class="form-group">
                                        <label>Loan Amount (RWF)</label>
                                        <input type="number" name="loan_amount" required placeholder="250000">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Debt to Income Ratio</label>
                                        <input type="number" step="0.01" name="debt_to_income_ratio" required placeholder="0.35">
                                    </div>
                                    <div class="form-group">
                                        <label>Interest Rate (%)</label>
                                        <input type="number" step="0.01" name="interest_rate" required placeholder="5.5">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h2 class="section-title">Credit & Employment</h2>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Credit Score</label>
                                        <input type="number" name="credit_score" required placeholder="720">
                                    </div>
                                    <div class="form-group">
                                        <label>Employment Status</label>
                                        <select name="employment_status" required>
                                            <option value="">Select status</option>
                                            <option value="Employed">Employed</option>
                                            <option value="Unemployed">Unemployed</option>
                                            <option value="Self-Employed">Self-Employed</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Loan Purpose</label>
                                        <select name="loan_purpose" required>
                                            <option value="">Select purpose</option>
                                            <option value="Home">Home</option>
                                            <option value="Auto">Auto</option>
                                            <option value="Education">Education</option>
                                            <option value="Business">Business</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Grade/Subgrade</label>
                                        <select name="grade_subgrade" required>
                                            <option value="">Select grade</option>
                                            <option value="A1">A1</option>
                                            <option value="A2">A2</option>
                                            <option value="A3">A3</option>
                                            <option value="B1">B1</option>
                                            <option value="B2">B2</option>
                                            <option value="B3">B3</option>
                                            <option value="C1">C1</option>
                                            <option value="C2">C2</option>
                                            <option value="C3">C3</option>
                                            <option value="D1">D1</option>
                                            <option value="D2">D2</option>
                                            <option value="D3">D3</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn-primary" id="predictBtn">
                                <span>Predict Loan Application</span>
                            </button>
                        </form>

                        <div id="errorMessage" class="error-message"></div>
                        <div id="singleResult" class="result"></div>
                    </div>

                    <!-- Batch Prediction Tab -->
                    <div id="batch" class="tab-content">
                        <div class="download-sample">
                            <button class="btn-secondary" onclick="downloadSampleCSV()">
                                📥 Download Sample CSV Template
                            </button>
                        </div>

                        <div class="file-upload" onclick="document.getElementById('fileInput').click()">
                            <h3>📁 Upload Your Data File</h3>
                            <p>Supports CSV, Excel (.xlsx, .xls), and TXT formats</p>
                            <input type="file" id="fileInput" accept=".csv,.xlsx,.xls,.txt" style="display:none" onchange="handleFileSelection()">
                        </div>

                        <div id="filePreview" style="display:none; margin-top: 24px;">
                            <div style="background: rgba(0, 200, 150, 0.1); padding: 20px; border-radius: 12px; border: 2px solid var(--success); margin-bottom: 24px;">
                                <p style="color: #1a1a2e; font-weight: 600; margin-bottom: 8px;">✓ File Selected</p>
                                <p id="fileName" style="color: rgba(26, 26, 46, 0.7);"></p>
                            </div>
                            <button class="btn-primary" onclick="processBatchFile()">
                                <span>🔮 Predict Batch Results</span>
                            </button>
                        </div>

                        <div class="loading" id="batchLoading">
                            <div class="spinner"></div>
                            <p class="loading-text">Processing your data...</p>
                        </div>

                        <div id="batchResults" style="display:none">
                            <div class="batch-summary">
                                <div class="summary-card total-card">
                                    <h4>Total Applications</h4>
                                    <div class="value" id="totalCount">0</div>
                                </div>
                                <div class="summary-card approved-card">
                                    <h4>Will Pay Back</h4>
                                    <div class="value" id="approvedCount">0</div>
                                </div>
                                <div class="summary-card rejected-card">
                                    <h4>Will Not Pay Back</h4>
                                    <div class="value" id="rejectedCount">0</div>
                                </div>
                            </div>

                            <div class="table-wrapper">
                                <table id="resultsTable">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Income</th>
                                            <th>Credit Score</th>
                                            <th>Loan Amount</th>
                                            <th>Employment</th>
                                            <th>Prediction</th>
                                            <th>Confidence</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableBody"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Prediction History Tab -->
                    <div id="history" class="tab-content">
                        <h2 class="section-title">Your Prediction History</h2>
                        <div class="table-wrapper">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Applicant</th>
                                        <th>Annual Income</th>
                                        <th>Loan Amount</th>
                                        <th>Type</th>
                                        <th>Prediction</th>
                                        <th>Confidence</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch predictions for current user
                                    $user_id = $_SESSION['user'];
                                    $query = "SELECT * FROM predictions WHERE user_id = ? ORDER BY created_at DESC LIMIT 50";
                                    $stmt = mysqli_prepare($con, $query);
                                    mysqli_stmt_bind_param($stmt, 's', $user_id);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    
                                    if (mysqli_num_rows($result) > 0):
                                        while ($row = mysqli_fetch_assoc($result)):
                                            $isPaid = $row['prediction'] === 'Will Pay Back';
                                    ?>
                                        <tr>
                                            <td><?php echo date('M d, Y H:i', strtotime($row['created_at'])); ?></td>
                                            <td><strong><?php echo htmlspecialchars($row['applicant_name']); ?></strong></td>
                                            <td>RWF <?php echo number_format($row['annual_income'], 0); ?></td>
                                            <td>RWF <?php echo number_format($row['loan_amount'], 0); ?></td>
                                            <td><?php echo ucfirst($row['prediction_type']); ?></td>
                                            <td>
                                                <span class="prediction-badge <?php echo $isPaid ? 'approved' : 'rejected'; ?>">
                                                    <?php echo htmlspecialchars($row['prediction']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo number_format($row['confidence'] * 100, 1); ?>%</td>
                                        </tr>
                                    <?php
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="7" style="text-align: center; padding: 40px; color: rgba(26, 26, 46, 0.5);">
                                                No predictions yet. Make your first prediction!
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Configuration
        const API_URL = 'http://localhost:5000';

        function switchTab(tabName) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            event.target.closest('.tab').classList.add('active');
            document.getElementById(tabName).classList.add('active');
        }

        function showError(message) {
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.innerHTML = `<strong>Error:</strong> ${message}`;
            errorDiv.classList.add('active');
            setTimeout(() => {
                errorDiv.classList.remove('active');
            }, 8000);
        }

        document.getElementById('singleForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            // Reset previous results and errors
            document.getElementById('singleResult').style.display = 'none';
            document.getElementById('errorMessage').classList.remove('active');
            
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData);

            // Convert numeric fields to proper types
            data.annual_income = parseFloat(data.annual_income);
            data.debt_to_income_ratio = parseFloat(data.debt_to_income_ratio);
            data.credit_score = parseInt(data.credit_score);
            data.loan_amount = parseFloat(data.loan_amount);
            data.interest_rate = parseFloat(data.interest_rate);

            // Validate data
            if (isNaN(data.annual_income) || isNaN(data.debt_to_income_ratio) || 
                isNaN(data.credit_score) || isNaN(data.loan_amount) || isNaN(data.interest_rate)) {
                showError('Please ensure all numeric fields contain valid numbers.');
                return;
            }

            // Disable submit button
            const btn = document.getElementById('predictBtn');
            btn.disabled = true;
            btn.innerHTML = '<span>Processing...</span>';

            try {
                console.log('Sending data to API:', data);

                // Get prediction from Flask API
                const response = await fetch(`${API_URL}/predict`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                console.log('Response status:', response.status);

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || `Server error: ${response.status}`);
                }

                const result = await response.json();
                console.log('Prediction result:', result);

                // Validate result has required fields
                if (!result.prediction || result.probability_paid_back === undefined) {
                    throw new Error('Invalid response from server. Missing prediction data.');
                }

                // Display result
                const resultDiv = document.getElementById('singleResult');
                const isPaidBack = result.prediction === 'Will Pay Back';
                
                const probPaidBack = (result.probability_paid_back * 100).toFixed(2);
                const probNotPaidBack = (result.probability_not_paid_back * 100).toFixed(2);

                resultDiv.className = `result ${isPaidBack ? 'will-pay' : 'will-not-pay'}`;
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = `
                    <h3>${isPaidBack ? '✅' : '❌'} ${result.prediction}</h3>
                    <p><strong>Applicant:</strong> ${data.name}</p>
                    <p><strong>Probability of Repayment:</strong> ${probPaidBack}%</p>
                    <p><strong>Risk of Default:</strong> ${probNotPaidBack}%</p>
                    <p><strong>Confidence Level:</strong> ${(result.confidence * 100).toFixed(2)}%</p>
                `;

                // Save to database via PHP
                try {
                    await fetch('save_prediction.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            ...data,
                            prediction: result.prediction,
                            probability_paid_back: result.probability_paid_back,
                            probability_not_paid_back: result.probability_not_paid_back,
                            confidence: result.confidence,
                            prediction_type: 'single'
                        })
                    });
                } catch (dbError) {
                    console.warn('Failed to save to database:', dbError);
                    // Don't show error to user since prediction was successful
                }

                resultDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

            } catch (error) {
                console.error('Prediction error:', error);
                showError(error.message || 'Failed to connect to the prediction server. Please ensure the Flask API is running on port 5000.');
            } finally {
                // Re-enable submit button
                btn.disabled = false;
                btn.innerHTML = '<span>Predict Loan Application</span>';
            }
        });

        let selectedFile = null;

        function handleFileSelection() {
            const fileInput = document.getElementById('fileInput');
            const file = fileInput.files[0];

            if (!file) return;

            selectedFile = file;

            document.getElementById('filePreview').style.display = 'block';
            document.getElementById('fileName').textContent = `📄 ${file.name} (${(file.size / 1024).toFixed(2)} KB)`;

            document.getElementById('batchResults').style.display = 'none';
        }

        async function processBatchFile() {
            if (!selectedFile) {
                alert('Please select a file first!');
                return;
            }

            const loadingDiv = document.getElementById('batchLoading');
            const resultsDiv = document.getElementById('batchResults');

            loadingDiv.classList.add('active');
            resultsDiv.style.display = 'none';

            const formData = new FormData();
            formData.append('file', selectedFile);

            try {
                // Get predictions from Flask API
                const response = await fetch(`${API_URL}/predict_batch`, {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.error || 'Failed to process file');
                }

                const result = await response.json();
                console.log('Batch result:', result);

                loadingDiv.classList.remove('active');

                // Validate result
                if (!result.predictions || !result.summary) {
                    throw new Error('Invalid response from server');
                }

                // Generate unique batch ID
                const batchId = 'batch_' + Date.now();

                // Save batch predictions to database
                try {
                    await fetch('save_batch_predictions.php', {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({
                            batch_id: batchId,
                            predictions: result.predictions
                        })
                    });
                } catch (dbError) {
                    console.warn('Failed to save batch to database:', dbError);
                }

                // Display results
                animateValue('totalCount', 0, result.summary.total, 1000);
                animateValue('approvedCount', 0, result.summary.will_pay_back, 1000);
                animateValue('rejectedCount', 0, result.summary.will_not_pay_back, 1000);

                const tbody = document.getElementById('tableBody');
                tbody.innerHTML = result.predictions.map(p => {
                    const isPaid = p.Prediction === 'Will Pay Back';
                    const probPaid = (p.Probability_Paid_Back * 100).toFixed(1);
                    return `
                        <tr>
                            <td><strong>${p.name || 'N/A'}</strong></td>
                            <td>RWF ${p.annual_income ? p.annual_income.toLocaleString() : 'N/A'}</td>
                            <td>${p.credit_score || 'N/A'}</td>
                            <td>RWF ${p.loan_amount ? p.loan_amount.toLocaleString() : 'N/A'}</td>
                            <td>${p.employment_status || 'N/A'}</td>
                            <td><span class="prediction-badge ${isPaid ? 'approved' : 'rejected'}">${p.Prediction}</span></td>
                            <td>${probPaid}%</td>
                        </tr>
                    `;
                }).join('');

                resultsDiv.style.display = 'block';
                resultsDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });

            } catch (error) {
                console.error('Batch processing error:', error);
                loadingDiv.classList.remove('active');
                showError(error.message || 'Failed to process batch file. Please check your file format and try again.');
            }
        }

        function animateValue(id, start, end, duration) {
            const element = document.getElementById(id);
            const range = end - start;
            const increment = end > start ? 1 : -1;
            const stepTime = Math.abs(Math.floor(duration / range));
            let current = start;

            const timer = setInterval(() => {
                current += increment;
                element.textContent = current;
                if (current === end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }

        function downloadSampleCSV() {
            const csvContent = `name,annual_income,debt_to_income_ratio,credit_score,loan_amount,interest_rate,gender,marital_status,education_level,employment_status,loan_purpose,grade_subgrade
John Doe,500000,0.3,720,250000,5.5,Male,Single,Bachelor's,Employed,Home,A1
Jane Smith,800000,0.25,680,300000,6.0,Female,Married,Master's,Self-Employed,Auto,B2
Mark Brown,300000,0.4,650,150000,7.0,Male,Divorced,High School,Unemployed,Education,C1
Lisa White,600000,0.2,700,200000,5.0,Female,Single,Bachelor's,Employed,Business,B1
Paul Green,450000,0.35,710,180000,6.5,Male,Married,Master's,Employed,Other,C2`;

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "loan_applications_sample.csv";
            link.click();
        }

        // Test API connection on page load
        window.addEventListener('load', async () => {
            try {
                const response = await fetch(`${API_URL}/health`);
                const health = await response.json();
                console.log('API Health Check:', health);
                if (health.status !== 'healthy') {
                    console.warn('API is not healthy:', health);
                }
            } catch (error) {
                console.error('Cannot connect to API:', error);
                showError('Warning: Cannot connect to prediction API. Please ensure Flask server is running on port 5000.');
            }
        });
    </script>
</body>
</html>