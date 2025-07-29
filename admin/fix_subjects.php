<?php
include '../db.php';

// Drop existing constraints and columns if they exist
$drop_constraints = "
ALTER TABLE `batch` 
DROP FOREIGN KEY IF EXISTS `batch_subject_fk`,
DROP COLUMN IF EXISTS `subject_id`";

mysqli_query($conn, $drop_constraints);

// Drop existing subjects table if exists
$drop_table = "DROP TABLE IF EXISTS `subjects`";
mysqli_query($conn, $drop_table);

// Create fresh subjects table
$create_subjects = "
CREATE TABLE `subjects` (
    `subject_id` int(11) NOT NULL AUTO_INCREMENT,
    `subject_code` varchar(20) NOT NULL,
    `subject_name` varchar(100) NOT NULL,
    `class_id` int(11) NOT NULL,
    PRIMARY KEY (`subject_id`),
    KEY `class_id` (`class_id`),
    CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`class_id`) 
    REFERENCES `class` (`class_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if(mysqli_query($conn, $create_subjects)) {
    echo "✓ Subjects table created successfully<br>";
} else {
    echo "✗ Error creating subjects table: " . mysqli_error($conn) . "<br>";
}

// Add subject_id to batch table
$alter_batch = "
ALTER TABLE `batch`
ADD COLUMN `subject_id` int(11) DEFAULT NULL AFTER `class_id`,
ADD CONSTRAINT `batch_subject_fk` 
FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) 
ON DELETE SET NULL;";

if(mysqli_query($conn, $alter_batch)) {
    echo "✓ Batch table modified successfully<br>";
} else {
    echo "✗ Error modifying batch table: " . mysqli_error($conn) . "<br>";
}

// Add sample subjects
$sample_subjects = "
INSERT INTO `subjects` (`subject_code`, `subject_name`, `class_id`) 
SELECT 'CSE101', 'Introduction to Programming', class_id 
FROM class WHERE dept = 'CSE' LIMIT 1;

INSERT INTO `subjects` (`subject_code`, `subject_name`, `class_id`) 
SELECT 'ECE101', 'Basic Electronics', class_id 
FROM class WHERE dept = 'ECE' LIMIT 1;";

if(mysqli_multi_query($conn, $sample_subjects)) {
    echo "✓ Sample subjects added<br>";
} else {
    echo "✗ Error adding sample subjects: " . mysqli_error($conn) . "<br>";
}

echo "<br>Setup complete! <a href='manage_subjects.php'>Go to Manage Subjects</a>";
?>
