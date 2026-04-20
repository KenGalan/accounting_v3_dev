<?php

session_start();

$db = new Postgresql();
$db_ken = new PostgresqlKen();
$month_id = $_POST['month_id'];
$user = $_SESSION['ppc']['emp_no'];
if (!isset($_SESSION['ppc']['emp_no'])) : $user = 0;
    echo json_encode($data);
    exit;
endif; //NOT ISSET SESSION

$month_row = $db_ken->fetchRow("
    SELECT id
    FROM M_ACC_DATE_RANGE
    WHERE id = $month_id
    AND is_dept_distributed = TRUE
");

if ($month_row) {
    echo json_encode([
        'status' => 'distributed',
        'message' => 'This month has already been distributed'
    ]);
    exit;
}

$select_month = $db->fetchRow("SELECT to_char(start_date,'YYYY-MM-DD') start_date, to_char(end_date, 'YYYY-MM-DD') end_date, TO_CHAR(to_date(to_char(start_date ,'YYYY-MM'),'YYYY-MM') + INTERVAL '1 month - 1 day', 'MM/DD/YYYY') LAST_DATE_OF_MONTH FROM M_ACC_DATE_RANGE WHERE ID =$month_id");

if ($select_month) {
    $from_date = $select_month['start_date'];
    $to_date = $select_month['end_date'];
    // echo select_month;
    // exit; 

    $date = new DateTime($from_date);

    // First day of the month
    $firstDay = $date->format('Y-m-01');

    // Last day of the month
    $lastDay = $date->format('Y-m-t');

    $last_date_of_month = $select_month['last_date_of_month'];
    $monthYear = substr($from_date, 0, 7);

    //     $qmos = "with mo_with_trx AS (
    //         SELECT DISTINCT mp.name, mp.id AS mo_id
    //         FROM mrp_production mp
    //         JOIN (
    //             SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
    //             FROM mrp_workorder
    //             WHERE date_finished + INTERVAL '8 hours' BETWEEN 
    //                 TO_TIMESTAMP('$from_date','YYYY-MM-DD') + INTERVAL '6 hours'
    //                 AND TO_TIMESTAMP('$to_date','YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
    //                 and state !='cancel'
    //         ) mw ON mw.production_id = mp.id
    //     ),
    //     all_mos AS (
    //         SELECT 
    //             mp.id AS mo_id, 
    //             mp.name AS mo,  
    //             TO_CHAR(mp.create_date + INTERVAL '8 hours', 'YYYY-MM-DD') AS date_load,
    //             am.name AS inv_no,
    //             spb.name AS batch_no, 
    //             spb.state AS voucher_status, 
    //             am.invoice_date,
    //             mp.state AS mo_status,
    //             CASE-- WHEN am.invoice_date <= TO_DATE('$last_date_of_month','MM/DD/YYYY')
    //                -- THEN 'INVOICED BEFORE MONTH END' 
    // 		--when am.name IS NOT NULL then 'INVOICED'
    //         when am.name IS NOT NULL and am.invoice_date <= TO_DATE('$last_date_of_month','MM/DD/YYYY')  then 'INVOICED'
    // 		ELSE '' END AS status
    //         FROM mrp_production mp
    //         LEFT JOIN (
    //             SELECT * 
    //             FROM mrp_production_stock_picking_rel A
    //             JOIN stock_picking B ON B.id = A.stock_picking_id 
    //             WHERE B.name LIKE 'WH/OUT/%' AND batch_id IS NOT NULL
    //         ) sp ON sp.mrp_production_id = mp.id
    //         LEFT JOIN stock_picking_batch spb ON spb.id = sp.batch_id
    //         LEFT JOIN account_move_stock_picking_batch_rel amspb ON amspb.stock_picking_batch_id = spb.id
    //         LEFT JOIN account_move am ON am.id = amspb.account_move_id
    //         JOIN mo_with_trx mwt ON mwt.mo_id = mp.id
    //     ),
    //     filtered_mo AS (
    //         SELECT DISTINCT 
    //             mo,
    //             mo_id,
    //             mo_status,
    //             MAX(status) OVER (PARTITION BY mo_id) AS status
    //         FROM all_mos
    //     ),
    //     set_status AS (
    //         SELECT
    //             fm.mo,
    //         fm.mo_id,
    //             pt.name AS device,
    //             pt.id as device_id,
    //             pc.name AS category,
    //             mp.lot_no,
    //             mp.customer_name,
    //             SUM(mrw.time_cycle_manual) AS total_labor,
    //             '' AS total_multiplied_in_quantity_done,
    //             '' AS earned_hrs,
    //             fm.mo_status,
    //             CASE WHEN SUM(mrw.time_cycle_manual) IS NULL 
    //                 THEN 'NO TRANSACTION BETWEEN THE MONTHEND RANGE' 
    //                 ELSE fm.status END AS status,
    //             max_wo,
    //             CASE WHEN PC.NAME LIKE 'DIE%' THEN
    //         CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
    //         ELSE
    //         TRIM(SPLIT_PART(PC.NAME, ' ', 1))
    //         END AS SBU
    //         FROM filtered_mo fm
    //         JOIN mrp_production mp ON mp.id = fm.mo_id
    //         JOIN product_product pp ON pp.id = mp.product_id
    //         JOIN product_template pt ON pt.id = pp.product_tmpl_id
    //         JOIN product_category pc ON pc.id = pt.categ_id
    //         LEFT JOIN (
    //             SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
    //             FROM mrp_workorder
    //             WHERE date_finished + INTERVAL '8 hours' BETWEEN 
    //                 TO_TIMESTAMP('$from_date', 'YYYY-MM-DD') + INTERVAL '6 hours' 
    //                 AND TO_TIMESTAMP('$to_date', 'YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
    //                 and state = 'done'
    //         ) mw ON mw.production_id = fm.mo_id
    //         LEFT JOIN mrp_routing_workcenter mrw ON mw.operation_id = mrw.id
    //         WHERE 
    //        -- fm.status != 'INVOICED BEFORE MONTH END' AND 
    //         fm.mo_status NOT IN ('cancel','draft','planned') 
    //         GROUP BY fm.mo, pt.name, pc.name, mp.lot_no, mp.customer_name, fm.mo_status, fm.status, max_wo,fm.mo_id,pt.id,
    //         CASE WHEN PC.NAME LIKE 'DIE%' THEN
    //         CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
    //         ELSE
    //         TRIM(SPLIT_PART(PC.NAME, ' ', 1))
    //       END
    //     )
    //     --, mo_percentage  as (
    //    SELECT
    //         a.mo,
    //         a.mo_id,
    //         a.device,
    //         a.device_id,
    //         a.sbu,
    //         a.category,
    //         a.lot_no,
    //         a.customer_name,
    //         coalesce(mw.done_qty,0) AS quantity_done,
    //         coalesce(a.total_labor,0) total_labor,
    //         (mw.done_qty * a.total_labor) AS total_multiplied_in_quantity_done,
    //         round(coalesce((mw.done_qty * a.total_labor) / 3600,0),5) AS earned_hrs,
    //         (round(mw.done_qty,5)/ (SUM(round(coalesce(mw.done_Qty,0),5)) OVER (PARTITION BY A.SBU))) qty_percentage_per_mo,
    //         (round(coalesce((mw.done_qty * a.total_labor) / 3600,0),5) / NULLIF(SUM(round(coalesce((mw.done_qty * a.total_labor) / 3600,0),5)) OVER (partition by a.sbu), 0)) eh_percentage_per_mo,
    //       a.mo_status,
    //       CASE WHEN a.status ='INVOICED' THEN 'true' else 'false' end inv_status
    //     FROM set_status a
    //     LEFT JOIN mrp_workorder mw ON mw.id = a.max_wo order by eh_percentage_per_mo";
    $qmos = "	with mos as (SELECT DISTINCT MO FROM M_ACC_DIST_MO WHERE DATE_RANGE_ID !=$month_id AND NOT IS_INVOICED),
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
        WHERE MP.STATE != 'cancel'
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
           LEFT JOIN account_move am ON am.id = amspb.account_move_id and am.invoice_date <= TO_DATE('$last_date_of_month','MM/DD/YYYY') and am.state !='cancel'
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
      -- fm.status != 'INVOICED BEFORE MONTH END' AND 
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
right join mos on mos.mo = mp.name
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
mp.state ";
    $resultmos = $db->fetchAll($qmos);

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
            'DATE_RANGE_ID' => $month_id,
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

        // $db_ken->insert('M_ACC_TO_WIP', [
        //     'MAIN_ID' => $old_accrual_id,
        //     'ACCOUNT_CODE' => $itemToWip['account_code'],
        //     'ACCOUNT_ID' => $itemToWip['account_id'],
        //     'CREDIT_ACCOUNT_ID' => $itemToWip['credit_account_id'],
        //     'ANALYTIC_ACCOUNT' => $itemToWip['analytic_account'],
        //     'ANALYTIC_ACCOUNT_ID' => $itemToWip['analytic_account_id'] ?: null,
        //     'MOS' => $itemToWip['mos'],
        //     'DEBIT' => $itemToWip['debit'] ?: null,
        //     'CREDIT' => $itemToWip['credit'] ?: null,
        //     'ITEM_LABEL' => $itemToWip['item_label'],
        //     // 'RAW_DEBIT' => $itemToWip['raw_debit'] ?: null,
        //     // 'RAW_CREDIT' => $itemToWip['raw_credit'] ?: null,
        //     'ADDED_BY' => $user,
        //     'SBU' => $itemToWip['sbu']
        // ]);
        $resultLineItems = $db_ken->insert('M_ACC_DIST_MO', $dataMoEntries);
    }
    // exit;
    $q = "WITH categ_percentage as (
       SELECT 
    act.id act_id,
        act.acc_category,
        coalesce(sum(acd.distribution_percentage),0) acc_categ_percentage,
	--ACD.distribution_percentage,
        act.journal_id
        FROM m_acc_category_tbl act
		LEFT JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACC_CATEGORY_ID = ACT.ID
        left join m_acc_cost_distribution acd on acd.m_acc_category_id =ACA.ACC_CATEGORY_ID AND ACD.DEBIT_TO = ACA.ACCOUNT_ID
        group by  act.id, act.acc_category			  
    )
	   , detailed_percentage as (
        select 
		acd.distribution_percentage ,
        acd.m_acc_category_id,
        acd.analytic_account_id,
        adg.dept_group,
        ADG.ID DEPT_GROUP_ID,
        aaa.name dept 
		,aaa.code,
        acd.debit_to,
        acd.wip_account,
        cp.journal_id
        from
        m_acc_cost_distribution acd
     	left JOIN account_analytic_account aaa ON aaa.id = acd.analytic_account_id
         left join m_acc_department_groups adg on adg.id =aaa.m_acc_group_id
		  LEFT JOIN categ_percentage CP ON CP.ACT_ID = ACD.m_acc_category_id 
        --  left join M_ACC_ACC_TAGGING aat on aat.dept_group_id = adg.id
		   WHERE CP.acc_categ_percentage = 100
		 order by m_acc_category_id, dept_group
		    )
	 ,accrual_entry as(
SELECT 
		 maa.id accrual_id,
MAA.TOTAL_ACCRUAL_VALUE total_debit,
	DP.distribution_percentage,
	trunc(MAA.TOTAL_ACCRUAL_VALUE * (DP.distribution_percentage/100),2) allocation_trunc,
	MAA.TOTAL_ACCRUAL_VALUE * (DP.distribution_percentage/100) allocation,
	sum(trunc(MAA.TOTAL_ACCRUAL_VALUE * (DP.distribution_percentage/100),2)) over (partition by MAA.ID) total_allocation_trunc,
		sum(MAA.TOTAL_ACCRUAL_VALUE * (DP.distribution_percentage/100)) over (partition by MAA.ID) total_allocation,
 	DP.dept_group,
     DP.dept_group_ID,
 	DP.dept,
 		DP.analytic_account_id,
		maa.credit_to,
		dp.debit_to,
        dp.wip_account,
        dp.journal_id
FROM
		 M_ACC_ACCRUAL MAA
		 JOIN detailed_percentage DP ON DP.m_acc_category_id =MAA.DIST_CATEG_ID
         WHERE MAA.date_range_id =$month_id
		 )
		 	, ranked as(
		select
ae.accrual_id,
	ae.total_debit,
	ae.distribution_percentage,
	ae.allocation - ae.allocation_trunc allocation_diff,
	ae.allocation_trunc,
	((ae.total_allocation - ae.total_allocation_trunc)/0.01)::integer rows_to_adjust,
	ROW_NUMBER() OVER (PARTITION BY ae.accrual_id ORDER BY ae.allocation - ae.allocation_trunc DESC) AS rn,
	ae.dept_group,
    ae.dept_group_ID,
	ae.dept,
			ae.analytic_account_id,
				ae.debit_to,
                ae.wip_account,
                ae.journal_id
	from accrual_entry ae)
	, final_DEPT_DIST as (
	select 
	accrual_id,
	distribution_percentage,
	 CASE 
            WHEN rn <= rows_to_adjust THEN allocation_trunc + 0.01
            ELSE allocation_trunc
        END AS debit_final,
	dept_group,
    dept_group_ID,
	dept,
		analytic_account_id,
		debit_to,
        wip_account,
        journal_id
	from 
	ranked
	)
	, debit_credit_DIST as(
	select
	fem.accrual_id,
	fem.distribution_percentage,
	fem.debit_final debit,
	0::numeric credit,
	fem.dept_group,
		fem.dept,
		fem.analytic_account_id,
 		fem.debit_to ACCOUNT_ID,
        fem.wip_account,
        fem.journal_id
	from
	final_DEPT_DIST fem
		-----FOR CREDIT
	union
	select
	je.id accrual_id,
	0::numeric distribution_percentage,
	0::numeric debit,
	je.total_accrual_value credit,
	'' dept_group,
   '' dept,
		null::integer analytic_account_id,
         JE.credit_to account_id,
         null::integer wip_account,
         mact.journal_id
--         je.ACCOUNT_ID main_account_id
	from
	m_acc_accrual je
    join m_acc_category_tbl mact on mact.id = je.dist_categ_id
    where je.date_range_id = $month_id
	)
	select 
	je.id accrual_id,
	--je.journal_entry,
	--je.ref reference,
	aj.name journal,
 	dcem.journal_id,
	-- je.account_code,
	-- je.account_id,
    AA.CODE ACCOUNT_CODE,
	DCEM.account_id,
