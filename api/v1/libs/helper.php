<?php
include 'configer.php';
function check_user($username_user, $password_user, $device) {
    include 'sql.php';
    $id = get_profile_id($username_user, $password_user);

    $SQL = "SELECT * FROM user WHERE profile_id=$id";

    $result = $conn->query($SQL);

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        if (($row['username'] == $username_user) && ($row['password'] == $password_user)) {
            get_oauth($username_user, $password_user, $device);
            $conn->close();
        } else {
            header($_SERVER["SERVER_PROTOCOL"]. " ".$GLOBALS['status_notfound']);
            $result_array = array('status' => 'error',
                'message' => 'User not found');
            print_r(json_encode($result_array));
        }
    }
}

function get_profile_id($username_user, $password_user) {
    $profile_id = null;
    if (isset($username_user) && isset($password_user)) {
        include 'sql.php';
        $sql = "SELECT profile_id FROM " . $dbname . ".user where username='$username_user' and password='$password_user'";

        $result = $conn->query($sql);


        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $profile_id = $row['profile_id'];
        }
    }

    return $profile_id;
}

function get_profile_id_from_oauth($oauth_key) {
    $profile_id = null;
    if (isset($oauth_key)) {
        include 'sql.php';
        $sql = "SELECT profile_id FROM " . $dbname . ".oauth where oauth_key='$oauth_key'";

        $result = $conn->query($sql);


        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $profile_id = $row['profile_id'];
        }
    }

    return $profile_id;
}

function check_oauth($profile_id, $device) {
    $oauth_key = null;
    if (isset($profile_id) && isset($device)) {
        include 'sql.php';

        $date = date_create();
        date_timestamp_set($date, time());
        $today = date_format($date, "Y-m-d H:i:s");
        $sql = "SELECT * FROM " . $dbname . ".oauth where profile_id=$profile_id and device = '$device'  ";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $expiry_date = $row['oauth_expiry'];
            if ($expiry_date >= $today) {
                $oauth_key = $row['oauth_key'];
            }
        }
    }

    return $oauth_key;
}

function oauth_exists($profile_id, $oauth) {
    $oauth_key = null;
    if (isset($profile_id) && isset($oauth)) {
        include 'sql.php';

        $date = date_create();
        date_timestamp_set($date, time());
        $today = date_format($date, "Y-m-d H:i:s");
        $sql = "SELECT * FROM " . $dbname . ".oauth where profile_id=$profile_id and oauth_key = '$oauth'  ";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $expiry_date = $row['oauth_expiry'];
            if ($expiry_date >= $today) {
                $oauth_key = $row['oauth_key'];
                return true;
            }
        }
    }

    return false;
}

