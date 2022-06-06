<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require 'db_connectionPDO.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php
//received testid from frontend react using POST
$get_data = json_decode(file_get_contents("php://input"));
$test_id  = isset($get_data) ? $get_data->testId : 0;

if ($test_id == 0 ) {
  return;
}
error_reporting(0);

$modbus = new ModbusMaster();
$modbus->connect();

//to check the communication status in frond end
print json_encode($modbus);


//$modbus->writeMultipleRegister(0, 11, $data, "UINT");//11 - logoutbit 

//11 - logout bit
//12 - E.shutdown error code set to 0
$data = array(0, 0); 
$modbus->writeMultipleRegister(0, 11, $data, "UINT,UINT"); //11, 12

 //94,95 - FCV (Air)  96,97 - FCV(Kerosene Fuel)
$data = array(0, 1, 0, 1); //94,95 - FCV (Air)  96,97 - FCV(Kerosene Fuel)
$modbus->writeMultipleRegister(0, 93, $data, "UINT,UINT,UINT,UINT"); //93-int, 94-divisor

//16 - preTest
$data = array(0); 
$modbus->writeMultipleRegister(0, 16, $data, "UINT");//16 - preTest

// //79 - test id bit to set current testid   
// $testidData = array($test_id);
// $modbus->writeMultipleRegister(0, 79, $testidData,  "INT");

//write 1 in 0'th register to indicate initialize operation
$data2 = array(1);
$modbus->writeMultipleRegister(0, 0, $data2, "UINT");

while (1) {

  $InitializeCompleted = $modbus->readMultipleRegisters(1, 0, 1);
  
  //Note:  for testcommands, the testid should not be 0
  if ($InitializeCompleted[1] == 1) {

    //remove old data in db with testid=0
    $insertCmd  = "DELETE FROM enertek_combuster.testdata WHERE test_id = 0; ";
    $insertCmd .=  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`) 
                    VALUES('$test_id','Communication','C1','C','',now());";
    $insertCmd .= "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
                   VALUES('$test_id','Initialize Started','C2','C','1',now());";
    $insertCmd .= "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
                  VALUES('$test_id','Initialize Completed','S1','S','1',now())";

    $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
    if (!$conn) {
      die("turboconfig : connection faild :" . $conn->connect_error);
    }
    $conn->query( $insertCmd, PDO::FETCH_ASSOC);
    $conn = NULL;

    //mysqli_multi_query($conn,  $inserttestData);
    
    break;
  }
}

$modbus->disconnect();
$modbus = NULL;

// mysqli_close($conn);
// $conn = NULL;
?>