<?php
session_start();
include "../link.php";

// Verify required parameters
if (!isset($_GET['room']) || !isset($_GET['date']) || !isset($_GET['batch'])) {
    echo "<div class='alert alert-danger'>Missing required parameters</div>";
    exit;
}

$roomid = $_GET['room'];
$exam_date = $_GET['date'];
$batch_time = $_GET['batch'];

// Check if exam_seats table exists
$tableExists = false;
$check_table = mysqli_query($conn, "SHOW TABLES LIKE 'exam_seats'");
if(mysqli_num_rows($check_table) > 0) {
    $tableExists = true;
} else {
    echo "<div class='alert alert-danger'>Exam seats table does not exist. Please set up the system correctly.</div>";
    exit;
}

// Get room details
$room_query = mysqli_query($conn, "SELECT room_no, floor, capacity FROM room WHERE rid = '$roomid'");
$room = mysqli_fetch_assoc($room_query);

if (!$room) {
    echo "<div class='alert alert-danger'>Room not found</div>";
    exit;
}

// Get students assigned to this room for the given date and batch using exam_seats table
if($tableExists) {
    $students_query = "SELECT es.*, s.name, s.rollno, 
                      c.year, c.dept, c.division,
                      sub.subject_code, sub.subject_name
                      FROM exam_seats es
                      INNER JOIN students s ON es.student_id = s.student_id
                      INNER JOIN class c ON s.class = c.class_id
                      LEFT JOIN subjects sub ON es.subject_id = sub.subject_id
                      WHERE es.room_id = '$roomid'
                      AND es.exam_date = '$exam_date'
                      AND es.batch_time = '$batch_time'
                      ORDER BY es.seat_no";
} else {
    // Legacy fallback (should not be reached due to exit above)
    $students_query = "SELECT s.*, c.year, c.dept, c.division, 
                      sub.subject_code, sub.subject_name
                      FROM students s
                      INNER JOIN class c ON s.class = c.class_id
                      LEFT JOIN batch b ON b.class_id = c.class_id
                      LEFT JOIN subjects sub ON b.subject_id = sub.subject_id
                      WHERE s.hall_no = '$roomid'
                      AND b.date = '$exam_date'
                      AND b.batch_time = '$batch_time'
                      AND s.rollno BETWEEN b.startno AND b.endno
                      ORDER BY s.seat_no";
}

$students_result = mysqli_query($conn, $students_query);

