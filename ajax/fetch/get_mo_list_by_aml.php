<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

$aml_id = 1644658;

if (!$aml_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing aml_id'
    ]);
    exit;
}

$sql = "
    SELECT 
        mrp.name AS monum,
        amlml.value,
        amlml.percent
    FROM account_move_line_mo_link amlml
    JOIN mrp_production mrp 
        ON amlml.production_id = mrp.id
    WHERE amlml.account_move_line_id = $1
";

$result = pg_query_params($conn, $sql, [$aml_id]);

if (!$result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Query failed'
    ]);
    exit;
}

$data = [];
while ($row = pg_fetch_assoc($result)) {
    $data[] = $row;
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);