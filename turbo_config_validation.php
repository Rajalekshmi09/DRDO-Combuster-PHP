<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
require 'db_connection.php';
require 'log.php';

// POST DATA
  $data = json_decode(file_get_contents("php://input"));
  $turbo_id = isset($data) ? $data->turbo_id : 0; 
  $date  = isset($data) ? $data->date : ''; 
  $nozzle_area  = isset($data) ? $data->nozzle_area : 0; 
  $description  = isset($data) ? $data->descriptions : ' '; 
  $noofblades  = isset($data) ? $data->noofblades : 0; 

  $conn = $db_conn;

    if(!$conn)
    {   
        die("connection faild:" .$conn-> connect_error);
        return;
    }
        
      $insertUser = mysqli_query($db_conn,"INSERT INTO `enertek_combuster`.`turboconfig`(`turboname`,`installeddate`,`nozzlearea`,`numofblades`,`description`,`status`,`completiondate`)VALUES('$turbo_id','$date','$nozzle_area','$noofblades', '$description','installed',now())");

        // $count = mysqli_num_rows($insertUser);  
        if($insertUser){    

            $sql  = "SELECT turboconfig_id,turboname,installeddate,nozzlearea,numofblades,description,status FROM enertek_combuster.turboconfig where status != 'Completed' order by  turboconfig_id desc";
                $result  = mysqli_query($conn,$sql);
                if(!$result){
                    wh_log("Trubine Insert : " . $db_conn -> error);
                }
                $rows  = array();

            if(mysqli_num_rows($result) > 0){
                while ($r  = mysqli_fetch_assoc($result)) {
                    array_push($rows, $r);
                    # code...
                }

                print json_encode($rows);
                

        }   
                }
                
        else{
         //    echo json_encode(
         //    http_response_code(404);
         // );
            print json_encode("no_data");
        }
    
   
    
// }
// else{
//     echo json_encode(["success"=>0,"msg"=>"Please fill all the required fields!"]);
// }