<?php
header('Content-Type: application/json');

$db = new Postgresql();
$conn = $db->getConnection();

if (!$conn) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$move_id = isset($input['move_id']) ? trim($input['move_id']) : '';
$rows    = isset($input['rows']) && is_array($input['rows']) ? $input['rows'] : [];
// echo $move_id;
// exit;

if ($move_id === '' || empty($rows)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields.'
    ]);
    exit;
}

foreach ($rows as $r) {
    $row_move_id = isset($r['move_id']) && $r['move_id'] !== '' ? trim($r['move_id']) : $move_id;
    $move_line_id = isset($r['move_line_id']) ? trim($r['move_line_id']) : '';
    $sbu = isset($r['sbu']) ? $r['sbu'] : [];
    $debit = isset($r['debit']) ? $r['debit'] : 0;
    $credit = isset($r['credit']) ? $r['credit'] : 0;
    $analytic_account_id = isset($r['analytic_account_id']) && $r['analytic_account_id'] !== '' ? $r['analytic_account_id'] : null;
    $analytic_account = isset($r['analytic_account']) ? trim($r['analytic_account']) : '';
    $account_id = isset($r['account_id']) && $r['account_id'] !== '' ? $r['account_id'] : null;
    $account_name = isset($r['account_name']) ? trim($r['account_name']) : '';

    if ($move_line_id === '' || $row_move_id === '') {
        continue;
    }

    if (is_string($sbu)) {
        $sbu = trim($sbu);
        if ($sbu === '') {
            $sbu = [];
        } else {
            $sbu = explode(',', $sbu);
        }
    } elseif (!is_array($sbu)) {
        $sbu = [$sbu];
    }

    $sbu = array_values(array_unique(array_filter(array_map('intval', $sbu))));

    if (empty($sbu)) {
        continue;
    }

    $check_sql = "
        SELECT move_line_id, sbu
        FROM m_acc_cust_dist_line
        WHERE move_line_id = $1
        LIMIT 1
    ";
    $check_result = pg_query_params($conn, $check_sql, array($move_line_id));

    if ($check_result && pg_num_rows($check_result) > 0) {
        $existing_row = pg_fetch_assoc($check_result);

        $existing_sbu = [];

        if (!empty($existing_row['sbu'])) {
            $trimmed = trim($existing_row['sbu'], '{}');
            if ($trimmed !== '') {
                $existing_sbu = array_values(array_unique(array_filter(array_map('intval', explode(',', $trimmed)))));
            }
        }

        $merged_sbu = array_values(array_unique(array_merge($existing_sbu, $sbu)));
        $pg_sbu_array = '{' . implode(',', $merged_sbu) . '}';

        $update_sql = "
            UPDATE m_acc_cust_dist_line
            SET sbu = $2,
                debit = $3,
                credit = $4,
                analytic_account_id = $5,
                analytic_account = $6,
                account_id = $7,
                account_name = $8,
                move_id = $9
            WHERE move_line_id = $1
        ";

        $update_result = pg_query_params($conn, $update_sql, array(
            $move_line_id,
            $pg_sbu_array,
            $debit,
            $credit,
            $analytic_account_id,
            $analytic_account,
            $account_id,
            $account_name,
            $row_move_id
        ));

        if (!$update_result) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update distribution line.'
            ]);
            exit;
        }
    } else {
        $pg_sbu_array = '{' . implode(',', $sbu) . '}';

        $insert_sql = "
            INSERT INTO m_acc_cust_dist_line (
                move_id,
                move_line_id,
                sbu,
                debit,
                credit,
                analytic_account_id,
                analytic_account,
                account_id,
                account_name
            )
            VALUES (
                $1,
                $2,
                $3,
                $4,
                $5,
                $6,
                $7,
                $8,
                $9
            )
        ";

        $insert_result = pg_query_params($conn, $insert_sql, array(
            $row_move_id,
            $move_line_id,
            $pg_sbu_array,
            $debit,
            $credit,
            $analytic_account_id,
            $analytic_account,
            $account_id,
            $account_name
        ));

        if (!$insert_result) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save distribution line.'
            ]);
            exit;
        }
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'Custom distribution lines saved successfully.'
]);