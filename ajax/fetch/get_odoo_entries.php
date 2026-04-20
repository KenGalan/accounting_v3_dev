<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

$accrual_id = isset($_POST['accrual_id']) ? (int)$_POST['accrual_id'] : 0;

if (!$accrual_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing accrual_id'
    ]);
    exit;
}

$sql = "
    SELECT *
    FROM (
        SELECT 
            'distribution' AS entry_group,
            acc.journal_name,
            aml.move_name,
            aa.name AS account_name,
            am.state AS status,
            am.type,
            aml.date,
            aml.debit,
            aml.credit,
            am.id,
            aml.id AS aml_id,
            acc.id AS accrual_id,
            aaa.name AS account_analytic
        FROM account_move_line aml
        JOIN account_move am ON aml.move_id = am.id
        JOIN M_ACC_ACCRUAL acc ON am.id = acc.distributed_account_move_id
        JOIN ACCOUNT_ACCOUNT aa ON aml.account_id = aa.id
        LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT aaa ON aml.analytic_account_id = aaa.id
        WHERE acc.id = $1

        UNION ALL

        SELECT 
            'cogs' AS entry_group,
            acc.journal_name,
            aml.move_name,
            aa.name AS account_name,
            am.state AS status,
            am.type,
            aml.date,
            aml.debit,
            aml.credit,
            am.id,
            aml.id AS aml_id,
            acc.id AS accrual_id,
            aaa.name AS account_analytic
        FROM account_move_line aml
        JOIN account_move am ON aml.move_id = am.id
        JOIN M_ACC_ACCRUAL acc ON am.id = acc.wip_account_move_id
        JOIN ACCOUNT_ACCOUNT aa ON aml.account_id = aa.id
        LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT aaa ON aml.analytic_account_id = aaa.id
        WHERE acc.id = $1

        UNION ALL

        SELECT 
            'reverse_distribution' AS entry_group,
            acc.journal_name,
            aml.move_name,
            aa.name AS account_name,
            am.state AS status,
            am.type,
            aml.date,
            aml.debit,
            aml.credit,
            am.id,
            aml.id AS aml_id,
            acc.id AS accrual_id,
            aaa.name AS account_analytic
        FROM account_move_line aml
        JOIN account_move am ON aml.move_id = am.id
        JOIN M_ACC_ACCRUAL acc ON am.id = acc.reverse_account_move_id
        JOIN ACCOUNT_ACCOUNT aa ON aml.account_id = aa.id
        LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT aaa ON aml.analytic_account_id = aaa.id
        WHERE acc.id = $1

        UNION ALL

        SELECT 
            'cogs_reverse' AS entry_group,
            acc.journal_name,
            aml.move_name,
            aa.name AS account_name,
            am.state AS status,
            am.type,
            aml.date,
            aml.debit,
            aml.credit,
            am.id,
            aml.id AS aml_id,
            acc.id AS accrual_id,
            aaa.name AS account_analytic
        FROM account_move_line aml
        JOIN account_move am ON aml.move_id = am.id
        JOIN M_ACC_ACCRUAL acc ON am.id = acc.wip_reverse_account_move_id
        JOIN ACCOUNT_ACCOUNT aa ON aml.account_id = aa.id
        LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT aaa ON aml.analytic_account_id = aaa.id
        WHERE acc.id = $1
    ) x
    ORDER BY x.date DESC
";

$result = pg_query_params($conn, $sql, [$accrual_id]);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query failed'
    ]);
    exit;
}

$data = [];

while ($row = pg_fetch_assoc($result)) {
    $aml_id = (int)$row['aml_id'];

    $mo_sql = "
        SELECT 
            mrp.name AS monum,
        CASE
	        WHEN amlml.value IS NULL THEN '0.00' 
            ELSE amlml.value 
        END AS value,
            CASE
	    WHEN amlml.percent IS NULL THEN '0.00' ELSE amlml.percent END AS percent,
            mrp.customer_name, 
            mrp.origin AS so_no,
        pt.name AS item_device
        FROM account_move_line_mo_link amlml
        JOIN mrp_production mrp 
            ON amlml.production_id = mrp.id
        JOIN product_product pp ON pp.id = mrp.product_id
        JOIN product_template pt ON pt.id = pp.product_tmpl_id
        WHERE amlml.account_move_line_id = $1
    ";

    $mo_result = pg_query_params($conn, $mo_sql, [$aml_id]);

    $mo_list = [];

    if ($mo_result) {
        while ($mo_row = pg_fetch_assoc($mo_result)) {
            $mo_list[] = $mo_row;
        }
    }

    $row['mo_list'] = $mo_list;
    $row['has_mo_link'] = !empty($mo_list);

    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);