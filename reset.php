<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php
$modbus = new ModbusMaster();
$modbus->connect();

wh_log("reset.php -->  logoutClicked = 1");

//write 1 in logout bit - 11 reg
$data2 = array(1);
$modbus->writeMultipleRegister(0, 11, $data2, "UINT");

$data = array(0);
$modbus->writeMultipleRegister(0, 0, $data, "UINT");

print json_encode($modbus);

$modbus->disconnect();
$modbus = NULL;

?>

