<?php
session_start();
include "../link.php";

// Get dates with allocations
$dates_query = mysqli_query($conn, "SELECT DISTINCT date FROM batch ORDER BY date");
$selected_date = isset($_GET['date']) ? $_GET['date'] : '';
$selected_batch = isset($_GET['batch']) ? $_GET['batch'] : 'FN';
?>

<html>
<head>
    <title>Room Allocation by Date</title>
    <link rel="stylesheet" href="common.css">
    <?php include '../link.php'; ?>
    <style>
        .room-card {
            margin-bottom: 20px;
        }
        .allocation-details {
            margin-top: 10px;
        }
        .date-nav {
            margin-bottom: 20px;
        }
        .batch-toggle {
            margin-bottom: 20px;
        }
        .room-status {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .room-status.available {
            background-color: #d4edda;
        }
        .room-status.partial {
            background-color: #fff3cd;
        }
        .room-status.full {
            background-color: #f8d7da;
        }
        .capacity-bar {
            height: 20px;
            border-radius: 4px;
            background-color: #e9ecef;
            margin-bottom: 10px;
        }
        .capacity-bar-fill {
            height: 100%;
            border-radius: 4px;
            background-color: #28a745;
        }
        .capacity-text {
            font-size: 0.9rem;
            text-align: right;
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
                <a href="view_room_allocation.php" class="active_link"><img src="https://img.icons8.com/ios-filled/25/ffffff/calendar.png"/> Room Schedule</a>
            </li>
        </ul>
    </nav>
    
    <div id="content">
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <button type="button" id="sidebarCollapse" class="btn btn-info">
                    <img src="https://img.icons8.com/ios-filled/19/ffffff/menu--v3.png"/>
                </button>
                <span class="page-name"> Room Allocation by Date</span>
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
            <h4>Room Allocation Overview</h4>
            
            <!-- Date Navigation -->
            <div class="card date-nav">
                <div class="card-body">
                    <form method="get" class="form-inline">
                        <div class="form-group mr-3">
                            <label class="mr-2">Select Date:</label>
                            <select name="date" class="form-control" onchange="this.form.submit()">
                                <option value="">All Dates</option>
                                <?php
                                mysqli_data_seek($dates_query, 0);
                                while($date = mysqli_fetch_assoc($dates_query)) {
                                    $formatted_date = date('d-m-Y (l)', strtotime($date['date']));
                                    $selected = ($date['date'] == $selected_date) ? 'selected' : '';
                                    echo "<option value='{$date['date']}' $selected>{$formatted_date}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <?php if($selected_date): ?>
                        <div class="form-group">
                            <div class="btn-group" role="group">
                                <a href="?date=<?php echo $selected_date; ?>&batch=FN" class="btn <?php echo $selected_batch == 'FN' ? 'btn-primary' : 'btn-outline-primary'; ?>">Forenoon</a>
                                <a href="?date=<?php echo $selected_date; ?>&batch=AF" class="btn <?php echo $selected_batch == 'AF' ? 'btn-primary' : 'btn-outline-primary'; ?>">Afternoon</a>
                            </div>
                        </div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <?php if($selected_date): ?>
                <h5>Room Allocation for <?php echo date('d-m-Y (l)', strtotime($selected_date)); ?> - <?php echo $selected_batch == 'FN' ? 'Forenoon' : 'Afternoon'; ?> Session</h5>
                
                <div class="row">
                    <?php
                    // Get all rooms with their allocations for the selected date and batch
                    $rooms_query = "SELECT r.*, 
                                  (SELECT SUM(b.total) FROM batch b WHERE b.room_id = r.rid AND b.date = '$selected_date' AND b.batch_time = '$selected_batch') as allocated_seats
                                  FROM room r
                                  ORDER BY r.room_no";
                    $rooms_result = mysqli_query($conn, $rooms_query);
                    
                    while($room = mysqli_fetch_assoc($rooms_result)) {
                        $allocated = $room['allocated_seats'] ? $room['allocated_seats'] : 0;
                        $capacity = $room['capacity'];
                        $percentage = ($capacity > 0) ? round(($allocated / $capacity) * 100) : 0;
                        
                        if($percentage == 0) {
                            $status_class = "available";
                            $status_text = "Available";
                        } elseif($percentage < 100) {
                            $status_class = "partial";
                            $status_text = "Partially Filled";
                        } else {
                            $status_class = "full";
                            $status_text = "Full";
                        }
                        
                        echo "<div class='col-md-4'>
                                <div class='card room-card'>
                                    <div class='card-header'>
                                        <h5>Room {$room['room_no']} (Floor {$room['floor']})</h5>
                                    </div>
                                    <div class='card-body'>
                                        <div class='room-status $status_class'>
                                            <strong>Status:</strong> $status_text
                                        </div>
                                        
                                        <div class='capacity-details'>
                                            <div class='capacity-bar'>
                                                <div class='capacity-bar-fill' style='width: $percentage%;'></div>
                                            </div>
                                            <div class='capacity-text'>
                                                $allocated / $capacity seats ($percentage%)
                                            </div>
                                        </div>";
                        
                        if($allocated > 0) {
                            echo "<div class='allocation-details mt-3'>
                                    <h6>Allocated Classes:</h6>
                                    <ul class='list-group'>";
                            
                            $allocations_query = "SELECT b.*, c.year, c.dept, c.division, 
                                                sub.subject_code, sub.subject_name
                                                FROM batch b
                                                INNER JOIN class c ON b.class_id = c.class_id
                                                LEFT JOIN subjects sub ON b.subject_id = sub.subject_id
                                                WHERE b.room_id = '{$room['rid']}'
                                                AND b.date = '$selected_date'
                                                AND b.batch_time = '$selected_batch'
                                                ORDER BY c.year, c.dept, c.division";
                            
                            $allocations_result = mysqli_query($conn, $allocations_query);
                            
                            while($allocation = mysqli_fetch_assoc($allocations_result)) {
                                $subject_info = $allocation['subject_code'] ? " - {$allocation['subject_code']}" : "";
                                echo "<li class='list-group-item'>
                                        <div><strong>{$allocation['year']} {$allocation['dept']} {$allocation['division']}</strong>{$subject_info}</div>
                                        <div>Roll numbers: {$allocation['startno']} to {$allocation['endno']} ({$allocation['total']} students)</div>
                                      </li>";
                            }
                            
                            echo "</ul>
                                  <div class='text-center mt-3'>
                                    <a href='allocate_seats.php?room_id={$room['rid']}&exam_date=$selected_date&batch_time=$selected_batch' class='btn btn-sm btn-primary'>
                                        Allocate Seats
                                    </a>
                                    <a href='print_seating.php?room={$room['rid']}&date=$selected_date&batch=$selected_batch' class='btn btn-sm btn-success ml-2' target='_blank'>
                                        Print Plan
                                    </a>
                                  </div>
                                </div>";
                        } else {
                            echo "<div class='text-center mt-3'>
                                    <p>No allocations for this session</p>
                                    <a href='dashboard.php' class='btn btn-sm btn-outline-primary'>Add Allocation</a>
                                  </div>";
                        }
                        
                        echo "</div>
                            </div>
                        </div>";
                    }
                    ?>
                </div>
                
                <!-- Summary statistics -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        Session Summary
                    </div>
                    <div class="card-body">
                        <?php
                        // Get overall statistics
                        $stats_query = "SELECT 
                                       COUNT(DISTINCT r.rid) as total_rooms,
                                       SUM(r.capacity) as total_capacity,
                                       SUM(COALESCE(b.total, 0)) as total_students,
                                       COUNT(DISTINCT c.class_id) as total_classes
                                       FROM room r
                                       LEFT JOIN batch b ON b.room_id = r.rid AND b.date = '$selected_date' AND b.batch_time = '$selected_batch'
                                       LEFT JOIN class c ON b.class_id = c.class_id";
                        
                        $stats_result = mysqli_query($conn, $stats_query);
                        $stats = mysqli_fetch_assoc($stats_result);
                        
                        $usage_percentage = ($stats['total_capacity'] > 0) ? 
                                          round(($stats['total_students'] / $stats['total_capacity']) * 100) : 0;
                        
                        echo "<div class='row'>
                                <div class='col-md-3'>
                                    <div class='card bg-light'>
                                        <div class='card-body text-center'>
                                            <h3>{$stats['total_rooms']}</h3>
                                            <p class='text-muted'>Total Rooms</p>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-md-3'>
                                    <div class='card bg-light'>
                                        <div class='card-body text-center'>
                                            <h3>{$stats['total_capacity']}</h3>
                                            <p class='text-muted'>Total Capacity</p>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-md-3'>
                                    <div class='card bg-light'>
                                        <div class='card-body text-center'>
                                            <h3>{$stats['total_students']}</h3>
                                            <p class='text-muted'>Total Students</p>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-md-3'>
                                    <div class='card bg-light'>
                                        <div class='card-body text-center'>
                                            <h3>{$usage_percentage}%</h3>
                                            <p class='text-muted'>Capacity Utilization</p>
                                        </div>
                                    </div>
                                </div>
                              </div>";
                        ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <h5>Select a date to view room allocations</h5>
                    <p>Use the date selector above to view exam schedules for a specific date.</p>
                </div>
                
                <!-- Show upcoming exams -->
                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        Upcoming Exam Dates
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Session</th>
                                        <th>Total Students</th>
                                        <th>View Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $today = date('Y-m-d');
                                    $upcoming_query = "SELECT 
                                                      b.date, 
                                                      b.batch_time,
                                                      COUNT(DISTINCT b.batch_id) as total_batches,
                                                      SUM(b.total) as total_students
                                                      FROM batch b
                                                      WHERE b.date >= '$today'
                                                      GROUP BY b.date, b.batch_time
                                                      ORDER BY b.date, b.batch_time
                                                      LIMIT 10";
                                    
                                    $upcoming_result = mysqli_query($conn, $upcoming_query);
                                    
                                    if(mysqli_num_rows($upcoming_result) > 0) {
                                        while($exam = mysqli_fetch_assoc($upcoming_result)) {
                                            $day_of_week = date('l', strtotime($exam['date']));
                                            $formatted_date = date('d-m-Y', strtotime($exam['date']));
                                            $session = ($exam['batch_time'] == 'FN') ? 'Forenoon' : 'Afternoon';
                                            
                                            echo "<tr>
                                                    <td>{$formatted_date}</td>
                                                    <td>{$day_of_week}</td>
                                                    <td>{$session}</td>
                                                    <td>{$exam['total_students']}</td>
                                                    <td>
                                                        <a href='?date={$exam['date']}&batch={$exam['batch_time']}' class='btn btn-sm btn-info'>
                                                            View Details
                                                        </a>
                                                    </td>
                                                  </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='5' class='text-center'>No upcoming exams scheduled</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php include'footer.php' ?>
<script>
    $(document).ready(function() {
        $('#sidebarCollapse').on('click', function() {
            $('#sidebar').toggleClass('active');
        });
    });
</script>
</body>
</html>