<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode([]);
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "
    SELECT DISTINCT
        SPLIT_PART(pc.complete_name, ' - ', 1) AS name
    FROM product_category pc
    WHERE pc.complete_name LIKE '% - %'
      AND pc.complete_name NOT ILIKE 'TPC%'
      AND NOT EXISTS (
          SELECT 1
          FROM m_acc_sbu_maint s
          WHERE s.sbu = SPLIT_PART(pc.complete_name, ' - ', 1)
          AND s.active = TRUE
      )
";

if ($search !== '') {
    $search = pg_escape_string($conn, $search);
    $sql .= " AND SPLIT_PART(pc.complete_name, ' - ', 1) ILIKE '%{$search}%'";
}

$sql .= " ORDER BY name ASC";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['name'],
            'text' => $row['name']
        ];
    }
}

echo json_encode($data);