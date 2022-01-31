<?php header('Access-Control-Allow-Origin: *');
require 'db_connection.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'log.php';
?>
<?php

$conn = $db_conn;

	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);
    return;
	}

		$sql1  = "SELECT test_id from enertek_combuster.test  order by test_id desc limit 1";
		$result1  = mysqli_query($conn,$sql1);
		 if(!$result1){
            wh_log("Get Data : " . $db_conn -> error);
        }
		$rows1  = array();

			if(mysqli_num_rows($result1) > 0){
				while ($r  = mysqli_fetch_assoc($result1)) {
					array_push($rows1, $r);
					# code...
				}
				$test_id = $rows1[0]['test_id'];
			
			}
      $rows  = array();
      if( $test_id != 0){  
		$sql  = "SELECT * FROM enertek_combuster.testcommands  where test_id='$test_id'";
		$result  = mysqli_query($conn,$sql);
		$rows  = array();
			if(mysqli_num_rows($result) > 0){
				while ($r  = mysqli_fetch_assoc($result)) {
					array_push($rows, $r);
					# code...
				}		
				// $test_id = $rows[0]['name'];
				// $testcommandsTime = $rows[0]['testcommandsTime'];
				
			}}
      print json_encode($rows);
      // mysqli_close($conn);
      // $conn = NULL;
  ?>