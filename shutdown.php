<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
include_once 'db_connectionPDO.php';
?>


<?php
$modbus = new ModbusMaster();
$modbus->connect();

$data = array(3);
$modbus->writeMultipleRegister(0, 0, $data, "INT");

//writting 0 in finetuneCV and fueltuneCv
$data_array = array(0, 1,0,1); ////93, 94
$target_datatypes = array("UINT","UINT","UINT","UINT");
$modbus->writeMultipleRegister(0, 93, $data_array, $target_datatypes); //93, 94, 95,96
//$modbus->writeMultipleRegister(0, 95, $data_array, $target_datatypes); //95, 96

$test_id = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 79, 1));//testid
$modbus->disconnect();
print json_encode($modbus);

$conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
if (!$conn) {
  die("connection faild:" . $conn->connect_error);
}

$sqlquery_shutdown = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
VALUES('$test_id','N.Shutdown Initiated','C9','S',CURRENT_TIME(),'')"; 

$conn->query( $sqlquery_shutdown);
$conn = NULL;
?>

