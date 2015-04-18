<?php

include '../libs/helper.php';
include '../libs/configer.php';
include '../libs/accesscontrol.php';
$json = file_get_contents('php://input');
$obj = json_decode($json);
if (isset($obj->{"username"}) && isset($obj->{"password"}) )
{
    $decode_password = base64_decode($obj->{"password"});
    $user_email=$obj->{"username"};
    $user_password=md5($decode_password);
   // $user_confirm_password=md5($obj->{"confirm_password"});
    
    $email = $user_email;
    $name = $user_email;
    $profile_password= $user_password;
    
    
    
    if (!is_user_present($email)) {
        
        /*
         * registe the user || write to profle tabel
         */
        if (register_user($email, $name) > 0) {
            
            $link = get_activation_link($email,$profile_password);
            $msg = "you are sucessfully registered: <br/>click to <a href='$link'>activate</a>";
            $info = array('to_email' => $email, 'subject' => 'vsm registration', 'body' => $msg);
            sendMail($info);
            header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_found']);
            $result_array = array('status' => 'Sucess',
                                    'message' => 'User registered and email sent');
        } else {
            header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_notfound']);
            $result_array = array('status' => 'Failure',
                                    'message' => 'fail during registration of user');
        }
    } else {
             header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_notfound']);
             $result_array = array('status' => 'Failure',
                                    'message' => 'User exists');
    }
    print_r(json_encode($result_array));
}else {
        header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['bad_request']);
    $result_array = array('status'=>'Error',
                    'message'=>'Method not allowed');
    print_r(json_encode($result_array));
   
}
?>

