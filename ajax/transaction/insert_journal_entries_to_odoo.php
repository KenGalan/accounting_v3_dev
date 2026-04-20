<?php

session_start();

$db = new Postgresql();
$db_ken = new PostgresqlKen();
$month_id = $_POST['month_id'];

// Added by Ivan 03/23/26 -  Check if the month is already inserted to Odoo.
$accrual_check = $db_ken->fetchRow("
    SELECT distributed_account_move_id, wip_account_move_id
    FROM M_ACC_ACCRUAL
    WHERE (distributed_account_move_id IS NOT NULL
    OR wip_account_move_id IS NOT NULL) and date_range_id = $month_id
");
// echo $accrual_check;
// exit;
if ($accrual_check) {
    echo json_encode([
        'status' => 'exists',
        'message' => 'Already been inserted to Odoo.'
    ]);
    exit;
}

$qWIP = "SELECT mo_id, sbu FROM
M_ACC_DIST_MO
WHERE DATE_RANGE_ID =$month_id
AND --NOT IS_INVOICED
IS_INVOICED
order by sbu";
// echo $qWIP;
// exit;
$mosRes = $db_ken->fetchAll($qWIP);
$allWIP = [];



foreach ($mosRes as $row) {
    $key = strtolower($row['sbu']); // "modules", "sot"
    $allWIP[$key][] = (string) $row['mo_id'];
}

// var_dump($allWIP);
// exit;

$q = "select a.* from (SELECT
maa.id accrual_id,
'' reference,
maad.item_label,
TO_CHAR(maa.date, 'MM/DD/YYYY') last_date_of_month,
maa.journal_name journal,
maa.journal_id,
maad.account_code,
-- adji.item_label label,
maad.account_id,
maad.analytic_account_id,
maad.analytic_account,
COALESCE(maad.debit,0)  debit,
COALESCE(maad.credit,0) credit,
aa.root_id,
false is_wip,
coalesce(maad.wip_Account_id, NULL::INTEGER) wip_account_id
FROM
M_ACC_DATE_RANGE ADR
				join m_acc_accrual maa on maa.date_range_id = adr.id
				join m_acc_accrual_dist maad on maad.accrual_id = maa.id
left join account_account aa on aa.id = maad.account_id
WHERE adr.id = $month_id
ORDER BY maa.id,COALESCE(maad.debit,0) DESC, maad.account_code	)A
UNION ALL
--select b.* from (
--	select
--	maa.id accrual_id,
--'' reference ,
--atw.item_label,
--TO_CHAR(maa.date, 'MM/DD/YYYY') last_date_of_month,
--maa.journal_name journal,
--maa.journal_id,
--aa.code account_code,
--aa.id account_id,
--atw.analytic_account_id,
--atw.analytic_account,
--COALESCE(atw.debit,0) debit,
--COALESCE(atw.credit,0) credit,
--aa.root_id,
--true is_wip,
--atw.account_id wip_account_id
--from m_acc_to_wip atw
--join m_acc_accrual maa on maa.id = atw.main_id
--join account_account aa on aa.id = atw.account_id
--join M_ACC_DATE_RANGE ADR on ADR.id = maa.date_range_id
--where adr.id =month_id
--ORDER BY maa.id,COALESCE(atw.debit,0) DESC, aa.code
--	)b
select b.* from (
	select
	maa.id accrual_id,
'' reference ,
-- atw.item_label,
'' item_label,
TO_CHAR(maa.date, 'MM/DD/YYYY') last_date_of_month,
maa.journal_name journal,
maa.journal_id,
aa.code account_code,
aa.id account_id,
atw.analytic_account_id,
atw.analytic_account,
sum(COALESCE(atw.debit,0)) debit,
sum(COALESCE(atw.credit,0)) credit,
aa.root_id,
true is_wip,
atw.account_id wip_account_id
from m_acc_to_wip atw
join m_acc_accrual maa on maa.id = atw.main_id
join account_account aa on aa.id = atw.account_id
join M_ACC_DATE_RANGE ADR on ADR.id = maa.date_range_id
where adr.id =$month_id
	group by
	maa.id,
TO_CHAR(maa.date, 'MM/DD/YYYY') ,
maa.journal_name,
maa.journal_id,
aa.code ,
aa.id ,
atw.analytic_account_id,
atw.analytic_account,
aa.root_id,
atw.account_id
 ORDER BY maa.id,debit DESC, aa.code
	)b
