<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include_once 'db_connectionPDO.php';
require 'log.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
?> 

<?php
$data = json_decode(file_get_contents("php://input"));
$test_id  = isset($data) ? $data->testId : 0; 
// $TargetRPM  = isset($data) ? $data->targetRPM : 0;
// $TargetTemp  = isset($data) ? $data->targetTemp : 0;
$Air_initVal  = isset($data) ? $data->initialAirFCV : 0; 
$Kerosene_initVal  = isset($data) ? $data->initialkeroseneFCV : 0;  

if ($test_id == 0 ) {
  return;
}

error_reporting(0);

$modbus = new ModbusMaster();
$modbus->connect();

//reading stage1temp, stage2temp, stage3rpm from db to send PLC 
$conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
if (!$conn) {
  die("connection faild:" . $conn->connect_error);
}

$sql  = "SELECT * FROM `testparamconfig`";
$result  = $conn->query( $sql, PDO::FETCH_ASSOC);
$rows  = array();
foreach($result as $r)
{
  array_push($rows, $r);
}
if ($rows[0]['testparamname'] == "Stage 1 Temperature")
  $stage1Temp = $rows[0]['testparamvalue'];
else if ($rows[1]['testparamname'] == "Stage 1 Temperature")
  $stage1Temp = $rows[1]['testparamvalue'];
else if ($rows[2]['testparamname'] == "Stage 1 Temperature")
  $stage1Temp = $rows[2]['testparamvalue'];

if ($rows[0]['testparamname'] == "Stage 2 Temperature")
  $stage2Temp = $rows[0]['testparamvalue'];
else if ($rows[1]['testparamname'] == "Stage 2 Temperature")
  $stage2Temp = $rows[1]['testparamvalue'];
else if ($rows[2]['testparamname'] == "Stage 2 Temperature")
  $stage2Temp = $rows[2]['testparamvalue'];

if ($rows[0]['testparamname'] == "Stage 3 RPM")
  $stage3RPM = $rows[0]['testparamvalue'];
else if ($rows[1]['testparamname'] == "Stage 3 RPM")
  $stage3RPM = $rows[1]['testparamvalue'];
else if ($rows[2]['testparamname'] == "Stage 3 RPM")
  $stage3RPM = $rows[2]['testparamvalue'];


//write in plc register
// 40001 - stage 1 temp 
// 40002 - stage 2 temp 
// 40003 - stage 3 rpm 
// 40004 - target temp 
if ($stage1Temp == NULL) $stage1Temp = 0;
if ($stage2Temp == NULL) $stage2Temp = 0;
if ($stage3RPM == NULL) $stage3RPM = 0;
// if ($TargetTemp == NULL) $TargetTemp = 0;
// if ($TargetRPM == NULL) $TargetRPM = 0;

$target_data = array($stage1Temp, $stage2Temp, $stage3RPM);
$target_datatypes = array("UINT", "UINT", "UINT");
$modbus->writeMultipleRegister(0, 1, $target_data, $target_datatypes);

// //convert target RPM into 4 different integers ,write the 4 intergers in 90,91,92,93 registers
// // PLC will convert this 4 integers into RPM & store it in 6 th reg
// $target_rpmdatatypes = array("UINT", "UINT", "UINT", "UINT");
// $ar = unpack("C*", pack("L", $TargetRPM));
// $modbus->writeMultipleRegister(0, 90, $ar, $target_rpmdatatypes);

//////////////////

$number = 0;
$divisor = 1;

//finding the position of the .  and get the length after dot
//eg: 3 --> 3, 1, 3/1 = 3
//eg:  3.4 -->  34, 10,   34/10=3.4
//eg:  3.45 --> 345, 100,  345/100 = 3.45
$SV_Data = array($Air_initVal);
$length = strlen(substr(strrchr($SV_Data[0], "."), 0)); //length includes . position also

if ($length == 0) {
  //no decimal part
  $number = $SV_Data[0];
  $divisor = 1;
} else {
  $number = $SV_Data[0] * pow(10,  $length - 1);
  $divisor = pow(10, $length - 1);
}

$number_array = array($number, $divisor);
$modbus->writeMultipleRegister(0, 93, $number_array, "UINT, UINT"); //93, 94

//writing maingas initial value
$SV_Data = array($Kerosene_initVal);
$length = strlen(substr(strrchr($SV_Data[0], "."), 0)); //length includes . position also

if ($length == 0) {
  //no decimal part
  $number = $SV_Data[0];
  $divisor = 1;
} else {
  $number = $SV_Data[0] * pow(10,  $length - 1);
  $divisor = pow(10, $length - 1);
}
$number_array = array($number, $divisor);
$modbus->writeMultipleRegister(0, 95, $number_array, "UINT, UINT"); //95, 96

//////////////////

$inserttestData =  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,
                    `value`,`testcommandsTime`)VALUES($test_id,'stage1temp','C3','C','$stage1Temp',
                    now());";
$inserttestData .= "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,
                    `value`,`testcommandsTime`) VALUES($test_id,'stage2temp','C4','C','$stage2Temp',
                    now());";
$inserttestData .=  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,
                    `value`,`testcommandsTime`)VALUES($test_id,'stage3rpm','C5','C','$stage3RPM',
                    now());";
// $inserttestData .=  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,
//                     `value`,`testcommandsTime`) VALUES($test_id,'targettemp','C6','C','$TargetTemp',
                    // now());";
// $inserttestData .= "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,
//                     `value`,`testcommandsTime`) VALUES($test_id,'targetrpm','C7','C','$TargetRPM',
                    // now());";
$inserttestData .= "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,
                    `value`,`testcommandsTime`) VALUES($test_id,'initialComprAir_openingvalue','C6','C','$Air_initVal',
                    now());";
$inserttestData .=  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,
                    `value`,`testcommandsTime`) VALUES($test_id,'initialMainGas_openingvalue','C7','C','$Kerosene_initVal',
                    now());";
$inserttestData .=  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,
                    `value`,`testcommandsTime`) VALUES($test_id,'Start Initiated','C8','C','1',
                    now())";

$conn->query( $inserttestData);
//mysqli_multi_query($conn,  $inserttestData);

//79 - test id bit to set current testid   
$testidData = array($test_id);
$modbus->writeMultipleRegister(0, 79, $testidData,  "UINT");

//write 2 in 0th register indicate start initiate
$data1 = array(2);
$modbus->writeMultipleRegister(0, 0, $data1, "UINT");

//read 0th register to check start completed or not
// $StartCompleted = $modbus->readMultipleRegisters(1, 0, 1);
// print json_encode($StartCompleted[1]);

$modbus->disconnect();
$modbus = NULL;
//mysqli_close($conn);
$conn = NULL;
?>