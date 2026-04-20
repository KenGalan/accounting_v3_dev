<?php
$db = new Postgresql();
$conn = $db->getConnection();

$emp_no = isset($_SESSION['ppc']['emp_no']) ? intval($_SESSION['ppc']['emp_no']) : 0;

$hasDashboardAccess = false;

if ($emp_no > 0) {
    $sql = "SELECT 1 FROM M_ACC_USER_MAINTENANCE 
            WHERE emp_no = $emp_no 
            AND is_dashboard = TRUE 
            LIMIT 1";
    $res = pg_query($conn, $sql);
    if ($res && pg_num_rows($res) > 0) {
        $hasDashboardAccess = true;
    }
}

if (!$hasDashboardAccess) {
    echo "<script>
            alert('You do not have access to the Dashboard. Please contact MIS or Systems to ask on access.');
            window.location.href = 'generated_distribution.php';
          </script>";
    exit;
}

?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #e6e4e4ff;
    }

    .container {
        background: #fff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
    }

    h2 {
        margin-bottom: 20px;
    }

    label {
        font-weight: bold;
        margin-right: 10px;
    }

    input[type=date] {
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button {
        padding: 6px 12px;
        background-color: #7C7BAD !important;
        border: none;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #7C7BAD !important;
    }

    button.active {
        background-color: #7C7BAD !important;
        box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.3);
        color: #ffffff !important;
        font-weight: 550;
        transform: scale(1.1);
    }

    table.dataTable thead th {
        background-color: #7C7BAD !important;
        color: white;
    }

    #moTableContainer,
    #deptTableContainer {
        margin-top: 20px;
    }

    .backBtn {
        background-color: #6c757d;
        margin-right: 10px;
        margin-bottom: 15px;
    }

    .btn-groups {
        margin-bottom: 15px;
        display: flex;
        gap: 10px;
    }

    .btn-primary {
        background-color: #7C7BAD !important;
        color: #000000;
        font-size: 14pt;
    }

    #invoice_wrapper {
        max-width: 100%;
        margin: 40px auto;
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    #invoiceTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #invoiceTable thead {
        background: #f8f9fb;
    }

    #invoiceTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD !important;
    }

    #invoiceTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
        background-color: #ffffff;
    }

    #invoiceTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #invoiceTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #invoiceTable td:last-child button {
        background: #7C7BAD !important;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #invoiceTable td:last-child button:hover {
        background: #7C7BAD !important;
    }

    #deptTableContainer,
    #moTableContainer,
    #sbuTableContainer,
    #invoice_wrapper {
        max-width: 100%;
        margin: 40px auto;
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    #deptTable,
    #moTable,
    #sbuTable,
    #invoiceTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #deptTable thead,
    #moTable thead,
    #sbuTable thead,
    #invoiceTable thead {
        background: #f8f9fb;
    }

    #deptTable th,
    #moTable th,
    #sbuTable th,
    #invoiceTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD !important;
    }

    #deptTable tbody tr,
    #moTable tbody tr,
    #sbuTable tbody tr,
    #invoiceTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #deptTable tbody tr:nth-child(even),
    #moTable tbody tr:nth-child(even),
    #sbuTable tbody tr:nth-child(even) #invoiceTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #deptTable td,
    #moTable td,
    #sbuTable td,
    #invoiceTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #deptTable td:last-child button,
    #moTable td:last-child button,
    #sbuTable td:last-child button {
        background: #7C7BAD !important;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #deptTable td:last-child button:hover,
    #moTable td:last-child button:hover,
    #sbuTable td:last-child button:hover {
        background: #7C7BAD;
    }

    .btn-design {
        margin-bottom: 15px;
        background-color: transparent !important;
        border: 2px solid darkgreen !important;
        color: #000000;
        box-shadow: none !important;
    }

    #moTable_processing {
        margin-top: 20px;
        font-weight: 550;
    }

    #deptTable_processing {
        margin-top: 20px;
        font-weight: 550;
    }

    #sbuTable_processing {
        margin-top: 35px;
        font-weight: 550;
    }

    .close {
        background-color: transparent !important;
        color: #000000 !important;
    }

    #filterSection {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    #filterSection select,
    #filterSection button {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    #filterSection select:focus,
    #filterSection button:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
    }

    #filterSection button {
        background-color: #007bff;
        color: white;
        border: none;
    }

    #filterSection button:hover {
        background-color: #0056b3;
    }

    #filterSection button#filterBtn {
        background-color: #28a745;
    }

    #filterSection button#filterBtn:hover {
        background-color: #1e7e34;
    }

    #yearMonthSelect {
        width: 235px !important;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #7C7BAD;
    }

    input:checked+.slider:before {
        transform: translateX(24px);
    }
</style>

