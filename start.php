<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require 'db_connection.php';
require 'log.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
?> 

<?php
  $data = json_decode(file_get_contents("php://input"));
  $test_id  = isset($data) ? $data->testId : 0; 
  $TargetRPM  = isset($data) ? $data->targetRPM : 0;
  $TargetTemp  = isset($data) ? $data->targetTemp : 0;
  $Airsv_initVal  = isset($data) ? $data->initialAirFCV : 0; 
  $Kerosene_initVal  = isset($data) ? $data->initialkeroseneFCV : 0; 

  if($test_id == 0 || $TargetRPM  == 0 || $TargetTemp  == 0 ){
    return;
  }

  $conn = $db_conn;
  if(!$conn){
    die("connection faild:" .$conn-> connect_error);
    return;
  }

  $modbus = new ModbusMaster();
  $modbus->connect();

  //reading stage1temp, stage2temp, stage3rpm from db to send PLC
  $sql  = "SELECT * FROM `testparamconfig`";
  $result  = mysqli_query($conn,$sql);
  if(!$result){
      wh_log("Start : " . $db_conn -> error);
    }
  $rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		$stage1Temp = $rows[0]['testparamvalue'];
		$stage2Temp = $rows[1]['testparamvalue'];
		$stage3RPM = $rows[2]['testparamvalue'];		

	}

  //dont forget to give no of blades
  //to check with naveen - Raji
  if($stage1Temp == NULL) $stage1Temp = 0;
  if($stage2Temp == NULL) $stage2Temp = 0;
  if($stage3RPM == NULL) $stage3RPM = 0;
  if($TargetTemp == NULL) $TargetTemp = 0;
  if($TargetRPM == NULL) $TargetRPM = 0;


	//dont forget to give no of blades
  $target_data = array($stage1Temp,$stage2Temp,$stage3RPM,$TargetTemp);
  $target_datatypes = array("UINT","UINT","UINT","UINT");
  $modbus->writeMultipleRegister(0, 1, $target_data, $target_datatypes);

  
//unpack RPM is implemented for long datatype RPM
  $data1 = $TargetRPM;
  $ar = unpack("C*", pack("L", $data1));
  $modbus->writeMultipleRegister(0, 90, $ar, "UINT");	

//writting Flow Air Control valve value,
  $number = 0;
  $digits = 0;

  //finding the position of the .  and get the length after dot
  $length = strlen(substr(strrchr( $Airsv_initVal, "."), 0));

  if($length == 0)
  {
    //no decimal part
    $number = $Airsv_initVal;
    $digits = 1;
  }
  else if($length == 2)
  {
    //after decimal - one digit
    $number = $Airsv_initVal * 10;   
    $digits = 10;
  }
  else if($length == 3)
  {
    //after decimal - 2 digits
    $number = $Airsv_initVal * 100;
    $digits = 100;
  }

  $target_data = array($number, $digits); //93, 94
  $target_datatypes = array("UINT","UINT");
  $modbus->writeMultipleRegister(0, 93, $target_data, $target_datatypes);

  //writing kerosene value 
  $number = 0;
  $digits = 0;

  //finding the position of the .  and get the length after dot
  $length = strlen(substr(strrchr( $Kerosene_initVal, "."), 0));

  if($length == 0)
  {
    //no decimal part
    $number = $Kerosene_initVal;
    $digits = 1;
  }
  else if($length == 2)
  {
    //after decimal - one digit
    $number =$Kerosene_initVal * 10;   
    $digits = 10;
  }
  else if($length == 3)
  {
    //after decimal - 2 digits
    $number = $Kerosene_initVal * 100;
    $digits = 100;
  }
  
  $target_data = array($number, $digits); //95, 96
  $target_datatypes = array("UINT","UINT");
  $modbus->writeMultipleRegister(0, 95, $target_data, $target_datatypes);

  // $inserttestData = mysqli_query($conn,"INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
  // VALUES('$test_id','Reset Values','S12','s',CURRENT_TIME(),'11')");

  $sql_query =  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'stage1temp','C3','C','$stage1Temp',now());";
  $sql_query .=  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'stage2temp','C4','C','$stage2Temp',now());";
  $sql_query .=    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'stage3rpm','C5','C','$stage3RPM',now());";
  $sql_query .=   "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'targettemp','C6','C','$TargetTemp',now());";
  $sql_query .=   "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'targetrpm','C7','C','$TargetRPM',now());";
  $sql_query .=  "INSERT INTO `enertek_combuster_goa`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'initialAirCV_openingvalue','C7_2','C','$Airsv_initVal',now());";
  $sql_query .= "INSERT INTO `enertek_combuster_goa`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'initialkeroseneCV_openingvalue','C7_3','C','$Kerosene_initVal',now());";
  $sql_query .= "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'numofblades','C8','C','',now());";
 $sql_query .= "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
  VALUES($test_id,'Start Initiated','C9','C','1',now())";

  mysqli_multi_query($conn,  $sql_query);
  
  //nozzlearea
  // $data2 = array(1);
  // $modbus->writeMultipleRegister(0, 10,$data2 , "INT");	

  //write 2 in 0th register to indicate START action
  $data2 = array(2);
  $modbus->writeMultipleRegister(0, 0, $data2, "INT");

  // $StartCompleted = $modbus->readMultipleRegisters(1, 0, 1);
  // print json_encode($StartCompleted[1]);

  $modbus->disconnect();


?>


