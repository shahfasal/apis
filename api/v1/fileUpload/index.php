<?php

include '../libs/helper.php';
include '../libs/accesscontrol.php';
$headers = apache_request_headers();
if (isset($_FILES) && isset($headers['oauth_key']) ){
    file_upload($headers['oauth_key']);
}
//if (isset($_FILES)) {
//    file_upload($_GET['oauth_key']);
//}
?>

<!--<html>
<head>
<title>Uploading Complete</title>
</head>
<body>
<h2>Uploaded File Info:</h2>
<ul>
<li>Sent file: <?php echo $_FILES['file']['name'];  ?>
<li>File size: <?php echo $_FILES['file']['size'];  ?> bytes
<li>File type: <?php echo $_FILES['file']['type'];  ?>
</ul>
</body>
</html>-->