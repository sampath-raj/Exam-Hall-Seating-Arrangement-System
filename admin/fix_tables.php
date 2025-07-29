<?php
include '../db.php';

$create_table = "
CREATE TABLE IF NOT EXISTS `subjects` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `class_id` int(11) NOT NULL,
  PRIMARY KEY (`subject_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if(mysqli_query($conn, $create_table)) {
    echo "Subjects table created successfully<br>";
} else {
    echo "Error creating table: " . mysqli_error($conn) . "<br>";
}

$alter_batch = "ALTER TABLE `batch` 
                ADD COLUMN IF NOT EXISTS `subject_id` int(11) DEFAULT NULL AFTER `class_id`";
                
if(mysqli_query($conn, $alter_batch)) {
    echo "Batch table modified successfully<br>";
} else {
    echo "Error modifying batch table: " . mysqli_error($conn) . "<br>";
}

$add_constraint = "ALTER TABLE `batch`
                  ADD CONSTRAINT `batch_subject_fk` 
                  FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) 
                  ON DELETE SET NULL";

if(mysqli_query($conn, $add_constraint)) {
    echo "Foreign key constraint added successfully<br>";
} else {
    echo "Note: Foreign key may already exist<br>";
}

// Add sample data
$sample_data = "INSERT INTO `subjects` (`subject_code`, `subject_name`, `class_id`) 
                SELECT 'CSE101', 'Introduction to Programming', class_id 
                FROM class LIMIT 1";

if(mysqli_query($conn, $sample_data)) {
    echo "Sample data added successfully<br>";
} else {
    echo "Note: Sample data may already exist<br>";
}

echo "<br><a href='manage_subjects.php'>Return to Manage Subjects</a>";
?>
