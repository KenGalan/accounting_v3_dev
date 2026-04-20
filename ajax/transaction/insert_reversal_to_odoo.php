<?php

session_start();

$db = new Postgresql();
$db_ken = new PostgresqlKen();
$month_id = $_POST['month_id'];


$qWIP = "SELECT mo_id, sbu FROM
M_ACC_DIST_MO
WHERE DATE_RANGE_ID =$month_id
AND NOT IS_INVOICED
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
TO_CHAR(maa.date, 'MM/DD/YYYY') last_date_of_month,
TO_CHAR(mar.date, 'MM/DD/YYYY') date,
maa.journal_name journal,
maa.journal_id,
mar.account_code,
-- adji.item_label label,
mar.account_id,
mar.analytic_account_id,
mar.analytic_account,
COALESCE(mar.debit,0)  debit,
COALESCE(mar.credit,0) credit,
aa.root_id,
false is_wip,
coalesce(mar.wip_Account_id, NULL::INTEGER) wip_account_id
FROM
M_ACC_DATE_RANGE ADR
				join m_acc_accrual maa on maa.date_range_id = adr.id
				join M_ACC_REVERSAL mar on mar.accrual_id = maa.id
left join account_account aa on aa.id = mar.account_id
WHERE adr.id = $month_id
and maa.reverse_account_move_id is null
ORDER BY maa.id,COALESCE(mar.debit,0) DESC, mar.account_code
	)A
UNION ALL
select b.* from (
    select
    maa.id accrual_id,
'' reference ,
TO_CHAR(maa.date, 'MM/DD/YYYY') last_date_of_month,
TO_CHAR(atw.date, 'MM/DD/YYYY') date,
maa.journal_name journal,
maa.journal_id,
aa.code account_code,
aa.id account_id,
atw.analytic_account_id,
atw.analytic_account,
COALESCE(atw.debit,0) debit,
COALESCE(atw.credit,0) credit,
aa.root_id,
true is_wip,
atw.account_id wip_account_id
from m_acc_to_wip_reversal atw
join m_acc_accrual maa on maa.id = atw.accrual_id
join account_account aa on aa.id = atw.account_id
join M_ACC_DATE_RANGE ADR on ADR.id = maa.date_range_id
where adr.id =$month_id
ORDER BY maa.id,COALESCE(atw.debit,0) DESC, aa.code
    )b";


$result = $db_ken->fetchAll($q);
// $result2 = $result;

