<?php
ob_start();

header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

// Current month
// $query = "
// SELECT id, name
// FROM ACCOUNT_MOVE
// WHERE NAME ILIKE '%APV/%'
// AND date_trunc('month', date) = date_trunc('month', CURRENT_DATE)
// AND state = 'posted'
// ORDER BY name
// ;";

// Adjust ko muna kendrick kasi walang data yung feb
// $query = "
// SELECT id, name,
// amount_untaxed
// FROM account_move
// WHERE name ILIKE '%APV/%'
//   AND invoice_date >= DATE '2025-11-01'
//   AND state = 'posted'
//   and id not in (select distinct coalesce(actual_apv_id,0) from m_acc_accrual)
// ORDER BY name";

$query = "SELECT distinct 
am.id, 
am.name,
am.amount_untaxed, 
aml.account_id,
maa.dist_categ_id,
max(aml.name) over(partition by am.name) max_label
FROM account_move am
join account_move_line aml on aml.move_id = am.id
join m_acc_accrual maa on maa.credit_to = aml.account_id
WHERE am.name ILIKE '%APV/%'
  AND am.invoice_date >= DATE '2025-11-01'
  AND am.state = 'posted'
  and am.id not in (select distinct coalesce(actual_apv_id,0) from m_acc_accrual)
  and aml.debit >0
 -- and account_id =2960
ORDER BY am.name";

$res = pg_query($conn, $query);

$data = [];

while ($row = pg_fetch_assoc($res)) {
  $data[] = $row;
}

echo json_encode($data);
ob_end_flush();
