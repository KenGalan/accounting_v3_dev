<?php
session_start();
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

$sql = "
    SELECT emp_no, is_notification
    FROM m_acc_user_maintenance
";

$result = pg_query($conn, $sql);

if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Failed to fetch users"
    ]);
    exit;
}

$users = []; 

while ($row = pg_fetch_assoc($result)) { 
    $users[] = [
        "employee_id_no" => $row['emp_no'],
        "is_notification" => $row['is_notification'] == "1" ? "1" : "0"
    ];
}

echo json_encode([
    "success" => true,
    "data" => $users
]);
?>
