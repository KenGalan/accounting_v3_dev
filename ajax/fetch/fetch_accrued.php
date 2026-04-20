<?php

$vendors = [];

while ($row = pg_fetch_assoc($result)) {
    $partner = $row['contact'];
    if (!isset($vendors[$partner])) {
        $vendors[$partner] = [];
    }
    $vendors[$partner][] = $row;
}

$html = "";

foreach ($vendors as $partner => $rows) {

    $html .= "
    <div class='vendor-container'>
        <h3>$partner</h3>

        <table>
            <thead>
                <tr>
                    <th style='text-align:left;'>PO#</th>
                    <th style='text-align:left;'>INV#</th>
                    <th style='text-align:left;'>DATE</th>
                    <th style='text-align:right;'>AMOUNT</th>
                </tr>
            </thead>
            <tbody>
    ";

    foreach ($rows as $r) {
        $po = htmlspecialchars($r['po']);
        $inv = htmlspecialchars($r['invoice_no']);
        $ref = htmlspecialchars($r['reference']);
        $amount = $r['amount'];  

        $html .= "
            <tr>
                <td style='text-align:left;'>$po</td>
                <td style='text-align:left;'>$inv</td>
                <td style='text-align:left;'>$ref</td>
                <td style='text-align:right;'>$amount</td>
            </tr>
        ";
    }

    $html .= "
            </tbody>
        </table>
    </div>
    ";
}

echo $html;
?>
