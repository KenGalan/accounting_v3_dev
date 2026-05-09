<?php

session_start();

$db_ken = new PostgresqlKen();

$month_id = isset($_POST['month_id']) ?  $_POST['month_id'] : null;
$yearMonth = isset($_POST['yearMonth']) ?  $_POST['yearMonth'] : null;
$cust_data = isset($_POST['cust_data']) ? json_decode($_POST['cust_data'], true) : [];
$transaction_type = isset($_POST['transaction_type']) ? $_POST['transaction_type'] : '';
// $is_accrual = $_POST['is_accrual'];
$user = $_SESSION['ppc']['emp_no'];
$cd_ids = '';

if (!isset($_SESSION['ppc']['emp_no'])) : $user = 0;
    echo json_encode($data);
    exit;
endif; //NOT ISSET SESSION

// $accrual_where = $is_accrual == 'true' ? '' : 'NOT';

//checking if month exists

//checking if month already exists
$hasMonth = $db_ken->fetchRow("select id from m_Acc_month where year_month ='$yearMonth'");

if (!$hasMonth) {
    $month_entries = [
        'YEAR_MONTH' =>    $yearMonth
    ];

    $month_id = $db_ken->insert_get_id('M_ACC_MONTH', $month_entries, 'id');
    // echo 'wala';
} else {
    $month_id = $hasMonth['id'];
    // echo 'meron';
}

