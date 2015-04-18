<?php
include '../libs/accesscontrol.php';
include '../libs/helper.php';
$json = file_get_contents('php://input');
$obj = json_decode($json);

if (isset($obj->{"email"})) {
    $email = $obj->{"email"};
    $profile_id = get_profile_id_email($email);
    if($profile_id){
    $res = get_userdata($profile_id);
    $result_array = array('status' => 'Success',
        'message' => 'User details', 'user_data' => $res);
    print_r(json_encode($result_array));
    }else{
         header($_SERVER["SERVER_PROTOCOL"] . " " . $GLOBALS['status_notfound']);
    $result_array = array('status' => 'Failure',
        'message' => 'User not found');
    print_r(json_encode($result_array));
    }
}else{
       header($_SERVER["SERVER_PROTOCOL"] . " " . $GLOBALS['bad_request']);
    $result_array = array('status' => 'Failure',
        'message' => 'Method not allowed');
    print_r(json_encode($result_array));
}
?>
