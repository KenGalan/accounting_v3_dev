<?php
session_start();
$db_ken = new PostgresqlKen();
// $conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_ids = isset($_POST['account_ids']) ? $_POST['account_ids'] : [];
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $added_by = $_SESSION['ppc']['emp_no'];
    // echo $added_by;
    // echo $category_id;
    // echo $account_ids;
    // exit;

    if (empty($account_ids) || $category_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data received']);
        exit;
    }

    $success = true;
    $skipped_accounts = [];

    foreach ($account_ids as $acc_id) {
        $acc_id = intval($acc_id);
        // echo $acc_id;
        // exit;

        $check_query = "SELECT aa.name AS account_name, aca.acc_category_id FROM
        account_account aa
         left join m_acc_category_accounts aca on aca.account_id = aa.id WHERE aca.account_id = $acc_id and aca.acc_category_id =$category_id";

        $row = $db_ken->fetchRow($check_query);
        // echo $row;
        // exit;

        if (!$row['acc_category_id']) {
            $skipped_accounts[] = $row['account_name'];
            continue;
        }
        // echo $row['account_name'];
        // exit;




        // $mo_id = $value;
        $acc_entries = [
            'ACC_CATEGORY_ID' =>    $category_id,
            'ACCOUNT_ID' => $acc_id
        ];

        $result =   $db_ken->insert('M_ACC_CATEGORY_ACCOUNTS', $acc_entries);;

        if (!$result) {
            $success = false;
            break;
        }
    }

    if ($success) {
        echo json_encode([
            'status' => 'success',
            'skipped' => $skipped_accounts
        ]);
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => pg_last_error($conn)
        ]);
    }
}
