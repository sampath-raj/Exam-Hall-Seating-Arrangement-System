<?php
session_start();
include "../link.php";

// Check if exam_seats table exists
$tableExists = false;
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'exam_seats'");
if(mysqli_num_rows($check_table) > 0) {
    $tableExists = true;
} else {
    // Create exam_seats table
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
        $tableExists = true;
    } else {
        $_SESSION['message'] = "Error creating exam_seats table. Contact administrator.";
    }
}
?>
<html>
<head>
    <title>Allocate Seats</title>
    <link rel="stylesheet" href="common.css">
    <style type="text/css">
        .date-selector {
            margin-bottom: 15px;
        }
        .batch-selector {
            margin-bottom: 15px;
        }
        .seating-layout {
            margin-top: 20px;
        }
        .desk {
            border: 1px solid #ccc;
            border-radius: 5px;
            margin: 10px;
            padding: 10px;
            background-color: #f8f9fa;
        }
        .seat {
            display: inline-block;
            margin: 5px;
            padding: 10px;
            width: 45%;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .seat.empty {
            background-color: #f0f0f0;
            color: #999;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4>DASHBOARD</h4>
        </div>
        <ul class="list-unstyled components">
            <li>
                <a href="add_class.php"><img src="https://img.icons8.com/ios-filled/26/ffffff/google-classroom.png"/> Classes</a>
            </li>
            <li>
                <a href="add_student.php"><img src="https://img.icons8.com/ios-filled/25/ffffff/student-registration.png"/> Students</a>
            </li>
            <li>
                <a href="add_room.php"><img src="https://img.icons8.com/metro/25/ffffff/building.png"/> Rooms</a>
            </li>
            <li>
                <a href="manage_subjects.php"><img src="https://img.icons8.com/ios-filled/25/ffffff/book.png"/> Subjects</a>
            </li>
            <li>
                <a href="dashboard.php"><img src="https://img.icons8.com/nolan/30/ffffff/summary-list.png"/> Allotment</a>
            </li>
            <li>
                <a href="allocate_seats.php" class="active_link"><img src="https://img.icons8.com/ios-filled/30/ffffff/room.png"/> Seats Allocation</a>
            </li>
        </ul>
    </nav>
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button><span class="page-name"> Allocate Seats</span>
                <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ml-auto">
                        <li class="nav-item active">
                            <a class="nav-link" href="../logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="main-content">
            <?php
            if(isset($_SESSION['message'])){
                echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['message']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
                unset($_SESSION['message']);
            }

            if(!$tableExists) {
                echo "<div class='alert alert-danger'>Cannot allocate seats: exam_seats table not available.</div>";
            } else {
            ?>
            <form method="POST" class="card p-4">
                <h4>Allocate Seats for Examination</h4>
                
                <div class="form-group date-selector">
                    <label>Select Date:</label>
                    <select name="exam_date" class="form-control" required>
                        <option value="">--Select Exam Date--</option>
                        <?php
                        // Get unique exam dates
                        $dates_query = mysqli_query($conn, "SELECT DISTINCT date FROM batch ORDER BY date");
                        while($date = mysqli_fetch_assoc($dates_query)) {
                            $formatted_date = date('d-m-Y (l)', strtotime($date['date']));
                            echo "<option value='{$date['date']}'>{$formatted_date}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group batch-selector">
                    <label>Select Batch:</label>
                    <select name="batch_time" class="form-control" required>
                        <option value="FN">Forenoon (FN)</option>
                        <option value="AF">Afternoon (AF)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Select Room:</label>
                    <select name="room_id" class="form-control" required>
                        <option value="">--Select Room--</option>
                        <?php
                        $rooms = mysqli_query($conn, "SELECT rid, room_no, floor FROM room");
                        while($room = mysqli_fetch_assoc($rooms)) {
                            echo "<option value='{$room['rid']}'>Room {$room['room_no']} (Floor {$room['floor']})</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <input type="submit" name="allocate_seats" value="Allocate Seats" class="btn btn-primary mt-3">
            </form>
            
            <?php
            if(isset($_POST['allocate_seats'])) {
                $roomid = $_POST['room_id'];
                $exam_date = $_POST['exam_date'];
                $batch_time = $_POST['batch_time'];
                
                // Validate the room has allocations for this date and batch
                $check_allocation = mysqli_query($conn, "SELECT COUNT(*) as count FROM batch 
                                                        WHERE room_id = '$roomid' 
                                                        AND date = '$exam_date' 
                                                        AND batch_time = '$batch_time'");
                $allocation_exists = mysqli_fetch_assoc($check_allocation)['count'];
                
                if($allocation_exists == 0) {
                    echo "<div class='alert alert-danger mt-4'>No allocations found for this room on the selected date and batch.</div>";
                } else {
                    // Check if seats are already allocated for this exam
                    $existing_allocation = mysqli_query($conn, "SELECT COUNT(*) as count FROM exam_seats 
                                                              WHERE room_id = '$roomid' 
                                                              AND exam_date = '$exam_date' 
                                                              AND batch_time = '$batch_time'");
                    $has_allocation = mysqli_fetch_assoc($existing_allocation)['count'] > 0;
                    
                    if($has_allocation) {
                        echo "<div class='alert alert-warning mt-4'>
                                Seats have already been allocated for this exam. 
                                <form method='post'>
                                    <input type='hidden' name='room_id' value='$roomid'>
                                    <input type='hidden' name='exam_date' value='$exam_date'>
                                    <input type='hidden' name='batch_time' value='$batch_time'>
                                    <button type='submit' name='reallocate_seats' class='btn btn-sm btn-warning'>Reallocate Seats</button>
                                    <a href='view_allocation.php?room=$roomid&date=$exam_date&batch=$batch_time' class='btn btn-sm btn-info'>View Existing Allocation</a>
                                </form>
                              </div>";
                    } else {
                        list($seat_matrix, $desk_assignments) = allocateSeats($roomid, $exam_date, $batch_time);
                        displaySeatingPlan($seat_matrix, $desk_assignments, $roomid, $exam_date, $batch_time);
                    }
                }
            }
            
            // Handle reallocation request
            if(isset($_POST['reallocate_seats'])) {
                $roomid = $_POST['room_id'];
                $exam_date = $_POST['exam_date'];
                $batch_time = $_POST['batch_time'];
                
                // Delete existing allocations
                mysqli_query($conn, "DELETE FROM exam_seats 
                                   WHERE room_id = '$roomid' 
                                   AND exam_date = '$exam_date' 
                                   AND batch_time = '$batch_time'");
                
                // Reallocate seats
                list($seat_matrix, $desk_assignments) = allocateSeats($roomid, $exam_date, $batch_time);
                displaySeatingPlan($seat_matrix, $desk_assignments, $roomid, $exam_date, $batch_time);
            }
            }  // End of if($tableExists) block
            ?>
        </div>
    </div>
</div>
<?php include'footer.php' ?>
</body>
</html>

<?php
// Update the allocateSeats function to ensure same department students are never at the same desk
function allocateSeats($roomid, $exam_date, $batch_time) {
    global $conn;
    
    // Get room details
    $room_query = mysqli_query($conn, "SELECT capacity, room_no, floor FROM room WHERE rid = '$roomid'");
    $room = mysqli_fetch_assoc($room_query);
    $total_seats = $room['capacity'];
    
    // Calculate number of desks (assuming 2 seats per desk)
    $total_desks = ceil($total_seats / 2);
    
    // Get all students from batches allocated to this room on this date and time
    $students_query = "SELECT s.student_id, s.name, s.rollno, c.dept, c.year, c.class_id, 
                       s.class, b.subject_id, sub.subject_code, sub.subject_name
                       FROM batch b
                       INNER JOIN class c ON b.class_id = c.class_id
                       INNER JOIN students s ON s.class = c.class_id AND s.rollno BETWEEN b.startno AND b.endno
                       LEFT JOIN subjects sub ON b.subject_id = sub.subject_id
                       WHERE b.room_id = '$roomid' 
                       AND b.date = '$exam_date'
                       AND b.batch_time = '$batch_time'
                       ORDER BY c.dept, c.year, s.rollno";
    
    $result = mysqli_query($conn, $students_query);
    
    if(!$result || mysqli_num_rows($result) == 0) {
        $_SESSION['message'] = "No students found for the selected criteria.";
        return array(array(), array());
    }
    
    // Group students by department
    $students_by_dept = array();
    $departments = array();
    
    while($student = mysqli_fetch_assoc($result)) {
        $students_by_dept[$student['dept']][] = $student;
        if (!in_array($student['dept'], $departments)) {
            $departments[] = $student['dept'];
        }
    }
    
    // Assign colors to departments for visual distinction
    $colors = array('#ffcccc', '#ccffcc', '#ccccff', '#ffffcc', '#ffccff', '#ccffff');
    $dept_colors = array();
    foreach ($departments as $i => $dept) {
        $dept_colors[$dept] = $colors[$i % count($colors)];
    }
    
    // Initialize seating arrangement
    $seat_matrix = array();
    $desk_assignments = array();
    
    // Create desk layout (1-based indexing)
    for($desk = 1; $desk <= $total_desks; $desk++) {
        $desk_assignments[$desk] = array(
            'left' => ($desk * 2) - 1,  // Odd numbers
            'right' => $desk * 2        // Even numbers
        );
    }
    
    // Track assigned students and department indices
    $assigned_students = array();
    $dept_index = array();
    foreach($departments as $dept) {
        $dept_index[$dept] = 0;
    }
    
    // DESK-BY-DESK ALLOCATION: Process each desk independently
    for($desk = 1; $desk <= $total_desks; $desk++) {
        $left_seat = $desk_assignments[$desk]['left'];
        $right_seat = $desk_assignments[$desk]['right'];
        $desk_depts = array();
        
        // First pass - try to find two students from different departments for this desk
        foreach($departments as $left_dept) {
            if(!isset($students_by_dept[$left_dept][$dept_index[$left_dept]]) || 
               in_array($students_by_dept[$left_dept][$dept_index[$left_dept]]['student_id'], $assigned_students)) {
                continue;
            }
            
            // Get the left seat student
            $left_student = $students_by_dept[$left_dept][$dept_index[$left_dept]];
            
            // Look for a right seat student from a different department
            foreach($departments as $right_dept) {
                if($right_dept == $left_dept) {
                    continue; // Skip same department - THIS IS THE KEY CONSTRAINT
                }
                
                if(!isset($students_by_dept[$right_dept][$dept_index[$right_dept]]) || 
                   in_array($students_by_dept[$right_dept][$dept_index[$right_dept]]['student_id'], $assigned_students)) {
                    continue;
                }
                
                // Get the right seat student
                $right_student = $students_by_dept[$right_dept][$dept_index[$right_dept]];
                
                // We found a valid pair! Assign both students
                // Assign left student
                $seat_matrix[$left_seat] = array(
                    'name' => $left_student['name'],
                    'dept' => $left_student['dept'],
                    'year' => $left_student['year'],
                    'rollno' => $left_student['rollno'],
                    'desk_no' => $desk,
                    'subject_code' => $left_student['subject_code'],
                    'subject_name' => $left_student['subject_name'],
                    'dept_color' => $dept_colors[$left_student['dept']]
                );
                
                // Insert left student into database
                $insert_left = "INSERT INTO exam_seats (student_id, room_id, seat_no, exam_date, batch_time, subject_id)
                                VALUES ('{$left_student['student_id']}', '$roomid', '$left_seat', 
                                        '$exam_date', '$batch_time', '{$left_student['subject_id']}')";
                mysqli_query($conn, $insert_left);
                
                // Assign right student
                $seat_matrix[$right_seat] = array(
                    'name' => $right_student['name'],
                    'dept' => $right_student['dept'],
                    'year' => $right_student['year'],
                    'rollno' => $right_student['rollno'],
                    'desk_no' => $desk,
                    'subject_code' => $right_student['subject_code'],
                    'subject_name' => $right_student['subject_name'],
                    'dept_color' => $dept_colors[$right_student['dept']]
                );
                
                // Insert right student into database
                $insert_right = "INSERT INTO exam_seats (student_id, room_id, seat_no, exam_date, batch_time, subject_id)
                                VALUES ('{$right_student['student_id']}', '$roomid', '$right_seat', 
                                        '$exam_date', '$batch_time', '{$right_student['subject_id']}')";
                mysqli_query($conn, $insert_right);
                
                // Mark both students as assigned
                $assigned_students[] = $left_student['student_id'];
                $assigned_students[] = $right_student['student_id'];
                
                // Increment department indices
                $dept_index[$left_dept]++;
                $dept_index[$right_dept]++;
                
                // Track desk departments
                $desk_depts = array($left_dept, $right_dept);
                break;
            }
            
            if(!empty($desk_depts)) {
                break; // We've assigned both seats, move to next desk
            }
        }
        
        // If we couldn't find a pair for this desk, try to fill at least one seat
        if(empty($desk_depts)) {
            // Try to find at least one student for the left seat
            foreach($departments as $dept) {
                if(isset($students_by_dept[$dept][$dept_index[$dept]]) && 
                   !in_array($students_by_dept[$dept][$dept_index[$dept]]['student_id'], $assigned_students)) {
                    
                    $student = $students_by_dept[$dept][$dept_index[$dept]];
                    $dept_index[$dept]++;
                    
                    // Assign student to left seat
                    $seat_matrix[$left_seat] = array(
                        'name' => $student['name'],
                        'dept' => $student['dept'],
                        'year' => $student['year'],
                        'rollno' => $student['rollno'],
                        'desk_no' => $desk,
                        'subject_code' => $student['subject_code'],
                        'subject_name' => $student['subject_name'],
                        'dept_color' => $dept_colors[$student['dept']]
                    );
                    
                    // Insert into database
                    $insert_seat = "INSERT INTO exam_seats (student_id, room_id, seat_no, exam_date, batch_time, subject_id)
                                   VALUES ('{$student['student_id']}', '$roomid', '$left_seat', '$exam_date', '$batch_time', '{$student['subject_id']}')";
                    mysqli_query($conn, $insert_seat);
                    
                    $assigned_students[] = $student['student_id'];
                    $desk_depts[] = $student['dept'];
                    break;
                }
            }
        }
    }
    
    // Store department colors for displaying
    $_SESSION['dept_colors'] = $dept_colors;
    
    // Set session message
    $_SESSION['message'] = "Seats allocated successfully for room {$room['room_no']} on " . date('d-m-Y', strtotime($exam_date)) . 
                          " for " . ($batch_time == 'FN' ? 'Forenoon' : 'Afternoon') . " batch. " .
                          "Students from the same department are never seated at the same desk.";
    
    return array($seat_matrix, $desk_assignments);
}

// Update the displaySeatingPlan function to explain the seating rule
function displaySeatingPlan($seat_matrix, $desk_assignments, $roomid, $exam_date, $batch_time) {
    global $conn;
    
    // Get room details
    $room_query = mysqli_query($conn, "SELECT room_no, floor FROM room WHERE rid = '$roomid'");
    $room = mysqli_fetch_assoc($room_query);
    
    echo "<div class='card mt-4 p-3'>";
    echo "<h3>Seating Arrangement</h3>";
    echo "<p><strong>Room:</strong> {$room['room_no']} | <strong>Floor:</strong> {$room['floor']} | 
          <strong>Date:</strong> " . date('d-m-Y', strtotime($exam_date)) . " | 
          <strong>Session:</strong> " . ($batch_time == 'FN' ? 'Forenoon' : 'Afternoon') . "</p>";
    
    // Add department color legend
    if (isset($_SESSION['dept_colors'])) {
        echo "<div class='mb-3 p-2 border rounded bg-light'><h5>Department Color Legend</h5><div class='d-flex flex-wrap'>";
        foreach ($_SESSION['dept_colors'] as $dept => $color) {
            echo "<div class='mr-3 mb-1'><span class='px-2 py-1 rounded' style='background-color: $color'>$dept</span></div>";
        }
        echo "</div></div>";
        
        // Updated explanation to clarify the seating rule
        echo "<div class='alert alert-info'>
                <strong>Seating Rule:</strong> Students from the same department are NEVER seated at the same desk.
                Each desk always has students from different departments sitting together.
                This helps maintain academic integrity during exams.
              </div>";
    }
    
    // Rest of the displaySeatingPlan function remains the same
    // ...existing code...
    echo "<div class='seating-layout'>";
    
    // Display desk-wise arrangement
    // If zigzag map exists, display desks in zigzag order
    if(isset($_SESSION['zigzag_map'])) {
        // Flatten zigzag map for display
        $display_order = array();
        foreach($_SESSION['zigzag_map'] as $row) {
            foreach($row as $desk) {
                $display_order[] = $desk;
            }
        }
        
        foreach($display_order as $desk_no) {
            echo "<div class='desk'>";
            echo "<h5>Desk $desk_no</h5>";
            
            // Left seat
            $left_seat = $desk_assignments[$desk_no]['left'];
            echo "<div class='seat" . (!isset($seat_matrix[$left_seat]) ? " empty" : "") . "' " . 
                 (isset($seat_matrix[$left_seat]) ? "style='background-color: " . $seat_matrix[$left_seat]['dept_color'] . "'" : "") . ">";
            if(isset($seat_matrix[$left_seat])) {
                $student = $seat_matrix[$left_seat];
                echo "<div><strong>Seat $left_seat:</strong> {$student['name']}</div>";
                echo "<div><strong>Dept/Year:</strong> {$student['dept']} / {$student['year']}</div>";
                echo "<div><strong>Roll:</strong> {$student['rollno']}</div>";
                if(!empty($student['subject_code'])) {
                    echo "<div><strong>Subject:</strong> {$student['subject_code']}</div>";
                }
            } else {
                echo "Seat $left_seat: Empty";
            }
            echo "</div>";
            
            // Right seat
            $right_seat = $desk_assignments[$desk_no]['right'];
            echo "<div class='seat" . (!isset($seat_matrix[$right_seat]) ? " empty" : "") . "' " . 
                 (isset($seat_matrix[$right_seat]) ? "style='background-color: " . $seat_matrix[$right_seat]['dept_color'] . "'" : "") . ">";
            if(isset($seat_matrix[$right_seat])) {
                $student = $seat_matrix[$right_seat];
                echo "<div><strong>Seat $right_seat:</strong> {$student['name']}</div>";
                echo "<div><strong>Dept/Year:</strong> {$student['dept']} / {$student['year']}</div>";
                echo "<div><strong>Roll:</strong> {$student['rollno']}</div>";
                if(!empty($student['subject_code'])) {
                    echo "<div><strong>Subject:</strong> {$student['subject_code']}</div>";
                }
            } else {
                echo "Seat $right_seat: Empty";
            }
            echo "</div>";
            
            echo "</div>";
        }
    } else {
        // Fall back to regular desk display
        foreach($desk_assignments as $desk_no => $seats) {
            echo "<div class='desk'>";
            echo "<h5>Desk $desk_no</h5>";
            
            // Left seat
            $left_seat = $seats['left'];
            echo "<div class='seat" . (!isset($seat_matrix[$left_seat]) ? " empty" : "") . "' " . 
                 (isset($seat_matrix[$left_seat]) ? "style='background-color: " . $seat_matrix[$left_seat]['dept_color'] . "'" : "") . ">";
            if(isset($seat_matrix[$left_seat])) {
                $student = $seat_matrix[$left_seat];
                echo "<div><strong>Seat $left_seat:</strong> {$student['name']}</div>";
                echo "<div><strong>Dept/Year:</strong> {$student['dept']} / {$student['year']}</div>";
                echo "<div><strong>Roll:</strong> {$student['rollno']}</div>";
                if(!empty($student['subject_code'])) {
                    echo "<div><strong>Subject:</strong> {$student['subject_code']}</div>";
                }
            } else {
                echo "Seat $left_seat: Empty";
            }
            echo "</div>";
            
            // Right seat
            // ...similar code for right seat with color indication...
            $right_seat = $seats['right'];
            echo "<div class='seat" . (!isset($seat_matrix[$right_seat]) ? " empty" : "") . "' " . 
                 (isset($seat_matrix[$right_seat]) ? "style='background-color: " . $seat_matrix[$right_seat]['dept_color'] . "'" : "") . ">";
            if(isset($seat_matrix[$right_seat])) {
                $student = $seat_matrix[$right_seat];
                echo "<div><strong>Seat $right_seat:</strong> {$student['name']}</div>";
                echo "<div><strong>Dept/Year:</strong> {$student['dept']} / {$student['year']}</div>";
                echo "<div><strong>Roll:</strong> {$student['rollno']}</div>";
                if(!empty($student['subject_code'])) {
                    echo "<div><strong>Subject:</strong> {$student['subject_code']}</div>";
                }
            } else {
                echo "Seat $right_seat: Empty";
            }
            echo "</div>";
            
            echo "</div>";
        }
    }
    
    echo "</div>";
    
    // Button area remains the same
    echo "<div class='mt-3'>
          <a href='print_seating.php?room=$roomid&date=$exam_date&batch=$batch_time' class='btn btn-success mr-2' target='_blank'>
          <i class='fas fa-print'></i> Print Seating Plan</a>
          <a href='download_pdf.php?room=$roomid&date=$exam_date&batch=$batch_time' class='btn btn-danger'>
          <i class='fas fa-file-pdf'></i> Download as PDF</a>
          </div>";
    
    echo "</div>";
}
?>
