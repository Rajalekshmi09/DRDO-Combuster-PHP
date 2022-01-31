<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
//require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php
  //received testid from frontend react using POST
  $get_data = json_decode(file_get_contents("php://input"));
  $test_id  = isset($get_data) ? $get_data->testId : 0; 
  $fcvState  = isset($get_data) ? $get_data->state : 0; 
  $FCV_Val = isset($get_data) ? $get_data->fcvValue : 0;
 
  if(  $test_id == 0 ){
    return;
  }
   
  $modbus = new ModbusMaster();
  $modbus->connect(); 
  
  $conn = $db_conn;
  if (!$conn) {
    die("connection faild:" . $conn->connect_error);
    return;
  }

  $number = 0;
  $digits = 0;

  //finding the position of the .  and get the length after dot
  //eg: 3 --> 3, 1, 3/1 = 3
  //eg:  3.4 -->  34, 10,   34/10=3.4
  //eg:  3.45 --> 345, 100,  345/100 = 3.45
  $SV_Data = array($FCV_Val);
  $length = strlen(substr(strrchr( $SV_Data[0], "."), 0));

  if($length == 0)
  {
    //no decimal part
    $number = $SV_Data[0];
    $digits = 1;
  }
  else if($length == 2)
  {
    //after decimal - one digit
    $number = $SV_Data[0] * 10;   
    $digits = 10;
  }
  else if($length == 3)
  {
    //after decimal - 2 digits
    $number = $SV_Data[0] * 100;
    $digits = 100;
  }

  $number_array = array($number);
  $digits_array = array($digits);

  //1 -> Airvalve, 2 -> kerosenevalve
  if($fcvState == 1){    
    
    $modbus->writeMultipleRegister(0, 93, $number_array, "INT");
    $modbus->writeMultipleRegister(0, 94, $digits_array, "INT");
    //$modbus->writeMultipleRegister(0, 66, $airSV2stDigit, "INT");

    $inserttestData = mysqli_query($conn, "INSERT INTO `enertek_combuster_goa`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
    VALUES($test_id,'AirFCV','C7_2,'C','$FCV_Val',now())");

  }else{ 

    $modbus->writeMultipleRegister(0, 95, $number_array, "INT");
    $modbus->writeMultipleRegister(0, 96, $digits_array, "INT");
    //$modbus->writeMultipleRegister(0, 77, $airSV2stDigit, "INT");

    $inserttestData = mysqli_query($conn, "INSERT INTO `enertek_combuster_goa`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
    VALUES($test_id,'initialkeroseneCV_openingvalue','C7_3','C','$FCV_Val',now())");
  }
   
  $modbus->disconnect();
  $modbus = NULL;
  
  // mysqli_close($conn);
  // $conn = NULL;

?>