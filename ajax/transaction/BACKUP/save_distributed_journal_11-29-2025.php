<?php

session_start();

$db = new Postgresql();

$month_id = $_POST['month_id'];
$user = $_SESSION['ppc']['emp_no'];

$select_month = $db->fetchRow("SELECT to_char(start_date,'YYYY-MM-DD') start_date, to_char(end_date, 'YYYY-MM-DD') end_date, TO_CHAR(to_date(to_char(start_date ,'YYYY-MM'),'YYYY-MM') + INTERVAL '1 month - 1 day', 'MM/DD/YYYY') LAST_DATE_OF_MONTH FROM M_ACC_DATE_RANGE WHERE ID =$month_id");

if ($select_month) {
    $from_date = $select_month['start_date'];
    $to_date = $select_month['end_date'];
    $last_date_of_month = $select_month['last_date_of_month'];
    $monthYear = substr($from_date, 0, 7);

    $q = "WITH categ_percentage as (
        SELECT 
    act.id act_id,
        act.acc_category,
        coalesce(sum(acd.distribution_percentage),0) acc_categ_percentage
        FROM m_acc_category_tbl act
        left join m_acc_cost_distribution acd on act.id =acd.m_acc_category_id
        group by  act.id, act.acc_category				  
    )
    ,journal_entry as(
    SELECT
    AM.ID am_id, 
    AM.NAME journal_entry, 
    AM.STATE, 
    '$last_date_of_month' DATE,
    sum(AML.DEBIT) AS TOTAL_DEBIT, 
    '$last_date_of_month' AS BILL_DATE,
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
    --(--AM.NAME = 'APV/2025/1632' 
    --or 
    --rp.name = 'ADVENTENERGY, INC.' or aa.name ='Accrued Utility Cost'
    --)
        --am.name ='APV/2025/1632'
    --AND
     AM.NAME NOT LIKE 'MTB/%'
    --'MTB/2024/2029'
    AND TO_DATE(to_char(AM.CREATE_DATE  AT TIME ZONE 	 
    'UTC' AT TIME ZONE 'Asia/Manila','YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE('$from_date','YYYY-MM-DD') AND TO_DATE('$to_date','YYYY-MM-DD')
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
    -- aml.name,
    aa.code
    ),mo_with_trx AS (
            SELECT DISTINCT mp.name, mp.id AS mo_id
            FROM mrp_production mp
            JOIN (
                SELECT MAX(ID) OVER (PARTITION BY production_id) AS max_wo, *
                FROM mrp_workorder
                WHERE date_finished + INTERVAL '8 hours' BETWEEN 
                    TO_TIMESTAMP('$from_date','YYYY-MM-DD') + INTERVAL '6 hours'
                    AND TO_TIMESTAMP('$to_date','YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
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
                CASE WHEN am.invoice_date <= TO_DATE('$to_date','YYYY-MM-DD')
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
                    TO_TIMESTAMP('$from_date', 'YYYY-MM-DD') + INTERVAL '6 hours' 
                    AND TO_TIMESTAMP('$to_date', 'YYYY-MM-DD') + INTERVAL '1 day 5 hours 59 minutes'
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
        ),
        mo_percentage  as (SELECT
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
            (((mw.done_qty * a.total_labor) / 3600) / NULLIF(SUM((mw.done_qty * a.total_labor) / 3600) OVER (), 0)) percentage_per_mo,
          a.mo_status,
            a.status
        FROM set_status a
        LEFT JOIN mrp_workorder mw ON mw.id = a.max_wo)
        , percentage_per_sbu as(
        select 
        mp.sbu,
        sum(mp.percentage_per_mo) percentage_per_sbu,
        case when sbu ='DIE SALES' then '8120'
        when sbu = 'HERMETICS' then '8100'
        when sbu= 'MODULES' then '8110'
        when sbu ='SOT' then '8310'
        when sbu ='TOs' then '8300'
        end analytic_account_code
        from
        mo_percentage mp
        group by mp.sbu
        )
        , adjusted_prod_percentage as (
        select 
        case when adg.dept_group != 'MANUFACTURING/PRODUCT LINE' then acd.distribution_percentage else --round(
            pps.percentage_per_sbu *sum(case when adg.dept_group='MANUFACTURING/PRODUCT LINE' then acd.distribution_percentage else 0 end) over(partition by m_acc_category_id, adg.dept_group)--,4) 
        end adjusted_percentage,
        acd.m_acc_category_id,
        acd.analytic_account_id,
        adg.dept_group,
        aaa.name dept , pps.percentage_per_sbu,aaa.code
        from
            m_acc_cost_distribution acd
     left JOIN account_analytic_account aaa ON aaa.id = acd.analytic_account_id
         left join m_acc_department_groups adg on adg.id =aaa.m_acc_group_id
         left join percentage_per_sbu pps on pps.analytic_account_code =split_part(aaa.name,' ',1)---------
    ),
    base AS (
        SELECT
            je.am_id,
            je.journal_entry,
            je.ref AS reference,
            je.date,
            je.journal,
            je.journal_id,
            -- CASE WHEN je.account_code = '2110500' THEN
            -- case when adg.dept_group ='GENERAL & ADMIN' then '6000700' else '5320200' end
            --  ELSE je.account_code END AS account_code,
           AA.CODE account_code,
            je.account_id,
            je.item_label,
            app.adjusted_percentage,
            app.dept AS analytic_account,
            app.analytic_account_id,
            NULL::NUMERIC AS credit,
            (app.adjusted_percentage / 100) * je.total_debit AS debit_full,
            trunc((app.adjusted_percentage / 100) * je.total_debit, 2) AS debit_trunc,
            ((app.adjusted_percentage / 100) * je.total_debit - trunc((app.adjusted_percentage / 100) * je.total_debit,2)) AS debit_diff
        FROM journal_entry je
        JOIN adjusted_prod_percentage app ON app.m_acc_category_id = je.account_category_id
        left JOIN account_analytic_account aaa ON aaa.id = app.analytic_account_id
         left join m_acc_department_groups adg on adg.id =aaa.m_acc_group_id
         LEFT JOIN M_ACC_ACC_TAGGING AAT ON AAT.FROM_ACCOUNT_ID =JE.account_id AND AAT.DEPT_GROUP_ID =ADG.ID
         LEFT JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AAT.TO_ACCOUNT_ID
    ),
    summary AS (
        SELECT
            b.journal_entry,
           je.total_debit AS total_debit_full,
            SUM(b.debit_trunc) AS total_debit_trunc,
            CEIL((je.total_debit - SUM(b.debit_trunc)) / 0.01)::int AS rows_to_adjust
        FROM base b
        join journal_entry je on je.journal_entry = b.journal_entry
        GROUP BY b.journal_entry, je.total_debit
    ),
    ranked AS (
        SELECT
            b.*,
            ROW_NUMBER() OVER (PARTITION BY b.journal_entry ORDER BY b.debit_diff DESC) AS rn,
            s.rows_to_adjust
        FROM base b
        JOIN summary s ON s.journal_entry = b.journal_entry
    )
    ,final as(
    SELECT
        r.am_id,
        r.journal_entry,
        r.reference,
        r.date,
        r.journal,
        r.journal_id,
        r.account_code,
        r.account_id,
        r.item_label,
        r.adjusted_percentage,
        r.analytic_account,
        r.analytic_account_id,
        r.credit,
        r.debit_full AS debit_before_adjust,
        r.debit_trunc AS debit_trunc,
        r.debit_diff AS debit_round_diff,
        CASE 
            WHEN rn <= rows_to_adjust THEN debit_trunc + 0.01
            ELSE debit_trunc
        END AS debit_final,
        SUM(debit_trunc + CASE WHEN rn <= rows_to_adjust THEN 0.01 ELSE 0 END) 
            OVER (PARTITION BY r.journal_entry) AS round_total_debit,
        je.total_debit AS total_debit
    FROM ranked r
    join journal_entry je on je.journal_entry = r.journal_entry
    ORDER BY journal_entry, debit_diff DESC)
    select 
     f.am_id,
    f.journal_entry,
    f.reference,
    f.date,
    f.journal,
    f.journal_id,
    f.account_code,
    f.account_id,
    f.item_label,
    f.adjusted_percentage,
    f.analytic_account,
    f.analytic_account_id,
    f.credit,
    f.debit_final debit,
    f.total_debit
    from
    final f
    -- order by journal_entry
    union all
    select
    je.am_id,
    je.journal_entry,
    je.ref reference,
    je.date,
    je.journal,
    je.journal_id,
    je.account_code,
    je.account_id,
    '' item_label,
    NULL::NUMERIC adjusted_percentage,
    '' analytic_account,
    NULL::integer analytic_account_id,
    je.total_debit credit,
    0::numeric debit,
    0::numeric total_debit
    from
    journal_entry je
    join adjusted_prod_percentage app on app.m_acc_category_id = je.account_category_id
    GROUP BY
    je.am_id,
    je.journal_entry,
    je.ref,
    je.date,
    je.journal,
    je.journal_id,
    je.account_code,
    je.account_id,
    je.total_debit
    ORDER BY JOURNAL_ENTRY, CREDIT DESC";
    // echo $q;
    // exit;
    $result = $db->fetchAll($q);
}

// $monthYear = substr($from_date, 0, 7);

// echo $from_date;
// exit;






// echo '<pre>';

if ($result) {
    $dataBatch = [
        'YEAR_MONTH' => "'$monthYear'",
        'START_DATE' => "'$from_date'",
        'END_DATE' => "'$to_date'",
        'ADDED_BY' => $user
    ];

    $resultBatch = $db->insert_get_id($dataBatch, 'M_ACC_DIST_BATCH');
    $new_batch_id = $resultBatch['id']; // ← this is the newly inserted ID

    $db->query("UPDATE M_ACC_DATE_RANGE SET IS_DISTRIBUTED = TRUE WHERE ID = $month_id");
    // $qInsertBatch = "INSERT INTO M_DIST_JOURNAL_BATCH(YEAR_MONTH, START_DATE, END_DATE, ADDED_BY) VALUES ()";

    $checker = '';


    //USE INSERT GET ID, CHECK SAMPLE FROM MRP_SYSTEM
    foreach ($result as $item) {
        $account_move_id = $item['am_id'];
        $journal_entry = $item['journal_entry'];
        $reference = $item['reference'];
        $journal = $item['journal'];
        $journal_id = $item['journal_id'];
        $account_code = $item['account_code'];
        $account_id = $item['account_id'];
        $item_label = $item['item_label'];
        $analytic_account = $item['analytic_account'];
        $analytic_account_id = $item['analytic_account_id'] != '' && $item['analytic_account_id'] ? $item['analytic_account_id']  : 'NULL::INTEGER';
        $distribution_percentage = $item['adjusted_percentage'] != '' && $item['adjusted_percentage']  ? $item['adjusted_percentage'] : 'NULL::NUMERIC';
        $debit = $item['debit'] != '' && $item['debit']  ? $item['debit'] : 'NULL::NUMERIC';
        $credit = $item['credit'] != '' && $item['credit']  ? $item['credit'] : 'NULL::NUMERIC';
        $account_move_date = $item['date'];
        // echo $credit;
        // exit;

        if ($item["journal_entry"] != $checker) {

            $dataEntries = [
                'DATE_RANGE_ID' => $month_id,
                'ACCOUNT_MOVE_ID' => $account_move_id,
                'ACCOUNT_MOVE_NAME' => "'$journal_entry'",
                'REFERENCE' => "'$reference'",
                'JOURNAL' => "'$journal'",
                'JOURNAL_ID' => $journal_id,
                'ADDED_BY' => $user,
                'ACCOUNT_MOVE_DATE' => "'$account_move_date'"
            ];
            $resultEntries = $db->insert_get_id($dataEntries, 'M_ACC_DIST_JOURNAL_ENTRIES');
            $new_entries_id = $resultEntries['id']; // ← this is the newly inserted ID


        }

        $dataLineItems = [
            'MAIN_ID' => $new_entries_id,
            'ACCOUNT_CODE' => "'$account_code'",
            'ACCOUNT_ID' => $account_id,
            'ITEM_LABEL' => "'$item_label'",
            'ANALYTIC_ACCOUNT' => "'$analytic_account'",
            'ANALYTIC_ACCOUNT_ID' => $analytic_account_id,
            'DISTRIBUTION_PERCENTAGE' => $distribution_percentage,
            'DEBIT' => $debit,
            'CREDIT' => $credit,
            'ADDED_BY' => $user
        ];
        $resultLineItems = $db->insert($dataLineItems, 'M_ACC_DIST_JOURNAL_ITEMS');




        $checker = $item["journal_entry"];
    }

    // $data = [
    //     'BATCH_NO' => "'$next_batch_no'",
    //     'FORECAST_MONTH' => "'October 2025'",
    //     'ADDED_BY' => $user
    // ];


    // $result = $db->insert_get_id($data, 'M_DIST_JOURNAL_BATCH');

    // $new_id = $result['id']; // ← this is the newly inserted ID
}


// var_dump($result);
// exit;

echo json_encode($month_id);
