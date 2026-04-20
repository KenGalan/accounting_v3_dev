<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode([]);
    exit;
} 

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
    SELECT A.id, A.name AS account_name
    FROM account_account A
    WHERE NOT EXISTS (
        SELECT 1
        FROM m_acc_customized_dist_accounts B
        WHERE B.account_id = A.id
        AND B.active = TRUE
    )
";

if ($search !== '') {
    $search = pg_escape_string($conn, $search);
    $sql .= " AND A.name ILIKE '%{$search}%'";
}

$sql .= " ORDER BY A.name ASC";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id'   => $row['id'],
            'text' => $row['account_name']
        ];
    }
}

echo json_encode($data);