<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();

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
    select maa.total_accrual_value - sum(debit) total_diff,maa.* from account_move am
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
        case when sign(ra.total_credit) = -1 then abs(ra.total_credit) else 0::numeric end credit,
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
        ORDER BY accrual_id";
    // echo $q;
    // exit;

    $result = $db->fetchAll($q);
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
            $account_move_date = $item['date'];
            $sbu = $item['sbu'];
            $actual_apv_id = $item['actual_apv_id'] != '' &&  $item['actual_apv_id'] ?  $item['actual_apv_id'] : 'NULL::INTEGER';
            $journal = $item['journal'];
            $journal_id = $item['journal_id'] != '' && $item['journal_id'] ? $item['journal_id'] : 'NULL::INTEGER';
            $date = $item['date'];
            // echo $credit;
            // exit;

            if ($accrual_id != (isset($old_accrual_id) ? $old_accrual_id : 0)) { // ← if this is not the same journal_entry

                // if ((isset($old_accrual_id) ? $old_accrual_id : 0) != 0) {

                $updateAccrualQ = "UPDATE M_ACC_ACCRUAL SET TOTAL_REVERSE_VALUE = ABS($total_credit), ACTUAL_APV_ID = $actual_apv_id, is_reversed =true WHERE ID =$accrual_id ";
                $db->query($updateAccrualQ);
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
                'SBU' => "'$sbu'"
                // 'WIP_ACCOUNT_ID' => $wip_account_id
            ];
            $resultLineItems = $db->insert($dataLineItems, 'M_ACC_REVERSAL');



            $old_accrual_id = $item["accrual_id"];
        }
    }
}



$last_q = "select * from M_ACC_REVERSAL";
$last_res = $db->fetchAll($last_q);
echo json_encode($last_res);
exit;
