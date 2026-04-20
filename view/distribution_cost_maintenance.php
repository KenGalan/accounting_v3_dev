<?php
$db = new Postgresql();
$conn = $db->getConnection();

$query_category = "
		SELECT 
		SUM(b.distribution_percentage) AS distribution_percentage,
        a.id,
        a.acc_category,
        a.added_on,
        a.added_by,
        a.changed_on,
        a.changed_by,
        a.active
    FROM M_ACC_CATEGORY_TBL a
	LEFT JOIN M_ACC_COST_DISTRIBUTION b ON b.m_acc_category_id = a.id
	GROUP BY a.id, 
		a.acc_category,
        a.added_on,
        a.added_by,
        a.changed_on,
        a.changed_by,
        a.active
    ORDER BY id ASC
";
$result_category = pg_query($conn, $query_category);

// $query_all_accounts = "
// SELECT DISTINCT
//     aa.id,
//     aa.code || ' ' || aa.name AS account_name
// FROM account_account aa
// LEFT JOIN M_ACC_COST_DISTRIBUTION mm 
//     ON mm.analytic_account_id = aa.id
// WHERE mm.analytic_account_id IS NULL
//   AND aa.m_acc_category_id IS NULL
// ORDER BY account_name ASC
// ";

$query_all_accounts = "SELECT DISTINCT
    aa.id,
    aa.code || ' ' || aa.name AS account_name
FROM account_account aa
LEFT JOIN M_ACC_COST_DISTRIBUTION mm 
    ON mm.analytic_account_id = aa.id
WHERE mm.analytic_account_id IS NULL
ORDER BY account_name ASC"; //bagong update

$result_all_accounts = pg_query($conn, $query_all_accounts);

// $query_accounts = "
// SELECT DISTINCT 
//     aa.id, 
//     aa.name AS account_name 
// FROM account_account aa
// JOIN M_ACC_CATEGORY_TBL cat ON aa.m_acc_category_id = cat.id
// JOIN M_ACC_COST_DISTRIBUTION mm ON cat.id = mm.m_acc_category_id
// WHERE cat.id = 1
// ORDER BY account_name ASC
// "; 
// $result_accounts = pg_query($conn, $query_accounts);

// $query_departments = "
//     SELECT AAA.id, 
//     CASE
//         WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
//         ELSE AAA.NAME
//     END AS dept_name,
//     CASE
//         WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
//         ELSE ''
//     END AS dept_code,
//     ADG.DEPT_GROUP,
//     AAA.CREATE_DATE AS added_on,
//     '' AS added_by,
//     '' AS changed_on,
//     '' AS changed_by,
//     AAA.active,
//     ACD.distribution_percentage
//     FROM ACCOUNT_ANALYTIC_ACCOUNT AAA
//     LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAA.M_ACC_GROUP_ID
//     JOIN M_ACC_COST_DISTRIBUTION ACD ON AAA.ID = ACD.ANALYTIC_ACCOUNT_ID
//     JOIN M_ACC_CATEGORY_TBL ACT ON ACD.M_ACC_CATEGORY_ID = ACT.ID
//     WHERE AAA.ACTIVE
//     ORDER BY AAA.id ASC
// ";
// $result_departments = pg_query($conn, $query_departments); 

// $query_all_departments = "
// 	    SELECT DISTINCT AAA.id, 
//     CASE
//         WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
//          ELSE AAA.NAME
//     END AS dept_name,
//      CASE
//          WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
//          ELSE ''
//      END AS dept_code,
//    ADG.DEPT_GROUP,
//      AAA.CREATE_DATE AS added_on,
//     '' AS added_by,
//    '' AS changed_on,
//     '' AS changed_by,
//     AAA.active
//    -- ACD.distribution_percentage
//      FROM ACCOUNT_ANALYTIC_ACCOUNT AAA
//      JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAA.M_ACC_GROUP_ID
//      JOIN M_ACC_COST_DISTRIBUTION ACD ON AAA.ID = ACD.ANALYTIC_ACCOUNT_ID
//      -- JOIN M_ACC_CATEGORY_TBL ACT ON ACD.M_ACC_CATEGORY_ID = ACT.ID
//      WHERE AAA.ACTIVE
//     ORDER BY AAA.id ASC
//  ";
// $result_all_departments = pg_query($conn, $query_all_departments); 
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
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
        border-bottom: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #7C7BAD;
        color: #ffffff;
    }

    button {
        padding: 6px 12px;
        background-color: #007BFF;
        border: none;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }

    .hidden {
        display: none;
    }

    .backBtn {
        background-color: #6c757d;
    }

    .backBtn:hover {
        background-color: #5a6268;
    }

    .select2-container {
        width: 400px !important;
    }

    #saveAccountBtn,
    #saveDeptBtn {
        margin-left: 10px;
        background-color: #28a745;
    }

    #saveAccountBtn:hover,
    #saveDeptBtn:hover {
        background-color: #218838;
    }

    .select2-container {
        width: 100% !important;
    }

    .select2-selection--multiple {
        min-height: 38px;
        border: 1px solid #ced4da !important;
        border-radius: 0.375rem !important;
    }

    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        margin-top: 5px;
        word-break: break-word;
    }

    .select2-container--default .select2-selection--multiple {
        overflow-y: auto;
        max-height: 100px;
    }

    #categoryContainer {
        max-width: 100%;
        margin: 40px auto;
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    #categoryTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #categoryTable thead {
        background: #f8f9fb;
    }

    #categoryTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
    }

    #categoryTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #categoryTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #categoryTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #categoryTable tbody tr:hover {
        background: #eef6ff;
        transform: scale(1.01);
    }

    #categoryTable td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #categoryTable td:last-child button:hover {
        background: #0056d2;
    }


    #accountTypeTable_wrapper_container {
        max-width: 100%;
        margin: 40px auto;
        background: transparent;
        padding: 20px 25px;
        border-radius: 12px;
        /* box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); */
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    #accountTypeTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #accountTypeTable thead {
        background: #f8f9fb;
    }

    #accountTypeTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
    }

    #accountTypeTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #accountTypeTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #accountTypeTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #accountTypeTable tbody tr:hover {
        background: #eef6ff;
        transform: scale(1.01);
    }

    #accountTypeTable td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #accountTypeTable td:last-child button:hover {
        background: #0056d2;
    }

    #accountTypeTableHist {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #accountTypeTableHist thead {
        background: #f8f9fb;
    }

    #accountTypeTableHist th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
    }

    #accountTypeTableHist tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #accountTypeTableHist tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #accountTypeTableHist td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #accountTypeTableHist tbody tr:hover {
        background: #eef6ff;
        transform: scale(1.01);
    }

    #accountTypeTableHist td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #accountTypeTableHist td:last-child button:hover {
        background: #0056d2;
    }


    #deptTable_wrapper_container {
        max-width: 100%;
        margin: 40px auto;
        background: transparent;
        padding: 20px 25px;
        border-radius: 12px;
        /* box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); */
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
         /* zoom: 90%; */
    }

    #deptTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #deptTable thead {
        background: #f8f9fb;
    }

    #deptTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
    }

    #deptTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #deptTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #deptTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #deptTable td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #deptTable td:last-child button:hover {
        background: #0056d2;
    }

    .viewBtn {
        background-color: #7C7BAD !important;
    }

    .edit-distribution {
        background-color: #7C7BAD !important;
    }

    .form-control[disabled],
    .form-control[readonly],
    fieldset[disabled] .form-control {
        background-color: #cccccc !important;
    }

    .inside {
        background-color: #ffffff !important;
        padding: 15px;
        border-radius: 15px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    }

    #histTaggedAcc {
        cursor: pointer;
        color: #0056d2;
    }

    #categoryTable_filter {
        margin-top: -45px;
    }
