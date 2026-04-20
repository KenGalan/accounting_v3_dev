<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();

$accrual_id = intval($_POST['accrual_id']);
$apv_id = intval($_POST['apv_id']);
// $new_way_reversal = $_POST['new_way_reversal'];
// echo $new_way_reversal;
// exit;

$q = "with maa as(
    select 
   -- case when new_way_reversal = false then
   --sum(debit) 
   -- else
	maa.total_accrual_value - sum(debit) --end 
     total_diff
    ,maa.* from account_move am
    join  account_move_line aml on aml.move_id = am.id
    join m_acc_accrual maa on maa.credit_to = aml.account_id
    where am.id = $apv_id and maa.id =$accrual_id
    group by
    maa.total_accrual_value, maa.id
    )
    , reversed_accrual as(
    select maa.id accrual_id,
    maa.total_diff total_credit,
    maad.distribution_percentage,
    trunc(ABS(maa.total_diff) * (maad.distribution_percentage/100),2) allocation_trunc,
    ABS(maa.total_diff) * (maad.distribution_percentage/100) allocation,
        sum(trunc(ABS(maa.total_diff) * (maad.distribution_percentage/100),2)) over (partition by MAA.ID) total_allocation_trunc,
            sum(ABS(maa.total_diff) * (maad.distribution_percentage/100)) over (partition by MAA.ID) total_allocation,
            '' dept_group,
            '' dept_group_id,
            maad.analytic_account dept,
            maad.analytic_account_id,
            maa.credit_to debit_to,
            maad.account_id credit_to,
                   null::integer wip_account,
            maa.journal_id
    from maa
    join M_ACC_ACCRUAL_DIST maad on  maad.accrual_id = maa.id
    where maa.id =$accrual_id and maad.distribution_percentage is not null
    )
    , ranked as(
            select
    ra.accrual_id,
        ra.total_credit,
        ra.distribution_percentage,
        ra.allocation - ra.allocation_trunc allocation_diff,
        ra.allocation_trunc,
        ((ra.total_allocation - ra.total_allocation_trunc)/0.01)::integer rows_to_adjust,
        ROW_NUMBER() OVER (PARTITION BY ra.accrual_id ORDER BY ra.allocation - ra.allocation_trunc DESC) AS rn,
        ra.dept_group,
        ra.dept_group_ID,
        ra.dept,
                ra.analytic_account_id,
                    ra.credit_to,
                    ra.wip_account,
                    ra.journal_id
        from reversed_accrual ra
        )
        , final_DEPT_DIST as (
        select 
        accrual_id,
        distribution_percentage,
         CASE 
                WHEN rn <= rows_to_adjust THEN allocation_trunc + 0.01
                ELSE allocation_trunc
            END AS credit_final,
        dept_group,
        dept_group_ID,
        dept,
            analytic_account_id,
            credit_to,
            wip_account,
            journal_id,
            total_credit
        from 
        ranked)
        , debit_credit_DIST as(
        select
        fem.accrual_id,
        fem.distribution_percentage,
    	case when sign(fem.total_credit) = -1 then fem.credit_final else 0::numeric end debit,
	    case when sign(fem.total_credit) = -1 then 0::numeric else fem.credit_final end credit,
        fem.dept_group,
            fem.dept,
            fem.analytic_account_id,
             fem.credit_to account_id,
            fem.wip_account,
            fem.journal_id,
            fem.total_credit
        from
        final_DEPT_DIST fem
        union all
        select
        distinct
        ra.accrual_id,
        0::numeric distribution_percentage,
        case when sign(ra.total_credit) = -1 then 0::numeric else ra.total_credit end debit,
        case when sign(ra.total_credit) = -1 then abs(ra.total_credit) else 0::numeric end  credit,
        '' dept_group,
       '' dept,
            null::integer analytic_account_id,
             ra.debit_to account_id,
             null::integer wip_account,
             ra.journal_id,
             ra.total_credit
        from
        reversed_accrual ra
            )
        select 
        je.id accrual_id,
        aj.name journal,
         dcem.journal_id,
        AA.CODE ACCOUNT_CODE,
        aa.code || ' ' || aa.name account,
        DCEM.account_id,
    '' DATE,
        dcem.dept,
        dcem.distribution_percentage,
        dcem.debit,
        dcem.credit,
        dcem.analytic_account_id,
        split_part(dcem.dept,' ', 1) AA_CODE,
        case when split_part(dcem.dept,' ', 1) = '8120' then 'DIE SALES' 
                    when split_part(dcem.dept,' ', 1) = '8300' then 'TOs' 
                    when split_part(dcem.dept,' ', 1) = '8310' then 'SOT' 
                    when split_part(dcem.dept,' ', 1) = '8100' then 'HERMETICS'
                    when split_part(dcem.dept,' ', 1) = '8110' then 'MODULES'
                end sbu,
        REPLACE(dcem.dept, '''', '''''') ANALYTIC_ACCOUNT,
        DCEM.DEPT_GROUP,
        dcem.wip_account wip_account_id,
        dcem.total_credit,
        $apv_id actual_apv_id
        from
        debit_credit_DIST dcem
        join m_acC_accrual je on je.id = dcem.accrual_id
        LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID =DCEM.ACCOUNT_ID
        left join account_journal aj on aj.id = dcem.journal_id
        ORDER BY sbu desc, analytic_account";


// echo $q; // exit;
$result = $db->fetchAll($q);


$qReverseWip = "with
old_wip as (
select
*
from
M_acc_to_wip
where main_id = $accrual_id
and (debit is not null or debit >0)
),
am as(
SELECT name move_name ,amount_untaxed FROM ACCOUNT_MOVE WHERE id = $apv_id
)
	,for_reverse as(
		select 
maa.id accrual_id,
maa.date_range_id,
aad.sbu, 
	 aad.ACCOUNT_CODE,
        aad.ACCOUNT_ID,
      aad.ANALYTIC_ACCOUNT,
        aad.ANALYTIC_ACCOUNT_ID,
        aad.wip_account_id,
	am.*,
    round(am.amount_untaxed * (aad.distribution_percentage/100),2) debitt,
aad.debit - round(am.amount_untaxed * (aad.distribution_percentage/100),2) diff
from
	M_ACC_ACCRUAL maa
	join M_ACC_ACCRUAL_DIST aad on  aad.accrual_id = maa.id
 join account_analytic_account aaa on aaa.id =aad.analytic_account_id
    join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
	join am on am.move_name is not null
	where  adg.dept_group ='MANUFACTURING/PRODUCT LINE' and accrual_id = $accrual_id
	)
	
	, not_tally as(
        select
       fr.accrual_id,
    adm.mo,
    adm.device,
    adm.category,
    adm.customer_name,
    adm.earned_hrs,
	
    abs(fr.diff) *
    CASE WHEN MACT.mo_pct_ref = 'EH' THEN adm.EH_percentage
    ELSE QTY_PERCENTAGE END allocation,
    fr.sbu,
        adm.is_invoiced,
        fr.ACCOUNT_CODE,
        fr.ACCOUNT_ID,
      fr.ANALYTIC_ACCOUNT,
        fr.ANALYTIC_ACCOUNT_ID,
        fr.wip_account_id
    from 
    for_reverse fr
   -- join m_acc_dist_mo adm on adm.sbu =fr.sbu and fr.date_range_id = adm.date_range_id
 --   JOIN ACCOUNT_ACCOUNT   AA ON AA.ID = fr.ACCOUNT_ID
  --  JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =AA.m_acc_category_id
    	JOIN M_ACC_ACCRUAL a_main on a_main.id = fr.accrual_id
    join m_acc_dist_mo adm on adm.sbu =fr.sbu and fr.date_range_id = adm.date_range_id
		JOIN M_ACC_CATEGORY_ACCOUNTS   ACA ON ACA.ACCOUNT_ID = fr.ACCOUNT_ID and aca.Acc_category_id = a_main.dist_categ_id 
		JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.Acc_category_id
join 	(SELECT name move_name ,amount_untaxed FROM ACCOUNT_MOVE WHERE id =$apv_id) am on am.move_name is not null
    where 
    fr.accrual_id in ($accrual_id) --and adg.dept_group ='MANUFACTURING/PRODUCT LINE'
	
-- 	select * from m_acc_dist_mo

    )
    ,MO_RANKED AS (
    SELECT 
    nt.accrual_id,
    NT.MO,
    NT.DEVICE,
    NT.CATEGORY,
    NT.ALLOCATION,
    NT.CUSTOMER_NAME,
    NT.earned_hrs,
    TRUNC(NT.ALLOCATION,5) TRUNC_ALLOCATION,
    SUM(NT.ALLOCATION) OVER(partition by nt.accrual_id) TOTAL_ALLOCATION,
    SUM(TRUNC(NT.ALLOCATION,5)) OVER(partition by nt.accrual_id) TOTAL_TRUNC_ALLOCATION,
    (
    NT.ALLOCATION- TRUNC(NT.ALLOCATION,5)
    ) ALLOCATION_DIFF,
    ((
    SUM(NT.ALLOCATION) OVER(partition by nt.accrual_id)- SUM(TRUNC(NT.ALLOCATION,5)) OVER(partition by nt.accrual_id)
    )/ 0.00001)::INTEGER ROWS_TO_ADJUST,
    ROW_NUMBER() OVER (partition by nt.accrual_id ORDER BY NT.ALLOCATION - TRUNC(NT.ALLOCATION,5) DESC) AS rn,
    nt.sbu,
    nt.is_invoiced,
    nt.ACCOUNT_CODE,
        nt.ACCOUNT_ID,
        nt.ANALYTIC_ACCOUNT,
        nt.ANALYTIC_ACCOUNT_ID,
        nt.wip_account_id
    FROM 
    NOT_TALLY NT
    )
    , allocation_adjusted as (
    SELECT 
                        accrual_id,
    MO,
    DEVICE,
    CATEGORY,
    CUSTOMER_NAME,
    EARNED_HRS,
    CASE 
    WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
    ELSE TRUNC_ALLOCATION
    END AS ALLOCATION,
                        total_allocation,
    sbu,
    is_invoiced,
                          ACCOUNT_CODE,
        ACCOUNT_ID,
        ANALYTIC_ACCOUNT,
        ANALYTIC_ACCOUNT_ID,
        WIP_aCCOUNT_ID
    FROM
    MO_RANKED
                             )
                             ,FINAL_DATA AS (
    select 
    accrual_id,
    string_Agg(DISTINCT mo,',') mos,
    round(sum(allocation),2) allocation,
    is_invoiced,
    sbu,
    ACCOUNT_CODE CREDIT_ACCOUNT_CODE,
    ACCOUNT_ID CREDIT_ACCOUNT_ID,
        ANALYTIC_ACCOUNT,
        ANALYTIC_ACCOUNT_ID,
        AADD.WIP_ACCOUNT_ID ACCOUNT_ID,
        AA.CODE ACCOUNT_CODE
    from
    allocation_adjusted aadd
    LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AADD.WIP_ACCOUNT_ID
    where is_invoiced
    group by accrual_id,sbu,is_invoiced, total_allocation,
        ANALYTIC_ACCOUNT,
        ANALYTIC_ACCOUNT_ID,
          ACCOUNT_CODE,
        ACCOUNT_ID,
        AADD.WIP_ACCOUNT_ID,
        AA.CODE,
        AADD.WIP_ACCOUNT_ID
    order by accrual_id,sbu,is_invoiced
    )
	,reversed as(
		select fd.*, ow.raw_debit accrual_debit, fd.allocation - ow.raw_debit diff from final_data fd
		join old_wip ow on ow.credit_account_id = fd.credit_account_id and ow.analytic_account_id = fd.analytic_account_id
			   )
    SELECT 
    R.ACCOUNT_CODE,
    R.ACCOUNT_ID,
    REPLACE(R.ANALYTIC_ACCOUNT, '''', '''''') ANALYTIC_ACCOUNT,
    R.ANALYTIC_ACCOUNT_ID,
    R.MOS,
    case when SIGN(R.diff) = 1 THEN 0 ELSE ABS(R.allocation) END DEBIT,
    case when SIGN(R.diff) = 1 THEN R.allocation ELSE 0 END CREDIT,
    R.SBU,
    '' reference,
    aa.name account_name,
    aaa.name department
    FROM reversed R
    join account_account aa on aa.id = r.account_id 
    join account_analytic_account aaa on aaa.id = r.analytic_account_id
    UNION ALL
    SELECT 
    R2.CREDIT_ACCOUNT_CODE ACCOUNT_CODE,
    R2.CREDIT_ACCOUNT_ID ACCOUNT_ID,
    NULL ANALYTIC_ACCOUNT,
    NULL ANALYTIC_ACCOUNT_ID,
    NULL MOS,
    CASE WHEN SIGN(SUM(R2.diff)) = 1 THEN SUM(R2.allocation) ELSE 0 END DEBIT,
    CASE WHEN SIGN(SUM(R2.diff)) = 1 THEN 0 ELSE ABS(SUM(R2.allocation)) END CREDIT,
    '' SBU,
    '' reference,
    aa.name account_name,
   '' department
    FROM reversed R2
    join account_account aa on aa.id = r2.CREDIT_ACCOUNT_ID
    GROUP BY
    R2.CREDIT_ACCOUNT_CODE,
    R2.CREDIT_ACCOUNT_ID,
    aa.name";

$resultRWip = $db->fetchAll($qReverseWip);


$data = [
    'result' => $result,
    'result_reverse_wip' => $resultRWip
];
echo json_encode($data);
