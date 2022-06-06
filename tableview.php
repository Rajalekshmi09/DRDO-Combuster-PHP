
<?php header('Access-Control-Allow-Origin: *');
require 'db_connectionPDO.php';
require 'log.php';
?>

<?php

  $sql  = "SELECT Paramname,unitname, paramindex, upperlimit,lowerlimit,normallimit ,
           graph_upper ,graph_lower   FROM paramconfig INNER JOIN enertek_combuster.unit 
           ON enertek_combuster.paramconfig.unit_id=enertek_combuster.unit.unit_id";
           
  $rows  = array();

  $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
  if (!$conn) {
    die("Tableview : connection faild :" . $conn->connect_error);
  }

  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  $conn = NULL;
  
  foreach($result as $r)
  {
    array_push($rows, $r);
  }
  print json_encode($rows);

?>