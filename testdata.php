
<?php header('Access-Control-Allow-Origin: *');
require 'db_connection.php';
require 'constant.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>
<?php

$conn = $db_conn;
if (!$conn) {
  die("connection faild:" . $conn->connect_error);
  return;
}

  //retriving testid from db
	$sql  = "SELECT test_id from enertek_combuster.test  order by test_id desc limit 1";
	$result  = mysqli_query($conn,$sql);
	if(!$result){
	    wh_log("Test Data : " . $db_conn -> error);
    }
  $rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);			
		}
		$test_id = $rows[0]['test_id'];
		wh_log("Initialize (PLC to Code) : Started");
	}

  if($test_id == 0)
  {
    mysqli_close($conn);
    $conn = NULL;
    return;
  } 

  $modbus = new ModbusMaster();
  $modbus->connect();

  $nshutdownInit = 0;
  $initiatecompleted = 0;//49
  $startcompleted = 0;//50
  $nshutdowncompleted = 0;//51
  $ignite =  0;//59
  $gasopened = 0; //60
  $stage1 = 0; //61
  $fuelopened = 0; //62
  $stage2 =  0; //63
  $gasclosed = 0; //64
  $stage3 =  0;//65
  $eshutdowninit =  0; //66
  $eshutdownComp =  0;//67
  $SV1_flame_Air = 0;//69
  $SV2_FuelIject_Air = 0; //70
  $SV3_Pilotflame_gas = 0; //71
  $SV4_1inch_Bypass = 0;//74
  $SV5_2inch_Bypass = 0;//75
  $SV6_Ignitor_Switch = 0;//76
  $SV7_Kerosene_pump =  0; //77
  $SV8_Lubeoil_Pump =  0; //78
 
  //count is used to  maintain to display under START
  //count1 is used to maintain to display under Shutdown
  $count = 1;
  $Count1 = 1;

