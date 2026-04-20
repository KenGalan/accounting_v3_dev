<?php
$db = new Postgresql();
$conn = $db->getConnection();

$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

$query = "
    SELECT DISTINCT aa.id, aa.name AS account_name
    FROM account_account aa
    JOIN M_ACC_CATEGORY_TBL cat ON aa.m_acc_category_id = cat.id
    JOIN M_ACC_COST_DISTRIBUTION mm ON cat.id = mm.m_acc_category_id
    WHERE cat.id = $category_id
    ORDER BY account_name ASC
";

$result = pg_query($conn, $query);
$data = [];

while ($row = pg_fetch_assoc($result)) {
    $data[] = ['account_name' => $row['account_name']];
}

echo json_encode(['data' => $data]);
