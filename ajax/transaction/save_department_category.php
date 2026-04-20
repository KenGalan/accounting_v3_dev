<?php
$db = new Postgresql();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dept_ids = $_POST['dept_ids'] ?? [];
    $category_id = intval($_POST['category_id'] ?? 0);

    if (empty($dept_ids) || $category_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data received']);
        exit;
    }

    $success = true;
    foreach ($dept_ids as $dept_id) {
        $dept_id = intval($dept_id);
        $update_query = "
            UPDATE ACCOUNT_ANALYTIC_ACCOUNT
            SET M_ACC_CATEGORY_ID = $category_id
            WHERE id = $dept_id
        ";
        $result = pg_query($conn, $update_query);
        if (!$result) {
            $success = false;
            break;
        }
    }

    echo json_encode(['status' => $success ? 'success' : 'error']);
}
?>
