<?php
$db = new Postgresql();
$conn = $db->getConnection();

// $is_dept_distributed = $_POST['is_dept_distributed'];

$query = "SELECT DISTINCT ID AS DATE_RANGE_ID, YEAR_MONTH, START_DATE, END_DATE FROM M_ACC_DATE_RANGE ORDER BY YEAR_MONTH DESC";

$result = pg_query($conn, $query);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            "year_month" => $row["year_month"],
            "date_range_id" => $row["date_range_id"],
            "start_date" => $row["start_date"],
            "end_date" => $row["end_date"]
        ];
    }
}

echo json_encode($data);
