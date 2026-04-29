<?php
session_start();
$db = new Postgresql();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $dist_id = isset($_POST['dist_id']) ? intval($_POST['dist_id']) : 0;
    // echo dist_id;
    // exit

    $data = isset($_POST['data']) ? json_decode($_POST['data'], true) : [];

    if ($category_id <= 0 || empty($data)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data received']);
        exit;
    }

    $success = true;
    $added_on = date('Y-m-d H:i:s.u');
    $added_by = $_SESSION['ppc']['emp_no'];

    $analytic_ids = array_map(function ($row) {
        return intval($row['analytic_account_id']);
    }, $data);
    $exclude_ids = implode(',', $analytic_ids);

    $query_total = "
        SELECT COALESCE(SUM(distribution_percentage),0) AS total_percentage
        FROM M_ACC_COST_DISTRIBUTION
        WHERE m_acc_category_id = $category_id
        AND analytic_account_id NOT IN ($exclude_ids)
    ";
    $result_total = pg_query($conn, $query_total);
    $row_total = pg_fetch_assoc($result_total);
    $current_total = floatval($row_total['total_percentage']);

    // Check if new total exceeds 100%
    $new_total = $current_total;
    foreach ($data as $row) {
        $new_total += floatval($row['distribution_percentage']);
    }

    if ($new_total > 100) {
        echo json_encode([
            'status' => 'error',
            'message' => "Cannot save. Total distribution exceeds 100%. Current: {$current_total}%, New total: {$new_total}%."
        ]);
        exit;
    }

    // Loop through each row to update or insert
    foreach ($data as $row) {
        $analytic_account_id = $row['analytic_account_id'] ? intval($row['analytic_account_id']) : 0;
        $group_id = isset($row['group_id']) ? intval($row['group_id']) : 0;
        $distribution_percentage = floatval($row['distribution_percentage']);
        $debit_to = intval($row['debit_to']);
        $wip_account = intval($row['wip_account']);





        // if ($result && pg_num_rows($result) > 0) {
        if ($dist_id) {


            $update_query = "
            UPDATE M_ACC_COST_DISTRIBUTION
            SET distribution_percentage = $distribution_percentage,
                debit_to = $debit_to,
                wip_account = $wip_account,
                changed_on = '$added_on',
                changed_by = $added_by
            WHERE id = $dist_id
        ";


            if (!pg_query($conn, $update_query)) {
                $success = false;
            }
        } else {

            $insert_query = "
            INSERT INTO M_ACC_COST_DISTRIBUTION
            (
                distribution_percentage,
                analytic_account_id,
                group_id,
                added_by,
                active,
                m_acc_category_id,
                debit_to,
                wip_account
            )
            VALUES (
                $distribution_percentage,
                $analytic_account_id,
                $group_id,
                $added_by,
                true,
                $category_id,
                $debit_to,
                $wip_account
            )
        ";

            if (!pg_query($conn, $insert_query)) {
                $success = false;
            }
        }
    }

    if ($success) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => pg_last_error($conn)]);
    }
}
