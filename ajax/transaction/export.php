<?php

$db = new Postgresql();
$conn = $db->getConnection();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=accrued_aging_report.csv');

echo "\xEF\xBB\xBF";

$output = fopen("php://output", "w");

// CSV HEADER
fputcsv($output, [
    // "Reference",
    "Vendor",
    "Invoice #",
    "PO #",
    "Amount",
    // "Is Reversed",
    "Date Received"
    // "Days Overdue"
]);

$sql = "
SELECT 
    sp.name AS reference,
    pt.name AS product,
    svl.value AS amount_value,
    rp.name AS contact,
    sp.invoice_no,
    sp.origin AS po,
    sp.is_reversed,
    sp.date_done AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila' AS date_done,
    (CURRENT_DATE - (sp.date_done AT TIME ZONE 'UTC' AT TIME ZONE 'Asia/Manila')::date) AS days_overdue
FROM product_product pp
JOIN product_template pt ON pp.product_tmpl_id = pt.id
JOIN stock_move sm ON sm.product_id = pp.id
JOIN stock_picking sp ON sm.picking_id = sp.id
JOIN res_partner rp ON sp.partner_id = rp.id
JOIN stock_valuation_layer svl ON svl.stock_move_id = sm.id
WHERE sp.name ILIKE '%WH/IN/%'
  AND sp.name NOT LIKE '%WH/INT%'
  AND sp.is_reversed IS NULL
  AND sp.state = 'done'
  AND PT.NAME ILIKE '%TPC.%'
  AND (sp.invoice_no != '' OR sp.invoice_no IS NOT NULL)
ORDER BY rp.name, sp.origin, sp.invoice_no DESC
";

$res = pg_query($conn, $sql);

while ($row = pg_fetch_assoc($res)) {
    fputcsv($output, [ 
        // $row['reference'],
        $row['contact'],
        $row['invoice_no'],
        $row['po'],
        '₱' . number_format((float)$row['amount_value'], 2),
        // $row['is_reversed'],
        $row['date_done']
        // $row['days_overdue']
    ]);
}

fclose($output);
exit;
?>
