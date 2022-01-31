
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

	$sql  = "select  T1, T2, T3, T4, T5, T11, P1, P2,P3,P4,P5,P6,P7,FFR,rpm, Air_FCV, Kerosene_FCV,testdatadate 
          from enertek_combuster.testdata order by `testdata_id` desc limit $graphLimit";
	$result  = mysqli_query($conn,$sql);
	 if(!$result){
            wh_log("Graph : " . $db_conn -> error);
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