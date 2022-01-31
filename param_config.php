
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

	$sql  = "SELECT Paramname,unitname, upperlimit,lowerlimit,normallimit FROM paramconfig INNER JOIN enertek_combuster.unit ON enertek_combuster.paramconfig.unit_id=enertek_combuster.unit.unit_id order by Paramname";
	$result  = mysqli_query($conn,$sql);
	if(!$result){
            wh_log("Param Config : " . $db_conn -> error);
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
		echo "no data";

	}

	// mysqli_close($conn);


?>