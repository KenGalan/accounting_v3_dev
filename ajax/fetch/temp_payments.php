<?php
$db = new Postgresql();

$temp_id = $_GET['temp_id'];

$where = "";

if($temp_id){
    $where = "WHERE temp_id = '$temp_id'";
}
$sql = "
    SELECT *
    FROM M_ACC_TEMP_PAYMENTS
    $where
    ORDER BY payment_date DESC
"; 

$rows = $db->fetchAll($sql);
?>

<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #e6e4e4ff;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 25px;
    }

    th,
    td {
        padding: 10px;
        /* border-bottom: 1px solid #000000; */
        text-align: left;
    }

    th {
        background-color: #7C7BAD;
        color: #ffffff;
    }

    button {
        padding: 10px 20px;
        /* background-color: #7C7BAD; */
        color: #fff;
        border: none;
        cursor: pointer;
    }

    #releasedBtn {
        background-color: green;
    }

    #clearedBtn {
        background-color: blue;
    }

    #deleteBtn {
        background-color: red;
    }

    #backBtn {
        background-color: gray;
    }

    #releasedBtnPerRow {
        background-color: green;
    }

    #clearedBtnPerRow {
        background-color: blue;
    }
</style>
<!-- <h2>Selected Payments</h2> -->
<div style="display: flex; align-items: center;">
    <img src="../../team_logo.png" alt="Team Logo" style="height: 70px;">
</div>
<h4>MASS OPTION</h4>
<button id="releasedBtn">Released</button>
<button id="clearedBtn">Cleared</button>
<button id="backBtn" onclick="window.location.href='../../payment_update.php'" style="float: right;">Go back</button>
<button id="deleteBtn">Cancel Template</button>
<br /><br />
<table border="1" width="100%">
        <tr>
        <th>Payment</th>
        <th>Amount</th>
        <!-- <th>Status</th> -->
        <th>Customer</th>
        <th>Date</th>
        <th style="width: 18%;"></th>
    </tr>

    <?php if ($rows): ?>
        <?php foreach ($rows as $r): ?>
            <tr id="<?= $r['payment_id'] ?>">
                <td><?= $r['payment_name'] ?></td>
                <td><?= $r['payment_amount'] ?></td>
                <!-- <td><?= $r['payment_status'] ?></td> -->
                <td><?= $r['partner_name'] ?></td>
                <td><?= $r['payment_date'] ?></td>
                <td>
                    <button
                        style="background:red"
                        onclick="deleteTempPayment(this, <?= $r['payment_id'] ?>)">Remove
                    </button>
                    <button
                        style="background:green" class="releasedPerRow"
                        onclick="updatePaymentStatusPerRow(this, <?= $r['payment_id'] ?>, 'released')">
                        Released
                    </button>

                    <button
                        style="background:blue" class="clearedPerRow"
                        onclick="updatePaymentStatusPerRow(this, <?= $r['payment_id'] ?>, 'cleared')">
                        Cleared
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>

</table>

<script>
    const urlParams = new URLSearchParams(window.location.search);
    const temp_id = urlParams.get('temp_id');
    clearedBtn();

    function processPayment(action) {

        if (!confirm('Are you sure?')) return;

        fetch('../transaction/process_payment_status.php', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'action=' + action + '&temp_id=' + temp_id
            })
            .then(res => res.json())
            .then(r => {

                if (r.status === 'success') {
                    alert('Process completed');
                            window.location.href =
                'ajax/fetch/temp_payments.php?status=' + status +
                '&temp_id=' + r.temp_id;
                } else {
                    alert('Process failed');
                }

            });

    }

    function deleteTempPayment(btn, payment_id) {

        if (!confirm('Delete this payment?')) return;

        fetch('../transaction/delete_temp_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'payment_id=' + payment_id
            })

            .then(res => res.json())
            .then(r => {

                if (r.status === 'success') {

                    btn.closest('tr').remove();

                } else {
                    alert('Delete failed');
                }

            });

    }

    function updatePaymentStatusPerRow(btn, payment_id, status) {

        if (!confirm('Update this payment status?')) return;

        fetch('../transaction/update_payment_status_row.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'payment_id=' + payment_id + '&status=' + status
            })
            .then(res => res.json())
            .then(r => { 

                if (r.status === 'success') {
                    btn.closest('tr').remove();

                } else {
                    alert('Update failed');
                }

            });
    }

    function clearedBtn() {
        let status = '<?= isset($rows[0]['payment_status']) ? $rows[0]['payment_status'] : '' ?>';
        if (status === 'released') {
            document.getElementById('releasedBtn').style.display = 'none';
            document.querySelectorAll('.releasedPerRow').forEach(el => el.style.display = 'none');
            return;
        }
    }

    document.getElementById('releasedBtn').onclick = function() {
        processPayment('released');
    }

    document.getElementById('clearedBtn').onclick = function() {
        processPayment('cleared');
    }

    document.getElementById('deleteBtn').onclick = function() {
        processPayment('delete');
    }
</script>