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

$move_line_id = isset($input['move_line_id']) ? trim($input['move_line_id']) : '';
$sbu_id = isset($input['sbu_id']) ? (int)$input['sbu_id'] : 0;

if ($move_line_id === '' || $sbu_id === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing move_line_id or sbu_id.'
    ]);
    exit;
}

$check_sql = "
    SELECT sbu
    FROM m_acc_cust_dist_line
    WHERE move_line_id = $1
    LIMIT 1
";
$check_result = pg_query_params($conn, $check_sql, array($move_line_id));

if (!$check_result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to check distribution line.'
    ]);
    exit;
}

if (pg_num_rows($check_result) === 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No distribution line found for this move_line_id.'
    ]);
    exit;
}

$row = pg_fetch_assoc($check_result);
$current_sbu = $row['sbu'];

$update_sql = "
    UPDATE m_acc_cust_dist_line
    SET sbu = array_remove(sbu, $2::integer)
    WHERE move_line_id = $1
    RETURNING sbu
";
$update_result = pg_query_params($conn, $update_sql, array($move_line_id, $sbu_id));

if (!$update_result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to remove SBU.'
    ]);
    exit;
}

$updated_row = pg_fetch_assoc($update_result);
$after_sbu = isset($updated_row['sbu']) ? $updated_row['sbu'] : null;

if ($after_sbu === '{}' || $after_sbu === null) {
    $delete_sql = "
        DELETE FROM m_acc_cust_dist_line
        WHERE move_line_id = $1
    ";
    $delete_result = pg_query_params($conn, $delete_sql, array($move_line_id));

    if (!$delete_result) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to delete empty distribution line.'
        ]);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Last SBU removed. Distribution line deleted.'
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'SBU removed successfully.',
    'remaining_sbu' => $after_sbu
]);