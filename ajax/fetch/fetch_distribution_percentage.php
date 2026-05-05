<?php
header('Content-Type: application/json');

$db = new Postgresql();

$category_id = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;

if ($category_id <= 0) {
    echo json_encode([]);
    exit;
}

$sql = "
SELECT 
    COALESCE(
        CASE
            WHEN AAA.NAME ~ '^[0-9]' 
                THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
            ELSE AAA.NAME
        END,
        ADG2.dept_group
    ) AS dept_name,
    COALESCE(acd.distribution_percentage, 0) AS distribution_percentage
FROM M_ACC_COST_DISTRIBUTION ACD
LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT AAA 
    ON AAA.ID = ACD.ANALYTIC_ACCOUNT_ID
LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG 
    ON ADG.ID = AAA.M_ACC_GROUP_ID
LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG2 
    ON ADG2.ID = ACD.GROUP_ID
JOIN M_ACC_CATEGORY_TBL ACT 
    ON ACD.M_ACC_CATEGORY_ID = ACT.ID
WHERE ACT.ID = $category_id
ORDER BY dept_name
";

$data = $db->fetchAll($sql);

echo json_encode($data);
exit;