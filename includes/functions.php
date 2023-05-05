<?php

function OpenConnection(){
    $serverName = "192.168.254.121,1433";
    $connectionOptions = array("Database"=>"CashOut",
        "Uid"=>"couser", "PWD"=>"qqQQ11!!");
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if($conn == false)
        die(FormatErrors(sqlsrv_errors()));

    return $conn;
}

function db_status_to_word($status){
    switch($status){
        case 1: return "COMPLETED"; break;
        case 2: return "CANCELED"; break;
        default: return "PENDING";
    }
}

function get_base_url(){
    return "http://".$_SERVER['HTTP_HOST'].'/admin';
}

function in_open_session(){
    if(isset($_SESSION['isLoggedIn'])){
        header("Location: http://".$_SERVER['HTTP_HOST'].'/admin');
    }
}

function in_close_session(){
    if(!isset($_SESSION['isLoggedIn'])){
        header("Location: http://".$_SERVER['HTTP_HOST'].'/admin/login.php');
    }
}

function update_machine_balance($machineId){
    $bc50 = 0;
    $bc100 = 0;
    $bc200 = 0;
    $bc500 = 0;
    $bc1000 = 0;
    
    try {
        $conn = OpenConnection();
        $sqlQry = "SELECT * FROM [BillCounter] WHERE SystemId = ". $machineId;
        $getRecord = sqlsrv_query($conn, $sqlQry);
        if ($getRecord == FALSE)
            die(FormatErrors(sqlsrv_errors()));

        while($row = sqlsrv_fetch_array($getRecord, SQLSRV_FETCH_ASSOC))
        {
            $bc50 = $row['50Bill'];
            $bc100 = $row['100Bill'];
            $bc200 = $row['200Bill'];
            $bc500 = $row['500Bill'];
            $bc1000 = $row['1000Bill'];
        }
        
        sqlsrv_free_stmt($getRecord);
        sqlsrv_close($conn);
    } catch(Exception $e){}

    $total = 0;
    if($bc50 > 0)
        $total = $total + (50 * $bc50);
    
    if($bc100 > 0)
        $total = $total + (100 * $bc100);

    if($bc200 > 0)
        $total = $total + (200 * $bc200);

    if($bc500 > 0)
        $total = $total + (500 * $bc500);

    if($bc1000 > 0)
        $total = $total + (1000 * $bc1000);

    try {
        $conn = OpenConnection();
        if (sqlsrv_begin_transaction($conn) == FALSE){
            die(sqlsrv_errors());
        }

        $sqlQry = "UPDATE [System] SET [Balance] = ".$total." WHERE Id = ". $machineId;
        $exec = sqlsrv_query($conn, $sqlQry);

        if($exec){
            sqlsrv_commit($conn);
        }
        
        sqlsrv_free_stmt($exec);
        sqlsrv_close($conn);
    } catch(Exception $e){
        die(sqlsrv_errors());
    }
}

function add_machine_accumulated_ammount($machineId, $fee){
    try {
        $conn = OpenConnection();
        if (sqlsrv_begin_transaction($conn) == FALSE){
            die(sqlsrv_errors());
        }

        $sqlQry = "UPDATE [System] SET [AccumulatedAmount] = [AccumulatedAmount] + ".$fee." WHERE Id = ". $machineId;
        $exec = sqlsrv_query($conn, $sqlQry);

        if($exec){
            sqlsrv_commit($conn);
        }
        
        sqlsrv_free_stmt($exec);
        sqlsrv_close($conn);
    } catch(Exception $e){
        die(sqlsrv_errors());
    }
}

function update_contact_balance($contactId, $total){
    try {
        $conn = OpenConnection();
        if (sqlsrv_begin_transaction($conn) == FALSE){
            die(sqlsrv_errors());
        }

        $sqlQry = "UPDATE [Wallet] SET [Balance] = [Balance] - ".$total." WHERE ContactId = ". $contactId;
        $exec = sqlsrv_query($conn, $sqlQry);

        if($exec){
            sqlsrv_commit($conn);
        }
        
        sqlsrv_free_stmt($exec);
        sqlsrv_close($conn);
    } catch(Exception $e){
        die(sqlsrv_errors());
    }
}

function update_transaction_status($reference, $status){
    try {
        $conn = OpenConnection();
        if (sqlsrv_begin_transaction($conn) == FALSE){
            die(sqlsrv_errors());
        }

        $sqlQry = "UPDATE [Transaction] SET [Status] = ".$status." WHERE [Reference] = '". $reference."'";
        $exec = sqlsrv_query($conn, $sqlQry);

        if($exec){
            sqlsrv_commit($conn);
        }
        
        sqlsrv_free_stmt($exec);
        sqlsrv_close($conn);
    } catch(Exception $e){
        die(sqlsrv_errors());
    }
}

?>