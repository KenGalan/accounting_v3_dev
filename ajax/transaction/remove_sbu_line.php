<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$move_id = isset($input['move_id']) ? trim($input['move_id']) : '';
$sbu_id  = isset($input['sbu_id']) ? (int)$input['sbu_id'] : 0;

if ($move_id === '' || $sbu_id === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing move_id or sbu_id.'
    ]);
    exit;
}

$check_sql = "
    SELECT sbu
    FROM m_acc_cust_dist
    WHERE move_id = $1
    LIMIT 1
";

$check_result = pg_query_params($conn, $check_sql, [$move_id]);

if (!$check_result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to check custom distribution.'
    ]);
    exit;
}

if (pg_num_rows($check_result) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No custom distribution found for this move_id.'
    ]);
    exit;
}

$update_sql = "
    UPDATE m_acc_cust_dist
    SET sbu = array_remove(sbu, $2::integer)
    WHERE move_id = $1
    RETURNING sbu
";

$update_result = pg_query_params($conn, $update_sql, [$move_id, $sbu_id]);

if (!$update_result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to remove SBU.'
    ]);
    exit;
}

$updated_row = pg_fetch_assoc($update_result);
$after_sbu = isset($updated_row['sbu']) ? $updated_row['sbu'] : null;

// Optional: keep row, just make SBU empty
echo json_encode([
    'status' => 'success',
    'message' => 'SBU removed successfully.',
    'remaining_sbu' => $after_sbu
]);