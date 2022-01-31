
<?php header('Access-Control-Allow-Origin: *');
require 'db_connection.php';
require 'constant.php';
require 'log.php';
?>

<?php
$conn = $db_conn;
	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);
    return;
	}

	$sql  = "SELECT dataacesstime as Delay, 
          AirFcv_Stage as AirFCV_Valve, 
          KeroseneFcv_Stage as KeroseneFCV_Valve,
          AirFCV_Initval as AirInitValve,
          KeroseneFCV_Initval as KeroseneInitValve
          FROM enertek_combuster.configuration";

	$result  = mysqli_query($conn,$sql);
	$rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		echo json_encode($rows);
	}
	else
	{
		echo "no data";
	}
	// mysqli_close($conn);
  // $conn = NULL;
?>