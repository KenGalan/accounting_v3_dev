<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

$sql = "
 SELECT 
        B.ID,
        B.REFERENCE,
        TO_CHAR(B.ADDED_ON,
        'YYYY-MM-DD'
        ) AS ADDED_ON,
        B.ADDED_BY,
        '' AS CHANGED_BY,
        '' AS CHANGED_ON
    FROM M_ACC_CUSTOMIZED_DIST_ACCOUNTS B 
    WHERE B.ACTIVE = TRUE
    ORDER BY B.ADDED_ON DESC
";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'reference' => $row['reference'],
            'date_added' => $row['added_on'],
            'added_by' => $row['added_by']
            // 'changed_by' => $row['changed_by'],
            // 'changed_on' => $row['changed_on']
        ];
    }
}

echo json_encode(['data' => $data]);
