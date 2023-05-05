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
    try {
        $conn = OpenConnection();
        if (sqlsrv_begin_transaction($conn) == FALSE){
            $dbFlag = 2;
        }

        $sqlQry = "UPDATE [BillCounter] SET 
                        [50Bill] = [50Bill] - ".$bc50.", 
                        [100Bill] = [100Bill] - ".$bc100.", 
                        [200Bill] = [200Bill] - ".$bc200.", 
                        [500Bill] = [500Bill] - ".$bc500.", 
                        [1000Bill] = [1000Bill] - ".$bc1000." 
                    WHERE SystemId = ". $systemId;
        $exec = sqlsrv_query($conn, $sqlQry);
        
        if($exec){
            sqlsrv_commit($conn);
            $dbFlag = 1;
        }
        
        sqlsrv_free_stmt($exec);
        sqlsrv_close($conn);
    } catch(Exception $e){
        $dbFlag = 2;
    }
    
    if($dbFlag == 1){
        //-- Print Transaction
        try {
            $conn = OpenConnection();
            $sqlQry = "SELECT 
                            t.*,
                            c.FullName,
                            c.PhoneNo,
                            (SELECT w.Balance FROM [Wallet] w WHERE w.ContactId = t.ContactId) as 'Balance',
                            (SELECT rr.Fee FROM RateRange rr WHERE rr.Id = t.RateRangeId) as 'Fee'
                        FROM [Transaction] t JOIN [Contact] c 
                            ON t.ContactId = c.Id
                    WHERE [Reference] = '". $_GET['reference']."'";
            $getRecord = sqlsrv_query($conn, $sqlQry);
            if ($getRecord == FALSE)
                die(FormatErrors(sqlsrv_errors()));

            $name = "";
            $amount = 0;
            $phone = "";
            $date = "";
            $reference = "";
            $fee = 0;
            $contactId = 0;
            $balance = 0;
            $total = 0;

            while($row = sqlsrv_fetch_array($getRecord, SQLSRV_FETCH_ASSOC))
            {
                $name = $row['FullName'];
                $amount = $row['Amount'];
                $phone = $row['PhoneNo'];
                $date = (string)$row['CreatedDate']->format('Y-m-d H:i:s');
                $reference = $row['Reference'];
                $fee = $row['Fee'];
                $contactId = $row['ContactId'];
                $balance = $row['Balance'];
                $total = $amount + $fee;
            }

            $commands = array (

                'echo "  AUTOMATED CASHOUT MACHINE   "> /dev/usb/lp0',
                'echo "____________________________"> /dev/usb/lp0',
                'echo "\n"> /dev/usb/lp0',
                'echo "Name: '.$name.'"> /dev/usb/lp0',
                'echo "Account: '.$phone.'"> /dev/usb/lp0',
                'echo "Date: '.$date.'"> /dev/usb/lp0',
                'echo "Reference: '.$reference.'"> /dev/usb/lp0',
                'echo "Amount: '.$amount.'"> /dev/usb/lp0',
                'echo "Fee: '.$fee.'"> /dev/usb/lp0',
                'echo "Total Deduction: '.$total.'"> /dev/usb/lp0',
                'echo "\n"> /dev/usb/lp0',
                'echo "____________________________"> /dev/usb/lp0',
                'echo "\n\n"> /dev/usb/lp0',
            );
            
            foreach ($commands as $command) {
                exec($command);
            }

            sqlsrv_free_stmt($getRecord);
            sqlsrv_close($conn);

            update_machine_balance($systemId);
            update_transaction_status($reference, 1);
            json_decode(get_request("transaction.php?action=update&sb=status&status=1&reference=$reference"));
            add_machine_accumulated_ammount($systemId,$fee);
            update_contact_balance($contactId,$total);
        } catch(Exception $e){
            
        }
    }
?>