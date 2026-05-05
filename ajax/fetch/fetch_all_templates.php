<?php
$db = new PostgresqlKen();
header('Content-Type: application/json');
// $qTemplateDetails = "with main as (SELECT 
// act.id act_id,
// act.acc_category,
// coalesce(sum(acd.distribution_percentage),0) acc_categ_percentage,
// --ACD.distribution_percentage,
// act.journal_id,
// string_agg(DISTINCT aa2.code || ' ' || aa2.name, ', ') AS debit_to
// FROM m_acc_category_tbl act
// LEFT JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACC_CATEGORY_ID = ACT.ID
// left join m_acc_cost_distribution acd on acd.m_acc_category_id =ACA.ACC_CATEGORY_ID AND ACD.DEBIT_TO = ACA.ACCOUNT_ID
// -- 	LEFT JOIN M_ACC_COST_DISTRIBUTION acd ON acd.m_acc_category_id = ma.dist_categ_id
// LEFT JOIN account_account aa2 ON aa2.id = acd.debit_to
// group by  act.id, act.acc_category	)
// select
// maa.id active_accruals,
// maa2.id ap_dist,
// main.*
// from
// main
// left join m_acc_accrual maa on maa.dist_categ_id = main.act_id and maa.is_accrual
// left join m_acc_accrual maa2 on maa2.dist_categ_id = main.act_id and not maa2.is_accrual
// order by maa.id nulls first, maa2.id nulls first
//  ";

$qTemplateDetails = "WITH 
accrual as (
select *,
	case when  distributed_account_move_id is not null and not is_reversed then 1 else 0 end reverse_count
	from m_acc_accrual
),
main AS (
    SELECT 
        act.id AS act_id,
        act.acc_category,
        COALESCE(SUM(acd.distribution_percentage), 0) AS acc_categ_percentage,
        act.journal_id,
        STRING_AGG(
            DISTINCT aa2.code || ' ' || aa2.name,
            ', '
        ) AS debit_to,

        STRING_AGG(
            DISTINCT aaa.name,
            ', '
        ) AS analytic_account_name,
        '' AS added_by,
        '' AS changed_on,
        '' AS changed_by
    FROM m_acc_category_tbl act
    LEFT JOIN m_acc_category_accounts aca 
        ON aca.acc_category_id = act.id
    LEFT JOIN m_acc_cost_distribution acd 
        ON acd.m_acc_category_id = aca.acc_category_id 
        AND acd.debit_to = aca.account_id
    LEFT JOIN account_account aa2 
        ON aa2.id = acd.debit_to
    LEFT JOIN account_analytic_account aaa 
        ON aaa.id = acd.analytic_account_id
    GROUP BY 
        act.id,
        act.acc_category,
        act.journal_id
)
SELECT
    count(maa.id) AS active_accruals,
    count(maa2.id) AS ap_dist,
	coalesce(sum(maa.reverse_count),0) pending_reversal,
    main.act_id,
        main.acc_category,
        
		CASE 
    WHEN main.acc_categ_percentage % 1 = 0 
        THEN main.acc_categ_percentage::int::text
    ELSE 
        TO_CHAR(main.acc_categ_percentage, 'FM999999990.00')
END AS acc_categ_percentage,
        main.journal_id,
       main.debit_to,
       main.analytic_account_name,
        main.added_by,
        main.changed_on,
        main.changed_by
FROM main
LEFT JOIN accrual maa 
    ON maa.dist_categ_id = main.act_id 
    AND maa.is_accrual
LEFT JOIN m_acc_accrual maa2 
    ON maa2.dist_categ_id = main.act_id 
    AND NOT maa2.is_accrual
	group by
--	case when  maa.distributed_account_move_id is not null and not maa.is_reversed then 1 else 0 end,
	  main.act_id,
        main.acc_category,
        CASE 
    WHEN main.acc_categ_percentage % 1 = 0 
        THEN main.acc_categ_percentage::int::text
    ELSE 
        TO_CHAR(main.acc_categ_percentage, 'FM999999990.00')
END,
        main.journal_id,
       main.debit_to,
       main.analytic_account_name,
        main.added_by,
        main.changed_on,
        main.changed_by
        order by active_accruals, ap_dist
        ";

$result = $db->fetchAll($qTemplateDetails);

$inc_setup = 0;
$active_acc = 0;
$pending_rev = 0;
$unprocessed_temp = 0;



foreach ($result as $row) {
    if ((intval($row['acc_categ_percentage']) <  100)) {
        $inc_setup++;
    }


    if ($row['active_accruals'] == 0 && $row['ap_dist'] == 0) {
        $unprocessed_temp++;
    } else {
        $active_acc += $row['active_accruals'];
    }

    if ($row['pending_reversal']) {
        $pending_rev += $row['pending_reversal'];
    }
}


$data = [
    'temp_details' => $result,
    'inc_setup' => $inc_setup,
    'unprocessed_temp' => $unprocessed_temp,
    'active_acc' => $active_acc,
    'pending_rev' => $pending_rev
];


// $data = [];
// while ($row = pg_fetch_assoc($res)) {
//     $data[] = $row;
// }

echo json_encode($data);

// echo json_encode($result);
