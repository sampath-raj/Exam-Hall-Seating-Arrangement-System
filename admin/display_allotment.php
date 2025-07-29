<?php
include "../link.php";
if(isset($_POST['display'])){
    $roomid = $_POST['display'];
    echo "<table>
            <thead>
            <tr>
            <th>Name</th>
            <th>Class</th>
            <th>Roll No.</th>
            <th>Seat No.</th>
            </tr>
            </thead>
            <tbody>
    ";
    
    $display = "SELECT s.name, s.rollno, s.seat_no, c.year, c.dept, c.division 
                FROM students s 
                INNER JOIN class c ON s.class = c.class_id
                INNER JOIN batch b ON b.class_id = c.class_id 
                WHERE b.room_id = '$roomid'
                ORDER BY s.seat_no";
                
    $display_query = mysqli_query($conn, $display);
    if(mysqli_num_rows($display_query)>0){
        while($row = mysqli_fetch_assoc($display_query)){
            echo "<tr>
                    <td>".$row['name']."</td>
                    <td>".$row['year']." ".$row['dept']." ".$row['division']."</td>
                    <td>".$row['rollno']."</td>
                    <td>".$row['seat_no']."</td>
                </tr>";
        }
    }
}
?>