<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?> 

<?php
error_reporting(E_ERROR | E_PARSE);

	$conn = $db_conn;
	$data = json_decode(file_get_contents("php://input"));
$turboIdValue = mysqli_real_escape_string($db_conn, trim($data->turboIdValue));
	// print json_encode($turboIdValue);
	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);

	}

	$sql  = "SELECT  testno FROM test where turboconfig_id = '$turboIdValue'  order by testno desc LIMIT 1";
	$result  = mysqli_query($conn,$sql);
	if(!$result){
            wh_log("Tubrbo Id Value : " . $db_conn -> error);
        }
	$rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		
		print json_encode($rows[0]['testno']+1);

	}

	else
	{
		print json_encode($rows);

	}

	// mysqli_close($conn);


?>