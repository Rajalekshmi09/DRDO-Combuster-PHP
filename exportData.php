<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
?> 

<?php
	$data = json_decode(file_get_contents("php://input"));
  $turboIdVal = isset($data) ? $data->turboIdVal : 0;

  if($turboIdVal == 0 ){
    return;
  }

  $conn = $db_conn;
	if(!$conn)
	{	
		die("connection faild:" .$conn-> connect_error);
    return;
	}
	error_reporting(E_ERROR | E_PARSE);
	
	$sql  = "SELECT testno from test where turboconfig_id='$turboIdVal' order by test_id desc";
	$result  = mysqli_query($conn,$sql);
	$rows  = array();

  if($result != NULL)
	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		print json_encode($rows);		
	}
  // mysqli_close($conn);
  // $conn = NULL;
?>


