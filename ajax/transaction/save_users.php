<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

header('Content-Type: application/json');

if (!isset($_POST['emp_no']) || trim($_POST['emp_no']) === '') {
    echo json_encode(['success' => false, 'message' => 'Missing employee number']);
    exit;
}

$emp_no = trim($_POST['emp_no']);
$is_notification = isset($_POST['is_notification']) ? $_POST['is_notification'] : 0;

// -----------------------------------------
// CHECK IF USER ALREADY EXISTS
// -----------------------------------------
$checkQuery = "SELECT emp_no FROM m_acc_user_maintenance WHERE emp_no = $1";
$checkResult = pg_query_params($conn, $checkQuery, [$emp_no]);

if ($checkResult && pg_num_rows($checkResult) > 0) {
    echo json_encode(['success' => false, 'message' => 'User already exists.']);
    exit;
}

// -----------------------------------------
// INSERT NEW USER
// -----------------------------------------
$insertQuery = "
    INSERT INTO m_acc_user_maintenance (emp_no, is_notification)
    VALUES ($1, $2)
    RETURNING emp_no
";

$insertResult = pg_query_params($conn, $insertQuery, [
    $emp_no,
    $is_notification
]);

if ($insertResult && $row = pg_fetch_assoc($insertResult)) {

    echo json_encode([
        'success' => true,
        'emp_no' => $row['emp_no'],
        'message' => 'User saved.'
    ]);

} else {
    echo json_encode(['success' => false, 'message' => 'Failed to insert user.']);
}
?>
