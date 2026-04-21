<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Postgresql();


$category_id = $_POST['category_id'];

// $prodPercentage = $resProdPercentage['prod_allocation'];
// $amountTotal = $resProdPercentage['total_debit'];


// $query_departments = "SELECT 
// DISTINCT
// acd.id dist_id,
//     AAA.id analytic_account_id,
//     CASE
//         WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
//         ELSE AAA.NAME
//     END AS dept_name,
//     CASE
//         WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
//         ELSE ''
//     END AS dept_code,
//     ADG.DEPT_GROUP,
//     acd.debit_to,
//     acd.wip_account,
//     ACD.ADDED_ON AS added_on,
//     '' AS added_by,
//     '' AS changed_on,
//     '' AS changed_by,
//     AAA.active,
//     ACD.distribution_percentage
// FROM 
// M_ACC_COST_DISTRIBUTION ACD
// LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT AAA ON AAA.ID = ACD.ANALYTIC_ACCOUNT_ID --AND AAA.COMPANY_ID = 1
// LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAA.M_ACC_GROUP_ID
// LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG2 ON ADG2.ID = ACD.GROUP_ID
// JOIN M_ACC_CATEGORY_TBL ACT ON ACD.M_ACC_CATEGORY_ID = ACT.ID
// WHERE
// ACT.ID = $category_id
//     ";

$query_departments = "SELECT 
    DISTINCT
    acd.id dist_id,
        AAA.id analytic_account_id,
        adg2.id group_id,
        coalesce(CASE
            WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
            ELSE AAA.NAME
        END,adg2.dept_group) AS dept_name,
        CASE
            WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
            ELSE ''
        END AS dept_code,
        coalesce(ADG.DEPT_GROUP,adg2.dept_group) dept_group,
        acd.debit_to,
        acd.wip_account,
        ACD.ADDED_ON AS added_on,
        '' AS added_by,
        '' AS changed_on,
        '' AS changed_by,
        coalesce(AAA.active,adg2.active) active,
        ACD.distribution_percentage
    FROM 
    M_ACC_COST_DISTRIBUTION ACD
    LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT AAA ON AAA.ID = ACD.ANALYTIC_ACCOUNT_ID --AND AAA.COMPANY_ID = 1
    LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAA.M_ACC_GROUP_ID
    LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG2 ON ADG2.ID = ACD.GROUP_ID
    JOIN M_ACC_CATEGORY_TBL ACT ON ACD.M_ACC_CATEGORY_ID = ACT.ID
    WHERE
    ACT.ID = $category_id";

$result_departments = $db->fetchAll($query_departments);



// $data = [
//     'mo_dist' => $result,

// ];

echo json_encode($result_departments);
