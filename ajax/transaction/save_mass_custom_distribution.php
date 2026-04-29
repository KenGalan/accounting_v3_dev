<?php
session_start();
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
$rows = isset($input['rows']) ? $input['rows'] : [];
$added_by = $_SESSION['ppc']['emp_no'];
// echo $rows;
// exit;

if (empty($rows)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No selected rows.'
    ]);
    exit;
}

foreach ($rows as $r) {
    $move_id         = isset($r['move_id']) ? trim($r['move_id']) : '';
    $total_amount    = isset($r['total_amount']) ? $r['total_amount'] : '';
    $accounting_date = isset($r['accounting_date']) ? trim($r['accounting_date']) : '';

    $from_date = isset($r['from_date']) && $r['from_date'] !== null ? trim($r['from_date']) : null;
    $to_date   = isset($r['to_date']) && $r['to_date'] !== null ? trim($r['to_date']) : null;
    $sbu       = array_key_exists('sbu', $r) ? $r['sbu'] : null;
    $mo_dist   = isset($r['mo_dist']) && $r['mo_dist'] !== null ? trim($r['mo_dist']) : null;

    if ($move_id === '' || $total_amount === '' || $accounting_date === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Missing row data.'
        ]);
        exit;
    }

    if (($from_date !== null && $to_date === null) || ($from_date === null && $to_date !== null)) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please select both From Date and To Date.'
        ]);
        exit;
    }

    if ($from_date !== null && $to_date !== null && $from_date > $to_date) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid date range.'
        ]);
        exit;
    }

    $check_sql = "
        SELECT id, sbu
        FROM m_acc_cust_dist
        WHERE move_id = $1
        LIMIT 1
    ";

    $check_result = pg_query_params($conn, $check_sql, [$move_id]);

    if ($check_result && pg_num_rows($check_result) > 0) {

        $existing_row = pg_fetch_assoc($check_result);

        $setParts = [
            "total_amount = $" . (count($params = [$move_id]) + 1),
        ];

        $params = [$move_id];

        $setParts = [];
        
        if ($from_date !== null && $to_date !== null) {
            $params[] = $from_date;
            $setParts[] = "from_date = $" . count($params);

            $params[] = $to_date;
            $setParts[] = "to_date = $" . count($params);
        }

        $params[] = $total_amount;
        $setParts[] = "total_amount = $" . count($params);

        $params[] = $accounting_date;
        $setParts[] = "accounting_date = $" . count($params);

        $params[] = $added_by;
        $setParts[] = "added_by = $" . count($params);

        if ($sbu !== null) {
            if (is_string($sbu)) {
                $sbu = trim($sbu);
                $sbu = $sbu === '' ? [] : explode(',', $sbu);
            } elseif (!is_array($sbu)) {
                $sbu = [$sbu];
            }

            $sbu = array_values(array_unique(array_filter(array_map('intval', $sbu))));

            $existing_sbu = [];
            if (!empty($existing_row['sbu'])) {
                $trimmed = trim($existing_row['sbu'], '{}');
                if ($trimmed !== '') {
                    $existing_sbu = array_values(array_unique(array_filter(array_map('intval', explode(',', $trimmed)))));
                }
            }

            $merged_sbu = array_values(array_unique(array_merge($existing_sbu, $sbu)));
            $pg_sbu_array = '{' . implode(',', $merged_sbu) . '}';

            $params[] = $pg_sbu_array;
            $setParts[] = "sbu = $" . count($params);
        }

        if ($mo_dist !== null && $mo_dist !== '') {
            $params[] = $mo_dist;
            $setParts[] = "mo_dist = $" . count($params);
        }

        $update_sql = "
            UPDATE m_acc_cust_dist
            SET " . implode(", ", $setParts) . "
            WHERE move_id = $1
        ";

        $update_result = pg_query_params($conn, $update_sql, $params);

        if (!$update_result) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to update data.'
            ]);
            exit;
        }

    } else {

        $pg_sbu_array = '{}';

        if ($sbu !== null) {
            if (is_string($sbu)) {
                $sbu = trim($sbu);
                $sbu = $sbu === '' ? [] : explode(',', $sbu);
            } elseif (!is_array($sbu)) {
                $sbu = [$sbu];
            }

            $sbu = array_values(array_unique(array_filter(array_map('intval', $sbu))));
            $pg_sbu_array = '{' . implode(',', $sbu) . '}';
        }

        $insert_sql = "
            INSERT INTO m_acc_cust_dist (
                move_id,
                from_date,
                to_date,
                total_amount,
                accounting_date,
                added_by,
                sbu,
                mo_dist
            )
            VALUES ($1, $2, $3, $4, $5, $6, $7, $8)
        ";

        $insert_result = pg_query_params($conn, $insert_sql, [
            $move_id,
            $from_date,
            $to_date,
            $total_amount,
            $accounting_date,
            $added_by,
            $pg_sbu_array,
            $mo_dist
        ]);

        if (!$insert_result) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Failed to save data.'
            ]);
            exit;
        }
    }
}

echo json_encode([
    'status' => 'success',
    'message' => 'Mass changes saved successfully.'
]);