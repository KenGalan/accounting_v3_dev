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

$query_all_accounts = "
    SELECT DISTINCT id, code || ' ' || name AS account_name
    FROM account_account 
    WHERE m_acc_category_id IS NULL
    ORDER BY account_name ASC
"; 
$result_all_accounts = pg_query($conn, $query_all_accounts);

$query_accounts = "
SELECT DISTINCT 
    aa.id, 
    aa.name AS account_name 
FROM account_account aa
JOIN M_ACC_CATEGORY_TBL cat ON aa.m_acc_category_id = cat.id
JOIN M_ACC_COST_DISTRIBUTION mm ON cat.id = mm.m_acc_category_id
WHERE cat.id = 1
ORDER BY account_name ASC
"; 
$result_accounts = pg_query($conn, $query_accounts);

$query_departments = "
    SELECT AAA.id, 
    CASE
        WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
        ELSE AAA.NAME
    END AS dept_name,
    CASE
        WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
        ELSE ''
    END AS dept_code,
    ADG.DEPT_GROUP,
    AAA.CREATE_DATE AS added_on,
    '' AS added_by,
    '' AS changed_on,
    '' AS changed_by,
    AAA.active,
    ACD.distribution_percentage
    FROM ACCOUNT_ANALYTIC_ACCOUNT AAA
    LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG ON ADG.ID = AAA.M_ACC_GROUP_ID
    JOIN M_ACC_COST_DISTRIBUTION ACD ON AAA.ID = ACD.ANALYTIC_ACCOUNT_ID
    JOIN M_ACC_CATEGORY_TBL ACT ON ACD.M_ACC_CATEGORY_ID = ACT.ID
    WHERE AAA.ACTIVE
    ORDER BY AAA.id ASC
";
$result_departments = pg_query($conn, $query_departments);

$query_all_departments = "
SELECT 
    AAA.id, 
    CASE
        WHEN AAA.NAME ~ '^[0-9]' THEN regexp_replace(AAA.NAME, '^\S+\s*', '')
        ELSE AAA.NAME
    END AS dept_name,
    CASE
        WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
        ELSE ''
    END AS dept_code,
    ADG.DEPT_GROUP
FROM ACCOUNT_ANALYTIC_ACCOUNT AAA
LEFT JOIN M_ACC_DEPARTMENT_GROUPS ADG 
    ON ADG.ID = AAA.M_ACC_GROUP_ID
WHERE 
    AAA.ACTIVE
    AND (
        CASE
            WHEN AAA.NAME ~ '^[0-9]' THEN split_part(AAA.NAME, ' ', 1)
            ELSE ''
        END
    ) != ''
    AND AAA.ID NOT IN (
        SELECT ANALYTIC_ACCOUNT_ID 
        FROM M_ACC_COST_DISTRIBUTION 
        WHERE ANALYTIC_ACCOUNT_ID IS NOT NULL
    )
ORDER BY AAA.id ASC
";
$result_all_departments = pg_query($conn, $query_all_departments);
?>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f9f9f9;
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
        background-color: #0056b3;
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
</style>

<body>

<button class="btn-primary" id="addCategoryBtn">+ Add Category</button>
<div id="addCategoryContainer" class="hidden" style="margin-top:15px;">
    <input type="text" id="newCategoryInput" placeholder="Enter new category name" 
        style="padding:6px; width:250px; border:1px solid #ccc; border-radius:4px;">
    <button class="btn-success" id="saveCategoryBtn">Save</button>
    <button class="btn-secondary" id="cancelCategoryBtn">Cancel</button>
