<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Postgresql();


// $response = ['data' => []];


$amountTotal = isset($_POST['amountTotal']) ? $_POST['amountTotal'] : 0;
$am_id = $_POST['amId'];
$acc_category = $_POST['accCategory'];
if (isset($_POST['fromDate']) && isset($_POST['toDate']) && !empty($_POST['fromDate']) && !empty($_POST['toDate'])) {
    $fromDate = $_POST['fromDate'];
    $date = new DateTime($fromDate);

    // First day of the month
    $firstDay = $date->format('Y-m-01');

    // Last day of the month
    $lastDay = $date->format('Y-m-t');

    $toDate = $_POST['toDate'];
    // echo $fromDate;
    // exit;
    $qMoDistribution = "WITH categ_percentage as (
        SELECT 
    act.id act_id,
        act.acc_category,
        coalesce(sum(acd.distribution_percentage),0) acc_categ_percentage
        FROM m_acc_category_tbl act
        left join m_acc_cost_distribution acd on act.id =acd.m_acc_category_id
        group by  act.id, act.acc_category				  
    )
	   , detailed_percentage as (
        select 
		acd.distribution_percentage ,
        acd.m_acc_category_id,
        acd.analytic_account_id,
        adg.dept_group,
        aaa.name dept 
		,aaa.code,
		aat.from_account_id
        from
            m_acc_cost_distribution acd
     left JOIN account_analytic_account aaa ON aaa.id = acd.analytic_account_id
         left join m_acc_department_groups adg on adg.id =aaa.m_acc_group_id
		  LEFT JOIN categ_percentage CP ON CP.ACT_ID = ACD.m_acc_category_id
          left join M_ACC_ACC_TAGGING aat on aat.dept_group_id = adg.id
		   WHERE CP.acc_categ_percentage = 100
		 order by m_acc_category_id, dept_group
    )
	, temp_percentage as(
	select * from detailed_percentage a 
	 )
	 ,journal_entry_initial as(
    SELECT
    AM.ID am_id, 
    AM.NAME journal_entry, 
    AM.STATE, 
    '$lastDay' DATE,
    sum(AML.DEBIT) AS aml_total_DEBIT, 
    '$lastDay' AS BILL_DATE,
    to_char(AM.CREATE_DATE  AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila','YYYY-MM-DD' ) CREATE_DATE,
    AA.NAME account_name,
    aa.id account_id,
    aa.code account_code,
    RP.NAME AS VENDOR,
    ACT.id account_category_id,
    cp.acc_categ_percentage,
    aj.name journal,
    aj.id journal_id,
    am.ref,
    act.mo_pct_ref,
    -- aml.name item_label
    '' item_label
    FROM ACCOUNT_MOVE AM
    JOIN ACCOUNT_MOVE_LINE AML ON AML.MOVE_ID = AM.ID
    JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AML.ACCOUNT_ID
    left JOIN RES_PARTNER RP ON RP.ID = AM.PARTNER_ID
    LEFT JOIN M_ACC_CATEGORY_TBL ACT ON ACT.ID =AA.M_ACC_CATEGORY_ID
    LEFT JOIN categ_percentage cp ON cp.act_id = act.id
    left join account_journal aj on aj.id = ACT.journal_id
    WHERE 
    am.id =$am_id
        --am.name in ('APV/2025/1706'--,'APV/2025/1685'
			--	   )
	--	AM.NAME LIKE 'APV/%'
    AND
     AM.NAME NOT LIKE 'MTB/%'
    AND am.date BETWEEN to_Date('$firstDay','YYYY-MM-DD') AND to_Date('$lastDay','YYYY-MM-DD')
    AND AM.STATE = 'posted'
    and aml.debit > 0
    and cp.acc_categ_percentage = 100
    group by
    AM.ID, 
    AM.NAME, 
    AM.STATE, 
    TO_CHAR(AM.INVOICE_DATE, 'YYYY-MM-DD'),
    to_char(AM.CREATE_DATE  AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila','YYYY-MM-DD' ),
    AA.NAME,
    aa.id,
    RP.NAME ,
    ACT.id,
    cp.acc_categ_percentage,
    aj.name,
    aj.id,
    am.ref,
    am.date,
    aa.code,
    act.mo_pct_ref)
    ,journal_entry as (
	select * ,
		sum(aml_total_debit) over (partition by am_id) total_debit
		from journal_entry_initial
	)
	,truncated_allocation as(
	select 
	je.am_id,
    je.account_id,
	je.total_debit,
	tp.distribution_percentage,
	trunc(je.total_debit * (tp.distribution_percentage/100),2) allocation_trunc,
	je.total_debit * (tp.distribution_percentage/100) allocation,
	sum(trunc(je.total_debit * (tp.distribution_percentage/100),2)) over (partition by je.am_id) total_allocation_trunc,
		sum(je.total_debit * (tp.distribution_percentage/100)) over (partition by je.am_id) total_allocation,
	tp.dept_group,
	tp.dept,
		tp.analytic_account_id
	from 
	journal_entry je
	left join temp_percentage tp on tp.m_acc_category_id = je.account_category_id and tp.from_account_id = je.account_id
		)
		, ranked as(
		select
		ta.am_id,
        ta.account_id,
	ta.total_debit,
	ta.distribution_percentage,
	ta.allocation - ta.allocation_trunc allocation_diff,
	ta.allocation_trunc,
	((ta.total_allocation - ta.total_allocation_trunc)/0.01)::integer rows_to_adjust,
	ROW_NUMBER() OVER (PARTITION BY ta.am_id ORDER BY ta.allocation - ta.allocation_trunc DESC) AS rn,
	ta.dept_group,
	ta.dept,
			ta.analytic_account_id
	from truncated_allocation ta)
	, final_DEPT_DIST as (
	select 
	am_id,
    account_id,
	distribution_percentage,
	 CASE 
            WHEN rn <= rows_to_adjust THEN allocation_trunc + 0.01
            ELSE allocation_trunc
        END AS debit_final,
	dept_group,
	dept,
		analytic_account_id
	from 
	ranked)
	, debit_credit_DIST as(
	select
	fem.am_id,
    fem.account_id,
	fem.distribution_percentage,
	fem.debit_final debit,
	0::numeric credit,
	fem.dept_group,
		fem.dept,
		fem.analytic_account_id
	from
	final_DEPT_DIST fem
	)
	, DISTRIBUTED_ALL AS
	(
	select 
	je.am_id,
	je.journal_entry,
	je.ref reference,
	je.journal,
	je.journal_id,
	je.account_code,
	je.account_id,
	je.item_label,
	je.date,
	dcem.dept,
	dcem.distribution_percentage,
	dcem.debit,
	dcem.credit,
	dcem.analytic_account_id,
		 split_part(dcem.dept,' ', 1) AA_CODE,
	dcem.dept analytic_account,
	DCEM.DEPT_GROUP,
    je.mo_pct_ref
	from
	debit_credit_DIST dcem
	join journal_entry je on je.am_id = dcem.am_id and dcem.account_id = je.account_id
	ORDER BY DEPT_GROUP
)
,DISTRIBUTION_TOTAL AS(
SELECT *,
	case when DA.aa_code = '8120' then 'DIE SALES' 
				when DA.aa_code = '8300' then 'TOs' 
				when DA.aa_code = '8310' then 'SOT' 
				when DA.aa_code = '8100' then 'HERMETICS'
				when DA.aa_code = '8110' then 'MODULES'
			end sbu
FROM
	DISTRIBUTED_ALL DA
	WHERE DA.DEPT_GROUP ='MANUFACTURING/PRODUCT LINE'
)
 , mo_with_trx AS (
            SELECT DISTINCT mp.name, mp.id AS mo_id
            FROM mrp_production mp
            JOIN (
                SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
                FROM mrp_workorder
                WHERE date_finished + INTERVAL '8 hours' BETWEEN 
                    TO_TIMESTAMP('$fromDate','YYYY-MM-DD') + INTERVAL '6 hours'
                    AND TO_TIMESTAMP('$toDate','YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
                    and state != 'cancel'
            ) mw ON mw.production_id = mp.id
        ),
        all_mos AS (
            SELECT 
                mp.id AS mo_id, 
                mp.name AS mo,  
                TO_CHAR(mp.create_date + INTERVAL '8 hours', 'YYYY-MM-DD') AS date_load,
                am.name AS inv_no,
                spb.name AS batch_no, 
                spb.state AS voucher_status, 
                am.invoice_date,
                mp.state AS mo_status,
                CASE WHEN am.invoice_date <= to_Date('$lastDay','YYYY-MM-DD')
                    THEN 'INVOICED BEFORE MONTH END' ELSE '' END AS status
            FROM mrp_production mp
            LEFT JOIN (
                SELECT * 
                FROM mrp_production_stock_picking_rel A
                JOIN stock_picking B ON B.id = A.stock_picking_id 
                WHERE B.name LIKE 'WH/OUT/%' AND batch_id IS NOT NULL
            ) sp ON sp.mrp_production_id = mp.id
            LEFT JOIN stock_picking_batch spb ON spb.id = sp.batch_id
            LEFT JOIN account_move_stock_picking_batch_rel amspb ON amspb.stock_picking_batch_id = spb.id
            LEFT JOIN account_move am ON am.id = amspb.account_move_id
            JOIN mo_with_trx mwt ON mwt.mo_id = mp.id
        ),
        filtered_mo AS (
            SELECT DISTINCT 
                mo,
                mo_id,
                mo_status,
                MAX(status) OVER (PARTITION BY mo_id) AS status
            FROM all_mos
        ),
        set_status AS (
            SELECT
                fm.mo,
                pt.name AS device,
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
END AS SBU
            FROM filtered_mo fm
            JOIN mrp_production mp ON mp.id = fm.mo_id
            JOIN product_product pp ON pp.id = mp.product_id
            JOIN product_template pt ON pt.id = pp.product_tmpl_id
            JOIN product_category pc ON pc.id = pt.categ_id
            LEFT JOIN (
                SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
                FROM mrp_workorder
                WHERE date_finished + INTERVAL '8 hours' BETWEEN 
                    TO_TIMESTAMP('$fromDate', 'YYYY-MM-DD') + INTERVAL '6 hours' 
                    AND TO_TIMESTAMP('$toDate', 'YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
                    and state !='cancel'
            ) mw ON mw.production_id = fm.mo_id
            LEFT JOIN mrp_routing_workcenter mrw ON mw.operation_id = mrw.id
            WHERE 
            --fm.status != 'INVOICED BEFORE MONTH END' AND 
            fm.mo_status NOT IN ('cancel','draft','planned') 
            GROUP BY fm.mo, pt.name, pc.name, mp.lot_no, mp.customer_name, fm.mo_status, fm.status, max_wo,
            CASE WHEN PC.NAME LIKE 'DIE%' THEN
            CONCAT(TRIM(SPLIT_PART(PC.NAME, ' ', 1)), ' ',TRIM(SPLIT_PART(PC.NAME, ' ', 2)))
            ELSE
            TRIM(SPLIT_PART(PC.NAME, ' ', 1))
          END
        ) 
		, NOT_TALLY AS (
	        SELECT
            a.mo,
            a.device,
            a.sbu,
            a.category,
            a.lot_no,
            a.customer_name,
            coalesce(mw.done_qty,0) AS quantity_done,
            coalesce(a.total_labor,0) total_labor,
            (mw.done_qty * a.total_labor) AS total_multiplied_in_quantity_done,
            case when dt.mo_pct_ref = 'EH' then
            ROUND(coalesce((mw.done_qty * a.total_labor) / 3600,0),5)
			else 
			round(mw.done_qty,5) end
			AS earned_hrs,
			case when dt.mo_pct_ref = 'EH' then
			(ROUND(coalesce((mw.done_qty * a.total_labor) / 3600,0),5)/ (SUM(ROUND(coalesce((mw.done_qty * a.total_labor) / 3600,0),5)) OVER (PARTITION BY A.SBU))) * DT.DEBIT 
			else
			(round(mw.done_qty,5)/ (SUM(round(coalesce(mw.done_Qty,0),5)) OVER (PARTITION BY A.SBU))) * DT.DEBIT
			end
			AS allocation,
			DEBIT,
            a.mo_status,
            a.status
        FROM set_status a
        LEFT JOIN mrp_workorder mw ON mw.id = a.max_wo
		left join distribution_total dt on dt.sbu = a.sbu
	--ORDER BY ALLOCATION
	)
	,MO_RANKED AS (
	SELECT 
	NT.MO,
	NT.DEVICE,
	NT.SBU,
	NT.CATEGORY,
	NT.LOT_NO,
	NT.ALLOCATION,
		NT.CUSTOMER_NAME,
		NT.QUANTITY_DONE,
		NT.TOTAL_LABOR,
		NT.total_multiplied_in_quantity_done,
		NT.earned_hrs,
		NT.MO_STATUS,
       NT.STATUS,
	TRUNC(NT.ALLOCATION,5) TRUNC_ALLOCATION,
	SUM(NT.ALLOCATION) OVER() TOTAL_ALLOCATION,
	SUM(TRUNC(NT.ALLOCATION,5)) OVER() TOTAL_TRUNC_ALLOCATION,
	(
		NT.ALLOCATION- TRUNC(NT.ALLOCATION,5)
	) ALLOCATION_DIFF,
	((
		SUM(NT.ALLOCATION) OVER()- SUM(TRUNC(NT.ALLOCATION,5)) OVER()
	)/ 0.00001)::INTEGER ROWS_TO_ADJUST,
	ROW_NUMBER() OVER (ORDER BY NT.ALLOCATION - TRUNC(NT.ALLOCATION,5) DESC) AS rn
	FROM 
	NOT_TALLY NT)
	SELECT 
MO,
DEVICE,
SBU,
CATEGORY,
LOT_NO,
CUSTOMER_NAME,
QUANTITY_DONE,
TOTAL_LABOR,
TOTAL_MULTIPLIED_IN_QUANTITY_DONE,
EARNED_HRS,
MO_STATUS,
STATUS,
	 CASE 
            WHEN rn <= rows_to_adjust THEN TRUNC_ALLOCATION + 0.00001
            ELSE TRUNC_ALLOCATION
        END AS ALLOCATION
	FROM
	MO_RANKED
";

    $resMoDist =  $db->fetchAll($qMoDistribution);

    $query2 = "
    with trunc_allocation as  (
        SELECT 
      act.acc_category,
                  acd.distribution_percentage percentage,
                  aaa.name AS department,
                  (acd.distribution_percentage::numeric / 100) *	$amountTotal AS allocation,
        trunc((acd.distribution_percentage::numeric / 100) *	$amountTotal,2) AS trunc_allocation,
      ((acd.distribution_percentage::numeric / 100) *	$amountTotal) -  trunc((acd.distribution_percentage::numeric / 100) *	$amountTotal,2)  allocation_diff,
                      $amountTotal AS total_amount,
        sum(trunc((acd.distribution_percentage::numeric / 100) *	$amountTotal,2)) over() trunc_total_amount,
        $amountTotal -  sum(trunc((acd.distribution_percentage::numeric / 100) *	$amountTotal,2)) over() total_amount_diff,
                      adg.dept_group
                      FROM m_acc_cost_distribution acd
              JOIN account_analytic_account aaa ON aaa.id = acd.analytic_account_id
              join m_acc_category_tbl act on act.id =acd.m_acc_category_id
              left join m_acc_department_groups adg on adg.id =aaa.m_acc_group_id
              where act.acc_category = '$acc_category'
    )
   , ranked as( select 
     ta.acc_category,
                  ta.percentage,
                  ta.department,
                  ta.allocation,
        ta.trunc_allocation,
      ta.allocation_diff,
       ta.total_amount,
        ta.trunc_total_amount,
       (ta.total_amount_diff/0.01)::integer rows_to_adjust,
       ROW_NUMBER() OVER ( ORDER BY ta.allocation_diff DESC) AS rn,
                      ta.dept_group
      from 
    trunc_allocation ta)
   , final_query as(
  select
   acc_category,
                  percentage,
                  department,
  --                 ta.allocation,
  -- 	  ta.trunc_allocation,
  -- 	ta.allocation_diff,
       total_amount,
  -- 	  ta.trunc_total_amount,
        CASE 
              WHEN rn <= rows_to_adjust THEN trunc_allocation + 0.01
              ELSE trunc_allocation
          END AS allocation,
      dept_group
  from
  ranked
  )
              select
                fq.acc_category,
                  fq.percentage || '%' AS percentage,
                  fq.department,
                  fq.allocation,
                  fq.total_amount,
                  fq.dept_group
              from 
              final_query fq
              where (dept_group !='MANUFACTURING/PRODUCT LINE' or dept_group is null)
              union all
              select 
                fq.acc_category,	
                  sum(fq.percentage)  || '%' AS percentage,
                  'Manufacturing' department,
                  sum(fq.allocation) allocation,
                  fq.total_amount,
                  fq.dept_group
              from 
              final_query fq
              where dept_group ='MANUFACTURING/PRODUCT LINE'
              group by
                fq.acc_category,
                  fq.total_amount,
                  fq.dept_group
    ";

    $result2 = $db->fetchAll($query2);
} else {
    echo json_encode($response);
    exit;
}
// echo $query;
// exit;





$data = [
    'mo_dist' => isset($resMoDist) ? $resMoDist : '',
    'flag' => 1,
    'dept_dist' => $result2
];

echo json_encode($data);
