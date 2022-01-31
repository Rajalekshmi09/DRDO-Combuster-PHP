<?php header('Access-Control-Allow-Origin: *');
require 'db_connection.php';
?>


<?php
$Commandc = 0;
$TEMPSETstage1c = 1;
$TEMPSETstage2c = 2;
$RPMSETstage3c = 3;
$TEMPSETc  = 4;
$RPMSETc = 5;
$NUMOFBLADESc = 6;
$T1c  = 19;
$T2c= 20;
$T3c = 21;
$T4c = 22;
$T5c = 23;
$T11c = 24;
$P1c = 26;
$P2c = 28;
$P3c = 30;
$P4c = 32;
$P5c = 34;
$P6c = 36;
$P7c = 38;
$FFRc = 40;
$rpmc= 42;
$InitateCompletedc = 49;
$StartCompletedc = 50;
$NShutdownCompletedc = 51;
$Ignitec = 59;
$GasOpenedc = 60;
$Stage1c = 61;
$FuelOpenedc = 62;
$Stage2c = 63;
$GasClosedc = 64;
$Stage3c = 65;
$EShutdownc = 66;
$PilotFlameAirc = 69;
$FuelInjectorAirc = 70;
$PilotFlameGasc = 71;
$FCVAirc = 72;
$FCVKeroseneFuelc = 73;
$ByPassValueIc = 74;
$ByPassValueIIc  = 75;
$IgnitorSwitchc   = 76;
$KerosenePumpc  = 77;
$LubeOilPumpc  = 78;
$graphLimit = 7;
$logFileConstant = 2;

	$conn = $db_conn;

	if(!$conn)
	{
		die("connection faild:" .$conn-> connect_error);

	}

	$sql  = "SELECT dataacesstime FROM enertek_combuster.configuration";
	$result  = mysqli_query($conn,$sql);

	$rows  = array();

	if(mysqli_num_rows($result) > 0){
		while ($r  = mysqli_fetch_assoc($result)) {
			array_push($rows, $r);
			# code...
		}

		// print json_encode($rows[0]['dataacesstime']*1000);
		$data_access_time = $rows[0]['dataacesstime'];

	}

?>
