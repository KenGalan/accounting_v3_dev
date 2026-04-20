<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Postgresql();


$category_id = $_POST['category_id'];

// $prodPercentage = $resProdPercentage['prod_allocation'];
// $amountTotal = $resProdPercentage['total_debit'];


$query_departments = "SELECT DISTINCT
acd.id dist_id,
    AAA.id analytic_account_id,
    CASE
        WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
        ELSE AAA.NAME
    END AS dept_name,
    CASE
        WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
        ELSE ''
    END AS dept_code,
    ADG.DEPT_GROUP,
    acd.debit_to,
    acd.wip_account,
    ACD.ADDED_ON AS added_on,
    '' AS added_by,
    '' AS changed_on,
    '' AS changed_by,
    AAA.active,
    ACD.distribution_percentage
FROM ACCOUNT_ANALYTIC_ACCOUNT AAA
LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAA.M_ACC_GROUP_ID
JOIN M_ACC_COST_DISTRIBUTION ACD ON AAA.ID = ACD.ANALYTIC_ACCOUNT_ID
JOIN M_ACC_CATEGORY_TBL ACT ON ACD.M_ACC_CATEGORY_ID = ACT.ID
WHERE AAA.ACTIVE
AND ACT.ID = $category_id
and aaa.company_id =1
ORDER BY ACD.added_on DESC
    ";

$result_departments = $db->fetchAll($query_departments);



// $data = [
//     'mo_dist' => $result,

// ];

echo json_encode($result_departments);
