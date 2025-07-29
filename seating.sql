-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3307
-- Generation Time: Feb 23, 2022 at 03:19 PM
-- Server version: 10.4.13-MariaDB
-- PHP Version: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `seating`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminid` int(11) NOT NULL,
  `name` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(10) NOT NULL,
  PRIMARY KEY (`adminid`),
  UNIQUE KEY `admin_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminid`, `name`, `email`, `password`) VALUES
(1, 'Admin1002', 'admin1002@gmail.com', 'root12'),
(2, 'Admin', 'xyz@gmail.com', 'PjwnJTF6'),
(9, 'Admin1001', 'admin1001@gmail.com', 'AWlxauaL');

-- --------------------------------------------------------

--
-- Table structure for tables in correct order
--

-- First: Create room table
DROP TABLE IF EXISTS `room`;
CREATE TABLE `room` (
  `rid` int(11) NOT NULL AUTO_INCREMENT,
  `room_no` int(11) NOT NULL,
  `floor` int(11) NOT NULL,
  `capacity` int(11) NOT NULL,
  PRIMARY KEY (`rid`),
  UNIQUE KEY `unique_room` (`room_no`, `floor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert room data immediately
INSERT INTO `room` (`rid`, `room_no`, `floor`, `capacity`) VALUES
(18, 3, 4, 10),
(9, 1, 3, 5),
(10, 2, 3, 5);

-- Second: Create class table
DROP TABLE IF EXISTS `class`;
CREATE TABLE `class` (
  `class_id` int(11) NOT NULL AUTO_INCREMENT,
  `year` varchar(20) NOT NULL,
  `dept` varchar(30) NOT NULL,
  `division` varchar(20) NOT NULL,
  PRIMARY KEY (`class_id`),
  UNIQUE KEY `uniqueclass` (`year`,`dept`,`division`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert class data immediately
INSERT INTO `class` (`class_id`, `year`, `dept`, `division`) VALUES
(7, '2nd', 'CSE', 'A'),
(8, '2nd', 'ECE', 'A');

-- Third: Create subjects table
DROP TABLE IF EXISTS `subjects`;
CREATE TABLE `subjects` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `class_id` int(11) NOT NULL,
  PRIMARY KEY (`subject_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert subjects immediately
INSERT INTO `subjects` (`subject_code`, `subject_name`, `class_id`) 
SELECT 'CSE101', 'Introduction to Programming', class_id 
FROM class WHERE dept = 'CSE' AND year = '2nd' AND division = 'A';

INSERT INTO `subjects` (`subject_code`, `subject_name`, `class_id`) 
SELECT 'ECE101', 'Basic Electronics', class_id 
FROM class WHERE dept = 'ECE' AND year = '2nd' AND division = 'A';

-- Finally: Create batch table after all referenced tables exist
DROP TABLE IF EXISTS `batch`;
CREATE TABLE `batch` (
  `batch_id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `startno` int(11) NOT NULL,
  `endno` int(11) NOT NULL,
  `date` date NOT NULL,
  `batch_time` enum('FN','AF') NOT NULL DEFAULT 'FN',
  `total` int(11) GENERATED ALWAYS AS (`endno` - `startno` + 1) VIRTUAL,
  PRIMARY KEY (`batch_id`),
  KEY `room_id` (`room_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `batch_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `room` (`rid`) ON DELETE CASCADE,
  CONSTRAINT `batch_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  CONSTRAINT `batch_subject_fk` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create students table
DROP TABLE IF EXISTS `students`;
CREATE TABLE `students` (
  `student_id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `class` int(11) NOT NULL,
  `rollno` int(11) NOT NULL,
  `seat_no` INT(11) NULL,
  `hall_no` INT(11) NULL,
  `floor_no` INT(11) NULL,
  PRIMARY KEY (`student_id`),
  KEY `class` (`class`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`class`) REFERENCES `class` (`class_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Now insert student data
INSERT INTO `students` (`student_id`, `password`, `name`, `class`, `rollno`, `seat_no`, `hall_no`, `floor_no`) VALUES
(7, 'john44', 'John', 8, 11, NULL, NULL, NULL),
(8, 'h@rry', 'Harry', 8, 8, NULL, NULL, NULL),
(9, 'Jam#s', 'James', 8, 2, NULL, NULL, NULL),
(10, 'Paul45', 'Paul', 8, 3, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `adminid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `batch`
--
ALTER TABLE `batch`
  MODIFY `batch_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `room`
--
ALTER TABLE `room`
  MODIFY `rid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
