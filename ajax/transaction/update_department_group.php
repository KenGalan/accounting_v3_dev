<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

header('Content-Type: application/json');

$dept_id = isset($_POST['id']) ? intval($_POST['id']) : null;
$dept_group = isset($_POST['dept_group']) ? trim($_POST['dept_group']) : '';
$changed_by = $_SESSION['ppc']['emp_no'];

if (!$dept_id) {
    echo json_encode(['success' => false, 'message' => 'Missing category ID']);
    exit;
}

if ($dept_group === '') {
    echo json_encode(['success' => false, 'message' => 'Category name cannot be empty']);
    exit;
}

$updateQuery = "
    UPDATE M_ACC_DEPARTMENT_GROUPS
    SET dept_group = $1, changed_by = $2, changed_on = NOW()
    WHERE id = $3
";

$result = pg_query_params($conn, $updateQuery, [$dept_group, $changed_by, $dept_id]);

if ($result) {
    echo json_encode(['success' => true, 'id' => $dept_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update category.']);
}
?>
