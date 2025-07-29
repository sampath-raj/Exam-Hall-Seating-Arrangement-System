<?php
include '../db.php';
session_start();

if(isset($_POST['delete_subject'])) {
    $subject_id = mysqli_real_escape_string($conn, $_POST['delete_subject']);
    
    $query = "DELETE FROM subjects WHERE subject_id = '$subject_id'";
    
    if(mysqli_query($conn, $query)) {
        $_SESSION['success'] = "Subject deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting subject: " . mysqli_error($conn);
    }
    
    header("Location: manage_subjects.php");
    exit();
}
?>
