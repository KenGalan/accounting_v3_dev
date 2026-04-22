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
        asm.id,
        asm.sbu,
        asm.added_on,
        asm.added_by,
        asm.analytic_account_id,
        aaa.name AS analytic_account
    FROM m_acc_sbu_maint asm
    LEFT JOIN ACCOUNT_ANALYTIC_ACCOUNT aaa ON asm.analytic_account_id = aaa.id
    WHERE asm.ACTIVE = TRUE
    ORDER BY asm.added_on DESC
";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'sbu' => $row['sbu'],
            'date_added' => !empty($row['added_on']) ? date('Y-m-d', strtotime($row['added_on'])) : '',
            'added_by' => $row['added_by'],
            'analytic_account_id' => $row['analytic_account_id'],
            'analytic_account' => $row['analytic_account'],
        ];
    }
}

echo json_encode([
    'data' => $data
]);