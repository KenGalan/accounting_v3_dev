<?php
session_start();
header("Content-Type: application/json");

$db = new Postgresql();
$conn = $db->getConnection();

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid ID."
    ]);
    exit;
}

$id = $_POST['id'];

$check_query = "SELECT COUNT(*) FROM M_ACC_DATE_RANGE WHERE id = $1";
$check_result = pg_query_params($conn, $check_query, [$id]);

if (!$check_result) {
    echo json_encode([
        "status" => "error",
        "message" => "Error checking record."
    ]);
    exit;
}

$count = pg_fetch_result($check_result, 0, 0);

if ($count == 0) {
    echo json_encode([
        "status" => "not_found",
        "message" => "Record not found. Delete failed."
    ]);
    exit;
}

$delete_query = "DELETE FROM M_ACC_DATE_RANGE WHERE id = $1";
$delete_result = pg_query_params($conn, $delete_query, [$id]);

if ($delete_result) {
    echo json_encode([
        "status" => "success",
        "message" => "Record deleted successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to delete record."
    ]);
}
?>
