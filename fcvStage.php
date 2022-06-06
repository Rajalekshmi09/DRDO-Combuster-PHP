<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include_once 'db_connectionPDO.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>

<?php
//received testid from frontend react using POST
$get_data = json_decode(file_get_contents("php://input"));
$test_id  = isset($get_data) ? $get_data->testId : 0;
$fcvState  = isset($get_data) ? $get_data->state : 0;

$whole_num = isset($get_data) ? $get_data->fcvValue : 0; //whole number
$decimal_num = isset($get_data) ? $get_data->decimalNum : 0;  //decimal number
$operation_type = isset($get_data) ? $get_data->operationType : 0;  //1-addition, 2-subtraction

wh_log("fcv.php --> --1  whole_num = $whole_num ,decimal_num =$decimal_num ");

if ($test_id == 0) {
  return;
}

$sql_query = "";
$number = 0;
$divisor = 1;

if($operation_type == 1)
  $FCV_Val = $whole_num + $decimal_num;
else if($operation_type == 2)
  $FCV_Val = $whole_num - $decimal_num;

  if($FCV_Val <= 0)
   $FCV_Val = 0;
else if($FCV_Val >= 100)
  $FCV_Val = 100;

//finding the position of the .  and get the length after dot
//eg: 3 --> 3, 1, 3/1 = 3
//eg:  3.4 -->  34, 10,   34/10=3.4
//eg:  3.45 --> 345, 100,  345/100 = 3.45
$SV_Data = array($FCV_Val);
$realdata = $SV_Data[0];
$length = strlen(substr(strrchr($realdata, "."), 0)); //length includes . position also
wh_log("fcv.php --> --2 realdata = $realdata ,length =$length ");

if ($length == 0) {
  //no decimal part
  $number = round($realdata, 0);
  $divisor = 1;
} else {
  $number = round($realdata * pow(10,  $length - 1), 0);
  $divisor = pow(10, $length - 1);
}

//to avoid sudden increment to 100 (it is a bug we need to fix properly)
if(($number / $divisor) >= 100){
  $number = 0;
  $divisor = 1;
}
wh_log("fcv.php --> --3 number = $number ,divisor =$divisor ");
//to fix 0.8 writing in PLC is failed, incremented and decremented
// $number++; $number--;
$number_array = array($number, $divisor);

$modbus = new ModbusMaster();
$modbus->connect();

//1 -> Airvalve, 2 -> maingasfuel, 3-> find tune valve
if ($fcvState == 1) {

  $modbus->writeMultipleRegister(0, 93, $number_array, "UINT, UINT"); //93, 94

  $sql_query = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`,`value`,`testcommandsTime`)
    VALUES($test_id,'AirFCV','C7_2','C','$FCV_Val',now())";

} elseif($fcvState == 2) {

  $modbus->writeMultipleRegister(0, 95, $number_array, "UINT, UINT"); //95,96
  
  $sql_query = "INSERT INTO `enertek_combuster`.`testcommands`(`test_id`,`name`,`index`,`type`
  ,`value`,`testcommandsTime`)
  VALUES($test_id,'initialkeroseneCV_openingvalue','C7_3','C','$FCV_Val',now())";
}

$modbus->disconnect();
$modbus = NULL;
  
  //DB processing - start
  if($sql_query != "")
  {
    $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
    if (!$conn) {
      die("connection faild:" . $conn->connect_error);
    }
    $conn->query( $sql_query, PDO::FETCH_ASSOC);
    $conn = NULL;
  }
  //DB processing - end

  // mysqli_close($conn);
  // $conn = NULL;

?>