<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode([]);
    exit;
}

$sql = "
   SELECT 
   name AS analytic_account, 
   id AS analytic_account_id
   FROM ACCOUNT_ANALYTIC_ACCOUNT
";

$sql .= " ORDER BY name ASC";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['analytic_account_id'],
            'text' => $row['analytic_account']
        ];
    }
}

echo json_encode($data);