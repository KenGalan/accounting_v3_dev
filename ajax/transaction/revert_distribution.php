<?php
header('Content-Type: application/json');
error_reporting(E_ALL);

$db = new Postgresql();
$conn = $db->getConnection();

$date_range_id = $_POST['date_range_id'];
// echo $date_range_id;
// exit;

if (!$date_range_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing date_range_id'
    ]);
    exit;
}

pg_query($conn, "BEGIN");
try {
    $sql_m_acc_to_wip = "
        DELETE FROM M_ACC_TO_WIP
        WHERE main_id IN (
            SELECT id
            FROM M_ACC_ACCRUAL
            WHERE date_range_id = $date_range_id
        )
    "; 
    pg_query($conn, $sql_m_acc_to_wip);
    $sql_m_acc_accrual_dist = "
        DELETE FROM M_ACC_ACCRUAL_DIST
        WHERE accrual_id IN (
            SELECT id
            FROM M_ACC_ACCRUAL
            WHERE date_range_id = $date_range_id
        )
    ";
    pg_query($conn, $sql_m_acc_accrual_dist);
    $sql_m_acc_dist_mo = "
        DELETE FROM M_ACC_DIST_MO
        WHERE date_range_id = $date_range_id
    ";
    pg_query($conn, $sql_m_acc_dist_mo);
    $sql_m_acc_date_range = "
        UPDATE M_ACC_DATE_RANGE
        SET is_dept_distributed = FALSE
        WHERE id = $date_range_id
    ";
    pg_query($conn, $sql_m_acc_date_range);

    pg_query($conn, "COMMIT");

    echo json_encode([
        'status' => 'success',
        'message' => 'Distribution reverted successfully'
    ]);

} catch (Exception $e) {
    pg_query($conn, "ROLLBACK");

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}