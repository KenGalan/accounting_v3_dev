<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Postgresql();

$query_category = "
	SELECT 
  --DISTINCT
		SUM(b.distribution_percentage) AS distribution_percentage,
        a.id,
        a.acc_category,
        a.added_on,
        a.added_by,
        a.changed_on,
        a.changed_by,
        a.active,
        a.journal_id,
        aj.name AS journal_acc,
		COUNT(aa.id) AS account_count
    FROM M_ACC_CATEGORY_TBL a
	-- LEFT JOIN M_ACC_COST_DISTRIBUTION b ON b.m_acc_category_id = a.id
	-- LEFT JOIN account_account aa ON aa.m_acc_category_id = a.id
  -- LEFT JOIN ACCOUNT_JOURNAL aj ON a.journal_id = aj.id
  LEFT JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACC_CATEGORY_ID = a.ID
  left join m_acc_cost_distribution b on b.m_acc_category_id =ACA.ACC_CATEGORY_ID AND b.DEBIT_TO = ACA.ACCOUNT_ID
left join account_account aa on aa.id = aca.account_id
LEFT JOIN ACCOUNT_JOURNAL aj ON a.journal_id = aj.id
  WHERE a.active = 'true'
  AND aj.name is not null
	GROUP BY a.id, 
		a.acc_category,
		--aa.name,
    aj.name
    ORDER BY a.id ASC
";

$result_category = $db->fetchAll($query_category);

echo json_encode($result_category);
