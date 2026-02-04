-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2026 at 10:14 AM
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
-- Database: `ticketing_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `desc` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_activities`
--

CREATE TABLE `project_activities` (
  `activity_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `activity_name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE `ticket` (
  `ticket_id` int(11) NOT NULL,
  `requester_user_id` int(11) NOT NULL,
  `requested_user_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `purpose_action` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL CHECK (`quantity` >= 1),
  `status` enum('OPEN','PENDING','IN_PROGRESS','ON_HOLD','COMPLETED','REJECTED') DEFAULT 'OPEN',
  `priority` enum('LOW','MEDIUM','HIGH','CRITICAL') DEFAULT 'LOW',
  `priority_notes` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `time_spent` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `f_name` varchar(100) DEFAULT NULL,
  `m_name` varchar(100) DEFAULT NULL,
  `l_name` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('employee','admin','superadmin') NOT NULL,
  `status` enum('Active','Suspended') DEFAULT 'Active',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `profile_photo` LONGBLOB DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`, `desc`) VALUES
(1, 'Office of the General Manager', NULL),
(2, 'Finance Services Department', NULL),
(3, 'Institutional Services Department', NULL),
(4, 'Technical Services Department', NULL),
(5, 'Internal Audit Department', NULL),
(6, 'IT and Energy Trading Department', NULL),
(7, 'Zone Operations Department', NULL);

--
-- Dumping data for table `project_activities`
--

INSERT INTO `project_activities` (`activity_id`, `department_id`, `activity_name`) VALUES
(1, 1, 'Strategic Planning & Business Development'),
(2, 1, 'Executive Reporting & Dashboard Preparation'),
(3, 1, 'Board Meeting Coordination'),
(4, 1, 'Policy Development & Approval'),
(5, 1, 'Crisis Management & Emergency Response'),
(6, 1, 'Performance Review of All Departments'),
(7, 1, 'Others'),
(8, 2, 'Budget Preparation & Monitoring'),
(9, 2, 'Financial Statement Preparation'),
(10, 2, 'Accounts Payable & Receivable Processing'),
(11, 2, 'Payroll Processing & Disbursement'),
(12, 2, 'Invoice Verification & Payment Approval'),
(13, 2, 'Tax Filing & Compliance'),
(14, 2, 'Cash Flow Management'),
(15, 2, 'Others'),
(16, 3, 'Human Resource Management'),
(17, 3, 'Recruitment & Hiring Process'),
(18, 3, 'Employee Training & Development Programs'),
(19, 3, 'Payroll & Benefits Administration'),
(20, 3, 'Legal Compliance & Contract Review'),
(21, 3, 'Records & Document Management'),
(22, 3, 'Administrative Support Services'),
(23, 3, 'Others'),
(24, 4, 'Electrical/Mechanical Equipment Installation'),
(25, 4, 'Infrastructure Repair & Maintenance'),
(26, 4, 'Technical Project Implementation'),
(27, 4, 'Equipment Calibration & Testing'),
(28, 4, 'Facility Maintenance & Repair'),
(29, 4, 'Technical Specification Development'),
(30, 4, 'Others'),
(31, 5, 'Internal Financial Audit'),
(32, 5, 'Compliance Audit & Review'),
(33, 5, 'Risk Assessment & Management'),
(34, 5, 'Process & Procedure Audit'),
(35, 5, 'Fraud Investigation'),
(36, 5, 'Audit Findings Reporting & Follow-up'),
(37, 5, 'Others'),
(38, 6, 'Set-up Computer (new)'),
(39, 6, 'Deployment/Installation of Computer & Accessories'),
(40, 6, 'Installation of LAN/ WAN Equipment & Accessories'),
(41, 6, 'Installation of Software Apps'),
(42, 6, 'Troubleshoot Computer & Accessories'),
(43, 6, 'Troubleshoot Printer'),
(44, 6, 'Troubleshoot UPS'),
(45, 6, 'Troubleshoot LAN/ WAN Equipment & Accessories/ Connection'),
(46, 6, 'Preventive Maintenance of LAN/ WAN Equipment and Accessories'),
(47, 6, 'Preventive Maintenance of Computer and Accessories'),
(48, 6, 'Upload Newbills at WEBSITE'),
(49, 6, 'Set-up/Operate Sound System/ Computer /video conferencing/webinar'),
(50, 6, 'Internet Configuration'),
(51, 6, 'End-user Support/ Assistance'),
(52, 6, 'Others'),
(53, 7, 'Zone Performance Monitoring'),
(54, 7, 'Field Operations Coordination'),
(55, 7, 'Customer Complaint Resolution'),
(56, 7, 'Site Inspection & Safety Compliance'),
(57, 7, 'Inventory Management in Zones'),
(58, 7, 'Daily Operations Reporting'),
(59, 7, 'Emergency Field Response'),
(60, 7, 'Others');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `project_activities`
--
ALTER TABLE `project_activities`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`ticket_id`),
  ADD KEY `requester_user_id` (`requester_user_id`),
  ADD KEY `requested_user_id` (`requested_user_id`),
  ADD KEY `activity_id` (`activity_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `project_activities`
--
ALTER TABLE `project_activities`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `ticket`
--
ALTER TABLE `ticket`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `project_activities`
--
ALTER TABLE `project_activities`
  ADD CONSTRAINT `project_activities_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`);

--
-- Constraints for table `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `ticket_ibfk_1` FOREIGN KEY (`requester_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `ticket_ibfk_2` FOREIGN KEY (`requested_user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `ticket_ibfk_3` FOREIGN KEY (`activity_id`) REFERENCES `project_activities` (`activity_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `department` (`department_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;