";
$result = $db_ken->fetchAll($q);
// $result2 = $result;

if ($result) {
    try {
        // START TRANSACTION
        $db_ken->beginTransaction();
        $account_move_checker = '';
        foreach ($result as $row) {
            $last_date_of_month = $row['last_date_of_month'];
            $aml_label = $row['item_label'];
            $journal_id = $row['journal_id'];
            $amount_total = $row['credit'];
            // $journal_name = $row['journal'];
            $aa_root_id = $row['root_id'];
            $debit = $row['debit'];
            $credit = $row['credit'];
            $balance =  $row['debit'] && $row['debit'] > 0 ?   $row['debit'] :  $row['credit'] * -1;
            $analytic_acount_id = $row['analytic_account_id'];
            $account_id = $row['account_id'];
            $analytic_account_code = explode(' ', trim($row['analytic_account']))[0];
            $is_wip =  $row['is_wip'];
            $from_prev = $row['item_label'] = '' || !$row['item_label'] ? false : true;
            $accrual_id = $row['accrual_id'];
            $wip_account_id = $row['wip_account_id'];
            // echo $wip_account_id . '-';
            // if (!$wip_account_id) {
            //     echo 'wala pala';
            // }


            if ($is_wip == 't') {
                $ref = '/sample cogs';
                $journal_name = 'Miscellaneous Operations';
                $journal_id = 3;
            } else {
                $ref = '/sample';
                $journal_name = $row['journal'];
                $journal_id = $row['journal_id'];
            }
            // echo $is_wip;
            // exit;
            // echo $balance;
            $account_move_checker_new = $row['accrual_id'] . $is_wip;
            if ($account_move_checker != $account_move_checker_new) {

                $am_entries = [
                    'NAME' =>    '/',
                    'DATE'    => $last_date_of_month,
                    'REF' =>    $ref,
                    'STATE' =>    'draft',
                    'TYPE'    => 'entry',
                    'TO_CHECK' =>    'false', //boolean
                    'JOURNAL_ID' =>    $journal_id,
                    'COMPANY_ID' =>    1,
                    'CURRENCY_ID' =>    36,
                    'AMOUNT_UNTAXED' => 0.000,
                    'AMOUNT_TAX' => 0.000,
                    'AMOUNT_TOTAL' =>    $amount_total,
                    'AMOUNT_RESIDUAL' => 0,
                    'AMOUNT_UNTAXED_SIGNED' => 0,
                    'AMOUNT_TAX_SIGNED' =>    0,
                    'AMOUNT_TOTAL_SIGNED' =>    $amount_total,
                    'AMOUNT_RESIDUAL_SIGNED' =>    0,
                    'AUTO_POST' =>    'false', //boolean
                    'INVOICE_USER_ID' => 2,
                    'INVOICE_SENT' => 'false', //boolean
                    'INVOICE_INCOTERM_ID' => 6,
                    'INVOICE_PARTNER_DISPLAY_NAME' => 'Created by: Administrator',
                    'TEAM_ID' => 1,
                    'DIVIDED_USD' => 0.000,
                    'TOTAL_USD' => $amount_total,
                    'TOTAL_AMOUNT_WITHOUT_MONETARY' => $amount_total,
                    // word_move	?
                    // word_move2	word of $amount_total
                    'CURRENCY_NAME_HERE' => 'PHP',
                    'GET_TOTAL_IN_DEB_CRED' => $amount_total,
                    // add_percent	?
                    // saving_forex_php_value	?
                    // adding_usd_with_percent_value	?
                    'FOREX_AND_AMM_VAL' => $amount_total,
                    'GETTING_TOTAL_OF_DEBIT_CREDIT_VAL' => $amount_total,
                    // deduct_value	?
                    // fetch_recheck_date	?
                    // amm_usd_val	?
                    // saving_forex_php_value_ap	?
                    // word_for_journal_entries_val	word of $amount_total
                    'IS_DEBIT_NOTE' => 'false', //boolean
                    'IS_MUI_CIP_TRANSACTION' => 'false', //boolean
                    'TRANSFER_STATUS' => 'pending',
                    'PAYABLE_VOUCHER_GENERATED' => 'false', //boolean
                    'JOURNAL_NAME' => $journal_name,
                    'ALREADY_GENERATED_COGS' => 'false' //boolean
                ];

                $new_am_id = $db_ken->insert_get_id('ACCOUNT_MOVE', $am_entries, 'id'); //← this is the newly inserted ID
                // $InsertAmEntries = $db->insert_get_id($am_entries, 'ACCOUNT_MOVE');
                // $new_am_id = $InsertAmEntries['id']; // ← this is the newly inserted ID
                // echo $is_wip;
                if ($is_wip == 't') {
                    $qUpdateAccrual = "UPDATE M_ACC_ACCRUAL SET wip_account_move_id =$1 WHERE ID = $2";
                } else {
                    // echo '1' . $wip_account_id;
                    // if ($wip_account_id) {
                    $qGetMosLine =  getMOS($month_id,  $accrual_id, '', '', false);
                    $resMosLine = $db_ken->fetchAll($qGetMosLine);
                    foreach ($resMosLine as $mosLine) {


                        $mo_line_entries = [
                            'DIST_MO_ID' =>    $mosLine['dist_mo_id'],
                            'ACCRUAL_ID' => $mosLine['accrual_id'],
                            'ACCRUAL_ALLOCATION' =>  $mosLine['allocation']
                        ];

                        $db_ken->insert('M_ACC_DIST_MO_LINES', $mo_line_entries);
                    }
                    // }

                    $qUpdateAccrual = "UPDATE M_ACC_ACCRUAL SET distributed_account_move_id =$1 WHERE ID = $2";
                }

                $db_ken->query(
                    $qUpdateAccrual,
                    [$new_am_id, $accrual_id]
                );
                // $db->query($qUpdateAccrual);




            }

            if ($new_am_id) { // IF IT HAS ACCOUNT_MOVE_ID


                $aml_entries = [
                    'NAME' => $aml_label,
                    'MOVE_ID' =>    $new_am_id,
                    'MOVE_NAME' => $ref,
                    'DATE' => $last_date_of_month,
                    'ref'    => $ref,
                    'PARENT_STATE' => 'draft',
                    'JOURNAL_ID' => $journal_id,
                    'COMPANY_ID' => 1,
                    'COMPANY_CURRENCY_ID' => 36,
                    'ACCOUNT_INTERNAL_TYPE' => 'other',
                    'ACCOUNT_ROOT_ID' => $aa_root_id,
                    'ACCOUNT_ID' => $account_id,
                    // sequence	?
                    'QUANTITY' =>    1,
                    'DISCOUNT' => 0.00,
                    'DEBIT' =>    $debit,
                    'CREDIT' =>    $credit,
                    'BALANCE' =>    $balance,
                    'RECONCILED' =>    'false', //boolean
                    'BLOCKED' => 'false', //boolean
                    'TAX_EXIGIBLE' => 'true', //boolean
                    'AMOUNT_RESIDUAL' => $amount_total,
                    'AMOUNT_RESIDUAL_CURRENCY' => 0.0,
                    'ANALYTIC_ACCOUNT_ID' => $analytic_acount_id ?: null,
                    'ASSET_MRR' => 0.00,
                    'DEBIT_DATA_PAYABLE' => $amount_total
                    // 'DEBIT_DATA' => $amount_total / $php_rate,
                    // bcdl_amount	?
                ];

                $new_aml_id = $db_ken->insert_get_id('ACCOUNT_MOVE_LINE', $aml_entries, 'id');
                // $InsertAmlEntries = $db->insert_get_id($aml_entries, 'ACCOUNT_MOVE_LINE');
                // $new_aml_id = $InsertAmlEntries['id'];
                if ($new_aml_id) { // if aml id has value


                    // $status = "pending";
                    $sbu_result = "";

                    switch ($analytic_account_code) {
                        case "8300":
                            $sbu_result = "tos";
                            break;

                        case "8310":
                            $sbu_result = "sot";
                            break;

                        case "8100":
                            $sbu_result = "hermetics";
                            break;
                        case "8110":
                            $sbu_result = "modules";
                            break;
                        case "8120":
                            $sbu_result = "die sales";
                            break;
                        default:
                            $sbu_result = "";
                    }


                    if ($sbu_result != "" && $wip_account_id) {


                        $qGetMos =  getMOS($month_id,  $accrual_id, $sbu_result, $is_wip, true);
                        $resMos = $db_ken->fetchAll($qGetMos);
                        foreach ($resMos as $mos) {


                            $mo_link_entries = [
                                'PRODUCTION_ID' =>    $mos['mo_id'],
                                'ACCOUNT_MOVE_LINE_ID' => $new_aml_id,
                                'PERCENT' =>  $mos['percentage'],
                                'VALUE' => $mos['allocation']
                            ];
                            // $db->insert($mo_link_entries, 'ACCOUNT_MOVE_LINE_MO_LINK');

                            $db_ken->insert('ACCOUNT_MOVE_LINE_MO_LINK', $mo_link_entries);
                        }
                    }
                }
            }

            $account_move_checker = $row['accrual_id'] . $is_wip;
        }

        $db_ken->commit();
        // echo "All accruals and lines inserted successfully.";
    } catch (Exception $e) {
        // ROLLBACK EVERYTHING on ANY error
        $db_ken->rollBack();
        echo "Transaction failed: " . $e->getMessage();
    }
}


