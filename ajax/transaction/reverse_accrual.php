<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$db_ken = new PostgresqlKen();

// header('Content-Type: application/json');
$user = $_SESSION['ppc']['emp_no'];
if (!isset($_SESSION['ppc']['emp_no'])) : $user = 0;
    echo json_encode($data);
    exit;
endif; //NOT ISSET SESSION

$input = file_get_contents("php://input");


// Decode JSON to associative array
$data = json_decode($input, true);

// Make sure acc_data exists
// $acc_data = $data['acc_data'] ? $data['acc_data']  : [];
// echo $data;
// exit;
foreach ($data  as $row) {
    // var_dump($data);
    // exit;
    $accrual_id = intval($row['accrual_id']);
    $apv_id     = intval($row['apv_id']);


    $q = "with maa as(
    select maa.total_accrual_value - sum(debit) total_diff,maa.*,am.date actual_apv_date from account_move am
    join  account_move_line aml on aml.move_id = am.id
    join m_acc_accrual maa on maa.credit_to = aml.account_id
    where am.id = $apv_id and maa.id =$accrual_id
    group by
    maa.total_accrual_value, maa.id,am.date
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
            MAAD.WIP_ACCOUNT_ID wip_account,
            maa.journal_id,
            maa.actual_apv_date
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
                    ra.journal_id,
                    ra.actual_apv_date
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
            total_credit,
            actual_apv_date
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
            fem.total_credit,
            fem.actual_apv_date
        from
        final_DEPT_DIST fem
        union all
        select
        distinct
        ra.accrual_id,
        0::numeric distribution_percentage,
        case when sign(ra.total_credit) = -1 then 0::numeric else ra.total_credit end debit,
        case when sign(ra.total_credit) = -1 then abs(ra.total_credit) else 0::numeric end credit,
        '' dept_group,
       '' dept,
            null::integer analytic_account_id,
             ra.debit_to account_id,
             null::integer wip_account,
             ra.journal_id,
             ra.total_credit,
             ra.actual_apv_date
        from
        reversed_accrual ra
            )
        select 
        je.id accrual_id,
        aj.name journal,
         dcem.journal_id,
        AA.CODE ACCOUNT_CODE,
        DCEM.account_id,
        dcem.actual_apv_date,
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
        ORDER BY accrual_id";
    // echo $q;
    // exit;

    $result = $db_ken->fetchAll($q);
    // echo '<pre>';
    // echo json_encode($result);
    // exit;

    if ($result) {

        // $db->query("UPDATE M_ACC_DATE_RANGE SET is_dept_distributed = TRUE WHERE ID = $month_id");

        $old_accrual_id = '';


        //USE INSERT GET ID
        foreach ($result as $item) {
            $total_credit = $item['total_credit'];
            $accrual_id = $item['accrual_id'];
            $account_code = $item['account_code'];
            $account_id = $item['account_id'];
            $analytic_account = $item['analytic_account'];
            $analytic_account_id = $item['analytic_account_id'] != '' && $item['analytic_account_id'] ? $item['analytic_account_id']  : 'NULL::INTEGER';
            $distribution_percentage = $item['distribution_percentage'] != '' && $item['distribution_percentage']  ? $item['distribution_percentage'] : 'NULL::NUMERIC';
            $debit = $item['debit'] != '' && $item['debit']  ? $item['debit'] : 'NULL::NUMERIC';
            $credit = $item['credit'] != '' && $item['credit']  ? $item['credit'] : 'NULL::NUMERIC';
            // $account_move_date = $item['date'];
            $sbu = $item['sbu'];
            $wip_account_id = $item['wip_account_id'] ?: 'NULL::INTEGER';
            $actual_apv_id = $item['actual_apv_id'] != '' &&  $item['actual_apv_id'] ?  $item['actual_apv_id'] : 'NULL::INTEGER';
            $journal = $item['journal'];
            $journal_id = $item['journal_id'] != '' && $item['journal_id'] ? $item['journal_id'] : 'NULL::INTEGER';
            $date = $item['actual_apv_date'];
            // echo $credit;
            // exit;

            if ($accrual_id != (isset($old_accrual_id) ? $old_accrual_id : 0)) { // ← if this is not the same journal_entry

                // if ((isset($old_accrual_id) ? $old_accrual_id : 0) != 0) {

                $updateAccrualQ = "UPDATE M_ACC_ACCRUAL SET TOTAL_REVERSE_VALUE = ABS($total_credit), ACTUAL_APV_ID = $actual_apv_id, is_reversed =true WHERE ID =$accrual_id ";
                $db->query($updateAccrualQ);

                if ($old_accrual_id != 0) {
                    // INSERT TO WIP
                    $qToWipRev = insertToWipReversal($old_accrual_id);
                    $resultToWipRev = $db_ken->fetchAll($qToWip);

                    foreach ($resultToWipRev as $itemToWipRev) {
                        $db_ken->insert('M_ACC_TO_WIP_REVERSAL', [
                            'accrual_id' => $old_accrual_id,
                            'ACCOUNT_CODE' => $itemToWip['account_code'],
                            'ACCOUNT_ID' => $itemToWip['account_id'],
                            'CREDIT_ACCOUNT_ID' => $itemToWip['credit_account_id'],
                            'ANALYTIC_ACCOUNT' => $itemToWip['analytic_account'],
                            'ANALYTIC_ACCOUNT_ID' => $itemToWip['analytic_account_id'] ?: null,
                            'MOS' => $itemToWip['mos'],
                            'DEBIT' => $itemToWip['debit'] ?: null,
                            'CREDIT' => $itemToWip['credit'] ?: null,
                            'ADDED_BY' => $user,
                            'SBU' => $itemToWip['sbu'],
                            'DATE' => "'$date'"
                        ]);
                    }
                }
                // } else {
                //     $updateAccrualQ = "UPDATE M_ACC_ACCRUAL SET TOTAL_REVERSE_VALUE = ABS($total_credit), ACTUAL_APV_ID = $actual_apv_id WHERE ID =$accrual_id ";
                //     $db->query($updateAccrualQ);
                // }
            }

            $dataLineItems = [
                'ACCRUAL_ID' => $accrual_id,
                'ACCOUNT_CODE' => "'$account_code'",
                'ACCOUNT_ID' => $account_id,
                'ANALYTIC_ACCOUNT' => "'$analytic_account'",
                'ANALYTIC_ACCOUNT_ID' => $analytic_account_id,
                'DISTRIBUTION_PERCENTAGE' => $distribution_percentage,
                'DEBIT' => $debit,
                'CREDIT' => $credit,
                'ADDED_BY' => $user,
                'SBU' => "'$sbu'",
                'WIP_ACCOUNT_ID' => $wip_account_id,
                'DATE' => "'$date'"
            ];
            $resultLineItems = $db->insert($dataLineItems, 'M_ACC_REVERSAL');



            $old_accrual_id = $item["accrual_id"];
        }
        // INSERT TO WIP
        $qToWipLastRecord = insertToWipReversal($accrual_id);
        $resultLastToWip = $db_ken->fetchAll($qToWipLastRecord);

        foreach ($resultLastToWip as $itemToWip) {
            $db_ken->insert('M_ACC_TO_WIP_REVERSAL', [
                'ACCRUAL_ID' => $accrual_id,
                'ACCOUNT_CODE' => $itemToWip['account_code'],
                'ACCOUNT_ID' => $itemToWip['account_id'],
                'CREDIT_ACCOUNT_ID' => $itemToWip['credit_account_id'],
                'ANALYTIC_ACCOUNT' => $itemToWip['analytic_account'],
                'ANALYTIC_ACCOUNT_ID' => $itemToWip['analytic_account_id'] ?: null,
                'MOS' => $itemToWip['mos'],
                'DEBIT' => $itemToWip['debit'] ?: null,
                'CREDIT' => $itemToWip['credit'] ?: null,
                'ADDED_BY' => $user,
                'SBU' => $itemToWip['sbu'],
                'DATE' => $itemToWip['actual_apv_date']
            ]);
        }
    }
}


