
<?php
  header("Access-Control-Allow-Origin: *");
  header("Access-Control-Allow-Headers: access");
  header("Access-Control-Allow-Methods: POST");
  header("Content-Type: application/json; charset=UTF-8");
  header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
  
  require 'db_connectionPDO.php';
  require 'constant.php';
  require 'log.php';
  ?>

  <?php


  $sql  =   "SELECT dataacesstime as Delay, 
            AirFcv_Stage as AirFCV_Valve, 
            KeroseneFcv_Stage as KeroseneFCV_Valve,
            AirFCV_Initval as AirInitValve,
            KeroseneFCV_Initval as KeroseneInitValve
            FROM enertek_combuster.configuration";
          
  $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
  if (!$conn) {
    die("Configuration : connection faild :" . $conn->connect_error);
  }
  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  $conn = NULL;

  if (!$result) {
    wh_log("Configuration : " . $result->error_reporting);
    return;
  }

  $rows  = array();
  foreach($result as $r)
  {
    array_push($rows, $r);
  }
  print json_encode($rows);

  // mysqli_close($conn);
 
?>