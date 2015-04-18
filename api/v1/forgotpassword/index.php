<?php
include '../libs/configer.php';
include '../libs/sql.php';
include '../libs/helper.php';
include '../libs/accesscontrol.php';
$json = file_get_contents('php://input');
$obj = json_decode($json);
$apiResponse = array();
if (isset($obj->{"email"})) {
    $email = $obj->{"email"};
    $profile_id = get_profile_id_email($email);

    if ($profile_id) {
        $code = sha1(uniqid());
        //$link = "http://127.0.0.1:8080/reset-password.html?code=$code";
       $link = "http://localhost/vsm_frontend/reset-password.html?code=$code";
        $msg = "Please use the link to reset password.<br/>click to <a href='$link'>reset</a>";
        $info = array('to_email' => $email, 'subject' => 'vsm registration', 'body' => $msg);
        sendMail($info);
        forgot_password_activation_code($email, $code);

        $apiResponse = array(
            'status' => 'success',
            'statusCode' => 200,
            'message' => 'Email  sent',
        );
        print_r(json_encode($apiResponse));
    } else {
         header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_notfound']);
        $apiResponse = array(
            'status' => 'error',
            'message' => 'Email not set',
            'error' => 'try again'
        );
        print_r(json_encode($apiResponse));
    }
}
?>

