<?php
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $account_id = isset($_POST['account_id']) ? intval($_POST['account_id']) : 0;
    $user_id = $_SESSION['ppc']['emp_no'];
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

    if ($account_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid account ID']);
        exit;
    }

    // $query = "
    //     UPDATE account_account 
    //     SET m_acc_category_id = NULL,
    //         m_emp_updated_acc_categ = $user_id
    //     WHERE id = $account_id
    // ";

    $query = "
    delete from M_ACC_CATEGORY_accounts where 
acc_category_id =$category_id and account_id =$account_id
";

    $result = pg_query($conn, $query);

    if ($result) {
        echo json_encode(['status' => 'success', 'message' => 'Account removed from category successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => pg_last_error($conn)]);
    }
}
