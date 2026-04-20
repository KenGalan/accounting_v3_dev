<?php
session_start();
$db_ken = new PostgresqlKen();
// $conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
    $data = isset($_POST['data']) ? json_decode($_POST['data'], true) : [];

    if ($category_id <= 0 || empty($data)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data received']);
        exit;
    }

    $sum_query = "SELECT COALESCE(SUM(distribution_percentage),0) AS total_percentage 
                  FROM M_ACC_COST_DISTRIBUTION 
                  WHERE m_acc_category_id = $category_id";
    // $sum_result = pg_query($conn, $sum_query);
    // $row = pg_fetch_assoc($sum_result);
    // $current_total = floatval($row['total_percentage']);
    $sum_result = $db_ken->fetchRow($sum_query);
    $current_total = $sum_result['total_percentage'];
    // echo $current_total;
    // exit;

    foreach ($data as $row) {
        $analytic_account_id = intval($row['analytic_account_id']);
        $new_percentage = $row['distribution_percentage'];
        $new_debit_to = intval($row['debit_to']);

        $check_query = "SELECT distribution_percentage FROM M_ACC_COST_DISTRIBUTION 
                        WHERE analytic_account_id = $analytic_account_id 
                        AND m_acc_category_id = $category_id
                        AND debit_to = $new_debit_to";
        // $check_result = pg_query($conn, $check_query);
        $check_result = $db_ken->fetchRow($check_query);

        $existing_percentage = 0;

        if (count($check_result) > 0) {
            // $existing_row = pg_fetch_assoc($check_result);
            $existing_percentage = $check_result['distribution_percentage'];
        }

        $current_total = $current_total - $existing_percentage + $new_percentage;
        // echo '<pre>';

        // var_dump($current_total);
        // // var_dump($current_total > 100);
        // // var_dump($current_total);
        // printf("%.20f\n", $existing_percentage);
        // printf("%.20f\n", $new_percentage);
        // printf("%.20f\n", $current_total);
        if (round($current_total, 2) > 100) {
            echo json_encode([
                'status' => 'error',
                'message' => 'The distribution percentage is already 100%'
            ]);
            exit;
        }
    }

    $success = true;
    $added_on = date('Y-m-d H:i:s.u');
    $added_by = $_SESSION['ppc']['emp_no'];
    try {
        $db_ken->beginTransaction();
        foreach ($data as $row) {
            $analytic_account_id = intval($row['analytic_account_id']);
            $distribution_percentage = floatval($row['distribution_percentage']);
            $debit_to = intval($row['debit_to']);
            $wip_account = intval($row['wip_account']);

            $check_query = "SELECT distribution_percentage FROM M_ACC_COST_DISTRIBUTION 
                        WHERE analytic_account_id = $analytic_account_id 
                        AND m_acc_category_id = $category_id
                        AND debit_to = $debit_to";
            // $check_result = pg_query($conn, $check_query);
            $check_result = $db_ken->fetchRow($check_query);
            // var_dump($check_result);
            // var_dump(count($check_result) > 0);
            // exit;
            if ($check_result) {
                $update_query = "UPDATE M_ACC_COST_DISTRIBUTION
                SET distribution_percentage = $1,
                debit_to = $2,
                    changed_on = $3,
                    changed_by = $4
                WHERE analytic_account_id = $5
                AND m_acc_category_id = $6
            ";
                // if (!pg_query($conn, $update_query)) $success = false;
                $db_ken->query(
                    $update_query,
                    [$distribution_percentage, $debit_to, $added_on, $added_by, $analytic_account_id, $category_id]
                );
            } else {
                // $insert_query = "
                //     INSERT INTO M_ACC_COST_DISTRIBUTION
                //     (distribution_percentage, analytic_account_id, added_on, added_by, active, m_acc_category_id, debit_to,wip_account)
                //     VALUES ($distribution_percentage, $analytic_account_id, '$added_on', $added_by, true, $category_id, $debit_to,$wip_account)
                // ";
                // if (!pg_query($conn, $insert_query)) $success = false;

                $dist_entries = [
                    'DISTRIBUTION_PERCENTAGE' =>    $distribution_percentage,
                    'ANALYTIC_ACCOUNT_ID' => $analytic_account_id,
                    'ADDED_BY' => $added_by,
                    'M_ACC_CATEGORY_ID' => $category_id,
                    'DEBIT_TO' => $debit_to,
                    'WIP_ACCOUNT' => $wip_account
                ];
                // $db->insert($mo_link_entries, 'ACCOUNT_MOVE_LINE_MO_LINK');

                $db_ken->insert('M_ACC_COST_DISTRIBUTION', $dist_entries);
            }
        }

        $db_ken->commit();
    } catch (Exception $e) {
        // ROLLBACK EVERYTHING on ANY error
        $db_ken->rollBack();
        echo "Transaction failed: " . $e->getMessage();
    }
    if ($success) {
        echo json_encode(['status' => 'success', 'message' => 'Distribution saved successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => pg_last_error($conn)]);
    }
}
