
<?php header('Access-Control-Allow-Origin: *');
require 'db_connectionPDO.php';
require 'log.php';
?>

<?php
  
  $sql  = "SELECT * FROM enertek_combuster.turboconfig where status = 'installed'";
  $rows  = array();

  $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
  if (!$conn) {
    die("Statusvalue : connection faild :" . $conn->connect_error);
  }

  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  $conn = NULL;
  
  foreach($result as $r)
  {
    array_push($rows, $r);
  }
  print json_encode($rows);

?>