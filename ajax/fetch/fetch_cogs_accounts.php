<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

$search = $_GET['search'];

$sql = "
    SELECT 
        id,
        CODE || ' ' || name AS text
    FROM account_account
    WHERE name ILIKE '%COGS%'
";

if (!empty($search)) {
    $search = pg_escape_string($conn, $search);

    $sql .= "
        AND (
            CODE ILIKE '%$search%'
            OR name ILIKE '%$search%'
        )
    ";
}

$sql .= " ORDER BY ID DESC";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'text' => $row['text']
        ];
    }
}

echo json_encode($data);