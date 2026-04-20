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

$query_all_accounts = "
SELECT DISTINCT
    aa.id,
    aa.code || ' ' || aa.name AS account_name
FROM account_account aa
LEFT JOIN m_acc_category_accounts aca ON aca.account_id = aa.id and aca.account_id = $category_id
WHERE aca.account_id is null
ORDER BY account_name ASC
";

$result = pg_query($conn, $query_all_accounts);

if (!$result) {
    echo json_encode(['error' => pg_last_error($conn)]);
    exit;
}

$accounts = [];
while ($row = pg_fetch_assoc($result)) {
    $accounts[] = $row;
}

echo json_encode($accounts);