</div>

    <div id="categoryContainer">
        <table id="categoryTable" class="display">
            <thead>
                <tr>
                    <th>Account Category</th>
                    <th>Total Distribution %</th>
                    <!-- <th>Added On</th> -->
                    <!-- <th>Added By</th> -->
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_category && pg_num_rows($result_category) > 0) {
                    while ($row = pg_fetch_assoc($result_category)) {
                        $percentage = floatval($row['distribution_percentage']);
                        $color = ($percentage == 100) ? 'darkgreen' : 'darkred';

                        echo "<tr>
                            <td>{$row['acc_category']}</td>
                            <td style='color: {$color}; font-weight: bold;'>" . number_format($percentage, 2) . "%</td>
                            <td><button class='viewBtn' data-id='{$row['id']}' data-category='{$row['acc_category']}'>View</button></td>
                        </tr>";
                    }
                } else { 
                    echo "<tr><td colspan='6'>No categories found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div id="detailsContainer" class="hidden">
        <button class="backBtn" id="backBtn" style="font-size: 18pt; background-color: transparent; color: #000000;">
            <i class="fa fa-arrow-circle-left"></i>
            <span id="categoryLabel" style="font-size: 18pt; margin-left: 10px; color: #000000;"></span>
        </button>

        <h3></h3>
        <button id="addAccountBtn">+ Add Account</button><br /><br />
        <div id="accountActionContainer" class="hidden">
            <select id="accountSelect" multiple>
                <option value="">Select Account Name</option>
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
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_accounts && pg_num_rows($result_accounts) > 0) {
                    while ($acc = pg_fetch_assoc($result_accounts)) {
                        echo "<tr><td>{$acc['account_name']}</td></tr>";
                    }
                } else {
                    echo "<tr><td>No Account Types found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <h3></h3>
        <button id="addDeptBtn">+ Add Department</button><br /><br />
        <div id="deptActionContainer" class="hidden">
            <select id="deptSelect" multiple>
                <option value="">Select Department</option>
                <?php
                if ($result_all_departments && pg_num_rows($result_all_departments) > 0) {
                    while ($dept = pg_fetch_assoc($result_all_departments)) {
                        echo "<option value='{$dept['id']}+{$dept['dept_name']}+{$dept['dept_group']}+{$dept['dept_code']}'>{$dept['dept_name']}</option>";
                    }
                }
                ?>
            </select>
            <button id="saveDeptBtn">Add Dept</button>
        </div>

        <table id="deptTable" class="display">
            <thead>
                <tr>
                    <th>Department Name</th>
                    <th>Dept Code</th>
                    <th>Distribution %</th>
                    <th>Group</th>
                    <th>Added On</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result_departments && pg_num_rows($result_departments) > 0) {
                    while ($dept = pg_fetch_assoc($result_departments)) { 
                        echo "<tr data-id='{$dept['id']}'>
                            <td>{$dept['dept_name']}</td>
                            <td>{$dept['dept_code']}</td>
                            <td><input type='text' class='form-control distribution-input' style='width:100%;' value='{$dept['distribution_percentage']}' disabled></td>
                            <td>{$dept['dept_group']}</td>
                            <td>{$dept['added_on']}</td>
                            <td><button class='EditBtn btn-secondary' data-id='{$dept['id']}'>Edit</button>
                            <button class='deleteBtn btn-danger' data-id='{$dept['id']}'>Delete</button>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No Departments found.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <hr style="border: 0.5px solid #cccccc;">
        <div class="flex-dept" style="display: flex; justify-content: flex-end; align-items: center; width: 100%; margin-top: 15px;">
            <div style="text-align: right;">
                <h1 id="totalDistribution" style="margin: 0;">0%</h1>
                <span style="display: block; font-size: 12pt; color: #444040ff;">Total Distribution %</span>
            </div>
        </div>

        <div style="text-align: right; margin-top: 15px;">
            <button id="saveDistributionBtn" class="btn btn-success" style="background-color: #28a745;">Save Distribution</button>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {

            var dt = {};
            let currentCategoryId = null;
            let currentCategoryName = '';



            $('#categoryTable').DataTable({
                pageLength: 5,
                lengthChange: false
            });
            
            $('#addCategoryBtn').on('click', function() {
            $('#addCategoryContainer').removeClass('hidden');
            $('#newCategoryInput').focus();
        });

        $('#cancelCategoryBtn').on('click', function() {
            $('#newCategoryInput').val('');
            $('#addCategoryContainer').addClass('hidden');
        });

            $('#saveCategoryBtn').on('click', function() {
                const newCategory = $('#newCategoryInput').val().trim();

                if (newCategory === '') {
                    alert('Please enter a category for account.');
                    return;
                }

                $.ajax({
                    url: 'ajax/transaction/save_category.php',
                    method: 'POST',
                    data: { category: newCategory },
                    success: function(response) {
                        try {
                            const res = JSON.parse(response);
                            if (res.success) {
                                alert('New category added successfully.');
                                $('#categoryTable').DataTable().row.add([
                                    newCategory,
                                    res.added_on ?? '',
                                    res.added_by ?? '',
                                    '',
                                    '',
                                    '<button class="viewBtn" data-id="'+res.id+'" data-category="'+newCategory+'">View</button>'
                                ]).draw();

                                $('#newCategoryInput').val('');
                                $('#addCategoryContainer').addClass('hidden');
                            } else {
                                alert('Error: ' + res.message);
                            }
                        } catch (e) {
                            alert('Unexpected response: ' + response);
                        }
                    },
                    error: function() {
                        alert('Failed to save category. Please try again.');
                    }
                });
            });

            const accountDTable = $('#accountTypeTable').DataTable({
                pageLength: 5,
                lengthChange: false,
                order: [],
            });

            dt.deptDTable = $('#deptTable').DataTable({
                pageLength: 5,
                lengthChange: false,
                order: [],
                // language: {
                //     search: "Search:",
                //     lengthMenu: "Show _MENU_ records per page",
                //     info: "Showing _START_ to _END_ of _TOTAL_ departments",
                //     infoFiltered: "(filtered from _MAX_ total)"
                // }
            });

            $('#backBtn').on('click', function() {
                $('#detailsContainer').addClass('hidden');
                $('#categoryContainer').show();
            });

            $('#accountSelect, #deptSelect').select2({
                placeholder: 'Select options',
                width: 'resolve'
            });

            $('#addAccountBtn').on('click', () => $('#accountActionContainer').toggleClass('hidden'));
            $('#saveAccountBtn').on('click', function() {
                const ids = $('#accountSelect').val();
                const selectedTexts = $('#accountSelect option:selected').map(function() {
                    return $(this).text();
                }).get();

                if (!ids?.length) {
                    alert('Please select accounts.');
                    return;
                }

                if (!currentCategoryId) {
                    alert('No category selected.');
                    return;
                }

                $.ajax({
                    url: 'ajax/transaction/save_account_category.php',
                    method: 'POST',
                    data: {
                        account_ids: ids,
                        category_id: currentCategoryId
                    },
                    success: function(res) {
                        let data;
                        try {
                            data = JSON.parse(res);
                        } catch (e) {
                            console.error('Invalid JSON:', res);
                            alert('Unexpected response from server.');
                            return;
                        }

                        if (data.status === 'success') {
                            alert('Accounts linked successfully!');

                            selectedTexts.forEach(name => {
                                accountDTable.row.add([
                                    name
                                ]).draw(false);
                            });

                            $('#accountSelect').val('').trigger('change');
                            $('#accountActionContainer').addClass('hidden');
                        } else {
                            alert('Error: ' + (data.message || 'Something went wrong.'));
                        }
                    },
                    error: function(err) {
                        console.error(err);
                        alert('Something went wrong while saving accounts.');
                    }
                });
            });

            $('#addDeptBtn').on('click', () => $('#deptActionContainer').toggleClass('hidden'));
            $('#saveDeptBtn').on('click', function() {
                const deptVals = $('#deptSelect').val();
                if (!deptVals?.length) return alert('Please select departments.');

                $.each(deptVals, function(index, value) {
                    var parts = value.split("+");
                    var parts_dept_id = parts[0];
                    var parts_dept_name = parts[1];
                    var parts_dept_group = parts[2];
                    var parts_dept_code = parts[3];

                    const newRow = dt.deptDTable.row.add([
                        parts_dept_name,
                        parts_dept_code,
                        `<input type="text" class="form-control distribution-input" style="width:100%;">`,
                        parts_dept_group,
                        '',
                        `<button class="delete-dept btn-danger" data-id="${parts_dept_id}">Remove</button>`
                    ]).draw(false).node();

                    $(newRow).attr('data-id', parts_dept_id);

                    $(`#deptSelect option[value='${value}']`).remove();
                });

                $('#deptSelect').val(null).trigger('change');

                $('#deptTable').off('click', '.delete-dept').on('click', '.delete-dept', function() {
                    var deptId = $(this).data('id');

                    var row = $('#deptTable').find('tr').filter(function() {
                        return $(this).find('.delete-dept').data('id') == deptId;
                    });

                    if (row.length > 0) { 
                        var dataTableRow = dt.deptDTable.row(row);
                        dataTableRow.remove().draw();

                        var deptName = row.find('td').eq(0).text();
                        var deptCode = row.find('td').eq(1).text();
                        var deptGroup = row.find('td').eq(3).text();
                        var optionValue = `${deptId}+${deptName}+${deptGroup}+${deptCode}`;

                        $('#deptSelect').append(
                            `<option value="${optionValue}">${deptCode} - ${deptName}</option>`
                        );

                        $('#deptSelect').trigger('change');

                        updateTotalDistribution();
                    } else {
                        console.log('Row not found for deptId:', deptId);
                    }
                });
            });

            $('#saveDistributionBtn').on('click', function() {
                if (!currentCategoryId) {
                    alert('No category selected.');
                    return;
                }

                let rows = [];

                $('#deptTable tbody tr').each(function() {
                    const analytic_account_id = $(this).data('id');
                    const distribution_percentage = Number($(this).find('.distribution-input').val()) || 0;

                    if (analytic_account_id) {
                        rows.push({
                            analytic_account_id: analytic_account_id,
                            distribution_percentage: distribution_percentage
                        });
                    }
                });

                if (!rows.length) {
                    alert('No departments to save.');
                    return;
                }

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
                            alert('Unexpected server response');
                            return;
                        }

                        if (data.status === 'success') {
                            alert('Distribution saved successfully!');

                            updateTotalDistribution();
                        } else {
                            alert('Error: ' + (data.message || 'Something went wrong.'));
                        }
                    },
                    error: function(err) {
                        console.error(err);
                        alert('Something went wrong while saving.');
                    }
                });
            });

            $('#deptTable').on('click', '.EditBtn', function() {
                const row = $(this).closest('tr');
                const input = row.find('.distribution-input');
                const deleteBtn = row.find('.deleteBtn');

                row.data('original-value', input.val());

                input.prop('disabled', false).focus();

                $(this)
                    .text('Save')
                    .removeClass('btn-secondary')
                    .addClass('btn-success');

                deleteBtn
                    .text('Cancel')
                    .removeClass('btn-danger')
                    .addClass('btn-warning');
            });

            // $('#deptTable').on('click', '.deleteBtn', function() {
            //     const row = $(this).closest('tr');
            //     const editBtn = row.find('.EditBtn');
            //     const input = row.find('.distribution-input');

            //     if ($(this).text() === 'Cancel') {
            //         const originalValue = row.data('original-value');
            //         input.val(originalValue);

            //         input.prop('disabled', true);

            //         editBtn
            //             .text('Edit')
            //             .removeClass('btn-success')
            //             .addClass('btn-secondary');

            //         $(this)
            //             .text('Delete')
            //             .removeClass('btn-warning')
            //             .addClass('btn-danger');

            //         row.removeData('original-value');
            //     } else {
            //         const id = $(this).data('id');
            //         console.log('Deleting row with id:', id);
            //     }
            // });

