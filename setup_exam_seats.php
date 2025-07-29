<?php
include "link.php";

// Create exam_seats table if it doesn't exist
$create_table = "CREATE TABLE IF NOT EXISTS exam_seats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    room_id INT NOT NULL,
    seat_no INT NOT NULL,
    exam_date DATE NOT NULL,
    batch_time ENUM('FN', 'AF') NOT NULL,
    subject_id INT,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES room(rid) ON DELETE CASCADE,
    UNIQUE KEY unique_seat_allocation (room_id, seat_no, exam_date, batch_time),
    UNIQUE KEY unique_student_exam (student_id, exam_date, batch_time)
)";

if(mysqli_query($conn, $create_table)){
    echo "Exam seats table created successfully!";
} else {
    echo "Error creating table: " . mysqli_error($conn);
}
?>
