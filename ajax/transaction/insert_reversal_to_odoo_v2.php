<?php

session_start();

$db = new Postgresql();
$db_ken = new PostgresqlKen();
$month_id = $_POST['month_id'];




$q = "select a.* from (
    select 
	maa.id,
	'reversed '|| am.name reference,
	am.date last_date_of_month,
	am.journal_name,
	am.journal_id,
	aml.account_id,
	aml.analytic_account_id,
	aml.debit credit,
	aml.credit debit,
	aml.account_root_id root_id,
	maa.distributed_account_move_id account_move_id, false is_wip ,
	aml.id old_aml_id
	from m_acc_accrual maa
	join account_move am on am.id =maa.distributed_account_move_id
	join account_move_line aml on aml.move_id = am.id
	where
	maa.date_range_id =$month_id
	and maa.reverse_account_move_id is null
	union all
	select 
	maa.id,
	'reversed '|| am.name reference,
	am.date last_date_of_month,
	am.journal_name,
	am.journal_id,
	aml.account_id,
	aml.analytic_account_id,
	aml.debit credit,
	aml.credit debit,
	aml.account_root_id root_id,
	wip_account_move_id account_move_id, true is_wip,
		aml.id old_aml_id
	from m_acc_accrual maa
	join account_move am on am.id =maa.wip_account_move_id
	join account_move_line aml on aml.move_id = am.id
	where 
	maa.date_range_id =$month_id
	and maa.reverse_account_move_id is null
ORDER BY maa.id,COALESCE(aml.debit,0) DESC	)A";
$result = $db_ken->fetchAll($q);
// $result2 = $result;

if ($result) {
    try {
        $db_ken->beginTransaction();
        $account_move_checker = '';
        foreach ($result as $row) {
            $last_date_of_month = $row['last_date_of_month'];
            $ref = $row['reference'];
            $journal_id = $row['journal_id'];
            $amount_total = $row['credit'];
            $journal_name = $row['journal'];
            $aa_root_id = $row['root_id'];
            $debit = $row['debit'];
            $credit = $row['credit'];
            $balance =  $row['debit'] && $row['debit'] > 0 ?   $row['debit'] :  $row['credit'] * -1;
            $analytic_acount_id = $row['analytic_account_id'];
            $account_id = $row['account_id'];
            // $analytic_account_code = explode(' ', trim($row['analytic_account']))[0];
            $is_wip =  $row['is_wip'];
            $accrual_id = $row['accrual_id'];
            $old_aml_id = $row['old_aml_id'];
            // echo $is_wip;
            // exit;
            // echo $balance;
            $account_move_checker_new = $row['accrual_id'] . $is_wip;
            if ($account_move_checker != $account_move_checker_new) {

                $am_entries = [
                    'NAME' =>    '/sample',
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
                if ($is_wip == 't') {
                    $qUpdateAccrual = "UPDATE M_ACC_ACCRUAL SET wip_reverse_account_move_id =$1 WHERE ID = $2";
                } else {
                    $qUpdateAccrual = "UPDATE M_ACC_ACCRUAL SET reverse_account_move_id =$1 WHERE ID = $2";
                }

                $db_ken->query(
                    $qUpdateAccrual,
                    [$new_am_id, $accrual_id]
                );
            }

            if ($new_am_id) { // IF IT HAS ACCOUNT_MOVE_ID


                $aml_entries = [
                    'MOVE_ID' =>    $new_am_id,
                    'MOVE_NAME' => '/sample',
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
                    'ANALYTIC_ACCOUNT_ID' => $analytic_acount_id ? $analytic_acount_id : null,
                    'ASSET_MRR' => 0.00,
                    'DEBIT_DATA_PAYABLE' => $amount_total
                    // 'DEBIT_DATA' => $amount_total / $php_rate,
                    // bcdl_amount	?
                ];

                $new_aml_id = $db_ken->insert_get_id('ACCOUNT_MOVE_LINE', $aml_entries, 'id');
                if ($new_aml_id && $is_wip == 't') { // if aml id has value


                    $qLinkedMo = "select production_id from ACCOUNT_MOVE_LINE_MO_LINK where account_move_line_id =$old_aml_id";
                    $resLinkedMo = $db_ken->fetchAll($qLinkedMo);


                    if (!empty($resLinkedMo)) {
                        foreach ($resLinkedMo as $value) {
                            // echo $value . "<br>";
                            $mo_id = $value['production_id'];
                            $mo_link_entries = [
                                'PRODUCTION_ID' =>    $mo_id,
                                'ACCOUNT_MOVE_LINE_ID' => $new_aml_id
                            ];
                            // $db->insert($mo_link_entries, 'ACCOUNT_MOVE_LINE_MO_LINK');

                            $db_ken->insert('ACCOUNT_MOVE_LINE_MO_LINK', $mo_link_entries);
                        }
                    }
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




echo json_encode('');
