<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

header('Content-Type: application/json');

$new_credit_to_id = isset($_POST['new_credit_to_id']) ? intval($_POST['new_credit_to_id']) : null;
$new_acc_value    = isset($_POST['new_acc_value']) ? floatval($_POST['new_acc_value']) : 0;
$new_template_id  = isset($_POST['new_template_id']) ? intval($_POST['new_template_id']) : null;
$new_journal_id   = isset($_POST['new_journal_id']) ? intval($_POST['new_journal_id']) : null;
$new_from_date = isset($_POST['new_from_date']) ? $_POST['new_from_date'] : null;
$new_to_date   = isset($_POST['new_to_date']) ? $_POST['new_to_date'] : null;
$accrual_id       = isset($_POST['accrual_id']) ? intval($_POST['accrual_id']) : null;
$changed_by       = $_SESSION['ppc']['emp_no'];

if (!$accrual_id) {
    echo json_encode(['success' => false, 'message' => 'Missing Accrual ID']);
    exit;
}

if (!$new_acc_value) {
    echo json_encode(['success' => false, 'message' => 'Missing Accrual Value']);
    exit;
}

if (!$new_credit_to_id) {
    echo json_encode(['success' => false, 'message' => 'Missing Credit To Account']);
    exit;
}

if (!$new_template_id) {
    echo json_encode(['success' => false, 'message' => 'Missing Distribution Template']);
    exit;
}

if (!$new_journal_id) {
    echo json_encode(['success' => false, 'message' => 'Missing Journal Account']);
    exit;
}


$updateQuery = "
    UPDATE M_ACC_ACCRUAL
SET credit_to = $1,
    total_accrual_value = $2,
    dist_categ_id = $3,
    journal_id = $4,
    from_date = $5,
    to_date = $6,
    changed_by = $7,
    changed_on = NOW()
WHERE id = $8
";

$result = pg_query_params($conn, $updateQuery, [
    $new_credit_to_id,
    $new_acc_value,
    $new_template_id,
    $new_journal_id,
    $new_from_date,
    $new_to_date,
    $changed_by,
    $accrual_id
]);

if ($result) {
    echo json_encode(['success' => true, 'id' => $accrual_id]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to update accrual.',
        'error' => pg_last_error($conn)
    ]);
}