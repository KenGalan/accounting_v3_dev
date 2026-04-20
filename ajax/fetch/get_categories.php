<?php

$db = new Postgresql();
$conn = $db->getConnection();

$query = "
    SELECT 
        a.id,
        a.acc_category,
        COALESCE(SUM(b.distribution_percentage), 0) AS distribution_percentage
    FROM M_ACC_CATEGORY_TBL a
    LEFT JOIN M_ACC_COST_DISTRIBUTION b ON b.m_acc_category_id = a.id
    GROUP BY a.id, a.acc_category
    ORDER BY a.id ASC
";

$result = pg_query($conn, $query);
$data = [];

while ($row = pg_fetch_assoc($result)) {
    $percentage = floatval($row['distribution_percentage']);
    $color = ($percentage == 100) ? 'darkgreen' : 'darkred';
    $data[] = [
        'acc_category' => $row['acc_category'],
        'distribution_percentage' => "<span style='color:$color;font-weight:bold;'>$percentage%</span>",
        'action' => "<button class='viewBtn' data-id='{$row['id']}' data-category='{$row['acc_category']}'>View</button>"
    ];
}

echo json_encode(['data' => $data]);
