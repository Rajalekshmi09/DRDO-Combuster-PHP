 
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$DATABASE = "enertek_combuster";
$DBUSER = "root";
$DBPASSWD = "1234";
$PATH = "/";
$FILE_NAME = "enertek_combuster" . date("Y-m-d") . ".sql.gz";
exec('/usr/bin/mysqldump -u ' . $DBUSER . ' -p' . $DBPASSWD . ' ' . $DATABASE . ' | gzip --best > ' . $PATH . $FILE_NAME);
echo json_encode("success");
?>
 