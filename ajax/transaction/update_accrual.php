<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

header('Content-Type: application/json');


$new_credit_to_id = isset($_POST['new_credit_to_id']) ? intval($_POST['new_credit_to_id']) : null;
$new_acc_value = isset($_POST['new_acc_value']) ? floatval($_POST['new_acc_value']) : '';
$new_template_id = isset($_POST['new_template_id']) ? intval($_POST['new_template_id']) : '';
$accrual_id  = isset($_POST['accrual_id']) ? intval($_POST['accrual_id']) : '';
$new_from_date = isset($_POST['new_from_date']) ? $_POST['new_from_date'] : null;
$new_to_date   = isset($_POST['new_to_date']) ? $_POST['new_to_date'] : null;
$changed_by = $_SESSION['ppc']['emp_no'];

if (!$new_acc_value) {
    echo json_encode(['success' => false, 'message' => 'Missing Accrual Value']);
    exit;
}

$updateQuery = "
    UPDATE M_ACC_ACCRUAL
    SET credit_to = $1,
    total_accrual_value =$2,
    dist_categ_id =$3,
     from_date = $4,
    to_date = $5,
     changed_by = $6, changed_on = NOW()
    WHERE id = $7
";

$result = pg_query_params($conn, $updateQuery, [$new_credit_to_id, $new_acc_value, $new_template_id, $new_from_date, $new_to_date, $changed_by, $accrual_id]);

if ($result) {
    echo json_encode(['success' => true, 'id' => $accrual_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update accrual.']);
}
