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
        Add Account
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

<table id="accountTable" class="table table-bordered table-striped w-100 !important">
    <thead>
        <tr>
            <th>Account</th>
            <th>Date Added</th>
            <th>Added By</th>
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
                url: 'ajax/fetch/fetch_customized_acc.php',
                dataSrc: 'data'
            },
            columns: [{
                    data: 'account'
                },
                {
                    data: 'date_added'
                },
                {
                    data: 'added_by'
                },
                // {
                //     data: 'changed_by'
                // },
                // {
                //     data: 'changed_on'
                // },
                {
                    data: 'id',
                    render: function(data) {
                        return `<button type="button" class="btn btn-sm btn-danger deleteRowBtn" data-id="${data}">Delete</button>`;
                    }
                }
            ]
        });

        $('#toggleInputBtn').on('click', function() {
            $('#accountInputContainer').slideToggle();
        });

        $('#accountSelect').select2({
            placeholder: "Select Account(s)",
            allowClear: true,
            width: '100%',
            ajax: {
                url: 'ajax/fetch/fetch_account_account.php',
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
                alert('Please select at least one account.');
                return;
            }

            $.ajax({
                url: 'ajax/transaction/save_customized_dist_account.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    account_ids: accountIds
                },
                success: function(response) {
                    if (response.status === 'success') {

                        selectedData.forEach((item, index) => {

                            let saved = response.data[index];

                            accountTable.row.add({
                                id: saved.id,
                                account: item.text,
                                date_added: saved.date_added,
                                added_by: saved.added_by,
                                // changed_by: '',
                                // changed_on: ''
                            }).draw(false);
                        });

                        $('#accountSelect').val(null).trigger('change');
                        $('#accountInputContainer').slideUp();

                    } else {
                        alert(response.message);
                    }
                },
                error: function() {
                    alert('Error saving accounts.');
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
                        url: 'ajax/transaction/delete_customized_dist_account.php',
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