function getMOS($month_id, $accrual_id, $sbu, $is_wip, $from_prev)
{

    // if ($is_wip == 't' &&  $from_prev == 1) {


    //     $q = "SELECT
    //      adm.mo_id,
    //      coalesce(adml.actual_allocation, adml.accrual_allocation) full_allocation,
    //      null::numeric percentage,
    //      maw.sbu,
    //      adm.mo,
    //      adm2.invoiced_qty,
    //      adm2.mo_done_qty,
    //      (adm2.invoiced_qty/adm2.mo_done_qty) * coalesce(adml.actual_allocation, adml.accrual_allocation) allocation
    //      from m_acc_accrual ma
    //      join m_acc_to_wip maw on maw.main_id= ma.id
    //      join m_acc_dist_mo adm on maw.mos like '%' || adm.mo || '%' and adm.date_Range_Id != ma.date_Range_id
    //      join m_acc_dist_mo adm2 on adm2.mo = adm.mo and adm2.date_range_id = ma.date_Range_id
    //      join m_acc_dist_mo_lines adml on adml.dist_mo_id =adm.id
    //      join m_acc_accrual ma2 on ma2.id = adml.accrual_id 
    //      join M_ACC_ACCRUAL_DIST mad2 on mad2.accrual_id = adml.accrual_id and upper(mad2.sbu) = upper(adm.sbu) and maw.account_id =mad2.wip_account_id
    //      where ma.date_Range_id = $month_id
    //      and ma.id = $accrual_id
    //      and maw.item_label != ''
    //      and lower(adm.sbu) = '$sbu'
    //       order by adm.mo";
    // } else {

    //     $q = "with not_tally as(select
    //     adm.id dist_mo_id,	
    //     adm.mo_id,
    // adm.mo,
    // adm.device,
    // adm.category,
    // adm.customer_name,
    // adm.earned_hrs,
    // aad.debit *
    // CASE WHEN aad.mo_pct_ref = 'EH' THEN adm.EH_percentage
    // ELSE QTY_PERCENTAGE END
    // allocation,
    // CASE WHEN aad.mo_pct_ref = 'EH' THEN adm.EH_percentage
    // ELSE QTY_PERCENTAGE END percentage,
    // aad.sbu,
    //                   adm.is_invoiced,
    // 			 adm.invoiced_qty,
    // 	adm.mo_done_qty
    // from 
    // m_acc_date_range adr
    // join M_ACC_ACCRUAL maa on maa.date_range_id = adr.ID
    // join (
    //     select  a.accrual_id, a.analytic_account, a.analytic_account_id, sum(a.distribution_percentage) distribution_percentage,a.sbu, sum(a.debit) debit,sum(a.credit) credit,
    //     MACT.mo_pct_ref
    //     from M_ACC_ACCRUAL_DIST a
    //     JOIN M_ACC_ACCRUAL a_main on a_main.id = a.accrual_id
    //     JOIN M_ACC_CATEGORY_ACCOUNTS   ACA ON ACA.ACCOUNT_ID = a.account_id and aca.Acc_category_id = a_main.dist_categ_id 
    //     JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.Acc_category_id
    //     WHERE A.ACCRUAL_ID = $accrual_id
    //     and a.WIP_ACCOUNT_ID IS NOT NULL
    //     group by  a.accrual_id, a.analytic_account, a.analytic_account_id,a.sbu,MACT.mo_pct_ref order by analytic_account_id
    //     ) aad on aad.accrual_id = maa.id	
    // join account_analytic_account aaa on aaa.id =aad.analytic_account_id
    // join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
    // join m_acc_dist_mo adm on adm.sbu =aad.sbu and adm.date_range_id = adr.id AND ADM.REMARKS !='INVOICED BUT NO MOVEMENT'
    // where adr.id = $month_id and 
    // maa.id = $accrual_id
    //     and adg.dept_group ='MANUFACTURING/PRODUCT LINE')
    // -- 	select * from m_acc_accrual
    // ,MO_RANKED AS (
    //     SELECT 
    //     nt.dist_mo_id,
    //     nt.mo_id,
    //     NT.MO,
    //     NT.DEVICE,
    //     NT.CATEGORY,
    //     NT.ALLOCATION,
    //     case when '$is_wip' = 't' then null::numeric else nt.percentage end percentage,
    //         NT.CUSTOMER_NAME,
    //         NT.earned_hrs,
    //     TRUNC(NT.ALLOCATION,5) TRUNC_ALLOCATION,
    //     SUM(NT.ALLOCATION) OVER() TOTAL_ALLOCATION,
    //     SUM(TRUNC(NT.ALLOCATION,5)) OVER() TOTAL_TRUNC_ALLOCATION,
    //     (
    //         NT.ALLOCATION- TRUNC(NT.ALLOCATION,5)
    //     ) ALLOCATION_DIFF,
    //     ((
    //         SUM(NT.ALLOCATION) OVER()- SUM(TRUNC(NT.ALLOCATION,5)) OVER()
    //     )/ 0.00001)::INTEGER ROWS_TO_ADJUST,
    //     ROW_NUMBER() OVER (ORDER BY NT.ALLOCATION - TRUNC(NT.ALLOCATION,5) DESC) AS rn,
    //     nt.sbu,
    //     nt.is_invoiced,
    // 	 nt.invoiced_qty,
    // 	nt.mo_done_qty
    //     FROM 
    //     NOT_TALLY NT
    //     ), final_data as(
    //         SELECT 
    //         $accrual_id accrual_id,
    //         dist_mo_id,
    //         mo_id,
    // MO,
    // DEVICE,
    // CATEGORY,
    // CUSTOMER_NAME,
    // EARNED_HRS,
    // invoiced_qty,
    // 		mo_done_qty,
    // 		 CASE 
    //             WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
    //             ELSE TRUNC_ALLOCATION
    //         END ALLOCATION,
    //         percentage,
    //         sbu,
    //         is_invoiced
    //     FROM
    //     MO_RANKED
    //     where lower(sbu) = case when '$sbu' = '' then lower(sbu) else lower('$sbu') end
    //     and is_invoiced = 
    //     case when '$is_wip' = 't' then true else is_invoiced end
    //     )	
    //     select 
    //     accrual_id,
    //     dist_mo_id,
    //     mo_id,
    //     MO,
    //     DEVICE,
    //     CATEGORY,
    //     CUSTOMER_NAME,
    //     EARNED_HRS,
    //     coalesce(round((invoiced_qty/mo_done_qty)* allocation,5),allocation) ALLOCATION,
    //     percentage,
    //     sbu,
    //     is_invoiced
    //     from 
    //     final_data
    // ";
    // }
    if ($is_wip == 't' &&  $from_prev == 1) {
        $addQuery = ", for_sum as (	
            select 
            --         accrual_id,
            --         dist_mo_id,
            mo_id,
            percentage,
            sbu,
            MO,
            --         DEVICE,
            --         CATEGORY,
            --         CUSTOMER_NAME,
            --         EARNED_HRS,
            coalesce(round((invoiced_qty/mo_done_qty)* allocation,5),allocation) ALLOCATION--,
            --         is_invoiced
            from 
            final_data
            union all
                SELECT
             adm.mo_id,
            --          coalesce(adml.actual_allocation, adml.accrual_allocation) full_allocation,
             null::numeric percentage,
             maw.sbu,
             adm.mo,
            --          adm2.invoiced_qty,
            --          adm2.mo_done_qty,
             (adm2.invoiced_qty/adm2.mo_done_qty) * coalesce(adml.actual_allocation, adml.accrual_allocation) allocation
             from m_acc_accrual ma
             join m_acc_to_wip maw on maw.main_id= ma.id
             join m_acc_dist_mo adm on maw.mos like '%' || adm.mo || '%' and adm.date_Range_Id != ma.date_Range_id
             join m_acc_dist_mo adm2 on adm2.mo = adm.mo and adm2.date_range_id = ma.date_Range_id
             join m_acc_dist_mo_lines adml on adml.dist_mo_id =adm.id
             join m_acc_accrual ma2 on ma2.id = adml.accrual_id 
             join M_ACC_ACCRUAL_DIST mad2 on mad2.accrual_id = adml.accrual_id and upper(mad2.sbu) = upper(adm.sbu) and maw.account_id =mad2.wip_account_id
             where ma.date_Range_id = $month_id
             and ma.id = $accrual_id
             and maw.item_label != ''
             and lower(adm.sbu) = '$sbu'
            )
            select 
            mo_id,
            percentage,
            sbu,
            mo,
            sum(allocation) allocation
            from
            for_sum
            group by mo_id,
            percentage,
            sbu,
            mo";
    } else {
        $addQuery = " select 
             accrual_id,
             dist_mo_id,
             mo_id,
             MO,
             DEVICE,
             CATEGORY,
             CUSTOMER_NAME,
             EARNED_HRS,
             --coalesce(round((invoiced_qty/mo_done_qty)* allocation,5),allocation) ALLOCATION,
             allocation,
             percentage,
             sbu,
             is_invoiced
             from 
             final_data";
    }


    $q = "with not_tally as(select
