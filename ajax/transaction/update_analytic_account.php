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

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$analytic_account_id = isset($_POST['analytic_account_id']) && $_POST['analytic_account_id'] !== '' ? (int) $_POST['analytic_account_id'] : null;
    // echo $id;
    // echo $analytic_account_id;
    // exit;
    
if ($id <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid ID.'
    ]);
    exit;
}

if ($analytic_account_id !== null) {
    $check_sql = "
        SELECT id
        FROM account_analytic_account
        WHERE id = $1
        LIMIT 1
    ";
    $check_result = pg_query_params($conn, $check_sql, [$analytic_account_id]);
    // echo pg_num_rows($check_result);
    exit;
    if (!$check_result || pg_num_rows($check_result) === 0) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid analytic account selected.'
        ]);
        exit;
    }
}

$update_sql = "
    UPDATE m_acc_cust_dist_line
    SET analytic_account_id = $1
    WHERE id = $2
";

$update_result = pg_query_params($conn, $update_sql, [$analytic_account_id, $id]);

if (!$update_result) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to update analytic account.',
        'pg_error' => pg_last_error($conn)
    ]);
    exit;
}

if (pg_affected_rows($update_result) <= 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No record was updated.'
    ]);
    exit;
}

echo json_encode([
    'status' => 'success',
    'message' => 'Analytic account updated successfully.'
]);