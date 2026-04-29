<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f9f9f9;
    }

    form {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
        display: none;
    }

    input[type=text],
    select {
        padding: 6px;
        margin: 4px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button {
        padding: 6px 12px;
        background-color: #4CAF50;
        border: none;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #45a049;
    }

    #addDeptBtn {
        background-color: #007BFF;
        margin-bottom: 15px;
    }

    #addDeptBtn:hover {
        background-color: #0056b3;
    }

    .editBtn {
        background-color: #7C7BAD !important;
    }

    .saveBtn {
        background-color: #28a745;
    }

    .cancelBtn {
        background-color: #dc3545 !important;
    }

    #category_wrapper {
        max-width: 100%;
        margin: 40px auto;
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
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
        background-color: #7C7BAD;
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

    #addCategoryBtn {
        background-color: #7C7BAD !important;
    }

    .saveEditBtn {
        background-color: #7C7BAD !important;
        margin-right: 5px;
    }
</style>

<body>

    <div style="margin-bottom: 15px;">
        <button id="addCategoryBtn">Add Category</button>
    </div>

    <div id="addCategoryContainer" style="display:none; margin-bottom: 15px;">
        <input type="text" id="newCategoryInput" placeholder="Enter category name" />
        <button id="saveCategoryBtn">Save</button>
        <button class="btn-danger" id="cancelCategoryBtn">Cancel</button>
    </div>

    <div class="category_wrapper">
        <table id="deptTable" class="display" style="width:100%;">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Date Added</th>
                    <th>Added By</th>
                    <!-- <th>Changed On</th>
            <th>Changed By</th> -->
                    <th>Journal</th>
                    <th>MO Distribution Reference</th>
                    <!-- <th>Active</th> -->
                    <th>Action</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            let journals = [];
            let deptTable;

            let moDistList = [];

            $.getJSON('ajax/json/mo_distribution.json', function (data) {
                moDistList = data;
                // initDeptTable();
            });

            $.ajax({
                url: "ajax/fetch/get_journals.php",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    journals = data;
                    console.log("Loaded journals:", journals);

                    initDeptTable();
                },
                error: function(err) {
                    console.error("Failed to load journals:", err);
                }
            });

            function initDeptTable() {
                deptTable = $('#deptTable').DataTable({
                    ajax: {
                        url: 'ajax/fetch/categories.php',
                        dataSrc: ''
                    },
                    columns: [{
                            data: 'acc_category'
                        },
                        {
                            data: 'added_on',
                            render: function(data) {
                                if (!data) return "";

                                let dateObj = new Date(data);
                                let formatted = dateObj.toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: '2-digit'
                                });

                                return formatted.toUpperCase();
                            }
                        },
                        {
                            data: 'added_by'
                        },
                        // { data: 'changed_on' },
                        // { data: 'changed_by' },
                        {
                            data: 'journal_id',
                            render: function(journal_id) {
                                let html = `<select class="journalSelect" disabled>`;
                                html += `<option value="">Select Journal</option>`;

                                journals.forEach(j => {
                                    html += `<option value="${j.id}" ${journal_id == j.id ? 'selected' : ''}>${j.name}</option>`;
                                });

                                html += `</select>`;
                                return html;
                            }
                        },
                        {
                            data: 'mo_pct_ref',
                            render: function(mo_pct_ref, type, row) {

                                let html = `<select class="pct_ref_select" disabled>`;
                                html += `<option value="">Select Option</option>`;

                                moDistList.forEach(item => {
                                    html += `<option value="${item.value}" ${mo_pct_ref == item.value ? 'selected' : ''}>
                                                ${item.name}
                                            </option>`;
                                });

                                html += `</select>`;
                                return html;
                            }
                        },
                        {
                            data: null,
                            render: data => `
                        <button class="editBtn" 
                            data-id="${data.id}" 
                            data-category="${data.acc_category}" 
                            data-journal_id="${data.journal_id}">
                            Edit
                        </button>`
                        }
                    ],
                    pageLength: 5,
                    lengthChange: false
                });

                enableCategoryEvents();
            }

            $('#addCategoryBtn').on('click', function() {
                $('#addCategoryContainer').show();
                $('#newCategoryInput').focus();
            });

            $('#cancelCategoryBtn').on('click', function() {
                $('#newCategoryInput').val('');
                $('#addCategoryContainer').hide();
            });

            $('#saveCategoryBtn').on('click', function() {
                const newCategory = $('#newCategoryInput').val().trim();
                if (!newCategory) return alert('Please enter a category name.');

                $.ajax({
                    url: 'ajax/transaction/save_category.php',
                    method: 'POST',
                    data: {
                        category: newCategory
                    },
                    success: function(res) {
                        const data = typeof res === 'string' ? JSON.parse(res) : res;
                        if (data.success) {
                            alert('Category added successfully!');
                            $('#newCategoryInput').val('');
                            $('#addCategoryContainer').hide();
                            deptTable.ajax.reload(null, false);
                        } else {
                            swal('Error: ' + data.message);
                        }
                    },
                    error: function(err) {
                        swal('Failed to save category.');
                        console.error(err);
                    }
                });
            });

            function enableCategoryEvents() {

                $('#deptTable tbody').on('click', '.editBtn', function() {
                    const row = $(this).closest('tr');
                    const rowData = deptTable.row(row).data();

                    if (row.find('input.editInput').length > 0) return;
                    row.find(".journalSelect").prop("disabled", false);
                    row.find(".pct_ref_select").prop("disabled", false);
                    const originalCategory = rowData.acc_category;
                    row.find('td').eq(0).html(
                        `<input type="text" class="editInput" value="${originalCategory}" style="width:90%;" />`
                    );

                    $(this).text('Save').removeClass('editBtn').addClass('saveEditBtn');

                    const actionCell = row.find('td').eq(5);
                    if (actionCell.find('.cancelEditBtn').length === 0) {
                        actionCell.append(`<button class="cancelEditBtn btn-danger"><i class="fa fa-remove"></i></button>`);
                    }
                });

                $('#deptTable tbody').on('click', '.saveEditBtn', function() {
                    const row = $(this).closest('tr');
                    const rowData = deptTable.row(row).data();

                    const id = rowData.id;
                    const newCategory = row.find('input.editInput').val().trim();
                    const journal_id = row.find('.journalSelect').val() || null;
                    const mo_pct_ref = row.find('.pct_ref_select').val() || null;

                    if (!newCategory) return alert('Category cannot be empty.');

                    $.ajax({
                        url: 'ajax/transaction/update_categories.php',
                        method: 'POST',
                        data: {
                            id: id,
                            category: newCategory,
                            journal_id: journal_id,
                            mo_pct_ref: mo_pct_ref
                        },
                        success: function(res) {
                            const data = typeof res === 'string' ? JSON.parse(res) : res;

                            if (data.success) {
                                swal('Category updated successfully!');
                                deptTable.ajax.reload(null, false);
                            } else {
                                swal('Error: ' + data.message);
                            }
                        },
                        error: function(err) {
                            swal('Failed to update category.');
                            console.error(err);
                        }
                    });
                });
                $('#deptTable tbody').on('click', '.cancelEditBtn', function() {
                    const row = $(this).closest('tr');
                    const rowData = deptTable.row(row).data();

                    row.find('td').eq(0).text(rowData.acc_category);

                    row.find(".journalSelect").val(rowData.journal_id).prop("disabled", true);
                    row.find(".pct_ref_select").val(rowData.mo_pct_ref).prop("disabled", true);

                    row.find('.saveEditBtn')
                        .text('Edit')
                        .removeClass('saveEditBtn')
                        .addClass('editBtn');

                    $(this).remove();
                });
            }

        });
    </script>

</body>

</html>