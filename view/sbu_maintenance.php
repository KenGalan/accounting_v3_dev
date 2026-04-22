<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #ffffff;
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
</style>

<div class="mb-3">
    <button type="button" id="toggleInputBtn" class="btn btn-primary" style="margin-bottom: 15px;">
        Add SBU
    </button>
</div>

<div id="accountInputContainer" style="display:none; margin-bottom:15px;">
    <div class="row">
        <div class="col-md-4">
            <select id="accountSelect" class="form-control" multiple style="width:100%;">
            </select>
        </div>
        <div class="col-md-2">
            <button type="button" id="addAccountRowBtn" class="btn btn-success">
                Save
            </button>
        </div>
    </div>
</div>

<table id="accountTable" class="table table-bordered table-striped w-100">
    <thead>
        <tr>
            <th>SBU</th>
            <th>Date Added</th>
            <th>Added By</th>
            <th>Analytic Account</th>
            <!-- <th>Changed By</th>
            <th>Changed On</th> -->
            <th>Action</th>
        </tr>
    </thead>
    <tbody></tbody>
</table>

<script>
    $(document).ready(function() {

        let accountTable = $('#accountTable').DataTable({
            ajax: {
                url: 'ajax/fetch/fetch_sbu_maint.php',
                dataSrc: 'data'
            },
            columns: [
                { data: 'sbu' },
                { data: 'date_added' },
                { data: 'added_by' },
                {
                    data: null,
                    render: function(data, type, row, meta) {
                        let selectedId = row.analytic_account_id || '';
                        let selectedText = row.analytic_account || 'Select Analytic Account';

                        return `
                            <select class="form-control analyticAccountSelect" style="width:100%;">
                                <option value="${selectedId}" selected>${selectedText}</option>
                            </select>
                        `;
                    }
                },
                {
                    data: 'id',
                    render: function(data, type, row, meta) {
                        return `
                            <button type="button" class="btn btn-sm btn-danger deleteRowBtn" data-id="${data}">
                                Delete
                            </button>
                            <button type="button" class="btn btn-sm btn-primary updateRowBtn" data-id="${data}" disabled>
                                Update
                            </button>
                        `;
                    }
                }
            ]
        });

        $('#accountTable').on('draw.dt', function () {
            $('.analyticAccountSelect').select2({
                width: '100%',
                placeholder: 'Select Analytic Account',
                ajax: {
                    url: 'ajax/fetch/fetch_analytic_acc_sbu.php',
                    type: 'GET',
                    dataType: 'json',
                    delay: 250,
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    },
                    cache: true
                }
            });
        });
        
        $('#accountTable tbody').on('change', '.analyticAccountSelect', function () {
            let tr = $(this).closest('tr');
            tr.find('.updateRowBtn').prop('disabled', false);
        });

       $('#accountTable tbody').on('click', '.updateRowBtn', function () {
            let tr = $(this).closest('tr');
            let row = accountTable.row(tr);
            let rowData = row.data();

            let analyticAccountId = tr.find('.analyticAccountSelect').val();
            let analyticAccountText = tr.find('.analyticAccountSelect option:selected').text();
            // console.log(analyticAccountId, analyticAccountText, 'selected values');

            rowData.analytic_account_id = analyticAccountId;
            rowData.analytic_account = analyticAccountText;
            // console.log(rowData, 'dwqdqwdd');

            $.ajax({
                url: 'ajax/transaction/update_analytic_account.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    id: rowData.id,
                    analytic_account_id: analyticAccountId
                },
                success: function(res) {
                    if (res.status === 'success') {
                        row.data(rowData).invalidate().draw(false);
                        tr.find('.updateRowBtn').prop('disabled', true);
                        swal("Success", "Updated successfully", "success");
                    } else {
                        swal("Error", res.message || "Update failed", "error");
                    }
                },
                error: function() {
                    swal("Error", "Update failed", "error");
                }
            });
        });

        $('#toggleInputBtn').on('click', function() {
            $('#accountInputContainer').slideToggle();
        });

        $('#accountSelect').select2({
            placeholder: "Select SBU(s)",
            allowClear: true,
            width: '100%',
            ajax: {
                url: 'ajax/fetch/fetch_sbu.php',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term || ''
                    };
                },
                processResults: function(data) {
                    return {
                        results: data
                    };
                },
                cache: true
            }
        });

        $('#addAccountRowBtn').on('click', function() {
            let accountIds = $('#accountSelect').val();
            let selectedData = $('#accountSelect').select2('data');

            if (!accountIds || accountIds.length === 0) {
                swal("Warning", "Please select at least one SBU.", "warning");
                return;
            }

            $.ajax({
                url: 'ajax/transaction/save_sbu.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    account_ids: accountIds
                },
                success: function(response) {
                    if (response.status === 'success') {

                        response.data.forEach(function(saved) {
                            accountTable.row.add({
                                id: saved.id,
                                sbu: saved.sbu,
                                date_added: saved.date_added,
                                added_by: saved.added_by
                            }).draw(false);
                        });

                        $('#accountSelect').val(null).trigger('change');
                        $('#accountInputContainer').slideUp();

                        swal("Success", "SBU saved successfully.", "success");

                    } else {
                        swal("Error", response.message, "error");
                    }
                },
                error: function() {
                    swal("Error", "Error saving SBU.", "error");
                }
            });
        });

        $('#accountTable tbody').on('click', '.deleteRowBtn', function() {
            let btn = $(this);
            let id = btn.data('id');

            swal({
                title: "Are you sure?",
                text: "This account will be removed.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it",
                cancelButtonText: "Cancel",
                closeOnConfirm: false
            }, function(isConfirm) {

                if (isConfirm) {

                    $.ajax({
                        url: 'ajax/transaction/delete_sbu_maint.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: id
                        },
                        success: function(response) {
                            if (response.status === 'success') {

                                accountTable
                                    .row(btn.closest('tr'))
                                    .remove()
                                    .draw(false);

                                swal("Deleted!", "Account has been removed.", "success");

                            } else {
                                swal("Error", response.message, "error");
                            }
                        },
                        error: function() {
                            swal("Error", "Error deleting account.", "error");
                        }
                    });

                }

            });
        });
    });
</script>