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
      $systemId = 1;
    ?>

    <div class="container my-4 py-4">
        <div class="text-center" id="phase1">
            <h3 class="fw-bold"> TO START THE TRANSACTION PLEASE INPUT THE AMOUNT YOU WISH TO CASH-OUT</h3>
            <h5><em>(Note: You must input an amount that is divisible by 50. The minimum input cash to be cashout is 50 and the maximum amount is 
              5000.)</em></h5>
            <br>
            <p><input type="tel" class="keyboard form-control keyboard-numpad my-4 py-2 text-center" id="amount" placeholder="Enter Amount"/></p>
            <p><input type="tel" class="keyboard form-control keyboard-numpad my-4 py-2 text-center" id="telephone" placeholder="Enter Phone Number"/></p>
            <button type="sbumit" class="btn btn-primary" id="btnSubmit"> ENTER </button>
        </div>
    </div>

    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/sweetalert.min.js"></script>
    <script src="assets/plugins/jqbtk/jqbtk.min.js"></script>
    <script>
      $(function(){
        $('#amount').keyboard();
        $('#telephone').keyboard();
      });
    </script>
  </body>
</html>