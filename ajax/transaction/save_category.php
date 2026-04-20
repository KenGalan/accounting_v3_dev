<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

header('Content-Type: application/json');

if (!isset($_POST['category']) || trim($_POST['category']) === '') {
    echo json_encode(['success' => false, 'message' => 'Missing category name']);
    exit;
}

$category = trim($_POST['category']);
$added_by = $_SESSION['ppc']['emp_no']; 
$active = 'true';

$checkQuery = "SELECT id FROM M_ACC_CATEGORY_TBL WHERE acc_category = $1";
$checkResult = pg_query_params($conn, $checkQuery, [$category]);

if (pg_num_rows($checkResult) > 0) {
    echo json_encode(['success' => false, 'message' => 'Category already exists.']);
    exit;
}  

$query = "
    INSERT INTO M_ACC_CATEGORY_TBL (acc_category, added_by, active)
    VALUES ($1, $2, $3)
    RETURNING id
";

$result = pg_query_params($conn, $query, [
    $category,
    $added_by,
    $active
]);

if ($result && $row = pg_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'id' => $row['id'],
        'added_by' => $added_by
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to insert category.']);
}
?>
