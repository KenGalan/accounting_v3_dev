<?php
$db = new Postgresql();
$conn = $db->getConnection();

$sql = "
WITH params AS (
    SELECT CURRENT_DATE AS selected_date
)
SELECT
    am.id AS am_id,
    rp.name AS vendor,
    am.name AS reference,
    am.invoice_date,
    am.invoice_date_due,
    am.id AS move_id,

    /* Make amount always positive */
    CASE 
        WHEN am.amount_total_signed < 0 THEN am.amount_total_signed * -1
        ELSE am.amount_total_signed
    END AS amount,
    /* NOT DUE */
    CASE 
        WHEN am.invoice_date_due > p.selected_date THEN 
            CASE WHEN am.amount_residual_signed < 0 
                 THEN am.amount_residual_signed * -1 
                 ELSE am.amount_residual_signed END
        ELSE 0 
    END AS not_due,

    /* 1–30 DAYS */
    CASE 
        WHEN (p.selected_date - am.invoice_date_due) BETWEEN 1 AND 30 THEN
            CASE WHEN am.amount_total_signed < 0 
                 THEN am.amount_total_signed * -1 
                 ELSE am.amount_total_signed END
        ELSE 0 
    END AS days_1_30,

    /* 31–60 DAYS */
    CASE 
        WHEN (p.selected_date - am.invoice_date_due) BETWEEN 31 AND 60 THEN
            CASE WHEN am.amount_total_signed < 0 
                 THEN am.amount_total_signed * -1 
                 ELSE am.amount_total_signed END
        ELSE 0 
    END AS days_31_60,

    /* 61–90 DAYS */
    CASE 
        WHEN (p.selected_date - am.invoice_date_due) BETWEEN 61 AND 90 THEN
            CASE WHEN am.amount_total_signed < 0 
                 THEN am.amount_total_signed * -1 
                 ELSE am.amount_total_signed END
        ELSE 0 
    END AS days_61_90,

    /* 91–120 DAYS */
    CASE 
        WHEN (p.selected_date - am.invoice_date_due) BETWEEN 91 AND 120 THEN
            CASE WHEN am.amount_total_signed < 0 
                 THEN am.amount_total_signed * -1 
                 ELSE am.amount_total_signed END
        ELSE 0 
    END AS days_91_120,

    /* 121–150 DAYS */
    CASE 
        WHEN (p.selected_date - am.invoice_date_due) BETWEEN 121 AND 150 THEN
            CASE WHEN am.amount_total_signed < 0 
                 THEN am.amount_total_signed * -1 
                 ELSE am.amount_total_signed END
        ELSE 0 
    END AS days_121_150,

    /* >150 DAYS */
    CASE 
        WHEN (p.selected_date - am.invoice_date_due) > 150 THEN
            CASE WHEN am.amount_total_signed < 0 
                 THEN am.amount_total_signed * -1 
                 ELSE am.amount_total_signed END
        ELSE 0 
    END AS days_over_150

FROM account_move am
JOIN res_partner rp ON rp.id = am.partner_id
CROSS JOIN params p
WHERE am.type = 'out_invoice'
  AND am.invoice_payment_state = 'not_paid'
  AND am.state = 'posted'
  AND am.pv_code IS NULL
  AND am.invoice_date <= p.selected_date
  AND am.name ILIKE '%INV/%'
  AND am.name NOT LIKE '%DINV%'
ORDER BY rp.name, am.invoice_date_due
";

$res = pg_query($conn, $sql);

$data = [];
while ($row = pg_fetch_assoc($res)) {
    $data[] = $row;
}

echo json_encode(["data" => $data]);
?>
