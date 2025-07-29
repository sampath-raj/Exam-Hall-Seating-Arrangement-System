<?php
session_start();
include "../link.php";

// Process form submission for seat assignment
if(isset($_POST['assign_seat'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    $room_id = mysqli_real_escape_string($conn, $_POST['room_id']);
    $seat_no = mysqli_real_escape_string($conn, $_POST['seat_no']);
    
    // Check if seat is already assigned to another student
    $check_query = mysqli_query($conn, "SELECT student_id FROM students WHERE hall_no='$room_id' AND seat_no='$seat_no' AND student_id != '$student_id'");
    
    if(mysqli_num_rows($check_query) > 0) {
        $_SESSION['error_message'] = "This seat is already assigned to another student.";
    } else {
        // Update the student's seat assignment
        $update_query = mysqli_query($conn, "UPDATE students SET hall_no='$room_id', seat_no='$seat_no' WHERE student_id='$student_id'");
        
        if($update_query) {
            $_SESSION['success_message'] = "Seat assigned successfully.";
        } else {
            $_SESSION['error_message'] = "Error assigning seat: " . mysqli_error($conn);
        }
    }
}

// Process seat clearing
if(isset($_POST['clear_seat'])) {
    $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
    
    $update_query = mysqli_query($conn, "UPDATE students SET hall_no=NULL, seat_no=NULL WHERE student_id='$student_id'");
    
    if($update_query) {
        $_SESSION['success_message'] = "Seat assignment cleared successfully.";
    } else {
        $_SESSION['error_message'] = "Error clearing seat assignment: " . mysqli_error($conn);
    }
}

// Get filter values
$filter_class = isset($_GET['class']) ? $_GET['class'] : '';
$filter_name = isset($_GET['name']) ? $_GET['name'] : '';
$filter_rollno = isset($_GET['rollno']) ? $_GET['rollno'] : '';
?>

<html>
<head>
    <title>Manual Seat Assignment</title>
    <link rel="stylesheet" href="common.css">
    <?php include '../link.php'; ?>
    <style>
        .student-filter {
            margin-bottom: 20px;
        }
        .seat-form {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .assigned {
            background-color: #d4edda;
        }
        .not-assigned {
            background-color: #f8d7da;
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
                <a href="allocate_seats.php"><img src="https://img.icons8.com/ios-filled/30/ffffff/room.png"/> Seats Allocation</a>
            </li>
            <li>
                <a href="manual_seat_assign.php" class="active_link"><img src="https://img.icons8.com/ios-filled/25/ffffff/chair.png"/> Manual Seat Assignment</a>
            </li>
        </ul>
    </nav>
    
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button>
                <span class="page-name"> Manual Seat Assignment</span>
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
            // Display success messages
            if(isset($_SESSION['success_message'])) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    " . $_SESSION['success_message'] . "
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
                unset($_SESSION['success_message']);
            }
            
            // Display error messages
            if(isset($_SESSION['error_message'])) {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    " . $_SESSION['error_message'] . "
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
                unset($_SESSION['error_message']);
            }
            ?>
            
            <h4>Manual Seat Assignment</h4>
            <p>Use this page to manually assign or change seat numbers for students.</p>
            
            <!-- Student Filter Form -->
            <div class="card student-filter">
                <div class="card-body">
                    <form method="get" class="form-inline">
                        <div class="form-group mr-2">
                            <label for="class" class="mr-2">Class:</label>
                            <select name="class" id="class" class="form-control">
                                <option value="">All Classes</option>
                                <?php
                                $classes_query = mysqli_query($conn, "SELECT * FROM class ORDER BY year, dept, division");
                                while($class = mysqli_fetch_assoc($classes_query)) {
                                    $selected = ($filter_class == $class['class_id']) ? 'selected' : '';
                                    echo "<option value='{$class['class_id']}' $selected>{$class['year']} {$class['dept']} {$class['division']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label for="name" class="mr-2">Name:</label>
                            <input type="text" name="name" id="name" class="form-control" value="<?php echo $filter_name; ?>" placeholder="Student name">
                        </div>
                        <div class="form-group mr-2">
                            <label for="rollno" class="mr-2">Roll No:</label>
                            <input type="text" name="rollno" id="rollno" class="form-control" value="<?php echo $filter_rollno; ?>" placeholder="Roll number">
                        </div>
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="manual_seat_assign.php" class="btn btn-secondary ml-2">Reset</a>
                    </form>
                </div>
            </div>
            
            <!-- Student List -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    Student List
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Class</th>
                                    <th>Roll No</th>
                                    <th>Current Assignment</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Build where clause based on filters
                                $where_clauses = array();
                                if($filter_class) {
                                    $where_clauses[] = "s.class = '$filter_class'";
                                }
                                if($filter_name) {
                                    $where_clauses[] = "s.name LIKE '%$filter_name%'";
                                }
                                if($filter_rollno) {
                                    $where_clauses[] = "s.rollno = '$filter_rollno'";
                                }
                                
                                $where_clause = !empty($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";
                                
                                $students_query = "SELECT s.*, c.year, c.dept, c.division, r.room_no, r.floor
                                                 FROM students s
                                                 INNER JOIN class c ON s.class = c.class_id
                                                 LEFT JOIN room r ON s.hall_no = r.rid
                                                 $where_clause
                                                 ORDER BY c.year, c.dept, c.division, s.rollno
                                                 LIMIT 100";
                                
                                $students_result = mysqli_query($conn, $students_query);
                                
                                if(mysqli_num_rows($students_result) > 0) {
                                    while($student = mysqli_fetch_assoc($students_result)) {
                                        $row_class = $student['seat_no'] ? 'assigned' : 'not-assigned';
                                        
                                        echo "<tr class='$row_class'>
                                                <td>{$student['name']}</td>
                                                <td>{$student['year']} {$student['dept']} {$student['division']}</td>
                                                <td>{$student['rollno']}</td>
                                                <td>";
                                        
                                        if($student['seat_no']) {
                                            echo "Room: {$student['room_no']} (Floor {$student['floor']}), Seat: {$student['seat_no']}";
                                        } else {
                                            echo "<span class='text-danger'>Not assigned</span>";
                                        }
                                        
                                        echo "</td>
                                                <td>
                                                    <button type='button' class='btn btn-sm btn-primary edit-seat'
                                                        data-id='{$student['student_id']}'
                                                        data-name='{$student['name']}'
                                                        data-class='{$student['year']} {$student['dept']} {$student['division']}'
                                                        data-room='{$student['hall_no']}'
                                                        data-seat='{$student['seat_no']}'>
                                                        <i class='fas fa-edit'></i> Assign Seat
                                                    </button>
                                                </td>
                                              </tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='5' class='text-center'>No students found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div