function insertToWipReversal($accrual_id)
{
    $qToWipReversal = "with not_tally as(
        select
        maa.id accrual_id,
    adm.mo,
    adm.device,
    adm.category,
    adm.customer_name,
    adm.earned_hrs,
    coalesce(aad.debit,aad.credit) *
    CASE WHEN MACT.mo_pct_ref = 'EH' THEN adm.EH_percentage
    ELSE QTY_PERCENTAGE END allocation,
    aad.sbu,
        adm.is_invoiced,
        aad.ACCOUNT_CODE,
        aad.ACCOUNT_ID,
      aad.ANALYTIC_ACCOUNT,
        aad.ANALYTIC_ACCOUNT_ID,
        aad.wip_account_id,
	case when aad.debit is not null then 1 else 0 end is_debit,
    aad.date actual_apv_date
    from 
    m_acc_date_range adr
        join M_ACC_ACCRUAL maa on maa.date_range_id = adr.ID
        join M_ACC_REVERSAL aad on aad.accrual_id = maa.id
    join account_analytic_account aaa on aaa.id =aad.analytic_account_id
    join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
   -- join m_acc_dist_mo adm on adm.sbu =aad.sbu
  --  JOIN ACCOUNT_ACCOUNT   AA ON AA.ID = aad.ACCOUNT_ID
  -- JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =AA.m_acc_category_id
    join m_acc_dist_mo adm on adm.sbu =aad.sbu and adr.id = adm.date_range_id
		JOIN M_ACC_CATEGORY_ACCOUNTS   ACA ON ACA.ACCOUNT_ID = aad.ACCOUNT_ID and aca.Acc_category_id = maa.dist_categ_id 
		JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.Acc_category_id
    where  
    maa.id in ($accrual_id) and adg.dept_group ='MANUFACTURING/PRODUCT LINE' and aad.wip_account_id is not null
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
        nt.wip_account_id,
		nt.is_debit,
        nt.actual_apv_date
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
        WIP_aCCOUNT_ID,
		is_debit,
        actual_apv_date
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
        AA.CODE ACCOUNT_CODE,
								 aadd.is_debit,
                                 aadd.actual_apv_date
    from
    allocation_adjusted aadd
    LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AADD.WIP_ACCOUNT_ID
    where  is_invoiced
    group by accrual_id,sbu,is_invoiced, total_allocation,
        ANALYTIC_ACCOUNT,
        ANALYTIC_ACCOUNT_ID,
          ACCOUNT_CODE,
        ACCOUNT_ID,
        AADD.WIP_ACCOUNT_ID,
        AA.CODE,
        AADD.WIP_ACCOUNT_ID,
								 aadd.is_debit,
                                 aadd.actual_apv_date
    order by accrual_id,sbu,is_invoiced
    )
    SELECT 
    FD.ACCOUNT_CODE,
    FD.ACCOUNT_ID,
    FD.CREDIT_ACCOUNT_ID,
    REPLACE(FD.ANALYTIC_ACCOUNT, '''', '''''') ANALYTIC_ACCOUNT,
    FD.ANALYTIC_ACCOUNT_ID,
    FD.MOS,
    case when fd.is_debit = 1 then FD.ALLOCATION else 0 end DEBIT,
    case when fd.is_debit = 1 then 0 else FD.ALLOCATION end CREDIT,
    FD.SBU,
	fd.is_debit,
    fd.actual_apv_date
    FROM FINAL_DATA FD
    UNION ALL
    SELECT 
    FD2.CREDIT_ACCOUNT_CODE ACCOUNT_CODE,
    FD2.CREDIT_ACCOUNT_ID ACCOUNT_ID,
    FD2.CREDIT_ACCOUNT_ID,
    NULL ANALYTIC_ACCOUNT,
    NULL ANALYTIC_ACCOUNT_ID,
    NULL MOS,
    case when fd2.is_debit = 1 then 0 else SUM(FD2.ALLOCATION) end DEBIT,
    case when fd2.is_debit = 1 then SUM(FD2.ALLOCATION) else 0 end CREDIT,
    '' SBU,
	fd2.is_debit,
    fd2.actual_apv_date
    FROM FINAL_DATA FD2
    GROUP BY
    FD2.CREDIT_ACCOUNT_CODE,
    FD2.CREDIT_ACCOUNT_ID,
	fd2.is_debit,
    fd2.actual_apv_date
    ";
    return $qToWipReversal;
}




$last_q = "select * from M_ACC_REVERSAL";
$last_res = $db_ken->fetchAll($last_q);
echo json_encode($last_res);
exit;
