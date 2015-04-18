<?php

include '../libs/helper.php';
include '../libs/accesscontrol.php';
include '../libs/configer.php';
include '../libs/sql.php';
$json = file_get_contents('php://input');
$sql = "insert into " . $dbname . ".course(json_dump) value ('$json')";
$result = $conn->query($sql);
?>
