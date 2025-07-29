<?php
include '../db.php';
session_start();

if(isset($_POST['add_subject'])) {
    $subject_code = mysqli_real_escape_string($conn, $_POST['subject_code']);
    $subject_name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $class_id = mysqli_real_escape_string($conn, $_POST['class_id']);
    
    $query = "INSERT INTO subjects (subject_code, subject_name, class_id) 
              VALUES ('$subject_code', '$subject_name', '$class_id')";
              
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Subject added successfully";
    } else {
        $_SESSION['error'] = "Error adding subject: " . mysqli_error($conn);
    }
    
    header("Location: manage_subjects.php");
    exit();
}
?>
