<?php
include '../db.php';
session_start();
if(isset($_POST['addallotment'])){
    $room = $_POST['room'];
    $room = mysqli_real_escape_string($conn, $room);
    $room = htmlentities($room);
    $class = $_POST['class'];
    $class = mysqli_real_escape_string($conn, $class);
    $class = htmlentities($class);
    $start = $_POST['start'];
    $start = mysqli_real_escape_string($conn, $start);
    $start = htmlentities($start);
    $end = $_POST['end'];
    $end = mysqli_real_escape_string($conn, $end);
    $end = htmlentities($end);

    // Calculate total students
    $total = $end - $start + 1;
    
    // Validate batch_time to only allow 'FN' or 'AF'
    $batch_time = in_array($_POST['batch_time'], ['FN', 'AF']) ? $_POST['batch_time'] : 'FN';
    $batch_time = mysqli_real_escape_string($conn, $batch_time);
    
    // Get the date from the form
    $date = $_POST['date'];
    $date = mysqli_real_escape_string($conn, $date);
    $date = htmlentities($date);
    
    // Validate the date format
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $date)) {
        $_SESSION['batchnot'] = "Error!! Invalid date format. Use YYYY-MM-DD.";
        header("Location: dashboard.php");
        exit;
    }
    
    $subject_id = $_POST['subject'];
    $subject_id = mysqli_real_escape_string($conn, $subject_id);
    $subject_id = htmlentities($subject_id);
    
    // Check room capacity
    $room_query = mysqli_query($conn, "SELECT capacity FROM room WHERE rid = '$room'");
    $room_data = mysqli_fetch_assoc($room_query);
    $room_capacity = $room_data['capacity'];
    
    // Check current allocation in the room for this date and batch time
    $allocated_query = mysqli_query($conn, "SELECT SUM(total) as allocated FROM batch WHERE room_id = '$room' AND date = '$date' AND batch_time = '$batch_time'");
    $allocated_data = mysqli_fetch_assoc($allocated_query);
    $current_allocation = $allocated_data['allocated'] ? $allocated_data['allocated'] : 0;
    
    // Check if adding these students would exceed room capacity
    if ($current_allocation + $total > $room_capacity) {
        $_SESSION['batchnot'] = "Error!! Room capacity exceeded. This room can only accommodate " . ($room_capacity - $current_allocation) . " more students for this session.";
        header("Location: dashboard.php");
        exit;
    }

    $insert = "INSERT INTO batch (class_id, subject_id, room_id, startno, endno, total, date, batch_time) 
               VALUES ('$class', '$subject_id', '$room', '$start', '$end', '$total', '$date', '$batch_time')";
    $insert_query = mysqli_query($conn, $insert);
    if($insert_query){
        $_SESSION['batch'] = "New allotment placed successfully.";
    }
    else{
        $_SESSION['batchnot'] = "Error!! New allotment not placed.";
    }
    header("Location: dashboard.php");
}
?>