<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Postgresql();


// $response = ['data' => []];

// $amountTotal = isset($_POST['amountTotal']) ? $_POST['amountTotal'] : 0;
$am_id = $_POST['amId'];
$acc_category = $_POST['accCategory'];
if (isset($_POST['fromDate']) && isset($_POST['toDate']) && !empty($_POST['fromDate']) && !empty($_POST['toDate'])) {
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];
    // echo $fromDate;
    // exit;
    $qProdPercentage = "with main_query as ( select 
          am.name,
          aa.name account_name,
          act.acc_category,
          SUM(AML.DEBIT) TOTAL_DEBIT, 
          act.id category_id
            FROM ACCOUNT_MOVE AM
            JOIN ACCOUNT_MOVE_LINE AML ON AML.MOVE_ID = AM.ID
            JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AML.ACCOUNT_ID
            left JOIN RES_PARTNER RP ON RP.ID = AM.PARTNER_ID
            LEFT JOIN M_ACC_CATEGORY_TBL ACT ON ACT.ID =AA.M_ACC_CATEGORY_ID 
            where am.id = $am_id
              AND AM.STATE = 'posted'
            and aml.debit > 0
            group by 
            am.name,
          aa.name ,
          act.acc_category,
          act.id)
          select 
           mq.name,
          mq.account_name,
          mq.acc_category,
           mq.TOTAL_DEBIT,
           sum(coalesce(ACD.DISTRIBUTION_PERCENTAGe,0::numeric)) prod_total_percentage,
          (sum(coalesce(ACD.DISTRIBUTION_PERCENTAGe,0::numeric))/100) * mq.TOTAL_DEBIT prod_allocation,
          acd.dept_group
          from
          main_query mq
          left join (
			select b.id adg_id, a.id aca_id, b.dept_group, c.m_acc_category_id acd_m_acc_category_id, c.distribution_percentage from M_ACC_COST_DISTRIBUTION c
			left join ACCOUNT_ANALYTIC_ACCOUNT a on a.id = c.analytic_account_id
				   left join M_ACC_DEPARTMENT_GROUPS b on b.id = a.m_acc_group_id  where  b.dept_group ='MANUFACTURING/PRODUCT LINE') acd ON  ACD.acd_m_acc_category_id = mq.category_id 
   -- where  adg.dept_group ='MANUFACTURING/PRODUCT LINE'
        group by
        mq.name,
        mq.account_name,
          mq.acc_category,
           mq.TOTAL_DEBIT,
          --ACA.NAME ,
        --  ACD.DISTRIBUTION_PERCENTAGE,
          acd.dept_group";

    $resProdPercentage =  $db->fetchRow($qProdPercentage);
    $prodPercentage = $resProdPercentage['prod_allocation'];
    $amountTotal = $resProdPercentage['total_debit'];

    if ($prodPercentage > 0) {

        $query = "
        WITH mo_with_trx AS (
            SELECT DISTINCT mp.name, mp.id AS mo_id
            FROM mrp_production mp
            JOIN (
                SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
                FROM mrp_workorder
                WHERE date_finished + INTERVAL '8 hours' BETWEEN 
                    TO_TIMESTAMP('$fromDate','YYYY-MM-DD') + INTERVAL '6 hours'
                    AND TO_TIMESTAMP('$toDate','YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
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
                CASE WHEN am.invoice_date <= TO_DATE('$toDate','YYYY-MM-DD')
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
            coalesce((mw.done_qty * a.total_labor) / 3600,0) AS earned_hrs,
            ROUND(coalesce((((mw.done_qty * a.total_labor) / 3600) / NULLIF(SUM((mw.done_qty * a.total_labor) / 3600) OVER (), 0)) * $prodPercentage,0), 2) AS allocation,
            a.mo_status,
            a.status
        FROM set_status a
        LEFT JOIN mrp_workorder mw ON mw.id = a.max_wo
    ";
        $result = $db->fetchAll($query);
    }
    $query2 = "
    with main_query as  (SELECT 
    act.acc_category,
                acd.distribution_percentage percentage,
                aaa.name AS department,
                (acd.distribution_percentage::numeric / 100) *	$amountTotal AS allocation,
                	$amountTotal AS total_amount,
					adg.dept_group
					FROM m_acc_cost_distribution acd
            JOIN account_analytic_account aaa ON aaa.id = acd.analytic_account_id
            join m_acc_category_tbl act on act.id =acd.m_acc_category_id
			left join m_acc_department_groups adg on adg.id =aaa.m_acc_group_id
            where act.acc_category = '$acc_category')
			select
			  mq.acc_category,
                mq.percentage || '%' AS percentage,
                mq.department,
                mq.allocation,
                mq.total_amount,
				mq.dept_group
			from 
			main_query mq
			where (dept_group !='MANUFACTURING/PRODUCT LINE' or dept_group is null)
			union all
			select 
			  mq.acc_category,
                sum(mq.percentage)  || '%' AS percentage,
                'Manufacturing' department,
                sum(mq.allocation) allocation,
                mq.total_amount,
				mq.dept_group
			from 
			main_query mq
			where dept_group ='MANUFACTURING/PRODUCT LINE'
			group by
			  mq.acc_category,
                mq.total_amount,
				mq.dept_group
    ";

    $result2 = $db->fetchAll($query2);
} else {
    echo json_encode($response);
    exit;
}
// echo $query;
// exit;





$data = [
    'mo_dist' => isset($result) ? $result : '',
    'flag' => 1,
    'dept_dist' => $result2
];

echo json_encode($data);
