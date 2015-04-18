<?php

include '../libs/helper.php';
include '../libs/accesscontrol.php';
include '../libs/configer.php';
$json = file_get_contents('php://input');
$obj = json_decode($json);

if (isset($obj->{"username"}) && isset($obj->{"password"}) && ($obj->{"isAdmin"}== 'true') ) {

    
    $decode_password = base64_decode($obj->{"password"});
    $username_user = $obj->{"username"};
   $password_user =  md5($decode_password);
    $device = $obj->{"user_data"};
    get_admin_oauth($username_user, $password_user, $device);
} else if (isset($obj->{"username"}) && isset($obj->{"password"})) {

    $decode_password = base64_decode($obj->{"password"});
    $username_user = $obj->{"username"};
    $password_user = md5($decode_password);
    $device = $obj->{"user_data"};
    get_oauth($username_user, $password_user, $device);
} else {
    header($_SERVER["SERVER_PROTOCOL"] . " " . $GLOBALS['bad_request']);
    $result_array = array('status' => 'error',
        'message' => 'Method not allowed');

    print_r(json_encode($result_array));
}
?>
