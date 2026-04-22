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

$move_id   = isset($_POST['move_id']) ? trim($_POST['move_id']) : '';
$from_date = isset($_POST['from_date']) ? trim($_POST['from_date']) : '';
$to_date   = isset($_POST['to_date']) ? trim($_POST['to_date']) : '';
$total_amount = isset($_POST['total_amount']) ? trim($_POST['total_amount']) : '';
$accounting_date = isset($_POST['accounting_date']) ? trim($_POST['accounting_date']) : '';

if ($move_id === '' || $from_date === '' || $to_date === '' || $total_amount === '' || $accounting_date === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields.'
    ]);
    exit;
}

if ($from_date > $to_date) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid date range.'
    ]); 
    exit;
}

$check_sql = "
    SELECT id
    FROM m_acc_cust_dist
    WHERE move_id = $1
    LIMIT 1
";
$check_result = pg_query_params($conn, $check_sql, array($move_id));

if ($check_result && pg_num_rows($check_result) > 0) {
    $update_sql = "
        UPDATE m_acc_cust_dist
        SET from_date = $2,
            to_date = $3,
            total_amount = $4,
            accounting_date = $5
        WHERE move_id = $1
    ";
    $update_result = pg_query_params($conn, $update_sql, array($move_id, $from_date, $to_date, $total_amount, $accounting_date));

    if ($update_result) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Date range updated successfully.'
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Failed to update date range.'
        ]);
    }
    exit;
}

$insert_sql = "
    INSERT INTO m_acc_cust_dist (
        move_id,
        from_date,
        to_date,
        total_amount,
        accounting_date
    )
    VALUES (
        $1,
        $2,
        $3,
        $4,
        $5
    )
";
$insert_result = pg_query_params($conn, $insert_sql, array($move_id, $from_date, $to_date, $total_amount, $accounting_date));

if ($insert_result) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Date range saved successfully.'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save date range.'
    ]);
}