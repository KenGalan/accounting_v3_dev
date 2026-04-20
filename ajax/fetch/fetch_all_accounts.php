<?php
$db = new Postgresql();
header('Content-Type: application/json');
$accountQuery = " SELECT distinct ID, code || ' ' || NAME full_name,
code || ' ' || NAME acc_fullname,
 name FROM ACCOUNT_ACCOUNT
 where company_id =1
 ";
$accounts = $db->fetchAll($accountQuery);

echo json_encode($accounts);
