<?php
$db = new Postgresql();

$journal_entries_id = $_POST['journal_entries_id'];
$date_range_id = $_POST['date_range_id'];

$sel = " 
select
'' reference ,
aa.code || ' '||trim(aa.name) account_name,
atw.analytic_account department,
atw.debit,
coalesce(atw.credit,0) credit,
atw.mos
from m_acc_to_wip atw
join M_ACC_ACCRUAL adje on adje.id = atw.main_id
join account_account aa on aa.id = atw.account_id
where atw.main_id = $journal_entries_id";

// $sel = "select a.*,
// round(coalesce(a.debit,0) + a.additional,2) new_debit
// from
// (select
// '' reference ,
// aa.code || ' '||trim(aa.name) account_name,
// atw.analytic_account department,
// atw.debit,
// coalesce(atw.credit,0) credit,
// atw.mos,
// atw.sbu,
// sum(coalesce(b.allocation,0)) additional
// from m_acc_to_wip atw
// join M_ACC_ACCRUAL adje on adje.id = atw.main_id
// join account_account aa on aa.id = atw.account_id
// left join (
// 	select  sum(coalesce(aml.actual_allocation,aml.reversed_allocation)) allocation, am.mo, am.sbu, am.mo_status from m_acc_dist_mo am
// 	left join m_acC_dist_mo_lines aml on aml.dist_mo_id = am.id
// 	WHERE date_range_id != $date_range_id and not is_invoiced
// 	and coalesce(aml.actual_allocation,aml.reversed_allocation) is not null
// 	group by mo, sbu, mo_status
// ) b on  atw.mos  LIKE  '%'||  b.mo || '%' AND UPPER(B.SBU) =UPPER(ATW.SBU)
// where adje.id = $journal_entries_id
// group by 
// aa.code || ' '||trim(aa.name) ,
// atw.analytic_account,
// atw.debit,
// coalesce(atw.credit,0),
// atw.mos,
// atw.sbu)a";
$res = $db->fetchAll($sel);

echo json_encode($res);
