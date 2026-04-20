<?php
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dept_group = isset($_POST['dept_group']) ? trim($_POST['dept_group']) : '';
    // $dept_code = isset($_POST['dept_code']) ? trim($_POST['dept_code']) : '';
    $added_by  = $_SESSION['ppc']['emp_no'];
    $active    = isset($_POST['active']) ? $_POST['active'] : 'true';

    if ($dept_group === '') {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit;
    }

    $query = "
        INSERT INTO M_ACC_DEPARTMENT_GROUPS (dept_group, added_on, added_by, active)
        VALUES ($1, NOW(), $2, $3)
        RETURNING id, dept_group, added_on, added_by, active
    ";

    $result = pg_query_params($conn, $query, [$dept_group, $added_by, $active]);

    if ($row = pg_fetch_assoc($result)) {
        echo json_encode(['status' => 'success', 'new' => $row]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed']);
    }
}
