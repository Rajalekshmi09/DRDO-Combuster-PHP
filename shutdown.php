<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
?>


<?php
$modbus = new ModbusMaster();
$modbus->connect();

$data = array(3);
$modbus->writeMultipleRegister(0, 0, $data, "INT");

//set 0 in test_id at 79 register
$data_array = array(0);
$modbus->writeMultipleRegister(0, 79, $data_array, "INT");

//writting 0 in finetuneCV and fueltuneCv
$data_array = array(0, 1,0,1); ////93, 94
$target_datatypes = array("UINT","UINT","UINT","UINT");
$modbus->writeMultipleRegister(0, 93, $data_array, $target_datatypes); //93, 94, 95,96
//$modbus->writeMultipleRegister(0, 95, $data_array, $target_datatypes); //95, 96

$modbus->disconnect();

print json_encode($modbus);
?>
