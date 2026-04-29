<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$db_ken = new PostgresqlKen();
// $month_id = $_POST['month_id'];
$user = $_SESSION['ppc']['emp_no'];
if (!isset($_SESSION['ppc']['emp_no'])) : $user = 0;
    echo json_encode($data);
    exit;
endif; //NOT ISSET SESSION

$month_id = isset($_POST['month_id']) ? intval($_POST['month_id']) : 0;

$year_month = $_POST['year_month'];
// echo $year_month;
// exit;
$acc_data = json_decode($_POST['acc_data'], true);


try {
    $db_ken->beginTransaction();

    if ($month_id == 0) {
        $month_entries = [
            'YEAR_MONTH' =>    $year_month
        ];

        $month_id = $db_ken->insert_get_id('M_ACC_MONTH', $month_entries, 'id');
    }
    foreach ($acc_data as $row) {

        if ($row['from_date']) {
            $insertData = [
                'MONTH_ID' => $month_id,
                'CREDIT_TO' => $row['credit_account'],
                'TOTAL_ACCRUAL_VALUE' => $row['accrual_value'],
                'DIST_CATEG_ID' => intval($row['dist_template']),
                'JOURNAL_ID' => isset($row['journal_id']) ? intval($row['journal_id']) : null,
                'JOURNAL_NAME' => $row['journal_acc'],
                'FROM_DATE' => $row['from_date'],
                'TO_DATE' => $row['to_date'],
                'ADDED_BY' => intval($user)
            ];
        } else {
            $insertData = [
                'MONTH_ID' => $month_id,
                'CREDIT_TO' => $row['credit_account'],
                'TOTAL_ACCRUAL_VALUE' => $row['accrual_value'],
                'DIST_CATEG_ID' => intval($row['dist_template']),
                'JOURNAL_ID' => isset($row['journal_id']) ? intval($row['journal_id']) : null,
                'JOURNAL_NAME' => $row['journal_acc'],
                'ADDED_BY' => intval($user)
            ];
        }


        $db_ken->insert('M_ACC_ACCRUAL', $insertData);
    }
    $db_ken->commit();
} catch (Exception $e) {
    // ROLLBACK EVERYTHING on ANY error
    $db_ken->rollBack();
    echo "Transaction failed: " . $e->getMessage();
}
echo json_encode('sucess');
// exit;