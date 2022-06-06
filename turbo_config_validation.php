<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db_connectionPDO.php';
require 'log.php';

// POST DATA
$data = json_decode(file_get_contents("php://input"));
$turbo_id = isset($data) ? $data->turbo_id : 0; 
$date  = isset($data) ? $data->date : ''; 
$description  = isset($data) ? $data->descriptions : ' '; 
// $noofblades  = isset($data) ? $data->noofblades : 0; 

if($date == '' ||$turbo_id == 0  )
return;

$noofblades = 0;
$sql1 = "INSERT INTO `enertek_combuster`.`turboconfig`(`turboname`,`installeddate`,
        `numofblades`,`description`,`status`,`completiondate`) VALUES('$turbo_id','$date',
        '$noofblades','$description','installed',now())";
$sql  = "SELECT turboconfig_id,turboname,installeddate,description,status 
              FROM enertek_combuster.turboconfig  order by  turboconfig_id desc";
$rows  = array();              

$conn = new PDO($db_connstring, $db_hostname, $db_pwd, array(PDO::ATTR_PERSISTENT => true));
if (!$conn) {
  die("turboconfig : connection faild :" . $conn->connect_error);
}
$result  = $conn->query( $sql1, PDO::FETCH_ASSOC);

if($result)
{
  $result  = $conn->query( $sql, PDO::FETCH_ASSOC);
  foreach($result as $r)
  {
    array_push($rows, $r);
  }  
}
print json_encode($rows);
$conn = NULL; //closing db connection

?>