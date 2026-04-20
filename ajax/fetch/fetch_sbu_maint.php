<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode(['data' => []]);
    exit;
}

$sql = "
    SELECT
        id,
        sbu,
        added_on,
        added_by
    FROM m_acc_sbu_maint
    WHERE ACTIVE = TRUE
    ORDER BY added_on DESC
";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'sbu' => $row['sbu'],
            'date_added' => !empty($row['added_on']) ? date('Y-m-d', strtotime($row['added_on'])) : '',
            'added_by' => $row['added_by']
        ];
    }
}

echo json_encode([
    'data' => $data
]);