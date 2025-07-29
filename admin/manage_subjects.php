<?php
session_start();
include '../db.php';
include "../link.php";

// Handle Add Subject
if(isset($_POST['addsubject'])) {
    $subject_code = mysqli_real_escape_string($conn, $_POST['subject_code']);
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    
    $insert = "INSERT INTO subjects (subject_code, subject_name, class_id) VALUES ('$subject_code', '$subject_name', '$class_id')";
    
    if(mysqli_query($conn, $insert)) {
        $_SESSION['subject_success'] = "Subject added successfully";
    } else {
        $_SESSION['subject_error'] = "Error adding subject: " . mysqli_error($conn);
    }
}

// Handle Delete Subject
if(isset($_POST['delete_subject'])) {
    $subject_id = mysqli_real_escape_string($conn, $_POST['delete_subject']);
    
    $delete = "DELETE FROM subjects WHERE subject_id = '$subject_id'";
    
    if(mysqli_query($conn, $delete)) {
        $_SESSION['subject_success'] = "Subject deleted successfully";
    } else {
        $_SESSION['subject_error'] = "Error deleting subject: " . mysqli_error($conn);
    }
}
?>

<html>
<head>
    <title>Manage Subjects</title>
    <link rel="stylesheet" href="common.css">
    <?php include '../link.php'; ?>
    <style>
        .subject-form {
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
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
                <a href="manage_subjects.php" class="active_link"><img src="https://img.icons8.com/ios-filled/25/ffffff/book.png"/> Subjects</a>
            </li>
            <li>
                <a href="dashboard.php"><img src="https://img.icons8.com/nolan/30/ffffff/summary-list.png"/> Allotment</a>
            </li>
            <li>
                <a href="allocate_seats.php"><img src="https://img.icons8.com/ios-filled/30/ffffff/room.png"/> Seats Allocation</a>
            </li>
        </ul>
    </nav>
    
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button>
                <span class="page-name"> Manage Subjects</span>
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
            if(isset($_SESSION['subject_success'])) {
                echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>
                    " . $_SESSION['subject_success'] . "
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
                unset($_SESSION['subject_success']);
            }
            
            // Display error messages
            if(isset($_SESSION['subject_error'])) {
                echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>
                    " . $_SESSION['subject_error'] . "
                    <button type='button' class='close' data-dismiss='alert' aria-label='Close'>
                        <span aria-hidden='true'>&times;</span>
                    </button>
                </div>";
                unset($_SESSION['subject_error']);
            }
            ?>
            
            <!-- Add Subject Form -->
            <div class="card subject-form">
                <h5 class="card-header bg-primary text-white">Add New Subject</h5>
                <div class="card-body">
                    <form method="post" action="">
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label for="subject_code">Subject Code</label>
                                <input type="text" class="form-control" id="subject_code" name="subject_code" placeholder="e.g. CSE101" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="subject_name">Subject Name</label>
                                <input type="text" class="form-control" id="subject_name" name="subject_name" placeholder="e.g. Introduction to Programming" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label for="class_id">Class</label>
                                <select class="form-control" id="class_id" name="class_id" required>
                                    <option value="">--Select Class--</option>
                                    <?php
                                    $classes_query = mysqli_query($conn, "SELECT * FROM class ORDER BY year, dept, division");
                                    while($class = mysqli_fetch_assoc($classes_query)) {
                                        echo "<option value='" . $class['class_id'] . "'>" . 
                                             $class['year'] . " " . $class['dept'] . " " . $class['division'] . 
                                             "</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <button type="submit" name="addsubject" class="btn btn-primary">Add Subject</button>
                    </form>
                </div>
            </div>
            
            <!-- Subject List Table -->
            <div class="card mt-4">
                <h5 class="card-header bg-info text-white">Subject List</h5>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Class</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $subjects_query = "SELECT s.*, c.year, c.dept, c.division 
                                                 FROM subjects s
                                                 INNER JOIN class c ON s.class_id = c.class_id
                                                 ORDER BY s.subject_code";
                                $subjects_result = mysqli_query($conn, $subjects_query);
                                
                                if(mysqli_num_rows($subjects_result) > 0) {
                                    while($subject = mysqli_fetch_assoc($subjects_result)) {
                                        echo "<tr>";
                                        echo "<td>" . $subject['subject_code'] . "</td>";
                                        echo "<td>" . $subject['subject_name'] . "</td>";
                                        echo "<td>" . $subject['year'] . " " . $subject['dept'] . " " . $subject['division'] . "</td>";
                                        echo "<td>
                                            <form method='post' action='' class='d-inline' onsubmit='return confirm(\"Are you sure you want to delete this subject?\")'>
                                                <button type='submit' class='btn btn-sm btn-danger' name='delete_subject' value='" . $subject['subject_id'] . "'>
                                                    <img src='https://img.icons8.com/color/20/000000/delete-forever.png'/>
                                                </button>
                                            </form>
                                            <button type='button' class='btn btn-sm btn-primary edit-subject' 
                                                data-id='" . $subject['subject_id'] . "'
                                                data-code='" . $subject['subject_code'] . "'
                                                data-name='" . $subject['subject_name'] . "'
                                                data-class='" . $subject['class_id'] . "'>
                                                <img src='https://img.icons8.com/fluent/20/000000/edit.png'/>
                                            </button>
                                        </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='4' class='text-center'>No subjects found</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>

<script>
$(document).ready(function() {
    // Initialize tooltip
    $('[data-toggle="tooltip"]').tooltip();
    
    // Handle edit subject button click
    $('.edit-subject').click(function() {
        var subjectId = $(this).data('id');
        var subjectCode = $(this).data('code');
        var subjectName = $(this).data('name');
        var classId = $(this).data('class');
        
        // Populate the form fields
        $('#subject_code').val(subjectCode);
        $('#subject_name').val(subjectName);
        $('#class_id').val(classId);
        
        // Add hidden field for subject ID
        if ($('#subject_id').length === 0) {
            $('<input>').attr({
                type: 'hidden',
                id: 'subject_id',
                name: 'subject_id',
                value: subjectId
            }).appendTo('form');
            
            // Change button text
            $('button[name="addsubject"]').text('Update Subject').attr('name', 'updatesubject');
        } else {
            $('#subject_id').val(subjectId);
        }
        
        // Scroll to form
        $('html, body').animate({
            scrollTop: $(".subject-form").offset().top
        }, 500);
    });
});
</script>
</body>
</html>
