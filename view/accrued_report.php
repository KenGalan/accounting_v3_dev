<?php
$db = new Postgresql();
$conn = $db->getConnection();

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
  AND SP.ACTIVE 
  AND (sp.invoice_no != '' OR sp.invoice_no IS NOT NULL)
  -- AND svl.value != 0
ORDER BY rp.name, sp.origin, sp.invoice_no DESC
";

$res = pg_query($conn, $sql);

$partners = [];
while ($row = pg_fetch_assoc($res)) {
    $partners[$row['contact']][] = $row;
}

?>

<style>
    body {
        font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
        background-color: #FAF7F3;
    }
    table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
    th, td { border: 1px solid #ccc; padding: 6px; text-align: right; }
    th { background: #eee; }
    h3 { margin: 0; }
    .vendor-container {
        border: 1px solid #ccc; 
        padding: 10px; 
        margin-bottom: 20px; 
        border-radius: 8px;
        background: #fff;
    }
    .search-container {
        text-align: right;
        margin-bottom: 15px;
        display: flex;
        justify-content: flex-end;
    }
    #vendorSearch {
        width: 550px;
        padding: 8px 12px;
        font-size: 11pt;
        border: 1px solid #bbb;
        border-radius: 6px;
        outline: none;
        color: #000000;
        transition: 0.3s;
        margin-right: 15px;
        margin-bottom: 15px;
    }

    #vendorSearch:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    }
    .exp{
        /* margin-right: 35px; */ 
        margin-left: 5px;
        margin-top: 25px;
        position: absolute;
        background-color: #7C7BAD !important;
    }
</style>

<a href="ajax/transaction/export.php" class="btn btn-primary exp">EXPORT</a>
<div class="search-container">
<input type="text" id="vendorSearch" placeholder="Search partner, po, or invoice..." />
</div>

<?php foreach ($partners as $partner => $rows): ?>

    <div class="vendor-container">
        <h3><?= htmlspecialchars($partner) ?></h3>
        <h4>PO Reference: <?= htmlspecialchars($rows[0]['po'] ?: 'N/A') ?></h4>

        <table class="report-table">
            <thead>
                <tr>
                    <th style="text-align:left;">INVOICE #</th>
                    <th style="text-align:left;">DATE</th>
                    <th>AMOUNT</th>
                    <th>1-30 DAYS</th>
                    <th>31-60 DAYS</th>
                    <th>61-90 DAYS</th>
                    <th>91-120 DAYS</th>
                    <th>121-150 DAYS</th>
                    <th>150 DAYS</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($rows as $r): 
                
                $amount = floatval($r['amount_value']);
                $days = intval($r['days_overdue']); 
                
                $d1_30 = $d31_60 = $d61_90 = $d91_120 = $d121_150 = $d150_up = 0;

                if ($days >= 1 && $days <= 30) $d1_30 = $amount;
                elseif ($days >= 31 && $days <= 60) $d31_60 = $amount;
                elseif ($days >= 61 && $days <= 90) $d61_90 = $amount;
                elseif ($days >= 91 && $days <= 120) $d91_120 = $amount;
                elseif ($days >= 121 && $days <= 150) $d121_150 = $amount;
                elseif ($days > 150) $d150_up = $amount;

            ?>
                <tr>
                    <td style="text-align:left;"><?= htmlspecialchars($r['invoice_no']) ?></td>
                    <td style="text-align:left;"><?= date("m/d/Y", strtotime($r['date_done'])) ?></td>

                    <td>₱ <?= number_format($amount, 2) ?></td>

                    <td>₱ <?= number_format($d1_30, 2) ?></td>
                    <td>₱ <?= number_format($d31_60, 2) ?></td>
                    <td>₱ <?= number_format($d61_90, 2) ?></td>
                    <td>₱ <?= number_format($d91_120, 2) ?></td>
                    <td>₱ <?= number_format($d121_150, 2) ?></td>
                    <td>₱ <?= number_format($d150_up, 2) ?></td>
                </tr>
            <?php endforeach; ?>

            </tbody>
            </table>

            </div>

            <?php endforeach; ?>
<script>
document.getElementById("vendorSearch").addEventListener("keyup", function () {
    let value = this.value.toLowerCase().trim();

    document.querySelectorAll(".vendor-container").forEach(container => {
        
        let partner = container.querySelector("h3").innerText.toLowerCase();
        let po = container.querySelector("h4").innerText.toLowerCase();

        let rows = container.querySelectorAll("tbody tr");
        let hasRowMatch = false;

        if (value === "") {
            container.style.display = "";
            rows.forEach(r => r.style.display = "");
            return;
        }

        rows.forEach(row => {
            let rowText = row.innerText.toLowerCase();
            if (rowText.includes(value)) {
                row.style.display = "";
                hasRowMatch = true;
            } else {
                row.style.display = "none";
            }
        });

        let matchHeader = partner.includes(value) || po.includes(value);

        if (matchHeader || hasRowMatch) {
            container.style.display = "";
            
            if (matchHeader) {
                rows.forEach(r => r.style.display = "");
            }
        } else {
            container.style.display = "none";
        }
    });
});

</script>