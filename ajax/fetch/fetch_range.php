<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

$db = new Postgresql();

$query_fetch_date_range = "
	SELECT is_dept_distributed, id, year_month, start_date, end_date, added_on, added_by, changed_on, change_by 
    FROM M_ACC_DATE_RANGE
    ORDER BY ID DESC
";

$result_date_range = $db->fetchAll($query_fetch_date_range);

echo json_encode($result_date_range);
