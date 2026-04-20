<?php
ob_start();
header('Content-Type: application/json');

$db = new Postgresql();
// $conn = $db->getConnection();


$q = "SELECT ADR.*, a.odoo_inserted, a.reversed, a.rev_odoo_inserted FROM
M_ACC_DATE_RANGE ADR
JOIN
(
    select 
    MIN(to_date(year_month, 'YYYY-MM')) MIN_DATE,
    max(odoo_inserted) odoo_inserted,
	min(reversed) reversed,
	min(rev_odoo_inserted) rev_odoo_inserted
    from
    m_acc_date_range adr
     left join (
        select *
		 ,case when distributed_account_move_id is not null then 'True' else 'False' end as odoo_inserted
 		  ,case when actual_apv_id is not null then 'True' else 'False' end as reversed
		 ,case when reverse_account_move_id is not null then 'True' else 'False' end as rev_odoo_inserted
        from m_acc_accrual 
        where not is_reversed
   
        ) nr on nr.date_range_id = adr.id
	where not adr.is_all_reversed and adr.active
) A ON TO_CHAR(A.MIN_DATE,'YYYY-MM') = ADR.YEAR_MONTH";

$resulta = $db->fetchRow($q);
// var_dump($resulta);
// exit; 
$query = "
WITH acdr AS (

SELECT ADR.* FROM
M_ACC_DATE_RANGE ADR
JOIN
(
    select 
-- 	adr.id,
    MIN(to_date(year_month, 'YYYY-MM')) MIN_DATE
    from
    m_acc_date_range adr
     left join (
        select  date_range_id ,is_reversed insert_odoo_counts
        from m_acc_accrual 
        where not is_reversed
     --   group by date_range_id
        ) nr on nr.date_range_id = adr.id
	where adr.active and not adr.is_all_reversed
-- 	group by adr.id
    --where  insert_odoo_counts = 0
) A ON TO_CHAR(A.MIN_DATE,'YYYY-MM') = ADR.YEAR_MONTH
)
SELECT
    ma.id,
    acdr.is_dept_distributed,
    aa.code || ' ' || aa.name AS credit_to,
    aa.id AS credit_to_id,
    ma.total_accrual_value,
    mct.acc_category AS distribution_category,
    mct.id AS category_id,
    ma.date_range_id,
    string_agg(DISTINCT aa2.code || ' ' || aa2.name, ', ') AS debit_to,
	am.name apv,
	am.id apv_id
FROM m_acc_accrual ma
JOIN account_account aa ON aa.id = ma.credit_to
left JOIN m_acc_category_tbl mct ON mct.id = ma.dist_categ_id
JOIN acdr ON acdr.id = ma.date_range_id
LEFT JOIN M_ACC_COST_DISTRIBUTION acd ON acd.m_acc_category_id = ma.dist_categ_id
LEFT JOIN account_account aa2 ON aa2.id = acd.debit_to
left join account_move am on am.id = ma.actual_apv_id
WHERE ma.active
--and ma.distributed_account_move_id is not null
GROUP BY
    ma.id,
    mct.id,
    aa.id,
    aa.code,
    aa.name,
    ma.total_accrual_value,
    mct.acc_category,
	am.name,
	am.id,
    ma.date_range_id,
    acdr.is_dept_distributed
";

$res = $db->fetchAll($query);



$queryReversal = "
WITH acdr AS (
    SELECT ADR.* FROM
	M_ACC_DATE_RANGE ADR
	JOIN
	(
		select 
		MIN(to_date(year_month, 'YYYY-MM')) MIN_DATE
		from
		m_acc_date_range adr
		left join (
			select distinct date_range_id 
			from m_acc_accrual 
			where not is_reversed
			) nr on nr.date_range_id = adr.id
            where not adr.is_all_reversed
	) A ON TO_CHAR(A.MIN_DATE,'YYYY-MM') = ADR.YEAR_MONTH
    where not adr.is_all_reversed
)
SELECT
    ma.id,
    acdr.is_dept_distributed,
    aa.code || ' ' || aa.name AS credit_to,
    aa.id AS credit_to_id,
    ma.total_accrual_value,
    mct.acc_category AS distribution_category,
    mct.id AS category_id,
    ma.date_range_id,
    string_agg(DISTINCT aa2.code || ' ' || aa2.name, ', ') AS debit_to,
	am.name apv,
	am.id apv_id
FROM m_acc_accrual ma
JOIN account_account aa ON aa.id = ma.credit_to
JOIN m_acc_category_tbl mct ON mct.id = ma.dist_categ_id
JOIN acdr ON acdr.id = ma.date_range_id
LEFT JOIN M_ACC_COST_DISTRIBUTION acd ON acd.m_acc_category_id = ma.dist_categ_id
LEFT JOIN account_account aa2 ON aa2.id = acd.debit_to
left join account_move am on am.id = ma.actual_apv_id
WHERE ma.active
and ma.distributed_account_move_id is not null
GROUP BY
    ma.id,
    mct.id,
    aa.id,
    aa.code,
    aa.name,
    ma.total_accrual_value,
    mct.acc_category,
	am.name,
	am.id,
    ma.date_range_id,
    acdr.is_dept_distributed
";
$resReversal = $db->fetchAll($queryReversal);

$data = [
    'date_range' => $resulta,
    'active_accrual' => $res,
    'reversal_accrual' => $resReversal
];


// $data = [];
// while ($row = pg_fetch_assoc($res)) {
//     $data[] = $row;
// }

echo json_encode($data);
ob_end_flush();
