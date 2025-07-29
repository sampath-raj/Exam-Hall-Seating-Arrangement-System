<?php 
session_start();
?>
<html>
<head>
    <title>Manage Student</title>
    <link rel="stylesheet" href="common.css">
    <?php include'../link.php' ?>
    <style type="text/css">
        .password-cell {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .action-buttons {
            white-space: nowrap;
        }
        
        .password-toggle {
            cursor: pointer;
            margin-left: 5px;
        }
    </style>
    </head>
<body>
    <?php
    if(isset($_POST['deletestudent'])){
        $student = $_POST['deletestudent'];
        $delete = "delete from students where student_id = '$student'";
        $delete_query = mysqli_query($conn, $delete);
        if($delete_query){
            $_SESSION['delstudent'] = "Student deleted successfully";
        }
        else{
            $_SESSION['delnotstudent'] = "Error!! student not deleted.";
        }
    }
    
    // Handle password reset form submission
    if(isset($_POST['reset_password'])) {
        $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
        $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
        
        $update_query = mysqli_query($conn, "UPDATE students SET password='$new_password' WHERE student_id='$student_id'");
        
        if($update_query) {
            $_SESSION['student'] = "Password reset successfully";
        } else {
            $_SESSION['studentnot'] = "Error resetting password: " . mysqli_error($conn);
        }
    }
?>
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
                        <a href="add_student.php" class="active_link"><img src="https://img.icons8.com/ios-filled/25/ffffff/student-registration.png"/> Students</a>
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
                </ul>
            </nav>
<div id="content">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <button type="button" id="sidebarCollapse" class="btn btn-info">
                <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
            </button><span class="page-name"> Manage Students</span>
            <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <img src="https://img.icons8.com/ios-filled/20/ffffff/menu--v3.png"/>
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
        if(isset($_SESSION['student'])){
            echo "<div class='alert alert-success alert-dismissible fade show' role='alert'>".$_SESSION['student']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
            unset($_SESSION['student']);
        }
        if(isset($_SESSION['studentnot'])){
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['studentnot']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
            unset($_SESSION['studentnot']);
        }
        if(isset($_SESSION['delstudent'])){
            echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['delstudent']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
            unset($_SESSION['delstudent']);
        }
        if(isset($_SESSION['delnotstudent'])){
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['delnotstudent']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
            unset($_SESSION['delnotstudent']);
        }
        ?>
        <div class="table-responsive border">
            <table class="table table-hover text-center">
               <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Class</th>
                    <th>RollNo.</th>
                    <th>Password</th>
                    <th>Actions</th>
                </tr>   
                </thead>
                <tbody>
                <tr>
                <form action="addstudent.php" method="post">
                    <th class="py-3 bg-light">
                        <input class="form-control" type="text" name="sname">
                    </th>
                    <th class="py-3 bg-light">
                        <select id="sem" name="sclass" class="form-control">
                    <?php 
                    $selectclass = "select * from class order by year, dept, division";
                    $selectclassQuery = mysqli_query($conn, $selectclass);
                    if($selectclassQuery){
                        echo "<option value=''>--select--</option>";
                        while($row = mysqli_fetch_assoc($selectclassQuery)){
                            echo "<option value=".$row['class_id'].">".$row['year']." ".$row['dept']." ".$row['division']."</option>";
                        }
                    }
                    else{
                        echo "<option value=''>No options</option>";
                    }
                    ?>
                    </select>
                    </th>
                    <th class="py-3 bg-light">
                        <input class="form-control" type="number" name="sroll" size=4>
                    </th>
                    <th class="py-3 bg-light">
                        <input class="form-control" type="Password" name="spwd">
                    </th>
                    <th class="py-3 bg-light">
                        <button class="btn btn-primary" name="addstudent">Add</button>
                    </th>
                </tr>  
            </form>
                <?php
                $selectclass = "Select * from students,class where students.class=class.class_id order by year, dept, division, rollno";
                $selectclassquery = mysqli_query($conn, $selectclass);
                if($selectclassquery){
                    while ($row = mysqli_fetch_assoc($selectclassquery)) {
                        echo "<tr>
                        <td>{$row['name']}</td>
                        <td>{$row['year']} {$row['dept']} {$row['division']}</td>
                        <td>{$row['rollno']}</td>
                        <td class='password-cell'>
                            <span class='password-value'>{$row['password']}</span>
                            <i class='fas fa-eye password-toggle' title='Show/Hide Password'></i>
                        </td>
                        <td class='action-buttons'>
                            <form method='post' class='d-inline'>
                                <button class='btn btn-light px-1 py-0' type='submit' value='{$row['student_id']}' name='deletestudent'>
                                <img src='https://img.icons8.com/color/25/000000/delete-forever.png'/>
                                </button>
                            </form>
                            <button class='btn btn-warning btn-sm ml-1' data-toggle='modal' data-target='#resetPasswordModal' 
                                data-studentid='{$row['student_id']}' data-studentname='{$row['name']}'>
                                <i class='fas fa-key'></i> Reset
                            </button>
                        </td>
                    </tr>";
                    }
                }
                ?>
                </tbody>
        </table>
    </div>
</div>
</div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resetPasswordModalLabel">Reset Student Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"></button></button>
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="post">
                <div class="modal-body"></div>
                    <p>You are about to reset the password for: <strong id="studentNameDisplay"></strong></p>
                    <input type="hidden" name="student_id" id="studentIdField" value="">
                    
                    <div class="form-group"></div>
                        <label for="new_password">New Password:</label>
                        <div class="input-group"></div>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <div class="input-group-append"></div>
                                <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword"></button>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <button type="button" class="btn btn-sm btn-info" id="generatePassword">Generate Password</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" name="reset_password">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include'footer.php' ?>

<script>
$(document).ready(function() {
    // Show/hide password
    $('.password-toggle').click(function() {
        var passwordCell = $(this).closest('.password-cell');
        var passwordValue = passwordCell.find('.password-value');
        
        if (passwordValue.hasClass('text-muted')) {
            passwordValue.removeClass('text-muted');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        } else {
            passwordValue.addClass('text-muted');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        }
    });
    
    // Reset password modal
    $('#resetPasswordModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var studentId = button.data('studentid');
        var studentName = button.data('studentname');
        
        var modal = $(this);
        modal.find('#studentNameDisplay').text(studentName);
        modal.find('#studentIdField').val(studentId);
    });
    
    // Toggle password visibility in modal
    $('#toggleNewPassword').click(function() {
        var passwordField = $('#new_password');
        var fieldType = passwordField.attr('type');
        
        if (fieldType === 'password') {
            passwordField.attr('type', 'text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    
    // Generate random password
    $('#generatePassword').click(function() {
        var chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*';
        var passwordLength = 8;
        var password = '';
        
        for (var i = 0; i < passwordLength; i++) {
            var randomNumber = Math.floor(Math.random() * chars.length);
            password += chars.substring(randomNumber, randomNumber + 1);
        }
        
        $('#new_password').val(password);
        // Show the generated password
        $('#new_password').attr('type', 'text');
        $('#toggleNewPassword').find('i').removeClass('fa-eye').addClass('fa-eye-slash');
    });
});
</script>