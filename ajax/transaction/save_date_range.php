<?php
session_start();
header("Content-Type: application/json");


$db = new Postgresql();
$conn = $db->getConnection();

$year_month = $_POST['year_month'];
$start_date = $_POST['start_date'];
$end_date   = $_POST['end_date'];

$added_by = $_SESSION['ppc']['emp_no'];

    // Added by Ivan 03/25/26 - Check for overlapping date ranges
    $check_sql = "
        SELECT 1
        FROM M_ACC_DATE_RANGE
        WHERE
            ($1::date BETWEEN start_date AND end_date)
            OR
            ($2::date BETWEEN start_date AND end_date)
        LIMIT 1
    ";

    $overlap_res = pg_query_params($conn, $check_sql, [
        $start_date,
        $end_date
    ]);

    if (pg_num_rows($overlap_res) > 0) {

        echo json_encode([
            "status" => "overlap",
            "message" => "Date range overlaps with an existing period."
        ]);
        exit;
    }  

    $check_sql = "
        SELECT 1
        FROM M_ACC_DATE_RANGE
        WHERE is_dept_distributed = TRUE
        AND year_month >= $1
        LIMIT 1
    ";

    $migulo_lockdown = pg_query_params($conn, $check_sql, [$year_month]);

    if ($migulo_lockdown && pg_num_rows($migulo_lockdown) > 0) {

        echo json_encode([
            "status" => "silos",
            "message" => "May distributed ka na sa hinaharap kaya bawal na bumalik sa nakaraan."
        ]);
        exit;
    }

$insert_query = "
    INSERT INTO M_ACC_DATE_RANGE 
    (year_month, start_date, end_date, added_on, added_by, active)
    VALUES ($1, $2, $3, NOW(), $4, 'Y')
";
$insert_result = pg_query_params($conn, $insert_query, [
    $year_month,
    $start_date,
    $end_date,
    $added_by
]);

if ($insert_result) {
    echo json_encode([
        "status" => "success",
        "message" => "Date range saved successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to save date range."
    ]);
}

?>