if ($result) {
    try {
        $db_ken->beginTransaction();
        $account_move_checker = '';
        foreach ($result as $row) {
            $last_date_of_month = $row['last_date_of_month'];
            $date = $row['date'];
            // $journal_id = $row['journal_id'];
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

            $wip_account_id = $row['wip_account_id'];


            if ($is_wip == 't') {
                $ref = '/sample Cogs Reversal';
                $journal_name = 'Miscellaneous Operations';
                $journal_id = 3;
            } else {
                $ref = '/sample Reversal';
                $journal_name = $row['journal'];
                $journal_id = $row['journal_id'];
            }
            $accrual_id = $row['accrual_id'];
            // echo $is_wip;
            // exit;
            // echo $balance;
            $account_move_checker_new = $row['accrual_id'] . $is_wip;
            if ($account_move_checker != $account_move_checker_new) {

                $am_entries = [
                    'NAME' =>    '/',
                    // 'DATE'    => $last_date_of_month,
                    'DATE' => $date,
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
                if ($is_wip == 't') {
                    $qUpdateAccrual = "UPDATE M_ACC_ACCRUAL SET wip_reverse_account_move_id =$1 WHERE ID = $2";
                } else {
                    $qUpdateAccrual = "UPDATE M_ACC_ACCRUAL SET reverse_account_move_id =$1 WHERE ID = $2";


                    // if ($wip_account_id) {
                    $qGetMosLine =  getMOS($month_id,  $accrual_id, '', '');
                    $resMosLine = $db_ken->fetchAll($qGetMosLine);
                    foreach ($resMosLine as $mosLine) {
                        // var_dump($mosLine);


                        $qUpdateMoLines = "UPDATE M_ACC_DIST_MO_LINES SET reversed_allocation =$1 , actual_allocation = case when $2 = 0 then accrual_allocation - $1 else accrual_allocation + $1 end WHERE ACCRUAL_ID = $3 AND DIST_MO_ID = $4";
                        $db_ken->query(
                            $qUpdateMoLines,
                            [$mosLine['allocation'], $mosLine['is_debit'], $mosLine['accrual_id'], $mosLine['dist_mo_id']]
                        );
                    }
                    // }
                }

                $db_ken->query(
                    $qUpdateAccrual,
                    [$new_am_id, $accrual_id]
                );
            }

            if ($new_am_id) { // IF IT HAS ACCOUNT_MOVE_ID


                $aml_entries = [
                    'MOVE_ID' =>    $new_am_id,
                    'MOVE_NAME' => '/',
                    // 'DATE' => $last_date_of_month,
                    'DATE' => $date,
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
                    'ANALYTIC_ACCOUNT_ID' => $analytic_acount_id ? $analytic_acount_id : null,
                    'ASSET_MRR' => 0.00,
                    'DEBIT_DATA_PAYABLE' => $amount_total
                    // 'DEBIT_DATA' => $amount_total / $php_rate,
                    // bcdl_amount	?
                ];

                $new_aml_id = $db_ken->insert_get_id('ACCOUNT_MOVE_LINE', $aml_entries, 'id');
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


                        $qGetMos =  getMOS($month_id,  $accrual_id, $sbu_result, $is_wip);
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
                    // if (!empty($allWIP[$sbu_result])) {
                    //     foreach ($allWIP[$sbu_result] as $value) {
                    //         // echo $value . "<br>";
                    //         $mo_id = $value;
                    //         $mo_link_entries = [
                    //             'PRODUCTION_ID' =>    $mo_id,
                    //             'ACCOUNT_MOVE_LINE_ID' => $new_aml_id
                    //         ];
                    //         // $db->insert($mo_link_entries, 'ACCOUNT_MOVE_LINE_MO_LINK');

                    //         $db_ken->insert('ACCOUNT_MOVE_LINE_MO_LINK', $mo_link_entries);
                    //     }
                    // }
                }
                // $InsertAmlEntries = $db->insert_get_id($aml_entries, 'ACCOUNT_MOVE_LINE');
                // $new_aml_id = $InsertAmlEntries['id'];
                // if ($InsertAmlEntries && $is_wip == 't') { // if aml id has value


                //     // $status = "pending";
                //     $sbu_result = "";

                //     switch ($analytic_account_code) {
                //         case "8300":
                //             $sbu_result = "tos";
                //             break;

                //         case "8310":
                //             $sbu_result = "sot";
                //             break;

                //         case "8100":
                //             $sbu_result = "hermetics";
                //             break;
                //         case "8110":
                //             $sbu_result = "modules";
                //             break;
                //         case "8120":
                //             $sbu_result = "die sales";
                //             break;
                //         default:
                //             $sbu_result = "";
                //     }

                //     // if (!empty($allWIP[$sbu_result])) {
                //     //     foreach ($allWIP[$sbu_result] as $value) {
                //     //         // echo $value . "<br>";
                //     //         $mo_id = $value;
                //     //         $mo_link_entries = [
                //     //             'PRODUCTION_ID' =>    $mo_id,
                //     //             'ACCOUNT_MOVE_LINE_ID' => $new_aml_id
                //     //         ];
                //     //         $db->insert($mo_link_entries, 'ACCOUNT_MOVE_LINE_MO_LINK');
                //     //     }
                //     // }
                // }
            }

            $account_move_checker = $row['accrual_id'] . $is_wip;
        }


        $qCount = "select count(id) count from m_acc_accrual maa where reverse_account_move_id is null";
        $qResCount = $db_ken->fetchRow($qCount);

        if ($qResCount && $qResCount['count'] == 0) {
            $qUpdateRangeStatus = "UPDATE M_ACC_DATE_RANGE SET is_all_reversed =$1 WHERE ID = $2";
            // }

            $db_ken->query(
                $qUpdateRangeStatus,
                ['true', $month_id]
            );
        }


        $db_ken->commit();
    } catch (Exception $e) {
        // ROLLBACK EVERYTHING on ANY error
        $db_ken->rollBack();
        echo "Transaction failed: " . $e->getMessage();
    }
}


