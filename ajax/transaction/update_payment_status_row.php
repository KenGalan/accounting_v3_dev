<?php

$db = new Postgresql();

$payment_id = $_POST['payment_id'];
$status     = $_POST['status'];

if(!$payment_id || !$status){
    echo json_encode(['status'=>'error']);
    exit;
}

$conn = $db->getConnection();

pg_query($conn,"BEGIN");

try{

    $update = "
        UPDATE ACCOUNT_PAYMENT
        SET PYMT_STATE = '$status'
        WHERE ID = ".intval($payment_id)."
    ";

    pg_query($conn,$update);

    $delete = "
        DELETE FROM M_ACC_TEMP_PAYMENTS
        WHERE PAYMENT_ID = ".intval($payment_id)."
    ";

    pg_query($conn,$delete);

    pg_query($conn,"COMMIT");

    echo json_encode(['status'=>'success']);

}catch(Exception $e){

    pg_query($conn,"ROLLBACK");

    echo json_encode(['status'=>'error']);
}