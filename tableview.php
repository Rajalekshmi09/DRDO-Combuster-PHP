
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

	$sql  = "SELECT Paramname,unitname, paramindex, upperlimit,lowerlimit,normallimit ,graph_upper ,graph_lower
            FROM paramconfig INNER JOIN enertek_combuster.unit 
            ON enertek_combuster.paramconfig.unit_id=enertek_combuster.unit.unit_id";
	$result  = mysqli_query($conn,$sql);
	// if(!$result){
 //            wh_log("Table View : " . $db_conn -> error);
 //        }
	$rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}
		 
		print json_encode($rows);
		// wh_log("Table View : Started");

		// echo $rows;

	}

	else
	{
		echo "no data";

	}

	// mysqli_close($conn);


?>