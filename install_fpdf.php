<?php
// This script helps you install FPDF library

// Define paths
$fpdfDir = __DIR__ . '/fpdf';
$fpdfZip = __DIR__ . '/fpdf.zip';
$fpdfUrl = 'http://www.fpdf.org/en/dl.php?v=184&f=zip';

// Create output buffer for messages
ob_start();
echo "<html><head><title>FPDF Installer</title>";
echo "<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css'>";
echo "</head><body><div class='container mt-4'>";
echo "<h2>FPDF Library Installer</h2><hr>";

// Check if FPDF already exists
if (file_exists($fpdfDir . '/fpdf.php')) {
    echo "<div class='alert alert-success'>✅ FPDF is already installed!</div>";
    echo "<p>The FPDF library is located at: <code>$fpdfDir</code></p>";
    echo "<p>You can use PDF functionality now.</p>";
    echo "<a href='admin/allocate_seats.php' class='btn btn-primary'>Return to Seat Allocation</a>";
} else {
    echo "<div class='alert alert-warning'>FPDF is not installed yet. Following these steps to install:</div>";
    echo "<ol>";
    echo "<li>Download FPDF from <a href='http://www.fpdf.org/en/download.php' target='_blank'>http://www.fpdf.org</a></li>";
    echo "<li>Extract the downloaded zip file</li>";
    echo "<li>Create a folder called \"fpdf\" in: <code>/d:/neramda/htdocs/pro1/pro/</code></li>";
    echo "<li>Copy all files from the extracted folder into the newly created fpdf folder</li>";
    echo "<li>Make sure the file structure looks like: <code>/d:/neramda/htdocs/pro1/pro/fpdf/fpdf.php</code></li>";
    echo "</ol>";
    
    echo "<div class='card mb-4 p-3'>";
    echo "<h4>Manual Installation Verification</h4>";
    echo "<p>After following the steps above, use this button to verify your installation:</p>";
    echo "<form method='post'><button name='verify' class='btn btn-primary'>Verify Installation</button></form>";
    echo "</div>";
}

// Process verification request
if (isset($_POST['verify'])) {
    echo "<h4>Installation Verification Results:</h4>";
    
    if (!is_dir($fpdfDir)) {
        echo "<div class='alert alert-danger'>❌ The fpdf directory does not exist.</div>";
        echo "<p>Please create the directory: <code>$fpdfDir</code></p>";
    } else {
        echo "<div class='alert alert-success'>✅ The fpdf directory exists!</div>";
        
        if (!file_exists($fpdfDir . '/fpdf.php')) {
            echo "<div class='alert alert-danger'>❌ The main fpdf.php file was not found.</div>";
            echo "<p>Make sure you copied all files from the extracted FPDF download.</p>";
        } else {
            echo "<div class='alert alert-success'>✅ The fpdf.php file was found!</div>";
            echo "<div class='alert alert-success'><strong>Congratulations!</strong> FPDF is now correctly installed.</div>";
            echo "<a href='admin/allocate_seats.php' class='btn btn-primary'>Return to Seat Allocation</a>";
        }
    }
}

echo "</div></body></html>";
ob_end_flush();
?>