</style>

<body>
    <!-- <button class="btn-primary" id="addCategoryBtn">+ Add Category</button>
    <div id="addCategoryContainer" class="hidden" style="margin-top:15px;">
        <input type="text" id="newCategoryInput" placeholder="Enter new category name" style="padding:6px; width:250px; border:1px solid #ccc; border-radius:4px;">
        <button class="btn-success" id="saveCategoryBtn">Save</button>
        <button class="btn-secondary" id="cancelCategoryBtn">Cancel</button>
    </div> -->

    <div id="categoryContainer">
        <div class="instruct" style="margin-top: 15px; margin-bottom: 15px;">
            <span style="margin-left: 10px; font-weight: bold;">Legend :</span>
            <span style="margin-left: 10px;"><i class="fa fa-circle" style="color: darkorange;"></i> No accounts tag.</span>
            <span style="margin-left: 10px;"><i class="fa fa-circle" style="color: darkred;"></i> Incomplete % / No Dept Tag</span>
        </div>
        <table id="categoryTable" class="display">
            <thead>
                <tr>
                    <th>Account Category</th>
                    <th>Account Journal</th>
                    <th>Total Distribution %</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <div id="detailsContainer" class="hidden inside">
        <div class="secc" style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
            <button class="backBtn" id="backBtn" style="font-size: 18pt; background-color: transparent !important; color: #000000; border: none; display: flex; align-items: center; gap: 5px; cursor: pointer;">
                <i class="fa fa-arrow-circle-left"></i>
                <span style="font-size: 18pt; color: #000000; margin-left: 15px !important;">Categories /</span>
                <span id="categoryLabel" style="font-size: 18pt; color: #000000; background-color: transparent !important;"></span>
            </button>

            <div style="display: flex; flex-direction: column; align-items: flex-end;">
                <h4 id="statusRibbon" style="
                    position: relative;
                    margin: 0;
                    font-size: 15pt;
                    font-weight: bold;
                    padding: 8px 20px;
                    border-radius: 15px 0 15px 5px;
                    box-shadow: 0 3px 5px rgba(0,0,0,0.2);
                    overflow: hidden;
                    color: white;
                    background: linear-gradient(90deg, #228B22, #2E8B57);
                ">
                    <span class="ribbon-tail" style="
                        position: absolute;
                        right: -15px;
                        top: 0;
                        width: 0;
                        height: 0;
                        border-top: 20px solid transparent;
                        border-bottom: 20px solid transparent;
                        border-left: 15px solid #2E8B57;
                    "></span>
                </h4>
                <span id="statusRibbonText" style="font-size: 11pt; color: #000; display: block; margin-top: 5px;"></span>
            </div>
        </div>
        <!-- <h3></h3>  -->

        <div id="accountTypeTable_wrapper_container">
            <button id="addAccountBtn" style="background-color: #7C7BAD; margin-top: 20px;">+ Add Account</button>
            <a id="histTaggedAcc" style="margin-left: 15px;">HISTORY</a><span style="margin-left: 5px;"><i class="fa fa-question-circle" title="View history of all previous tagged accounts"></i></span> <br /><br />
            <div id="accountActionContainer" class="hidden">
                <select id="accountCategSelect" multiple>
                    <option value="" disabled hidden>Select Account Name</option>
                    <?php
                    if ($result_all_accounts && pg_num_rows($result_all_accounts) > 0) {
                        while ($acc = pg_fetch_assoc($result_all_accounts)) {
                            echo "<option value='{$acc['id']}'>{$acc['account_name']}</option>";
                        }
                    }
                    ?>
                </select>
                <button id="saveAccountBtn">Save</button>
            </div>

            <table id="accountTypeTable" class="display">
                <thead>
                    <tr>
                        <th>Account Name</th>
                        <th>Account Code</th>
                        <th>Account Journal</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <!-- <?php
                            if ($result_accounts && pg_num_rows($result_accounts) > 0) {
                                while ($acc = pg_fetch_assoc($result_accounts)) {
                                    echo "<tr><td>{$acc['account_name']}</td></tr>";
                                }
                            } else {
                                echo "<tr><td>No Account Types found.</td></tr>";
                            }
                            ?> -->
                </tbody>
            </table>

            <div id="accountHistoryContainer" class="hidden">
                <button id="backToAccounts" class="btn btn-secondary">Close</button><br><br>

                <table id="accountTypeTableHist" class="display"></table>
            </div>
        </div>
        <hr style="border: 0.2px solid #e7e7e7ff;" />
        <div id="deptTable_wrapper_container">
            <h3></h3>
            <button id="addDeptBtn" style="background-color: #7C7BAD;">+ Add Department</button>
            <!-- <a id="histTaggedDept" style="margin-left: 15px;">HISTORY</a><span style="margin-left: 5px;"><i class="fa fa-question-circle" title="View history of all previous tagged Deparment and Distribution"></i></span><br /><br /> -->
            <div id="deptActionContainer" class="hidden">
                <select id="deptSelect" multiple>
                    <option value=""></option>

                </select>
                <button id="saveDeptBtn">Add Dept</button>
            </div>

            <table id="deptTable" class="display">
                <thead>
                    <tr>
                        <th>Department Name</th>
                        <th>Dept Code</th>
                        <th>Distribution %</th>
                        <th style="max-width:10px !important;">Group</th>
                        <th>Debit To</th>
                        <th>COGS Account</th>
                        <th>Added On</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <div class="flex-dept" style="display: flex; justify-content: flex-end; align-items: center; width: 100%; margin-top: 35px; margin-bottom: -50px;;">
                <div style="text-align: right;">
                    <h1 id="totalDistribution" style="margin: 0;">0%</h1>
                    <span style="display: block; font-size: 12pt; color: #444040ff;">Total Distribution %</span>
                </div>
            </div>

            <div style="text-align: right; margin-top: 15px;">
                <button id="saveDistributionBtn" class="btn btn-success" style="background-color: #28a745; margin-top: 60px; margin-bottom: -35px">Save Distribution</button>
            </div>

        </div>
    </div>
    <!-- <hr style="border: 0.5px solid #cccccc;"> -->

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script> -->

    <script>
        $(document).ready(function() {
            let accounts_tagged_global = [];
            let all_accounts_global = [];

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: 'ajax/fetch/fetch_all_accounts.php',
                success: function(data) {
                    // console.log(data)
                    if (data) {
                        all_accounts_global = data
                    }


                    // spinner.stop(target);
                } //SUCCESS
            }); //AJAX
            inputTextNumberAndPeriodOnly('.distribution-input');
            var dt = {};
            let currentCategoryId = null;
            let currentCategoryName = '';

            dt.categoryTable = $('#categoryTable').DataTable({
                ajax: {
                    url: 'ajax/fetch/fetch_categories.php',
                    type: 'POST',
                    dataSrc: ''
                },
                columns: [{
                        data: 'acc_category'
                    },
                    {
                        data: 'journal_acc',
                        render: function(data) {
                            return data && data.trim() !== "" ? data : "-";
                        }
                    },
                    {
                        data: 'distribution_percentage',
                        render: function(data, type, row) {
                            let percentage = parseFloat(data) || 0;
                            let color = (percentage === 100) ? '#06923E' : '#8A0000';
                            return `<span style="color:${color}; font-weight:bold;">${percentage.toFixed(2)}%</span>`;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `
                                <button class="viewBtn btn btn-primary" 
                                    data-id="${row.id}" 
                                    data-category="${row.acc_category}">
                                    <i class="fa fa-arrow-circle-right"></i>
                                </button>`;
                        }
                    }
                ],
                pageLength: 5,
                lengthChange: false,
                searching: true,
                ordering: false,

                createdRow: function(row, data) {
                    let percentage = parseFloat(data.distribution_percentage) || 0;

                    const hasAccounts = data.account_count && parseInt(data.account_count) > 0;

                    if (percentage === 100 && hasAccounts) {
                        $(row).css({
                            'background-color': 'transparent',
                            'transition': 'background-color 0.2s ease'
                        });
                    } else if (hasAccounts == 0) {
                        $(row).css({
                            'background-color': '#FFDBAA',
                            'transition': 'background-color 0.2s ease'
                        });
                    } else {
                        $(row).css({
                            'background-color': '#f5dedeff',
                            'transition': 'background-color 0.2s ease'
                        });
                    }
                }
            });

            $('#addCategoryBtn').on('click', function() {
                $('#addCategoryContainer').removeClass('hidden');
                $('#newCategoryInput').focus();
            });

            $('#cancelCategoryBtn').on('click', function() {
                $('#newCategoryInput').val('');
                $('#addCategoryContainer').addClass('hidden');
            });



            dt.taggedAccTbl = $('#accountTypeTable').DataTable({
                pageLength: 5,
                lengthChange: false,
                order: [],
            });

            dt.deptDTable = $('#deptTable').DataTable({
                pageLength: 5,
                lengthChange: false,
                order: [],

            });

            $('#backBtn').on('click', function() {
                $('#detailsContainer').addClass('hidden');
                $('#categoryContainer').show();
            });

            $('#accountSelect').select2({
                placeholder: 'Select an account',
                width: 'resolve',
                allowClear: false,
                width: '100%'
            });


            $('#accountCategSelect').select2({
                placeholder: 'Select an account',
                width: 'resolve',
                allowClear: false,
                width: '100%'
            });




            $('#deptSelect').select2({
                placeholder: 'Select department',
                width: 'resolve',
                allowClear: false,
                width: '100%'
            });

            $('#addAccountBtn').on('click', () => $('#accountActionContainer').toggleClass('hidden'));
            $('#saveAccountBtn').on('click', function() {
                const accountVals = $('#accountCategSelect').val() || [];
                console.log(accountVals)
                const filteredIds = accountVals.filter(id => id && id.trim() !== "");

                if (!filteredIds.length) {
                    swal('Please select at least one account before saving.');
                    return;
                }

                if (!currentCategoryId) {
                    alert('No Category Selected.\nPlease select a category before linking accounts.');
                    return;
                }

                const selectedTexts = filteredIds.map(id =>
                    $('#accountCategSelect option[value="' + id + '"]').text()
                );

                swal({
                        title: "Save accounts?",
                        text: "Are you sure you want to save these accounts?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Yes, save it!",
                        cancelButtonText: "Cancel",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {
                        if (!isConfirm) return;

                        $.ajax({
                            url: 'ajax/transaction/save_account_category.php',
                            method: 'POST',
                            data: {
                                account_ids: filteredIds,
                                category_id: currentCategoryId
                            },
                            success: function(res) {
                                let data;
                                try {
                                    data = JSON.parse(res);
                                } catch (e) {
                                    console.error('Invalid JSON:', res);
                                    swal("Error", "Invalid response from the server.", "error");
                                    return;
                                }

                                if (data.status === 'success') {
                                    swal({
                                        title: "Saved!",
                                        text: "Accounts linked successfully.",
                                        type: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    reloadTaggedAccounts();

                                    Object.entries(selectedTexts).forEach(([name, code]) => {
                                        dt.taggedAccTbl.row.add([
                                            name,
                                            code,
                                            '',
                                            `<button class="remove-accBtn btn-sm btn-danger" style="float: right !important">
                                                <i class="fa fa-trash"></i>
                                            </button>`
                                        ]).draw(false);
                                    });

                                    filteredIds.forEach(id => {
                                        $(`#accountSelect option[value="${id}"]`).remove();
                                    });

                                    updateTotalDistribution();
                                    $('#accountSelect').val([]).trigger('change.select2');
                                    $('#wipAccountSelect').val([]).trigger('change.select2');
                                    $('#accountActionContainer').addClass('hidden');

                                } else {
                                    swal("Error", data.message || "Something went wrong.", "error");
                                }
                            },
                            error: function(err) {
                                console.error(err);
                                swal("Error", "Network error while saving accounts.", "error");
                            }
                        });
                    }
                );

            });

            $('#categoryTable').on('click', '.viewBtn', function() {
                const categoryId = $(this).data('id');

                $.ajax({
                    type: 'POST',
                    url: 'ajax/fetch/fetch_available_dept.php',
                    data: {
                        category_id: categoryId
                    },

                    dataType: 'json',
                    success: function(data) {
                        // console.log('Available departments:', data);

                        if (data.error) {
                            alert('Error: ' + data.error);
                            return;
                        }

                        $('#deptSelect').empty();
                        $('#detailsContainer').show();

                        data.forEach(dept => {
                            $('#deptSelect').append(`
                            <option value="${dept.id}+${dept.dept_name}+${dept.dept_group}+${dept.dept_code}+${dept.debit_to}+${dept.dist_id}+${dept.wip_account}">
                                ${dept.dept_name} ${dept.dept_code ? '(' + dept.dept_code + ')' : ''}
                            </option>

                                `);
                        });
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: 'ajax/fetch/fetch_available_account.php',
                    data: {
                        category_id: categoryId
                    },

                    dataType: 'json',
                    success: function(data) {
                        // console.log('Available departments:', data);

                        if (data.error) {
                            alert('Error: ' + data.error);
                            return;
                        }

                        $('#accountCategSelect').empty();
                        $('#detailsContainer').show();

                        data.forEach(acc => {
                            $('#accountCategSelect').append(`
                            <option value="${acc.id}">
                                ${acc.account_name}
                            </option>

                                `);
                        });
                    }
                });

            });

            $('#saveDistributionBtn').hide();
            $('#addDeptBtn').on('click', () => $('#deptActionContainer').toggleClass('hidden'));
            $('#saveDeptBtn').on('click', function() {
                const deptVals = $('#deptSelect').val();
                if (!deptVals?.length) return alert('Please select departments.');

                let newRows = [];

                for (const value of deptVals) {
                    const parts = value.split("+");
                    const parts_dept_id = parts[0];
                    const parts_dept_name = parts[1];
                    const parts_dept_group = parts[2] ? parts[2].trim() : '';
                    const parts_dept_code = parts[3];
                    const parts_debit_to = parts[4];
                    const parts_dist_id = parts[5];
                    const parts_wip_account = parts[6];

                    html = '';
                    html += '<select class="accountSelect js-select2">';
                    html += `<option value="">Select Account</option>`;

                    accounts_tagged_global.forEach(j => {

                        html += `<option value="${j.id}" ${parts_debit_to== j.id ? 'disabled' : ''}>${j.acc_fullname}</option>`;


                    });

                    html += `</select>`;

                    html_wip = '';
                    html_wip += '<select class="wipAccountSelect js-select2">';
                    html_wip += `<option value="">Select Account</option>`;

                    all_accounts_global.forEach(j => {

                        html_wip += `<option value="${j.id}" >${j.acc_fullname}</option>`;
                    }); 

                    html_wip += `</select>`;

                    if (parts_dept_group != 'MANUFACTURING/PRODUCT LINE') {
                        html_wip = '';
                    }

                    if (!parts_dept_group || parts_dept_group.toLowerCase() === 'null' || parts_dept_group.toLowerCase() === 'undefined') {
                        // swal(`Department "${pasrts_dept_name}" does not have a group assigned. Please assign a group first.`);
                        swal({
                            type: "warning",
                            title: "No Department Group",
                            text: `Department "${parts_dept_name}" does not have a group assigned. Please assign a group first.`,
                            confirmButtonColor: "#d33"
                        });
                        continue;
                    }
                    $('#saveDistributionBtn').show();
                    const newRowData = [
                        parts_dept_name,
                        parts_dept_code,
                        `<input type="text" class="form-control distribution-input input-enabled" style="width:100%;">`,
                        parts_dept_group,
                        html,
                        html_wip,
                        '',
                        `<button class="delete-dept btn-danger" data-id="${parts_dept_id}">REMOVE</button>`
                    ];

                    newRows.push({
                        id: parts_dept_id,
                        dist_id: parts_dist_id,
                        is_enabled: 'true',
                        data: newRowData
                    }); 
                    $(`#deptSelect option[value="${value}"]`).remove();
                } 

                if (!newRows.length) return;

                const currentData = dt.deptDTable.rows().nodes().toArray().map(row => {
                    const $row = $(row);
                    const existingId = $row.data('id');
                    const existingValue = $row.find('.distribution-input').val() || '';
                    const isDisabled = $row.find('.distribution-input').prop('disabled');

                    return {
                        id: existingId,
                        is_enabled: $row.hasClass('input-enabled'),
                        data: [
                            $row.find('td').eq(0).html(),
                            $row.find('td').eq(1).html(),
                            `<input type="text" class="form-control distribution-input ${$row.hasClass('input-enabled') ? 'input-enabled' : ''}" style="width:100%;" value="${existingValue}" ${isDisabled ? 'disabled' : ''}>`,
                            $row.find('td').eq(3).html(),
                            $row.find('td').eq(4).html(),
                            $row.find('td').eq(5).html(),
                            $row.find('td').eq(6).html(),
                            $row.find('td').eq(7).html()
                        ]
                    };
                });

                const updatedData = [
                    ...newRows,
                    ...currentData
                ];

                dt.deptDTable.rows().nodes().to$().find('.js-select2').each(function () {
                    if ($(this).hasClass('select2-hidden-accessible')) {
                        $(this).select2('destroy');
                    }
                });

                dt.deptDTable.clear();

                // updatedData.forEach((rowObj) => {
                //     const node = dt.deptDTable.row.add(rowObj.data).draw(false).node();
                //     if (rowObj.id) $(node).attr('data-id', rowObj.id);
                //     if (rowObj.is_enabled) $(node).addClass('input-enabled');
                // });
                // newRows.forEach((rowObj) => {
                //     const node = dt.deptDTable
                //         .row.add(rowObj.data)
                //         .draw(false)
                //         .node();

                //     if (rowObj.id) $(node).attr('data-id', rowObj.id);
                //     if (rowObj.is_enabled) $(node).addClass('input-enabled');
                // });

                // newRows.forEach((rowObj) => {
                //     const rowApi = dt.deptDTable.row.add(rowObj.data);
                //     rowApi.draw(false);
                //     // console.log(rowApi)

                //     const node = rowApi.node(); 
                //     // console.log(node)
                //     if (rowObj.id) $(node).attr('data-id', rowObj.id);
                //     if (rowObj.is_enabled) $(node).addClass('input-enabled'); 
                    
                //     $(node).prependTo(dt.deptDTable.table().body()); 
                //     initSelect2(node); 
                // }); 

            updatedData.forEach((rowObj) => {
                const rowApi = dt.deptDTable.row.add(rowObj.data);
                const node = rowApi.node();

                if (rowObj.id) $(node).attr('data-id', rowObj.id);
                if (rowObj.is_enabled) $(node).addClass('input-enabled');
            });

            dt.deptDTable.draw(false);
            dt.deptDTable.page('first').draw('page');

            dt.deptDTable.rows({ page: 'current' }).every(function () {
                initSelect2(this.node());
            });

                // dt.deptDTable.page('first').draw(false); 
                $('#deptSelect').val([]).trigger('change.select2');
                inputTextNumberAndPeriodOnly('.distribution-input');
            });

             function initSelect2(container) {
                $(container).find('.js-select2').each(function () {
                    if ($(this).hasClass('select2-hidden-accessible')) return;

                    $(this).select2({
                        width: '100%',
                        // dropdownParent: $('#yourModalId') 
                    });
                });
            }

            function toggleSaveBtnByButtons() {
                const hasRows = $('#deptTable .delete-dept[data-id]').length > 0;
                $('#saveDistributionBtn').toggle(hasRows);
            }

            $('#deptTable').on('click', '.delete-dept', function(e) {
                e.preventDefault();

                const $btn = $(this);
                const deptId = $btn.data('id');

                const $tr = $btn.closest('tr');

                if ($tr.length === 0) {
                    console.warn('Row not found for delete button', deptId);
                    return;
                }

                const dtRow = dt.deptDTable.row($tr);
                const rowData = dtRow.data();
                dtRow.remove().draw(false);

                let deptName = $tr.find('td').eq(0).text().trim();
                let deptCode = $tr.find('td').eq(1).text().trim();
                let deptGroup = $tr.find('td').eq(3).text().trim();

                if (Array.isArray(rowData)) {
                    deptName = rowData[0] || deptName;
                    deptCode = rowData[1] || deptCode;
                    deptGroup = rowData[3] || deptGroup;
                }

                const optionValue = `${deptId}+${deptName}+${deptGroup}+${deptCode}`;
                $('#deptSelect').append(`<option value="${optionValue}">${deptCode} - ${deptName}</option>`);
                $('#deptSelect').trigger('change');

                updateTotalDistribution();
                toggleSaveBtnByButtons();
            });

            $('#saveDistributionBtn').on('click', function() {

                if (!currentCategoryId) {
                    swal("No category selected",
                        "Please select a category before saving.",
                        "warning");
                    return;
                }

                let rows = [];
                dt.deptDTable.rows('.input-enabled').every(function() {
                    // console.log(this.data());
                    // });
                    // dt.deptDTable.rows().every(function() {

                    const rowNode = this.node();
                    const analytic_account_id = $(rowNode).data('id');
                    const val = $(rowNode).find('.distribution-input').val();
                    const valAccount = $(rowNode).find('.accountSelect').val();
                    const valWipAccount = $(rowNode).find('.wipAccountSelect').val();
                    // const inputAccount = row.find('.accountSelect');
                    const distribution_percentage = val;
                    const debit_to = Number(valAccount);
                    const wip_account = Number(valWipAccount);


                    if (analytic_account_id && val !== "" && distribution_percentage > 0) {
                        rows.push({
                            analytic_account_id: analytic_account_id,
                            distribution_percentage: distribution_percentage,
                            debit_to: debit_to,
                            wip_account: wip_account
                        });
                    }
                });

                if (!rows.length) {
                    swal("No departments to save",
                        "Please enter valid distribution percentages before saving.",
                        "warning");
                    return;
                }

                swal({
                        title: "Are you sure?",
                        text: "Do you want to save the distribution?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        confirmButtonText: "Yes, save it!",
                        cancelButtonText: "Cancel",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {
                        console.log(rows);
                        // return;
                        if (!isConfirm) return;

                        $.ajax({
                            url: 'ajax/transaction/save_distribution.php',
                            method: 'POST',
                            data: {
                                category_id: currentCategoryId,
                                data: JSON.stringify(rows)
                            },
                            success: function(res) {
                                let data;
                                try {
                                    data = JSON.parse(res);
                                } catch (e) {
                                    swal("Error", "Unexpected server response.", "error");
                                    return;
                                }

                                if (data.status === 'success') {
                                    reloadDeptTable();
                                    $('#saveDistributionBtn').hide();

                                    swal({
                                        title: "Saved!",
                                        text: data.message,
                                        type: "success"
                                    });
                                } else {
                                    swal("Error",
                                        data.message || "Something went wrong.",
                                        "error");
                                }
                            },
                            error: function(err) {
                                console.error(err);
                                swal("Error",
                                    "Something went wrong while saving.",
                                    "error");
                            }
                        });
                    }
                );
            });

            function reloadTaggedAccounts() {
                if (!currentCategoryId) return;

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: 'ajax/fetch/fetch_tagged_accounts.php',
                    data: {
                        category_id: currentCategoryId
                    },
                    success: function(data) {
                        dt.taggedAccTbl.clear().draw();


                        if (data && data.length > 0) {
                            accounts_tagged_global = data
                            for (var i = 0; i < data.length; i++) {
                                let tagged_accounts = data[i];
                                dt.taggedAccTbl.row.add([
                                    tagged_accounts['account_name'],
                                    tagged_accounts['code'],
                                    '',
                                    `<button class="remove-accBtn btn-sm btn-danger" 
                                            style="float: right !important" 
                                            data-id="${tagged_accounts['id']}">
                                            <i class="fa fa-trash"></i>
                                        </button>`
                                ]).draw(false).node();
                            }
                        }

                        updateTotalDistribution();
                    },
                    error: function(err) {
                        console.error('Error reloading tagged accounts:', err);
                    }
                });
            }

            function reloadDeptTable() {
                dt.deptDTable.clear().draw();

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        category_id: currentCategoryId
                    },
                    url: 'ajax/fetch/fetch_tagged_dept.php',
                    success: function(data) {
                        if (data && data.length > 0) {
                            for (let i = 0; i < data.length; i++) {
                                const tagged_dept = data[i];
                                if (tagged_dept['added_on']) {
                                    formattedDate = new Date(tagged_dept['added_on']).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    });
                                } else {
                                    formattedDate = '';
                                }
                                html = '';
                                html += '<select class="accountSelect js-select2" disabled>';
                                html += `<option value="">Select Account</option>`;

                                accounts_tagged_global.forEach(j => {
                                    html += `<option value="${j.id}" ${tagged_dept['debit_to'] == j.id ? 'selected' : ''}>${j.acc_fullname}</option>`;
                                });

                                html += `</select>`;


                                html_wip = '';
                                html_wip += '<select class="wipAccountSelect js-select2" disabled>';
                                html_wip += `<option value="">Select Account</option>`;

                                all_accounts_global.forEach(j => {
                                    html_wip += `<option value="${j.id}" ${tagged_dept['wip_account'] == j.id ? 'selected' : ''}>${j.acc_fullname}</option>`;
                                });

                                html_wip += `</select>`;


                                if (tagged_dept['dept_group'] != 'MANUFACTURING/PRODUCT LINE') {
                                    html_wip = '';
                                }

                                const node = dt.deptDTable.row.add([
                                    tagged_dept['dept_name'],
                                    tagged_dept['dept_code'],
                                    `<input type="number" class="form-control distribution-input" value="${tagged_dept['distribution_percentage'] || 0}" min="0" max="100" step="0.01" style="width:100%;" disabled>`,
                                    tagged_dept['dept_group'],
                                    html,
                                    html_wip,
                                    formattedDate,

                                    `<button class="edit-distribution btn btn-sm btn-primary" data-button="Edit"><i class="fa fa-pencil"></i></button>
                                    <button class="cancel-distribution btn btn-sm btn-secondary" style="display:none;"><i class="fa fa-remove"></i></button>
                                    <button class="remove-distribution btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>
                                    `
                                ]).draw(false).node();

                                // $(node).attr('data-dist-id', tagged_dept['dist-id']);
                                // $(node).attr('data-id', tagged_dept['analytic_account_id']);
                                // $(node).data('original-value', tagged_dept['distribution_percentage'] || 0);
                                // $(node).data('original-debit-to', tagged_dept['debit_to'] || 0);


                                $(node).attr('data-id', tagged_dept['analytic_account_id']);
                                $(node).attr('data-dist-id', tagged_dept['dist_id']);
                                $(node).data('original-value', tagged_dept['distribution_percentage'] || 0);
                                $(node).data('original-debit-to', tagged_dept['debit_to'] || 0);
                                $(node).data('original-wip-account', tagged_dept['wip_account'] || 0);

                            }
                            // setSelect2();
                            updateTotalDistribution();
                            toggleDistributionButton();
                        } else {
                            $('#totalDistribution').text('0.00%').css('color', 'darkred');
                            toggleDistributionButton();
                        }
                    },
                    error: function(err) {
                        console.error(err);
                        alert('Failed to reload departments.');
                    }
                });
            }

            $('#accountTypeTable').on('click', '.remove-accBtn', function() {
                const account_id = $(this).data('id');

                if (!account_id) {
                    swal("Error", "Missing account ID.", "error");
                    return;
                }

                swal({
                        title: "Remove account?",
                        text: "Are you sure you want to remove this account from its category?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        confirmButtonText: "Yes, remove it!",
                        cancelButtonText: "Cancel",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {
                        if (!isConfirm) return;

                        $.ajax({
                            url: 'ajax/transaction/remove_account_category.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                account_id: account_id,
                                category_id: currentCategoryId
                            },
                            success: function(res) {
                                if (res.status === 'success') {
                                    swal({
                                        title: "Removed!",
                                        text: "Account removed successfully.",
                                        type: "success",
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    reloadTaggedAccounts();
                                } else {
                                    swal("Error", res.message || "Something went wrong.", "error");
                                }
                            },
                            error: function(err) {
                                console.error(err);
                                swal("Error", "Request failed. Check console or PHP logs.", "error");
                            }
                        });
                    }
                );
            });

            function updateTotalDistribution() {
                let totalPercentage = 0;

                dt.deptDTable.rows().every(function() {
                    const rowNode = this.node();
                    const $input = $(rowNode).find('.distribution-input');
                    totalPercentage += parseFloat($input.val()) || 0;
                });

                const totalElem = $('#totalDistribution');
                totalElem.text(totalPercentage.toFixed(2) + '%');

                if (Math.abs(totalPercentage - 100) < 0.01) {
                    totalElem.css('color', '#06923E');
                } else {
                    totalElem.css('color', '#8A0000');
                }

                const ribbon = $('#statusRibbon');
                const ribbonTail = ribbon.find('.ribbon-tail');
                const statusText = $('#statusRibbonText');

                const hasAccounts = (dt.taggedAccTbl && dt.taggedAccTbl.data().count() > 0);

                // console.log('taggedAccTbl exists:', !!dt.taggedAccTbl);
                // console.log('taggedAccTbl rows count:', dt.taggedAccTbl?.rows().count());

                if (Math.abs(totalPercentage - 100) < 0.01 && hasAccounts) {
                    ribbon.text("SETUP COMPLETE");
                    ribbon.css({
                        background: "linear-gradient(90deg, #228B22, #2E8B57)",
                        color: "white"
                    });
                    ribbonTail.css('border-left-color', '#2E8B57');
                    statusText.text("");
                } else {
                    ribbon.text("SETUP INCOMPLETE");
                    ribbon.css({
                        background: "linear-gradient(90deg, #B22222, #DC143C)",
                        color: "white"
                    });
                    ribbonTail.css('border-left-color', '#DC143C');

                    if (!hasAccounts) {
                        statusText.text("No accounts tagged.");
                        ribbon.css({
                            background: "linear-gradient(90deg, #E57C23, #E8AA42)",
                            color: "white"
                        });
                    } else if (Math.abs(totalPercentage - 100) >= 0.01) {
                        statusText.text("Distribution is not complete: " + totalPercentage.toFixed(2) + "%");
                    } else {
                        statusText.text("");
                    }
                }

                return totalPercentage.toFixed(2);
            }

            dt.deptDTable.on('draw.dt', function() {
                updateTotalDistribution();
            });

            dt.deptDTable.on('page.dt', function() {
                updateTotalDistribution();
            });

            if (dt.taggedAccTbl) {
                dt.taggedAccTbl.on('draw.dt', function() {
                    updateTotalDistribution();
                });
            }



            $('#deptTable').on('keyup change', '.distribution-input', function() {
                const total = updateTotalDistribution();
                const input = $(this);
                const inputVal = Number(input.val().trim() === '' ? 0 : input.val().trim());
                const maxAllowed = (100 - (total - inputVal)).toFixed(2);

                if (inputVal > maxAllowed) {
                    alert(`Total distribution cannot exceed 100%. Remaining allowed: ${maxAllowed}%`);
                    input.val('');
                }

                updateTotalDistribution();
                toggleDistributionButton();
            });

            function toggleDistributionButton() {
                const totalPercentage = parseFloat($('#totalDistribution').text()) || 0;
                const distBtn = $('.infoBtn');

                if (Math.abs(totalPercentage - 100) < 0.01) {
                    distBtn.prop('disabled', false).css('opacity', 1);
                } else {
                    distBtn.prop('disabled', true).css('opacity', 0.6);
                }
            }


            updateTotalDistribution();
            $('#categoryTable').on('click', '.viewBtn', function() {
                dt.taggedAccTbl.clear().draw();
                // dt.taggedAccTbl.destroy();
                currentCategoryId = $(this).data('id');
                currentCategoryName = $(this).data('category');

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: {

                        category_id: currentCategoryId
                    },
                    url: 'ajax/fetch/fetch_tagged_accounts.php',

                    success: function(data) {
                        // console.log(data)
                        if (data) {
                            accounts_tagged_global = data
                            // console.log(data);

                            // console.log('niceone')

                            for (var i = 0; i < data.length; i++) {
                                let tagged_accounts = data[i];
                                dt.taggedAccTbl.row.add([
                                    tagged_accounts['account_name'],
                                    tagged_accounts['code'],
                                    '',
                                    `<button class="remove-accBtn btn-sm btn-danger" style="float: right !important"  data-id="${tagged_accounts['id']}"><i class="fa fa-trash"></i></button>`
                                    // tagged_accounts['device'],
                                    // tagged_accounts['category'],
                                    // tagged_accounts['customer_name'],
                                    // tagged_accounts['quantity_done'],
                                    // tagged_accounts['earned_hrs'],
                                    // tagged_accounts['allocation']
                                ]).draw(false).node();

                            }

                            $('#addCategoryBtn').hide();
                            $('#addCategoryContainer').hide();

                            // $('#moTable_tbody').html(moTableTbody);


                        }


                        // spinner.stop(target);
                    } //SUCCESS
                }); //AJAX





                $('#categoryLabel').text('' + currentCategoryName);

                $('#addCategoryBtn').hide();
                $('#categoryContainer').hide();
                $('#detailsContainer').removeClass('hidden');
            });

            $('#categoryTable').on('click', '.viewBtn', function() {
                dt.deptDTable.clear().draw();
                currentCategoryId = $(this).data('id');
                currentCategoryName = $(this).data('category');

                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: {
                        category_id: currentCategoryId
                    },
                    url: 'ajax/fetch/fetch_tagged_dept.php',
                    success: function(data) {
                        // console.log(data);
                        if (data && data.length > 0) {
                            for (let i = 0; i < data.length; i++) {
                                const tagged_dept = data[i];
                                let formattedDate = '';
                                if (tagged_dept['added_on']) {
                                    formattedDate = new Date(tagged_dept['added_on']).toLocaleDateString('en-US', {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    });
                                } else {
                                    formattedDate = '';
                                }
                                html = '';
                                html += '<select class="accountSelect js-select2" disabled>';
                                html += `<option value="">Select Account</option>`;

                                accounts_tagged_global.forEach(j => {
                                    html += `<option value="${j.id}" ${tagged_dept['debit_to'] == j.id ? 'selected' : ''}>${j.acc_fullname}</option>`;
                                });


                                html += `</select>`;

                                html_wip = '';
                                html_wip += '<select class="wipAccountSelect js-select2" disabled>';
                                html_wip += `<option value="">Select Account</option>`;
                                // console.log(all_accounts_global)
                                all_accounts_global.forEach(j => {
                                    html_wip += `<option value="${j.id}" ${tagged_dept['wip_account'] == j.id ? 'selected' : ''}>${j.acc_fullname}</option>`;
                                });

                                html_wip += `</select>`;
                                if (tagged_dept['dept_group'] != 'MANUFACTURING/PRODUCT LINE') {
                                    html_wip = '';
                                }

                                const node = dt.deptDTable.row.add([
                                    tagged_dept['dept_name'],
                                    tagged_dept['dept_code'],
                                    `<input type="text" class="form-control distribution-input" value="${tagged_dept['distribution_percentage'] || 0}" style="width:100%;" disabled>`,
                                    tagged_dept['dept_group'],
                                    html,
                                    html_wip,
                                    formattedDate,

                                    `<button class="edit-distribution btn btn-sm btn-primary" data-button="Edit"><i class="fa fa-pencil"></i></button> 
                                        <button class="cancel-distribution btn btn-sm btn-secondary" style="display:none;"><i class="fa fa-remove"></i></button>
                                        <button class="remove-distribution btn btn-sm btn-danger"><i class="fa fa-trash"></i></button>`
                                ]).draw(false).node();

                                $(node).attr('data-id', tagged_dept['analytic_account_id']);
                                $(node).attr('data-dist-id', tagged_dept['dist_id']);
                                $(node).data('original-value', tagged_dept['distribution_percentage'] || 0);
                                $(node).data('original-debit-to', tagged_dept['debit_to'] || 0);
                                $(node).data('original-wip-account', tagged_dept['wip_account'] || 0);
                            }
                            // setSelect2();
                            updateTotalDistribution();
                            toggleDistributionButton();
                        } else {
                            $('#totalDistribution').text('0.00%').css('color', 'darkred');
                            toggleDistributionButton();
                        }

                        inputTextNumberAndPeriodOnly('.distribution-input');
                    }
                });

                $('#categoryLabel').text(currentCategoryName);
                $('#addCategoryBtn').hide();
                $('#categoryContainer').hide();
                $('#detailsContainer').removeClass('hidden');
            });

            $('#deptTable').on('click', '.remove-distribution', function() {
                const row = $(this).closest('tr');
                const analytic_account_id = row.data('id');
                const dist_id = row.data('dist-id');

                if (!analytic_account_id) {
                    swal("Error", "Missing analytic account ID.", "error");
                    return;
                }

                swal({
                        title: "Are you sure?",
                        text: "Do you want to remove this distribution?",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Yes, remove it!",
                        cancelButtonText: "No, cancel",
                        closeOnConfirm: false,
                        closeOnCancel: true
                    },
                    function(isConfirm) {

                        if (!isConfirm) return;

                        $.ajax({
                            url: 'ajax/transaction/remove_distribution.php',
                            method: 'POST',
                            dataType: 'json',
                            data: {
                                analytic_account_id: analytic_account_id,
                                category_id: currentCategoryId,
                                dist_id: dist_id
                            },
                            success: function(res) {
                                if (res.status === 'success') {

                                    swal({
                                        title: "Removed!",
                                        text: "Distribution removed successfully.",
                                        type: "success"
                                    });

                                    reloadDeptTable();
                                    updateTotalDistribution();

                                } else {
                                    swal("Error", res.message || "Something went wrong.", "error");
                                }
                            },
                            error: function(err) {
                                console.error(err);
                                swal("Error", "Failed to remove distribution.", "error");
                            }
                        });
                    }
                );
            });

            $('#deptTable').on('click', '.edit-distribution', function() {
                // console.log($(this).attr('data-button'))
                const row = $(this).closest('tr');
                const input = row.find('.distribution-input');
                const inputAccount = row.find('.accountSelect');
                const inputWipAccount = row.find('.wipAccountSelect');
                const cancelBtn = row.find('.cancel-distribution');

                if ($(this).attr('data-button') === 'Edit') {
                    // console.log('tama naman')
                    row.data('original-value', input.val());
                    row.data('original-debit-to', inputAccount.val());
                    row.data('original-wip-account', inputWipAccount.val());

                    input.prop('disabled', false).focus();
                    inputAccount.prop('disabled', false);
                    inputWipAccount.prop('disabled', false);
                    $(this).attr('data-button', 'Save').removeClass('btn-primary').addClass('btn-success')
                        .find('i') // find the <i> tag inside the button
                        .attr('class', 'fa fa-save');
                    $('#saveDistributionBtn').hide();
                    // $('.remove-distribution').hide();
                    cancelBtn.show();
                } else {
                    const analytic_account_id = row.data('id');
                    const dist_id = row.data('dist-id');
                    const distribution_percentage = Number(input.val()) || 0;
                    const debit_to = Number(inputAccount.val()) || 0;
                    const wip_account = Number(inputWipAccount.val()) || 0;
                    // console.log(wip_account, ' ito yon')
                    if (distribution_percentage == 0 || debit_to == 0) {
                        swal("Warning",
                            "Please enter a valid distribution percentage.",
                            "warning");
                        return;
                    }

                    swal({
                            title: "Save changes?",
                            text: "Are you sure you want to update this distribution?",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#3085d6",
                            confirmButtonText: "Yes, save it!",
                            cancelButtonText: "Cancel",
                            closeOnConfirm: false,
                            closeOnCancel: true
                        },
                        function(isConfirm) {

                            if (!isConfirm) return;

                            $.ajax({
                                url: 'ajax/transaction/save_edited_distribution.php',
                                method: 'POST',
                                data: {
                                    category_id: currentCategoryId,
                                    dist_id: dist_id,
                                    data: JSON.stringify([{
                                        analytic_account_id: analytic_account_id,
                                        distribution_percentage: distribution_percentage,
                                        debit_to: debit_to,
                                        wip_account: wip_account
                                    }])
                                },
                                dataType: 'json',
                                success: function(res) {
                                    if (res.status === 'success') {

                                        swal({
                                            title: "Saved!",
                                            text: "Distribution saved successfully!",
                                            type: "success",
                                            timer: 1500,
                                            showConfirmButton: false
                                        });

                                        input.prop('disabled', true);
                                        cancelBtn.hide();

                                        row.find('.edit-distribution')
                                            .attr('data-button', 'Edit')
                                            .removeClass('btn-success')
                                            .addClass('btn-primary')
                                            .find('i')
                                            .attr('class', 'fa fa-edit');

                                        updateTotalDistribution();
                                        reloadDeptTable();

                                    } else {
                                        swal("Error",
                                            res.message || "Something went wrong.",
                                            "error");
                                    }
                                },
                                error: function(err) {
                                    console.error(err);

                                    swal("Error",
                                        "Something went wrong while saving.",
                                        "error");
                                }
                            });

                        }
                    );
                }
            });

            $('#deptTable').on('click', '.cancel-distribution', function() {
                const row = $(this).closest('tr');
                const input = row.find('.distribution-input');
                const inputAccount = row.find('.accountSelect');
                const inputWipAccount = row.find('.wipAccountSelect');
                const editBtn = row.find('.edit-distribution');

                const originalValue = row.data('original-value');
                const originalDebitTo = row.data('original-debit-to');
                const originalWipAccount = row.data('original-wip-account');

                input.val(originalValue).prop('disabled', true);
                inputAccount.val(originalDebitTo).prop('disabled', true);
                inputWipAccount.val(originalWipAccount).prop('disabled', true);
                // $('#saveDistributionBtn').show();
                editBtn.attr('data-button', 'Edit').removeClass('btn-success').addClass('btn-primary').find('i').attr('class', 'fa fa-pencil');
                $(this).hide();
                // $('.remove-distribution').show();

                updateTotalDistribution();
            });

            function fetch_categories() {
                if ($.fn.DataTable.isDataTable('#categoryTable')) {
                    $('#categoryTable').DataTable().ajax.reload(null, false);
                } else {
                    $('#categoryTable').DataTable({
                        ajax: {
                            url: 'ajax/fetch_categories.php',
                            dataSrc: ''
                        },
                        columns: [{
                                data: 'acc_category'
                            },
                            {
                                data: 'distribution_percentage',
                                render: function(data) {
                                    const percentage = parseFloat(data);
                                    const color = (percentage === 100) ? '#06923E' : '#8A0000';
                                    return `<span style="color: ${color}; font-weight: bold;">${percentage.toFixed(2)}%</span>`;
                                }
                            },
                            {
                                data: null,
                                render: function(data) {
                                    return `<button class="viewBtn" data-id="${data.id}" data-category="${data.acc_category}">View</button>`;
                                }
                            }
                        ],
                        pageLength: 5,
                        lengthChange: false
                    });
                }
            }

            $('#histTaggedAcc').on('click', function() {

                if (!currentCategoryId) {
                    alert("No category selected.");
                    return;
                }

                $('#accountTypeTableContainer').hide();
                $('#accountHistoryContainer').removeClass('hidden');
                $('#accountTypeTable').hide();
                $('#accountTypeTable_info').hide();
                $('#accountTypeTable_paginate').hide();
                $('#accountTypeTable_filter').hide();
                $('#accountActionContainer').hide();
                reloadTaggedAccounts();

                if ($.fn.DataTable.isDataTable('#accountTypeTableHist')) {
                    $('#accountTypeTableHist').DataTable().clear().destroy();
                }

                $('#accountTypeTableHist').DataTable({
                    pageLength: 5,
                    lengthChange: false,
                    searching: false,
                    info: false,
                    ajax: {
                        url: 'ajax/fetch/fetch_hist_account.php',
                        type: 'POST',
                        data: {
                            category_id: currentCategoryId
                        },
                        dataSrc: ''
                    },
                    columns: [{
                            data: "account_name",
                            title: "Account Name"
                        },
                        {
                            data: "code",
                            title: "Code"
                        },
                        {
                            data: "updated_by",
                            title: "Updated By"
                        },
                        {
                            data: "updated_on",
                            title: "Updated On",
                            render: function(date) {
                                return date ? new Date(date).toLocaleString('en-US') : '';
                            }
                        }
                    ]
                });

            });

            $('#backToAccounts').on('click', function() {
                $('#accountHistoryContainer').addClass('hidden');
                $('#accountTypeTableContainer').show();
                $('#accountTypeTable').show();
                $('#accountTypeTable_info').show();
                $('#accountTypeTable_paginate').show();
                $('#accountTypeTable_filter').show();
                $('#accountActionContainer').show();
            });

            $('#backBtn').on('click', function() {
                $('#detailsContainer').addClass('hidden');
                $('#categoryContainer').show();
                $('#categoryLabel').text('');
                // $('#deptActionContainer').hide();
                fetch_categories();
            });

            // Commented by Ivan - 04/16/26
            // dt.deptDTable.on('draw', function() {
            //     $('.js-select2').each(function() {
            //         if ($(this).hasClass('select2-hidden-accessible')) {
            //             $(this).select2('destroy');
            //         }

            //         $(this).select2({
            //             width: '100%',
            //             placeholder: 'Select account'
            //         });
            //     });
            // });

            // function setSelect2() {
            //     $('.wipAccountSelect').select2({
            //         width: '100%',
            //         placeholder: 'Select account'
            //     });
            //     $('.accountSelect').select2({
            //         width: '100%',
            //         placeholder: 'Select account'
            //     });
            // }

        });
    </script>
</body>