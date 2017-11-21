<?php


class db_functions
{


    private $conn;

    // constructor
    function __construct()
    {
        require_once 'db_connect.php';
        // connecting to database
        $db = new db_connect();
        $this->conn = $db->connect();
    }

    // destructor
    function __destruct()
    {

    }

    /*register employee at every station including the chairman and the station attendant and returns employee details*/
    public function storeEmployee($fname, $lname, $nrc, $employeeType, $userName, $phoneNumber, $pin)
    {
        $employee = array();
        $hash = md5($pin);

        $stmt = $this->conn->prepare("INSERT INTO employeeTbl(fname,lname,nrc,employeeType,phoneNumber,userName,pin)
            VALUES (?,?,?,?,?,?,?)");

        $stmt->bind_param("sssssss", $fname, $lname, $nrc, $employeeType, $phoneNumber, $userName, $hash);

        $result = $stmt->execute();
        $stmt->close();


        // check for successful store
        if ($result) {
            $stmt = $this->conn->prepare("SELECT fname,lname,userName,employeeType FROM employeeTbl WHERE nrc= ?");
            $stmt->bind_param("s", $nrc);

            if ($stmt->execute()) {

                $stmt->bind_result($employee['fname'], $employee['lname'], $employee['employeeType'], $employee['userName']);
                $stmt->fetch();
                $stmt->close();

                return $employee;
            }


        } else {
            return NULL;
        }
    }


    //record purchases on the system
    public function recordCreditPayment($nrc, $creditAmount)
    {

        $stmt = $this->conn->prepare("INSERT INTO transactionTbl(amount,PaymentID,transactionDate)  VALUES(?,(
                                      SELECT MAX(p.id) FROM paymentTbl p, driverTbl d
                                      WHERE d.vehicleID=p.vehicleID AND d.nrc=?),NOW())");
        $stmt->bind_param("is", $creditAmount, $nrc);
        $result = $stmt->execute();
        $stmt->close();
        if ($result) {

            return $transaction = $this->getCreditPayment($nrc);

        } else {

            return false;
        }


    }


    public function storeDriver($fname, $lname, $nrc, $licenseNumber, $phoneNumber, $stationName)
    {


        $stmt = $this->conn->prepare("INSERT INTO driverTbl(fname,lname,nrc,licenseNumber,phoneNumber,vehicleID,employeeID) VALUES (?,?,?,?,?,(SELECT max(id) FROM vehicleTbl),(SELECT id FROM stationTbl s WHERE s.name = ? ))");
        $stmt->bind_param("ssssss", $fname, $lname, $nrc, $licenseNumber, $phoneNumber, $stationName);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {

            return true;

        } else {
            return false;
            echo "regitering driver failed";
        }


    }

    //stores vehicles details in the database 
    public function storeVehicle($vehicleNumber)
    {

        $stmt = $this->conn->prepare("INSERT INTO vehicleTbl(vehicleNumber, dateOfRegistration) VALUES (?, (SELECT now()))");
        $stmt->bind_param("s", $vehicleNumber);

        $result = $stmt->execute();
        $stmt->close();

        if ($result) {

            return true;

        } else {

            return false;
        }

    }

    public function storeOwner($fname, $lname, $phoneNumber)
    {

        $stmt = $this->conn->prepare("INSERT INTO ownerTbl(fname,lname,phoneNumber,vehicleID) VALUES (?,?,?,(SELECT max(id) FROM vehicleTbl))");
        $stmt->bind_param("sss", $fname, $lname, $phoneNumber);

        if ($stmt->execute()) {


            return true;

        } else {

            return false;

        }


    }


