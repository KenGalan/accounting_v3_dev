<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

header('Content-Type: application/json');


$new_credit_to_id = isset($_POST['new_credit_to_id']) ? intval($_POST['new_credit_to_id']) : null;
$new_acc_value = isset($_POST['new_acc_value']) ? floatval($_POST['new_acc_value']) : '';
$new_template_id = isset($_POST['new_template_id']) ? intval($_POST['new_template_id']) : '';
$accrual_id  = isset($_POST['accrual_id']) ? intval($_POST['accrual_id']) : '';
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
     changed_by = $4, changed_on = NOW()
    WHERE id = $5
";

$result = pg_query_params($conn, $updateQuery, [$new_credit_to_id, $new_acc_value, $new_template_id, $changed_by, $accrual_id]);

if ($result) {
    echo json_encode(['success' => true, 'id' => $accrual_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update accrual.']);
}
