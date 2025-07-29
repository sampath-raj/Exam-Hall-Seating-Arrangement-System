<?php
include '../db.php';

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
    echo "Subjects table created successfully";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}

// Add batch table modification for subject_id if not exists
$alter_batch_table = "
ALTER TABLE `batch` 
ADD COLUMN IF NOT EXISTS `subject_id` int(11) DEFAULT NULL AFTER `class_id`,
ADD CONSTRAINT IF NOT EXISTS `batch_subject_fk` 
FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`subject_id`) ON DELETE SET NULL;
";

if(mysqli_query($conn, $alter_batch_table)) {
    echo "\nBatch table modified successfully";
} else {
    echo "\nError modifying batch table: " . mysqli_error($conn);
}
?>