adm.id dist_mo_id,	
adm.mo_id,
adm.mo,
adm.device,
adm.category,
adm.customer_name,
adm.earned_hrs,
aad.debit *
CASE WHEN aad.mo_pct_ref = 'EH' THEN adm.EH_percentage
ELSE QTY_PERCENTAGE END
allocation,
CASE WHEN aad.mo_pct_ref = 'EH' THEN adm.EH_percentage
ELSE QTY_PERCENTAGE END percentage,
aad.sbu,
              adm.is_invoiced,
         adm.invoiced_qty,
adm.mo_done_qty
from 
m_acc_date_range adr
join M_ACC_ACCRUAL maa on maa.date_range_id = adr.ID
join (
select  a.accrual_id, a.analytic_account, a.analytic_account_id, sum(a.distribution_percentage) distribution_percentage,a.sbu, sum(a.debit) debit,sum(a.credit) credit,
MACT.mo_pct_ref
from M_ACC_ACCRUAL_DIST a
JOIN M_ACC_ACCRUAL a_main on a_main.id = a.accrual_id
JOIN M_ACC_CATEGORY_ACCOUNTS   ACA ON ACA.ACCOUNT_ID = a.account_id and aca.Acc_category_id = a_main.dist_categ_id 
JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.Acc_category_id
WHERE A.ACCRUAL_ID = $accrual_id
and a.WIP_ACCOUNT_ID IS NOT NULL
group by  a.accrual_id, a.analytic_account, a.analytic_account_id,a.sbu,MACT.mo_pct_ref order by analytic_account_id
) aad on aad.accrual_id = maa.id	
join account_analytic_account aaa on aaa.id =aad.analytic_account_id
join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
join m_acc_dist_mo adm on adm.sbu =aad.sbu and adm.date_range_id = adr.id AND ADM.REMARKS !='INVOICED BUT NO MOVEMENT'
where adr.id = $month_id and 
maa.id = $accrual_id
and adg.dept_group ='MANUFACTURING/PRODUCT LINE')
-- 	select * from m_acc_accrual
,MO_RANKED AS (
SELECT 
nt.dist_mo_id,
nt.mo_id,
NT.MO,
NT.DEVICE,
NT.CATEGORY,
NT.ALLOCATION,
case when '$is_wip' = 't' then null::numeric else nt.percentage end percentage,
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
nt.sbu,
nt.is_invoiced,
 nt.invoiced_qty,
nt.mo_done_qty
FROM 
NOT_TALLY NT
), final_data as(
    SELECT 
    $accrual_id accrual_id,
    dist_mo_id,
    mo_id,
MO,
DEVICE,
CATEGORY,
CUSTOMER_NAME,
EARNED_HRS,
invoiced_qty,
    mo_done_qty,
     CASE 
        WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
        ELSE TRUNC_ALLOCATION
    END ALLOCATION,
    percentage,
    sbu,
    is_invoiced
FROM
MO_RANKED
where lower(sbu) = case when '$sbu' = '' then lower(sbu) else lower('$sbu') end
and is_invoiced = 
case when '$is_wip' = 't' then true else is_invoiced end
)
$addQuery
";



    return $q;
}



echo json_encode('');
