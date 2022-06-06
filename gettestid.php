<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require 'db_connectionPDO.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?> 

<?php
  $data = json_decode(file_get_contents("php://input"));
  $turboIdVal  = isset($data) ? $data->turboIdVal : 0;

  if ($turboIdVal == 0) {
    return;
  }

  $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
  if (!$conn) {
    die("turboconfig : connection faild :" . $conn->connect_error);
  }

  // delay time is used for duration calculation in report
  $sql  = "SELECT dataacesstime FROM enertek_combuster.configuration";
  $rows  = array();
  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  foreach($result as $r)
  {
    array_push($rows, $r);
  }
  $delay_time = $rows[0]['dataacesstime'];

  //get testno
  $sql  = "SELECT testno from enertek_combuster.test 
          where turboconfig_id='$turboIdVal' order by test_id desc limit 1";
  $rows  = array();
  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);

  $testno = 0;
  foreach($result as $r)
  {
    //array_push($rows, $r);
    $testno =  $r['testno'];
  }

  //increment to get next id
  $newtestNo = $testno + 1;
  
  // $witnessItems = [];
  // $testerItems = [];

  // // if ($data->testerItems) {    
  //   for ($i = 0; $i < count($data->testerItems); $i++) {
  //     $testerItems[$i] = $data->testerItems[$i]; 
  //   }
  //   $testerItems = implode("," , $testerItems);
  // // }

  // // if ($data->witnessItems) {    
  //   for ($i = 0; $i < count($data->witnessItems); $i++) {
  //     $witnessItems[$i] = $data->witnessItems[$i];     
  //   }
  //   $witnessItems = implode("," , $witnessItems);
  // // }

  $witnessItems = '';
  $testerItems = '';
  // delay time is used for duration calculation in report
  $sql =  "INSERT INTO `enertek_combuster`.`test`(`turboconfig_id`,`testno`,`tester`,`witness`,
          `testingdatetime`, delay_time) 
          VALUES('$turboIdVal','$newtestNo','$testerItems','$witnessItems',Now(),'$delay_time')";
  $conn->query( $sql,PDO::FETCH_ASSOC);

  //read the testid from db
  $sql  = "SELECT test_id from enertek_combuster.test  order by test_id desc limit 1";
  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
   $rows  = array();
  foreach($result as $r)
  {
    array_push($rows, $r);
  }
  $test_id =  intval($rows[0]['test_id']);

  echo json_encode($test_id);

  $conn = NULL; //closing db connection

?>


