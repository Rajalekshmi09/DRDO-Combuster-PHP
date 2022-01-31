
<?php header('Access-Control-Allow-Origin: *');
require 'db_connection.php';
require 'log.php';
?>

<?php


	$conn = $db_conn;
	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);

	}

	$sql  = "SELECT * FROM enertek_combuster.turboconfig where status = 'installed'";
	$result  = mysqli_query($conn,$sql);
	if(!$result){
            wh_log("LoginPage : " . $db_conn -> error);
        }
	$rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		
		print json_encode($rows);

	}

	else
	{
		print json_encode(array());

	}

	// mysqli_close($conn);


?>