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
  $Key  = isset($data) ? $data->keyValue : 0;
  $Status = isset($data) ? $data->status : 0;
  
  $data1 = array(0);
  if($Status === true){
    $data1 = array($Key);
  }
  elseif($Status === false){
    $data1 = array(0);
  }
 
  $modbus = new ModbusMaster();
  $modbus->connect();
 
  //16 - preTest
  $modbus->writeMultipleRegister(0, 16, $data1, "UINT");

  // print json_encode($modbus);

  $modbus->disconnect();
  $modbus = NULL;

?>

