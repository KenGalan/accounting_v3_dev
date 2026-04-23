<?php
session_start();
$db_ken = new PostgresqlKen();
// $conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // $analytic_account_id = isset($_POST['analytic_account_id']) ? intval($_POST['analytic_account_id']) : 0;
    // $dist_id = isset($_POST['dist_id']) ? intval($_POST['dist_id']) : 0;
    // $category_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;



    $query = "
    select a.* from 
    (
    select distributed_account_move_id am_id from m_acc_accrual
    union all
    select wip_account_move_id am_id from m_acc_accrual
    union all
    select reverse_account_move_id am_id from m_acc_accrual
    union all
    select wip_reverse_account_move_id am_id from m_acc_accrual
    ) a
    where a.am_id is not null
    ";

    $result = $db_ken->fetchAll($query);

    try {
        if ($result) {

            // START TRANSACTION
            $db_ken->beginTransaction();
            foreach ($result as $row) {
                $am_id = $row['am_id'];
                $queryDel = "DELETE FROM account_move where id = $am_id";
                $db_ken->query($queryDel);
            }
        }

        $delete1 = "DELETE FROM M_ACC_REVERSAL";
        $delete2 = "DELETE FROM M_ACC_TO_WIP";
        $delete3 = "DELETE FROM m_acc_dist_mo";
        $delete4 = "DELETE FROM  M_ACC_ACCRUAL_DIST";
        $delete5 = "DELETE FROM m_acc_to_wip_reversal";
        $delete6 = "DELETE FROM M_ACC_DIST_MO_LINES";
        $delete7 = "DELETE FROM M_ACC_MO_WIP";
        $delete8 = "DELETE FROM M_ACC_MO_WIP_LINE";
        $db_ken->query($delete1);
        $db_ken->query($delete2);
        $db_ken->query($delete3);
        $db_ken->query($delete4);
        $db_ken->query($delete5);
        $db_ken->query($delete6);
        $db_ken->query($delete7);
        $db_ken->query($delete8);

        $updateAmId = "UPDATE M_ACC_ACCRUAL SET IS_REVERSED = false, distributed_account_move_id = NULL, wip_account_move_id = NULL, reverse_account_move_id = NULL,  wip_reverse_account_move_id = NULL, total_reverse_value = NULL, actual_apv_id = NULL";
        $db_ken->query($updateAmId);


        $updateDateRange = "UPDATE M_ACC_DATE_RANGE SET IS_ALL_REVERSED = false, IS_DEPT_DISTRIBUTED = false";
        $db_ken->query($updateDateRange);

        $updateMonth = "UPDATE M_ACC_MONTH SET IS_ALL_REVERSED = false, IS_DEPT_DISTRIBUTED = false";
        $db_ken->query($updateMonth);

        $db_ken->commit();
        // echo "All accruals and lines inserted successfully.";

        echo json_encode(['status' => 'success']);

        //  else {
        //     echo json_encode(['status' => 'error', 'message' => 'error']);
        // }

    } catch (Exception $e) {
        // ROLLBACK EVERYTHING on ANY error
        $db_ken->rollBack();
        echo "Transaction failed: " . $e->getMessage();
    }
}
