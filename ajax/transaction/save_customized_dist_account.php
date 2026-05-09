<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

$account_id = isset($_POST['account_ids']) ? $_POST['account_ids'] : [];
$added_by = isset($_SESSION['ppc']['emp_no']) ? $_SESSION['ppc']['emp_no'] : '';

if (empty($account_id) || $added_by === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing data.'
    ]);
    exit;
}

$resultData = [];

// foreach ($reference as $reference) {

     $check_sql = "
        SELECT id
       FROM m_acc_customized_dist_accounts
     WHERE reference = $1
         AND active = TRUE
        LIMIT 1
     ";
    $check = pg_query_params($conn, $check_sql, [$account_id]);

    if ($check && pg_num_rows($check) > 0) {
    //    continue;
        echo json_encode([
            'status' => 'error',
            'message' => "Reference '$account_id' already exists."
        ]);
        exit;
     }

    $insert_sql = "
        INSERT INTO m_acc_customized_dist_accounts (
            account_id,
            added_by,
            active
        )
        VALUES ($1, $2, TRUE)
        RETURNING id
    ";
    $insert = pg_query_params($conn, $insert_sql, [$account_id, $added_by]);

    if ($insert) {
        $row = pg_fetch_assoc($insert);

        $resultData[] = [
            'id' => $row['id'],
            'account_id' => $account_id,
            'date_added' => date('Y-m-d'),
            'added_by' => $added_by
        ];
    }
// }

echo json_encode([
    'status' => 'success',
    'data' => $resultData
]);