$('#deptTable').on('click', '.EditBtn', function() {
    const row = $(this).closest('tr');
    const input = row.find('.distribution-input');
    const deleteBtn = row.find('.deleteBtn');

    if ($(this).text() === 'Save') {
        input.prop('disabled', true);
        $(this)
            .text('Edit')
            .removeClass('btn-success')
            .addClass('btn-secondary');
        deleteBtn
            .text('Delete')
            .removeClass('btn-warning')
            .addClass('btn-danger');
        updateTotalDistribution();
        row.removeData('original-value');
    }
});


            function updateTotalDistribution() {
                let totalPercentage = 0;
                // let total = 0;
                dt.deptDTable.rows({
                    search: "applied"
                }).every(function() {
                    let $input = $(this.node()).find('.distribution-input');
                    totalPercentage += parseFloat($input.val()) || 0;
                });

                // return total;



                // $('#deptTable tbody tr').each(function() {
                //     const val = $(this).find('.distribution-input').val().trim();
                //     if (val !== '') {
                //         totalPercentage += parseFloat(val) || 0;
                //     }
                // });

                const totalElem = $('#totalDistribution');
                totalElem.text(totalPercentage.toFixed(2) + '%');

                if (Math.abs(totalPercentage - 100) < 0.01) {
                    totalElem.css('color', 'darkgreen');
                } else {
                    totalElem.css('color', 'darkred');
                }

                return totalPercentage.toFixed(2);
            }

            $('#deptTable').on('keyup change', '.distribution-input', function() {
                const total = updateTotalDistribution();
                const input = $(this);

                const inputVal = Number(input.val().trim() === '' ? 0 : input.val().trim());


                let otherTotal = 0;
                // $('#deptTable tbody tr').not(input.closest('tr')).each(function() {
                //     const val = $(this).find('.distribution-input').val().trim();
                //     if (val !== '') {
                //         otherTotal += parseFloat(val) || 0;
                //     }
                // });
                console.log('total before: ', total);
                console.log('val : ', inputVal);
                const maxAllowed = (100 - (total - inputVal)).toFixed(2);
                console.log('maxAllowed : ', maxAllowed);
                if (inputVal > maxAllowed) {
                    alert(`Total distribution cannot exceed 100%. Remaining allowed: ${maxAllowed}%`);
                    input.val('');
                }

                updateTotalDistribution();
            });

            updateTotalDistribution();
        });

        $('#categoryTable').on('click', '.viewBtn', function() {
            currentCategoryId = $(this).data('id'); 
            currentCategoryName = $(this).data('category');

            $('#categoryLabel').text('' + currentCategoryName);

            $('#addCategoryBtn').hide();
            $('#categoryContainer').hide();
            $('#detailsContainer').removeClass('hidden');
        }); 

        $('#backBtn').on('click', function() {
            $('#detailsContainer').addClass('hidden');
            $('#categoryContainer').show();
            $('#addCategoryBtn').show();
            $('#categoryLabel').text('');
        });
    </script>
</body>