function get_oauth($username_user, $password_user, $device) {
    if (isset($username_user) && isset($password_user) && isset($device)) {

        $device = json_encode($device);
        $profile_id = get_profile_id($username_user, $password_user);
        $oauth_key = check_oauth($profile_id, $device);
        $status = "failure";
        //$device_name = 'device_'.uniqid(rand(), true); 
      include 'sql.php';
        if ($profile_id != null && $oauth_key == null) {
            
            /*
             * generate and write the oauth key to db
             */
            $token = md5(uniqid(rand(), true));
            $date = date_create();
            date_timestamp_set($date, time() + ( 2 * 24 * 60 * 60));
            $dtm = date_format($date, "Y-m-d H:i:s");
            $sql = "insert into " . $dbname . ".oauth(oauth_key,oauth_expiry,device,profile_id,unique_id_of_device) value ('$token','$dtm','$device',$profile_id,'$device_name')";
            $result = $conn->query($sql);   
            
          
                $oauth_key = $token;
            header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_found']);
            header('Oauth: ' . $oauth_key);
            $res=get_userdata($profile_id);   
            $result_array = array('status' => 'Success',
                'message' => 'User found', 'number_of_devices_logged_in' => get_users_logged_in($profile_id),'user_data' => $res);
            
            /*
             * write to logs
             */
            date_default_timezone_set('Asia/Calcutta');
            $date = date_create();
            date_timestamp_set($date, time());
            $dtm = date_format($date, "Y-m-d H:i:s");
            //echo $dtm;
            //exit;
             $sql = "insert into " . $dbname . ".logs(profile_id,state,mytime,oauth_key,device) value ($profile_id,'login','$dtm','$oauth_key','$device')";
             $result = $conn->query($sql); 
            $conn->close();
        } else if ($oauth_key != null) {
           
            $res=get_userdata($profile_id);
          
            header($_SERVER["SERVER_PROTOCOL"]. " ".$GLOBALS['status_found']);
            header('oauth: ' . $oauth_key);
            $status = "Sucess";
           
            $result_array = array('status' => 'Success',
                'message' => 'User found', 'number_of_devices_logged_in' => get_users_logged_in($profile_id),'user_data' => $res);
        /*
         * write to logs
         */
            date_default_timezone_set('Asia/Calcutta');
            $date = date_create();
            date_timestamp_set($date, time());
            $dtm = date_format($date, "Y-m-d H:i:s");
           
            $sql = "insert into " . $dbname . ".logs(profile_id,state,mytime,oauth_key,device) value ($profile_id,'login','$dtm','$oauth_key','$device')";
            $result = $conn->query($sql); 
            
        } else {
            header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_notfound']);
            header('oauth: ' . $oauth_key);
            $result_array = array('status' => 'Failure',
                'message' => 'User not found');
        }

        print_r(json_encode($result_array));
    }
}
function get_admin_oauth($username_user, $password_user, $device) {
    if (isset($username_user) && isset($password_user) && isset($device)) {
        $secret=check_admin($username_user);
        if($secret!= 'false'){
        $device = json_encode($device);
        $profile_id = get_profile_id($username_user, $password_user);
        $oauth_key = check_oauth($profile_id, $device);
        $status = "failure";
        $device_name = 'device_'.uniqid(rand(), true); 
      
       include 'sql.php';
        if ($profile_id != null && $oauth_key == null) {
            
            /*
             * generate and write the oauth key to db
             */
            $token = md5(uniqid(rand(), true));
            $date = date_create();
            date_timestamp_set($date, time() + ( 2 * 24 * 60 * 60));
            $dtm = date_format($date, "Y-m-d H:i:s");
            $sql = "insert into " . $dbname . ".oauth(oauth_key,oauth_expiry,device,profile_id,unique_id_of_device) value ('$token','$dtm','$device',$profile_id,'$device_name')";
            $result = $conn->query($sql);   
            
          
                $oauth_key = $token;
            header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_found']);
            header('Oauth: ' . $oauth_key);
            $res=get_userdata($profile_id);   
            $result_array = array('status' => 'Success',
                'message' => 'User found', 'number_of_devices_logged_in' => get_users_logged_in($profile_id),'secret' => $secret,'user_data' => $res);
            
            /*
             * write to logs
             */
            date_default_timezone_set('Asia/Calcutta');
            $date = date_create();
            date_timestamp_set($date, time());
            $dtm = date_format($date, "Y-m-d H:i:s");
            //echo $dtm;
            //exit;
             $sql = "insert into " . $dbname . ".logs(profile_id,state,mytime,oauth_key,device) value ($profile_id,'login','$dtm','$oauth_key','$device')";
             $result = $conn->query($sql); 
            $conn->close();
        } else if ($oauth_key != null) {
           
            $res=get_userdata($profile_id);
          
            header($_SERVER["SERVER_PROTOCOL"]. " ".$GLOBALS['status_found']);
            header('oauth: ' . $oauth_key);
            $status = "Sucess";
           
            $result_array = array('status' => 'Success',
                'message' => 'User found', 'number_of_devices_logged_in' => get_users_logged_in($profile_id),'secret' => $secret,'user_data' => $res);
            /*
         * write to logs
         */
            date_default_timezone_set('Asia/Calcutta');
            $date = date_create();
            date_timestamp_set($date, time());
            $dtm = date_format($date, "Y-m-d H:i:s");
           
            $sql = "insert into " . $dbname . ".logs(profile_id,state,mytime,oauth_key,device) value ($profile_id,'login','$dtm','$oauth_key','$device')";
            $result = $conn->query($sql); 
        } else {
            header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_notfound']);
//            header('oauth: ' . $oauth_key);
            $result_array = array('status' => 'Failure',
                'message' => 'User not found');
        }

        print_r(json_encode($result_array));
    }else {
        header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_notfound']);
            $result_array = array('status' => 'Failure',
                'message' => 'User is not admin');
    }
    }  
}
function get_userdata($profile_id){
    include 'sql.php';
   $devices=get_users_logged_in_devices($profile_id);

            $res = array();
            foreach ($devices as $device_json_data) {
                $device_object = json_decode($device_json_data);
                $values = $device_object;
                $res[] = $values;
            }
        return $res;
}
function get_users_logged_in($profile_id) {
    include 'sql.php';

    $date = date_create();
    date_timestamp_set($date, time());
    $today = date_format($date, "Y-m-d H:i:s");
    $sql = "SELECT * FROM " . $dbname . ".oauth where profile_id='$profile_id' ";
    $result = $conn->query($sql);
    if (!$result) {
        die(sprintf("Error: %s", $conn->error));
    }
    $count = 0;
    while ($row = $result->fetch_assoc()) {
        $expiry_date = $row['oauth_expiry'];
        if ($expiry_date >= $today) {
            $count++;
        }
    }
    return $count;
}

