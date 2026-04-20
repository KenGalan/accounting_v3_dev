<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');


$db = new Postgresql();
$conn = $db->getConnection();

$category_id = $_POST['category_id'];

if (!$category_id) {
    echo json_encode(['error' => 'Missing category_id']);
    exit;
}

$query_all_departments = "
SELECT DISTINCT 
    AAA.id, 
    CASE
        WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
        ELSE AAA.NAME
    END AS dept_name,
    CASE
        WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
        ELSE ''
    END AS dept_code,
    AAA.CREATE_DATE AS added_on,
    '' AS added_by,
    '' AS changed_on,
    '' AS changed_by,
    AAA.active,
    ADG.DEPT_GROUP--,
    -- acd.debit_to,
    -- acd.wip_account
FROM ACCOUNT_ANALYTIC_ACCOUNT AAA
-- LEFT JOIN M_ACC_COST_DISTRIBUTION ACD 
--     ON AAA.id = ACD.analytic_account_id
--    AND ACD.m_acc_category_id = $category_id
LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG 
    ON ADG.ID = AAA.M_ACC_GROUP_ID
WHERE 
    TRIM(
        CASE
            WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
            ELSE AAA.NAME
        END
    ) <> ''
    AND AAA.NAME IS NOT NULL
    and aaa.company_id =1
    --AND ACD.id IS NULL;  
";

$result = pg_query($conn, $query_all_departments);

if (!$result) {
    echo json_encode(['error' => pg_last_error($conn)]);
    exit;
}

$departments = [];
while ($row = pg_fetch_assoc($result)) {
    $departments[] = $row;
}

echo json_encode($departments);
