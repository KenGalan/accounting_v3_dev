<?php
header('Content-Type: application/json');

if(!isset($_POST['emp_no'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing emp_no']);
    exit;
}

$db = new Postgresql();
$conn = $db->getConnection();

$emp_no = intval($_POST['emp_no']);
$is_notification = isset($_POST['is_notification']) ? ($_POST['is_notification'] == 1 ? 'true' : 'false') : null;
$is_dashboard = isset($_POST['is_dashboard']) ? ($_POST['is_dashboard'] == 1 ? 'true' : 'false') : null;
$is_admin = isset($_POST['is_admin']) ? ($_POST['is_admin'] == 1 ? 'true' : 'false') : null;
$is_system = isset($_POST['is_system']) ? ($_POST['is_system'] == 1 ? 'true' : 'false') : null;
$email = isset($_POST['email']) && trim($_POST['email']) !== '' 
    ? pg_escape_literal($conn, trim($_POST['email'])) 
    : 'NULL';

try {
    $checkSql = "SELECT * FROM M_ACC_USER_MAINTENANCE WHERE emp_no = $emp_no";
    $res = pg_query($conn, $checkSql);

    if(pg_num_rows($res) > 0) {
        $updates = [];
        if(isset($is_notification)) $updates[] = "is_notification = $is_notification";
        if(isset($is_dashboard)) $updates[] = "is_dashboard = $is_dashboard";
        if(isset($is_admin)) $updates[] = "is_admin = $is_admin";
        if(isset($is_system)) $updates[] = "is_system = $is_system";
        if($email !== 'NULL') $updates[] = "email = $email";

        if(count($updates) > 0) {
            $updateSql = "UPDATE M_ACC_USER_MAINTENANCE SET " . implode(', ', $updates) . " WHERE emp_no = $emp_no";
            pg_query($conn, $updateSql);
        }

    } else {
        $insertSql = "INSERT INTO M_ACC_USER_MAINTENANCE (emp_no, is_notification, email, is_dashboard, is_admin, is_system) 
                      VALUES ($emp_no, " . (isset($is_notification) ? $is_notification : 'false') . ", $email, " . (isset($is_dashboard) ? $is_dashboard : 'false') . ", " . (isset($is_admin) ? $is_admin : 'false') . ", " . (isset($is_system) ? $is_system : 'false') . ")";
        pg_query($conn, $insertSql);
    }

    echo json_encode(['status' => 'success']);
} catch(Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
