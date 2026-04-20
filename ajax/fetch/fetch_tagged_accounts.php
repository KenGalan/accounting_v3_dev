<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Postgresql();


$category_id = $_POST['category_id'];

// $prodPercentage = $resProdPercentage['prod_allocation'];
// $amountTotal = $resProdPercentage['total_debit'];


$query_accounts = "SELECT DISTINCT 
aa.id, 
aa.name AS account_name,
aa.code AS code,
aa.code || ' ' || aa.name as acc_fullname
FROM account_account aa
JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACCOUNT_ID = AA.ID
WHERE ACA.ACC_CATEGORY_ID = $category_id
ORDER BY account_name ASC 
    ";

$result_accounts =  $db->fetchAll($query_accounts);



// $data = [
//     'mo_dist' => $result,

// ];

echo json_encode($result_accounts);
