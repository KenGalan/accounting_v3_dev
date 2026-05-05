<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

session_start();
$db = new Postgresql();

$category_id  = $_POST['category_id'];
$new_category = trim($_POST['new_category']);

if (!$category_id || $new_category == '') {
    echo json_encode([
        "status" => "error",
        "message" => "Missing category or new category name."
    ]);
    exit;
}

$new_category_clean = strtolower(trim($new_category));

$check = $db->fetchAll("
    SELECT id 
    FROM M_ACC_CATEGORY_TBL 
    WHERE LOWER(TRIM(acc_category)) = '$new_category_clean'
    AND active = true
    LIMIT 1
");

if (!empty($check)) {
    $db->query("ROLLBACK");
    echo json_encode([
        "status" => "error",
        "message" => "Template name already exists."
    ]);
    exit;
}

$added_by = $_SESSION['ppc']['emp_no'];

try {

    $db->query("BEGIN");

    $check = $db->fetchAll("
        SELECT id 
        FROM M_ACC_CATEGORY_TBL 
        WHERE LOWER(acc_category) = LOWER('$new_category')
        AND active = true
    ");

    if (!empty($check)) {
        $db->query("ROLLBACK");
        echo json_encode([
            "status" => "error",
            "message" => "Category name already exists."
        ]);
        exit;
    }

    $newCat = $db->fetchAll("
      INSERT INTO M_ACC_CATEGORY_TBL
        (
            acc_category,
            journal_id,
            mo_pct_ref,
            copy_from,
            added_by,
            active
        )
        SELECT
            '$new_category',
            journal_id,
            mo_pct_ref,
            id,
            $added_by,
            active
        FROM M_ACC_CATEGORY_TBL
        WHERE id = '$category_id'
        RETURNING id
    ");

    if (empty($newCat)) {
        throw new Exception("Original category not found.");
    }

    $new_category_id = $newCat[0]['id']; 

    $db->query("
        INSERT INTO M_ACC_CATEGORY_ACCOUNTS
        (
            acc_category_id,
            account_id
        )
        SELECT
            '$new_category_id',
            account_id
        FROM M_ACC_CATEGORY_ACCOUNTS
        WHERE acc_category_id = '$category_id'
    ");

    $db->query("
        INSERT INTO M_ACC_COST_DISTRIBUTION
        (
            m_acc_category_id,
            debit_to,
            distribution_percentage,
            group_id,
            wip_account,
            analytic_account_id,
            added_by
        )
        SELECT
            '$new_category_id',
            debit_to,
            distribution_percentage,
            group_id,
            wip_account,
            analytic_account_id,
            $added_by
        FROM M_ACC_COST_DISTRIBUTION
        WHERE m_acc_category_id = '$category_id'
    ");

    $db->query("COMMIT");

    echo json_encode([
        "status" => "success",
        "message" => "Template duplicated successfully.",
        "new_category_id" => $new_category_id
    ]);

} catch (Exception $e) {

    $db->query("ROLLBACK");

    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}