    //checks if the driver has a credit balance before recording a payment
    public function getCreditPayment($nrc)
    {

        $balance = array();

        $stmt = $this->conn->prepare("SELECT d.fname, d.lname, (p.amountDue-SUM(t.amount)) AS Balance
	FROM driverTbl d, paymentTbl p, transactionTbl t
	WHERE d.nrc=?  AND d.vehicleID=p.vehicleID AND t.PaymentID=p.id AND '0' <>(
	SELECT (p.amountDue-SUM(t.amount)) AS bal
	FROM driverTbl d, paymentTbl p, transactionTbl t, vehicleTbl v
	WHERE d.nrc=? AND d.vehicleID=v.id AND p.vehicleID=v.id
	AND t.PaymentID =(
	SELECT MAX(p.id)
  	FROM paymentTbl p, driverTbl d
    	WHERE p.vehicleID=d.vehicleID AND d.nrc=?
	)
	)
	GROUP BY t.PaymentID
	ORDER BY t.PaymentID DESC LIMIT 1");

        $stmt->bind_param("sss", $nrc, $nrc, $nrc);

        if ($stmt->execute()) {

            $stmt->bind_result($balance['fname'], $balance['lname'], $balance['Balance']);
            $stmt->fetch();
            //$stmt->store_result($balance);
            $stmt->close();

            return $balance;


        } else {

            return NULL;

        }

    }

    //record payment with no balance 
    public function recordPayment($type, $amount)
    {

        //get nrc number associated the vehicleID
        $sql1 = "INSERT INTO paymentTbl(amountDue,buyDate,fuelType,vehicleID) VALUES (('$amount'+10),NOW(),'$type',1);";
        $sql2 = "INSERT INTO transactionTbl(amount,paymentID,transactionDate) VALUES ('0',LAST_INSERT_ID(), NOW());";

        $result1 = mysqli_query($this->conn, $sql1);
        $result2 = mysqli_query($this->conn, $sql2);

        if ($result1 && $result2) {

            return $result = "it has been inserted";
        } else {

            echo "error" . mysql_error();

        }

    }

    /*public function 		storeClient($vehicleLicenseNumber,$vehicleOwnerFirstName,$vehicleOwnerLastName,$vehicleOwnerNumber,$driverFirstName,$driverLastName,$driverNrc,$driverLicenseNumber, $driverPhoneNumber){
    	
    $sql1 = "INSERT INTO vehicleTbl(vehicleNumber, dateOfRegistration) VALUES ('$vehicleLicenseNumber', (SELECT now()))";
    $sql2 = "INSERT INTO ownerTbl(fname,lname,nrc,vehicleID) VALUES ('$vehicleOwnerFirstName','$vehicleOwnerLastName','$driverNrc',(SELECT max(id) FROM vehicleTbl))";
    $sql3 = "INSERT INTO driverTbl(fname,lname,nrc,licenseNumber,phoneNumber,vehicleID,employeeID) VALUES (?,?,?,?,?,(SELECT max(id) FROM vehicleTbl),(SELECT id from stationTbl s where s.name = ? ))";
    
    $result = mysqli_query($this->conn,$sql1);
    $result1 = mysqli_query($this->conn,$sql2);
    $result2 = mysqli_query($this->conn,$sql3);
    
    if($result ){
    
        $result1 = mysqli_query($this->conn,$sql2);
	$result2 = mysqli_query($this->conn,$sql3);
    
    
    	return $result = "it has been inserted";
     
    }else{
    
    	echo "error" . mysql_error();
    
    }
    
    
    }*/

    public function storeClient($name, $vehicleLicenseNumber, $vehicleOwnerFirstName, $vehicleOwnerLastName, $vehicleOwnerNumber, $driverFirstName, $driverLastName, $driverNrc, $driverLicenseNumber, $driverPhoneNumber)
    {

        $sql1 = "INSERT INTO vehicleTbl(vehicleNumber, dateOfRegistration) VALUES ('$vehicleLicenseNumber', (SELECT now()))";
        $sql2 = "INSERT INTO ownerTbl(fname,lname,nrc,vehicleID) VALUES ('$vehicleOwnerFirstName','$vehicleOwnerLastName','$driverNrc',(SELECT max(id) FROM vehicleTbl))";
        $sql3 = "INSERT INTO driverTbl(fname,lname,nrc,licenseNumber,phoneNumber,vehicleID,employeeID) VALUES ('$driverFirstName','$driverLastName', '$driverNrc', '$driverLicenseNumber','$driverPhoneNumber',(SELECT max(id) FROM vehicleTbl),(SELECT id from stationTbl s where s.name = '$name' ))";

        $result = mysqli_query($this->conn, $sql1);
        $result1 = mysqli_query($this->conn, $sql2);
        $result2 = mysqli_query($this->conn, $sql3);

        if ($result) {

            $result1 = mysqli_query($this->conn, $sql2);
            $result2 = mysqli_query($this->conn, $sql3);

            return $result = "it has been inserted";

        } else {

            echo "error" . mysql_error();

        }
    }


    //checks if user being registered is already in the system 
    public function isUserExisted($nrc)
    {
        $stmt = $this->conn->prepare("SELECT nrc FROM employeeTbl WHERE nrc = ?");

        $stmt->bind_param("s", $nrc);

        $stmt->execute();


        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // user existed 
            $stmt->close();
            return true;
        } else {
            // user not existed
            $stmt->close();
            return false;
        }


    }

