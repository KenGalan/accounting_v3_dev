<?php
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

// if (isset($_POST['id']) && isset($_POST['dept_name']) && isset($_POST['dept_code']) && isset($_POST['dept_group'])) {
if (isset($_POST['dept_group'])) {
    $id = $_POST['id'];
    // $dept_name = $_POST['dept_name'];
    // $dept_code = $_POST['dept_code'];
    $dept_group = $_POST['dept_group'];
    // $active = $_POST['active'];
    // $changed_by  = isset($_POST['added_by']) ? strtoupper($_POST['added_by']) : strtoupper($_SESSION['ppc']['fullname']);
    // $emp_no  = $_SESSION['ppc']['emp_no'];
    $changed_on = date('Y-m-d H:i:s');

    $query = "UPDATE account_analytic_account 
              set  m_acc_group_id = '$dept_group'
              WHERE id = $id";

    if (pg_query($conn, $query)) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => pg_last_error($conn)]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
}
