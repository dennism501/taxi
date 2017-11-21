<?php

require_once 'db_functions.php';
$db = new db_functions();

$response = array('error' => FALSE);


if(isset($_POST['userName']) && isset($_POST['pin'])){


	$userName = $_POST['userName'];
    $pin = $_POST['pin']; 
	$user = $db->getUserByPin($userName,$pin);

	if ($user != false) {
        // use is found
        $response["error"] = FALSE;
        $response["employee_pin"]["pin"] = $user["pin"];
        $response["employee_userName"]["userName"] = $user["userName"];
        echo json_encode($response);
    } else {
        // user is not found with the credentials
        $response["error"] = TRUE;
        $response["error_msg"] = "Login credentials are wrong. Please try again!";
        echo json_encode($response);
    }



}else{

	// required post params is missing
    $response["error"] = TRUE;
    $response["error_msg"] = "Your pin is incorrect or not registered!";
    echo json_encode($response);
}


?>