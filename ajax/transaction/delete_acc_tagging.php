<?php
session_start();
$db = new Postgresql();
$sesion = $_SESSION['ppc'];

$tagId = $_POST['id'];

if (empty($tagId)) {
    respond(false, 'Validation erorr');
}

$delQuery = "DELETE FROM M_ACC_ACC_TAGGING WHERE ID = '$tagId'";
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
