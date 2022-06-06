<?php header('Access-Control-Allow-Origin: *');
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
include_once 'db_connectionPDO.php';
?>

<?php

$conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
if (!$conn) {
  die("connection faild:" . $conn->connect_error);
}

$sql  = "SELECT valvestatus,testcommandsTime
           FROM enertek_combuster.testcommands order by testcommands_id desc limit 1";

$result  = $conn->query( $sql, PDO::FETCH_ASSOC);
$testid = 0;
if($result != NULL)
{
  foreach($result as $r)
    $testid = $r['testno'] ;

    print json_encode($testid + 1);
}
else
{
  print json_encode(1);
}
$conn = NULL; //to close PDO



?>