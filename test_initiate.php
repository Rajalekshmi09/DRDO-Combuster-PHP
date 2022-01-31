<?php

require_once dirname(__FILE__) . '/ModbusMasterTCP.php';


$modbus = new ModbusMaster();

$modbus->connect();
 
?>