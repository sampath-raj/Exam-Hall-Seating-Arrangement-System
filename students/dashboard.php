<?php
session_start();
include '../link.php';

// Check if exam_seats table exists
$tableExists = false;
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'exam_seats'");
if(mysqli_num_rows($check_table) > 0) {
    $tableExists = true;
}

// If table doesn't exist, create it
if(!$tableExists) {
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
    
    mysqli_query($conn, $create_table);
    // Check again if table was created
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'exam_seats'");
    if(mysqli_num_rows($check_table) > 0) {
        $tableExists = true;
    }
}
?>
<html>
<head>
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="../admin/common.css">
    <style>
        .seat-info {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .student-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .exam-details {
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .status-allocated {
            color: #28a745;
            font-weight: bold;
        }
        .status-pending {
            color: #dc3545;
            font-weight: bold;
        }
        .exam-card {
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .exam-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            background-color: #e9ecef;
        }
        .seat-details {
            display: none; /* Initially hidden */
            margin-top: 15px;
            background-color: #f0f8ff;
            border-radius: 5px;
            padding: 15px;
            border-left: 4px solid #007bff;
        }
        .view-seat-btn {
            margin-top: 10px;
        }
        .nav-icons {
            font-size: 18px;
            margin-right: 5px;
        }
        
        .user-menu {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 10px 15px;
            margin-bottom: 20px;
        }
        
        .user-menu .dropdown-menu {
            min-width: 200px;
        }
        
        .user-menu .dropdown-item {
            padding: 8px 15px;
        }
        
        .user-menu .dropdown-item i {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <span class="page-name"> DASHBOARD</span>
                <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                   <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user-circle nav-icons"></i> Account
                            </a>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="change_password.php"><i class="fas fa-key"></i> Change Password</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="main-content container py-4">
<?php
    if(isset($_SESSION['loginid'])){
        $id = $_SESSION['loginid'];
        
        // First, get student details
        $student_query = "SELECT s.*, c.year, c.dept, c.division 
                         FROM students s 
                         INNER JOIN class c ON s.class = c.class_id
                         WHERE s.student_id = '$id'";
        
        $student_result = mysqli_query($conn, $student_query);
        
        if($student = mysqli_fetch_assoc($student_result)) {
            // Display Student Details Section
            echo "<div class='student-details'>
                    <h4>{$student['name']}</h4>
                    <p>Class: {$student['year']} {$student['dept']} {$student['division']}</p>
                    <p>Roll No: {$student['rollno']}</p>
                  </div>";
            
            // Now get all exams for this student's class
            $exams_query = "SELECT b.*, r.room_no, r.floor, sub.subject_code, sub.subject_name
                          FROM batch b
                          INNER JOIN room r ON b.room_id = r.rid
                          LEFT JOIN subjects sub ON b.subject_id = sub.subject_id
                          WHERE b.class_id = '{$student['class']}'
                          AND {$student['rollno']} BETWEEN b.startno AND b.endno
                          ORDER BY b.date ASC, FIELD(b.batch_time, 'FN', 'AF')";
            
            $exams_result = mysqli_query($conn, $exams_query);
            
            echo "<div class='seat-info'>";
            echo "<h5>My Examinations</h5>";
            
            if(mysqli_num_rows($exams_result) > 0) {
                // Create tabs for upcoming and past exams
                echo "<ul class='nav nav-tabs mb-3' id='examTabs' role='tablist'>
                        <li class='nav-item'>
                            <a class='nav-link active' id='upcoming-tab' data-toggle='tab' href='#upcoming' role='tab'>Upcoming Exams</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' id='past-tab' data-toggle='tab' href='#past' role='tab'>Past Exams</a>
                        </li>
                      </ul>";
                
                echo "<div class='tab-content' id='examTabsContent'>";
                echo "<div class='tab-pane fade show active' id='upcoming' role='tabpanel'>";
                
                $today = date('Y-m-d');
                $hasUpcoming = false;
                $hasPast = false;
                
                // Reset result pointer
                mysqli_data_seek($exams_result, 0);
                
                // Process upcoming exams
                while($exam = mysqli_fetch_assoc($exams_result)) {
                    if($exam['date'] >= $today) {
                        $hasUpcoming = true;
                        displayExam($exam, $student, $conn, $tableExists);
                    }
                }
                
                if(!$hasUpcoming) {
                    echo "<div class='alert alert-info'>No upcoming exams scheduled.</div>";
                }
                
                echo "</div>"; // End upcoming tab
                
                // Past exams tab
                echo "<div class='tab-pane fade' id='past' role='tabpanel'>";
                
                // Reset result pointer
                mysqli_data_seek($exams_result, 0);
                
                // Process past exams
                while($exam = mysqli_fetch_assoc($exams_result)) {
                    if($exam['date'] < $today) {
                        $hasPast = true;
                        displayExam($exam, $student, $conn, $tableExists);
                    }
                }
                
                if(!$hasPast) {
                    echo "<div class='alert alert-info'>No past exam records.</div>";
                }
                
                echo "</div>"; // End past tab
                echo "</div>"; // End tab content
                
            } else {
                echo "<div class='alert alert-warning'>No exams have been scheduled for you yet.</div>";
            }
            echo "</div>"; // End seat-info div
        }
    }
    
    // Function to display exam details with exam-specific seat information
    function displayExam($exam, $student, $conn, $tableExists) {
        $seat_info = null;
        
        if($tableExists) {
            // Get the specific seat for this exam date and batch from exam_seats table
            $seat_query = "SELECT es.*, r.room_no, r.floor 
                          FROM exam_seats es
                          JOIN room r ON es.room_id = r.rid
                          WHERE es.student_id = '{$student['student_id']}'
                          AND es.exam_date = '{$exam['date']}'
                          AND es.batch_time = '{$exam['batch_time']}'";
            
            $seat_result = mysqli_query($conn, $seat_query);
            $seat_info = mysqli_fetch_assoc($seat_result);
        }
        
        // If we couldn't find exam-specific seating, display a message that seats aren't allocated yet
        
        $batch_time = $exam['batch_time'] == 'FN' ? 'Forenoon (9:30 AM - 12:30 PM)' : 'Afternoon (1:30 PM - 4:30 PM)';
        $exam_date = date('l, F j, Y', strtotime($exam['date']));
        $exam_id = $exam['batch_id']; // Using batch_id as a unique identifier
        
        echo "<div class='card exam-card'>";
        echo "<div class='card-header exam-header d-flex justify-content-between align-items-center'>
                <div><strong>{$exam['subject_code']} - {$exam['subject_name']}</strong></div>
                <div class='text-muted'>{$exam_date}</div>
              </div>";
        echo "<div class='card-body'>";
        
        echo "<div class='row'>";
        echo "<div class='col-md-6'>
                <p><strong>Date:</strong> {$exam_date}</p>
                <p><strong>Time:</strong> {$batch_time}</p>
                <p><strong>Exam Room:</strong> {$exam['room_no']} (Floor {$exam['floor']})</p>
              </div>";
        
        echo "<div class='col-md-6'>";
        if($seat_info) {
            echo "<p class='status-allocated'>✓ Seat Allocated</p>";
            echo "<button class='btn btn-sm btn-info view-seat-btn' onclick='toggleSeatDetails(\"seat-{$exam_id}\")'>View Seat Details</button>";
        } else {
            echo "<p class='status-pending'>⚠ Seat Not Yet Allocated</p>";
            echo "<p>Please check back later for your seat assignment.</p>";
        }
        echo "</div>"; // End col-md-6
        echo "</div>"; // End row
        
        // Seat details section - initially hidden
        if($seat_info) {
            echo "<div class='seat-details' id='seat-{$exam_id}'>
                    <h5>Your Seat Information</h5>
                    <div class='row'>
                        <div class='col-md-6'>
                            <p><strong>Room Number:</strong> {$seat_info['room_no']}</p>
                            <p><strong>Floor:</strong> {$seat_info['floor']}</p>
                        </div>
                        <div class='col-md-6'>
                            <p><strong>Seat Number:</strong> {$seat_info['seat_no']}</p>
                        </div>
                    </div>
                    <div class='alert alert-info mt-2' role='alert'>
                        <small>Please arrive 30 minutes before the examination time with your ID card.</small>
                    </div>
                 </div>";
        }
        
        echo "</div>"; // End card-body
        echo "</div>"; // End card
    }
?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
        function toggleSeatDetails(seatId) {
            const seatElement = document.getElementById(seatId);
            if (seatElement.style.display === 'block') {
                seatElement.style.display = 'none';
            } else {
                seatElement.style.display = 'block';
            }
        }
    </script>
</body>
</html>