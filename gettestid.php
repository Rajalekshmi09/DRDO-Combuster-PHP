<?php header("Access-Control-Allow-Origin: *");
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
  $turboIdVal  = isset($data) ? $data->turboIdVal : 0;

  $conn = $db_conn;
  error_reporting(E_ERROR | E_PARSE);

	if(!$conn)
	{	
     die("connection faild:" .$conn-> connect_error);
      return;
	}

  if ($turboIdVal != '') {
    $sql  = "SELECT dataacesstime FROM enertek_combuster.configuration";
    $result  = mysqli_query($conn, $sql);
  
    $rows  = array();
  
    if (mysqli_num_rows($result) > 0) {
      while ($r  = mysqli_fetch_assoc($result)) {
        array_push($rows, $r);
      }
      $delay_time = $rows[0]['dataacesstime'];
    }
  
    $sql  = "SELECT testno from enertek_combuster.test where turboconfig_id='$turboIdVal'
             order by test_id desc limit 1";
    $result  = mysqli_query($conn, $sql);
  
    $rows  = array();
  
    if (mysqli_num_rows($result) > 0) {
      while ($r  = mysqli_fetch_assoc($result)) {
        array_push($rows, $r);
      }
      $testno =  $rows[0]['testno'];
    } else {
      $testno = 0;
    }
  
    $newtestNo = $testno + 1;
    $witnessItems = [];
    $testerItems = [];
  
    if ($data->testerItems) {
      for ($i = 0; $i < count($data->testerItems); $i++) {
        $testerItems[$i] = $data->testerItems[$i];
      }
      $testerItems = implode(',', $testerItems);
    }
  
    if ($data->witnessItems) {
      for ($i = 0; $i < count($data->witnessItems); $i++) {
        $witnessItems[$i] = $data->witnessItems[$i];
      }
      $witnessItems = implode(',', $witnessItems);
    }
  
    // delay time is used for duration calculation in report
    $inserttestidData = mysqli_query($conn, 
    "INSERT INTO `enertek_combuster`.`test`(`turboconfig_id`,`testno`,`tester`,`witness`,`testingdatetime`,delay_time)
     VALUES('$turboIdVal','$newtestNo','$testerItems','$witnessItems',Now(),'$delay_time')");
  
    $sql  = "SELECT test_id from enertek_combuster.test  order by test_id desc limit 1";
    $result  = mysqli_query($conn, $sql);
  
    $rows  = array();
    $test_id = 0;
  
    if (mysqli_num_rows($result) > 0) {
      while ($r  = mysqli_fetch_assoc($result)) {
        array_push($rows, $r);
        # code...
      }
      $test_id = intval($rows[0]['test_id']);
      echo json_encode($test_id);
    }
  }
  
  
  
  
  
  
  
  // $sql  = "SELECT testno from enertek_combuster.test where turboconfig_id='$turboIdVal' order by test_id desc limit 1";
    // $result  = mysqli_query($conn,$sql);
    // // if(!$result){
    // //   wh_log("Get Test ID : " . $db_conn -> error);
    // // }
    // $rows  = array();

    //   if(mysqli_num_rows($result) > 0){
    //     while ($r  = mysqli_fetch_assoc($result)) {
    //       array_push($rows, $r);
    //       # code...
    //     }

    //     $testno =  $rows[0]['testno'];
    //     // wh_log("Get Test ID : Started");
                  

    //   }
    //   else
    //   {
    //     $testno = 0;
    //   }

    //   $newtestNo = $testno+1;
    //   $witnessItems = [];
    //   $testerItems = [];
    //   for($i=0;$i<count($data->testerItems);$i++){
    //     $testerItems[$i] = $data->testerItems[$i];
    //   }
    //   $testerItems = implode(',', $testerItems);
    //   for($i=0;$i<count($data->witnessItems);$i++){
    //     $witnessItems[$i] = $data->witnessItems[$i];
    //   }
    //   $witnessItems = implode(',', $witnessItems);

    //   $inserttestidData = mysqli_query($conn,"INSERT INTO `enertek_combuster`.`test`(`turboconfig_id`,`testno`,`tester`,`witness`,`testingdatetime`)VALUES('$turboIdVal','$newtestNo','$testerItems','$witnessItems',Now())");
?>


