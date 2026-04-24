<?php
$db = new Postgresql();
header('Content-Type: application/json');
$accountQuery = " SELECT ID AS JOURNAL_ID, CODE || ' ' || NAME AS JOURNAL_ACC
FROM ACCOUNT_JOURNAL
 ";
$accounts = $db->fetchAll($accountQuery);

echo json_encode($accounts);
