<?php

$id = $_GET['id'];

// require '../../db/Postgresql.php';

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
$db = new Postgresql();

// $from = $_GET['fromDate'] ?? null;
// $to   = $_GET['toDate'] ?? null;

// Query using dates
// $q = 'SELECT
// adje.account_move_name,
// adje.reference "Reference",
// TO_CHAR(adje.account_move_date, \'MM/DD/YYYY\') "Date",
// adje.journal "Journal",
// adji.account_code "Journal Items/Account",
// adji.item_label "Journal Items/Label",
// adji.analytic_account "Journal Items/Analytic Account",
// COALESCE(adji.debit,0) "Journal Items/Debit",
// COALESCE(adji.credit,0) "Journal Items/Credit"
// FROM
// M_ACC_DATE_RANGE ADR
// JOIN M_ACC_DIST_JOURNAL_ENTRIES adje ON adje.date_range_id = ADR.id
// JOIN M_ACC_DIST_JOURNAL_ITEMS adji ON adji.main_id = adje.id
// WHERE adr.id = ' . $id . '
// ORDER BY ADJE.ACCOUNT_MOVE_NAME,COALESCE(adji.debit,0) DESC, adji.account_code';

$q = 'select a.* from (SELECT
adje.account_move_name,
adje.reference "Reference",
TO_CHAR(adje.account_move_date, \'MM/DD/YYYY\') "Date",
adje.journal "Journal",
adji.account_code "Journal Items/Account",
adji.item_label "Journal Items/Label",
adji.analytic_account "Journal Items/Analytic Account",
COALESCE(adji.debit,0) "Journal Items/Debit",
COALESCE(adji.credit,0) "Journal Items/Credit"
FROM
M_ACC_DATE_RANGE ADR
JOIN M_ACC_DIST_JOURNAL_ENTRIES adje ON adje.date_range_id = ADR.id
JOIN M_ACC_DIST_JOURNAL_ITEMS adji ON adji.main_id = adje.id
WHERE adr.id = ' . $id . '
ORDER BY ADJE.ACCOUNT_MOVE_NAME,COALESCE(adji.debit,0) DESC, adji.account_code)a
UNION ALL
	select b.* from (
	select
	adje.account_move_name,
adje.reference ,
TO_CHAR(adje.account_move_date, \'MM/DD/YYYY\') "Date",
adje.journal "Journal",
aa.code "Journal Items/Account",
\'\' "Journal Items/Label",
atw.analytic_account "Journal Items/Analytic Account",
COALESCE(atw.debit,0) "Journal Items/Debit",
COALESCE(atw.credit,0) "Journal Items/Credit"
from m_acc_to_wip atw
join M_ACC_DIST_JOURNAL_ENTRIES adje on adje.id = atw.main_id
join account_account aa on aa.id = atw.account_id
join M_ACC_DATE_RANGE ADR on ADR.id = adje.date_range_id
where adr.id =' . $id . '
ORDER BY ADJE.ACCOUNT_MOVE_NAME,COALESCE(atw.debit,0) DESC, aa.code)b
';
$result = $db->fetchAll($q);
$result2 = $result;
// echo '<pre>';
// echo $q;
// // var_dump($result);
// exit;
// echo $q;
// Filename

unset($result2[0]['account_move_name']);
// echo '<pre>';
// var_dump($result2);
// exit;

if (!$result) die("No data found.");

$filename = "export_" . date("Ymd_His") . ".xlsx";

header('Content-disposition: attachment; filename="' . $filename . '"');
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");

$writer = new XLSXWriter();

// Write header row
$writer->writeSheetHeader(
    'Sheet1',
    array_fill_keys(array_keys($result2[0]), 'string')
);
$checker = '';
// Write data rows
foreach ($result as $row) {


    if ($row['account_move_name'] == $checker) {

        $row['Reference'] = '';
        $row['Date'] = '';
        $row['Journal'] = '';
    } else {
        $checker = $row['account_move_name'];
    }
    // echo $checker . 'try';
    unset($row['account_move_name']);
    // var_dump($row);
    $writer->writeSheetRow('Sheet1', array_values($row));
}

$writer->writeToStdOut();
exit;
