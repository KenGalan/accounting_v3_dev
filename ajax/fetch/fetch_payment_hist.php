<?php

$db = new Postgresql();

$sql = 
"SELECT ATPH.PAYMENT_NAME, ATPH.PAYMENT_AMOUNT, TO_CHAR(ATPH.ADDED_ON AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila'
        , 'Mon DD, YYYY' ) AS ADDED_ON, ATPH.PYMT_STATE, HE.FULLNAME, ATPH.CUSTOMER AS PARTNER_NAME
	FROM M_ACC_TEMP_PAYMENT_HIST ATPH
	JOIN M_HRIS_EMP HE ON ATPH.ADDED_BY = HE.EMPLOYEE_NO
	ORDER BY ATPH.ADDED_ON DESC";

$rows = $db->fetchAll($sql);

$data = [];

if($rows){
    foreach($rows as $row){ 
        $data[] = [
            'payment_name' => $row['payment_name'],
            'payment_amount' => $row['payment_amount'],
            'added_on' => $row['added_on'],
            'pymt_state' => $row['pymt_state'],
            'fullname' => $row['fullname'],
            'partner_name' => $row['partner_name'],
        ];
    }
}

echo json_encode(['data'=>$data]);