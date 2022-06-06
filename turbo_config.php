
<?php header('Access-Control-Allow-Origin: *');
require 'db_connectionPDO.php';
require 'log.php';
?>

<?php

  $sql  = "SELECT turboconfig_id,turboname,installeddate,description,status 
          FROM enertek_combuster.turboconfig  order by  turboconfig_id desc";
  $rows  = array();

  $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
  if (!$conn) {
    die("turboconfig : connection faild :" . $conn->connect_error);
  }
  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  $conn = NULL;

  foreach($result as $r)
  {
    array_push($rows, $r);
  }
  print json_encode($rows);

?>