<body>

    <!-- <div id="filterSection" style="margin-bottom: 20px;">
        <label>From:</label>
        <input type="date" id="fromDate" value="2025-07-01">
        <label>To:</label>
        <input type="date" id="toDate" value="2025-07-31">
        <button id="filterBtn">Filter</button>
    </div> -->

    <!-- <div id="filterSection" style="margin-bottom: 20px;">
        <label>From:</label>
        <input type="date" id="fromDate">
        <label>To:</label>
        <input type="date" id="toDate">
        <button id="filterBtn">Filter</button>
        <button id="createJournal" style="display: none;">Run Journal Entries</button>
    </div> -->

    <!-- <div class="instruct" style="margin-top: 15px; margin-bottom: 15px;">
        <span><i class="fa fa-circle" style="color: green;"></i> The highlighted date selection has already been distributed.</span>
    </div> -->

    <div id="filterSection" style="margin-bottom: 20px;">
        <select id="yearMonthSelect">
            <option value="">Select Month</option>
        </select>
        <!-- <button id="filterBtn">Filter</button> -->
        <button id="createJournal" style="display: none;" style="color: #ffffff !important;">Run Journal Entries</button>

        <?php if ($_SESSION['ppc']['emp_no'] == '10929' || $_SESSION['ppc']['emp_no'] == '8228'  || $_SESSION['ppc']['emp_no'] == '10768' || $_SESSION['ppc']['emp_no'] == '10947') { ?>
            <label for="auto-switch">AUTO INSERT TO ODOO</label>
            <label class="switch">

                <input name="auto-switch" id='auto_insert_switch' type="checkbox" class="toggle-notification">
                <span class="slider"></span>
            </label>
        <?php } ?>

    </div>

    <div class="invoice_wrapper">
        <table id="invoiceTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Invoice Name</th>
                    <th>State</th>
                    <th>Amount Total</th>
                    <th>Create Date</th>
                    <th>Bill Date</th>
                    <th>Account Name</th>
                    <th>Account Category</th>
                    <th>Vendor</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- Distribution Containers -->
    <div id="distributionSection" style="display:none;">
        <div class="btn-groups">
            <button id="backBtn" style="font-size: 14pt; margin-right: 15px;"><i class="fa fa-arrow-circle-left"></i></button>
            <button id="sbuBtn" class="active" style="margin-right: 15px;">SBU Distribution</button>
            <button id="moBtn" style="margin-right: 15px;">MO Distribution</button>
            <button id="deptBtn">Department Distribution</button>
        </div>

        <div id="sbuTableContainer">
            <table id="sbuTable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>SBU</th>
                        <!-- <th>Percentage</th> -->
                        <!-- <th>Category</th> -->
                        <!-- <th>Customer</th> -->
                        <!-- <th>Quantity Done</th> -->
                        <!-- <th>Earned Hours</th> -->
                        <th>Allocation</th>
                        <!-- <th>Total Amount</th> -->
                        <th></th>
                    </tr>
                </thead>
                <tbody id='sbuTable_tbody'></tbody>
                <tfoot>
                    <tr id="totalSaSBU">
                        <td colspan="0" style="text-align:right; font-weight:bold">Total:</td>
                        <td id="totalSBUAllocationCell" style="font-weight:bold"></td>
                        <!-- <td></td> -->
                    </tr>
                </tfoot>
            </table>

        </div>

        <div id="moTableContainer" style="display:none;">
            <table id="moTable" class="display" style="width:100%; color: #000000;">
                <thead>
                    <tr>
                        <th>MO</th>
                        <th>Device</th>
                        <th>Category</th>
                        <th>Customer</th>
                        <th>Quantity Done</th>
                        <th>Earned Hours</th>
                        <th>Allocation</th>
                    </tr>
                </thead>
                <tbody id='moTable_tbody'></tbody>
                <tfoot id="totalSaMo">
                    <tr>
                        <td colspan="6" style="text-align:right; font-weight:bold">Total:</td>
                        <td id="totalMOAllocationCell" style="font-weight:bold"></td>
                        <!-- <td></td> -->
                    </tr>
                </tfoot>
            </table>

        </div>

        <div id="deptTableContainer" style="display:none;">
            <table id="deptTable" class="display" style="width:100%; color: #000000;">
                <thead>
                    <tr>
                        <th>Account Category</th>
                        <th>Dept Name</th>
                        <th>Percentage</th>
                        <th>Allocation</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody id='deptTable_tbody'></tbody>
                <tfoot>
                    <tr id="totalSaDept">
                        <td colspan="2" style="text-align:right; font-weight:bold">Total:</td>
                        <td id="totalPercentageCell" style="font-weight:bold"></td>
                        <td id="totalAllocationCell" style="font-weight:bold"></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <!-- MO Modal -->
    <div class="modal fade" id="moModal" tabindex="-1" role="dialog" aria-labelledby="moModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="moModalLabel"></h2>
                    <button type="button" class="close" data-dismiss="modal" style="color: #000000;">X</button>
                </div>
                <div class="modal-body">
                    <table id="moModalTable" class="table table-striped table-bordered" style="width:100%; color: #000000;">
                        <thead>
                            <tr>
                                <th>MO</th>
                                <th>Device</th>
                                <th>Category</th>
                                <th>Customer</th>
                                <th>Quantity Done</th>
                                <th>Earned Hrs</th>
                                <th>Allocation</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tfoot id="totalAllocationMo">
                            <tr>
                                <td colspan="6" style="text-align:right; font-weight:bold; color: #000000;">Total:</td>
                                <td id="totalSBUMOAllocationCell" style="font-weight:bold; color: #000000;"></td>
                                <!-- <td></td> -->
                            </tr>
                        </tfoot>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const today = new Date().toISOString().split('T')[0];
            var dt = {};
            reinitializeMoTbl();

            function reinitializeMoTbl() {
                if (dt.moTable) {
                    dt.moTable.clear().draw();
                    dt.moTable.destroy();
                }

                if (dt.sbuTable) {
                    dt.sbuTable.clear().draw();
                    dt.sbuTable.destroy();
                }

                dt.moTable = $('#moTable').DataTable({
                    destroy: true,
                    pageLength: 5,
                    processing: true,
                    dom: 'Bfrtip',
                    buttons: [{
                        extend: 'csvHtml5',
                        text: 'Export',
                        className: 'btn btn-success btn-design',
                        title: `MO_Distribution_${today}`,
                        exportOptions: {
                            columns: ':visible',
                            format: {
                                body: function(data) {
                                    return typeof data === 'string' ? data.replace(/₱/g, '₱').trim() : data;
                                }
                            }
                        },
                        customize: function(csv) {
                            return '\uFEFF' + csv;
                        }
                    }],
                    columnDefs: [{
                        targets: 6,
                        render: function(data, type, row) {

                            // RAW value for sorting, filtering, type === 'sort' or 'filter'
                            if (type !== 'display') {
                                return data; // return unchanged 5-decimal raw number
                            }

                            // FORMATTED VALUE ONLY ON DISPLAY
                            return new Intl.NumberFormat('en-PH', {
                                style: 'currency',
                                currency: 'PHP',
                                minimumFractionDigits: 5,
                                maximumFractionDigits: 5
                            }).format(data);
                        }
                    }]

                });
                dt.moModalTable = $('#moModalTable').DataTable({
                    destroy: true,
                    pageLength: 5,
                    dom: 'Bfrtip',
                    buttons: [{
                        extend: 'csvHtml5',
                        text: 'Export',
                        className: 'btn btn-success btn-design',
                        title: `MO_Distribution_${today}`
                    }],
                    columnDefs: [{
                        targets: 6,
                        render: function(data, type, row) {

                            // RAW value for sorting, filtering, type === 'sort' or 'filter'
                            if (type !== 'display') {
                                return data; // return unchanged 5-decimal raw number
                            }

                            // FORMATTED VALUE ONLY ON DISPLAY
                            return new Intl.NumberFormat('en-PH', {
                                style: 'currency',
                                currency: 'PHP',
                                minimumFractionDigits: 5,
                                maximumFractionDigits: 5
                            }).format(data);
                        }
                    }]
                });

                dt.sbuTable = $('#sbuTable').DataTable({
                    destroy: true,
                    pageLength: 5,
                    processing: true,
                    lengthChange: false,
                    // dom: 'Bfrtip',
                    // buttons: [{
                    //     extend: 'csvHtml5',
                    //     text: 'Export',
                    //     className: 'btn btn-success btn-design',
                    //     title: `SBU_Distribution_${today}`
                    // }],
                    columns: [{
                            title: 'SBU'
                        },
                        {
                            title: 'Allocation'
                        },
                        {
                            title: 'Action'
                        }
                    ],
                    footerCallback: function(row, data, start, end, display) {
                        let api = this.api();

                        let total = api
                            .column(1, {
                                page: 'current'
                            })
                            .data()
                            .reduce(function(sum, value) {

                                let numeric = Number(
                                    String(value).replace(/[₱,]/g, '')
                                );

                                return sum + (isNaN(numeric) ? 0 : numeric);
                            }, 0);

                        $(api.column(1).footer()).html(
                            '₱' + total.toLocaleString('en-US', {
                                minimumFractionDigits: 5,
                                maximumFractionDigits: 5
                            })
                        );
                    },
                    columnDefs: [{
                        targets: 1,
                        render: function(data, type, row) {

                            // RAW value for sorting, filtering, type === 'sort' or 'filter'
                            if (type !== 'display') {
                                return data; // return unchanged 5-decimal raw number
                            }

                            // FORMATTED VALUE ONLY ON DISPLAY
                            return new Intl.NumberFormat('en-PH', {
                                style: 'currency',
                                currency: 'PHP'
                            }).format(data);
                        }
                    }]
                });

            }
            dt.deptTable = $('#deptTable').DataTable({
                destroy: true,
                pageLength: 5,
                lengthChange: false,
                // dom: 'Bfrtip',
                // buttons: [{
                //     extend: 'csvHtml5',
                //     text: 'Export',
                //     className: 'btn btn-success btn-design',
                //     title: `Department_Distribution_${today}`,
                //     exportOptions: {
                //         columns: ':visible',
                //         format: {
                //             body: function(data) {
                //                 return typeof data === 'string' ? data.replace(/₱/g, '₱').trim() : data;
                //             }
                //         }
                //     },
                //     customize: function(csv) {
                //         return '\uFEFF' + csv;
                //     } 
                // }],
                columnDefs: [{
                    targets: 3,
                    render: function(data, type, row) {

                        // RAW value for sorting, filtering, type === 'sort' or 'filter'
                        if (type !== 'display') {
                            return data; // return unchanged 5-decimal raw number
                        }

                        // FORMATTED VALUE ONLY ON DISPLAY
                        return new Intl.NumberFormat('en-PH', {
                            style: 'currency',
                            currency: 'PHP'
                        }).format(data);
                    }
                }]
            });

            $('#yearMonthSelect').select2({
                placeholder: "Select Month",
                allowClear: true,
                width: "235px"
            });

            $.ajax({
                url: 'ajax/fetch/fetch_date_range.php',
                type: 'GET',
                success: function(data) {

                    let yearGroups = {};

                    data.forEach(item => {
                        if (!item.is_dept_distributed) return;

                        const ym = item.year_month;
                        const [year, month] = ym.split('-');

                        if (!yearGroups[year]) yearGroups[year] = [];

                        yearGroups[year].push({
                            ...item,
                            monthNumber: parseInt(month, 10)
                        });
                    });

                    $('#yearMonthSelect').find('option:not([value=""])').remove();

                    Object.keys(yearGroups).sort().forEach(year => {
                        const group = $('<optgroup>', {
                            label: year
                        });

                        yearGroups[year].sort((a, b) => a.monthNumber - b.monthNumber);

                        yearGroups[year].forEach(item => {
                            const monthName = new Date(item.year_month + "-01")
                                .toLocaleString('default', {
                                    month: 'long'
                                });

                            group.append(`
                                <option value="${item.year_month}"
                                        data-id="${item.id}"
                                        data-start="${item.start_date}"
                                        data-end="${item.end_date}"
                                        data-dis="${item.is_dept_distributed}">
                                    ${year}-${monthName}
                                </option>
                            `);
                        });

                        $('#yearMonthSelect').append(group);
                    });

                    $('#yearMonthSelect').trigger('change.select2');
                }
            });

            var hasPlus = 0;
            const table = $('#invoiceTable').DataTable({
                ajax: {
                    url: 'ajax/fetch/journal_entries.php',
                    type: 'POST',
                    data: function(d) {
                        // d.fromDate = $('#fromDate').val();
                        // d.toDate = $('#toDate').val();
                        const selected = $('#yearMonthSelect option:selected');
                        d.fromDate = selected.data('start') || '';
                        d.toDate = selected.data('end') || '';
                    }
                },
                columns: [{
                        data: 'name'
                    },
                    {
                        data: 'state'
                    },
                    {
                        data: 'amount_total',
                        render: function(data, type, row) {
                            let value = parseFloat(data);
                            if (isNaN(value)) return '₱0.00';
                            return '₱' + value.toLocaleString('en-PH', {
                                minimumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'create_date'
                    },
                    {
                        data: 'bill_date'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let account_per_am = `
                                        <div style="display:flex; flex-direction:column; gap:5px;">
                                    `;

                            let input = row.account_name || "";
                            let am = input.split(',');
                            hasPlus = 0;

                            am.forEach(v => {
                                let cleanText = v.trim();
                                let isPlus = cleanText.endsWith("+");

                                if (isPlus) {
                                    hasPlus += 1;
                                    cleanText = cleanText.slice(0, -1);
                                }

                                account_per_am += `
                                            <span class="dblock" style="
                                                padding:0.3rem 1rem;
                                                background-color:${isPlus ? 'red' : '#ddd'};
                                                color:${isPlus ? 'white' : 'black'};
                                            ">
                                                ${cleanText}
                                            </span>
                                        `;
                            });

                            account_per_am += "</div>";
                            return account_per_am;
                        }
                    },
                    {
                        data: 'account_category',
                        render: function(data, type, row) {
                            const isNot100 = parseFloat(row.acc_categ_percentage) !== 100;

                            return `
                                    <span class="dblock" style="
                                        display:inline-block;
                                        width:100%;
                                        padding:0.3rem 0.6rem;
                                        background-color:${isNot100 ? 'darkorange' : 'transparent'};
                                        color:${isNot100 ? 'white' : 'black'};
                                    ">
                                        ${data || ''}
                                    </span>
                                `;
                        }
                    },
                    {
                        data: 'vendor'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let alert = (hasPlus > 0) ? "alert-categ" : "";
                            let disabledAttr = "";

                            if (parseFloat(row.acc_categ_percentage) !== 100 || alert) {
                                disabledAttr = "disabled style='opacity:0.6; cursor:not-allowed;'";
                            }

                            return `
                                            <button 
                                                class="infoBtn ${alert}" 
                                                data-id="${row.id}"
                                                data-acc-category="${row.account_category}"
                                                data-amount="${row.amount_total}" 
                                                data-from="${$('#filterSection').find(':selected').attr('data-start')}"
                                                data-to="${$('#filterSection').find(':selected').attr('data-end')}"
                                                ${disabledAttr}
                                            >
                                                Distribution
                                            </button>
                                        `;
                        }
                    }
                ]
            });

            // {
            //                         data: null,
            //                         render: function(data, type, row) {
            //                             let alertClass = "";

            //                             let totalPercentage = 0;
            //                             $('#deptTable tbody tr').each(function() {
            //                                 let percentText = $(this).find('td').eq(2).text(); 
            //                                 let percent = parseFloat(percentText) || 0;
            //                                 totalPercentage += percent;
            //                             });

            //                             if (totalPercentage.toFixed(2) !== '100.00') {
            //                                 alertClass = "disabled alert-categ"; 
            //                             }

            //                             return `
            //                                 <button 
            //                                     class="infoBtn ${alertClass}" 
            //                                     data-id="${row.id}"
            //                                     data-acc-category="${row.account_category}"
            //                                     data-amount="${row.amount_total}" 
            //                                     data-from="${$('#fromDate').val()}"
            //                                     data-to="${$('#toDate').val()}"
            //                                 >
            //                                     Distribution
            //                                 </button>
            //                             `;
            //                         }
            //                     }
            //                 ]
            //             }); 

            //                 function getTotalPercentage() {
            //                 let total = 0;

            //                 $('#deptTable tbody tr').each(function() {
            //                     let percentText = $(this).find('td').eq(2).text(); 
            //                     let percent = parseFloat(percentText) || 0;
            //                     total += percent;
            //                 });

            //                 return total;
            //             }

            function DecimalMoHindiRoundOff(value, decimals) {
                let num = parseFloat(value);
                if (isNaN(num)) return value;
                let factor = Math.pow(10, decimals);
                return Math.floor(num * factor) / factor;
            }

            // $('#yearMonthSelect').on('change', function() {
            //     //onchange ng yearmonth

            //     const selected = $(this).find(':selected');
            //     // console.log(selected)
            //     const isDistributed = selected.data('dis');
            //     console.log(isDistributed)


            //     table.ajax.reload(function(data) {

            //         if (data && data.data && data.data.length > 0) {

            //             if (isDistributed == 'f') {
            //                 // console.log(isDistributed)
            //                 $('#createJournal').show();
            //             } else {
            //                 $('#createJournal').hide();
            //             } 

            //             setTimeout(() => {
            //                 const hasDisabledInfoBtn = $('#invoiceTable .infoBtn:disabled').length > 0;

            //                 if (hasDisabledInfoBtn) { 
            //                     $('#createJournal')
            //                         .prop('disabled', true)
            //                         .css({
            //                             opacity: 0.6,
            //                             cursor: 'not-allowed'
            //                         });
            //                 } else {    
            //                     $('#createJournal')
            //                         .prop('disabled', false)
            //                         .css({
            //                             opacity: 1,
            //                             cursor: 'pointer'
            //                         });
            //                 } 
            //             }, 300);

            //         } else {
            //             $('#createJournal').hide();
            //         }
            //     });
            // }) 

            $('#yearMonthSelect').on('change', function() {

                const selected = $(this).find(':selected');
                const isDistributed = selected.data('dis');
                const selectedValue = selected.val();

                function getLastDayOfMonth(yearMonth) {
                    const [year, month] = yearMonth.split('-').map(Number);
                    return new Date(year, month, 0);
                }

                const lastDay = getLastDayOfMonth(selectedValue);
                const today = new Date();

                table.ajax.reload(function(data) {

                    if (data && data.data && data.data.length > 0) {

                        if (isDistributed == 'f') {
                            $('#createJournal').show();
                        } else {
                            $('#createJournal').hide();
                        }

                        setTimeout(() => {

                            const hasDisabledInfoBtn =
                                $('#invoiceTable .infoBtn:disabled').length > 0;

                            const shouldDisable = hasDisabledInfoBtn || (lastDay >= today);

                            if (shouldDisable) {
                                $('#createJournal')
                                    .prop('disabled', true)
                                    .css({
                                        opacity: 0.6,
                                        cursor: 'not-allowed'
                                    });
                            } else {
                                $('#createJournal')
                                    .prop('disabled', false)
                                    .css({
                                        opacity: 1,
                                        cursor: 'pointer'
                                    });
                            }

                        }, 300);

                    } else {
                        $('#createJournal').hide();
                    }
                });
            });

            $('#sbuTable').on('click', '.viewSbuBtn', function() {
                let sbu = $(this).data('sbu');

                if (!window.cachedMOData) return;

                $('#moModalLabel').text(`${sbu}`);

                let filteredMO = window.cachedMOData.filter(row => row.sbu === sbu);

                dt.moModalTable.clear();

                let totalAllocation = 0;

                filteredMO.forEach(mo => {
                    let formattedEarnedHrs = DecimalMoHindiRoundOff(mo.earned_hrs, 5).toLocaleString('en-US', {
                        minimumFractionDigits: 5,
                        maximumFractionDigits: 5
                    });

                    let allocation = parseFloat(mo.allocation) || 0;
                    totalAllocation += allocation;

                    let formattedAllocation = '₱' + allocation

                    dt.moModalTable.row.add([
                        mo.mo,
                        mo.device,
                        mo.category,
                        mo.customer_name,
                        mo.quantity_done,
                        // formattedEarnedHrs,
                        mo.earned_hrs,
                        mo.allocation
                        // formattedAllocation
                    ]);
                });

                dt.moModalTable.draw();

                $('#totalSBUMOAllocationCell').text(
                    '₱' + totalAllocation.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );

                $('#moModal').modal('show');
            });


            // $('#filterBtn').on('click', function() {

            //     const selected = $('#yearMonthSelect option:selected');
            //     const isDistributed = selected.data('dis');

            //     if (isDistributed) {
            //         $('#createJournal').hide();
            //     } else {
            //         $('#createJournal').show();
            //     }

            //     table.ajax.reload(function(data) {

            //         if (data && data.data && data.data.length > 0) {

            //             if (!isDistributed) {
            //                 $('#createJournal').show();
            //             }

            //             setTimeout(() => {
            //                 const hasDisabledInfoBtn = $('#invoiceTable .infoBtn:disabled').length > 0;

            //                 if (hasDisabledInfoBtn) {
            //                     $('#createJournal')
            //                         .prop('disabled', true)
            //                         .css({
            //                             opacity: 0.6,
            //                             cursor: 'not-allowed'
            //                         });
            //                 } else {
            //                     $('#createJournal')
            //                         .prop('disabled', false)
            //                         .css({
            //                             opacity: 1,
            //                             cursor: 'pointer'
            //                         });
            //                 }
            //             }, 300);

            //         } else {
            //             $('#createJournal').hide();
            //         }
            //     });
            // });

            // $('#createJournal').on('click', function() {

            //     let yearMonth = $('#yearMonthSelect').val();
            //     let month_id = $('#yearMonthSelect').find(':selected').data('id');

            //     console.log('MONTH ID', month_id);
            //     console.log('YEAR MONTH', yearMonth);

            //     swal({
            //             title: "Are you sure you want to generate?",
            //             text: "once submitted, you cannot revert this transaction",
            //             type: "warning",
            //             showCancelButton: true,
            //             confirmButtonColor: '#DD6B55',
            //             confirmButtonText: 'Yes, I am sure!',
            //             cancelButtonText: "No, cancel it!",
            //             closeOnConfirm: false,
            //             closeOnCancel: false
            //         },
            //         function(isConfirm) {

            //             if (isConfirm) {
            //                 generateJournalEntries(month_id, yearMonth);
            //                 swal.close();
            //             } else {
            //                 swal("Saving cancelled", "", "error");
            //             }

            //         });
            // });

            $('#createJournal').on('click', function() {

                let yearMonth = $('#yearMonthSelect').val();
                let month_id = $('#yearMonthSelect').find(':selected').data('id');

                console.log('MONTH ID', month_id);
                console.log('YEAR MONTH', yearMonth);

                swal({
                        title: "Are you sure you want to generate?",
                        text: "once submitted, you cannot revert this transaction",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Yes, I am sure!',
                        cancelButtonText: "No, cancel it!",
                        closeOnConfirm: false,
                        closeOnCancel: false
                    },
                    function(isConfirm) {

                        if (isConfirm) {
                            generateJournalEntries(month_id, yearMonth);
                            swal.close();
                        } else {
                            swal("Saving cancelled", "", "error");
                        }

                    });
            });


            // $('#createJournalKen').on('click', function() {

            //     swal({
            //             title: "Are you sure you want to generate?",
            //             text: "once submitted, you cannot revert this transaction",
            //             type: "warning",
            //             showCancelButton: true,
            //             confirmButtonColor: '#DD6B55',
            //             confirmButtonText: 'Yes, I am sure!',
            //             cancelButtonText: "No, cancel it!",
            //             closeOnConfirm: false,
            //             closeOnCancel: false
            //         },
            //         function(isConfirm) {

            //             if (isConfirm) {
            //                 // console.log('confirmed')
            //                 // $fromdate = $('#fromDate').val();
            //                 // $todate = $('#toDate').val();
            //                 $month_id = $('#yearMonthSelect').find(':selected').attr('data-id')
            //                 console.log($month_id)
            //                 // console.log($fromdate)
            //                 // console.log($todate)
            //                 generateJournalEntriesKen($month_id)
            //                 swal.close();
            //             } else {
            //                 swal("Saving cancelled", "", "error");
            //             }
            //         });
            //     // alert('PWEDE PERO DIPINDI');
            // });


            $('#invoiceTable').on('click', '.infoBtn', function() {
                if ($(this).hasClass('alert-categ')) {
                    alert('Please Contact Systems/MIS local 314/267 to tag a group for all account name in this Journal Entry!');
                    return;
                }

                const fromDate = $(this).data('from');
                const toDate = $(this).data('to');
                const amount = $(this).data('amount');

                const am_id = $(this).data('id');
                const acc_category = $(this).data('acc-category');
                $('#invoiceTable').closest('div').hide();
                $('#filterSection').hide();
                $('#distributionSection').show();

                $('#invoiceTable_wrapper .dataTables_length').hide();
                $('#invoiceTable_wrapper .dataTables_filter').hide();
                $('#invoiceTable_wrapper #invoiceTable_info').hide();
                $('#invoiceTable_wrapper #invoiceTable_paginate').hide();
                $('#sbuTable_length').hide();

                loadMoAndDeptTable(fromDate, toDate, amount, am_id, acc_category);
                $('#sbuBtn').addClass('active');
                $('#deptBtn').removeClass('active');
                $('#moTableContainer').hide();
                $('#deptTableContainer').hide();
                $('#sbuTableContainer').show();
            });

            function loadMoAndDeptTable(fromDate, toDate, amount, am_id, acc_category) {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: {

                        fromDate,
                        toDate,
                        amId: am_id,
                        accCategory: acc_category,
                        amountTotal: amount
                    },
                    url: 'ajax/fetch/distribution.php',
                    beforeSend: function() {
                        $('#moTable_processing, #deptTable_processing, #sbuTable_processing').show();
                        $('td.dataTables_empty, td.dataTables_empty').hide();
                        $('#totalSaMo').hide();
                        $('#totalSaDept').hide();
                        $('#totalSaSBU').hide();
                    },
                    success: function(data) {
                        $('#moTable_processing, #deptTable_processing, #sbuTable_processing').hide();
                        if (data['mo_dist']) {
                            let totalMOAllocation = 0;
                            var rows = [];
                            console.log('niceone')
                            reinitializeMoTbl();

                            $('#totalSaMo').show();
                            $('#totalSaDept').show();
                            $('#totalSaSBU').show();

                            for (var i = 0; i < data['mo_dist'].length; i++) {
                                let mo_dist = data['mo_dist'][i];
                                let earnedHrsDecimals = 5;

                                // let formattedEarnedHrs = DecimalMoHindiRoundOff(mo_dist['earned_hrs'], 5).toLocaleString('en-US', {
                                //     minimumFractionDigits: 5,
                                //     maximumFractionDigits: 5
                                // });
                                totalMOAllocation += parseFloat(mo_dist['allocation']) || 0;
                                // console.log('ORIGINAL VALUE PAG HINDI NAKA TOFIXED:', mo_dist['earned_hrs']);

                                // let formattedAllocation = '₱' + parseFloat(mo_dist['allocation']);
                                // console.log('ORIGINAL VALUE PAG HINDI NAKA TOFIXED:', mo_dist['allocation']);

                                rows.push([
                                    mo_dist['mo'],
                                    mo_dist['device'],
                                    mo_dist['category'],
                                    mo_dist['customer_name'],
                                    mo_dist['quantity_done'],
                                    mo_dist['earned_hrs'],
                                    // formattedEarnedHrs,
                                    mo_dist['allocation']
                                    // formattedAllocation
                                ]);
                                // console.log(mo_dist['allocation'])

                            }
                            dt.moTable.rows.add(rows).draw();
                            // console.log(dt.moTable.rows().data().toArray());
                            // ===============================
                            // GROUP BY SBU
                            if (data['mo_dist']) {
                                window.cachedMOData = data['mo_dist'];
                                let raw = data['mo_dist'];
                                let grouped = {};
                                let totalAllocation = 0;

                                raw.forEach(row => {
                                    let sbu = row.sbu || 'UNKNOWN';

                                    if (!grouped[sbu]) {
                                        grouped[sbu] = {
                                            sbu: sbu,
                                            allocation: 0,
                                            action: ''
                                        };
                                    }

                                    let alloc = Number(row.allocation) || 0;
                                    grouped[sbu].allocation += alloc;
                                    totalAllocation += alloc;
                                });

                                let finalSBU = Object.values(grouped);

                                dt.sbuTable.clear();

                                finalSBU.forEach(row => {
                                    let pesoAllocation = '₱' + row.allocation.toLocaleString('en-US', {
                                        minimumFractionDigits: 2
                                    });

                                    dt.sbuTable.row.add([
                                        row.sbu,
                                        row.allocation,
                                        `<button class="btn btn-primary btn-sm viewSbuBtn" data-sbu="${row.sbu}">
                                            View
                                        </button>`
                                    ]).draw(false);
                                });

                                $('#totalSBUAllocationCell').text(
                                    '₱' + totalAllocation.toLocaleString('en-US', {
                                        minimumFractionDigits: 2
                                    })
                                );
                            }

                            function DecimalMoHindiRoundOff(value, decimals) {
                                let num = parseFloat(value);
                                if (isNaN(num)) return value;
                                let factor = Math.pow(10, decimals);
                                return Math.floor(num * factor) / factor;
                            }

                            // $('#moTable_tbody').html(moTableTbody);
                            let formattedMOTotalAllocation = '₱' + totalMOAllocation.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            $('#totalMOAllocationCell').text(formattedMOTotalAllocation);

                        }

                        function formatPesoNoRound(amount) {
                            let num = parseFloat(amount);
                            if (isNaN(num)) return amount;

                            num = Math.floor(num * 100) / 100;

                            return '₱' + num.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }

                        if (data['dept_dist']) {

                            dt.deptTable.clear().draw();

                            // console.log('niceone')
                            let totalAllocation = 0;
                            let totalPercentage = 0;
                            for (var i = 0; i < data['dept_dist'].length; i++) {
                                let dept_dist = data['dept_dist'][i];

                                let formattedAmount = '₱' + parseFloat(dept_dist['total_amount']).toLocaleString('en-US', {
                                    minimumFractionDigits: 2
                                });

                                let formattedAllocation = formatPesoNoRound(dept_dist['allocation']);
                                totalAllocation += parseFloat(dept_dist['allocation']) || 0;
                                totalPercentage += parseFloat(dept_dist['percentage']) || 0;
                                // console.log(totalPercentage);
                                // let formattedAllocation = '₱' + parseFloat(dept_dist['allocation']).toLocaleString('en-US', {
                                //     minimumFractionDigits: 2,
                                //     maximumFractionDigits: 2
                                // }); 
                                // console.log('ORIGINAL VALUE PAG HINDI NAKA TOFIXED:', dept_dist['allocation']);

                                dt.deptTable.row.add([
                                    dept_dist['acc_category'],
                                    dept_dist['department'],
                                    dept_dist['percentage'],
                                    // formattedAllocation,
                                    // formattedAmount
                                    dept_dist['allocation'],
                                    dept_dist['total_amount']
                                ]).draw(false).node();

                            }

                            let formattedTotalPercentage = totalPercentage.toFixed(2) + '%';
                            let formattedTotalAllocation = '₱' + totalAllocation.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                            // console.log('TEST', formattedTotalPercentage);
                            $('#totalPercentageCell')
                                .text(formattedTotalPercentage)
                            // .css({
                            //     'color': (totalPercentage === 100 ? 'darkgreen' : 'darkred'),
                            //     'font-weight': 'bold'
                            // });

                            $('#totalAllocationCell').text(formattedTotalAllocation);

                            // $('#moTable_tbody').html(moTableTbody);

                        }

                        // spinner.stop(target);
                    } //SUCCESS
                }); //AJAX


                // $('#moTable').DataTable({
                //     destroy: true,
                //     pageLength: 25,
                //     ajax: {
                //         url: 'ajax/fetch/distribution.php',
                //         type: 'POST',
                //         data: {

                //             fromDate,
                //             toDate,
                //             amId: am_id,
                //             amountTotal: amount
                //         }
                //     },
                //     columns: [{
                //             data: 'mo'
                //         },
                //         {
                //             data: 'device'
                //         },
                //         {
                //             data: 'category'
                //         },
                //         {
                //             data: 'customer_name'
                //         },
                //         {
                //             data: 'quantity_done'
                //         },
                //         {
                //             data: 'earned_hrs'
                //         },
                //         {
                //             data: 'allocation'
                //         }
                //     ]
                // });
            }

            function loadDeptTable(amount) {
                $('#deptTable').DataTable({
                    destroy: true,
                    pageLength: 25,
                    ajax: {
                        url: 'ajax/fetch/distribution.php',
                        type: 'POST',
                        data: {
                            amountTotal: amount
                        }
                    },
                    columns: [{
                            data: 'dept_code'
                        },
                        {
                            data: 'dept_name'
                        },
                        {
                            data: 'percentage'
                        },
                        {
                            data: 'account'
                        },
                        {
                            data: 'allocation'
                        },
                        {
                            data: 'total_amount'
                        }
                    ]
                });
            }

            // function generateJournalEntries(month_id) {

            //     $.ajax({
            //         type: 'post',
            //         dataType: 'json',
            //         data: {

            //             month_id
            //         },
            //         url: 'ajax/transaction/save_distributed_journal.php',
            //         success: function(data) {

            //             window.location = "export_journal_batch_csv.php?id=" + data;


            //         } //SUCCESS
            //     }); //AJAX
            // }

            function generateJournalEntries(month_id, yearMonth) {
                // console.log($('#auto_insert_switch').prop('checked'));
                var autoInsertChecked = $('#auto_insert_switch').prop('checked');

                $.ajax({
                    type: 'POST',
                    url: 'ajax/transaction/save_distributed_journal.php',
                    dataType: 'json',
                    data: {
                        month_id: month_id
                    },

                    success: function(response) {


                        $.ajax({
                            type: 'POST',
                            url: 'ajax/transaction/send_journal_email.php',
                            dataType: 'json',
                            data: {
                                yearMonth: yearMonth,
                                month_id: month_id,
                            },

                            success: function(mailRes) {

                                if (autoInsertChecked) {
                                    $.ajax({
                                        url: "ajax/transaction/insert_journal_entries_to_odoo.php",
                                        method: "POST",
                                        dataType: "json",
                                        data: {
                                            month_id: month_id
                                        },
                                        success: function(data) {

                                            swal(
                                                "Success",
                                                "Journal Generated",
                                                "success"
                                            );
                                            // window.location =
                                            //     "generated_distribution.php?id=" + month_id + "&ym=" + yearMonth;
                                        }
                                    })
                                } else {
                                    swal(
                                        "Success",
                                        "Journal Generated",
                                        "success"
                                    );
                                    // window.location =
                                    //     "generated_distribution.php?id=" + month_id + "&ym=" + yearMonth;
                                }

                                // console.log("Mailer:", mailRes); 



                            }
                        });

                    }
                });
            }

            // function generateJournalEntriesKen(month_id) {

            //     $.ajax({
            //         type: 'post',
            //         dataType: 'json',
            //         data: {

            //             month_id
            //         },
            //         url: 'ajax/transaction/save_distributed_journal_copy.php',
            //         success: function(data) {

            //             window.location = "export_journal_batch_csv.php?id=" + data;


            //         } //SUCCESS
            //     }); //AJAX
            // }

            $('#moBtn').on('click', function() {
                const amount = $('.infoBtn').data('amount');
                const fromDate = $('.infoBtn').data('from');
                const toDate = $('.infoBtn').data('to');

                $('#moBtn').addClass('active');
                $('#deptBtn').removeClass('active');
                $('#sbuBtn').removeClass('active');
                $('#deptTableContainer').hide();
                $('#moTableContainer').show();
                $('#sbuTableContainer').hide();
                // loadMoTable(fromDate, toDate, amount);
            });

            $('#sbuBtn').on('click', function() {
                const amount = $('.infoBtn').data('amount');
                const fromDate = $('.infoBtn').data('from');
                const toDate = $('.infoBtn').data('to');

                $('#sbuBtn').addClass('active');
                $('#deptBtn').removeClass('active');
                $('#moBtn').removeClass('active');
                $('#deptTableContainer').hide();
                $('#moTableContainer').hide();
                $('#sbuTableContainer').show();

                // loadMoTable(fromDate, toDate, amount);
            });

            $('#deptBtn').on('click', function() {
                const amount = $('.infoBtn').data('amount');

                $('#deptBtn').addClass('active');
                $('#moBtn').removeClass('active');
                $('#sbuBtn').removeClass('active');
                $('#moTableContainer').hide();
                $('#deptTableContainer').show();
                $('#sbuTableContainer').hide();
            });

            $('#backBtn').on('click', function() {
                $('#distributionSection').hide();
                $('#invoiceTable').closest('div').show();
                $('#filterSection').show();
                reinitializeMoTbl();

                $('#invoiceTable_wrapper .dataTables_length').show();
                $('#invoiceTable_wrapper .dataTables_filter').show();
                $('#invoiceTable_wrapper #invoiceTable_info').show();
                $('#invoiceTable_wrapper #invoiceTable_paginate').show();
            });

        });

        // document.addEventListener("DOMContentLoaded", function() {
        //     const fromDate = document.getElementById("fromDate");
        //     const toDate = document.getElementById("toDate");

        //     const now = new Date();
        //     const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        //     const lastDay = new Date(now.getFullYear(), now.getMonth() + 1, 0);

        //     const formatDate = (date) => {
        //         const year = date.getFullYear();
        //         const month = String(date.getMonth() + 1).padStart(2, "0");
        //         const day = String(date.getDate()).padStart(2, "0");
        //         return `${year}-${month}-${day}`;
        //     };

        //     fromDate.value = formatDate(firstDay);
        //     toDate.value = formatDate(lastDay);
        // });
    </script>

</body>