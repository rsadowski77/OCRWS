-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 14, 2026 at 07:29 PM
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
-- Database: `ocrws`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(20) NOT NULL,
  `title` varchar(255) NOT NULL,
  `capacity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `title`, `capacity`) VALUES
(5, 'ENG225', 'Introduction to Film', 1),
(6, 'CST313', 'Software Testing', 30),
(7, 'COM200', 'Interpersonal Communication', 30);

-- --------------------------------------------------------

--
-- Table structure for table `course_offerings`
--

CREATE TABLE `course_offerings` (
  `id` int(11) NOT NULL,
  `semester_pk` int(11) NOT NULL,
  `course_pk` int(11) NOT NULL,
  `instructor_pk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `course_offerings`
--

INSERT INTO `course_offerings` (`id`, `semester_pk`, `course_pk`, `instructor_pk`) VALUES
(4, 1, 6, 5),
(5, 2, 5, 5);

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_pk` int(11) NOT NULL,
  `offering_pk` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_pk`, `offering_pk`, `created_at`) VALUES
(22, 3, 4, '2026-03-10 17:04:55'),
(26, 3, 5, '2026-03-14 11:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL,
  `user_pk` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `profiles`
--

INSERT INTO `profiles` (`id`, `user_pk`, `full_name`, `email`, `phone`, `created_at`) VALUES
(3, 3, 'Robert Sadowski', 'robert.sadowski@student.uagc.edu', '(217) 379-7827', '2026-03-10 12:21:08'),
(4, 4, 'Administrator', 'admin1@administrator.uagc.edu', '(777) 777-7777', '2026-03-10 13:34:56'),
(5, 5, 'Primary Instructor', 'instructor@instructor.uagc.edu', '(555) 555-5555', '2026-03-10 14:20:36');

-- --------------------------------------------------------

--
-- Table structure for table `semesters`
--

CREATE TABLE `semesters` (
  `id` int(11) NOT NULL,
  `term` varchar(10) NOT NULL,
  `term_order` int(11) NOT NULL,
  `year` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `semesters`
--

INSERT INTO `semesters` (`id`, `term`, `term_order`, `year`) VALUES
(1, 'Spring', 1, 2026),
(2, 'Summer', 2, 2026),
(3, 'Fall', 3, 2026);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Student','Instructor','Administrator') NOT NULL DEFAULT 'Student',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `user_id`, `password_hash`, `role`, `created_at`) VALUES
(3, 'rsadowski77', '$2y$10$zcryR30DX11VayTBTWqu8.NnShLWvlCS/bS//hJEQxmmDrQPbjOVe', 'Student', '2026-03-10 12:21:08'),
(4, 'admin', '$2y$10$6OXdekmCYvFidqGInOnJmOs17A1QPzyi2QQu/xtbxOgVxN3Z1m5Ce', 'Administrator', '2026-03-10 13:34:56'),
(5, 'instructor', '$2y$10$xSs5P8XcTPiyOl3LBfZGguipOVnuKACFo8.D6ENCCwz5.10qluWja', 'Instructor', '2026-03-10 14:20:36');

-- --------------------------------------------------------

--
-- Table structure for table `waitlist_entries`
--

CREATE TABLE `waitlist_entries` (
  `id` int(11) NOT NULL,
  `user_pk` int(11) NOT NULL,
  `offering_pk` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `status` enum('Waiting','Enrolled','Removed','Dropped') NOT NULL DEFAULT 'Waiting',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `waitlist_entries`
--

INSERT INTO `waitlist_entries` (`id`, `user_pk`, `offering_pk`, `position`, `status`, `created_at`) VALUES
(2, 4, 5, 1, 'Waiting', '2026-03-10 15:04:11'),
(4, 3, 5, 1, 'Enrolled', '2026-03-10 15:45:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_course_code` (`course_code`);

--
-- Indexes for table `course_offerings`
--
ALTER TABLE `course_offerings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_offering` (`semester_pk`,`course_pk`,`instructor_pk`),
  ADD KEY `fk_offering_course` (`course_pk`),
  ADD KEY `fk_offering_instructor` (`instructor_pk`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_enrollment` (`user_pk`,`offering_pk`),
  ADD KEY `fk_enroll_offering` (`offering_pk`);

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_profiles_email` (`email`),
  ADD KEY `fk_profiles_users` (`user_pk`);

--
-- Indexes for table `semesters`
--
ALTER TABLE `semesters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_semester` (`term`,`year`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_user_id` (`user_id`);

--
-- Indexes for table `waitlist_entries`
--
ALTER TABLE `waitlist_entries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_waitlist_active` (`user_pk`,`offering_pk`) USING BTREE,
  ADD KEY `fk_waitlist_offering` (`offering_pk`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `course_offerings`
--
ALTER TABLE `course_offerings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `semesters`
--
ALTER TABLE `semesters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `waitlist_entries`
--
ALTER TABLE `waitlist_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_offerings`
--
ALTER TABLE `course_offerings`
  ADD CONSTRAINT `fk_offering_course` FOREIGN KEY (`course_pk`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offering_instructor` FOREIGN KEY (`instructor_pk`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_offering_semester` FOREIGN KEY (`semester_pk`) REFERENCES `semesters` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `fk_enroll_offering` FOREIGN KEY (`offering_pk`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_enroll_user` FOREIGN KEY (`user_pk`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `profiles`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_profiles_users` FOREIGN KEY (`user_pk`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `waitlist_entries`
--
ALTER TABLE `waitlist_entries`
  ADD CONSTRAINT `fk_waitlist_offering` FOREIGN KEY (`offering_pk`) REFERENCES `course_offerings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_waitlist_user` FOREIGN KEY (`user_pk`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
