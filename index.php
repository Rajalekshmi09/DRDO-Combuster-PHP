
<?php header('Access-Control-Allow-Origin: *');
require 'db_connection.php';
?>

<?php


	//$url = "localhost";
	//$database = "orc_db" ;
	//$username ="root";
	//$password  = "";

	//$conn  = mysqli_connect($url,$username, $password, $database);

	$conn = $db_conn;

	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);

	}

	$sql  = "select * from data order by `data_id` desc limit 1";
	$result  = mysqli_query($conn,$sql);

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