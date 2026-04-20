<?php
$db = new Postgresql();
$conn = $db->getConnection();

$from = isset($_POST['fromDate']) ? $_POST['fromDate'] : '';
$to = isset($_POST['toDate']) ? $_POST['toDate'] : '';
// echo $from;
// exit;


if ($from && $to) {

    $fromDate = $from;
    $date = new DateTime($fromDate);

    // First day of the month
    $firstDay = $date->format('Y-m-01');

    // Last day of the month
    $lastDay = $date->format('Y-m-t');

    // echo $firstDay;
    // echo $lastDay;

    // $query = "
    //     SELECT DISTINCT
    //         AM.ID, 
    //         AM.NAME, 
    //         AM.STATE, 
    //         AM.AMOUNT_TOTAL, 
    //         TO_CHAR(AM.INVOICE_DATE, 'YYYY-MM-DD') AS BILL_DATE,
    //         to_char(AM.CREATE_DATE  AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila','YYYY-MM-DD' ) CREATE_DATE,
    //         AA.NAME AS ACCOUNT_NAME, 
    //         RP.NAME AS VENDOR
    //     FROM ACCOUNT_MOVE AM
    //     JOIN ACCOUNT_MOVE_LINE AML ON AML.MOVE_ID = AM.ID
    //     JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AML.ACCOUNT_ID
    //     JOIN RES_PARTNER RP ON RP.ID = AM.PARTNER_ID
    //     WHERE AML.EXCLUDE_FROM_INVOICE_TAB = FALSE
    //     AND TO_DATE(to_char(AM.CREATE_DATE  AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila','YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE($1,'YYYY-MM-DD') AND TO_DATE($2,'YYYY-MM-DD')
    //     AND AM.STATE = 'posted'
    //     AND rp.name = 'ADVENTENERGY, INC.' 
    //     ORDER BY to_char(AM.CREATE_DATE  AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila','YYYY-MM-DD' ) DESC
    // ";

    $query = "
    with categ_percentage as (
		SELECT 
	act.id act_id,
    	act.acc_category,
        coalesce(sum(acd.distribution_percentage),0) acc_categ_percentage
		FROM m_acc_category_tbl act
        left join m_acc_cost_distribution acd on act.id =acd.m_acc_category_id
		group by  act.id, act.acc_category				  
)
    SELECT
    AM.ID, 
    AM.NAME, 
    AM.STATE, 
    SUM(AML.DEBIT) AS TOTAL_DEBIT, 
    TO_CHAR(AM.INVOICE_DATE, 'YYYY-MM-DD') AS BILL_DATE,
    to_char(AM.date ,'YYYY-MM-DD' ) CREATE_DATE,
    STRING_AGG(DISTINCT AA.NAME || case when act.acc_category is null or act.acc_category = '' then '+' else '' end,',') AS ACCOUNT_NAME, 
    --AA.NAME ACCOUNT_NAME,
    RP.NAME AS VENDOR,
   max(ACT.ACC_CATEGORY) account_category,
   max(cp.acc_categ_percentage)acc_categ_percentage
   --SUM(ACD.DISTRIBUTION_PERCENTAGE) AS distribution_percentage
FROM ACCOUNT_MOVE AM
JOIN ACCOUNT_MOVE_LINE AML ON AML.MOVE_ID = AM.ID
JOIN ACCOUNT_ACCOUNT AA ON AA.ID = AML.ACCOUNT_ID
left JOIN RES_PARTNER RP ON RP.ID = AM.PARTNER_ID
JOIN M_ACC_CATEGORY_TBL ACT ON ACT.ID =AA.M_ACC_CATEGORY_ID
LEFT JOIN categ_percentage cp ON cp.act_id = act.id
WHERE --AML.EXCLUDE_FROM_INVOICE_TAB = FALSE
--AND
--(AM.NAME = 'APV/2025/3154' or rp.name = 'ADVENTENERGY, INC.' or aa.name ='Accrued Utility Cost')
(am.name like 'APV/%' or am.name like 'ACC/%')
AND AM.NAME NOT LIKE 'MTB/%'
--'MTB/2024/2029'
  AND TO_DATE(to_char(AM.date  AT TIME ZONE 	 
  'UTC' AT TIME ZONE 'Asia/Manila','YYYY-MM-DD'),'YYYY-MM-DD') BETWEEN TO_DATE($1,'YYYY-MM-DD') AND TO_DATE($2,'YYYY-MM-DD')
AND AM.STATE = 'posted'
and aml.debit > 0
GROUP BY 
 AM.ID, 
    AM.NAME, 
    AM.STATE, 
    TO_CHAR(AM.date, 'YYYY-MM-DD'),
    to_char(AM.invoice_date  AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila','YYYY-MM-DD' ),
    --AA.NAME, 
    RP.NAME--,
	--cp.acc_categ_percentage
    ";

    $result = pg_query_params($conn, $query, array($firstDay, $lastDay));
    $data = [];

    if ($result) {
        while ($row = pg_fetch_assoc($result)) {
            $data[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'state' => $row['state'],
                'amount_total' => $row['total_debit'],
                'bill_date' => $row['bill_date'],
                'create_date' => $row['create_date'],
                'account_name' => $row['account_name'],
                'account_category' => $row['account_category'],
                'vendor' => $row['vendor'],
                'acc_categ_percentage' => $row['acc_categ_percentage']
            ];
        }
    }

    echo json_encode(['data' => $data]);
} else {
    echo json_encode(['data' => []]);
}
