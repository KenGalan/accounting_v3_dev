<?php
$db = new Postgresql();

$tagId = $_POST['id'];

$sel = "SELECT FROM_ACCOUNT_ID, DEPT_GROUP_ID, TO_ACCOUNT_ID, TO_WIP_ACCOUNT_ID FROM M_ACC_ACC_TAGGING WHERE ID = '$tagId'";
$res = $db->fetchRow($sel);

echo json_encode($res);
