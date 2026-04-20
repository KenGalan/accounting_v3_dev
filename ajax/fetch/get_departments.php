<?php
$db = new Postgresql();
$conn = $db->getConnection();

$category_id = isset($_POST['category_id']) ? $_POST['category_id'] : '';

$query = "
    SELECT 
        AAA.id,
        CASE WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '') ELSE AAA.NAME END AS dept_name,
        CASE WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1) ELSE '' END AS dept_code,
        ADG.DEPT_GROUP,
        AAA.CREATE_DATE AS added_on,
        COALESCE(ACD.distribution_percentage, 0) AS distribution_percentage
    FROM ACCOUNT_ANALYTIC_ACCOUNT AAA
    LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAA.M_ACC_GROUP_ID
    JOIN M_ACC_COST_DISTRIBUTION ACD ON AAA.ID = ACD.ANALYTIC_ACCOUNT_ID
    JOIN M_ACC_CATEGORY_TBL ACT ON ACD.M_ACC_CATEGORY_ID = ACT.ID
    WHERE ACT.ID = $category_id AND AAA.ACTIVE
    ORDER BY AAA.id ASC
";

$result = pg_query($conn, $query); 
$data = [];

while ($row = pg_fetch_assoc($result)) {
    $data[] = [
        'dept_name' => $row['dept_name'],
        'dept_code' => $row['dept_code'],
        'distribution' => "<input type='text' class='form-control distribution-input' style='width:100%;' value='{$row['distribution_percentage']}' disabled>",
        'dept_group' => $row['dept_group'],
        'added_on' => $row['added_on'],
        'action' => "
            <button class='EditBtn btn-secondary' data-id='{$row['id']}'>Edit</button>
            <button class='deleteBtn btn-danger' data-id='{$row['id']}'>Delete</button>
        "
    ];
}

echo json_encode(['data' => $data]);
