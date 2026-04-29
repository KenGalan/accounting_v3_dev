<?php
session_start();
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

$move_id = isset($_POST['move_id']) ? intval($_POST['move_id']) : 0;

if (!$move_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid move_id']);
    exit;
}

$sql = "
    UPDATE m_acc_cust_dist
    SET mo_dist = NULL
    WHERE move_id = $1
";

$result = pg_query_params($conn, $sql, [$move_id]);

if ($result) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to remove MO Distribution']);
}