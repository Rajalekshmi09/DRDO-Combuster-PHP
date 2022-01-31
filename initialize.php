<?php 
header("Access-Control-Allow-Origin: *");
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
  $test_id  = isset($data) ? $data->testId : 0;
  
  // if($test_id == 0 
  if($test_id == 0 ){
    return;
  }

  error_reporting(0);
  $conn = $db_conn;

	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);
    return;
	}

  $modbus = new ModbusMaster();

  $modbus->connect();
  print json_encode($modbus);

  //41 - logout bit set 0
  //42 - E.shutdown error code set to 0
  //93 - E.Shutdown completed  bit is set to 0
  $data_array = array(0);
  $modbus->writeMultipleRegister(0, 11, $data_array, "UINT");
  $modbus->writeMultipleRegister(0, 12, $data_array, "UINT");
  $modbus->writeMultipleRegister(0, 66, $data_array, "UINT");

  //Write the testid in PLC register to use by software
  $data_array = array((int)$test_id);
  $modbus->writeMultipleRegister(0, 79, $data_array, "UINT");

  $data = array(1);
  $modbus->writeMultipleRegister(0, 0, $data, "INT");

  while(1){
    $InitializeCompleted = $modbus->readMultipleRegisters(1, 0, 1);

    if ($InitializeCompleted[1] == 1) {
          $sql_query =  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
          VALUES('$test_id','Communication','C1','C','',now());";        
          $sql_query .=  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
          VALUES('$test_id','Initialize Started','C2','C','1',now())"; 

          mysqli_multi_query($conn,  $sql_query); 
          break;
    }
    
  }
  // print json_encode($InitializeCompleted);
  $modbus->disconnect();

?>

