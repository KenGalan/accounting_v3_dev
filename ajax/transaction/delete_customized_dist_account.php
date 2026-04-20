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

$id = isset($_POST['id']) ? trim($_POST['id']) : '';

if ($id === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing ID.'
    ]);
    exit;
}

$update_sql = "
    UPDATE m_acc_customized_dist_accounts
    SET active = FALSE
    WHERE id = $1
";
$update_result = pg_query_params($conn, $update_sql, array($id));

if ($update_result) {
    echo json_encode([
        'status' => 'success'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete account.'
    ]);
}