-- 	je.item_label,
'$last_date_of_month' DATE,
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
    dcem.wip_account wip_account_id
--     aa_main.code main_account_code,
-- 	dcem.main_account_id
	from
	debit_credit_DIST dcem
	join m_acC_accrual je on je.id = dcem.accrual_id-- and dcem.account_id = je.account_id
    LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID =DCEM.ACCOUNT_ID
    left join account_journal aj on aj.id = dcem.journal_id
 --   left join account_account aa_main on aa_main.id = dcem.main_account_id
	ORDER BY accrual_id";
    // echo $q;
    // exit;
    $result = $db->fetchAll($q);
    // echo '<pre>';
    // var_dump($result);
    // exit;
}

// $monthYear = substr($from_date, 0, 7);

// echo $from_date;
// exit;






// echo '<pre>';

if ($result) {
    // echo 'meron';

    $db->query("UPDATE M_ACC_DATE_RANGE SET is_dept_distributed = TRUE WHERE ID = $month_id");

    $old_accrual_id = '';
    try {
        // START TRANSACTION
        $db_ken->beginTransaction();

        $old_accrual_id = 0;

        foreach ($result as $item) {

            $accrual_id = $item['accrual_id'];
            $account_code = $item['account_code'];
            $account_id = $item['account_id'];
            $analytic_account = $item['analytic_account'];
            $analytic_account_id = $item['analytic_account_id'] ?: null;
            $distribution_percentage = $item['distribution_percentage'] ?: null;
            $debit = $item['debit'] ?: null;
            $credit = $item['credit'] ?: null;
            $account_move_date = $item['date'];
            $sbu = $item['sbu'];
            $wip_account_id = $item['wip_account_id'] ?: null;
            $journal = $item['journal'];
            $journal_id = $item['journal_id'] ?: null;
            $date = $item['date'];

            // IF accrual_id changed → process update and WIP
            if ($accrual_id != $old_accrual_id) {

                if ($old_accrual_id != 0) {
                    // UPDATE old accrual
                    $db_ken->query(
                        "UPDATE M_ACC_ACCRUAL SET JOURNAL_ID=$1, JOURNAL_NAME=$2, DATE=$3 WHERE ID=$4",
                        [$journal_id, $journal, $date, $accrual_id]
                    );

                    // INSERT TO WIP
                    $qToWip = insertToWip($old_accrual_id, $month_id);
                    $resultToWip = $db_ken->fetchAll($qToWip);

                    foreach ($resultToWip as $itemToWip) {
                        $db_ken->insert('M_ACC_TO_WIP', [
                            'MAIN_ID' => $old_accrual_id,
                            'ACCOUNT_CODE' => $itemToWip['account_code'],
                            'ACCOUNT_ID' => $itemToWip['account_id'],
                            'CREDIT_ACCOUNT_ID' => $itemToWip['credit_account_id'],
                            'ANALYTIC_ACCOUNT' => $itemToWip['analytic_account'],
                            'ANALYTIC_ACCOUNT_ID' => $itemToWip['analytic_account_id'] ?: null,
                            'MOS' => $itemToWip['mos'],
                            'DEBIT' => $itemToWip['debit'] ?: null,
                            'CREDIT' => $itemToWip['credit'] ?: null,
                            'ITEM_LABEL' => $itemToWip['item_label'],
                            // 'RAW_DEBIT' => $itemToWip['raw_debit'] ?: null,
                            // 'RAW_CREDIT' => $itemToWip['raw_credit'] ?: null,
                            'ADDED_BY' => $user,
                            'SBU' => $itemToWip['sbu']
                        ]);
                    }
                } else {
                    // first time update
                    $db_ken->query(
                        "UPDATE M_ACC_ACCRUAL SET JOURNAL_ID=$1, JOURNAL_NAME=$2, DATE=$3 WHERE ID=$4",
                        [$journal_id, $journal, $date, $accrual_id]
                    );
                }
            }

            // INSERT ACCOUNT DISTRIBUTION LINE
            $db_ken->insert('M_ACC_ACCRUAL_DIST', [
                'ACCRUAL_ID' => $accrual_id,
                'ACCOUNT_CODE' => $account_code,
                'ACCOUNT_ID' => $account_id,
                'ANALYTIC_ACCOUNT' => $analytic_account,
                'ANALYTIC_ACCOUNT_ID' => $analytic_account_id,
                'DISTRIBUTION_PERCENTAGE' => $distribution_percentage,
                'DEBIT' => $debit,
                'CREDIT' => $credit,
                'ADDED_BY' => $user,
                'SBU' => $sbu,
                'WIP_ACCOUNT_ID' => $wip_account_id
            ]);

            $old_accrual_id = $accrual_id;
        }


        // $qToWipLastRecord = insertToWip($accrual_id, $month_id);
        // // echo 'insert to wip last';
        // $resultLastToWip = $db->fetchAll($qToWipLastRecord);

        // foreach ($resultLastToWip as $itemToWip) {

        //     $tw_account_code = $itemToWip['account_code'];
        //     $tw_account_id = $itemToWip['account_id'];
        //     $tw_credit_account_id = $itemToWip['credit_account_id'];
        //     $tw_analytic_account = $itemToWip['analytic_account'];
        //     $tw_analytic_account_id = $itemToWip['analytic_account_id'] != '' && $itemToWip['analytic_account_id'] ? $itemToWip['analytic_account_id']  : 'NULL::INTEGER';
        //     $tw_mos = $itemToWip['mos'];
        //     $tw_sbu = $itemToWip['sbu'];
        //     $tw_debit = $itemToWip['debit'] != '' && $itemToWip['debit']  ? $itemToWip['debit'] : 'NULL::NUMERIC';
        //     $tw_credit = $itemToWip['credit'] != '' && $itemToWip['credit']  ? $itemToWip['credit'] : 'NULL::NUMERIC';

        //     $dataLineToWip = [
        //         'MAIN_ID' => $accrual_id,
        //         'ACCOUNT_CODE' => "'$tw_account_code'",
        //         'ACCOUNT_ID' => $tw_account_id,
        //         'CREDIT_ACCOUNT_ID' => $tw_credit_account_id,
        //         'ANALYTIC_ACCOUNT' => "'$tw_analytic_account'",
        //         'ANALYTIC_ACCOUNT_ID' => $tw_analytic_account_id,
        //         'MOS' => "'$tw_mos'",
        //         'DEBIT' => $tw_debit,
        //         'CREDIT' => $tw_credit,
        //         'ADDED_BY' => $user,
        //         'SBU' => "'$tw_sbu'"
        //     ];
        //     $db->insert($dataLineToWip, 'M_ACC_TO_WIP');
        //     // echo 'narurun';
        // }


        // INSERT TO WIP
        $qToWipLastRecord = insertToWip($accrual_id, $month_id);
        $resultLastToWip = $db_ken->fetchAll($qToWipLastRecord);

        foreach ($resultLastToWip as $itemToWip) {
            $db_ken->insert('M_ACC_TO_WIP', [
                'MAIN_ID' => $accrual_id,
                'ACCOUNT_CODE' => $itemToWip['account_code'],
                'ACCOUNT_ID' => $itemToWip['account_id'],
                'CREDIT_ACCOUNT_ID' => $itemToWip['credit_account_id'],
                'ANALYTIC_ACCOUNT' => $itemToWip['analytic_account'],
                'ANALYTIC_ACCOUNT_ID' => $itemToWip['analytic_account_id'] ?: null,
                'MOS' => $itemToWip['mos'],
                'DEBIT' => $itemToWip['debit'] ?: null,
                'CREDIT' => $itemToWip['credit'] ?: null,
                'ITEM_LABEL' => $itemToWip['item_label'],
                // 'RAW_DEBIT' => $itemToWip['raw_debit'] ?: null,
                // 'RAW_CREDIT' => $itemToWip['raw_credit'] ?: null,
                'ADDED_BY' => $user,
                'SBU' => $itemToWip['sbu']
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
    // //USE INSERT GET ID
    // foreach ($result as $item) {    
    //     $accrual_id = $item['accrual_id'];
    //     $account_code = $item['account_code'];
    //     $account_id = $item['account_id'];
    //     $analytic_account = $item['analytic_account'];
    //     $analytic_account_id = $item['analytic_account_id'] != '' && $item['analytic_account_id'] ? $item['analytic_account_id']  : 'NULL::INTEGER';
    //     $distribution_percentage = $item['distribution_percentage'] != '' && $item['distribution_percentage']  ? $item['distribution_percentage'] : 'NULL::NUMERIC';
    //     $debit = $item['debit'] != '' && $item['debit']  ? $item['debit'] : 'NULL::NUMERIC';
    //     $credit = $item['credit'] != '' && $item['credit']  ? $item['credit'] : 'NULL::NUMERIC';
    //     $account_move_date = $item['date'];
    //     $sbu = $item['sbu'];
    //     $wip_account_id = $item['wip_account_id'] != '' &&  $item['wip_account_id'] ?  $item['wip_account_id'] : 'NULL::INTEGER';
    //     $journal = $item['journal'];
    //     $journal_id = $item['journal_id'] != '' && $item['journal_id'] ? $item['journal_id'] : 'NULL::INTEGER';
    //     $date = $item['date'];
    //     // echo $credit;
    //     // exit;

    //     if ($accrual_id != (isset($old_accrual_id) ? $old_accrual_id : 0)) { // ← if this is not the same journal_entry
    //         // echo 'insert wip';
    //         // exit;
    //         ///////////////////////// TO WIP ACCOUNT
    //         if ((isset($old_accrual_id) ? $old_accrual_id : 0) != 0) {

    //             $updateAccrualQ = "UPDATE M_ACC_ACCRUAL SET JOURNAL_ID = $journal_id, JOURNAL_NAME = '$journal', DATE =  '$date' WHERE ID =$accrual_id ";
    //             $db->query($updateAccrualQ);


    //             $qToWip = insertToWip($old_accrual_id, $month_id);
    //             $resultToWip = $db->fetchAll($qToWip);

    //             foreach ($resultToWip as $itemToWip) {

    //                 $tw_account_code = $itemToWip['account_code'];
    //                 $tw_account_id = $itemToWip['account_id'];
    //                 $tw_analytic_account = $itemToWip['analytic_account'];
    //                 $tw_analytic_account_id = $itemToWip['analytic_account_id'] != '' && $itemToWip['analytic_account_id'] ? $itemToWip['analytic_account_id']  : 'NULL::INTEGER';
    //                 $tw_mos = $itemToWip['mos'];
    //                 $tw_sbu = $itemToWip['sbu'];
    //                 $tw_debit = $itemToWip['debit'] != '' && $itemToWip['debit']  ? $itemToWip['debit'] : 'NULL::NUMERIC';
    //                 $tw_credit = $itemToWip['credit'] != '' && $itemToWip['credit']  ? $itemToWip['credit'] : 'NULL::NUMERIC';

    //                 $dataLineToWip = [
    //                     'MAIN_ID' => $old_accrual_id,
    //                     'ACCOUNT_CODE' => "'$tw_account_code'",
    //                     'ACCOUNT_ID' => $tw_account_id,
    //                     'ANALYTIC_ACCOUNT' => "'$tw_analytic_account'",
    //                     'ANALYTIC_ACCOUNT_ID' => $tw_analytic_account_id,
    //                     'MOS' => "'$tw_mos'",
    //                     'DEBIT' => $tw_debit,
    //                     'CREDIT' => $tw_credit,
    //                     'ADDED_BY' => $user,
    //                     'SBU' => "'$tw_sbu'"
    //                 ];
    //                 $db->insert($dataLineToWip, 'M_ACC_TO_WIP');
    //             }
    //         } else {
    //             $updateAccrualQ = "UPDATE M_ACC_ACCRUAL SET JOURNAL_ID = $journal_id, JOURNAL_NAME = '$journal', DATE =  '$date' WHERE ID =$accrual_id ";
    //             // echo $updateAccrualQ;
    //             // exit;
    //             $db->query($updateAccrualQ);
    //         }

    //         /////////////////////// TO WIP ACCOUNT
    //     }

    //     $dataLineItems = [
    //         'ACCRUAL_ID' => $accrual_id,
    //         'ACCOUNT_CODE' => "'$account_code'",
    //         'ACCOUNT_ID' => $account_id,
    //         'ANALYTIC_ACCOUNT' => "'$analytic_account'",
    //         'ANALYTIC_ACCOUNT_ID' => $analytic_account_id,
    //         'DISTRIBUTION_PERCENTAGE' => $distribution_percentage,
    //         'DEBIT' => $debit,
    //         'CREDIT' => $credit,
    //         'ADDED_BY' => $user,
    //         'SBU' => "'$sbu'",
    //         'WIP_ACCOUNT_ID' => $wip_account_id
    //     ];
    //     $resultLineItems = $db->insert($dataLineItems, 'M_ACC_ACCRUAL_DIST');

    //     $old_accrual_id = $item["accrual_id"];
    // }
    /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

} else {
    echo '<pre>';
    echo $q;
    exit;
}

function insertToWip($previous_main_id, $month_id)
{
    /////// ------------------------------JOINED ITO
    //     $qToWip = "
    //with not_tally as(
    //         select
    //         maa.id accrual_id,
    //     adm.mo,
    //     adm.device,
    //     adm.category,
    //     adm.customer_name,
    //     adm.earned_hrs,
    //     aad.debit *
    //     CASE WHEN MACT.mo_pct_ref = 'EH' THEN adm.EH_percentage
    //     ELSE QTY_PERCENTAGE END allocation,
    //     aad.sbu,
    //         adm.is_invoiced,
    //         aad.ACCOUNT_CODE,
    //         aad.ACCOUNT_ID,
    //       aad.ANALYTIC_ACCOUNT,
    //         aad.ANALYTIC_ACCOUNT_ID,
    //         aad.wip_account_id
    //     from 
    //     m_acc_date_range adr
    //         join M_ACC_ACCRUAL maa on maa.date_range_id = adr.ID
    //         join M_ACC_ACCRUAL_DIST aad on aad.accrual_id = maa.id
    //     join account_analytic_account aaa on aaa.id =aad.analytic_account_id
    //     join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
    //     join m_acc_dist_mo adm on adm.sbu =aad.sbu and adm.date_range_id = adr.id
    //     JOIN ACCOUNT_ACCOUNT   AA ON AA.ID = aad.ACCOUNT_ID
    // 	JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACCOUNT_ID = AA.ID and aca.acc_category_id = maa.dist_categ_id
    //     JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.acc_category_id
    //     where adr.id = $month_id and 
    //     maa.id in ($previous_main_id) and adg.dept_group ='MANUFACTURING/PRODUCT LINE'
    //     )
    //     ,MO_RANKED AS (
    //     SELECT 
    //     nt.accrual_id,
    //     NT.MO,
    //     NT.DEVICE,
    //     NT.CATEGORY,
    //     NT.ALLOCATION,
    //     NT.CUSTOMER_NAME,
    //     NT.earned_hrs,
    //     TRUNC(NT.ALLOCATION,5) TRUNC_ALLOCATION,
    //     SUM(NT.ALLOCATION) OVER(partition by nt.accrual_id) TOTAL_ALLOCATION,
    //     SUM(TRUNC(NT.ALLOCATION,5)) OVER(partition by nt.accrual_id) TOTAL_TRUNC_ALLOCATION,
    //     (
    //     NT.ALLOCATION- TRUNC(NT.ALLOCATION,5)
    //     ) ALLOCATION_DIFF,
    //     ((
    //     SUM(NT.ALLOCATION) OVER(partition by nt.accrual_id)- SUM(TRUNC(NT.ALLOCATION,5)) OVER(partition by nt.accrual_id)
    //     )/ 0.00001)::INTEGER ROWS_TO_ADJUST,
    //     ROW_NUMBER() OVER (partition by nt.accrual_id ORDER BY NT.ALLOCATION - TRUNC(NT.ALLOCATION,5) DESC) AS rn,
    //     nt.sbu,
    //     nt.is_invoiced,
    //     nt.ACCOUNT_CODE,
    //         nt.ACCOUNT_ID,
    //         nt.ANALYTIC_ACCOUNT,
    //         nt.ANALYTIC_ACCOUNT_ID,
    //         nt.wip_account_id
    //     FROM 
    //     NOT_TALLY NT
    //     )
    //     , allocation_adjusted as (
    //     SELECT 
    //                         accrual_id,
    //     MO,
    //     DEVICE,
    //     CATEGORY,
    //     CUSTOMER_NAME,
    //     EARNED_HRS,
    //     CASE 
    //     WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
    //     ELSE TRUNC_ALLOCATION
    //     END AS ALLOCATION,
    //                         total_allocation,
    //     sbu,
    //     is_invoiced,
    //                           ACCOUNT_CODE,
    //         ACCOUNT_ID,
    //         ANALYTIC_ACCOUNT,
    //         ANALYTIC_ACCOUNT_ID,
    //         WIP_aCCOUNT_ID
    //     FROM
    //     MO_RANKED
    //                              )
    //                             ,RAW_DATA AS (
    //     select 
    //     accrual_id,
    //     string_Agg(DISTINCT mo,',') mos,
    //     round(sum(allocation),2) allocation,
    //     is_invoiced,
    //     sbu,
    //     ACCOUNT_CODE CREDIT_ACCOUNT_CODE,
    //     ACCOUNT_ID CREDIT_ACCOUNT_ID,
    //         ANALYTIC_ACCOUNT,
    //         ANALYTIC_ACCOUNT_ID,
    //         AADD.WIP_ACCOUNT_ID ACCOUNT_ID,
    //         AA.CODE ACCOUNT_CODE
    //     from
    //     allocation_adjusted aadd
    //     LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AADD.WIP_ACCOUNT_ID
    //     where --not is_invoiced
    //      is_invoiced
    //     group by accrual_id,sbu,is_invoiced, total_allocation,
    //         ANALYTIC_ACCOUNT,
    //         ANALYTIC_ACCOUNT_ID,
    //           ACCOUNT_CODE,
    //         ACCOUNT_ID,
    //         AADD.WIP_ACCOUNT_ID,
    //         AA.CODE,
    //         AADD.WIP_ACCOUNT_ID
    //     order by accrual_id,sbu,is_invoiced
    //     )
    // 	, FINAL_DATA AS(
    // 		--------
    // 		SELECT
    // 		A.ACCRUAL_ID,
    // 		A.MOS,
    // 		A.ALLOCATION,
    // 		A.IS_INVOICED,
    // 		A.SBU,
    // 		A.CREDIT_ACCOUNT_CODE,
    // 		A.CREDIT_ACCOUNT_ID,
    // 		A.ANALYTIC_ACCOUNT,
    //         A.ANALYTIC_ACCOUNT_ID,
    // 		A.ACCOUNT_ID,
    // 		A.ACCOUNT_CODE,
    // 		a.allocation + coalesce(ROUND(SUM(B.ALLOCATION),2),0) new_allocation
    // 		FROM
    // 		RAW_DATA A
    // 		left join (
    // 	select  sum(coalesce(aml.actual_allocation,aml.reversed_allocation)) allocation, am.mo, am.sbu, am.mo_status from m_acc_dist_mo am
    // 	left join m_acC_dist_mo_lines aml on aml.dist_mo_id = am.id
    // 	WHERE date_range_id != 17 and not is_invoiced
    // 	and coalesce(aml.actual_allocation,aml.reversed_allocation) is not null
    // 	group by mo, sbu, mo_status
    // ) b on  A.mos  LIKE  '%'||  b.mo || '%' AND UPPER(B.SBU) =UPPER(A.SBU)
    // GROUP BY
    // 		A.ACCRUAL_ID,
    // 		A.MOS,
    // 		A.ALLOCATION,
    // 		A.IS_INVOICED,
    // 		A.SBU,
    // 		A.CREDIT_ACCOUNT_CODE,
    // 		A.CREDIT_ACCOUNT_ID,
    // 		A.ANALYTIC_ACCOUNT,
    //         A.ANALYTIC_ACCOUNT_ID,
    // 		A.ACCOUNT_ID,
    // 		A.ACCOUNT_CODE
    // 	)
    //     SELECT 
    //     FD.ACCOUNT_CODE,
    //     FD.ACCOUNT_ID,
    //     FD.CREDIT_ACCOUNT_ID,
    // 		REPLACE(FD.ANALYTIC_ACCOUNT, '''', '''''') ANALYTIC_ACCOUNT,
    //     FD.ANALYTIC_ACCOUNT_ID,
    //     FD.MOS,
    //     FD. new_ALLOCATION DEBIT,
    //     0 CREDIT,
    // 	FD.ALLOCATION RAW_DEBIT,
    // 	0 RAW_CREDIT,
    //     FD.SBU
    //     FROM FINAL_DATA FD
    //     UNION ALL
    //     SELECT 
    //     FD2.CREDIT_ACCOUNT_CODE ACCOUNT_CODE,
    //     FD2.CREDIT_ACCOUNT_ID ACCOUNT_ID,
    //     FD2.CREDIT_ACCOUNT_ID,
    //     NULL ANALYTIC_ACCOUNT,
    //     NULL ANALYTIC_ACCOUNT_ID,
    //     NULL MOS,
    //     0 DEBIT,
    //     SUM(FD2.new_ALLOCATION) CREDIT,
    // 	0 RAW_DEBIT,
    // 	SUM(FD2.ALLOCATION) RAW_CREDIT,
    //     '' SBU
    //     FROM FINAL_DATA FD2
    //     GROUP BY
    //     FD2.CREDIT_ACCOUNT_CODE,
    //     FD2.CREDIT_ACCOUNT_ID
    // ";

    // echo $qToWip;
    // exit;
    /////// ------------------------------ORIGINAL ITO
    // $qToWip = "with not_tally as(
    //     select
    //     maa.id accrual_id,
    // adm.mo,
    // adm.device,
    // adm.category,
    // adm.customer_name,
    // adm.earned_hrs,
    // aad.debit *
    // CASE WHEN MACT.mo_pct_ref = 'EH' THEN adm.EH_percentage
    // ELSE QTY_PERCENTAGE END allocation,
    // aad.sbu,
    //     adm.is_invoiced,
    //     aad.ACCOUNT_CODE,
    //     aad.ACCOUNT_ID,
    //   aad.ANALYTIC_ACCOUNT,
    //     aad.ANALYTIC_ACCOUNT_ID,
    //     aad.wip_account_id
    // from 
    // m_acc_date_range adr
    //     join M_ACC_ACCRUAL maa on maa.date_range_id = adr.ID
    //     join M_ACC_ACCRUAL_DIST aad on aad.accrual_id = maa.id
    // join account_analytic_account aaa on aaa.id =aad.analytic_account_id
    // join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
    // join m_acc_dist_mo adm on adm.sbu =aad.sbu and adm.date_range_id = adr.id
    // JOIN ACCOUNT_ACCOUNT   AA ON AA.ID = aad.ACCOUNT_ID
    // JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACCOUNT_ID = AA.ID and aca.acc_category_id = maa.dist_categ_id
    // JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.acc_category_id
    // where adr.id = $month_id and 
    // maa.id in ($previous_main_id) and adg.dept_group ='MANUFACTURING/PRODUCT LINE'
    // )
    // ,MO_RANKED AS (
    // SELECT 
    // nt.accrual_id,
    // NT.MO,
    // NT.DEVICE,
    // NT.CATEGORY,
    // NT.ALLOCATION,
    // NT.CUSTOMER_NAME,
    // NT.earned_hrs,
    // TRUNC(NT.ALLOCATION,5) TRUNC_ALLOCATION,
    // SUM(NT.ALLOCATION) OVER(partition by nt.accrual_id) TOTAL_ALLOCATION,
    // SUM(TRUNC(NT.ALLOCATION,5)) OVER(partition by nt.accrual_id) TOTAL_TRUNC_ALLOCATION,
    // (
    // NT.ALLOCATION- TRUNC(NT.ALLOCATION,5)
    // ) ALLOCATION_DIFF,
    // ((
    // SUM(NT.ALLOCATION) OVER(partition by nt.accrual_id)- SUM(TRUNC(NT.ALLOCATION,5)) OVER(partition by nt.accrual_id)
    // )/ 0.00001)::INTEGER ROWS_TO_ADJUST,
    // ROW_NUMBER() OVER (partition by nt.accrual_id ORDER BY NT.ALLOCATION - TRUNC(NT.ALLOCATION,5) DESC) AS rn,
    // nt.sbu,
    // nt.is_invoiced,
    // nt.ACCOUNT_CODE,
    //     nt.ACCOUNT_ID,
    //     nt.ANALYTIC_ACCOUNT,
    //     nt.ANALYTIC_ACCOUNT_ID,
    //     nt.wip_account_id
    // FROM 
    // NOT_TALLY NT
    // )
    // , allocation_adjusted as (
    // SELECT 
    //                     accrual_id,
    // MO,
    // DEVICE,
    // CATEGORY,
    // CUSTOMER_NAME,
    // EARNED_HRS,
    // CASE 
    // WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
    // ELSE TRUNC_ALLOCATION
    // END AS ALLOCATION,
    //                     total_allocation,
    // sbu,
    // is_invoiced,
    //                       ACCOUNT_CODE,
    //     ACCOUNT_ID,
    //     ANALYTIC_ACCOUNT,
    //     ANALYTIC_ACCOUNT_ID,
    //     WIP_aCCOUNT_ID
    // FROM
    // MO_RANKED
    //                          )
    //                          ,FINAL_DATA AS (
    // select 
    // accrual_id,
    // string_Agg(DISTINCT mo,',') mos,
    // round(sum(allocation),2) allocation,
    // is_invoiced,
    // sbu,
    // ACCOUNT_CODE CREDIT_ACCOUNT_CODE,
    // ACCOUNT_ID CREDIT_ACCOUNT_ID,
    //     ANALYTIC_ACCOUNT,
    //     ANALYTIC_ACCOUNT_ID,
    //     AADD.WIP_ACCOUNT_ID ACCOUNT_ID,
    //     AA.CODE ACCOUNT_CODE
    // from
    // allocation_adjusted aadd
    // LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AADD.WIP_ACCOUNT_ID
    // where --not is_invoiced
    //  is_invoiced
    // group by accrual_id,sbu,is_invoiced, total_allocation,
    //     ANALYTIC_ACCOUNT,
    //     ANALYTIC_ACCOUNT_ID,
    //       ACCOUNT_CODE,
    //     ACCOUNT_ID,
    //     AADD.WIP_ACCOUNT_ID,
    //     AA.CODE,
    //     AADD.WIP_ACCOUNT_ID
    // order by accrual_id,sbu,is_invoiced
    // )
    // SELECT 
    // FD.ACCOUNT_CODE,
    // FD.ACCOUNT_ID,
    // FD.CREDIT_ACCOUNT_ID,
    // REPLACE(FD.ANALYTIC_ACCOUNT, '''', '''''') ANALYTIC_ACCOUNT,
    // FD.ANALYTIC_ACCOUNT_ID,
    // FD.MOS,
    // FD. ALLOCATION DEBIT,
    // 0 CREDIT,
    // FD.SBU
    // FROM FINAL_DATA FD
    // UNION ALL
    // SELECT 
    // FD2.CREDIT_ACCOUNT_CODE ACCOUNT_CODE,
    // FD2.CREDIT_ACCOUNT_ID ACCOUNT_ID,
    // FD2.CREDIT_ACCOUNT_ID,
    // NULL ANALYTIC_ACCOUNT,
    // NULL ANALYTIC_ACCOUNT_ID,
    // NULL MOS,
    // 0 DEBIT,
    // SUM(FD2.ALLOCATION) CREDIT,
    // '' SBU
    // FROM FINAL_DATA FD2
    // GROUP BY
    // FD2.CREDIT_ACCOUNT_CODE,
    // FD2.CREDIT_ACCOUNT_ID
    // ";


    /////// ------------------------------HINDI ITO JOINED
    $qToWip = "
    with not_tally as(
        select
        maa.id accrual_id,
    adm.mo,
    adm.device,
    adm.category,
    adm.customer_name,
    adm.earned_hrs,
    aad.debit *
    CASE WHEN MACT.mo_pct_ref = 'EH' THEN adm.EH_percentage
    ELSE QTY_PERCENTAGE END allocation,
    aad.sbu,
        adm.is_invoiced,
        aad.ACCOUNT_CODE,
        aad.ACCOUNT_ID,
      aad.ANALYTIC_ACCOUNT,
        aad.ANALYTIC_ACCOUNT_ID,
        aad.wip_account_id,
        adm.invoiced_qty,
        adm.mo_done_qty
    from 
    m_acc_date_range adr
        join M_ACC_ACCRUAL maa on maa.date_range_id = adr.ID
        join M_ACC_ACCRUAL_DIST aad on aad.accrual_id = maa.id
    join account_analytic_account aaa on aaa.id =aad.analytic_account_id
    join m_acc_depARTMENT_groups adg on adg.id = aaa.m_acc_group_id
    join m_acc_dist_mo adm on adm.sbu =aad.sbu and adm.date_range_id = adr.id
    JOIN ACCOUNT_ACCOUNT   AA ON AA.ID = aad.ACCOUNT_ID
    JOIN M_ACC_CATEGORY_ACCOUNTS ACA ON ACA.ACCOUNT_ID = AA.ID and aca.acc_category_id = maa.dist_categ_id
    JOIN M_ACC_CATEGORY_TBL MACT ON MACT.ID =ACA.acc_category_id
    where adr.id = $month_id and aad.wip_account_id is not null and
    adm.is_invoiced and
    maa.id in ($previous_main_id) and adg.dept_group ='MANUFACTURING/PRODUCT LINE'
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
        nt.invoiced_qty,
        nt.mo_done_qty
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
        WIP_aCCOUNT_ID,
        invoiced_qty,
		mo_done_qty
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
		'' reference
    from
    allocation_adjusted aadd
    LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AADD.WIP_ACCOUNT_ID
    where --not is_invoiced
     is_invoiced
    group by accrual_id,sbu,is_invoiced, total_allocation,
        ANALYTIC_ACCOUNT,
        ANALYTIC_ACCOUNT_ID,
          ACCOUNT_CODE,
        ACCOUNT_ID,
        AADD.WIP_ACCOUNT_ID,
        AA.CODE,
        AADD.WIP_ACCOUNT_ID
union all							 
select  
    	im.accrual_id accrual_id,
    	string_Agg(DISTINCT am.mo,',') mos,
    	-- round(sum(coalesce(aml.actual_allocation,aml.accrual_allocation)),2) allocation, 
        round(sum( (im.invoiced_qty/im.mo_done_qty) * coalesce(aml.actual_allocation,aml.accrual_allocation)),2) allocation, 
		true is_invoiced,
    	am.sbu, 
    	IM.ACCOUNT_CODE CREDIT_ACCOUNT_CODE,
    	IM.ACCOUNT_ID CREDIT_ACCOUNT_ID,
        IM.ANALYTIC_ACCOUNT,
        IM.ANALYTIC_ACCOUNT_ID,
        IM.WIP_ACCOUNT_ID ACCOUNT_ID,
        AA.CODE ACCOUNT_CODE,
		'From WIP of Previous Months' reference
    	from m_acc_dist_mo am
    	left join m_acc_dist_mo_lines aml on aml.dist_mo_id = am.id
    	join m_acc_accrual maa on maa.id = aml.accrual_id
    	join M_ACC_ACCRUAL_DIST mad on mad.accrual_id = aml.accrual_id and upper(mad.sbu) = upper(am.sbu)
    	join M_ACC_CATEGORY_TBL mact on mact.id = maa.dist_categ_id
    	JOIN allocation_adjusted IM ON IM.MO =AM.MO AND IM.WIP_ACCOUNT_ID = MAD.wip_account_id
		LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = IM.WIP_ACCOUNT_ID
    	WHERE am.date_range_id != $month_id and not am.is_invoiced
    	and coalesce(aml.actual_allocation,aml.accrual_allocation) is not null
    	group by im.accrual_id, am.sbu,IM.ACCOUNT_CODE,IM.ACCOUNT_ID,IM.ANALYTIC_ACCOUNT,IM.ANALYTIC_ACCOUNT_ID,IM.WIP_ACCOUNT_ID,aa.CODE
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
    return $qToWip;
}
// var_dump($result);
// exit;

echo json_encode($month_id);
