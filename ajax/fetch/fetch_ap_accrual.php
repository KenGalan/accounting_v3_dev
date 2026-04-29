<?php
ob_start();
header('Content-Type: application/json');

$db = new Postgresql();
// $conn = $db->getConnection();

$year_month = isset($_POST['year_month']) ? $_POST['year_month'] : '';
// echo $year_month;
// exit;
// if ($year_month != '') {
$q = "select distinct  mam.id, mam.year_month,
case when maa.id is not null then
mam.is_ap_distributed else false end is_ap_distributed
,mam.active, maa.active acc_active,maa.journal_id
    ,case when distributed_account_move_id is not null then 'True' else 'False' end as odoo_inserted 
    from m_acc_month mam
    left join m_acc_accrual maa on maa.month_id = mam.id and not maa.is_accrual
    where mam.year_month = '$year_month'
    and mam.active
    order by acc_active";
// } else {
//     $q = "select distinct  mam.*, maa.active acc_active,maa.journal_id
// ,case when distributed_account_move_id is not null then 'True' else 'False' end as odoo_inserted
//  		  ,case when actual_apv_id is not null then 'True' else 'False' end as reversed
// 		 ,case when reverse_account_move_id is not null then 'True' else 'False' end as rev_odoo_inserted
// from m_acc_month mam
// left join m_acc_accrual maa on maa.month_id = mam.id and not maa.is_accrual
// where (not mam.is_dept_distributed or not mam.is_all_reversed)
// and mam.active
// order by acc_active";
// }



$resulta = $db->fetchRow($q);
// var_dump($resulta);
// exit; 
if ($resulta) {
    $month_id = $resulta['id'];

    // echo $month_id;
    $query = "
select 
ma.from_date,
ma.to_date,
ma.id,
mam.is_ap_distributed,
aa.code || ' ' || aa.name AS credit_to,
aa.id AS credit_to_id,
ma.total_accrual_value,
mct.acc_category AS distribution_category,
mct.id AS category_id,
ma.month_id,
string_agg(DISTINCT aa2.code || ' ' || aa2.name, ', ') AS debit_to,
am.name apv,
am.id apv_id,
ma.journal_name ,
ma.journal_id
from
m_acc_month mam
join m_acc_accrual ma on ma.month_id = mam.id and not ma.is_accrual
JOIN account_account aa ON aa.id = ma.credit_to
left JOIN m_acc_category_tbl mct ON mct.id = ma.dist_categ_id
LEFT JOIN M_ACC_COST_DISTRIBUTION acd ON acd.m_acc_category_id = ma.dist_categ_id
LEFT JOIN account_account aa2 ON aa2.id = acd.debit_to
left join account_move am on am.id = ma.actual_apv_id
where mam.id = $month_id
group by
ma.from_date,
ma.to_date,
ma.id,
mam.is_ap_distributed,
aa.code || ' ' || aa.name,
aa.id,
ma.total_accrual_value,
mct.acc_category,
mct.id,
ma.month_id,
am.name ,
am.id,
ma.journal_id
";

    $res = $db->fetchAll($query);
} else {
    $res = false;
}

$data = [
    'date_range' => $resulta,
    'active_accrual' => $res,
];


// $data = [];
// while ($row = pg_fetch_assoc($res)) {
//     $data[] = $row;
// }

echo json_encode($data);
ob_end_flush();
