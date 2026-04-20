<?php
session_start();

$db = new Postgresql();

$payments = $_POST['payments'];
$added_by = $_SESSION['ppc']['emp_no']; 

if(empty($payments)){
    echo json_encode(['status'=>'error']);
    exit;
}

$ids = implode(",", array_map('intval',$payments));

$checkSql = "
    SELECT temp_id 
    FROM M_ACC_TEMP_PAYMENTS 
    WHERE payment_id IN ($ids)
    AND added_by = '$added_by'
    LIMIT 1
";

$existing = $db->fetchRow($checkSql);

if($existing){
    $temp_id = $existing['temp_id']; 
} else {
    $temp_id = uniqid('TMP_');
}

$sql = "
    INSERT INTO M_ACC_TEMP_PAYMENTS
    (temp_id, payment_id, payment_name, payment_status, payment_date, partner_name, payment_amount, added_by)

    SELECT 
        '$temp_id',
        AP.ID,
        AP.NAME,
        AP.PYMT_STATE,
        NOW() AT TIME ZONE 'Asia/Manila',
        RP.NAME,
        AP.TO_PHP,
        '$added_by' 

    FROM ACCOUNT_PAYMENT AP
    JOIN RES_PARTNER RP ON AP.PARTNER_ID = RP.ID

    WHERE AP.ID IN ($ids)

    AND NOT EXISTS (
        SELECT 1 
        FROM M_ACC_TEMP_PAYMENTS ATP
        WHERE ATP.PAYMENT_ID = AP.ID
        AND ATP.added_by = '$added_by'
    )
"; 

$db->query($sql);

echo json_encode([
    'status'=>'success',
    'temp_id'=>$temp_id 
]);