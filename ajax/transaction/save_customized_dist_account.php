<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

$reference = isset($_POST['reference']) ? $_POST['reference'] : [];
$added_by = isset($_SESSION['ppc']['emp_no']) ? $_SESSION['ppc']['emp_no'] : '';

if (empty($reference) || $added_by === '') {
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
    $check = pg_query_params($conn, $check_sql, [$reference]);

    if ($check && pg_num_rows($check) > 0) {
    //    continue;
        echo json_encode([
            'status' => 'error',
            'message' => "Reference '$reference' already exists."
        ]);
        exit;
     }

    $insert_sql = "
        INSERT INTO m_acc_customized_dist_accounts (
            reference,
            added_by,
            active
        )
        VALUES ($1, $2, TRUE)
        RETURNING id
    ";
    $insert = pg_query_params($conn, $insert_sql, [$reference, $added_by]);

    if ($insert) {
        $row = pg_fetch_assoc($insert);

        $resultData[] = [
            'id' => $row['id'],
            'reference' => $reference,
            'date_added' => date('Y-m-d'),
            'added_by' => $added_by
        ];
    }
// }

echo json_encode([
    'status' => 'success',
    'data' => $resultData
]);