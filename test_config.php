
<?php header('Access-Control-Allow-Origin: *');
require 'db_connectionPDO.php';
require 'log.php';
?>

<?php
  $sql  = "SELECT testparamconfig_id,testparamname,testparamvalue FROM `testparamconfig` ";
  $rows  = array();

  $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
  if (!$conn) {
    die("turboconfig : connection faild :" . $conn->connect_error);
  }
  $result  = $conn->query($sql, PDO::FETCH_ASSOC);
  foreach($result as $r)
  {
    array_push($rows, $r);
    //" . $r['testparamconfig_id'] . "," . $r['testparamname'] "."  $r['testparamvalue']");
  }
  print json_encode($rows);
  $conn = NULL; //closing db connection
?>