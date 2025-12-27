-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 28, 2025 at 12:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `loanpayback`
--

-- --------------------------------------------------------

--
-- Table structure for table `predictions`
--

CREATE TABLE `predictions` (
  `id` int(11) NOT NULL,
  `user_id` varchar(100) DEFAULT NULL,
  `applicant_name` varchar(255) NOT NULL,
  `annual_income` decimal(15,2) DEFAULT NULL,
  `debt_to_income_ratio` decimal(5,2) DEFAULT NULL,
  `credit_score` int(11) DEFAULT NULL,
  `loan_amount` decimal(15,2) DEFAULT NULL,
  `interest_rate` decimal(5,2) DEFAULT NULL,
  `gender` varchar(50) DEFAULT NULL,
  `marital_status` varchar(50) DEFAULT NULL,
  `education_level` varchar(100) DEFAULT NULL,
  `employment_status` varchar(100) DEFAULT NULL,
  `loan_purpose` varchar(100) DEFAULT NULL,
  `grade_subgrade` varchar(10) DEFAULT NULL,
  `prediction` varchar(50) NOT NULL,
  `probability_paid_back` decimal(5,4) DEFAULT NULL,
  `probability_not_paid_back` decimal(5,4) DEFAULT NULL,
  `confidence` decimal(5,4) DEFAULT NULL,
  `prediction_type` enum('single','batch') NOT NULL DEFAULT 'single',
  `batch_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `predictions`
--

INSERT INTO `predictions` (`id`, `user_id`, `applicant_name`, `annual_income`, `debt_to_income_ratio`, `credit_score`, `loan_amount`, `interest_rate`, `gender`, `marital_status`, `education_level`, `employment_status`, `loan_purpose`, `grade_subgrade`, `prediction`, `probability_paid_back`, `probability_not_paid_back`, `confidence`, `prediction_type`, `batch_id`, `created_at`) VALUES
(1, 'Bobo', ' Alice Brown', 95000.00, 0.15, 800, 20000.00, 4.48, 'Female', 'Married', 'Master\'s', 'Employed', 'Business', 'A1', 'Will Pay Back', 0.9863, 0.0137, 0.9863, 'single', NULL, '2025-12-27 23:37:46'),
(2, 'Bobo', 'John Doe', 500000.00, 0.30, 720, 250000.00, 5.50, 'Male', 'Single', 'Bachelor\'s', 'Employed', 'Home', 'A1', 'Will Pay Back', 0.6128, 0.3872, 0.6128, 'batch', 'batch_1766878756980', '2025-12-27 23:39:17'),
(3, 'Bobo', 'Jane Smith', 800000.00, 0.25, 680, 300000.00, 6.00, 'Female', 'Married', 'Master\'s', 'Self-Employed', 'Auto', 'B2', 'Will Pay Back', 0.9460, 0.0540, 0.9460, 'batch', 'batch_1766878756980', '2025-12-27 23:39:17'),
(4, 'Bobo', 'Mark Brown', 30000.00, 0.40, 50, 15000000.00, 7.00, 'Male', 'Divorced', 'High School', 'Unemployed', 'Education', 'C1', 'Will Not Pay Back', 0.1047, 0.8953, 0.8953, 'batch', 'batch_1766878756980', '2025-12-27 23:39:17'),
(5, 'Bobo', 'Lisa White', 600000.00, 0.20, 30, 200000.00, 5.00, 'Female', 'Single', 'Bachelor\'s', 'Employed', 'Business', 'B1', 'Will Pay Back', 0.6055, 0.3945, 0.6055, 'batch', 'batch_1766878756980', '2025-12-27 23:39:17'),
(6, 'Bobo', 'Paul Green', 450000.00, 0.35, 710, 180000.00, 6.50, 'Male', 'Married', 'Master\'s', 'Employed', 'Other', 'C2', 'Will Pay Back', 0.7178, 0.2822, 0.7178, 'batch', 'batch_1766878756980', '2025-12-27 23:39:17');

-- --------------------------------------------------------

--
-- Stand-in structure for view `prediction_summary`
-- (See below for the actual view)
--
CREATE TABLE `prediction_summary` (
`prediction_date` date
,`user_id` varchar(100)
,`prediction_type` enum('single','batch')
,`total_predictions` bigint(21)
,`will_pay_count` decimal(22,0)
,`will_not_pay_count` decimal(22,0)
,`avg_confidence` decimal(9,8)
,`avg_loan_amount` decimal(19,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `email`, `password`, `created_at`, `last_login`) VALUES
(1, 'System Administrator', 'admin', 'admin@loan.com', 'Admin123', '2025-12-17 13:14:23', NULL),
(2, 'HAGENIMANA Jean Bosco', 'Bobo', 'hagebobo2024@gmail.com', '$2y$10$CGtJOXrxlTNBndUx72fy6ukQQJAzi8NFSgy7a.CpRd1YIF.LoMjwm', '2025-12-17 16:04:01', NULL);

-- --------------------------------------------------------

--
-- Structure for view `prediction_summary`
--
DROP TABLE IF EXISTS `prediction_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `prediction_summary`  AS SELECT cast(`predictions`.`created_at` as date) AS `prediction_date`, `predictions`.`user_id` AS `user_id`, `predictions`.`prediction_type` AS `prediction_type`, count(0) AS `total_predictions`, sum(case when `predictions`.`prediction` = 'Will Pay Back' then 1 else 0 end) AS `will_pay_count`, sum(case when `predictions`.`prediction` = 'Will Not Pay Back' then 1 else 0 end) AS `will_not_pay_count`, avg(`predictions`.`confidence`) AS `avg_confidence`, avg(`predictions`.`loan_amount`) AS `avg_loan_amount` FROM `predictions` GROUP BY cast(`predictions`.`created_at` as date), `predictions`.`user_id`, `predictions`.`prediction_type` ORDER BY cast(`predictions`.`created_at` as date) DESC, `predictions`.`user_id` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `predictions`
--
ALTER TABLE `predictions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_batch_id` (`batch_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_prediction` (`prediction`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`),
  ADD KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `predictions`
--
ALTER TABLE `predictions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
