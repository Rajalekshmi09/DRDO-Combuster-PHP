<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php
$data = json_decode(file_get_contents("php://input"));
$ResetTemp  = isset($data) ? $data->ResetTemp : 0; 
$ResetRPM  = isset($data) ? $data->ResetRPM : 0; 
$test_id  = isset($data) ? $data->testId : 0; 

if($test_id == 0 ){
  return;
}

$modbus = new ModbusMaster();
$modbus->connect();

//write reset temp , rpm in plc
if($ResetTemp == NULL) $ResetTemp = 0;
if($ResetRPM == NULL) $ResetRPM = 0;

$data = array($ResetTemp);
$modbus->writeMultipleRegister(0, 4, $data, "UINT");

//unpack RPM is implemented for long datatype RPM
$data3 = $ResetRPM;
$ar = unpack("C*", pack("L", $data3));
$modbus->writeMultipleRegister(0, 90, $ar, "UINT");

$modbus->disconnect();
$modbus = NULL;

$conn = $db_conn;

	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);
    return;
	}

$inserttestData = mysqli_query($conn,
"INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`value`)
 VALUES('$test_id','Reset Values','C10','C',CURRENT_TIME(),'$ResetTemp, $ResetRPM')");
?>

