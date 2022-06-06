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
  $turboIdValue  = isset($data) ? $data->turboIdValue : 0;

  if($turboIdValue == 0 )
    return;

  error_reporting(E_ERROR | E_PARSE);

  $conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
  if (!$conn) {
    die("connection faild:" . $conn->connect_error);
  }

  $sql  = "SELECT  testno FROM test where turboconfig_id = '$turboIdValue'  order by testno 
            desc LIMIT 1";

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