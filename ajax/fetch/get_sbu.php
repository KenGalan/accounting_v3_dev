<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode([]);
    exit;
} 

$sql = "
    SELECT id, sbu AS name
    FROM m_acc_sbu_maint
    ORDER BY name ASC
";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'text' => $row['name']
        ];
    }
}

echo json_encode($data);