function get_users_logged_in_devices($profile_id) {
    include 'sql.php';

    $date = date_create();
    date_timestamp_set($date, time());
    $today = date_format($date, "Y-m-d H:i:s");
    $sql = "SELECT * FROM " . $dbname . ".oauth where profile_id='$profile_id' ";
    $result = $conn->query($sql);
    if (!$result) {
        die(sprintf("Error: %s", $conn->error));
    }
    $count = 0;
    $devices = array();
    while ($row = $result->fetch_assoc()) {
        $expiry_date = $row['oauth_expiry'];
        if ($expiry_date >= $today) {
            $devices[] = $row['device'];
        }
    }
    return $devices;
}

function logout($ouath_key) {
    $profile_id=get_profile_id_from_oauth($ouath_key);
    include 'sql.php';
    $sql = "DELETE FROM " . $dbname . ".oauth where oauth_key='$ouath_key'";
    if ($conn->query($sql)) {
        /*
         * write to logs
         */
            date_default_timezone_set('Asia/Calcutta');
            $date = date_create();
            date_timestamp_set($date, time());
            $dtm = date_format($date, "Y-m-d H:i:s");
            
            $device=get_device_deatil_from_oauth($oauth_key);
            $sql = "insert into " . $dbname . ".logs(profile_id,state,mytime,oauth_key,device) value ($profile_id,'logout','$dtm','$ouath_key','$device')";
            $result = $conn->query($sql); 
//            echo $profile_id;
//            exit;
        //header("Location: " .$GLOBALS['login_url']);
    } else {
        $result_array = array('status' => 'error',
            'message' => 'ouath do not exist');
        print_r(json_encode($result_array));
    }
    $conn->close();
}

function logout_from_all_devices($profile_id, $oauth_key) {
    /*
     * authenticat eand validate the oauth and  then delete
     */
    if (isset($profile_id) && isset($oauth_key)) {
        if (oauth_exists($profile_id, $oauth_key)) {
            include 'sql.php';
            $sql = "DELETE FROM " . $dbname . ".oauth where profile_id=$profile_id";
            if ($conn->query($sql)) {
                $result_array = array('status' => 'sucess',
                    'message' => "user logged out from all the devices");
                print_r(json_encode($result_array));
            } else {
                $result_array = array('status' => 'error',
                    'message' => 'ouath do not exist');
                print_r(json_encode($result_array));
            }
            $conn->close();
        }
    }
}

function is_user_present($email) {

    if (isset($email)) {
        include 'sql.php';
        $sql = "SELECT profile_id FROM " . $dbname . ".user where username='$email'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {

            return true;
        }
    }
    return false;
}

function get_profile_id_email($email) {

    if (isset($email)) {
        include 'sql.php';
        $sql = "SELECT profile_id FROM " . $dbname . ".profile where profile_email='$email'";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $profile_id = $row['profile_id'];
            return $profile_id;
        }
    }

    return 0;
}

function register_user($email, $name) {
    include 'sql.php';
    $sql = "insert into profile(profile_email,profile_name,profile_activated) values('$email','$name',0)";
    $result = $conn->query($sql);
    return $result;
}

function sendMail($info) {
    require 'mail-lib/PHPMailerAutoload.php';

    $mail = new PHPMailer;

//$mail->SMTPDebug = 3;                               // Enable verbose debug output

    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp.gmail.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'vsmava@visionaryschoolmen.com';                 // SMTP username
    $mail->Password = 'vsmava123@demo';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;                                    // TCP port to connect to

    $mail->From = 'vsmava@visionaryschoolmen.com';
    $mail->FromName = 'VSM';
    $mail->addAddress($info['to_email']);     // Add a recipient
    //$mail->addAddress('abhishek.ramkrishna002@gmail.com');               // Name is optional
    //$mail->addReplyTo('abhishek.ramkrishna002@gmail.com', 'Information');
    //$mail->addCC('abhishek.ramkrishna002@gmail.com');
    // $mail->addBCC('fassha08@gmail.com');
//$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
    $mail->isHTML(true);                                  // Set email format to HTML

    $mail->Subject = $info['subject'];
    $mail->Body = $info['body'];
    //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    if (!$mail->send()) {
        return false;
    } else {
        return true;
    }
}

