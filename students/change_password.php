<?php
session_start();
include '../link.php';

// Check if user is logged in
if (!isset($_SESSION['loginid'])) {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['loginid'];
$success_message = '';
$error_message = '';

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_password = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    // Validate form data
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($new_password != $confirm_password) {
        $error_message = "New passwords do not match.";
    } elseif ($new_password == $current_password) {
        $error_message = "New password must be different from current password.";
    } elseif (strlen($new_password) < 4) {
        $error_message = "New password must be at least 4 characters long.";
    } else {
        // Verify current password
        $verify_query = mysqli_query($conn, "SELECT password FROM students WHERE student_id='$student_id'");
        $student_data = mysqli_fetch_assoc($verify_query);
        
        if ($student_data['password'] != $current_password) {
            $error_message = "Current password is incorrect.";
        } else {
            // Update password
            $update_query = mysqli_query($conn, "UPDATE students SET password='$new_password' WHERE student_id='$student_id'");
            
            if ($update_query) {
                $success_message = "Password changed successfully!";
            } else {
                $error_message = "Error updating password: " . mysqli_error($conn);
            }
        }
    }
}

// Get student details
$student_query = mysqli_query($conn, "SELECT s.*, c.year, c.dept, c.division 
                               FROM students s 
                               INNER JOIN class c ON s.class = c.class_id
                               WHERE s.student_id = '$student_id'");
$student = mysqli_fetch_assoc($student_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Password</title>
    <link rel="stylesheet" href="../admin/common.css">
    <?php include '../link.php'; ?>
    <style>
        .password-form {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .student-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .password-field {
            position: relative;
        }
        
        .toggle-password {
            position: absolute;
            right: 10px;
            top: 10px;
            cursor: pointer;
        }
        
        .password-requirements {
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <span class="page-name">Change Password</span>
                <button class="btn btn-dark d-inline-block d-lg-none ml-auto" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="nav navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">Dashboard</a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link" href="../logout.php">Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <div class="main-content container py-4">
            <div class="password-form">
                <div class="student-info">
                    <h4><?php echo $student['name']; ?></h4>
                    <p>Class: <?php echo $student['year'] . ' ' . $student['dept'] . ' ' . $student['division']; ?></p>
                    <p>Roll No: <?php echo $student['rollno']; ?></p>
                </div>
                
                <?php if($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
                <div class="text-center mb-3">
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                </div>
                <?php endif; ?>
                
                <?php if($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
                <?php endif; ?>
                
                <?php if(!$success_message): ?>
                <h4>Change Your Password</h4>
                <form method="post">
                    <div class="form-group password-field">
                        <label for="current_password">Current Password:</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                        <i class="fas fa-eye toggle-password" data-target="current_password"></i>
                    </div>
                    
                    <div class="form-group password-field">
                        <label for="new_password">New Password:</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <i class="fas fa-eye toggle-password" data-target="new_password"></i>
                        <div class="password-requirements">
                            Password must be at least 4 characters long.
                        </div>
                    </div>
                    
                    <div class="form-group password-field">
                        <label for="confirm_password">Confirm New Password:</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        <i class="fas fa-eye toggle-password" data-target="confirm_password"></i>
                    </div>
                    
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                        <a href="dashboard.php" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password visibility
            $('.toggle-password').click(function() {
                const targetId = $(this).data('target');
                const passwordField = $('#' + targetId);
                
                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    $(this).removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    passwordField.attr('type', 'password');
                    $(this).removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });
            
            // Check password match
            $('#confirm_password').on('keyup', function() {
                if ($('#new_password').val() == $('#confirm_password').val()) {
                    $('#confirm_password').removeClass('is-invalid').addClass('is-valid');
                } else {
                    $('#confirm_password').removeClass('is-valid').addClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>
