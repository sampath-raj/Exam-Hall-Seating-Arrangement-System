<?php
include '../db.php';

// Create subjects table
$create_subjects_table = "
CREATE TABLE IF NOT EXISTS `subjects` (
  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_code` varchar(20) NOT NULL,
  `subject_name` varchar(100) NOT NULL,
  `class_id` int(11) NOT NULL,
  PRIMARY KEY (`subject_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

if(mysqli_query($conn, $create_subjects_table)) {
    echo "<p>✓ Subjects table created successfully</p>";
} else {
    echo "<p>✗ Error creating subjects table: " . mysqli_error($conn) . "</p>";
}

// Modify batch table to include subject_id if not exists
$check_column = mysqli_query($conn, "SHOW COLUMNS FROM `batch` LIKE 'subject_id'");
if(mysqli_num_rows($check_column) == 0) {
    $alter_batch_table = "
    ALTER TABLE `batch` 
    ADD COLUMN `subject_id` int(11) DEFAULT NULL AFTER `class_id`,
    ADD CONSTRAINT `batch_subject_fk` 
    FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE SET NULL;
    ";

    if(mysqli_query($conn, $alter_batch_table)) {
        echo "<p>✓ Batch table modified successfully</p>";
    } else {
        echo "<p>✗ Error modifying batch table: " . mysqli_error($conn) . "</p>";
    }
} else {
    echo "<p>✓ Subject_id column already exists in batch table</p>";
}

// Add some sample subjects
$sample_subjects = "
INSERT INTO `subjects` (`subject_code`, `subject_name`, `class_id`) 
SELECT 'CSE101', 'Introduction to Programming', class_id FROM class WHERE dept = 'CSE' LIMIT 1;
";

if(mysqli_query($conn, $sample_subjects)) {
    echo "<p>✓ Sample subjects added successfully</p>";
} else {
    echo "<p>Note: Sample subjects not added (may already exist)</p>";
}

echo "<p>Setup complete! You can now <a href='manage_subjects.php'>go back to manage subjects</a>.</p>";
?>
