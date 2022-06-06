<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require 'constant.php';


function wh_log($log_msg)
{
 // if($logFileConstant == 2 || $logFileConstant == 1 ){
	$log_time = date('Y-m-d h:i:sa');
	$log_msg = $log_time.": ".$log_msg;

    $log_filename = "logFile";
    if (!file_exists($log_filename)) 
    {
        // create directory/folder uploads.
        mkdir($log_filename, 0777, true);
    }
    $log_file_data = $log_filename.'/log_' . date('d-M-Y') . '.log';
    file_put_contents($log_file_data, $log_msg  . "\n", FILE_APPEND);
 // }
}
?>