while(1){
  $nshutdownInit = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 0, 1));

  $sensorData = $modbus->readMultipleRegisters(0, 49, 32);
  $Temp = array_chunk($sensorData, 2);

  $initiatecompleted = PhpType::bytes2signedInt($Temp[0]);//49
  $startcompleted = PhpType::bytes2signedInt($Temp[1]);//50
  $nshutdowncompleted = PhpType::bytes2signedInt($Temp[2]);//51

  $ignite = PhpType::bytes2signedInt($Temp[10]); //59
  $gasopened =PhpType::bytes2signedInt($Temp[11]); //60
  $stage1 = PhpType::bytes2signedInt($Temp[12]); //61
  $fuelopened =PhpType::bytes2signedInt($Temp[13]); //62
  $stage2 = PhpType::bytes2signedInt($Temp[14]); //63
  $gasclosed = PhpType::bytes2signedInt($Temp[15]); //64
  $stage3 = PhpType::bytes2signedInt($Temp[16]); //65
  $eshutdowninit = PhpType::bytes2signedInt($Temp[17]); //66
  $eshutdownComp =  PhpType::bytes2signedInt($Temp[18]);//67

  //should be in the same order because it is going to display in help button
  $SV1_flame_Air =PhpType::bytes2signedInt($Temp[20]);  //69
  $SV2_FuelIject_Air = PhpType::bytes2signedInt($Temp[21]); //70
  $SV3_Pilotflame_gas = PhpType::bytes2signedInt($Temp[22]); //71
  $SV4_1inch_Bypass = PhpType::bytes2signedInt($Temp[26]); //74
  $SV5_2inch_Bypass =PhpType::bytes2signedInt($Temp[27]); //75
  $SV6_Ignitor_Switch =PhpType::bytes2signedInt($Temp[28]); //76
  $SV7_Kerosene_pump = PhpType::bytes2signedInt($Temp[29]); //77
  $SV8_Lubeoil_Pump = PhpType::bytes2signedInt($Temp[30]); //78   
  $SV9_Eshut_errcode = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 12, 1));

  $valves = [$SV1_flame_Air,$SV2_FuelIject_Air,$SV3_Pilotflame_gas,$SV4_1inch_Bypass,$SV5_2inch_Bypass,
  $SV6_Ignitor_Switch,$SV7_Kerosene_pump,$SV8_Lubeoil_Pump,$SV9_Eshut_errcode];

  $valves = implode(',', $valves);
 
    //// shutdown process - start ////////////
   //N.shutdown cases handled with priority
   if ($nshutdownInit == 3 && $Count1 == 1) {
    $inserttestData = mysqli_query($conn, "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`value`,`type`,`testcommandsTime`,`valvestatus`)
     VALUES('$test_id','N.Shutdown Initiated','S10','$nshutdownInit','S',CURRENT_TIME(),'$valves')");
    $Count1++;
  }

  if ($nshutdowncompleted == 1) {
    $inserttestData = mysqli_query($conn, "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`value`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','N.Shutdown Completed','S10','$nshutdowncompleted','S',CURRENT_TIME(),'$valves')");
    // $modbus->disconnect();
    break;
  }

  //e.Shutdwon eror cases handled here
  if ($eshutdowninit == 1 && $Count1 == 1) {
    $inserttestData = mysqli_query($conn, "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','E.Shutdown Initiated','S11','S',CURRENT_TIME(),'$valves')");
    $Count1++;
  }

  if ($eshutdownComp == 1) {
    $inserttestData = mysqli_query($conn, "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','E.Shutdown Completed','S11','s',CURRENT_TIME(),'$valves')");
    // $modbus->disconnect();
    break;
  }
  //// shutdown process - end ////////////

  if($initiatecompleted == 1 && $count == 1){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','Initialize Completed','S1','s',CURRENT_TIME(),'$valves')");
    $count++;
  }

  if($startcompleted == 1 && $count == 2){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`)
    VALUES('$test_id','Start Completed','S2','s',CURRENT_TIME(),'$valves')");
    $count++;
  }
  if($ignite == 1 && $count == 3){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','Ignite','S3','s',CURRENT_TIME(),'$valves')");
    $count++;
  }
  if($gasopened == 1 && $count == 4){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','Gas Opened','S4','s',CURRENT_TIME(),'$valves')");
    $count++;
  }
  if($stage1 == 1 && $count == 5){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','Stage1','S5','s',CURRENT_TIME(),'$valves')");
    $count++;
  }
  if($fuelopened == 1 && $count == 6){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','Fuel Opened','S6','s',CURRENT_TIME(),'$valves')");
    $count++;
  }
  if($stage2 == 1 && $count == 7){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','Stage2','S7','s',CURRENT_TIME(),'$valves')");
    $count++;
  }
  if($gasclosed == 1 && $count == 8){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','Gas Closed','S8','s',CURRENT_TIME(),'$valves')");
    $count++;
  }
  if($stage3 == 1 && $count == 9){
    $inserttestData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`testcommandsTime`,`valvestatus`) 
    VALUES('$test_id','Stage3','S9','s',CURRENT_TIME(),'$valves')");
    $count++;
  }

  if($logFileConstant == 2){
  $DATA = strval($initiatecompleted) . ','. strval($startcompleted). ','.strval($nshutdowncompleted).
  ','.strval($ignite). ','.strval($gasopened). ','.strval($stage1) . ','.strval($fuelopened).
  ','. strval($stage2) . ','. strval($gasclosed) . ','. strval($stage3) . ','. strval($eshutdowninit).
  ','.strval($SV1_flame_Air) . ','. strval($SV2_FuelIject_Air) . ','. strval($SV3_Pilotflame_gas) . 
  ','. strval($SV4_1inch_Bypass) . ','. strval($SV5_2inch_Bypass). ','. strval($SV6_Ignitor_Switch).
  ','. strval($SV7_Kerosene_pump) . ','. strval($SV8_Lubeoil_Pump) .
  ','. strval($SV9_Eshut_errcode);

      wh_log("Command And Valve Status :" . $DATA);
  }
  sleep(2); // in sec
}//end of while loop


$modbus->disconnect();
$modbus = NULL;

// mysqli_close($conn);
// $conn = NULL;
?>
