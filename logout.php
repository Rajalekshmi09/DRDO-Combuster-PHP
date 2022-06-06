<?php header('Access-Control-Allow-Origin: *');
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php
error_reporting(0);

$modbus = new ModbusMaster();
$modbus->connect();

wh_log("logout.php -->  logoutClicked = 1");

//write 1 in logout bit - 11 reg
$data2 = array(1);
$modbus->writeMultipleRegister(0, 11, $data2, "UINT");

print json_encode($modbus);

$modbus->disconnect();
$modbus = NULL;

?>

