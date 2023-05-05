<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E- Wallet Cashout Machine Application</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/jqbtk/jqbtk.min.css" />
    <link rel="stylesheet" href="assets/css/styles.css">
    <script>
      <?php
        echo "var systemId = 1;";
        echo "var apiUrl = 'http://dev.cashout.com/api/';";
      ?>
    </script>
  </head>
  <body>
    <div class="container my-3 py-3">
        <div class="text-center">
            <h2> Welcome to E-Wallet Cashout Application</h2>
        </div>
    </div>
    <hr>

    <?php 
    include('includes/functions.php');

    $amount = 0;
    $systemId = 0;
    $reference = $_GET['reference'];

    try {
        $conn = OpenConnection();
        $sqlQry = "SELECT * FROM [Transaction] WHERE [Reference] = '". $_GET['reference']."'";
        $getRecord = sqlsrv_query($conn, $sqlQry);
        if ($getRecord == FALSE)
            die(FormatErrors(sqlsrv_errors()));

        while($row = sqlsrv_fetch_array($getRecord, SQLSRV_FETCH_ASSOC))
        {
            $amount = $row['Amount'];
            $systemId = $row['SystemId'];
        }
        
        sqlsrv_free_stmt($getRecord);
        sqlsrv_close($conn);
    } catch(Exception $e){
        
    }

    $bc50 = 0;
    $bc100 = 0;
    $bc200 = 0;
    $bc500 = 0;
    $bc1000 = 0;
    
    try {
        $conn = OpenConnection();
        $sqlQry = "SELECT * FROM [BillCounter] WHERE SystemId = $systemId";
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
    } catch(Exception $e){
    }


    if(isset($_POST['dispense'])){
        $cmd = "python Dispense-Cash.py $amount $reference $systemId $bc50 $bc100 $bc200 $bc500 $bc1000";
        //exec($cmd);
    }

    ?>

    <div class="container my-4 py-4">
        <div class="col-md-12">
            <center>
                <div id="qrcode"></div>
            </center>
        </div>
        <div class="col-md-12" style="padding-top: 15px">
            <center>
                <form method="POST" action="<?=$_SERVER['REQUEST_URI']?>">
                    <input type="text" id="refNo" value="<?=$reference?>" />
                    <button type="submit" name="dispense" id="dispense" >Dispense</button>
                </form>
            </center>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/sweetalert.min.js"></script>
    <script src="assets/js/qrcode.js"></script>
    <script>
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "http://jindo.dev.naver.com/collie",
            width: 300,
            height: 300,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    </script>
    </body>
</html>