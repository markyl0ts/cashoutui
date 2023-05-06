<?php

function get_request($url){
    $baseUrl = "http://localhost:8080/connector/";
    $curlCon = curl_init();
    curl_setopt($curlCon, CURLOPT_URL, $baseUrl.$url);
    curl_setopt($curlCon, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($curlCon);
    curl_close($curlCon);

    return $response;
}

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
    $billObj = json_decode(get_request("bill.php?action=bymachine&id=$machineId"));
    $bc50 = $billObj->b50;
    $bc100 = $billObj->b100;
    $bc200 = $billObj->b200;
    $bc500 = $billObj->b500;
    $bc1000 = $billObj->b1000;

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

    $updObj = json_decode(get_request("machine.php?action=update&field=balance&total=$total&id=$machineId"));
}
?>