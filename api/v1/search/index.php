<?php

include '../libs/helper.php';
include '../libs/accesscontrol.php';
include '../libs/configer.php';

$json = file_get_contents('php://input');
$request = json_decode($json);
//print_r($request);

/*
 * getting all the logged in users || start
 */
include '../libs/sql.php';
$sql = "SELECT profile_id,mytime,device FROM logs where state like '%login%'  ";
$result = $conn->query($sql);
if (!$result) {
    die(sprintf("Error: %s", $conn->error));
}
$count = 0;
$logged_in_profiles = array();

while ($row = $result->fetch_assoc()) {
    $p_id = $row['profile_id'];
    $p_time = $row['mytime'];
    $p_device = json_decode($row['device']);
    $myuser = array('profile_id' => $p_id, 'mytime' => $p_time, 'device' => $p_device);
    $logged_in_profiles[] = $myuser;
}
//print_R($logged_in_profiles);
/*
 * getting all the logged in users || end
 */


/*
 * filtering all the users || start
 */
//converting evertything Asia/Kuala_Lumpur time stamp for sample
//$request has all the parameters set
$reault_array = array();
$flag = true;
for ($x = 0; $x < count($logged_in_profiles); $x++) {
    /*
     * get login user json_dump || start
     */
    $flag = false;

    //case 1: location
    if (isset($request->{'location'})) {
        //print_r($request->{'location'});
        /*
         * compare ui fields with bakend fields || start
         */
        $location_request = $request->{'location'};
        $location_response = $logged_in_profiles[$x]['device']->{'location-details'};


        if ($location_request->{'country'} != "" && $location_request->{'region'} != "" && $location_request->{'city'} != "") {
            if ($location_request->{'city'} == $location_response->{'city'} &&
                    $location_request->{'region'} == $location_response->{'region'} &&
                    $location_request->{'country'} == $location_response->{'country'}) {
                $flag = true;
            } else {
                $flag = false;

                continue;
            }
        } else {
            if ($location_request->{'country'} == "") {


                if ($location_request->{'region'} == "" && $location_request->{'city'} == "") {
                    continue;
                }
                $location_request->{'country'} = $location_response->{'country'};
            }
            if ($location_request->{'region'} == "") {
                $location_request->{'region'} = $location_response->{'region'};
            }
            if ($location_request->{'city'} == "") {
                $location_request->{'city'} = $location_response->{'city'};
            }

            if ($location_request->{'city'} == $location_response->{'city'} &&
                    $location_request->{'region'} == $location_response->{'region'} &&
                    $location_request->{'country'} == $location_response->{'country'}) {
                $flag = true;
            } else {
                $flag = false;

                continue;
            }
        }





        /*
         * compare ui fields with bakend fields || start
         */
    }
    //case 2: browser
    if (isset($request->{'browser'})) {

        /*
         * compare ui fields with bakend fields || start
         */
        $location_request = $request->{'browser'};
        
        $location_response = $logged_in_profiles[$x]['device']->{'device-details'}->{'browser'};
        if ($location_request->{'name'} == "" && $location_request->{'version'} == "") {
            continue;
        }
        if ($location_request->{'name'} == "") {
            $location_request->{'name'} = $location_response->{'name'};
        }
        if ($location_request->{'version'} == "") {
            $location_request->{'version'} = $location_response->{'version'};
        }


        if ($location_request->{'name'} == $location_response->{'name'} &&
                $location_request->{'version'} == $location_response->{'version'}) {
                    
            $flag = true;
        } else {
            $flag = false;
           
            continue;
        }
        /*
         * compare ui fields with bakend fields || end
         */
    }


    //case 3:time
    //case 4:date
    if (isset($request->{'date_time'})) {
        /*
         * compare ui fields with bakend fields || start
         */
        $location_request = $request->{'date_time'};
        $location_response = $logged_in_profiles[$x]['mytime'];
//        print_r($location_request);
//        print_r($location_response);
        if ($location_request->{'logout_date'} == "") {
            if ($location_request->{'login_date'} != "") {
                if ($location_request->{'login_time'} == "" && $location_request->{'logout_time'} == "") {
                    $start_date_time = $location_request->{'login_date'} . " " . "0:0:0";
                    $end_date_time = $location_request->{'login_date'} . " " . "23:59:59";
                } else if ($location_request->{'login_time'} != "" && $location_request->{'logout_time'} != "") {
                    $start_date_time = $location_request->{'login_date'} . " " . $location_request->{'login_time'};
                    $end_date_time = $location_request->{'login_date'} . " " . $location_request->{'logout_time'};
                } else if ($location_request->{'login_time'} == "" && $location_request->{'logout_time'} != "") {
                    $start_date_time = $location_request->{'login_date'} . " " . "0:0:0";
                    $end_date_time = $location_request->{'login_date'} . " " . $location_request->{'logout_time'};
                } else if ($location_request->{'login_time'} != "" && $location_request->{'logout_time'} == "") {
                    $start_date_time = $location_request->{'login_date'} . " " . $location_request->{'login_time'};
                    $end_date_time = $location_request->{'login_date'} . " " . "23:59:59";
                }
            } else if ($location_request->{'login_date'} == "") {
                date_default_timezone_set('Asia/Calcutta');

                // Then call the date functions
                $date = date('Y-m-d');
                if ($location_request->{'login_time'} == "" && $location_request->{'logout_time'} == "") {
                    //break;
                } else if ($location_request->{'login_time'} != "" && $location_request->{'logout_time'} != "") {
                    $start_date_time = $date . " " . $location_request->{'login_time'};
                    $end_date_time = $date . " " . $location_request->{'logout_time'};
                } else if ($location_request->{'login_time'} == "" && $location_request->{'logout_time'} != "") {
                    $start_date_time = $date . " " . "0:0:0";
                    $end_date_time = $date . " " . $location_request->{'logout_time'};
                } else if ($location_request->{'login_time'} != "" && $location_request->{'logout_time'} == "") {
                    $start_date_time = $date . " " . $location_request->{'login_time'};
                    $end_date_time = $date . " " . "23:59:59";
                }
            }
        } else if ($location_request->{'logout_date'} != "") {
            if ($location_request->{'login_date'} != "") {
                if ($location_request->{'login_time'} == "" && $location_request->{'logout_time'} == "") {
                    $start_date_time = $location_request->{'login_date'} . " " . "0:0:0";
                    $end_date_time = $location_request->{'logout_date'} . " " . "23:59:59";
                } else if ($location_request->{'login_time'} != "" && $location_request->{'logout_time'} != "") {
                    $start_date_time = $location_request->{'login_date'} . " " . $location_request->{'login_time'};
                    $end_date_time = $location_request->{'logout_date'} . " " . $location_request->{'logout_time'};
                } else if ($location_request->{'login_time'} == "" && $location_request->{'logout_time'} != "") {
                    $start_date_time = $location_request->{'login_date'} . " " . "0:0:0";
                    $end_date_time = $location_request->{'logout_date'} . " " . $location_request->{'logout_time'};
                } else if ($location_request->{'login_time'} != "" && $location_request->{'logout_time'} == "") {
                    $start_date_time = $location_request->{'login_date'} . " " . $location_request->{'login_time'};
                    $end_date_time = $location_request->{'logout_date'} . " " . "23:59:59";
                }
            } else if ($location_request->{'login_date'} == "") {
                date_default_timezone_set('Asia/Calcutta');

                // Then call the date functions
                $date = date('Y-m-d');
                if ($location_request->{'login_time'} == "" && $location_request->{'logout_time'} == "") {
                    //break;
                } else if ($location_request->{'login_time'} != "" && $location_request->{'logout_time'} != "") {
                    $start_date_time = $date . " " . $location_request->{'login_time'};
                    $end_date_time = $date . " " . $location_request->{'logout_time'};
                } else if ($location_request->{'login_time'} == "" && $location_request->{'logout_time'} != "") {
                    $start_date_time = $date . " " . "0:0:0";
                    $end_date_time = $date . " " . $location_request->{'logout_time'};
                } else if ($location_request->{'login_time'} != "" && $location_request->{'logout_time'} == "") {
                    $start_date_time = $date . " " . $location_request->{'login_time'};
                    $end_date_time = $date . " " . "23:59:59";
                }
            }
        }
        $timezone = $location_request->{'timezone'};
        $start_time = convert_time($start_date_time, $timezone, "Asia/Calcutta");
        $end_time = convert_time($end_date_time, $timezone, "Asia/Calcutta");

        if (differnt_dates($start_time, $location_response) == 1 &&
                differnt_dates($location_response, $end_time) == 1) {
            $flag = true;
        } else {
            $flag = false;
            continue;
        }

        /*
         * compare ui fields with bakend fields || end
         */
    }


    //case 5: device
    if (isset($request->{'device'})) {
        /*
         * compare ui fields with bakend fields || start
         */
        $location_request = $request->{'device'};
        $location_response = $logged_in_profiles[$x]['device']->{'device-details'}->{'device'};
//        print_r($location_request);
//        print_r($location_response);
        if ($location_request->{'model'} == "" && $location_request->{'vendor'} == ""
                && $location_request->{'type'} == "") {
            continue;
        }
        if ($location_request->{'model'} == "") {
            $location_request->{'model'} = $location_response->{'model'};
        }
        if ($location_request->{'type'} == "") {
            $location_request->{'type'} = $location_response->{'type'};
        }
        if ($location_request->{'vendor'} == "") {
            $location_request->{'vendor'} = $location_response->{'vendor'};
        }
        if ($location_request->{'model'} == $location_response->{'model'} &&
                $location_request->{'type'} == $location_response->{'type'} &&
                $location_request->{'vendor'} == $location_response->{'vendor'}) {
            $flag = true;
        } else {
            $flag = false;
            continue;
        }
        /*
         * compare ui fields with bakend fields || end
         */
    }

    //case 6: engine
    if (isset($request->{'engine'})) {
        /*
         * compare ui fields with bakend fields || start
         */
        $location_request = $request->{'engine'};
        $location_response = $logged_in_profiles[$x]['device']->{'device-details'}->{'engine'};
        if ($location_request->{'name'} == "" && $location_request->{'version'} == "") {
            continue;
        }
        if ($location_request->{'name'} == "") {
            $location_request->{'name'} = $location_response->{'name'};
        }
        if ($location_request->{'version'} == "") {
            $location_request->{'version'} = $location_response->{'version'};
        }
//        print_r($location_request);
//        print_r($location_response);
        if ($location_request->{'name'} == $location_response->{'name'} &&
                $location_request->{'version'} == $location_response->{'version'}) {
            $flag = true;
        } else {
            $flag = false;
            continue;
        }
        /*
         * compare ui fields with bakend fields || end
         */
    }
    //case 7: OS
    if (isset($request->{'os'})) {
        /*
         * compare ui fields with bakend fields || start
         */
        $location_request = $request->{'os'};
        $location_response = $logged_in_profiles[$x]['device']->{'device-details'}->{'os'};
//        print_r($location_request);
//        print_r($location_response);
        if ($location_request->{'name'} == "" && $location_request->{'version'} == "") {
            continue;
        }
        if ($location_request->{'name'} == "") {
            $location_request->{'name'} = $location_response->{'name'};
        }
        if ($location_request->{'version'} == "") {
            $location_request->{'version'} = $location_response->{'version'};
        }
        if ($location_request->{'name'} == $location_response->{'name'} &&
                $location_request->{'version'} == $location_response->{'version'}) {
            $flag = true;
        } else {
            $flag = false;
            continue;
        }
        /*
         * compare ui fields with bakend fields || end
         */
    }
    //case 8: others
    if (isset($request->{'fingerprint'})) {
        /*
         * compare ui fields with bakend fields || start
         */
        $location_request = $request->{'fingerprint'};
        $location_response = $logged_in_profiles[$x]['device']->{'fingerprint'};
        if ($location_request == $location_response) {
            $flag = true;
        } else {
            $flag = false;
            continue;
        }
        /*
         * compare ui fields with bakend fields || end
         */
    }
    if (isset($request->{'connection_type'})) {
        /*
         * compare ui fields with bakend fields || start
         */
        $location_request = $request->{'connection_type '};
        $location_response = $logged_in_profiles[$x]['device']->{'connection_type '};
        if ($location_request == $location_response) {
            $flag = true;
        } else {
            $flag = false;
            continue;
        }
        /*
         * compare ui fields with bakend fields || end
         */
    }

    /*
     * get login user json_dump || end
     */
    if ($flag == true) {
        $reault_array[] = $logged_in_profiles[$x];
    }
}

/*
 * filtering all the users || end
 */

print_r(json_encode($reault_array));

/*
 * conert timr to time zones || start
 * usage: echo convert_time($logged_in_profiles[$x]['mytime'], "Asia/Kuala_Lumpur");
 */

function convert_time($time, $bfr_timezone, $afrt_timezone) {

    $date = new DateTime($time, new DateTimeZone($bfr_timezone));
    $date->setTimezone(new DateTimeZone($afrt_timezone));

    return $date->format('Y-m-d H:i:s');
}

/*
 * conert timr to time zones || end
 */

function differnt_dates($date1, $date2) {
    $datetime1 = date_create($date1);
    $datetime2 = date_create($date2);
    $interval = date_diff($datetime1, $datetime2);
    //print_r($interval);
    //echo $interval->{'h'};
    if (($interval->{'y'} >= 0 ||
            $interval->{'m'} >= 0 ||
            $interval->{'d'} >= 0 ||
            $interval->{'h'} >= 0 ||
            $interval->{'i'} >= 0 ||
            $interval->{'s'} >= 0) &&
            $interval->{'invert'} == 0
    ) {
        return 1;
    } else {
        return 0;
    }
}

?>
