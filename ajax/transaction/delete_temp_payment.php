<?php

$db = new Postgresql();

$payment_id = $_POST['payment_id'];

if(!$payment_id){
    echo json_encode(['status'=>'error']);
    exit;
}

$sql = "
    DELETE FROM M_ACC_TEMP_PAYMENTS
    WHERE PAYMENT_ID = ".intval($payment_id)."
";

$db->query($sql);

echo json_encode(['status'=>'success']);