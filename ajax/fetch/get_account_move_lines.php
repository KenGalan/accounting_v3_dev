<?php
$db = new PostgresqlKen();
$move_id = $_POST['am_id'];
// $yearMonth = '2026-02';
// $q = "	  
// SELECT 
//             aml.move_name,
//             aa.code ||' ' || aa.name AS account_name,
//             am.state AS status,
//             am.type,
//             aml.name item_label,
//             aml.date,
//             aml.debit,
//             aml.credit,
//             am.id,
//             aml.id AS aml_id,
//             aaa.name AS account_analytic
//         FROM account_move_line aml
//         JOIN account_move am ON aml.move_id = am.id
//         JOIN ACCOUNT_ACCOUNT aa ON aml.account_id = aa.id
//         LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT aaa ON aml.analytic_account_id = aaa.id
//         WHERE aml.move_id = $1
// ";

$q = "
WITH setup AS (
    SELECT 
        ac.move_id,
        acdl.move_line_id,
        unnest(acdl.sbu) AS sbu_id,
        acdl.id AS cust_line_dist_id
    FROM m_acc_cust_dist ac
    JOIN m_acc_cust_dist_line acdl 
        ON acdl.move_id = ac.move_id
)
SELECT 
    aml.move_name,
    aa.code || ' ' || aa.name AS account_name,
    am.state AS status,
    am.type,
    aml.name AS item_label,
    aml.date,
    aml.debit,
    aml.credit,
    am.id,
    aml.id AS aml_id,
    aaa.name AS account_analytic,
    COALESCE(
        json_agg(asm.sbu ORDER BY s.sbu_id) 
        FILTER (WHERE s.sbu_id IS NOT NULL),
        '[]'::json
    ) AS sbu,
    COALESCE(
        json_agg(s.sbu_id ORDER BY s.sbu_id) 
        FILTER (WHERE s.sbu_id IS NOT NULL),
        '[]'::json
    ) AS sbu_ids,
     s.cust_line_dist_id
FROM account_move_line aml
JOIN account_move am ON aml.move_id = am.id
JOIN account_account aa ON aml.account_id = aa.id
LEFT JOIN account_analytic_account aaa ON aml.analytic_account_id = aaa.id
LEFT JOIN setup s ON s.move_line_id = aml.id
LEFT JOIN m_acc_sbu_maint asm ON asm.id = s.sbu_id
WHERE aml.move_id = $1
GROUP BY 
    aml.move_name,
    aa.code || ' ' || aa.name,
    am.state,
    am.type,
    aml.name,
    aml.date,
    aml.debit,
    aml.credit,
    am.id,
    aml.id,
    aaa.name,
    s.cust_line_dist_id
";

$result = $db->fetchAll($q, [$move_id]);

// echo json_encode($result);
echo json_encode([
    'status' => 'success',
    'data' => $result
]);
