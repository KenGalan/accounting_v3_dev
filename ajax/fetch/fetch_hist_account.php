<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Postgresql();

$category_id = $_POST['category_id'];

$query_history = "
    SELECT   
        aa.id AS account_id,
        aa.name AS account_name,
        aa.code AS code,
        aahist.updated_by AS updated_by,
        aahist.updated_on AS updated_on
    FROM m_account_account_m_acc_category_id_update_hist aahist
    JOIN M_ACC_CATEGORY_TBL cat 
        ON cat.id = aahist.old_value::INTEGER
    JOIN account_account aa 
        ON aahist.account_id = aa.id
    WHERE cat.id = $category_id
    ORDER BY aahist.updated_on DESC
";

$result_history = $db->fetchAll($query_history);

echo json_encode($result_history);
