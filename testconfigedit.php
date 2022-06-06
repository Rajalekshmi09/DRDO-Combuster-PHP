<?php header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db_connection.php';
require 'log.php';
require_once dirname(__FILE__) . '/ModbusMasterTCP.php';
?> 
<?php
  $data = json_decode(file_get_contents("php://input"));
  
  if($data == null)
  return;

  $page = mysqli_real_escape_string($db_conn, trim($data->page));//testpramconfig table
  $configIndexID =mysqli_real_escape_string($db_conn, trim($data->configID));//primarykey

  $editRowIndex = null;
  if($data->editRowIndex != null || $data->editRowIndex != '')
    $editRowIndex = mysqli_real_escape_string($db_conn, trim($data->editRowIndex));

  error_reporting(E_ALL ^ E_WARNING);
  $conn = $db_conn;

	if(!$conn)
	{	
		die("connection faild:" .$conn-> connect_error);
    return;
	}
  // $modbus = new ModbusMaster();
  // $modbus->connect();

  if($editRowIndex != null){
    if($data->editData != null)
    {
      //data->editdata it has edited new value
      //$editDataKey - it has column name 
      // $editDataKey = mysqli_real_escape_string($db_conn, key($data->editData)); //from   
      // $editData = mysqli_real_escape_string($db_conn, trim($data->editData->$editDataKey));
      $editDataKey = 'testparamvalue';
      $editData = isset($data) ? trim($data->editData->$editDataKey) : 0;
    }

    if($page== 'testparamconfig'){
      $sql  = "update $page set $editDataKey = $editData  where testparamconfig_id = $configIndexID ";
      $result  = mysqli_query($conn,$sql);
      $sql  = "SELECT * FROM `$page`";
        $result  = mysqli_query($conn,$sql);
        $rows  = array();
        if(!$result){
                  wh_log("Table Edit : " . $db_conn -> error);
              }
        if(mysqli_num_rows($result) > 0){
          while ($r  = mysqli_fetch_assoc($result)) {
            array_push($rows, $r);        
            }        
          print json_encode($rows);
        }
    }//end of if($page== 'testparamconfig')

    else if($page == 'turboconfig'){
      if($data->editData->status){
      $turboIdVal = mysqli_real_escape_string($db_conn, trim($data->turboIdVal));
      $editData = mysqli_real_escape_string($db_conn, trim($data->editData->status));
      $sql  = "update turboconfig set status = '$editData'  where turboconfig_id = $turboIdVal";
      $result  = mysqli_query($conn,$sql);
      }
      
      if($data->editData->description){
        $turboIdVal = mysqli_real_escape_string($db_conn, trim($data->turboIdVal));
        $editData = mysqli_real_escape_string($db_conn, trim($data->editData->description));
        $sql  = "update turboconfig set description = '$editData'  where turboconfig_id =$turboIdVal";
        $result  = mysqli_query($conn,$sql);
      }

      $sql  = "SELECT turboconfig_id,turboname,installeddate,numofblades,description,status 
      FROM enertek_combuster.turboconfig  order by  turboconfig_id desc";
      $result  = mysqli_query($conn,$sql);

      $rows  = array();

      if(mysqli_num_rows($result) > 0){
        while ($r  = mysqli_fetch_assoc($result)) {
          array_push($rows, $r);
          # code...
        }
        print json_encode($rows);
      }	else{      
            //    echo json_encode(
            //    http_response_code(404);
            // );
                print json_encode(array());
            }
    }// end of if($page == 'turboconfig')
  }// end of if($editRowIndex != null)

  else if($editRowIndex == null){
    if($page== 'testparamconfig'){
      
      $sql  = "SELECT testparamvalue FROM enertek_combuster.test_config_default";
      $result  = mysqli_query($conn,$sql);
      $rows  = array();
        if(mysqli_num_rows($result) > 0){
          while ($r  = mysqli_fetch_assoc($result)) {
            array_push($rows, $r);
            # code...
          }
        
          // $turboIdVal = mysqli_real_escape_string($db_conn, trim($rows[0]->test_id));
          // print json_encode("success");	
        }
        
        $testparamvalue = $rows[0]['testparamvalue'];
        $testparamvalue1 = $rows[1]['testparamvalue'];
        $testparamvalue2 = $rows[2]['testparamvalue'];

        $sql  = "update enertek_combuster.testparamconfig set testparamvalue = '$testparamvalue' 
                  where testparamname = 'stage 1 Temperature'";
        $result  = mysqli_query($conn,$sql);
        $sql  = "update enertek_combuster.testparamconfig set testparamvalue = '$testparamvalue1' 
                  where testparamname = 'Stage 2 Temperature'";
        $result  = mysqli_query($conn,$sql);
        $sql  = "update enertek_combuster.testparamconfig set testparamvalue = '$testparamvalue2' 
                where testparamname = 'Stage 3 RPM'";
        $result  = mysqli_query($conn,$sql);

        $sql  = "SELECT * FROM `$page`";
        $result  = mysqli_query($conn,$sql);

        $rows  = array();

        if(mysqli_num_rows($result) > 0){
          while ($r  = mysqli_fetch_assoc($result)) {
            array_push($rows, $r);
            # code...
          }

          print json_encode($rows);
        }
    }

    if($page== 'paramconfig'){
      $sql  = "SELECT upperlimit,lowerlimit,normallimit FROM enertek_combuster.param_default";
      $result  = mysqli_query($conn,$sql);
      $rows  = array();
        if(mysqli_num_rows($result) > 0){
          while ($r  = mysqli_fetch_assoc($result)) {
            array_push($rows, $r);
            # code...
          }				
        }
        
        $upperlimit = $rows[0]['upperlimit'];
        $lowerlimit = $rows[0]['lowerlimit'];
        $normallimit = $rows[0]['normallimit'];

        $upperlimit1 = $rows[1]['upperlimit'];
        $lowerlimit1 = $rows[1]['lowerlimit'];
        $normallimit1 = $rows[1]['normallimit'];

        $upperlimit2 = $rows[2]['upperlimit'];
        $lowerlimit2 = $rows[2]['lowerlimit'];
        $normallimit2 = $rows[2]['normallimit'];

        $upperlimit3 = $rows[3]['upperlimit'];
        $lowerlimit3 = $rows[3]['lowerlimit'];
        $normallimit3 = $rows[3]['normallimit'];

        $upperlimit4 = $rows[4]['upperlimit'];
        $lowerlimit4 = $rows[4]['lowerlimit'];
        $normallimit4 = $rows[4]['normallimit'];

        $upperlimit5 = $rows[5]['upperlimit'];
        $lowerlimit5 = $rows[5]['lowerlimit'];
        $normallimit5 = $rows[5]['normallimit'];

        
        $sql  = "update enertek_combuster.paramconfig set upperlimit = '$upperlimit', lowerlimit = '$lowerlimit', normallimit = '$normallimit'  where paramconfig_id = 1";
        $result  = mysqli_query($conn,$sql);

        $sql  = "update enertek_combuster.paramconfig set upperlimit = '$upperlimit1', lowerlimit = '$lowerlimit1', normallimit = '$normallimit1'  where paramconfig_id = 2";
        $result  = mysqli_query($conn,$sql);

        $sql  = "update enertek_combuster.paramconfig set upperlimit = '$upperlimit2', lowerlimit = '$lowerlimit2', normallimit = '$normallimit2'  where paramconfig_id = 3";
        $result  = mysqli_query($conn,$sql);

        $sql  = "update enertek_combuster.paramconfig set upperlimit = '$upperlimit3', lowerlimit = '$lowerlimit3', normallimit = '$normallimit3'  where paramconfig_id = 4";
        $result  = mysqli_query($conn,$sql);

        $sql  = "update enertek_combuster.paramconfig set upperlimit = '$upperlimit4', lowerlimit = '$lowerlimit4', normallimit = '$normallimit4'  where paramconfig_id = 5";
        $result  = mysqli_query($conn,$sql);

        $sql  = "update enertek_combuster.paramconfig set upperlimit = '$upperlimit5', lowerlimit = '$lowerlimit5', normallimit = '$normallimit5'  where paramconfig_id = 6";

        $result  = mysqli_query($conn,$sql);

        $sql  = "SELECT * FROM `$page`";
        $result  = mysqli_query($conn,$sql);

        $rows  = array();

        if(mysqli_num_rows($result) > 0){
          while ($r  = mysqli_fetch_assoc($result)) {
            array_push($rows, $r);
            # code...
          }
          print json_encode($rows);
        }



    }// end of if($page == 'turboconfig'){

	}//end of  else if($editRowIndex == null)

?>


