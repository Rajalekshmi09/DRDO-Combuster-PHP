
<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
include_once 'db_connectionPDO.php';
require 'log.php';
?>

<?php
$data = json_decode(file_get_contents("php://input"));
//$test_id  = isset($data) ? $data->testId : 0;

//$conn = $db_conn;
$conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
if (!$conn) {
  die("connection faild:" . $conn->connect_error);
}

//reading test id
$sql  = "SELECT test_id from enertek_combuster.test  order by test_id desc limit 1";
$result  = $conn->query( $sql, PDO::FETCH_ASSOC);
foreach($result as $r)
  $test_id = $r['test_id'];

//live data from testdata - starts
$sql  = "select rpm, T1, T2, T3, T4, T5, T11, P1, P2,P3,P4,P5,P6,P7,FFR,
 Air_FCV, Kerosene_FCV,testdatadate 
from enertek_combuster.testdata order by `testdata_id` desc limit $graphLimit";
$result  = $conn->query( $sql, PDO::FETCH_ASSOC);
if (!$result) {
  wh_log("Graph : 1 " . $db_conn->error);
  return;
}
$rows  = array();
foreach($result as $r)
{
  array_push($rows, $r);
}
//live data from testdata - ends

//commands from testcommands -start
$rows1  = array();
if ($test_id != 0) {
  $sql  = "SELECT * FROM enertek_combuster.testcommands  where test_id ='$test_id'";
  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  if (!$result) {
    wh_log("Graph : 2 " . $db_conn->error);
    return;
  }
  foreach($result as $r)
  {
     array_push($rows1, $r);
  }
}
//commands from testcommands -end

array_push($rows, $rows1);
print json_encode($rows); //rows and row1

//mysqli_close($conn);
$conn = NULL;
?>