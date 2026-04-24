<?php
$db = new Postgresql();

$q = "	  SELECT act.id, act.acc_category, sum(coalesce(acd.distribution_percentage,0))::integer total_percentage FROM 
m_acc_category_tbl act
left  join  m_Acc_cost_distribution acd on acd.m_acc_category_id = act.id
group by act.id";

$result = $db->fetchAll($q);

echo json_encode($result);
