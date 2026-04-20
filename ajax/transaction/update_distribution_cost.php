<?php
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

if (
    isset($_POST['id']) &&
    isset($_POST['distribution_percentage']) &&
    isset($_POST['dept_id']) &&
    isset($_POST['account_id'])
) {
    $id = intval($_POST['id']);
    $distribution_percentage = trim($_POST['distribution_percentage']);
    $dept_id = intval($_POST['dept_id']);
    $account_id = intval($_POST['account_id']);
    $changed_by = $_SESSION['ppc']['emp_no'];
    $active = isset($_POST['active']) ? ($_POST['active'] === 'true' || $_POST['active'] == '1') : true;

    if ($id <= 0 || $distribution_percentage === '' || $dept_id === 0 || $account_id === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or missing required fields.']);
        exit;
    }

    $groupQuery = "SELECT group_id FROM M_ACC_DEPARTMENT_GROUPS WHERE dept_id = $1 LIMIT 1";
    $groupResult = pg_query_params($conn, $groupQuery, [$dept_id]);

    if (!$groupResult || pg_num_rows($groupResult) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'No group found for the selected department.']);
        exit;
    }

    $groupRow = pg_fetch_assoc($groupResult);
    $group_id = intval($groupRow['group_id']);

    $updateQuery = "
        UPDATE M_ACC_COST_DISTRIBUTION
        SET 
            distribution_percentage = $1,
            account_id = $2,
            dept_id = $3,
            group_id = $4,
            changed_on = NOW(),
            changed_by = $5,
            active = $6
        WHERE id = $7
        RETURNING id, distribution_percentage, dept_id, account_id, group_id, changed_on, changed_by, active
    ";

    $params = array($distribution_percentage, $account_id, $dept_id, $group_id, $changed_by, $active, $id);
    $updateResult = pg_query_params($conn, $updateQuery, $params);

    if ($updateResult && pg_num_rows($updateResult) > 0) {
        $updated = pg_fetch_assoc($updateResult);
        echo json_encode(['status' => 'success', 'message' => 'Record updated successfully.', 'updated' => $updated]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed or no changes detected.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Incomplete request.']);
}
?>
