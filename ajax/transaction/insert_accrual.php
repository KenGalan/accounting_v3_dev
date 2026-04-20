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

$month_id = intval($_POST['month_id']);

$acc_data = json_decode($_POST['acc_data'], true);


try {
    $db_ken->beginTransaction();


    foreach ($acc_data as $row) {

        $insertData = [
            'DATE_RANGE_ID' => $month_id,
            'CREDIT_TO' => $row['credit_account'],
            'TOTAL_ACCRUAL_VALUE' => $row['accrual_value'],
            'DIST_CATEG_ID' => intval($row['dist_template']),
        ];

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