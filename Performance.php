<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
?> 

<?php



	$conn = $db_conn;

	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);

	}

	$data = json_decode(file_get_contents("php://input"));
$turboIdVal = mysqli_real_escape_string($db_conn, trim($data->turboIdVal));
$testno = mysqli_real_escape_string($db_conn, trim($data->testno));

$sql  = "select * from test where turboconfig_id = '$turboIdVal' and testno = '$testno' ";
$result  = mysqli_query($conn,$sql);

$rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {

			array_push($rows, $r);
			# code...
		}
		
		$result1 = $rows[0]['test_id'];				
		
	}

	$sql  = "CALL Performance($result1)";
	$result  = mysqli_query($conn,$sql);
	
	$rows  = array();	
	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			
		}

		print json_encode($rows);

	}

	else
	{
		print json_encode($rows);

	}

	// mysqli_close($conn);


?>