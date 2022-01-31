<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: access");
header("Access-Control-Allow-Methods: POST");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db_connection.php';

// POST DATA
$data = json_decode(file_get_contents("php://input"));
if(isset($data->start_date) 
    && isset($data->end_date) 
    && !empty(trim($data->start_date)) 
    && !empty(trim($data->end_date))
    ){
        
        $currentPage = $_GET['currentPage'];
        $recordsPerPage = $_GET['recordsPerPage'];
        $nextPage = $currentPage*$recordsPerPage;
        $start_date = mysqli_real_escape_string($db_conn, trim($data->start_date));
        $end_date = mysqli_real_escape_string($db_conn, trim($data->end_date));
        
        $start_date_formatted = date_format(date_create($start_date), 'Y-m-d H:i:s');
        $end_date_formatted = date_format(date_create($end_date), 'Y-m-d H:i:s');
        
        $countRow = mysqli_query($db_conn, "SELECT COUNT(*) AS totalCount FROM data WHERE date_Time BETWEEN '" . $start_date_formatted . "' AND  '" . $end_date_formatted."'");
        $totalCount = mysqli_fetch_assoc($countRow);

        $insertUser = mysqli_query($db_conn,"SELECT * FROM data WHERE date_Time BETWEEN '" . $start_date_formatted . "' AND  '" . $end_date_formatted . "' ORDER by data_id DESC limit ".$recordsPerPage." offset ".$nextPage);

        $count = mysqli_num_rows($insertUser);  
        $rows  = array();
        if($count > 0){  
            while ($r  = mysqli_fetch_assoc($insertUser)) {
            array_push($rows, $r);
            # code...
            }  
            //$last_id = mysqli_insert_id($db_conn);
            $output = array('totalCount'=>$totalCount['totalCount'], 'records'=>$rows);

            print json_encode($output);

            //echo json_encode(["Login success"]);
        }
        else{
            echo json_encode(["login failed"]);
        }  
   
    
}
else{
    echo json_encode(["success"=>0,"msg"=>"Please fill all the required fields!"]);
}