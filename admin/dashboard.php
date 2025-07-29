<?php
session_start();
?>
<html>
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="common.css">
    <?php include'../link.php' ?>
    <script>
        // Function to calculate total students
        function calculateTotal() {
            let start = parseInt(document.getElementsByName('start')[0].value);
            let end = parseInt(document.getElementsByName('end')[0].value);
            let totalField = document.getElementById('total-students');
            
            if (!isNaN(start) && !isNaN(end) && start <= end) {
                let total = end - start + 1;
                totalField.innerText = total;
            } else {
                totalField.innerText = "-";
            }
        }
        
        // Function to set minimum date to today
        function setMinDate() {
            let today = new Date();
            let dd = String(today.getDate()).padStart(2, '0');
            let mm = String(today.getMonth() + 1).padStart(2, '0');
            let yyyy = today.getFullYear();
            
            today = yyyy + '-' + mm + '-' + dd;
            document.getElementsByName('date')[0].min = today;
        }
        
        // Run when page loads
        window.onload = function() {
            setMinDate();
        }
    </script>
    </head>
<body>
<?php
    if(isset($_POST['deletebatch'])){
        $batch = $_POST['deletebatch'];
        $delete = "delete from batch where batch_id = '$batch'";
        $delete_query = mysqli_query($conn, $delete);
        if($delete_query){
            $_SESSION['delbatch'] = "Allotment deleted successfully";
        }
        else{
            $_SESSION['delnotbatch'] = "Error!! Allotment not deleted.";
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
                        <a href="add_student.php"><img src="https://img.icons8.com/ios-filled/25/ffffff/student-registration.png"/> Students</a>
                    </li>
                    <li>
                        <a href="add_room.php"><img src="https://img.icons8.com/metro/25/ffffff/building.png"/> Rooms</a>
                    </li>
                    <li>
                        <a href="manage_subjects.php"><img src="https://img.icons8.com/ios-filled/25/ffffff/book.png"/> Subjects</a>
                    </li>
                    <li>
                        <a href="dashboard.php" class="active_link"><img src="https://img.icons8.com/nolan/30/ffffff/summary-list.png"/> Allotment</a>
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
                    </button><span class="page-name"> Allotment</span>
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
        if(isset($_SESSION['batch'])){
            echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['batch']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
            unset($_SESSION['batch']);
        }
        if(isset($_SESSION['batchnot'])){
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['batchnot']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
            unset($_SESSION['batchnot']);
        }

        if(isset($_SESSION['delbatch'])){
            echo "<div class='alert alert-warning alert-dismissible fade show' role='alert'>".$_SESSION['delbatch']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
            unset($_SESSION['delbatch']);
        }
        if(isset($_SESSION['delnotbatch'])){
            echo "<div class='alert alert-danger alert-dismissible fade show' role='alert'>".$_SESSION['delnotbatch']."<button class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>";
            unset($_SESSION['delnotbatch']);
        }
        ?>
            <div class="table-responsive border">
                <table class="table table-hover text-center">
                        <thead class="thead-light">
                            <tr>
                                <th>Room & Floor</th>
                                <th>Class</th>
                                <th>Subject</th>
                                <th>Start Roll No.</th>
                                <th>End Roll No.</th>
                                <th>Total</th>
                                <th>Date</th>
                                <th>Batch Time</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <form action="addallot.php" method="post">
                           <tr>
                                <th class="py-3 bg-light">
                                    <select name="room" class="form-control" required>
                                        <?php
                                        $select_rooms = "SELECT r.rid, r.room_no, r.floor, r.capacity, 
                                                         COALESCE(SUM(b.total), 0) as filled 
                                                         FROM room r 
                                                         LEFT JOIN batch b ON b.room_id = r.rid 
                                                         GROUP BY r.rid";
                                        $select_rooms_query = mysqli_query($conn, $select_rooms);
                                        if(mysqli_num_rows($select_rooms_query)>0){
                                            echo "<option value=''>--select--</option>";
                                            while($row = mysqli_fetch_assoc($select_rooms_query)){
                                                echo "<option value=\"". $row['rid']."\">Room-".$row['room_no']." & Floor-".$row['floor']." (Capacity: ".$row['capacity'].")</option>";
                                            }
                                        } 
                                        else{
                                            echo "<option>No Rooms</option>";
                                        }
                                        ?>
                                    </select>
                                </th>
                                <th class="py-3 bg-light">
                                    <select id="sem" name="class" class="form-control" required>
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
                                            echo "<option value='No options'>no</option>";
                                        }
                                    ?>
                                    </select>
                                </th>
                                <th class="py-3 bg-light">
                                    <select name="subject" class="form-control" required>
                                        <option value="">--Select Subject--</option>
                                        <?php
                                            $subject_query = "SELECT * FROM subjects ORDER BY subject_code";
                                            $subject_result = mysqli_query($conn, $subject_query);
                                            while($subject = mysqli_fetch_assoc($subject_result)) {
                                                echo "<option value='".$subject['subject_id']."'>".$subject['subject_code']." - ".$subject['subject_name']."</option>";
                                            }
                                        ?>
                                    </select>
                                </th>
                                <th class="py-3 bg-light">
                                    <input type="number" name="start" class="form-control" size=4 min="1" required onchange="calculateTotal()">
                                </th>
                                <th class="py-3 bg-light">
                                    <input type="number" name="end" class="form-control" size=4 min="1" required onchange="calculateTotal()">
                                </th>
                                <th class="py-3 bg-light" id="total-students">-</th>
                                <th class="py-3 bg-light">
                                    <input type="date" name="date" class="form-control" required>
                                </th>
                                <th class="py-3 bg-light">
                                    <select name="batch_time" class="form-control">
                                        <option value="FN">Forenoon (FN)</option>
                                        <option value="AF">Afternoon (AF)</option>
                                    </select>
                                </th>
                                <th class="py-3 bg-light"><button class="btn btn-info form-control" name="addallotment">Add</button></th>
                            </tr> 
                            </form>    
                <?php
                $selectclass = "SELECT b.*, c.year, c.dept, c.division, r.room_no, r.floor, s.subject_code, s.subject_name 
                                FROM batch b
                                JOIN class c ON c.class_id = b.class_id 
                                JOIN room r ON r.rid = b.room_id
                                LEFT JOIN subjects s ON s.subject_id = b.subject_id
                                ORDER BY b.date, b.batch_time";
                $selectclassquery = mysqli_query($conn, $selectclass);
                if($selectclassquery){
                    while ($row = mysqli_fetch_assoc($selectclassquery)) {
                        echo "<tr>
                        <td>Room-".$row['room_no']." & Floor-".$row['floor']."</td>
                        <td>".$row['year']."-".$row['dept']."-".$row['division']."</td>
                        <td>".$row['subject_code']." - ".$row['subject_name']."</td>
                        <td>".$row['startno']."</td>
                        <td>".$row['endno']."</td>
                        <td>".$row['total']."</td>
                        <td>".date('d-m-Y', strtotime($row['date']))."</td>
                        <td>".($row['batch_time'] == 'FN' ? 'Forenoon' : 'Afternoon')."</td>
                        <form method='post'>
                        <td><button class='btn btn-light px-1 py-0' type='submit' value='".$row['batch_id']."' name='deletebatch'>
                        <img src='https://img.icons8.com/color/25/000000/delete-forever.png'/>
                        </button>
                        </td>
                        </form>
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
<?php include'footer.php' ?>
</body>
</html>