foreach ($cust_data as $row) {
    // echo $row['cd_id']; 
    $cd_ids .= $row['cd_id'] . ',';

    $cd_id = $row['cd_id'];
    $details = $db_ken->fetchRow("select a.*, am.journal_id, aj.name journal_name from m_Acc_cust_dist a
    join account_move am on am.id = a.move_id 
    join account_journal aj on aj.id = am.journal_id
     where a.id = $cd_id");


    $entry_details = [
        'TOTAL_ACCRUAL_VALUE' => $details['total_amount'],
        'FROM_DATE' => $details['from_date'],
        'TO_DATE' => $details['to_date'],
        'MONTH_ID' =>  $month_id,
        'JOURNAL_ID' => $details['journal_id'],
        'JOURNAL_NAME' => $details['journal_name'],
        'DATE' => $details['accounting_date'],
        'TRANSACTION_TYPE' => $transaction_type
    ];

    $new_acc_id = $db_ken->insert_get_id('M_ACC_ACCRUAL', $entry_details, 'id');
    if ($new_acc_id) {
        $db_ken->query("UPDATE M_ACC_CUST_DIST SET accrual_id = $new_acc_id WHERE ID = $cd_id");
    }
}
// remove last comma
$cd_ids = rtrim($cd_ids, ',');

$selectDateRange  = $db_ken->fetchAll("SELECT 
    DISTINCT
    to_char(FROM_DATE,'YYYY-MM-DD') start_date, 
    to_char(TO_DATE, 'YYYY-MM-DD') end_date,
    to_char(TO_DATE, 'MM/DD/YYYY') end_date_slash
    , TO_CHAR(to_date(to_char(FROM_DATE ,'YYYY-MM'),'YYYY-MM') + INTERVAL '1 month - 1 day', 'MM/DD/YYYY') LAST_DATE_OF_MONTH
    FROM m_acc_cust_dist WHERE ACTIVE and id in ($cd_ids)");
// exit;









if ($selectDateRange) {
    foreach ($selectDateRange as $date_range) {
        $from_date = $date_range['start_date'];
        $to_date = $date_range['end_date'];
        $to_date_slash = $date_range['end_date_slash'];
        $last_date_of_month = $date_range['last_date_of_month'];

        $qCheck = "SELECT * FROM M_ACC_MO_WIP WHERE FROM_DATE = TO_dATE('$from_date','YYYY-MM-DD')  AND TO_DATE = TO_dATE('$to_date','YYYY-MM-DD')";
        if ($db_ken->fetchRow($qCheck)) {
            continue;
        }
        // exit;

        $qmos = "with mos as (SELECT DISTINCT MO,IS_INVOICED FROM M_ACC_MO_WIP WHERE MONTH_ID != $month_id),
         mo_with_trx AS (
           SELECT DISTINCT mp.name, mp.id AS mo_id
           FROM mrp_production mp
           JOIN (
               SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
               FROM mrp_workorder
               WHERE date_finished + INTERVAL '8 hours' BETWEEN 
                   TO_TIMESTAMP('$from_date','YYYY-MM-DD') + INTERVAL '6 hours'
                   AND TO_TIMESTAMP('$to_date','YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
                   and state !='cancel'
           ) mw ON mw.production_id = mp.id
           LEFT JOIN MOS ON MOS.MO = MP.NAME  AND MOS.IS_INVOICED
        WHERE MP.STATE != 'cancel'
        AND MOS.MO IS NULL
        ),all_mos AS (
           SELECT 
               mp.id AS mo_id, 
               mp.name AS mo,   
               mp.state AS mo_status,
               CASE
           when am.name IS NOT NULL  then 'INVOICED'
           ELSE '' END AS status,
           sum(case when am.name is not null then
                   case when sml.qty_done = 0 or sml.qty_done is null then sml.qty_to_invoice else  sml.qty_done end
             else null::numeric end) invoiced_qty,
    sml2.qtY_done mo_done_qty,
            sml2.date
           FROM mrp_production mp
           LEFT JOIN (
               SELECT * 
               FROM mrp_production_stock_picking_rel A
               JOIN stock_picking B ON B.id = A.stock_picking_id 
               WHERE B.name LIKE 'WH/OUT/%' AND batch_id IS NOT NULL
           ) sp ON sp.mrp_production_id = mp.id
           LEFT JOIN stock_picking_batch spb ON spb.id = sp.batch_id and spb.state ='done'
           left join stock_move_line sml on sml.picking_id = sp.id  and mp.id = sml.manufacturing_order
    left JOIN (select * from stock_move_line where location_dest_id =8) sml2 on sml2.reference = mp.name
           LEFT JOIN account_move_stock_picking_batch_rel amspb ON amspb.stock_picking_batch_id = spb.id
          -- LEFT JOIN account_move am ON am.id = amspb.account_move_id and am.invoice_date <= TO_DATE('$last_date_of_month','MM/DD/YYYY') and am.state !='cancel'
           LEFT JOIN account_move am ON am.id = amspb.account_move_id and am.invoice_date <= TO_DATE('$to_date','YYYY-MM-DD') and am.state !='cancel'
           JOIN mo_with_trx mwt ON mwt.mo_id = mp.id
           group by
             mp.id, 
               mp.name,   
               mp.state,
               CASE
           when am.name IS NOT NULL  then 'INVOICED'
           ELSE '' END,
    sml2.qtY_done,
    sml2.date), REMOVE_DONE_DUPLICATE AS(
    select *,
    ROW_NUMBER() OVER(
    PARTITION BY MO_ID ORDER BY DATE DESC
    ) RN
    from 
    all_mos)
    ,filtered_mo AS (
           SELECT DISTINCT 
               mo,
               mo_id,
               mo_status,
               MAX(status) OVER (PARTITION BY mo_id) AS status,
        invoiced_qty,
    -- 	   max(invoiced_qty) OVER (PARTITION BY mo_id)  invoiced_qty,
           mo_done_qty
           FROM REMOVE_DONE_DUPLICATE RDD
           where 
           RDD.RN = 1),
   set_status AS (
       SELECT
           fm.mo,
       fm.mo_id,
           pt.name AS device,
           pt.id as device_id,
           pc.name AS category,
           mp.lot_no,
           mp.customer_name,
           SUM(mrw.time_cycle_manual) AS total_labor,
           '' AS total_multiplied_in_quantity_done,
           '' AS earned_hrs,
           fm.mo_status,
           CASE WHEN SUM(mrw.time_cycle_manual) IS NULL 
               THEN 'NO TRANSACTION BETWEEN THE MONTHEND RANGE' 
               ELSE fm.status END AS status,
           max_wo,
           CASE WHEN PC.NAME LIKE 'DIE%' THEN
       CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
       ELSE
       TRIM(SPLIT_PART(PC.NAME, ' ', 1))
       END AS SBU,
       fm.invoiced_qty,
       fm.mo_done_qty
       FROM filtered_mo fm
       JOIN mrp_production mp ON mp.id = fm.mo_id
       JOIN product_product pp ON pp.id = mp.product_id
       JOIN product_template pt ON pt.id = pp.product_tmpl_id
       JOIN product_category pc ON pc.id = pt.categ_id
       LEFT JOIN (
           SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
           FROM mrp_workorder
           WHERE date_finished + INTERVAL '8 hours' BETWEEN 
               TO_TIMESTAMP('$from_date', 'YYYY-MM-DD') + INTERVAL '6 hours' 
               AND TO_TIMESTAMP('$to_date', 'YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
               and state = 'done'
       ) mw ON mw.production_id = fm.mo_id
       LEFT JOIN mrp_routing_workcenter mrw ON mw.operation_id = mrw.id
       WHERE 
       FM.STATUS !='INVOICED' AND
       fm.mo_status NOT IN ('cancel','draft','planned') 
       GROUP BY fm.mo, pt.name, pc.name, mp.lot_no, mp.customer_name, fm.mo_status, fm.status, max_wo,fm.mo_id,pt.id,
       CASE WHEN PC.NAME LIKE 'DIE%' THEN
       CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
       ELSE
       TRIM(SPLIT_PART(PC.NAME, ' ', 1))
     END,
     fm.invoiced_qty,
     fm.mo_done_qty
     UNION ALL
	        SELECT
           fm.mo,
       fm.mo_id,
           pt.name AS device,
           pt.id as device_id,
           pc.name AS category,
           mp.lot_no,
           mp.customer_name,
           SUM(mrw.time_cycle_manual) AS total_labor,
           '' AS total_multiplied_in_quantity_done,
           '' AS earned_hrs,
           fm.mo_status,
           CASE WHEN SUM(mrw.time_cycle_manual) IS NULL 
               THEN 'NO TRANSACTION BETWEEN THE MONTHEND RANGE' 
               ELSE fm.status END AS status,
           max_wo,
           CASE WHEN PC.NAME LIKE 'DIE%' THEN
       CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
       ELSE
       TRIM(SPLIT_PART(PC.NAME, ' ', 1))
       END AS SBU,
       fm.invoiced_qty,
       fm.mo_done_qty
       FROM filtered_mo fm
       JOIN mrp_production mp ON mp.id = fm.mo_id
       JOIN product_product pp ON pp.id = mp.product_id
       JOIN product_template pt ON pt.id = pp.product_tmpl_id
       JOIN product_category pc ON pc.id = pt.categ_id
       LEFT JOIN (
           SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
           FROM mrp_workorder
           WHERE date_finished + INTERVAL '8 hours' > 
               TO_TIMESTAMP('$from_date', 'YYYY-MM-DD') + INTERVAL '6 hours' 
               --AND TO_TIMESTAMP('$to_date', 'YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
               and state = 'done'
       ) mw ON mw.production_id = fm.mo_id
       LEFT JOIN mrp_routing_workcenter mrw ON mw.operation_id = mrw.id
       WHERE 
      -- fm.status != 'INVOICED BEFORE MONTH END' AND 
	  FM.STATUS = 'INVOICED' AND
	--  fm.mo ='MO898804' and
       fm.mo_status NOT IN ('cancel','draft','planned') 
       GROUP BY fm.mo, pt.name, pc.name, mp.lot_no, mp.customer_name, fm.mo_status, fm.status, max_wo,fm.mo_id,pt.id,
       CASE WHEN PC.NAME LIKE 'DIE%' THEN
       CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
       ELSE
       TRIM(SPLIT_PART(PC.NAME, ' ', 1))
     END,
     fm.invoiced_qty,
     fm.mo_done_qty
   )
   --, mo_percentage  as (
  SELECT
       a.mo,
       a.mo_id,
       a.device,
       a.device_id,
       a.sbu,
       a.category,
       a.lot_no,
       a.customer_name,
       coalesce(mw.done_qty,0) AS quantity_done,
       coalesce(a.total_labor,0) total_labor,
       (mw.done_qty * a.total_labor) AS total_multiplied_in_quantity_done,
       round(coalesce((mw.done_qty * a.total_labor) / 3600,0),5) AS earned_hrs,
       (round(mw.done_qty,5)/ (SUM(round(coalesce(mw.done_Qty,0),5)) OVER (PARTITION BY A.SBU))) qty_percentage_per_mo,
       (round(coalesce((mw.done_qty * a.total_labor) / 3600,0),5) / NULLIF(SUM(round(coalesce((mw.done_qty * a.total_labor) / 3600,0),5)) OVER (partition by a.sbu), 0)) eh_percentage_per_mo,
     a.mo_status,
     CASE WHEN a.status ='INVOICED' THEN true else false end inv_status,
     invoiced_qty invoiced_qty,
     mo_done_qty mo_done_qty,
'' REMARKS
   FROM set_status a
   LEFT JOIN mrp_workorder mw ON mw.id = a.max_wo --order by eh_percentage_per_mo

   UNION ALL
   select
  mp.name mo,
  mp.id mo_id,
  pt.name device,
  pt.id device_id,
    CASE WHEN PC.NAME LIKE 'DIE%' THEN
       CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
       ELSE
       TRIM(SPLIT_PART(PC.NAME, ' ', 1))
       END AS SBU,
  pc.name category,
  mp.LOT_NO LOT_NUMBER,
  mp.customer_name,
  sml2.qtY_done quantity_done,
  null::numeric total_labor,
  null::numeric total_multiplied_in_quantity_done,
  null::numeric earned_hrs,
  null::numeric qty_percentage_per_mo,
  null::numeric eh_percentage_per_mo,
  mp.state mo_status,
  true inv_status,
  sum(sml.qty_done) invoiced_qty,
  sml2.qtY_done mo_done_qty,
  'INVOICED BUT NO MOVEMENT' REMARKS
  from
  mrp_production mp
  JOIN PRODUCT_PRODUCT PP ON PP.ID = MP.PRODUCT_ID
  JOIN PRODUCT_TEMPLATE PT ON PT.ID = PP.PRODUCT_TMPL_ID
  JOIN PRODUCT_CATEGORY PC ON PC.ID =PT.CATEG_ID
  left join mrp_production_stock_picking_rel mpsp on mpsp.mrp_production_id = mp.id
  left join stock_picking sp on sp.id = mpsp.stock_picking_id
  left join stock_picking_batch spb on spb.id = sp.batch_id
  join stock_move_line sml on sml.picking_id = sp.id  and mp.id = sml.manufacturing_order
  left JOIN (select * from stock_move_line where location_dest_id =8) sml2 on sml2.reference = mp.name
  left join account_move_stock_picking_batch_rel amsp on amsp.stock_picking_batch_id = spb.id
  left join (select * from account_move where NAME LIKE 'INV/%'  ) am on am.id = amsp.account_move_id
  right join mos on mos.mo = mp.name AND  NOT MOS.IS_INVOICED
    LEFT JOIN mo_with_trx MWT ON MWT.NAME = MOS.MO
  where --mp.name ='MO902351' and
  spb.state ='done'
  and am.invoice_date::date between to_date('$from_date','YYYY-MM-DD') and to_date('$to_date','YYYY-MM-DD')
    AND MWT.NAME IS NULL
  group by 
  mp.name,
  mp.id,
  pt.name,
  pt.id,
  CASE WHEN PC.NAME LIKE 'DIE%' THEN
       CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
       ELSE
       TRIM(SPLIT_PART(PC.NAME, ' ', 1))
       END,
  pc.name,
  mp.LOT_NO,
  mp.customer_name,
  sml2.qtY_done,
  mp.state
 ";


        $resultmos = $db_ken->fetchAll($qmos);

        // var_dump($resultmos);
        // exit;
        //USE INSERT GET ID
        foreach ($resultmos as $item) {
            $mo = $item['mo'];
            $mo_id = $item['mo_id'];
            $device = $item['device'];
            $device_id = $item['device_id'];
            $category = $item['category'];
            $customer_name = $item['customer_name'];
            $earned_hrs = $item['earned_hrs'];
            $eh_percentage = $item['eh_percentage_per_mo'];
            $qty_done = $item['quantity_done'];
            $qty_percentage = $item['qty_percentage_per_mo'];
            $mo_status = $item['mo_status'];
            $inv_status = $item['inv_status'];
            $sbu = $item['sbu'];
            $invoiced_qty = $item['invoiced_qty'];
            $mo_done_qty = $item['mo_done_qty'];
            $remarks = $item['remarks'];
            $dataMoEntries = [
                'MONTH_ID' => $month_id,
                'FROM_DATE' => $from_date,
                'TO_DATE' => $to_date,
                'MO' => $mo,
                'MO_ID' => $mo_id,
                'DEVICE' => $device,
                'DEVICE_ID' => $device_id,
                'CATEGORY' => $category,
                'CUSTOMER_NAME' => $customer_name,
                'EARNED_HRS' => $earned_hrs,
                'EH_PERCENTAGE' => $eh_percentage,
                'QTY_DONE' => $qty_done,
                'QTY_PERCENTAGE' => $qty_percentage,
                'MO_STATUS' => $mo_status,
                'SBU' => $sbu,
                'IS_INVOICED' => $inv_status,
                'ADDED_BY' => $user,
                'INVOICED_QTY' => $invoiced_qty,
                'MO_DONE_QTY' => $mo_done_qty,
                'REMARKS'  => $remarks

            ];


            $resultLineItems = $db_ken->insert('M_ACC_MO_WIP', $dataMoEntries);
        }
    }
    // exit;
    // if ($transaction_type == 'custom_distribution') {
    // }
    if ($transaction_type == 'custom_distribution') {
        $q = "with acd as(select acd.id, acd.move_id ,acd.total_amount amount_total,acd.from_date,acd.to_date,
        STRING_AGG(upper(sbu.sbu), ', ') AS sbu_names from m_acc_cust_dist acd
JOIN m_acc_sbu_maint sbu
ON sbu.id = ANY(acd.sbu)
where acd.id in ($cd_ids)
group by acd.id,acd.move_id,acd.total_amount,acd.from_date,acd.to_date
       )
, main  as(
select 
acd.id acd_id, 
acd.amount_total,
aml.account_id, 
sum(aml.debit) debit,
aml.account_id credit_to,
aml.account_id debit_to,
am.journal_id,
acd.from_date,
acd.to_date,acd.sbu_names,
cda.cogs_account_id
from acd
join account_move am on am.id = acd.move_id
join account_move_line aml on aml.move_id = acd.move_id and aml.debit > 0 
join m_acc_customized_dist_accounts cda on cda.account_id  = aml.account_id and cda.active
group by acd.id, 
acd.amount_total,
aml.account_id, 
aml.account_id ,
am.journal_id,
acd.from_date,
acd.to_date,acd.sbu_names,cda.cogs_account_id
)
, mfg as (
          select 
          m.acd_id,
          adm.mo,
          m.debit,
          ADM.EARNED_HRS/SUM(adm.EARNED_HRS) OVER(PARTITION BY m.acd_id,m.account_id) percentage,
          m.debit * ADM.EARNED_HRS/SUM(adm.EARNED_HRS) OVER(PARTITION BY m.acd_id,m.account_id) mo_allocation,
          adm.sbu,
          m.FROM_DATE,
          m.TO_DATE,
        m.account_id,
         m.amount_total,
         m.journal_id,
    m.cogs_account_id
         from main m
         JOIN M_ACC_MO_WIP ADM ON ADM.FROM_DATE = m.FROM_DATE  AND ADM.TO_DATE = m.TO_DATE and m.sbu_names  like '%' || upper(adm.sbu) || '%' 
         AND ADM.REMARKS !='INVOICED BUT NO MOVEMENT'
         ), mfg_sbu as(
     select 
     m.acd_id,
     m.sbu,
     sum(mo_allocation) total,
     trunc(sum(mo_allocation),2) trunc_total,
         (sum(mo_allocation)/amount_total)*100 total_pct,
     trunc((sum(mo_allocation)/amount_total)*100,2) trunc_total_pct,
     m.debit,
          m.FROM_DATE,
          m.TO_DATE,
          m.account_id debit_to,
             m.account_id,
         m.cogs_account_id ,
         m.journal_id,
--              m.distribution_percentage,
         m.amount_total
     from mfg m
          group by m.acd_id,
     m.sbu,m.debit,
          m.FROM_DATE,
          m.TO_DATE,
             m.account_id ,
             m.cogs_account_id,
         m.journal_id,
--              m.distribution_percentage,
         m.amount_total), mfg_rank as(
     select
     ms.acd_id,
     ms.sbu,
     ms.total sbu_total,
     ms.trunc_total trunc_sbu_total,
     sum(ms.total) over(partition by acd_id,ms.account_id) total,
     sum(ms.trunc_total) over(partition by acd_id, ms.account_id) trunc_total,
     ((ms.debit - (sum(ms.trunc_total) over(partition by acd_id, ms.account_id)))/ 0.01)::integer rows_to_adjust,
     ROW_NUMBER() OVER (PARTITION BY ms.acd_id, ms.account_id ORDER BY ms.total - ms.trunc_total DESC) AS rn,
     -- pct
     ms.total_pct ,
     ms.trunc_total_pct,
     sum(ms.total_pct) over(partition by acd_id, ms.account_id) pct_total,
     sum(ms.trunc_total_pct) over(partition by acd_id, ms.account_id) pct_trunc_total,
--          ((ms.distribution_percentage -(sum(ms.trunc_total_pct) over(partition by acd_id,ms.wip_account)) )/ 0.01)::integer rows_to_adjust_pct,
--          ROW_NUMBER() OVER (PARTITION BY ms.acd_id, ms.wip_account ORDER BY ms.total_pct - ms.trunc_total_pct DESC) AS rn_pct,
     ms.debit,
              ms.FROM_DATE,
          ms.TO_DATE,
             ms.debit_to,
         ms.cogs_account_id,
         ms.journal_id
     from 
     mfg_sbu ms
         )
         ,distributed_final as(
    select
    acd_id,
    CASE 
             WHEN rn <= rows_to_adjust THEN trunc_sbu_total + 0.01
             ELSE trunc_sbu_total
         END AS debit,
    adg.dept_group,
    adg.id dept_group_id,
    aaa.name dept,
    sbu.analytic_account_id,
     mr.debit_to,
         mr.cogs_account_id,
     mr.journal_id,
    mr.from_date,
    mr.to_date
    from 
    mfg_rank mr
    join m_acc_sbu_maint sbu on upper(sbu.sbu) = upper(mr.sbu)
    left JOIN account_analytic_account aaa ON aaa.id = sbu.analytic_account_id
          left join m_acc_department_groups adg on adg.id =aaa.m_acc_group_id)
    , debit_credit_DIST as(
     select
     df.acd_id,
--          df.distribution_percentage,
     df.debit debit,
     0::numeric credit,
     df.dept_group,
         df.dept,
         df.analytic_account_id,
          df.debit_to ACCOUNT_ID,
         df.cogs_account_id,
         df.journal_id
     from
     distributed_final df
     union all
     select
     m.acd_id,
--          0::numeric distribution_percentage,
     0::numeric debit,
     m.debit credit,
     '' dept_group,
    '' dept,
         null::integer analytic_account_id,
          m.account_id account_id,
          null::integer cogs_account_id,
          m.journal_id
     from
     main m )
     select 
     dcem.acd_id transaction_id,
     aj.name journal,
      dcem.journal_id,
     AA.CODE ACCOUNT_CODE,
     DCEM.account_id,
    '$to_date_slash' DATE,
     dcem.dept,
--          dcem.distribution_percentage,
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
     dcem.cogs_account_id cogs_account_id
     from
     debit_credit_DIST dcem
     LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID =DCEM.ACCOUNT_ID
     left join account_journal aj on aj.id = dcem.journal_id
     ORDER BY acd_id";
    }

    $result = $db_ken->fetchAll($q);
}










if ($result) {
    // if ($is_accrual == 'true') {
    //     $db->query("UPDATE M_ACC_MONTH SET is_dept_distributed = TRUE WHERE ID = $month_id");
    // } else {
    //     $db->query("UPDATE M_ACC_MONTH SET is_ap_distributed = TRUE WHERE ID = $month_id");
    // }
    $old_transaction_id = '';
    try {
        // START TRANSACTION
        $db_ken->beginTransaction();

        $old_transaction_id = 0;

        foreach ($result as $item) {

            $transaction_id = $item['transaction_id'];
            $account_code = $item['account_code'];
            $account_id = $item['account_id'];
            $analytic_account = $item['analytic_account'];
            $analytic_account_id = $item['analytic_account_id'] ?: null;
            $distribution_percentage = isset($item['distribution_percentage'])  && $item['distribution_percentage'] ? $item['distribution_percentage'] : null;
            $debit = $item['debit'] ?: null;
            $credit = $item['credit'] ?: null;
            $account_move_date = $item['date'];
            $sbu = $item['sbu'];
            $cogs_account_id = isset($item['cogs_account_id']) && $item['cogs_account_id'] ? $item['cogs_account_id'] : null;
            $journal = $item['journal'];
            $journal_id = $item['journal_id'] ?: null;
            $date = $item['date'];

            // IF accrual_id changed → process update and COGS
            if ($transaction_id != $old_transaction_id) {


                if ($old_transaction_id != 0) {


                    // INSERT TO COGS
                    $qToCogs = insertToCogs($old_transaction_id);
                    $resultToCogs = $db_ken->fetchAll($qToCogs);

                    foreach ($resultToCogs as $itemToCogs) {
                        $db_ken->insert('M_ACC_TRX_COGS', [
                            'MAIN_ID' => $old_transaction_id,
                            'ACCOUNT_CODE' => $itemToCogs['account_code'],
                            'ACCOUNT_ID' => $itemToCogs['account_id'],
                            'CREDIT_ACCOUNT_ID' => $itemToCogs['credit_account_id'],
                            'ANALYTIC_ACCOUNT' => $itemToCogs['analytic_account'],
                            'ANALYTIC_ACCOUNT_ID' => $itemToCogs['analytic_account_id'] ?: null,
                            'MOS' => $itemToCogs['mos'],
                            'DEBIT' => $itemToCogs['debit'] ?: null,
                            'CREDIT' => $itemToCogs['credit'] ?: null,
                            'ITEM_LABEL' => $itemToCogs['item_label'],
                            // 'RAW_DEBIT' => $itemToWip['raw_debit'] ?: null,
                            // 'RAW_CREDIT' => $itemToWip['raw_credit'] ?: null,
                            'ADDED_BY' => $user,
                            'SBU' => $itemToCogs['sbu']
                        ]);
                    }
                }
            }

            // INSERT ACCOUNT DISTRIBUTION LINE
            $db_ken->insert('M_ACC_DISTRIBUTION_TRANSACTION', [
                'TRANSACTION_ID' => $transaction_id,
                'ACCOUNT_CODE' => $account_code,
                'ACCOUNT_ID' => $account_id,
                'ANALYTIC_ACCOUNT' => $analytic_account,
                'ANALYTIC_ACCOUNT_ID' => $analytic_account_id,
                'DISTRIBUTION_PERCENTAGE' => $distribution_percentage,
                'DEBIT' => $debit,
                'CREDIT' => $credit,
                'ADDED_BY' => $user,
                'SBU' => $sbu,
                'COGS_ACCOUNT_ID' => $cogs_account_id,
                'TRANSACTION_TYPE' => $transaction_type
            ]);

            $old_transaction_id = $transaction_id;
            // $db_ken->commit();
            // exit;
        }
        // $db_ken->commit();
        // exit;



        // INSERT TO COGS
        $qToCogsLastRecord = insertToCogs($transaction_id);
        $resultLastToCogs = $db_ken->fetchAll($qToCogsLastRecord);

        foreach ($resultLastToCogs as $itemToCogs) {
            $db_ken->insert('M_ACC_TRX_COGS', [
                'TRANSACTION_ID' => $transaction_id,
                'ACCOUNT_CODE' => $itemToCogs['account_code'],
                'ACCOUNT_ID' => $itemToCogs['account_id'],
                'CREDIT_ACCOUNT_ID' => $itemToCogs['credit_account_id'],
                'ANALYTIC_ACCOUNT' => $itemToCogs['analytic_account'],
                'ANALYTIC_ACCOUNT_ID' => $itemToCogs['analytic_account_id'] ?: null,
                'MOS' => $itemToCogs['mos'],
                'DEBIT' => $itemToCogs['debit'] ?: null,
                'CREDIT' => $itemToCogs['credit'] ?: null,
                'ITEM_LABEL' => $itemToCogs['item_label'],
                // 'RAW_DEBIT' => $itemToWip['raw_debit'] ?: null,
                // 'RAW_CREDIT' => $itemToWip['raw_credit'] ?: null,
                'ADDED_BY' => $user,
                'SBU' => $itemToCogs['sbu']
            ]);
        }

        // COMMIT ALL INSERTS
        $db_ken->commit();
        // echo "All accruals and lines inserted successfully.";
    } catch (Exception $e) {
        // ROLLBACK EVERYTHING on ANY error
        $db_ken->rollBack();
        echo "Transaction failed: " . $e->getMessage();
    }

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

} else {
    echo '<pre>';
    echo $q;
    exit;
}

function insertToCogs($previous_main_id)
{

    $qToCogs = "
    with not_tally as(
        select
        maa.id transaction_id,
    adm.mo,
    adm.device,
    adm.category,
    adm.customer_name,
    adm.earned_hrs,
    aad.debit *
    CASE WHEN maa.mo_dist = 'EH' THEN adm.EH_percentage
    ELSE QTY_PERCENTAGE END allocation,
    aad.sbu,
        adm.is_invoiced,
        aad.ACCOUNT_CODE,
        aad.ACCOUNT_ID,
      aad.ANALYTIC_ACCOUNT,
        aad.ANALYTIC_ACCOUNT_ID,
        aad.cogs_account_id,
        adm.invoiced_qty,
        adm.mo_done_qty
    from
--         join M_ACC_ACCRUAL maa on maa.month_id = adr.ID AND accrual_where MAA.IS_ACCRUAL
--         join M_ACC_ACCRUAL_DIST aad on aad.transaction_id = maa.id
-- 	select * from m_acc_distribution_transaction
-- 	select * from m_acc_cust_dist
	 m_acc_cust_dist maa 
	join m_acc_distribution_transaction aad on aad.transaction_id = maa.id and aad.transaction_type ='custom_distribution'
    join account_analytic_account aaa on aaa.id =aad.analytic_account_id
    join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
    join m_acc_mo_wip adm on adm.sbu =aad.sbu and adm.from_date = maa.from_date and adm.to_date = maa.to_date
    JOIN ACCOUNT_ACCOUNT   AA ON AA.ID = aad.ACCOUNT_ID
--     JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACCOUNT_ID = AA.ID and aca.acc_category_id = maa.dist_categ_id
--     JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.acc_category_id
    where  aad.cogs_account_id is not null and
    adm.is_invoiced and
    maa.id in ($previous_main_id) and adg.dept_group ='MANUFACTURING/PRODUCT LINE'
    )
    ,MO_RANKED AS (
    SELECT 
    nt.transaction_id,
    NT.MO,
    NT.DEVICE,
    NT.CATEGORY,
    NT.ALLOCATION,
    NT.CUSTOMER_NAME,
    NT.earned_hrs,
    TRUNC(NT.ALLOCATION,5) TRUNC_ALLOCATION,
    SUM(NT.ALLOCATION) OVER(partition by nt.transaction_id) TOTAL_ALLOCATION,
    SUM(TRUNC(NT.ALLOCATION,5)) OVER(partition by nt.transaction_id) TOTAL_TRUNC_ALLOCATION,
    (
    NT.ALLOCATION- TRUNC(NT.ALLOCATION,5)
    ) ALLOCATION_DIFF,
    ((
    SUM(NT.ALLOCATION) OVER(partition by nt.transaction_id)- SUM(TRUNC(NT.ALLOCATION,5)) OVER(partition by nt.transaction_id)
    )/ 0.00001)::INTEGER ROWS_TO_ADJUST,
    ROW_NUMBER() OVER (partition by nt.transaction_id ORDER BY NT.ALLOCATION - TRUNC(NT.ALLOCATION,5) DESC) AS rn,
    nt.sbu,
    nt.is_invoiced,
    nt.ACCOUNT_CODE,
        nt.ACCOUNT_ID,
        nt.ANALYTIC_ACCOUNT,
        nt.ANALYTIC_ACCOUNT_ID,
        nt.cogs_account_id,
        nt.invoiced_qty,
        nt.mo_done_qty
    FROM 
    NOT_TALLY NT
    )
    , allocation_adjusted as (
    SELECT 
                        transaction_id,
    MO,
    DEVICE,
    CATEGORY,
    CUSTOMER_NAME,
    EARNED_HRS,
  --  CASE 
   -- WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
   -- ELSE TRUNC_ALLOCATION
   -- END AS ALLOCATION,
    coalesce(round((invoiced_qty/mo_done_qty)* CASE 
    WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
    ELSE TRUNC_ALLOCATION
    END,5),CASE 
    WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
    ELSE TRUNC_ALLOCATION
    END) ALLOCATION,
                        total_allocation,
    sbu,
    is_invoiced,
                          ACCOUNT_CODE,
        ACCOUNT_ID,
        ANALYTIC_ACCOUNT,
        ANALYTIC_ACCOUNT_ID,
        cogs_account_id,
        invoiced_qty,
		mo_done_qty
    FROM
    MO_RANKED
                             )
							 ,FINAL_DATA AS (
							  select 
    transaction_id,
    string_Agg(DISTINCT mo,',') mos,
    round(sum(allocation),2) allocation,
    is_invoiced,
    sbu,
    ACCOUNT_CODE CREDIT_ACCOUNT_CODE,
    ACCOUNT_ID CREDIT_ACCOUNT_ID,
        ANALYTIC_ACCOUNT,
        ANALYTIC_ACCOUNT_ID,
        AADD.cogs_account_id ACCOUNT_ID,
        AA.CODE ACCOUNT_CODE,
		'' reference
    from
    allocation_adjusted aadd
    LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AADD.cogs_account_id
    where --not is_invoiced
     is_invoiced
    group by transaction_id,sbu,is_invoiced, total_allocation,
        ANALYTIC_ACCOUNT,
        ANALYTIC_ACCOUNT_ID,
          ACCOUNT_CODE,
        ACCOUNT_ID,
        AADD.cogs_account_id,
        AA.CODE,
        AADD.cogs_account_id
--     union all							 
--     select  
--      	im.accrual_id accrual_id,
--      	string_Agg(DISTINCT am.mo,',') mos,
--          round(sum( (im.invoiced_qty/im.mo_done_qty) * coalesce(aml.actual_allocation,aml.accrual_allocation)),2) allocation, 
--  		true is_invoiced,
--      	am.sbu, 
--      	IM.ACCOUNT_CODE CREDIT_ACCOUNT_CODE,
--      	IM.ACCOUNT_ID CREDIT_ACCOUNT_ID,
--          IM.ANALYTIC_ACCOUNT,
--          IM.ANALYTIC_ACCOUNT_ID,
--          IM.cogs_account_id ACCOUNT_ID,
--          AA.CODE ACCOUNT_CODE,
--  		'From WIP of Previous Months' reference
--      	from m_acc_mo_wip am
--      	left join m_acc_mo_wip_line aml on aml.mo_wip_id = am.id
--      	join m_acc_accrual maa on maa.id = aml.accrual_id -- AND accrual_where MAA.IS_ACCRUAL
--      	join M_ACC_ACCRUAL_DIST mad on mad.accrual_id = aml.accrual_id and upper(mad.sbu) = upper(am.sbu)
--      	join M_ACC_CATEGORY_TBL mact on mact.id = maa.dist_categ_id
--      	JOIN allocation_adjusted IM ON IM.MO =AM.MO AND IM.cogs_account_id = MAD.cogs_account_id
--  		LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = IM.cogs_account_id
--      	WHERE am.month_id != month_id and not am.is_invoiced
--      	and coalesce(aml.actual_allocation,aml.accrual_allocation) is not null
--      	group by im.accrual_id, am.sbu,IM.ACCOUNT_CODE,IM.ACCOUNT_ID,IM.ANALYTIC_ACCOUNT,IM.ANALYTIC_ACCOUNT_ID,IM.cogs_account_id,aa.CODE
		)
	SELECT 
    FD.ACCOUNT_CODE,
    FD.ACCOUNT_ID,
    FD.CREDIT_ACCOUNT_ID,
    REPLACE(FD.ANALYTIC_ACCOUNT, '''', '''''') ANALYTIC_ACCOUNT,
    FD.ANALYTIC_ACCOUNT_ID,
    FD.MOS,
    FD. ALLOCATION DEBIT,
    0 CREDIT,
    FD.SBU,
	FD.REFERENCE ITEM_LABEL
    FROM FINAL_DATA FD
    UNION ALL
    SELECT 
    FD2.CREDIT_ACCOUNT_CODE ACCOUNT_CODE,
    FD2.CREDIT_ACCOUNT_ID ACCOUNT_ID,
    FD2.CREDIT_ACCOUNT_ID,
    NULL ANALYTIC_ACCOUNT,
    NULL ANALYTIC_ACCOUNT_ID,
    NULL MOS,
    0 DEBIT,
    SUM(FD2.ALLOCATION) CREDIT,
    '' SBU,
	'' ITEM_LABEL
    FROM FINAL_DATA FD2
    GROUP BY
    FD2.CREDIT_ACCOUNT_CODE,
    FD2.CREDIT_ACCOUNT_ID
    ";
    return $qToCogs;
}
// var_dump($result);
// exit;

echo json_encode($month_id);
