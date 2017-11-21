<?php

require_once 'db_functions.php';
$db = new db_functions();

// json response array
$response = array("error" => FALSE);

if(isset($_POST['driverName'])&&isset($_POST['vehicleLicenseNumber'])){


	$driverName = $_POST['driverName'];
	$stationName = $_POST['stationNamr'];
	$driverNrc = $_POST['driverNrc'];
	$driverPhoneNumber = $_POST['driverPhoneNumber'];
	$vehicleLicenseNumber = $_POST['vehicleLicenseNumber'];
	$vehicleOwnerName = $_POST['vehicleOwnerName'];
	$vehicleOwnerNumber = $_POST['vehicleOwnerNumber'];
	$driverTbl = "driverTbl";

	if($db->isisDriverandOwnerExisted($driverNrc,$driverTbl)){

		$response["error"]= true;
		$response["driver"] = "Driver already exists with " .$driverNrc;
		echo json_encode($response);


	}
	else{

		if($db->storeVehicle($vehicleLicenseNumber)){

			$db->storeDriver();
			$db->storeOwner();

		}




	}

}



?>