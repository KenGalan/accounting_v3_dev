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

$from_date = isset($input['from_date']) ? trim($input['from_date']) : '';
$to_date   = isset($input['to_date']) ? trim($input['to_date']) : '';
$rows      = isset($input['rows']) ? $input['rows'] : [];

if ($from_date === '' || $to_date === '' || empty($rows)) {
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

foreach ($rows as $r) {
    $move_id         = isset($r['move_id']) ? trim($r['move_id']) : '';
    $total_amount    = isset($r['total_amount']) ? $r['total_amount'] : '';
    $accounting_date = isset($r['accounting_date']) ? trim($r['accounting_date']) : '';

    if ($move_id === '' || $total_amount === '' || $accounting_date === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing row data.'
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
        $update_result = pg_query_params($conn, $update_sql, array(
            $move_id,
            $from_date,
            $to_date,
            $total_amount,
            $accounting_date
        ));

        if (!$update_result) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update date range.'
            ]);
            exit;
        }
    } else {
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
        $insert_result = pg_query_params($conn, $insert_sql, array(
            $move_id,
            $from_date,
            $to_date,
            $total_amount,
            $accounting_date
        ));

        if (!$insert_result) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save date range.'
            ]);
            exit;
        }
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'Mass date range saved successfully.'
]);