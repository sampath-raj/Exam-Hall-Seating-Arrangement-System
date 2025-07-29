<?php
// Prevent any output before PDF generation
ob_start();

// Start a new session or resume an existing one
session_start();

// Include the database connection
include "../link.php";

// Check if FPDF is available
$fpdf_exists = file_exists('../fpdf/fpdf.php');
if (!$fpdf_exists) {
    // Clean any buffered output
    ob_end_clean();
    // Display instructions and provide alternative if FPDF is not available
    display_alternative();
    exit;
}

// Verify required parameters
if (!isset($_GET['room']) || !isset($_GET['date']) || !isset($_GET['batch'])) {
    // Clean any buffered output
    ob_end_clean();
    die("Missing required parameters");
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
    // Clean any buffered output
    ob_end_clean();
    die("Exam seats table does not exist");
}

// Get room details
$room_query = mysqli_query($conn, "SELECT room_no, floor, capacity FROM room WHERE rid = '$roomid'");
$room = mysqli_fetch_assoc($room_query);

if (!$room) {
    // Clean any buffered output
    ob_end_clean();
    die("Room not found");
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

if (!$exam) {
    // Clean any buffered output
    ob_end_clean();
    die("No exam scheduled for this room, date and batch");
}

// Get students assigned to this room for the given date and batch
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

$students_result = mysqli_query($conn, $students_query);

// Clean any buffered output before including FPDF
ob_end_clean();

// Include FPDF library
require('../fpdf/fpdf.php');

// Create PDF
class PDF extends FPDF {
    function Header() {
        global $room, $exam_date, $batch_time, $exam;
        
        // Logo or header image if needed
        // $this->Image('logo.png', 10, 10, 30);
        
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'Exam Seating Arrangement', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Room ' . $room['room_no'] . ' - Floor ' . $room['floor'], 0, 1, 'C');
        
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, 'Date: ' . date('d-m-Y (l)', strtotime($exam_date)), 0, 1, 'C');
        $this->Cell(0, 5, 'Session: ' . ($batch_time == 'FN' ? 'Forenoon (9:30 AM - 12:30 PM)' : 'Afternoon (1:30 PM - 4:30 PM)'), 0, 1, 'C');
        if(isset($exam['subject_code']) && $exam['subject_code']) {
            $this->Cell(0, 5, 'Subject: ' . $exam['subject_code'] . ' - ' . $exam['subject_name'], 0, 1, 'C');
        }
        $this->Ln(5);
    }
    
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

// Start a new PDF document
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

// Student List Section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Student List', 0, 1);

$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(20, 7, 'Seat No', 1, 0, 'C');
$pdf->Cell(60, 7, 'Student Name', 1, 0, 'C');
$pdf->Cell(20, 7, 'Roll No', 1, 0, 'C');
$pdf->Cell(50, 7, 'Class', 1, 0, 'C');
$pdf->Cell(40, 7, 'Subject', 1, 1, 'C');

$pdf->SetFont('Arial', '', 9);
// Reset pointer and loop through students
mysqli_data_seek($students_result, 0);
while ($student = mysqli_fetch_assoc($students_result)) {
    $pdf->Cell(20, 6, $student['seat_no'], 1, 0, 'C');
    $pdf->Cell(60, 6, $student['name'], 1, 0);
    $pdf->Cell(20, 6, $student['rollno'], 1, 0, 'C');
    $pdf->Cell(50, 6, $student['year'] . ' ' . $student['dept'] . ' ' . $student['division'], 1, 0);
    $pdf->Cell(40, 6, isset($student['subject_code']) ? $student['subject_code'] : 'N/A', 1, 1);
    
    // Check if we need to add a new page
    if($pdf->GetY() > 250) {
        $pdf->AddPage();
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(20, 7, 'Seat No', 1, 0, 'C');
        $pdf->Cell(60, 7, 'Student Name', 1, 0, 'C');
        $pdf->Cell(20, 7, 'Roll No', 1, 0, 'C');
        $pdf->Cell(50, 7, 'Class', 1, 0, 'C');
        $pdf->Cell(40, 7, 'Subject', 1, 1, 'C');
        $pdf->SetFont('Arial', '', 9);
    }
}

// Room Layout Section on a new page
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Room Layout', 0, 1);

// Get seat matrix
mysqli_data_seek($students_result, 0);
$seat_matrix = array();
while ($student = mysqli_fetch_assoc($students_result)) {
    $seat_matrix[$student['seat_no']] = $student;
}

// Display desks with seats
$total_seats = $room['capacity'];
$total_desks = ceil($total_seats / 2);

$start_x = $pdf->GetX();
$start_y = $pdf->GetY();
$desk_width = 80;
$desk_height = 50;
$desks_per_row = 2;
$current_x = $start_x;
$current_y = $start_y;

for ($desk = 1; $desk <= $total_desks; $desk++) {
    // Check if we need to add a new page
    if ($current_y > 240) {
        $pdf->AddPage();
        $current_x = $pdf->GetX();
        $current_y = $pdf->GetY();
    }
    
    // Draw desk outline
    $pdf->SetFillColor(240, 240, 240);
    $pdf->Rect($current_x, $current_y, $desk_width, $desk_height, 'DF');
    
    // Desk header
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetXY($current_x, $current_y);
    $pdf->Cell($desk_width, 7, "Desk $desk", 0, 1, 'C');
    
    // Left seat (odd numbers)
    $left_seat = ($desk * 2) - 1;
    $pdf->SetXY($current_x + 2, $current_y + 10);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(30, 5, "Seat $left_seat", 0, 1);
    $pdf->SetFont('Arial', '', 8);
    
    if (isset($seat_matrix[$left_seat])) {
        $student = $seat_matrix[$left_seat];
        $pdf->SetXY($current_x + 2, $current_y + 15);
        $pdf->Cell(36, 5, $student['name'], 0, 1);
        $pdf->SetXY($current_x + 2, $current_y + 20);
        $pdf->Cell(36, 5, "Roll: " . $student['rollno'], 0, 1);
        $pdf->SetXY($current_x + 2, $current_y + 25);
        $pdf->Cell(36, 5, "{$student['year']} {$student['dept']} {$student['division']}", 0, 1);
    } else {
        $pdf->SetXY($current_x + 2, $current_y + 15);
        $pdf->Cell(36, 5, "Empty", 0, 1);
    }
    
    // Right seat (even numbers)
    $right_seat = $desk * 2;
    $pdf->SetXY($current_x + 42, $current_y + 10);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(30, 5, "Seat $right_seat", 0, 1);
    $pdf->SetFont('Arial', '', 8);
    
    if (isset($seat_matrix[$right_seat])) {
        $student = $seat_matrix[$right_seat];
        $pdf->SetXY($current_x + 42, $current_y + 15);
        $pdf->Cell(36, 5, $student['name'], 0, 1);
        $pdf->SetXY($current_x + 42, $current_y + 20);
        $pdf->Cell(36, 5, "Roll: " . $student['rollno'], 0, 1);
        $pdf->SetXY($current_x + 42, $current_y + 25);
        $pdf->Cell(36, 5, "{$student['year']} {$student['dept']} {$student['division']}", 0, 1);
    } else {
        $pdf->SetXY($current_x + 42, $current_y + 15);
        $pdf->Cell(36, 5, "Empty", 0, 1);
    }
    
    // Move to the next position
    if ($desk % $desks_per_row == 0) {
        $current_x = $start_x;
        $current_y += $desk_height + 10;
    } else {
        $current_x += $desk_width + 10;
    }
}

// Add signature fields
$pdf->SetFont('Arial', '', 10);
$pdf->Ln(10);
$pdf->Cell(0, 7, 'Room Supervisor: _________________________', 0, 1);
$pdf->Cell(0, 7, 'Signature: _________________________', 0, 1);

// Generate PDF file name
$fileName = "SeatingPlan_Room{$room['room_no']}_{$exam_date}_{$batch_time}.pdf";

// Output PDF for download
$pdf->Output('D', $fileName);
exit;

// Function to display alternative when FPDF is not available
function display_alternative() {
    global $conn, $_GET;
    
    // Get parameters
    $roomid = $_GET['room'];
    $exam_date = $_GET['date'];
    $batch_time = $_GET['batch'];
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>PDF Generation Error</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    </head>
    <body>
        <div class="container mt-5">
            <div class="alert alert-warning">
                <h4 class="alert-heading">FPDF Library Not Found!</h4>
                <p>The FPDF library is required to generate PDF files but is not installed on this server.</p>
                <hr>
                <h5>Installation Instructions:</h5>
                <ol>
                    <li>Download FPDF from <a href="http://www.fpdf.org/en/download.php" target="_blank">http://www.fpdf.org</a></li>
                    <li>Extract the downloaded zip file</li>
                    <li>Create a folder called "fpdf" in the directory: <code>/d:/neramda/htdocs/pro1/pro/</code></li>
                    <li>Copy all files from the extracted folder into the newly created fpdf folder</li>
                    <li>Refresh this page to try again</li>
                </ol>
                <hr>
                <h5>Alternative Options:</h5>
                <p>In the meantime, you can use one of these options:</p>
                <a href="print_seating.php?room='.$roomid.'&date='.$exam_date.'&batch='.$batch_time.'" class="btn btn-primary" target="_blank">
                    View Printable Version
                </a>
                <a href="view_allocation.php?room='.$roomid.'&date='.$exam_date.'&batch='.$batch_time.'" class="btn btn-secondary ml-2">
                    Go Back to View Allocation
                </a>
            </div>
        </div>
    </body>
    </html>';
}
?>
