<?php
$db = new Postgresql();

$accrual_id = $_POST['accrual_id'];
$date_range_id = $_POST['date_range_id'];
// echo $date_range_id;
// echo $journal_entries_id;
// exit;
$sel = "with not_tally as(select
adm.mo,
adm.device,
adm.category,
adm.customer_name,
adm.earned_hrs,
aad.debit *
CASE WHEN aad.mo_pct_ref = 'EH' THEN adm.EH_percentage
ELSE QTY_PERCENTAGE END
allocation,
aad.sbu
from 
m_acc_date_range adr
-- join m_acc_dist_journal_entries adje on adje.date_range_id =adr.id
-- join m_acc_dist_journal_items adji on adji.main_id = adje.id
join M_ACC_ACCRUAL maa on maa.date_range_id = adr.ID
join (
	select  a.accrual_id, a.analytic_account, a.analytic_account_id, sum(a.distribution_percentage) distribution_percentage,a.sbu, sum(a.debit) debit,sum(a.credit) credit,
	MACT.mo_pct_ref
	from M_ACC_ACCRUAL_DIST a
	JOIN M_ACC_ACCRUAL a_main on a_main.id = a.accrual_id
	JOIN M_ACC_CATEGORY_ACCOUNTS   ACA ON ACA.ACCOUNT_ID = a.account_id and aca.Acc_category_id = a_main.dist_categ_id 
	JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.Acc_category_id
	WHERE A.ACCRUAL_ID = $accrual_id
	group by  a.accrual_id, a.analytic_account, a.analytic_account_id,a.sbu,MACT.mo_pct_ref order by analytic_account_id
	) aad on aad.accrual_id = maa.id	
join account_analytic_account aaa on aaa.id =aad.analytic_account_id
join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
join m_acc_dist_mo adm on adm.sbu =aad.sbu and adm.date_range_id = adr.id

where adr.id = $date_range_id and 
maa.id =$accrual_id
	and adg.dept_group ='MANUFACTURING/PRODUCT LINE')
,MO_RANKED AS (
	SELECT 
	NT.MO,
	NT.DEVICE,
	NT.CATEGORY,
	NT.ALLOCATION,
		NT.CUSTOMER_NAME,
		NT.earned_hrs,
	TRUNC(NT.ALLOCATION,5) TRUNC_ALLOCATION,
	SUM(NT.ALLOCATION) OVER() TOTAL_ALLOCATION,
	SUM(TRUNC(NT.ALLOCATION,5)) OVER() TOTAL_TRUNC_ALLOCATION,
	(
		NT.ALLOCATION- TRUNC(NT.ALLOCATION,5)
	) ALLOCATION_DIFF,
	((
		SUM(NT.ALLOCATION) OVER()- SUM(TRUNC(NT.ALLOCATION,5)) OVER()
	)/ 0.00001)::INTEGER ROWS_TO_ADJUST,
	ROW_NUMBER() OVER (ORDER BY NT.ALLOCATION - TRUNC(NT.ALLOCATION,5) DESC) AS rn,
    nt.sbu
	FROM 
	NOT_TALLY NT
	)
		SELECT 
MO,
DEVICE,
CATEGORY,
CUSTOMER_NAME,
EARNED_HRS,
	 CASE 
            WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
            ELSE TRUNC_ALLOCATION
        END AS ALLOCATION,
        sbu
	FROM
	MO_RANKED";
$res = $db->fetchAll($sel);

echo json_encode($res);
