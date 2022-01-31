<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
require 'db_connection.php';
require 'log.php';

$conn = $db_conn;
	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);
    return;
	}

//set zero in login bit in PLC 
$modbus = new ModbusMaster();
$modbus->connect();

$initial_data = array(0);

//11 -> logout bit
//12 -> eshutdown error code
//79 => test id value set 0
$modbus->writeMultipleRegister(0, 11, $initial_data, "INT");
$modbus->writeMultipleRegister(0, 12, $initial_data, "INT");
$modbus->writeMultipleRegister(0, 79, $initial_data, "INT");
// Globals::$static_testid = 0;
// Globals::$static_nozzlearea = 0;

$modbus->disconnect();
$modbus = NULL;

// Delete all dummy live data in Db with testid = 0 
$data = json_decode(file_get_contents("php://input"));
$sql1  = "DELETE FROM enertek_combuster.testdata WHERE test_id = 0";
$result1  = mysqli_query($conn,$sql1);

// POST DATA
$data = json_decode(file_get_contents("php://input"));
 
if(isset($data->user_name) 
	&& isset($data->password) 
	&& !empty(trim($data->user_name)) 
	&& !empty(trim($data->password))
	){
    $username = mysqli_real_escape_string($db_conn, trim($data->user_name));
    $password = mysqli_real_escape_string($db_conn, trim($data->password));
    $en_password = md5($password);
        $insertUser = mysqli_query($db_conn,"select username from user where email = '$username' and pwd = '$en_password'");
        if(!$insertUser){
            wh_log("LoginPage : " . $db_conn -> error);
        }
        $count = mysqli_num_rows($insertUser);  
        $rows  = array();
        if(mysqli_num_rows($insertUser) > 0){
        while ($r  = mysqli_fetch_assoc($insertUser)) {
            array_push($rows, $r);
            # code...
        }
    }
        if($count == 1){    
            //$last_id = mysqli_insert_id($db_conn);
            echo json_encode(["success",$rows[0]['username']]);
            // print json_encode($rows);                     
        }
        else{
            echo json_encode(["failed"]);
        }
}
else{
    echo json_encode(["success"=>0,"msg"=>"Please fill all the required fields!"]);
}
// mysqli_close($conn);
// $conn = NULL;

?>