<?php
header('Content-Type: application/json');
session_start();

$db = new Postgresql();
$conn = $db->getConnection();

// $sql = "
// 		SELECT 
//         B.ID,
//         -- B.REFERENCE,
// 		C.id account_id,
//         B.cogs_account_id,
// 		C.CODE || ' ' || C.name AS account_name,
//         TO_CHAR(B.ADDED_ON,
//         'YYYY-MM-DD'
//         ) AS ADDED_ON,
//         B.ADDED_BY,
//         '' AS CHANGED_BY,
//         '' AS CHANGED_ON
//     FROM M_ACC_CUSTOMIZED_DIST_ACCOUNTS B 
// 	JOIN account_account C ON B.account_id = C.id
//     WHERE B.ACTIVE = TRUE
//     ORDER BY B.ADDED_ON DESC
// "; 

$sql = "
	 	SELECT 
        B.ID,
        -- B.REFERENCE,
		C.CODE || ' ' || C.name AS account_name,
        TO_CHAR(B.ADDED_ON,
        'YYYY-MM-DD'
        ) AS ADDED_ON,
        B.ADDED_BY,
		B.COGS_ACCOUNT_ID,
		D.CODE || ' ' || D.name AS account_name_cogs,
        '' AS CHANGED_BY,
        '' AS CHANGED_ON
    FROM M_ACC_CUSTOMIZED_DIST_ACCOUNTS B 
	JOIN account_account C ON B.account_id = C.id
	JOIN account_account D ON B.cogs_account_id = D.id
    WHERE B.ACTIVE = TRUE
    ORDER BY B.ADDED_ON DESC";

$result = pg_query($conn, $sql);

$data = [];

if ($result) {
    while ($row = pg_fetch_assoc($result)) {
        $data[] = [
            'id' => $row['id'],
            'account_name' => $row['account_name'],
            'date_added' => $row['added_on'],
            // 'account_id' => $row['account_id'],
            'cogs_account_id' => $row['cogs_account_id'],
            'account_name_cogs' => $row['account_name_cogs'],
            'added_by' => $row['added_by']
            // 'changed_by' => $row['changed_by'],
            // 'changed_on' => $row['changed_on']
        ];
    }
}

echo json_encode(['data' => $data]);
