<?php

require_once 'db_functions.php';
$db = new db_functions();

if(isset($_POST['name']) && isset($_POST['branch'])){

    $name = $_POST['name'];
    $branch = $_POST['branch'];

    $db->storeStations($name,$branch);

}


?>