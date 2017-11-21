<?php
require_once 'db_functions.php';
	$db = new db_functions();

	// json response array
$response = array("error" => FALSE);

if(isset($_POST['nrc']) && isset($_POST['balanceAmount'])){

	$nrc = $_POST['nrc'];
	$balanceAmount = $_POST['balanceAmount'];


	$user = $db->recordCreditPayment($nrc,$balanceAmount);

	if($user){

		$response["error"] = false;
		$response["amount"] = "Amount entered ". $balanceAmount ." by this nrc ".$nrc;
		$response["balance"] = $user["Balance"];

		echo json_encode($response);
	}
	else{

		$response["error"] = true;
		$response["error"] = "Failed to enter amount";
		echo json_encode($response);
	}
}else{

	$response["error"] = true;
	$response["error"] = "Error found";

	echo json_encode($response);
}


?>