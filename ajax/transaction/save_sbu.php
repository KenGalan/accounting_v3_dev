<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    exit;
}

$sbu_list = isset($_POST['account_ids']) ? $_POST['account_ids'] : [];
$added_by = isset($_SESSION['ppc']['emp_no']) ? $_SESSION['ppc']['emp_no'] : '';

if (empty($sbu_list) || $added_by === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing SBU or added_by.'
    ]);
    exit;
}

$data = [];

foreach ($sbu_list as $sbu) {

    $check_sql = "
        SELECT id
        FROM m_acc_sbu_maint
        WHERE sbu = $1
        LIMIT 1
    ";
    $check_result = pg_query_params($conn, $check_sql, [$sbu]);

    if ($check_result && pg_num_rows($check_result) > 0) {
        continue;
    }

    $insert_sql = "
        INSERT INTO m_acc_sbu_maint (
            sbu,
            added_by
        )
        VALUES (
            $1,
            $2
        )
        RETURNING id
    ";
    $insert_result = pg_query_params($conn, $insert_sql, [$sbu, $added_by]);

    if ($insert_result) {
        $row = pg_fetch_assoc($insert_result);

        $data[] = [
            'id' => $row['id'],
            'sbu' => $sbu,
            'date_added' => date('Y-m-d'),
            'added_by' => $added_by
        ];
    }
}

echo json_encode([
    'status' => 'success',
    'data' => $data
]);