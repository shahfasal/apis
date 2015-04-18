<?php

if(isset($_GET['authToken']) && isset($_GET['id'])){
    $auth = $_GET['authToken'];
    $id = $_GET['id'];
    check_oauth($auth,$id);
}else{
    $apiResponse = array(
                            'status'   => 'error',
                             'statusCode' => 405,
                            'message' => 'Method not allowed',
                            'error' => 'Method not allowed'
                  );
    print_r(json_encode($apiResponse));
}

function check_oauth($auth,$id){
    include 'sql.php';
    $SQL = "SELECT * FROM oauth WHERE profile_id = $id";
    $result = $conn->query($SQL);
    
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        if($row['oauth_key'] == $auth ){
            getlist();
           $conn->close();
        }else{
            $apiResponse = array(
                        'status'=>'error',
                        'statusCode'=>404,
                        'message'=>'User not found'

                    );
            print_r(json_encode($apiResponse));
        }
    }
}

function getlist(){
    include 'sql.php';
    $SQL = "SELECT * FROM video";
    $result = $conn->query($SQL);
   if($result->num_rows > 0){
       $row = $result->fetch_assoc();
       //print_r($result);
       $apiResponse = array(
                    'status' => 'success',
                    'statusCode' => 200,
                    'message' => 'List found',
                    'success' => $row

                );
        print_r(json_encode($apiResponse));
   }

}

?>
