<?php header('Access-Control-Allow-Origin: *');
require 'db_connection.php';
require 'constant.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php
$modbus = new ModbusMaster();
$modbus->connect();

$conn = $db_conn;
	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);
    return;
	}

	function hex2float($strHex) {
    $hex = sscanf($strHex, "%02x%02x%02x%02x%02x%02x%02x%02x");
    $bin = implode('', array_map('chr', $hex));
    $array = unpack("Gnum", $bin);
    return $array['num'];
  }

while(1){
    $test_id = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 79, 1));

  //sensor value read
    $sensorData = $modbus->readMultipleRegisters(0,19, 30);
    $valveData = $modbus->readMultipleRegisters(0, 81, 6);
  
    $TempData = array_slice($sensorData,0,14);
    $arr = array_slice($sensorData,14,32);
    $Temp = array_chunk($TempData, 2);
    $T1 =PhpType::bytes2signedInt($Temp[0]);   
    $T2 = PhpType::bytes2signedInt($Temp[1]);    
    $T3 = PhpType::bytes2signedInt($Temp[2]);    
    $T4 =PhpType::bytes2signedInt($Temp[3]);   
    $T5 =PhpType::bytes2signedInt($Temp[4]); 
    $T11 = PhpType::bytes2signedInt($Temp[5]);


    //real Values
      $pr = array_chunk($arr, 4);    

      $b = $pr[0];
      $a = dechex($b[0]);
      for ($x = 1; $x < count($b); $x++) {
        if(strlen(dechex($b[$x])) <= 1)
          $a = $a . "0". dechex($b[$x]);
          else
          $a = $a . dechex($b[$x]);
      }
      $float = hex2float($a);
      $P1 =  round($float,1);
      
      $b = $pr[1];
      $a = dechex($b[0]);
      for ($x = 1; $x < count($b); $x++) {
        if(strlen(dechex($b[$x])) <= 1)
          $a = $a . "0". dechex($b[$x]);
          else
          $a = $a . dechex($b[$x]);
      }
      $float = hex2float($a);
      $P2 =  round($float,1);
      
      $b = $pr[2];
      $a = dechex($b[0]);
      for ($x = 1; $x < count($b); $x++) {
        if(strlen(dechex($b[$x])) <= 1)
          $a = $a . "0". dechex($b[$x]);
          else
          $a = $a . dechex($b[$x]);
      }
      $float = hex2float($a);
      $P3 =  round($float,1);
    
      $b = $pr[3];
      $a = dechex($b[0]);
      for ($x = 1; $x < count($b); $x++) {
        if(strlen(dechex($b[$x])) <= 1)
          $a = $a . "0". dechex($b[$x]);
          else
          $a = $a . dechex($b[$x]);
      }
      $float = hex2float($a);
      $P4=  round($float,1);
    
      $b = $pr[4];
      $a = dechex($b[0]);
      for ($x = 1; $x < count($b); $x++) {
        if(strlen(dechex($b[$x])) <= 1)
          $a = $a . "0". dechex($b[$x]);
          else
          $a = $a . dechex($b[$x]);
      }
      $float = hex2float($a);
      $P5 =  round($float,1);
      
      $b = $pr[5];
      $a = dechex($b[0]);
      for ($x = 1; $x < count($b); $x++) {
        if(strlen(dechex($b[$x])) <= 1)
          $a = $a . "0". dechex($b[$x]);
          else
          $a = $a . dechex($b[$x]);
      }
      $float = hex2float($a);
      $P6 =  round($float,1);
    
      $b = $pr[6];
      $a = dechex($b[0]);
      for ($x = 1; $x < count($b); $x++) {
        if(strlen(dechex($b[$x])) <= 1)
          $a = $a . "0". dechex($b[$x]);
          else
          $a = $a . dechex($b[$x]);
      }
      $float = hex2float($a);
      $P7 =  round($float,1);
      
      $b = $pr[7];
      $a = dechex($b[0]);
      for ($x = 1; $x < count($b); $x++) {
        if(strlen(dechex($b[$x])) <= 1)
          $a = $a . "0". dechex($b[$x]);
          else
          $a = $a . dechex($b[$x]);
      }
      $float = hex2float($a);
      $FFR =  round($float,1);


    $ar = $modbus->readMultipleRegisters(0, $rpmc, 2);   
    $rpm1 = ($ar[0]<<24) + ($ar[1]<<16) + ($ar[2]<<8) + $ar[3];

   
    $pr2 = array_chunk($valveData, 4);
    $b = $pr2[0];
    $a = dechex($b[0]);  
    for ($x = 1; $x < count($b); $x++) {
      if(strlen(dechex($b[$x])) <= 1)
        $a = $a . "0". dechex($b[$x]);
        else
        $a = $a . dechex($b[$x]);       
     }
    $float = hex2float($a);
    $AirFCV =  round($float, 2);
    
    $b = $pr2[1];
    $a = dechex($b[0]);
    for ($x = 1; $x < count($b); $x++) {
      if(strlen(dechex($b[$x])) <= 1)
      $a = $a . "0". dechex($b[$x]);
      else
      $a = $a . dechex($b[$x]);      
    }
    $float = hex2float($a);
    $FuelFCV =  round($float, 2);


    $eshutdownInit = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 66, 1));
    $eshutdowncomp = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 67, 1));
    $logoutClicked = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 11, 1));


    $insertData = mysqli_query($conn,
    "INSERT INTO `enertek_combuster`.`testdata`(`test_id`,`rpm`,`T1`,`T2`,`T3`,`T4`,`T5`,
    `T11`,`P1`,`P2`,`P3`,`P4`,`P5`,`P6`,`P7`,`FFR`,`Air_FCV`,`Kerosene_FCV`,`testdataDate`,`Date`)
    VALUES('$test_id','$rpm1','$T1','$T2','$T3','$T4','$T5','$T11','$P1','$P2','$P3',
    '$P4','$P5','$P6','$P7','$FFR','$AirFCV','$FuelFCV', now(),now())");

    if($logFileConstant == 2){
        $DATA = strval($rpm1) . ','. strval($T1). ','.strval($T2). ','.strval($T3). ','.strval($T4). ','.strval($T5) . ','.strval($T11). ','. strval($P1) . ','. strval($P2) . ','. strval($P3) . ','. strval($P4). ','.strval($P5) . ','. strval($P6) . ','. strval($P7) . ','. strval($FFR) ;
        wh_log("Sensor data :" . $DATA);
    }
        

    if ($eshutdownInit == 1 or $eshutdowncomp == 1) {
      //79->set 0 in test id 
      $data1 = array(0);
      $modbus->writeMultipleRegister(0, 79,  $data1, "INT");
    }

    if ($logoutClicked == 1) {     
      break;
    }

    //for looping the process until shutdown/logout
    // CONSTANT VAL data_access_time
    sleep(1);
  } // end of while loop


  $modbus->disconnect();
  $modbus = NULL;

  // mysqli_close($conn);
  // $conn = NULL;

