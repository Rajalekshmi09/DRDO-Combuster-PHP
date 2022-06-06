<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'db_connectionPDO.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require_once "log.php";
?> 

<?php
  $data = json_decode(file_get_contents("php://input"));
  $turboIdVal  = isset($data) ? $data->turboIdVal : 0;
  $testno  = isset($data) ? $data->testno : 0;

  // $turboIdVal = mysqli_real_escape_string($db_conn, trim($data->turboIdVal));
  // $testno = mysqli_real_escape_string($db_conn, trim($data->testno));
 
  $sql  = "SELECT test_id from enertek_combuster.test where turboconfig_id = '$turboIdVal' and testno = '$testno'";  
  $rows  = array();
  $test_id = 0;

  $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
  if (!$conn) {
    die("Configuration : connection faild :" . $conn->connect_error);
  }

  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  foreach($result as $r)
      $test_id = $r['test_id'];


  $sql  = "SELECT testdataDate as testdataTime, rpm as 'RPM Sensor',T1 as 'Combuster outlet Temperature',
          T2 as 'Turbine Inlet Temperature',T3 as 'Turbine Outlet Temperature',
          T4 as 'Compressor Inlet Temperature', T5 as 'Compressor Outlet Temperature',
          T11 as 'Ambient Temperature', P1 as 'Combuster Inlet Pressure',P2 as 'Fuel Line Pressure',
          P3 as 'Turbine Inlet Pressure',P4 as 'Ambient Pressure',P5 as 'Compressor Inlet Pressure',
          P6 as 'Compressor Outlet Pressure',
          P7 as 'Ventury meter differencial Pressure',FFR as 'Fuel Flow Rate' 
          FROM enertek_combuster.testdata where test_id = '$test_id'";

  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  foreach($result as $r)
  {
    array_push($rows, $r);
  }
  print json_encode($rows);

  $conn = NULL;

?>


