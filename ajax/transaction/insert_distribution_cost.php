<?php
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

if (isset($_POST['distribution_percentage'], $_POST['dept_id'], $_POST['account_id'])) {
    $distribution_percentage = trim($_POST['distribution_percentage']);
    $dept_id = intval($_POST['dept_id']);
    $account_id = intval($_POST['account_id']);
    $added_by  = $_SESSION['ppc']['emp_no'];
    $active = true;

    if ($distribution_percentage === '' || $dept_id === 0 || $account_id === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
        exit;
    }

    $check_query = "
        SELECT COUNT(*) AS count 
        FROM M_ACC_COST_DISTRIBUTION 
        WHERE account_id = $1 AND dept_id = $2
    ";
    $check_params = array($account_id, $dept_id);
    $check_result = pg_query_params($conn, $check_query, $check_params);

    if ($check_result) {
        $row = pg_fetch_assoc($check_result);
        if ($row['count'] > 0) {
            echo json_encode(['status' => 'error', 'message' => 'This department is already assigned to this account.']);
            exit;
        }
    }

    $query = "
        INSERT INTO M_ACC_COST_DISTRIBUTION 
        (distribution_percentage, account_id, dept_id, added_on, added_by, active)
        VALUES ($1, $2, $3, NOW(), $4, $5)
        RETURNING id, distribution_percentage, dept_id, account_id, added_on, added_by, active
    ";

    $params = array($distribution_percentage, $account_id, $dept_id, $added_by, $active);
    $result = pg_query_params($conn, $query, $params);

    if ($result && pg_num_rows($result) > 0) {
        $new = pg_fetch_assoc($result);
        echo json_encode(['status' => 'success', 'message' => 'Inserted successfully', 'new' => $new]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed.']);
    }

} else {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete request.']);
}
?>
