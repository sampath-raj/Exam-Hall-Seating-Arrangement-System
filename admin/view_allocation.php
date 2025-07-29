<?php
session_start();
include "../link.php";

// Check if exam_seats table exists
$tableExists = false;
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'exam_seats'");
if(mysqli_num_rows($check_table) > 0) {
    $tableExists = true;
}

// Verify required parameters
if(!isset($_GET['room']) || !isset($_GET['date']) || !isset($_GET['batch'])) {
    $_SESSION['message'] = "Missing required parameters.";
    header("Location: allocate_seats.php");
    exit;
}

$roomid = $_GET['room'];
$exam_date = $_GET['date'];
$batch_time = $_GET['batch'];
?>

<html>
<head>
    <title>View Seat Allocation</title>
    <link rel="stylesheet" href="common.css">
    <style>
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
            <li><a href="add_class.php"><img src="https://img.icons8.com/ios-filled/26/ffffff/google-classroom.png"/> Classes</a></li>
            <li><a href="add_student.php"><img src="https://img.icons8.com/ios-filled/25/ffffff/student-registration.png"/> Students</a></li>
            <li><a href="add_room.php"><img src="https://img.icons8.com/metro/25/ffffff/building.png"/> Rooms</a></li>
            <li><a href="manage_subjects.php"><img src="https://img.icons8.com/ios-filled/25/ffffff/book.png"/> Subjects</a></li>
            <li><a href="dashboard.php"><img src="https://img.icons8.com/nolan/30/ffffff/summary-list.png"/> Allotment</a></li>
            <li><a href="allocate_seats.php" class="active_link"><img src="https://img.icons8.com/ios-filled/30/ffffff/room.png"/> Seats Allocation</a></li>
        </ul>
    </nav>
    
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button><span class="page-name"> View Seat Allocation</span>
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
            if(!$tableExists) {
                echo "<div class='alert alert-danger'>Exam seats table does not exist. Please set up the system correctly.</div>";
                exit;
            }
            
            // Get room details
            $room_query = mysqli_query($conn, "SELECT room_no, floor FROM room WHERE rid = '$roomid'");
            $room = mysqli_fetch_assoc($room_query);
            
            if(!$room) {
                echo "<div class='alert alert-danger'>Room not found</div>";
                exit;
            }
            
            // Get exam details
            $exam_query = mysqli_query($conn, "SELECT b.*, sub.subject_code, sub.subject_name 
                                             FROM batch b
                                             LEFT JOIN subjects sub ON b.subject_id = sub.subject_id
                                             WHERE b.room_id = '$roomid' 
                                             AND b.date = '$exam_date' 
                                             AND b.batch_time = '$batch_time'
                                             LIMIT 1");
            $exam = mysqli_fetch_assoc($exam_query);
            
            if(!$exam) {
                echo "<div class='alert alert-danger'>No exam found for the selected criteria</div>";
                exit;
            }
            
            // Display exam and room info
            echo "<div class='card mb-4'>
                    <div class='card-header bg-info text-white'>
                        <h4>Exam Seating Plan</h4>
                    </div>
                    <div class='card-body'>
                        <div class='row'>
                            <div class='col-md-6'>
                                <p><strong>Room:</strong> {$room['room_no']} (Floor {$room['floor']})</p>
                                <p><strong>Date:</strong> " . date('d-m-Y (l)', strtotime($exam_date)) . "</p>
                            </div>
                            <div class='col-md-6'>
                                <p><strong>Session:</strong> " . ($batch_time == 'FN' ? 'Forenoon (9:30 AM - 12:30 PM)' : 'Afternoon (1:30 PM - 4:30 PM)') . "</p>
                                <p><strong>Subject:</strong> {$exam['subject_code']} - {$exam['subject_name']}</p>
                            </div>
                        </div>
                    </div>
                  </div>";
            
            // Fetch all seat allocations for this exam
            $seats_query = "SELECT es.*, s.name, s.rollno, c.dept, c.year, c.division 
                          FROM exam_seats es
                          JOIN students s ON es.student_id = s.student_id
                          JOIN class c ON s.class = c.class_id
                          WHERE es.room_id = '$roomid'
                          AND es.exam_date = '$exam_date'
                          AND es.batch_time = '$batch_time'
                          ORDER BY es.seat_no";
            $seats_result = mysqli_query($conn, $seats_query);
            
            if(mysqli_num_rows($seats_result) == 0) {
                echo "<div class='alert alert-warning'>No seats have been allocated for this exam yet.</div>";
                echo "<div class='mb-3'>
                      <a href='allocate_seats.php' class='btn btn-primary'>Allocate Seats</a>
                      </div>";
                exit;
            }
            
            // Build the seat matrix
            $seat_matrix = array();
            $desk_assignments = array();
            
            // Get room capacity to determine total number of seats
            $capacity_query = mysqli_query($conn, "SELECT capacity FROM room WHERE rid = '$roomid'");
            $capacity = mysqli_fetch_assoc($capacity_query)['capacity'];
            $total_desks = ceil($capacity / 2);
            
            // Create desk layout (1-based indexing)
            for($desk = 1; $desk <= $total_desks; $desk++) {
                $desk_assignments[$desk] = array(
                    'left' => ($desk * 2) - 1,  // Odd numbers
                    'right' => $desk * 2        // Even numbers
                );
            }
            
            // Populate seat matrix from database results
            while($seat = mysqli_fetch_assoc($seats_result)) {
                $seat_matrix[$seat['seat_no']] = array(
                    'name' => $seat['name'],
                    'dept' => $seat['dept'],
                    'year' => $seat['year'],
                    'rollno' => $seat['rollno'],
                    'division' => $seat['division'],
                    'desk_no' => ceil($seat['seat_no'] / 2)
                );
            }
            
            // Get department colors for visual distinction
            $dept_colors = array();
            $departments = array();
            
            // Extract unique departments from seat data
            foreach($seat_matrix as $seat_no => $data) {
                if(!in_array($data['dept'], $departments)) {
                    $departments[] = $data['dept'];
                }
            }
            
            // Assign colors to departments
            $colors = array('#ffcccc', '#ccffcc', '#ccccff', '#ffffcc', '#ffccff', '#ccffff');
            foreach ($departments as $i => $dept) {
                $dept_colors[$dept] = $colors[$i % count($colors)];
            }
            
            // Add department color legend
            echo "<div class='mb-3 p-2 border rounded bg-light'><h5>Department Color Legend</h5><div class='d-flex flex-wrap'>";
            foreach ($dept_colors as $dept => $color) {
                echo "<div class='mr-3 mb-1'><span class='px-2 py-1 rounded' style='background-color: $color'>$dept</span></div>";
            }
            echo "</div></div>";
            
            // Updated explanation to clarify the seating rule
            echo "<div class='alert alert-info'>
                    <strong>Seating Rule:</strong> Students from different departments can share the same desk, 
                    but students from the same department are not seated next to each other at the same desk
                    to maintain exam integrity. Different colors represent different departments.
                  </div>";
            
            // Display seat matrix as a visual seating plan
            echo "<div class='card'>
                    <div class='card-header'>
                        <h5>Seating Arrangement</h5>
                    </div>
                    <div class='card-body'>
                        <div class='seating-layout'>";
            
            // Display desk-wise arrangement with color coding by department
            foreach($desk_assignments as $desk_no => $seats) {
                echo "<div class='desk'>
                        <h5>Desk $desk_no</h5>";
                
                // Left seat with department color
                $left_seat = $seats['left'];
                $left_color = isset($seat_matrix[$left_seat]) ? $dept_colors[$seat_matrix[$left_seat]['dept']] : '';
                echo "<div class='seat" . (!isset($seat_matrix[$left_seat]) ? " empty" : "") . "'" . 
                     (!empty($left_color) ? " style='background-color: $left_color'" : "") . ">";
                if(isset($seat_matrix[$left_seat])) {
                    $student = $seat_matrix[$left_seat];
                    echo "<div><strong>Seat $left_seat:</strong> {$student['name']}</div>
                          <div><strong>Dept/Year:</strong> {$student['dept']} / {$student['year']}</div>
                          <div><strong>Roll:</strong> {$student['rollno']}</div>";
                } else {
                    echo "Seat $left_seat: Empty";
                }
                echo "</div>";
                
                // Right seat with department color  
                $right_seat = $seats['right'];
                $right_color = isset($seat_matrix[$right_seat]) ? $dept_colors[$seat_matrix[$right_seat]['dept']] : '';
                echo "<div class='seat" . (!isset($seat_matrix[$right_seat]) ? " empty" : "") . "'" . 
                     (!empty($right_color) ? " style='background-color: $right_color'" : "") . ">";
                if(isset($seat_matrix[$right_seat])) {
                    $student = $seat_matrix[$right_seat];
                    echo "<div><strong>Seat $right_seat:</strong> {$student['name']}</div>
                          <div><strong>Dept/Year:</strong> {$student['dept']} / {$student['year']}</div>
                          <div><strong>Roll:</strong> {$student['rollno']}</div>";
                } else {
                    echo "Seat $right_seat: Empty";
                }
                echo "</div>";
                
                echo "</div>";
            }
            
            echo "</div>
                </div>
                <div class='card-footer'>
                    <a href='allocate_seats.php' class='btn btn-secondary'>Back</a>
                    <a href='print_seating.php?room=$roomid&date=$exam_date&batch=$batch_time' class='btn btn-success' target='_blank'>
                    <i class='fas fa-print'></i> Print Seating Plan</a>
                    <a href='download_pdf.php?room=$roomid&date=$exam_date&batch=$batch_time' class='btn btn-danger'>
                    <i class='fas fa-file-pdf'></i> Download as PDF</a>
                </div>
            </div>";
            ?>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>
</body>
</html>