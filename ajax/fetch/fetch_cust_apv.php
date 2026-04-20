<?php
$db = new Postgresql();
$yearMonth = $_POST['year_month'];
// $yearMonth = '2026-02';
$q = "	  
select distinct AM.NAME journal_entry,am.ref reference, am.getting_total_of_debit_credit_val amount_total, am.date accounting_date, am.state status, am.id am_id from
account_move am
join account_move_line aml on aml.move_id = am.id and aml.debit >0
JOIN m_acc_customized_dist_accounts ACD ON ACD.ACCOUNT_ID = AML.ACCOUNT_ID
where to_char(am.date, 'YYYY-MM') = '$yearMonth'
and am.state = 'posted'
--and am.name ='MISC/2025/10272'
";
$result = $db->fetchAll($q);

echo json_encode($result);
