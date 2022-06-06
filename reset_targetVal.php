<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require 'db_connectionPDO.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php

$data = json_decode(file_get_contents("php://input"));
$ResetTemp  = isset($data) ? $data->ResetTemp : 0;
$ResetRPM  = isset($data) ? $data->ResetRPM : 0;
$test_id  = isset($data) ? $data->testId : 0;

if($test_id  == 0)
  return;

$modbus = new ModbusMaster();
$modbus->connect();

// $conn = $db_conn;
// if (!$conn) {
//   die("connection faild:" . $conn->connect_error);
// }
error_reporting(0);

//write targer Temp in - 4 reg
$data = array($ResetTemp);
$modbus->writeMultipleRegister(0, 4, $data, "UINT");

//since rpm is big value , it is parsed into 4 values 
//and written in 4 registers starts from 90. 
//PLC will add these 4 values and store in 6th register 
$target_rpmdatatypes = array("UINT", "UINT", "UINT", "UINT");
$ar = unpack("C*", pack("L", $ResetRPM));
$modbus->writeMultipleRegister(0, 90, $ar, $target_rpmdatatypes);

$modbus->disconnect();
$modbus = NULL;

$sql = 
  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`value`)
  VALUES('$test_id','Reset Values','C10','C',CURRENT_TIME(),'$ResetTemp, $ResetRPM')";

$conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
if (!$conn) {
  die("turboconfig : connection faild :" . $conn->connect_error);
}
$conn->query( $sql);
$conn = NULL;

?>

