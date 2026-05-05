<?php
$db = new PostgresqlKen();
header('Content-Type: application/json');
$accountQuery = " SELECT 
act.id act_id,
act.acc_category,
coalesce(sum(acd.distribution_percentage),0) acc_categ_percentage,
--ACD.distribution_percentage,
act.journal_id,
string_agg(DISTINCT aa2.code || ' ' || aa2.name, ', ') AS debit_to
FROM m_acc_category_tbl act
LEFT JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACC_CATEGORY_ID = ACT.ID
left join m_acc_cost_distribution acd on acd.m_acc_category_id =ACA.ACC_CATEGORY_ID AND ACD.DEBIT_TO = ACA.ACCOUNT_ID
-- 	LEFT JOIN M_ACC_COST_DISTRIBUTION acd ON acd.m_acc_category_id = ma.dist_categ_id
LEFT JOIN account_account aa2 ON aa2.id = acd.debit_to
group by  act.id, act.acc_category			
 ";
$accounts = $db->fetchAll($accountQuery);

echo json_encode($accounts);
