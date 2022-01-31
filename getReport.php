<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
?> 
<?php
$get_data = json_decode(file_get_contents("php://input"));
$turboIdVal = isset($get_data) ? $get_data->turboIdVal : 0;
$testno = isset($get_data) ? $get_data->testno : 0;

if($turboIdVal == 0 || $testno == 0 ){
  return;
}

$conn = $db_conn;
	if(!$conn)
	{	
		die("connection faild:" .$conn-> connect_error);
    return;
	}

$sql  = "SELECT test_id from enertek_combuster.test where turboconfig_id = '$turboIdVal' and testno = '$testno'";
$result  = mysqli_query($conn,$sql);
$rows  = array();
	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		
		// $turboIdVal = mysqli_real_escape_string($db_conn, trim($rows[0]->test_id));
		$test_id = $rows[0]['test_id'];		

	}

$sql  = "SELECT testdataDate, rpm as 'RPM',T1 as 'Combustor Outlet Temp',T2 as 'Turbine Inlet Temperature',
T3 as 'Turbine Outlet Temperature',T4 as 'Compressor Inlet Temperature', T5 as 'Compressor Outlet Temperature',
T11 as 'Ambient Temperature', P1 as 'Combustor Inlet Pressure',P2 as 'Fuel Line Pressure',
 P3 as 'Turbine Inlet Pressure',P4 as 'Ambient Pressure',P5 as 'Compressor Inlet Pressure',P6 as 'Compressor Outlet Pressure',
 P7 as 'Ventury meter diff pressure',FFR as 'Fuel Flow Rate' 
 FROM enertek_combuster.testdata where test_id = '$test_id'";

$result  = mysqli_query($conn,$sql);
$rows  = array();
	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		
		// $turboIdVal = mysqli_real_escape_string($db_conn, trim($rows[0]->test_id));
		print json_encode($rows);		

	}

// mysqli_close($conn);
// $conn = NULL;
?>


