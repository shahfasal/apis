<?php
include '../libs/helper.php';
include '../libs/accesscontrol.php';
$headers = apache_request_headers();
print_r($headers);

if(isset($headers['oauth_key']) && !empty($headers['oauth_key']) && isset($headers['logout_all_devices'])=='true')
{
    
    logout_from_all_devices(get_profile_id_from_oauth($headers['oauth_key']),$headers['oauth_key']);   
}
else if(isset($headers['oauth_key']) && !empty($headers['oauth_key']))
{
    //print_r($_GET['oauth_key']);
   logout($headers['oauth_key']); 
}


?>