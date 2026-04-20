<?php
$db = new Postgresql();
$conn = $db->getConnection();

$query = "
    SELECT DISTINCT id, code || ' ' || name AS account_name
    FROM account_account
    WHERE m_acc_category_id IS NULL
    ORDER BY account_name ASC
";

$result = pg_query($conn, $query);
$data = [];

while ($row = pg_fetch_assoc($result)) {
    $data[] = [
        'id' => $row['id'],
        'account_name' => $row['account_name']
    ];
}

echo json_encode(['data' => $data]);
