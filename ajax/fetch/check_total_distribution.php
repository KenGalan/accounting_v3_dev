<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();

$db = new Postgresql();
$conn = $db->getConnection();

$category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;

if ($category_id <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid category ID']);
    exit;
}

$query_total = "
    SELECT COALESCE(SUM(distribution_percentage), 0) AS total_percentage
    FROM M_ACC_COST_DISTRIBUTION
    WHERE m_acc_category_id = $category_id
";
$result_total = pg_query($conn, $query_total);
// echo $query_total;
// exit;

if (!$result_total) {
    echo json_encode(['status' => 'error', 'message' => pg_last_error($conn)]);
    exit;
}

$row_total = pg_fetch_assoc($result_total);
$current_total = floatval($row_total['total_percentage']);

echo json_encode([
    'status' => 'success',
    'total_percentage' => $current_total,
    'is_full' => $current_total >= 100
]);
