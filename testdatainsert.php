<?php header('Access-Control-Allow-Origin: *');
require 'db_connectionPDO.php';
require 'constant.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php
$modbus = new ModbusMaster();
$modbus->connect();

function hex2float($strHex) {
  $hex = sscanf($strHex, "%02x%02x%02x%02x%02x%02x%02x%02x");
  $bin = implode('', array_map('chr', $hex));
  $array = unpack("Gnum", $bin);
  return $array['num'];
}

$count_shutdowncmds = 1;
$Count1_normalcmds = 2;
$valves = 0;
$test_id = 0;
$sqlquery_shutdown = "";
$sqlquery_normal = "";

while(1){

  $sqlquery_shutdown = "";
  $sqlquery_normal = "";

  $logoutClicked = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 11, 1));
  //wh_log("testdatainsert.php --> a --  logoutClicked =$logoutClicked");
  if($logoutClicked == 0)
  {
    $T1 = 0;   
    $T2 = 0;    
    $T3 = 0;    
    $T4 = 0;   
    $T5 = 0; 
    $T11 = 0;

    $nshutdownInit = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 0, 1));
    $nshutdowncomp =  PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 51, 1)); //51
    $eshutdownInit = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 66, 1));  //66
    $eshutdowncomp = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 67, 1)); //67
    
     //initialize 
     if ( ($nshutdownInit == 0 || $nshutdownInit == 3) && $count_shutdowncmds >= 3) {   
      $count_shutdowncmds = 1;
      $Count1_normalcmds = 2;

      wh_log("testdatainsert.php --> 0--  nshutdownInit =$nshutdownInit. count_shutdowncmds =$count_shutdowncmds");
      
      //79 - test_id = 0
      $data1 = array( 0);
      $modbus->writeMultipleRegister(0, 79, $data1, "UINT"); //79
    }

    //shutdown process handled - start
    //N.shutdown cases handled
    if ($nshutdownInit == 3 && $count_shutdowncmds == 1) { 
      wh_log("testdatainsert.php --> 1-- nshutdownInit = $nshutdownInit, nshutdowncomp = $nshutdowncomp,count_shutdowncmds =$count_shutdowncmds");
      $count_shutdowncmds++;
    }
    else if ($nshutdowncomp == 1 && $count_shutdowncmds == 2) {
      wh_log("testdatainsert.php --> 2-- nshutdownInit = $nshutdownInit, nshutdowncomp = $nshutdowncomp,count_shutdowncmds =$count_shutdowncmds");
      $sqlquery_shutdown = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,
                            `type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                            'N.Shutdown Completed','S10','S', CURRENT_TIME(),'$valves')";
      $count_shutdowncmds++;
    }
      //e.Shutdwon eror cases handled here
    else if ($eshutdownInit == 1 && $count_shutdowncmds == 1) {
      wh_log("testdatainsert.php --> 3-- nshutdownInit = $nshutdownInit, nshutdowncomp = $nshutdowncomp,count_shutdowncmds =$count_shutdowncmds");

        $sqlquery_shutdown = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                              `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                              'E.Shutdown Initiated','S11','S',CURRENT_TIME(),'$valves')";

        $count_shutdowncmds++;
      }
      else if ($eshutdowncomp == 1 && $count_shutdowncmds == 2) {
        wh_log("testdatainsert.php --> 4-- nshutdownInit = $nshutdownInit, nshutdowncomp = $nshutdowncomp,count_shutdowncmds =$count_shutdowncmds");
        $sqlquery_shutdown = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                            `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                            'E.Shutdown Completed','S12','S',CURRENT_TIME(),'$valves')";
        $count_shutdowncmds++;
      }
      //shutdown process handled - end      

    //reading test id from 79 register in plc
    //initially it will be 0 , after initialize it has actual test_id
    //while shutdown again the bit will be set to 0
    $test_id = PhpType::bytes2signedInt($modbus->readMultipleRegisters(0, 79, 1));

    //wh_log("testdatainsert.php --> b --  test_id =$test_id");

    //reading bulk register values from PLC for real values
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

    try{
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

      $ar = $modbus->readMultipleRegisters(0, 42, 2);   
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
    }
    catch(Exception $Ex)
    {
      wh_log("testdatainsert.php --> c --  Ex =$Ex");
    }
    
    if ($test_id != 0) {
      $sensorData = $modbus->readMultipleRegisters(0, 49, 32);
      $Temp = array_chunk($sensorData, 2);
    
      //$initiatecompleted = PhpType::bytes2signedInt($Temp[0]);//49
      $startcompleted = PhpType::bytes2signedInt($Temp[1]);//50
      //$nshutdowncompleted = PhpType::bytes2signedInt($Temp[2]);//51
    
      $ignite = PhpType::bytes2signedInt($Temp[10]); //59
      $gasopened =PhpType::bytes2signedInt($Temp[11]); //60
      $stage1 = PhpType::bytes2signedInt($Temp[12]); //61
      $fuelopened =PhpType::bytes2signedInt($Temp[13]); //62
      $stage2 = PhpType::bytes2signedInt($Temp[14]); //63
      $gasclosed = PhpType::bytes2signedInt($Temp[15]); //64
      $stage3 = PhpType::bytes2signedInt($Temp[16]); //65
     // $eshutdowninit = PhpType::bytes2signedInt($Temp[17]); //66
      //$eshutdownComp =  PhpType::bytes2signedInt($Temp[18]);//67

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

      $valves = [$SV1_flame_Air,$SV2_FuelIject_Air,$SV3_Pilotflame_gas,$SV4_1inch_Bypass,
                $SV5_2inch_Bypass,$SV6_Ignitor_Switch,$SV7_Kerosene_pump,$SV8_Lubeoil_Pump,
                $SV9_Eshut_errcode];
      $valves = implode(',', $valves);
     
      if ($startcompleted == 1 && $Count1_normalcmds == 2) {      
        $sqlquery_normal =  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                             `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                             'Start Completed','S2','S',CURRENT_TIME(),'$valves')";
        $Count1_normalcmds++;
      }
      else if ($ignite == 1 && $Count1_normalcmds == 3) {
        $sqlquery_normal =  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                            `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                            'Ignite','S3','S',CURRENT_TIME(),'$valves')";
          $Count1_normalcmds++;
      }
      else if ($gasopened == 1 && $Count1_normalcmds == 4) {
        $sqlquery_normal =  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                              `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                              'Gas Opened','S4','S',CURRENT_TIME(),'$valves')";
          $Count1_normalcmds++;
      }    
      else if ($stage1 == 1 && $Count1_normalcmds == 5) {
        $sqlquery_normal = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                            `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                            'Stage1','S5','S',CURRENT_TIME(),'$valves')";
          $Count1_normalcmds++;
      }  
      else if ($fuelopened == 1 && $Count1_normalcmds == 6) {
        $sqlquery_normal = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                            `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                            'Fuel Opened','S6','S',CURRENT_TIME(),'$valves')";
        $Count1_normalcmds++;
      }
      else if ($stage2 == 1 && $Count1_normalcmds == 7) {
        $sqlquery_normal = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                            `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                            'Stage2','S7','S',CURRENT_TIME(),'$valves')";
          $Count1_normalcmds++;
      }  
      else if ($gasclosed == 1 && $Count1_normalcmds == 8) {
        $sqlquery_normal =  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                            `index`,`type`,`testcommandsTime`,`valvestatus`) VALUES('$test_id',
                            'Gas Closed','S8','S',CURRENT_TIME(),'$valves')";
        $Count1_normalcmds++;
      }    
      else if ($stage3 == 1 && $Count1_normalcmds == 9) {
        $sqlquery_normal =  "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,
                            `index`,`type`,`testcommandsTime`,`valvestatus`)VALUES('$test_id',
                            'Stage3','S9','S',CURRENT_TIME(),'$valves')";
        $Count1_normalcmds++;
      }

    }//end of if ($test_id != 0)

    //DB processing - start
    $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
    if (!$conn) {
      die("connection faild:" . $conn->connect_error);
      return;
    }

    if($sqlquery_shutdown != "")
      $result  = $conn->query( $sqlquery_shutdown);

    if($sqlquery_normal != "")
      $result  = $conn->query( $sqlquery_normal);

      $sql = "INSERT INTO `enertek_combuster`.`testdata`(`test_id`,`rpm`,`T1`,`T2`,`T3`,`T4`,`T5`,
              `T11`,`P1`,`P2`,`P3`,`P4`,`P5`,`P6`,`P7`,`FFR`,`Air_FCV`,`Kerosene_FCV`,`testdataDate`,`Date`)
                VALUES('$test_id','$rpm1','$T1','$T2','$T3','$T4','$T5','$T11','$P1','$P2','$P3',
                '$P4','$P5','$P6','$P7','$FFR','$AirFCV','$FuelFCV', now(),now())";

      $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
      $conn = NULL;
    //DB processing - end

      if($logFileConstant == 2){
          $DATA = strval($rpm1) . ','. strval($T1). ','.strval($T2). ','.strval($T3). ','.strval($T4).
          ','.strval($T5) . ','.strval($T11). ','. strval($P1) . ','. strval($P2) . ','. strval($P3) .
            ','. strval($P4). ','.strval($P5) . ','. strval($P6) . ','. strval($P7) . ','. strval($FFR) ;
          // wh_log("Sensor data :" . $DATA);
      }
 
    }// $lockoutclicked == 0  end

    sleep(1);

  } // end of while loop


  $modbus->disconnect();
  $modbus = NULL;

  $conn = NULL;
?>

