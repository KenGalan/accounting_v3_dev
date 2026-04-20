<?php
session_start();
$db = new Postgresql();

$tagId = $_POST['id'];
$fromAccount = $_POST['from'];
$toAccount = $_POST['to'];
$deptGroups = $_POST['dept'];
$toWip = $_POST['to_wip'];
$emp_no = $_SESSION['ppc']['emp_no'];

if (empty($tagId) || empty($toAccount) || empty($deptGroups) || empty($toWip)) {
    respond(false, 'Validation Error');
}

// trappings if combination exist already
$chkQuery = "SELECT * FROM M_ACC_ACC_TAGGING WHERE DEPT_GROUP_ID = '$deptGroups' AND TO_ACCOUNT_ID = '$toAccount' AND FROM_ACCOUNT_ID = '$fromAccount' AND ID !=$tagId ";
$res = $db->fetchRow($chkQuery);

if (!empty($res)) {
    respond(false, 'Item Already exist.');
}

$sel = "UPDATE M_ACC_ACC_TAGGING SET DEPT_GROUP_ID = '$deptGroups', TO_ACCOUNT_ID = '$toAccount', TO_WIP_ACCOUNT_ID ='$toWip', CHANGED_BY = '$emp_no', CHANGED_ON = NOW() WHERE ID = '$tagId'";
$db->query($sel);

respond(true, 'Success');

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
