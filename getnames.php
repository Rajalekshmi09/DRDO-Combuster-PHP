<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once 'db_connectionPDO.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
?> 

<?php
$data = json_decode(file_get_contents("php://input"));
$turboIdVal  = isset($data) ? $data->turboIdVal : 0;
$testno  = isset($data) ? $data->testno : 0;

// $turboIdVal = mysqli_real_escape_string($db_conn, trim($data->turboIdVal));
// $testno = mysqli_real_escape_string($db_conn, trim($data->testno));

$sql  = "select tester,witness from test where turboconfig_id = '$turboIdVal' and testno = '$testno'";
$rows  = array();
$conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
if (!$conn) {
  die("connection faild:" . $conn->connect_error);
}

$result  = $conn->query( $sql, PDO::FETCH_ASSOC);

foreach($result as $r)
{
  array_push($rows, $r);
}
print json_encode($rows);
$conn = NULL;
?>