    public function isDriverandOwnerExisted($driverNrc, $tableName)
    {

        $stmt = $this->conn->prepare("SELECT " . $driverNrc . " FROM" . $tableName . " WHERE" . $driverNrc . " = ?");
        $stmt->bind_param("ss", $driverNrc, $tableName);
        $stmt->execute();

        $stmt->store_result();

        if ($stmt->num_rows > 0) {

            $stmt->close();

            return true;
        } else {

            $stmt->close();
            return false;
        }


    }


    //user logs in usin the hash pin and username 
    public function getUserByPin($userName, $pin)
    {

        $user = array();

        $stmt = $this->conn->prepare("SELECT pin FROM employeeTbl WHERE userName = ?");
        $stmt->bind_param("s", $userName);

        if ($stmt->execute()) {


            $stmt->bind_result($user['pin']);

            while ($stmt->fetch()) {
                //verifying employee pin
                $encrypted_password = $user['pin'];

            }

            $stmt->close();

            $hash = md5($pin);

            if ($encrypted_password == $hash) {

                return $user;

                echo $hash;
            }

        } else {
            return NULL;
        }
    }


    /*
 methods gets all type of reports
 and the type of report is determined
 by the $type variable that comes from the app
 */
    public function report($type)
    {

        $report = array();
        $result = array();

        //19hrs reports
        if ($type == 1) {

            $sql = "SELECT d.fname, d.lname, d.nrc FROM driverTbl d, paymentTbl p, transactionTbl t, vehicleTbl v
	WHERE d.vehicleID=v.id AND t.PaymentID=p.id AND v.id=p.vehicleID AND timediff(CURRENT_TIME,'19:00:00')>0 AND datediff(NOW(),p.buyDate)
  	BETWEEN 0 AND 2
	GROUP BY p.id";

            $stmt = $this->conn->prepare($sql);

            if ($stmt->execute()) {

                $stmt->bind_result($fname, $lname, $nrc);

                while ($stmt->fetch()) {

                    $report['fname'] = $fname;
                    $report['lname'] = $lname;
                    $report['nrc'] = $nrc;

                    $result[] = $report;

                }

                $stmt->close();


                if (!empty($report)) {

                    echo json_encode($result);
                } else {

                    echo 'Report not found';

                }
            } else {

                return NULL;
            }

        }

        //saturday reports
        if ($type == 2) {

            $result = array();

            $sql = "SELECT d.fname, d.lname, d.nrc, count(*) AS `NumberofTransactions` FROM driverTbl d, paymentTbl p, vehicleTbl v
	WHERE d.vehicleID=v.id AND v.id=p.vehicleID
	GROUP BY d.nrc
	ORDER BY COUNT(*) DESC ";

            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute()) {

                $stmt->bind_result($fname, $lname, $nrc, $NumberofTransactions);

                while ($stmt->fetch()) {

                    $report['fname'] = $fname;
                    $report['lname'] = $lname;
                    $report['nrc'] = $nrc;
                    $report['NumberofTransactions'] = $NumberofTransactions;

                    $result[] = $report;

                }

                $stmt->close();

                if (!empty($result)) {

                    echo json_encode($result);
                } else {

                    echo 'Reports not found';

                }
            } else {

                return NULL;
            }


        }

