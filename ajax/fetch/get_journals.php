<?php
header("Content-Type: application/json");

$db = new Postgresql();
$conn = $db->getConnection();

$query = "SELECT id, name AS journal_name FROM ACCOUNT_JOURNAL where company_id = 1 ORDER BY name ASC";
$result = pg_query($conn, $query);

$data = [];
while ($row = pg_fetch_assoc($result)) {
    $data[] = [
        "id" => $row["id"],
        "name" => $row["journal_name"]
    ];
}

echo json_encode($data);
