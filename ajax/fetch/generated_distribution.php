<?php
ob_start();

header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

$parameter = isset($_POST['year_month']) ? "ACDR.YEAR_MONTH = '" . $_POST['year_month'] . "'" : 'ACDR.ID = ' . $_POST['date_range_id'];
// echo $parameter;
// exit;

$query = "
SELECT 
    ACDR.ID AS DATE_RANGE_ID,
maa.id AS JOURNAL_ENTRY_ID,
'' item_label,
'test ref' AS REFERENCE,
maa.journal_name    JOURNAL,
    AA.NAME AS ACCOUNT_NAME,
    CASE
        WHEN aad.ANALYTIC_ACCOUNT ~ '^[0-9]' THEN regexp_replace(aad.ANALYTIC_ACCOUNT, '^\S+\s*', '')
        ELSE aad.ANALYTIC_ACCOUNT
    END AS dept_name,
    CASE
        WHEN aad.ANALYTIC_ACCOUNT ~ '^[0-9]' THEN split_part(aad.ANALYTIC_ACCOUNT, ' ', 1)
        ELSE ''
    END AS dept_code,
--     ADJT.ITEM_LABEL,
    aad.DISTRIBUTION_PERCENTAGE,
    CASE
    WHEN aad.DEBIT is null THEN '0.00'
    ELSE aad.DEBIT
    END AS DEBIT,
    CASE
    WHEN aad.CREDIT is null THEN '0.00'
    ELSE aad.CREDIT
    END AS CREDIT,
    ACDR.YEAR_MONTH
FROM M_ACC_MONTH ACDR
join M_ACC_ACCRUAL maa on maa.MONTH_ID = ACDR.ID AND MAA.IS_ACCRUAL
join M_ACC_ACCRUAL_DIST aad on aad.accrual_id = maa.id
JOIN ACCOUNT_ACCOUNT AA ON aad.ACCOUNT_ID = AA.ID
WHERE $parameter
AND ACDR.is_dept_distributed = 'true';
";

$res = pg_query($conn, $query);

$data = [];

while ($row = pg_fetch_assoc($res)) {
    $data[] = $row;
}

echo json_encode($data);
ob_end_flush();
