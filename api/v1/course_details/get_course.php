<?php
include '../libs/sql.php';
if(isset($_GET['course_id'])){

    $id=$_GET['course_id'];
        $sql = "SELECT json_dump FROM " . $dbname . ".course where course_id=$id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $json = $row['json_dump'];
            print_r($json);
        }
        
}
?>
