<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    exit;
}

$id = isset($_POST['id']) ? trim($_POST['id']) : '';

if ($id === '') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing ID.'
    ]);
    exit;
}

// Check kung yung reference ay may nakatag na date range, sbu, and mo dist. Regardless kung isa lang sa mga 'yan ang meron hindi pa rin madedelete.
$check_sql = "SELECT 1
	FROM ACCOUNT_MOVE A
	JOIN m_acc_cust_dist B ON A.ID = B.MOVE_ID
	JOIN m_acc_customized_dist_accounts C ON A.REF = C.REFERENCE
    WHERE C.ID = $1";

$check = pg_query_params($conn, $check_sql, array($id));

if ($check && pg_num_rows($check) > 0) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Cannot delete reference. It is currently in use.'
    ]);
    exit;
} // KATAPUSAN

$update_sql = "
    UPDATE m_acc_customized_dist_accounts
    SET active = FALSE
    WHERE id = $1
";
$update_result = pg_query_params($conn, $update_sql, array($id));

if ($update_result) {
    echo json_encode([
        'status' => 'success'
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to delete reference.'
    ]);
}