<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E- Wallet Cashout Machine Application</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/jqbtk/jqbtk.min.css" />
    <link rel="stylesheet" href="assets/css/styles.css">
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

    $reference = $_GET['reference'];
    $transObj = json_decode(get_request("transaction.php?action=reference&reference=".$reference));
    $amount = $transObj->Amount;
    $systemId = $transObj->SystemId;

    $billObj = json_decode(get_request("bill.php?action=bymachine&id=$systemId"));
    $bc50 = $billObj->b50;
    $bc100 = $billObj->b100;
    $bc200 = $billObj->b200;
    $bc500 = $billObj->b500;
    $bc1000 = $billObj->b1000;


    if(isset($_POST['dispense'])){
        $cmd = "python Dispense-Cash.py $amount $reference $systemId $bc50 $bc100 $bc200 $bc500 $bc1000";
        echo $cmd;
        //exec($cmd);
    }

    if(isset($_POST['cancel']))
        $updObj = json_decode(get_request("transaction.php?action=update&sb=status&status=2&reference=$reference"));
        if(!empty($updObj))
            header("Location: index.php");
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
                    <button type="submit" name="cancel" id="dispense" >Cancel</button>
                </form>
            </center>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    </body>
</html>