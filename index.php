<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>E- Wallet Cashout Machine Application</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/plugins/jqbtk/jqbtk.min.css" />
    <link rel="stylesheet" href="assets/css/styles.css">
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
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
      $systemId = 1; //-- Machine ID - Needs to update whenever you want to change machine configuration
      $formFlag = 0;
      $formMsg = "";
      $isAllowedToTransact = true;
      $isMachineConfigured = false;
      $machineBalance = 0;
      $machineRateId = 0;
      $rateRangeId = 0;

      $machineObj = json_decode(get_request("machine.php?action=get&id=$systemId"));
      
      //-- Check machine configuration
      if(!empty($machineObj)){
        $isMachineConfigured = true;
        $machineRateId = $machineObj->RateId;
        $machineBalance = $machineObj->Balance;
      }

      if(!$isMachineConfigured){
        $isAllowedToTransact = false;
        $formFlag = 2;
        $formMsg = "Machine is not configured! Contact machine administrator";
      }

      //-- Machine balance zero or no bills configured
      if($machineBalance == 0 && $isMachineConfigured){
        $isMachineConfigured = false;
        $isAllowedToTransact = false;
        $formFlag = 2;
        $formMsg = "Machine balance is zero! Contact machine administrator";
      }

      $isRateConfigured = false;
      //-- Check machine rating has range fees
      if($isMachineConfigured){
        $rateRangeObj = json_decode(get_request("rate_range.php?action=byrate&id=$machineRateId"));
        if(count($rateRangeObj) > 0)
          $isRateConfigured = true;
      }

      if($isMachineConfigured && !$isRateConfigured){
        $formFlag = 2;
        $formMsg = "Machine rate is not configured! Contact machine administrator";
      }

      $amount = "";
      $phone = "";
      if(isset($_POST['enter']))
      {
        $amount = $_POST['amount'];
        $phone = $_POST['phone'];
        $contactId = 0;
        $isContactFound = false;
        
        if($amount % 50 == 0)
        {
          //-- Check contact
          $contactObj = json_decode(get_request("contact.php?action=byphone&phone=$phone"));
          if(!empty($contactObj))
          {
            $isContactFound = true;
            $contactId = $contactObj->id;
          }

          if(!$isContactFound)
          {
            $formFlag = 2;
            $formMsg = "No contact data found using that phone number!";
          }
          else
          {
            $isMachineBalanceAndAmountValid = false;
            if($amount > $machineBalance)
            {
              $formFlag = 2;
              $formMsg = "You entered amount more than machine can dispense";
            }
            else
            {
              //-- Check wallet balance
              $isEnoughtWalletBalance = false;
              $walletBalance = 0;
              $walletObj = json_decode(get_request("wallet.php?action=bycontact&id=$contactId"));
              if(!empty($walletObj))
              {
                if($walletObj->Balance > $amount)
                  $isEnoughtWalletBalance = true;
              }

              if(!$isEnoughtWalletBalance)
              {
                $formFlag = 2;
                $formMsg = "Wallet balance is not enough!";
              }
              else
              {
                //-- Show confirmation
                echo "
                  <script>
                    $(function(){
                      $('#confirmModal').modal('show');
                    });
                  </script>
                ";
              }
            }
          }
        }
        else
        {
          $formFlag = 2;
          $formMsg = "Amount should be divisible by 50";
        }
      }

      if(isset($_POST['cancel'])){
        header("Location: ".$_SERVER['REQUEST_URI']);
      }

      if(isset($_POST['confirm'])){
        foreach($rateRangeObj as $rr){
          $sr = $rr->StartRange;
          $er = $rr->EndRange;
          if($sr > $amount && $amount <= $er)
            $rateRangeId = $rr->Id; break;
        }

        $amount = $_POST['amount'];
        $phone = $_POST['phone'];
        $contactObj = json_decode(get_request("contact.php?action=byphone&phone=$phone"));
        $insObj = json_decode(get_request("transaction.php?action=add&systemId=$systemId&contactId=".$contactObj->id."&rateRange=$rateRangeId&amount=$amount"));
        $transObj = json_decode(get_request("transaction.php?action=get&id=".$insObj->Id));
        
        header("Location: transaction.php?guid=".$transObj->guid."&reference=".$transObj->Reference);
      }
    ?>
    <form method="POST" action="<?=$_SERVER['REQUEST_URI']?>" >
      <div class="container my-4 py-4">
          <div class="text-center" id="phase1">
              <h3 class="fw-bold"> TO START THE TRANSACTION PLEASE INPUT THE AMOUNT YOU WISH TO CASH-OUT</h3>
              <h5><em>(Note: You must input an amount that is divisible by 50. The minimum input cash to be cashout is 50 and the maximum amount is 
                5000.)</em></h5>
              <br>
              <?php if($formFlag > 0){ ?>
                <div class="alert alert-<?=($formFlag == 1) ? "success" : "danger"?>" role="alert">
                    <?=$formMsg?>
                </div>
              <?php } ?>
              <p><input type="tel" name="amount" value="<?=$amount?>" <?=(!$isAllowedToTransact) ? "disabled" : ""?> class="keyboard form-control keyboard-numpad my-4 py-2 text-center" id="amount" placeholder="Enter Amount"/></p>
              <p><input type="tel" name="phone" value="<?=$phone?>" <?=(!$isAllowedToTransact) ? "disabled" : ""?> class="keyboard form-control keyboard-numpad my-4 py-2 text-center" id="telephone" placeholder="Enter Phone Number"/></p>
              <button type="submit" name="enter" class="btn btn-primary" id="btnSubmit" <?=(!$isAllowedToTransact) ? "disabled" : ""?>> ENTER </button>
          </div>
      </div>

      <div class="modal fade" id="confirmModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="confirmModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title" id="myModalLabel">Cashout Confirmation</h4>
            </div>
            <div class="modal-body">
              <h3>Do you wish to continue the transaction?</h3>
            </div>
            <div class="modal-footer">
              <button type="submit" name="cancel" class="btn btn-secondary">Cancel</button>
              <button type="submit" name="confirm" class="btn btn-primary">Confirm</button>
            </div>
          </div>
        </div>
      </div>
    </form>

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