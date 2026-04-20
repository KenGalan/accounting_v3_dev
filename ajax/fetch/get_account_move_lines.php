<?php
$db = new PostgresqlKen();
$move_id = $_POST['am_id'];
// $yearMonth = '2026-02';
$q = "	  
SELECT 
            aml.move_name,
            aa.code ||' ' || aa.name AS account_name,
            am.state AS status,
            am.type,
            aml.name item_label,
            aml.date,
            aml.debit,
            aml.credit,
            am.id,
            aml.id AS aml_id,
            aaa.name AS account_analytic
        FROM account_move_line aml
        JOIN account_move am ON aml.move_id = am.id
        JOIN ACCOUNT_ACCOUNT aa ON aml.account_id = aa.id
        LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT aaa ON aml.analytic_account_id = aaa.id
        WHERE aml.move_id = $1
";
$result = $db->fetchAll($q, [$move_id]);

// echo json_encode($result);
echo json_encode([
    'status' => 'success',
    'data' => $result
]);
