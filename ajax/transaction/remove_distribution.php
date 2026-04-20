<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $analytic_account_id = isset($_POST['analytic_account_id']) ? intval($_POST['analytic_account_id']) : 0;
    $dist_id = isset($_POST['dist_id']) ? intval($_POST['dist_id']) : 0;
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

    if ($dist_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid Distribution ID']);
        exit;
    }

    $query = "
        DELETE FROM M_ACC_COST_DISTRIBUTION 
        WHERE id =$dist_id
    ";
    // echo '<pre>';
    // echo $query;
    // exit;
    $result = pg_query($conn, $query);

    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => pg_last_error($conn)]);
    }
}
