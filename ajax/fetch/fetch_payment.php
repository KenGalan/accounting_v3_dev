<?php

$db = new Postgresql();

$whereStatus = "";
$released = isset($_GET['released']) && $_GET['released'] == 1;
$cleared = isset($_GET['cleared']) && $_GET['cleared'] == 1;

if($released && !$cleared){
    $whereStatus = " AND AP.PYMT_STATE = 'released' ";
}
elseif(!$released && $cleared){
    $whereStatus = " AND AP.PYMT_STATE = 'cleared' ";
}
elseif($released && $cleared){
    $whereStatus = " AND AP.PYMT_STATE IN ('released','cleared') ";
}
else{
    $whereStatus = " AND AP.PYMT_STATE IS NULL ";
}

$sql = "
    SELECT 
        AP.NAME,
        AP.ID AS PAYMENT_ID,
        AP.TO_PHP AS AMOUNT,
        CASE WHEN AP.PYMT_STATE IS NULL THEN '-' ELSE AP.PYMT_STATE END AS STATE,
        TO_CHAR(AP.CREATE_DATE AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila'
        , 'Mon DD, YYYY' ) AS DATE,
        RP.NAME AS PARTNER_NAME,
        CASE WHEN ATP.PAYMENT_ID IS NOT NULL THEN 1 ELSE 0 END AS IS_SELECTED
    FROM ACCOUNT_PAYMENT AP
    JOIN RES_PARTNER RP ON AP.PARTNER_ID = RP.ID
    LEFT JOIN M_ACC_TEMP_PAYMENTS ATP ON AP.ID = ATP.PAYMENT_ID
    WHERE AP.NAME ILIKE '%PYMT%'
    AND AP.PARTNER_TYPE = 'supplier'
    AND AP.STATE = 'posted'
    $whereStatus
    ORDER BY AP.CREATE_DATE DESC
";

$rows = $db->fetchAll($sql);

$data = [];

if($rows){
    foreach($rows as $row){ 
        $data[] = [
            'name' => $row['name'],
            'state' => $row['state'],
            'partner' => $row['partner_name'],
            'payment_id' => $row['payment_id'],
            'amount' => $row['amount'],
            'date' => $row['date'],
            'is_selected' => $row['is_selected']
        ];
    }
}

echo json_encode(['data'=>$data]);