function getMOS($month_id, $accrual_id, $sbu, $is_wip)
{
    $q = "with not_tally as(select
    adm.id dist_mo_id,	
    adm.mo_id,
adm.mo,
adm.device,
adm.category,
adm.customer_name,
adm.earned_hrs,
coalesce(aad.debit,aad.credit) *
CASE WHEN aad.mo_pct_ref = 'EH' THEN adm.EH_percentage
ELSE QTY_PERCENTAGE END
allocation,
CASE WHEN aad.mo_pct_ref = 'EH' THEN adm.EH_percentage
ELSE QTY_PERCENTAGE END percentage,
aad.sbu,
				  adm.is_invoiced,
				  case when aad.debit is not null then 1 else 0 end as is_debit
from 
m_acc_date_range adr
 --join m_acc_dist_journal_entries adje on adje.date_range_id =adr.id
 --join m_acc_dist_journal_items adji on adji.main_id = adje.id
join M_ACC_ACCRUAL maa on maa.date_range_id = adr.ID
join (
--	select  a.accrual_id, a.analytic_account, a.analytic_account_id, sum(a.distribution_percentage) distribution_percentage,a.sbu, sum(a.debit) debit,sum(a.credit) credit,
	--MACT.mo_pct_ref
	--from M_ACC_ACCRUAL_DIST a
	--JOIN ACCOUNT_ACCOUNT   AA ON AA.ID = a.account_id
   -- JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =AA.m_acc_category_id
	--group by  a.accrual_id, a.analytic_account, a.analytic_account_id,a.sbu,MACT.mo_pct_ref order by analytic_account_id
    select  a.accrual_id, a.analytic_account, a.analytic_account_id, sum(a.distribution_percentage) distribution_percentage,a.sbu, sum(a.debit) debit,sum(a.credit) credit,
    MACT.mo_pct_ref
    from M_ACC_REVERSAL a
    JOIN M_ACC_ACCRUAL a_main on a_main.id = a.accrual_id
    JOIN M_ACC_CATEGORY_ACCOUNTS   ACA ON ACA.ACCOUNT_ID = a.account_id and aca.Acc_category_id = a_main.dist_categ_id 
    JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.Acc_category_id
    WHERE A.ACCRUAL_ID = $accrual_id
    and a.WIP_ACCOUNT_ID IS NOT NULL
    group by  a.accrual_id, a.analytic_account, a.analytic_account_id,a.sbu,MACT.mo_pct_ref order by analytic_account_id
    ) aad on aad.accrual_id = maa.id	
join account_analytic_account aaa on aaa.id =aad.analytic_account_id
join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
join m_acc_dist_mo adm on adm.sbu =aad.sbu and adm.date_range_id = adr.id
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
    nt.percentage,
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
	nt.is_debit
	FROM 
	NOT_TALLY NT
	)
		SELECT 
        $accrual_id accrual_id,
        dist_mo_id,
        mo_id,
MO,
DEVICE,
CATEGORY,
CUSTOMER_NAME,
EARNED_HRS,
	 CASE 
            WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
            ELSE TRUNC_ALLOCATION
        END AS ALLOCATION,
        case when '$is_wip' = 't' then NULL::NUMERIC else percentage END percentage,
        sbu,
		is_invoiced,
		is_debit
	FROM
	MO_RANKED
    where lower(sbu) = case when '$sbu' = '' then lower(sbu) else lower('$sbu') end
    and is_invoiced = 
    case when '$is_wip' = 't' then true else is_invoiced end
";
    return $q;
}


echo json_encode('');