        //defaulted reports
        if ($type == 3) {

            $result = array();
            $sql = "SELECT t.paymentID, d.fname, d.lname, d.nrc, p.amountDue, (p.amountDue-SUM(t.amount)) AS balamce
	FROM paymentTbl p,transactionTbl t, driverTbl d, vehicleTbl v
	WHERE p.id=t.PaymentID AND v.id=d.vehicleID AND p.vehicleID=v.id AND datediff(now(),p.buyDate)>=2
	GROUP BY t.paymentID
	HAVING (p.amountDue-SUM(t.amount))>0";

            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute()) {

                $stmt->bind_result($paymentID, $fname, $lname, $nrc, $amountDue, $balance);

                while ($stmt->fetch()) {

                    $report['paymentID'] = $paymentID;
                    $report['fname'] = $fname;
                    $report['lname'] = $lname;
                    $report['nrc'] = $nrc;
                    $report['amountDue'] = $amountDue;
                    $report['balance'] = $balance;

                    $result[] = $report;


                }

                $stmt->close();

                if (!empty($result)) {

                    echo json_encode($result);
                }
            } else {

                return NULL;
            }

        }

        //dormant reports
        if ($type == 4) {

            $result = array();
            $sql = "SELECT  DISTINCT d.fname, d.lname,d.nrc,p.buyDate, datediff(now(), p.buyDate) AS cdate
	FROM driverTbl d, vehicleTbl v, paymentTbl p
	WHERE d.vehicleID=v.id AND p.vehicleID=v.id AND p.vehicleID NOT IN(
   	SELECT p1.vehicleID
    	FROM paymentTbl p1
    	WHERE datediff(now(), p1.buyDate)<4)";

            $stmt = $this->conn->prepare($sql);
            if ($stmt->execute()) {

                $stmt->bind_result($fname, $lname, $nrc, $buydate, $date);
                while ($stmt->fetch()) {

                    $report['fname'] = $fname;
                    $report['lname'] = $lname;
                    $report['nrc'] = $nrc;
                    $report['butDate'] = $buydate;
                    $report['cdate'] = $date;

                    $result[] = $report;


                }
                $stmt->close();


                if (!empty($result)) {

                    echo json_encode($result);
                }
            } else {

                return NULL;
            }

        }


    }

    //gets reports of a specific client
    public function reportByNrc($nrc)
    {

        $report = array();
        $result = array();

        $stmt = $this->conn->prepare("SELECT d.fname, d.lname, d.nrc,p.id, p.amountDue, t.amount, t.transactionDate, p.buyDate
        FROM driverTbl d, paymentTbl p, transactionTbl t, vehicleTbl v
        WHERE d.vehicleID=v.id AND t.PaymentID=p.id AND v.id=p.vehicleID AND nrc=?");
        $stmt->bind_param("s", $nrc);

        if ($stmt->execute()) {


            $stmt->bind_result($fname, $lname, $nrc1, $id, $amountDue, $amount, $transactionDate, $buyDate);

            while ($stmt->fetch()) {

                $report['fname'] = $fname;
                $report['lname'] = $lname;
                $report['nrc'] = $nrc1;
                $report['id'] = $id;
                $report['amountDue'] = $amountDue;
                $report['amount'] = $amount;
                $report['transactionDate'] = $transactionDate;
                $report['buyDate'] = $buyDate;

                $result[] = $report;


            }

            $stmt->close();

            if (!empty($report)) {

                echo json_encode($result);
            } else {

                echo 'Reports not found';
            }

        } else {

            return NULL;
        }


    }


    public function storeStations($name, $fname, $lname, $phone)
    {


        $stmt = $this->conn->prepare("INSERT INTO stationTbl(name, chairman_fname, chairman_lname, chairman_phone) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name, $fname, $lname, $phone);

        if ($stmt->execute()) {

            echo "it has been inserted";
        } else {

            echo "Station has not been inserted";
        }


    }


    public function getStations()
    {

        $sql = "SELECT name FROM stationTbl";


        $result = mysqli_query($this->conn, $sql);

        $res = array();

        if ($result) {

            while ($row = mysqli_fetch_array($result)) {
                array_push($res, array("name" => $row['name']));
            }
        } else {

            echo 'result not found';
        }

        echo json_encode($res);

    }


    /**
     * Encrypting pin
     * @param pin
     * returns salt and encrypted pin
     */
    public function hashSSHAb($pin)
    {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($pin . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }


    /**
     * Decrypting pin
     * @param salt , pin
     * returns hash string
     */
    public function checkhashSSHA($salt, $pin)
    {

        $hash = base64_encode(sha1($pin . $salt, true) . $salt);

        return $hash;
    }


    public function checkNewUser($nrc)
    {
        $user = array();
        $result = array();

        $stmt = $this->conn->prepare("SELECT COUNT(p.id) AS count FROM paymentTbl p WHERE p.vehicleID IN(SELECT d.vehicleID FROM driverTbl d WHERE d.nrc=?)");
        $stmt->bind_param("s", $nrc);

        if ($stmt->execute()) {

            $stmt->bind_result($count);

            while ($stmt->fetch()) {

                $user['count'] = $count;

            }

            $stmt->close();
            if (!empty($user)) {

                //echo json_encode($user);

                if (current($user) > 0) {

                    $this->getCreditPayment($nrc);

                }

                if (current($user) == 0) {

                    $stmt = $this->conn->prepare("SELECT fname,lname,(SELECT 0) AS Balance FROM driverTbl WHERE nrc=?");
                    $stmt->bind_param("s", $nrc);

                    if ($stmt->execute()) {

                        $stmt->bind_result($fname, $lname, $balance);

                        while ($stmt->fetch()) {

                            $user['fname'] = $fname;
                            $user['lname'] = $lname;
                            $user['balance'] = $balance;

                            $result[] = $user;

                        }

                    }
                    $stmt->close();

                    if(!empty($result)){

                       echo json_encode($result);
                    }

                }

            } else {

                echo 'User not found';
            }

        } else {

            return NULL;
        }


    }


}

?>