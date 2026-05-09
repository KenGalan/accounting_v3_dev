<?php

header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

$id = $_POST['id'];
$cogs_account_id = $_POST['cogs_account_id'];
$changed_by = $_SESSION['ppc']['emp_no'];

if (empty($id)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid ID.'
    ]);
    exit;
}

$id = (int)$id;

if ($cogs_account_id !== null && $cogs_account_id !== '') {
    $cogs_account_id = (int)$cogs_account_id;
} else {
    $cogs_account_id = 'NULL';
}

$sql = "
    UPDATE M_ACC_CUSTOMIZED_DIST_ACCOUNTS
    SET cogs_account_id = $cogs_account_id,
    changed_by = '$changed_by',
    changed_on = now()
    WHERE id = $id
";

$result = pg_query($conn, $sql);

if ($result) {

    echo json_encode([
        'success' => true
    ]);

} else {

    echo json_encode([
        'success' => false,
        'message' => pg_last_error($conn)
    ]);
}
?>