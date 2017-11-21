
<?php

require_once 'db_functions.php';
$db = new db_functions();

// json response array
$response = array("error" => FALSE);

if (isset($_POST['fname']) && isset($_POST['lname']) && isset($_POST['nrc']) && isset($_POST['employeeType']) && isset($_POST['phoneNumber'])) {

    // receiving the post params
    $lname = $_POST['lname'];
    $fname = $_POST['fname'];
    $nrc = $_POST['nrc'];
    $employeeType = $_POST['employeeType'];
    $phoneNumber = $_POST['phoneNumber'];
    $pin = $_POST['pin'];
    $userName = $_POST['userName'];

    // check if user is already existed with the same nrc
    if ($db->isUserExisted($nrc)) {
        // user already existed
        $response["error"] = TRUE;
        $response["error_msg"] = "User already existed with " . $nrc;
        echo json_encode($response);
    } else {
        // create a new user
        $employee = $db->storeEmployee($fname, $lname, $nrc, $employeeType,$userName, $phoneNumber,$pin);
        if ($employee) {
            // user stored successfully
            $response["error"] = FALSE;
            $response["employee"]["id"] = $employee["id"];
            $response["employee"]["fname"] = $employee["fname"];
            $response["employee"]["lname"] = $employee["lname"];
            $response["employee"]["nrc"] = $employee["nrc"];
            $response["employee"]["employeeType"] = $employee["employeeType"];
            $response["employee"]["phoneNumber"] = $employee["phoneNumber"];
            $response["employee"]["userName"] = $employee["userName"];
            
            echo json_encode($response);
        } else {
            // user failed to store
            $response["error"] = TRUE;
            $response["error_msg"] = "Unknown error occurred in registration!";
            echo json_encode($response);
        }
    }
} else {
    $response["error"] = TRUE;
    $response["error_msg"] = "Required parameters (fname, lname and nrc) is missing!";
    echo json_encode($response);
}

?>