function forgot_password_activation_code($email, $code) {
    $profile_id = get_profile_id_email($email);
    include 'sql.php';
    $sql = "insert into forgot_activation(profile_id,activation_code,profile_email) values($profile_id,'$code','$email')";
    $result = $conn->query($sql);
}

function get_activation_link($email, $profile_password) {
    $profile_id = get_profile_id_email($email);
    $string = md5($email);
    include 'sql.php';
    $sql = "insert into activation(profile_id,activation_code,profile_email,profile_password) values($profile_id,'$string','$email','$profile_password')";
    $result = $conn->query($sql);
   // return $GLOBALS['url']."/activation.php?activation_code=$string";
     // return "http://localhost/vsm/api/activation.php?activation_code=$string";
        return $GLOBALS['activation_url']."/"."activation.php?activation_code=$string";

}

function activate($activation_code) {
    include 'sql.php';
    $sql = "SELECT profile_id,profile_email,profile_password FROM " . $dbname . ".activation where activation_code='$activation_code' ";
    $result = $conn->query($sql);
    if (!$result) {
        die(sprintf("Error: %s", $conn->error));
    }
    $user = array();
    if ($row = $result->fetch_assoc()) {
        $user['profile_id'] = $row['profile_id'];
        $user['profile_email'] = $row['profile_email'];
        $user['profile_password'] = $row['profile_password'];
    }
    $result_array = array();
    if (create_user($user) > 0) {
        //header("Location: " .$GLOBALS['mac_url']);
        header($_SERVER["SERVER_PROTOCOL"]. " ".$GLOBALS['status_found']);
        $result_array = array('status' => 'sucess',
            'message' => 'User activated sucessfully');
    } else {
        header($_SERVER["SERVER_PROTOCOL"]." ".$GLOBALS['status_notfound']);
        $result_array = array('status' => 'failure',
            'message' => 'User activation failed!');
    }
    print_r(json_encode($result_array));
}

function create_user($user) {
    include 'sql.php';
    $username = $user['profile_email'];
    $password = ($user['profile_password']);
    $profile_id = $user['profile_id'];
    $sql = "insert into user(username,password,profile_id,is_admin) values('$username','$password',$profile_id,'false')";
    $result = $conn->query($sql);
    return $result;
}

function update_user($email, $user_password) {
    include 'sql.php';
    $sql = "update user set password='$user_password' where username='$email'";
    $result = $conn->query($sql);
    return $result;
}

function file_meta($profile_id, $file_path) {
    include 'sql.php';


    $sql = "insert into file(profile_id,file_path) values($profile_id,'$file_path')";
    $result = $conn->query($sql);
    return $result;
}

function file_upload($oauth_key) {
    if ($_FILES['file']['name'] != "" && isset($oauth_key)) {
        $profile_id = get_profile_id_from_oauth($oauth_key);
        $filename = $_FILES['file']['tmp_name'];
        $destination = "C:/xampp/htdocs/vsm/files/" . $_FILES['file']['name'];
        if (move_uploaded_file($filename, $destination) or
                die("Could not copy file!")) {

            /*
             * entry into database
             */
            file_meta($profile_id, $destination);
            $result_array = array('status' => 'sucess',
                'file_path' => $destination);
            print_r(json_encode($result_array));
        } else {
            $result_array = array('status' => 'failure',
                'file_path' => "failed to upload");
            print_r(json_encode($result_array));
        }
    } else {
        die("No file specified!");
    }
}

function get_device_deatil_from_oauth($oauth_key)
{
    include 'sql.php';
     $sql = "SELECT device FROM " . $dbname . ".oauth where oauth_key='$oauth_key' ";
    $result = $conn->query($sql);
    if (!$result) {
        die(sprintf("Error: %s", $conn->error));
    }
    $count = 0;
    if ($row = $result->fetch_assoc()) {
        return $row['device'];
    }
}

function check_admin($username_user){
    include 'sql.php';
        $sql = "SELECT * FROM " . $dbname . ".user where username='$username_user'";
    $result = $conn->query($sql);
  
    
    if ($result->num_rows > 0) {
       
        $row = $result->fetch_assoc();
       
        if (($row['is_admin'] == 'true')) {
           $secret = $row['secret']; 
           
           return $secret;
            $conn->close();
        }else{
            return false;
        }
}
}
?>