<?php header('Access-Control-Allow-Origin: *');
require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>
<?php

$modbus = new ModbusMaster();
$Db_delay = 1;
$modbus->connect();
$conn = $db_conn;

	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);

	}
	$count=1;
	$sql  = "SELECT valvestatus,testcommandsTime FROM enertek_combuster.testcommands order by testcommands_id desc limit 1";
$result  = mysqli_query($conn,$sql);
if(!$result){
            wh_log("Valve Status : " . $db_conn -> error);
        }
$rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		$test_id = $rows[0];
		
	print json_encode($test_id);			
	
	}