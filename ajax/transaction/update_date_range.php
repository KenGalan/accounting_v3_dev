<?php
session_start();
header("Content-Type: application/json");

$db = new Postgresql();
$conn = $db->getConnection();

$old_year_month = $_POST['old_year_month'];
$start_date     = $_POST['start_date'];
$end_date       = $_POST['end_date'];

$changed_by = $_SESSION['ppc']['emp_no'];

    $check_query = "
        SELECT COUNT(*) 
        FROM M_ACC_DATE_RANGE 
        WHERE year_month = $1
    ";
    $check_result = pg_query_params($conn, $check_query, [$old_year_month]);

    if (!$check_result) {
        echo json_encode([
            "status" => "error",
            "message" => "Error checking date range."
        ]);
        exit;
    }
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


$count = pg_fetch_result($check_result, 0, 0);
if ($count == 0) {
    echo json_encode([
        "status" => "not_found",
        "message" => "Record not found. Update failed."
    ]);
    exit;
}

$duplicate_query = "
    SELECT COUNT(*) 
    FROM M_ACC_DATE_RANGE 
    WHERE start_date = $1
      AND year_month <> $2
";
$duplicate_result = pg_query_params($conn, $duplicate_query, [
    $start_date,
    $old_year_month
]);

if ($duplicate_result) {
    $dup_count = pg_fetch_result($duplicate_result, 0, 0);

    if ($dup_count > 0) {
        echo json_encode([
            "status" => "exists",
            "message" => "Start date already exists in another record. Update cancelled."
        ]);
        exit;
    }
}

$new_year_month = substr($start_date, 0, 7);

$sql_check = "
        SELECT 1
        FROM M_ACC_DATE_RANGE
        WHERE is_dept_distributed = TRUE
        AND year_month >= $1
        LIMIT 1
    ";

    $migulo_lockdown = pg_query_params($conn, $sql_check, [$new_year_month]);

    if ($migulo_lockdown && pg_num_rows($migulo_lockdown) > 0) {

        echo json_encode([
            "status" => "silos",
            "message" => "May distributed ka na sa hinaharap kaya bawal na bumalik sa nakaraan."
        ]);
        exit;
    }

$update_query = "
    UPDATE M_ACC_DATE_RANGE
    SET 
        year_month = $1,
        start_date = $2,
        end_date = $3,
        changed_on = NOW(),
        change_by = $4
    WHERE year_month = $5
";

$update_result = pg_query_params($conn, $update_query, [
    $new_year_month,
    $start_date,
    $end_date,
    $changed_by,
    $old_year_month
]);

if ($update_result) {
    echo json_encode([
        "status" => "success",
        "message" => "Date range updated successfully."
    ]);
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to update date range."
    ]);
}
?>