// Get exam details
$exam_query = mysqli_query($conn, "SELECT b.*, sub.subject_code, sub.subject_name 
                                  FROM batch b 
                                  LEFT JOIN subjects sub ON b.subject_id = sub.subject_id
                                  WHERE b.room_id = '$roomid' 
                                  AND b.date = '$exam_date' 
                                  AND b.batch_time = '$batch_time'
                                  LIMIT 1");
$exam = mysqli_fetch_assoc($exam_query);

if (!$exam) {
    echo "<div class='alert alert-danger'>No exam scheduled for this room, date and batch</div>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seating Plan - Room <?php echo $room['room_no']; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .seating-info {
            margin-bottom: 20px;
        }
        .student-row {
            margin-bottom: 5px;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .student-row:nth-child(even) {
            background-color: #f9f9f9;
        }
        .desk-layout {
            margin-top: 30px;
        }
        .desk {
            border: 1px solid #ccc;
            padding: 10px;
            margin: 10px;
            display: inline-block;
            width: 300px;
            text-align: center;
        }
        .seat {
            display: inline-block;
            margin: 5px;
            padding: 10px;
            width: 48%;
            background-color: #f0f0f0;
            border-radius: 5px;
            text-align: left;
            vertical-align: top;
            height: 120px;
        }
        @media print {
            .no-print {
                display: none;
            }
            .page-break {
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="header">
            <h2>Exam Seating Arrangement</h2>
            <h4>Room <?php echo $room['room_no']; ?> - Floor <?php echo $room['floor']; ?></h4>
        </div>
        
        <div class="seating-info">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Date:</strong> <?php echo date('d-m-Y (l)', strtotime($exam_date)); ?></p>
                    <p><strong>Session:</strong> <?php echo ($batch_time == 'FN') ? 'Forenoon (9:30 AM - 12:30 PM)' : 'Afternoon (1:30 PM - 4:30 PM)'; ?></p>
                    <p><strong>Subject:</strong> <?php echo $exam['subject_code'] ? "{$exam['subject_code']} - {$exam['subject_name']}" : "Not specified"; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Room Capacity:</strong> <?php echo $room['capacity']; ?> seats</p>
                    <p><strong>Total Students:</strong> <?php echo mysqli_num_rows($students_result); ?></p>
                </div>
            </div>
        </div>

        <?php
        // Get seat matrix
        mysqli_data_seek($students_result, 0);
        $seat_matrix = array();
        while ($student = mysqli_fetch_assoc($students_result)) {
            $seat_matrix[$student['seat_no']] = $student;
        }
        
        // Get unique departments and assign colors
        $departments = array();
        foreach ($seat_matrix as $seat_no => $student) {
            if (!in_array($student['dept'], $departments)) {
                $departments[] = $student['dept'];
            }
        }
        $colors = array('#ffcccc', '#ccffcc', '#ccccff', '#ffffcc', '#ffccff', '#ccffff');
        $dept_colors = array();
        foreach ($departments as $i => $dept) {
            $dept_colors[$dept] = $colors[$i % count($colors)];
        }
        
        // Display department color legend
        echo "<div class='mb-3 p-2 border rounded bg-light no-print'>";
        echo "<h5>Department Color Legend</h5>";
        echo "<div class='d-flex flex-wrap'>";
        foreach ($dept_colors as $dept => $color) {
            echo "<div class='mr-3 mb-1'><span class='px-2 py-1 rounded' style='background-color: $color'>$dept</span></div>";
        }
        echo "</div>";
        // Updated explanation for clarity
        echo "<div class='mt-2'><strong>Seating Rule:</strong> Students from different departments can share the same desk, but students from the same department are not seated next to each other at the same desk.</div>";
        echo "</div>";
        ?>

        <button class="btn btn-primary no-print mb-3" onclick="window.print();">Print Seating Plan</button>
        <a href="download_pdf.php?room=<?php echo $roomid; ?>&date=<?php echo $exam_date; ?>&batch=<?php echo $batch_time; ?>" class="btn btn-danger no-print mb-3 ml-2">
            <i class="fas fa-file-pdf"></i> Download as PDF
        </a>
        <button class="btn btn-secondary no-print mb-3 ml-2" onclick="window.location='allocate_seats.php'">Back to Allocation</button>
        
        <h4>Student List</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Seat No</th>
                        <th>Student Name</th>
                        <th>Roll No</th>
                        <th>Class</th>
                        <th>Subject</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Reset the pointer to the beginning
                    mysqli_data_seek($students_result, 0);
                    
                    while ($student = mysqli_fetch_assoc($students_result)) {
                        echo "<tr>";
                        echo "<td>{$student['seat_no']}</td>";
                        echo "<td>{$student['name']}</td>";
                        echo "<td>{$student['rollno']}</td>";
                        echo "<td>{$student['year']} {$student['dept']} {$student['division']}</td>";
                        echo "<td>" . ($student['subject_code'] ? "{$student['subject_code']} - {$student['subject_name']}" : "Not specified") . "</td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div class="page-break"></div>
        
        <h4 class="mt-4">Room Layout</h4>
        <div class="desk-layout">
            <?php
            // Display desks with seats
            $total_seats = $room['capacity'];
            $total_desks = ceil($total_seats / 2);
            
            for ($desk = 1; $desk <= $total_desks; $desk++) {
                echo "<div class='desk'>";
                echo "<h5>Desk $desk</h5>";
                
                // Left seat (odd numbers)
                $left_seat = ($desk * 2) - 1;
                $left_color = isset($seat_matrix[$left_seat]) ? $dept_colors[$seat_matrix[$left_seat]['dept']] : '';
                echo "<div class='seat' " . (!empty($left_color) ? "style='background-color: $left_color'" : "") . ">";
                if (isset($seat_matrix[$left_seat])) {
                    $student = $seat_matrix[$left_seat];
                    echo "<strong>Seat $left_seat</strong><br>";
                    echo "{$student['name']}<br>";
                    echo "Roll No: {$student['rollno']}<br>";
                    echo "{$student['year']} {$student['dept']} {$student['division']}";
                } else {
                    echo "<strong>Seat $left_seat</strong><br>Empty";
                }
                echo "</div>";
                
                // Right seat (even numbers)
                $right_seat = $desk * 2;
                $right_color = isset($seat_matrix[$right_seat]) ? $dept_colors[$seat_matrix[$right_seat]['dept']] : '';
                echo "<div class='seat' " . (!empty($right_color) ? "style='background-color: $right_color'" : "") . ">";
                if (isset($seat_matrix[$right_seat])) {
                    $student = $seat_matrix[$right_seat];
                    echo "<strong>Seat $right_seat</strong><br>";
                    echo "{$student['name']}<br>";
                    echo "Roll No: {$student['rollno']}<br>";
                    echo "{$student['year']} {$student['dept']} {$student['division']}";
                } else {
                    echo "<strong>Seat $right_seat</strong><br>Empty";
                }
                echo "</div>";
                
                echo "</div>";
            }
            ?>
        </div>
        
        <div class="mt-4">
            <p><strong>Room Supervisor:</strong> _________________________</p>
            <p><strong>Signature:</strong> _________________________</p>
        </div>
    </div>
</body>
</html>
