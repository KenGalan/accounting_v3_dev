<?php
session_start();

$db = new Postgresql();

$action = $_POST['action'];
$added_by = $_SESSION['ppc']['emp_no']; 
$temp_id = $_POST['temp_id'];
// echo $action;
// exit;

if(!$action || !$temp_id){
    echo json_encode(['status'=>'error']);
    exit;
}

$conn = $db->getConnection();

pg_query($conn, "BEGIN");
try{

        // if($action == 'released' || $action == 'cleared'){

        //     if($action == 'released'){
        //         $status = 'released';
        //         $condition = "AP2.PYMT_STATE IS NULL";
        //     }

        //     if($action == 'cleared'){ 
        //         $status = 'cleared';
        //         $condition = "AP2.PYMT_STATE = 'released'";
        //     }  

        //     $hist = "
        //         INSERT INTO M_ACC_TEMP_PAYMENT_HIST
        //         (payment_id, payment_name, payment_amount, payment_date, added_by, pymt_state, customer)

        //         SELECT
        //             ATP.PAYMENT_ID,
        //             ATP.PAYMENT_NAME,
        //             ATP.PAYMENT_AMOUNT,
        //             NOW() AT TIME ZONE 'Asia/Manila',
        //             '$added_by',
        //             '$status',
        //             ATP.PARTNER_NAME

        //         FROM M_ACC_TEMP_PAYMENTS ATP
        //         JOIN ACCOUNT_PAYMENT AP2 ON AP2.ID = ATP.PAYMENT_ID
        //         WHERE $condition
        //     ";
        //     pg_query($conn, $hist); 
        //     $update = "
        //         UPDATE ACCOUNT_PAYMENT AP
        //         SET PYMT_STATE = '$status'
        //         WHERE AP.ID IN (
        //             SELECT ATP.PAYMENT_ID
        //             FROM M_ACC_TEMP_PAYMENTS ATP
        //             JOIN ACCOUNT_PAYMENT AP2 ON AP2.ID = ATP.PAYMENT_ID
        //             WHERE $condition
        //         )
        //     ";
        //     pg_query($conn, $update); 

        //     $delete = "
        //         DELETE FROM M_ACC_TEMP_PAYMENTS
        //         WHERE PAYMENT_ID IN (
        //             SELECT ATP.PAYMENT_ID
        //             FROM M_ACC_TEMP_PAYMENTS ATP
        //             JOIN ACCOUNT_PAYMENT AP2 ON AP2.ID = ATP.PAYMENT_ID
        //             WHERE $condition
        //         )
        //     ";
        //     pg_query($conn, $delete);
        // }

        if($action == 'released' || $action == 'cleared'){

            if($action == 'released'){
                $status = 'released';
                $condition = "AP2.PYMT_STATE IS NULL";
            }

            if($action == 'cleared'){  
                $status = 'cleared';
                $condition = "(AP2.PYMT_STATE IS NULL OR AP2.PYMT_STATE = 'released')";
            }  

            $idsSql = "
                SELECT ATP.PAYMENT_ID
                FROM M_ACC_TEMP_PAYMENTS ATP
                JOIN ACCOUNT_PAYMENT AP2 ON AP2.ID = ATP.PAYMENT_ID
                WHERE ATP.temp_id = '$temp_id'
                AND ATP.added_by = '$added_by'
                AND $condition
            ";

            $idsResult = pg_query($conn, $idsSql);

            $paymentIds = []; 
            while($row = pg_fetch_assoc($idsResult)){
                $paymentIds[] = $row['payment_id'];
            }

            if(empty($paymentIds)){
                pg_query($conn, "COMMIT");
                echo json_encode(['status'=>'success']);
                exit;
            }

            $ids = implode(',', $paymentIds);

            $hist = "
                INSERT INTO M_ACC_TEMP_PAYMENT_HIST
                (payment_id, payment_name, payment_amount, payment_date, added_by, pymt_state, customer)

                SELECT
                    ATP.PAYMENT_ID,
                    ATP.PAYMENT_NAME,
                    ATP.PAYMENT_AMOUNT,
                    NOW() AT TIME ZONE 'Asia/Manila',
                    '$added_by',
                    '$status',
                    ATP.PARTNER_NAME

                FROM M_ACC_TEMP_PAYMENTS ATP
                WHERE ATP.PAYMENT_ID IN ($ids)
            ";
            pg_query($conn, $hist); 

            $update = "
                UPDATE ACCOUNT_PAYMENT
                SET PYMT_STATE = '$status'
                WHERE ID IN ($ids)
            ";
            pg_query($conn, $update); 

            $delete = "
                DELETE FROM M_ACC_TEMP_PAYMENTS
                WHERE temp_id = '$temp_id'
                AND added_by = '$added_by'
            ";
            pg_query($conn, $delete);
        }

    // if($action == 'delete'){

    //     $delete = "DELETE FROM M_ACC_TEMP_PAYMENTS";
    //     pg_query($conn, $delete);

    // }

    pg_query($conn, "COMMIT");

    echo json_encode(['status'=>'success']);

}catch(Exception $e){

    pg_query($conn, "ROLLBACK");

    echo json_encode([
        'status'=>'error',
        'message'=>$e->getMessage()
    ]);
}