<?php

	require_once 'db_functions.php';
	$db = new db_functions();

	// json response array
	$response = array("error" => FALSE);


		if(isset($_POST['nrc'])){


			$nrc = $_POST['nrc'];

			$balance = $db->getCreditPayment($nrc);

			if($balance != false){

				$response["balance"] = TRUE;
				$response["balance"] = "has a balance";
				$response["fname"] = $balance["fname"];
				$response["lname"] = $balance["lname"];
				$response["Balance"] = $balance["Balance"];

				echo json_encode($response);
			}else{

				$response["balance"] = FALSE;
				$response["balance"] = "there is no balance";

				echo json_encode($response);
			}

		}

?>