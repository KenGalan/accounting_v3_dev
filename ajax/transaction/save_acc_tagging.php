<?php
session_start();
$db = new Postgresql();


$fromAccount = isset($_POST['from']) ? $_POST['from'] : null;
$toAccount   = isset($_POST['to']) ? $_POST['to'] : null;
$deptGroups  = isset($_POST['dept']) ? $_POST['dept'] : array();
$toWip      = isset($_POST['to_wip']) ? $_POST['to_wip'] : null;
$session     = isset($_SESSION['ppc']) ? $_SESSION['ppc'] : null;

if (!is_array($deptGroups)) {
    $deptGroups = array($deptGroups);
}


if (empty($fromAccount) || empty($toAccount) || empty($deptGroups)) {
    respond(false, 'Validation Error');
}

foreach ($deptGroups as $dept) {

    $chkQuery = "
        SELECT 1 
        FROM M_ACC_ACC_TAGGING 
        WHERE 
            DEPT_GROUP_ID = '$dept'
            AND TO_ACCOUNT_ID = '$toAccount'
            AND FROM_ACCOUNT_ID = '$fromAccount'
        LIMIT 1
    ";
    $res = $db->fetchRow($chkQuery);

    if (!empty($res)) {
        respond(false, 'Item already exists.');
    }

    $finalWip = ($dept == '2' || empty($toWip)) ? 'NULL' : $toWip;

    $data = array(
        'from_account_id'     => $fromAccount,
        'dept_group_id'       => $dept,
        'to_account_id'       => $toAccount,
        'to_wip_account_id'  => $finalWip,
        'added_by'            => (int)$session['emp_no']
    );

    $db->insert($data, 'M_ACC_ACC_TAGGING');
}

respond(true, 'Success');

function respond($flag, $msg)
{
    echo json_encode(array(
        'flag' => $flag,
        'msg'  => $msg
    ));
    exit;
}
