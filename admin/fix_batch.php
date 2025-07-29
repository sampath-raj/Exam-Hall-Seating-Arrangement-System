<?php
include '../db.php';

// Drop and recreate batch table with correct format
$fix_batch = "
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if(mysqli_multi_query($conn, $fix_batch)) {
    echo "Batch table structure fixed successfully";
} else {
    echo "Error fixing batch table: " . mysqli_error($conn);
}

// Reinsert sample data
$sample_data = "
INSERT INTO `batch` (`class_id`, `room_id`, `startno`, `endno`, `date`, `batch_time`) VALUES
(7, 18, 1, 7, '2021-06-08', 'FN'),
(8, 18, 1, 3, '2021-06-08', 'FN');";

if(mysqli_query($conn, $sample_data)) {
    echo "<br>Sample data reinserted successfully";
} else {
    echo "<br>Error reinserting sample data: " . mysqli_error($conn);
}

echo "<br><a href='dashboard.php'>Return to Dashboard</a>";
?>
