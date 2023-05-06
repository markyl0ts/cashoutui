<?php 
    include('includes/functions.php');
    $billRaw = $_GET['bill'];
    $systemId = $_GET['machineId'];
    $reference = $_GET['reference'];

    $billArr = explode("|",$billRaw);
    $bc50 = explode("-",$billArr[0])[1];
    $bc100 = explode("-",$billArr[1])[1];
    $bc200 = explode("-",$billArr[2])[1];
    $bc500 = explode("-",$billArr[3])[1];
    $bc1000 = explode("-",$billArr[4])[1];
    $dbFlag = 0;

    //-- Update bill counter
    $billUpdObj = json_decode(get_request("bill.php?action=update&systemId=$systemId&bc50=$bc50&bc100=$bc100&bc200=$bc200&bc500=$bc500&bc1000=$bc1000"));
    
    if($billUpdObj->trans == 1){
        //-- Print Transaction
        $printObj = json_decode(get_request("custom.php?action=print_data&reference=$reference"));

        $commands = array (

            'echo "  AUTOMATED CASHOUT MACHINE   "> /dev/usb/lp0',
            'echo "____________________________"> /dev/usb/lp0',
            'echo "\n"> /dev/usb/lp0',
            'echo "Name: '.$printObj->FullName.'"> /dev/usb/lp0',
            'echo "Account: '.$printObj->PhoneNo.'"> /dev/usb/lp0',
            'echo "Date: '.(string)$printObj->CreatedDate->date.'"> /dev/usb/lp0',
            'echo "Reference: '.$reference.'"> /dev/usb/lp0',
            'echo "Amount: '.$printObj->Amount.'"> /dev/usb/lp0',
            'echo "Fee: '.$printObj->Fee.'"> /dev/usb/lp0',
            'echo "Total Deduction: '.($printObj->Amount + $printObj->Fee).'"> /dev/usb/lp0',
            'echo "\n"> /dev/usb/lp0',
            'echo "____________________________"> /dev/usb/lp0',
            'echo "\n\n"> /dev/usb/lp0',
        );

        foreach ($commands as $command) {
            exec($command);
        }

        //-- Update Balance
        update_machine_balance($printObj->SystemId);
        //-- Update Status
        json_decode(get_request("transaction.php?action=update&sb=status&status=1&reference=$reference"));
        //-- Update accumulate amount
        json_decode(get_request("machine.php?action=update&field=AccuAmount&fee=".$printObj->Fee."&id=$systemId"));
        //-- Update wallet balance
        json_decode(get_request("wallet.php?action=update&field=balance&balance=".($printObj->Amount + $printObj->Fee)."&contactId=$printObj->ContactId"));
    }
?>