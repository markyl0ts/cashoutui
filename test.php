<?php

$serverName = "192.168.254.121,1433";
$connectionOptions = array("Database"=>"CashOut",
    "Uid"=>"couser", "PWD"=>"qqQQ11!!");
$conn = sqlsrv_connect($serverName, $connectionOptions);
if($conn == false)
    echo "Not connected";
else
    echo "connected";

sqlsrv_close($conn);

?>