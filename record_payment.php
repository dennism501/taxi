<?php 

require_once 'db_functions.php';
$db = new db_functions();


	if(isset($_POST['type']) && isset($_POST['amount'])){

		$type = $_POST['type'];
		$amount = $_POST['amount'];

		$payment = $db->recordPayment($type,$amount);

		if(strpos($payment, 'it has been inserted') !== false){

			echo 'it has been inserted';

		}else{


			echo 'check db function';
		}


	}
	else{


	}



?>