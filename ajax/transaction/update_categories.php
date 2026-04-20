<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

header('Content-Type: application/json');


$id = isset($_POST['id']) ? intval($_POST['id']) : null;
$category = isset($_POST['category']) ? trim($_POST['category']) : '';
$mo_pct_ref = isset($_POST['mo_pct_ref']) ? trim($_POST['mo_pct_ref']) : '';

$journal_id = isset($_POST['journal_id']) && $_POST['journal_id'] !== ''
    ? intval($_POST['journal_id'])
    : null;

$changed_by = $_SESSION['ppc']['emp_no'];

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'Missing category ID']);
    exit;
}

if ($category === '') {
    echo json_encode(['success' => false, 'message' => 'Category name cannot be empty']);
    exit;
}

$checkQuery = "SELECT id FROM M_ACC_CATEGORY_TBL WHERE acc_category = $1 AND id != $2";
$checkResult = pg_query_params($conn, $checkQuery, [$category, $id]);

if (pg_num_rows($checkResult) > 0) {
    echo json_encode(['success' => false, 'message' => 'Another category with this name already exists.']);
    exit;
}

$updateQuery = "
    UPDATE M_ACC_CATEGORY_TBL
    SET acc_category = $1,
        mo_pct_ref = $2,
        journal_id = $3,
        changed_by = $4
    WHERE id = $5
";

$result = pg_query_params($conn, $updateQuery, [$category, $mo_pct_ref, $journal_id, $changed_by, $id]);

if ($result) {
    echo json_encode(['success' => true, 'id' => $id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update category.']);
}
