<?php
session_start();
$db = new Postgresql();
$sesion = $_SESSION['ppc'];

$accId = $_POST['acc_id'];

if (empty($accId)) {
    respond(false, 'Validation erorr');
}

$delQuery = "DELETE FROM M_ACC_ACCRUAL WHERE ID = '$accId'";
$db->query($delQuery);

respond(true, "Successfully Deleted");

function respond($flag, $msg)
{
    echo json_encode(
        array(
            'flag' => $flag,
            'msg' => $msg
        )
    );
    exit;
}
