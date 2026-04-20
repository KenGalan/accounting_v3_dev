<?php
$selectedYM = isset($_GET['ym']) ? $_GET['ym'] : '';
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

    #distTable_wrapper {
        max-width: 100%;
        margin: 40px auto;
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    #distTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #distTable thead {
        background: #f8f9fb;
    }

    #distTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
    }

    #distTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #distTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #distTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #distTable td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #distTable td:last-child button:hover {
        background: #0056d2;
    }


    .flex-cell {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    #addAccrualTbl_length {
        display: none !important;
    }

    .dist-error {
        border: 2px solid #dc3545 !important;
        background-color: #fff5f5;
        font-size: 10pt;
    }

    .input-error {
        border: 2px solid #dc3545 !important;
        background-color: #fff5f5;
    }
    .dataTables_length {
        display: none !important;
    }
            input[type=date] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- <label for="select">SELECT MONTH YEAR</label> -->
<div class="distTable_wrapper">


    <input type="month" name="month_year" id="yearMonthSelect" class="form-control" style="width:250px;">


    <div id="massDateRangeSection" style="display:none; float:right; margin-bottom:15px;">
        <label>From:</label>
        <input type="date" id="massFromDate">
        <label>To:</label>
        <input type="date" id="massToDate">
        <button id="filterBtnMass" class="btn btn-success">Apply</button>
        <div>
        <p style="color: darkred; margin-top: 5px; font-weight: 550;">Note: The selected date range will be applied to all selected rows.</p>
    </div>
    </div>

    <table id="distTable" class="table table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th></th>
                <th>Journal Entry ID</th>
                <th>Journal Entry Name</th>
                <th>Reference</th>
                <th>Total Amount</th>
                <th>Accounting Date</th>
                <th>Status</th>
                <th>From - to</th>
                <th>Action</th>

            </tr>
        </thead>
        <tbody>


        </tbody>
    </table>
</div>

<div class="modal fade" id="myModalOdooEntries" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-right: 8px; margin-top: 5px; background-color: transparent !important; color:#000000 !important;">
                <span aria-hidden="true" style="color:#000000 !important;">&times;</span>
            </button>
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel" style="letter-spacing: 1px;">

                    JOURNAL ITEMS <br />
                    <span id="journalEntryNameDisplay" style="font-size: 11pt; color: #727070; letter-spacing: 1px;"></span>
                </h5>
                <!-- <h5>Edit the date range</h5> -->
            <div id="filterSection"  style="text-align:right; margin-left:auto;">
                <label>From:</label>
                <input type="date" id="fromDate" disabled>
                <label>To:</label>
                <input type="date" id="toDate" disabled>
                <button id="filterBtn" class="btn btn-success" style="display:none;">Apply</button>
                <button id="editDateRange" class="btn-primary">Edit</button>
            </div>
        </div>
            <div class="modal-body">

                <div id="distributionSection">
                    <!-- <div class="btn-groups">
                    
                        <button id="deBtn" class="active" style="margin-right: 15px;">
                            Distribution Entries
                        </button>

                        <button id="ceBtn" style="margin-right: 15px;">
                            COGS Entries
                        </button>
                        <button id="rdeBtn" style="margin-right: 15px;">
                            Reverse Distribution Entries
                        </button>

                        <button id="crBtn" style="margin-right: 15px;">
                            COGS Reverse
                        </button>
                    </div> -->
                    <div style="padding-top:40px;">
                        <div id="distributionEntriesContainer" style="display:none;">
                         <button class="selectSbu btn btn-primary" style="display: none;">SELECT SBU</button>

                        <div id="sbuContainer" style="display:none; margin-top:10px;">
                            <div style="display:flex; gap:10px; align-items:flex-start;">
                                <select id="sbuSelect" class="form-control" multiple style="width:300px;">
                                    <!-- <option value="">Select SBU</option> -->
                                </select>

                                <button type="button" id="applySbuBtn" class="btn btn-success" style="display:none;">
                                    Add
                                </button>
                            </div>
                        </div>
                          <table id="distributionEntriesTbl" class="table table-bordered table-striped" style="width:100%; color: #000000;">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Item Label</th>
                                    <th>Account</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Analytic Account</th>
                                    <th>Date</th>
                                    <th>SBU</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3" style="text-align:right; font-weight:bold;">Total:</td>
                                    <td id="distributionTotalDebit" style="font-weight:bold;"></td>
                                    <td id="distributionTotalCredit" style="font-weight:bold;"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                </tfoot>
                            </table>
                            <div class="btness"  style="display: none;">
                                 <button class="btn btn-success" id="saveCustom">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal -->

<script>
    $(document).ready(function() {

        let distTable;
        let PreviewDistTable;
        let addAccrualTbl;
        let accounts_tagged_global = [];
        let dist_template_global = [];
        let accrualTableInitialized = false;
        // let hasSbu = false;
        var dt = {};
        reinitializeMoTbl();

        function reinitializeMoTbl() { 
            // if (dt.moTable) {
            //     dt.moTable.clear().draw();
            //     dt.moTable.destroy();
            // }

            // if (dt.sbuTable) {
            //     dt.sbuTable.clear().draw();
            //     dt.sbuTable.destroy();
            // }

            // if (dt.wipTable) {
            //     dt.wipTable.clear().draw();
            //     dt.wipTable.destroy();
            // } 

            if (dt.distributionEntriesTbl) {
                dt.distributionEntriesTbl.clear().draw();
                dt.distributionEntriesTbl.destroy();
            } 

            dt.distributionEntriesTbl = $('#distributionEntriesTbl').DataTable({
                destroy: true,
                pageLength: 5,
                processing: true,
                searching: true,
                columns: [
                    {
                        render: function(data, type, row) {
                            let checkboxId = `perRow_${row.aml_id}`;
                            if (row.debit > 0) {
                                return `
                                    <input type="checkbox" id="${checkboxId}" class="rowCheckbox" value="${row.aml_id}">
                                    <label for="${checkboxId}"></label>
                                `;
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        data: 'item_label',
                        defaultContent: ''
                    },
                    {
                        data: 'account_name',
                        defaultContent: ''
                    },
                    {
                        data: 'debit',
                        defaultContent: '',
                        render: function(data, type, row) {
                            if (!data || isNaN(data)) return '';
                            if (type !== 'display') return data;

                            return '₱ ' + Number(data).toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'credit',
                        defaultContent: '',
                        render: function(data, type, row) {
                            if (!data || isNaN(data)) return '';
                            if (type !== 'display') return data;

                            return '₱ ' + Number(data).toLocaleString('en-PH', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        }
                    },
                    {
                        data: 'account_analytic',
                        defaultContent: ''
                    },
                    {
                        data: 'date',
                        defaultContent: '',
                        render: function(data, type, row) {
                            if (!data) return '';
                            if (type !== 'display') return data;

                            let d = new Date(data);
                            return new Intl.DateTimeFormat('en-PH', {
                                year: 'numeric',
                                month: 'short',
                                day: '2-digit'
                            }).format(d);
                        }
                    },
                    {
                        data: 'sbu',
                        defaultContent: '',
                        render: function(data, type, row) {
                            if (!data || data.length === 0) return '';

                            let html = '';

                            data.forEach(sbu => {
                                html += `
                                    <span class="sbu-badge" style="
                                        display:inline-block;
                                        background:#7C7BAD;
                                        color:#fff;
                                        padding:3px 8px;
                                        border-radius:12px;
                                        margin-right:5px;
                                        margin-top: 2px;
                                        font-size: 12px;
                                    ">
                                        ${sbu}
                                        <span class="removeSbu" data-sbu="${sbu}" style="
                                            margin-left:6px;
                                            cursor:pointer;
                                            font-weight:bold;
                                        ">✕</span>
                                    </span>
                                `;
                            });

                            return html;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            if (row.has_mo_link) {
                                return `<button class="btn btn-sm btn-primary viewMosBtn" style="background-color: #7C7BAD !important;">View</button>`;
                            }
                            return '';
                        }
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    let api = this.api();

                    let totalDebit = 0;
                    let totalCredit = 0;

                    api.rows({ search: 'applied' }).every(function() {
                        let rowData = this.data();
                        totalDebit += parseFloat(rowData.debit) || 0;
                        totalCredit += parseFloat(rowData.credit) || 0;
                    });

                    $('#distributionTotalDebit').html(
                        '₱ ' + totalDebit.toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })
                    );

                    $('#distributionTotalCredit').html(
                        '₱ ' + totalCredit.toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        })
                    );
                }
            });

            $('#distributionEntriesTbl').on('change', '.rowCheckbox', function () {
            let anyChecked = $('.rowCheckbox:checked').length > 0;

            if (anyChecked) {
                $('.selectSbu').show();
            } else {
                $('.selectSbu').hide();
                $('#sbuContainer').hide();
            }

        });

        $('.selectSbu').on('click', function () {
            $('#sbuContainer').slideDown();

            setTimeout(() => {
                $('#sbuSelect').select2('open');
            }, 200);
        });

        }

            $('#sbuSelect').select2({
                placeholder: "Select SBU",
                allowClear: true,
                multiple: true,
                width: '100%',
                ajax: {
                    url: 'ajax/fetch/get_sbu.php',
                    dataType: 'json',
                    processResults: function(data) {
                        return {
                            results: data
                        };
                    }
                }
            });

            $('.selectSbu').on('click', function () {
                $('#sbuContainer').slideToggle();
            });

            $('#sbuSelect').on('change', function () {
                let selected = $(this).val();

                if (selected && selected.length > 0) {
                    $('#applySbuBtn').show();
                } else {
                    $('#applySbuBtn').hide();
                }
            });

            $('#applySbuBtn').on('click', function () {
                let selectedSbus = $('#sbuSelect').select2('data');

                if (!selectedSbus.length) {
                    swal("Warning", "Please select at least one SBU.", "warning");
                    return;
                }

                let checkedBoxes = dt.distributionEntriesTbl.$('.rowCheckbox:checked');

                if (checkedBoxes.length === 0) {
                    swal("Warning", "Please select at least one row.", "warning");
                    return;
                }

                checkedBoxes.each(function () {
                    let tr = $(this).closest('tr');
                    let row = dt.distributionEntriesTbl.row(tr);
                    let rowData = row.data();

                    if (!rowData.sbu || !Array.isArray(rowData.sbu)) {
                        rowData.sbu = [];
                    }

                    if (!rowData.sbu_ids || !Array.isArray(rowData.sbu_ids)) {
                        rowData.sbu_ids = [];
                    }

                    selectedSbus.forEach(function(item) {
                        let sbuText = item.text;
                        let sbuId = parseInt(item.id);

                        if (!rowData.sbu.includes(sbuText)) {
                            rowData.sbu.push(sbuText);
                        }

                        if (!rowData.sbu_ids.includes(sbuId)) {
                            rowData.sbu_ids.push(sbuId);
                        }
                    });

                    row.data(rowData);
                });

                dt.distributionEntriesTbl.draw(false);

                swal("Success", "SBU added to selected row(s).", "success");

                toggleSaveButton();
                $('#sbuSelect').val(null).trigger('change');
                $('#applySbuBtn').hide();
                $('#sbuContainer').slideUp();
                $('.selectSbu').hide();
            });


            $('#distributionEntriesTbl tbody').on('click', '.removeSbu', function (e) {
                e.stopPropagation();

                let sbuToRemove = $(this).data('sbu');
                let tr = $(this).closest('tr');
                let row = dt.distributionEntriesTbl.row(tr);
                let rowData = row.data();

                swal({
                    title: "Remove SBU?",
                    text: `Remove ${sbuToRemove}?`,
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Yes"
                }, function (isConfirm) {

                    if (isConfirm) {

                        rowData.sbu = rowData.sbu.filter(s => s !== sbuToRemove);

                        row.data(rowData).draw(false);
                        toggleSaveButton();

                        swal("Removed!", "SBU removed.", "success");
                    }

                });
            });

             function toggleSaveButton() {
             let hasSbu = false;

                dt.distributionEntriesTbl.rows().every(function () {
                    let rowData = this.data();

                    if (rowData.sbu && rowData.sbu.length > 0) {
                        hasSbu = true;
                        return false;
                    }
                });

                if (hasSbu) {
                    $('.btness').show();
                } else {
                    $('.btness').hide();
                }
            }

            $('#saveCustom').on('click', function () {
            let moveId = $('#myModalOdooEntries').data('id');

            if (!moveId) {
                swal("Error", "No selected journal entry.", "error");
                return;
            }

            let selectedLines = [];

            dt.distributionEntriesTbl.rows().every(function () {
                let rowData = this.data();
                console.log('rowData:', rowData);

                if (rowData.sbu_ids && rowData.sbu_ids.length > 0) {
                    selectedLines.push({
                        move_line_id: rowData.aml_id,
                        sbu: rowData.sbu_ids,
                        debit: rowData.debit || 0,
                        credit: rowData.credit || 0,
                        analytic_account_id: rowData.analytic_account_id || null,
                        analytic_account: rowData.account_analytic || '',
                        account_id: rowData.account_id || null,
                        account_name: rowData.account_name || ''
                    });
                }
            });

            if (selectedLines.length === 0) {
                swal("Warning", "No SBU-tagged rows to save.", "warning");
                return;
            }

            $.ajax({
                url: 'ajax/transaction/save_custom_distribution_lines.php',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    move_id: moveId,
                    rows: selectedLines
                }),
                success: function (response) {
                    if (response.status === 'success') {
                        swal("Success", response.message, "success");
                        $('.btness').hide();
                    } else {
                        swal("Error", response.message, "error");
                    }
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    swal("Error", "Failed to save distribution lines.", "error");
                }
            });
        });
        

        $('#editDateRange').on('click', function () {
            $('#fromDate, #toDate').prop('disabled', false);

            $('#filterBtn').show();
            $(this).hide(); 
        });

        function formatDate(dateStr) {
            if (!dateStr) return '';

            let d = new Date(dateStr);

            return new Intl.DateTimeFormat('en-PH', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            }).format(d);
        }

         $('#filterBtn').on('click', function () {
            let fromDate = $('#fromDate').val(); 
            let toDate = $('#toDate').val();
            let moveId = $('#myModalOdooEntries').data('id');
            let totalAmounts = $('#myModalOdooEntries').data('total-amount');
            let accountingDate = $('#myModalOdooEntries').data('accounting-date');
            // console.log(totalAmounts);

            if (!fromDate || !toDate) {
                swal("Warning", "Please select both dates.", "warning");
                return; 
            }

            if (fromDate > toDate) {
                swal("Warning", "From date cannot be greater than To date.", "warning");
                return;
            }

            if (!moveId || !totalAmounts || !accountingDate) {
                swal("Error", "No selected journal entry.", "error");
                return;
            }

            let display = `${formatDate(fromDate)} - ${formatDate(toDate)}`;
            $('#dateRangeDisplay').text(display);

            $.ajax({
                url: 'ajax/transaction/save_custom_distribution.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    move_id: moveId,
                    from_date: fromDate,
                    to_date: toDate, 
                    total_amount: totalAmounts,
                    accounting_date: accountingDate
                },
                success: function (response) {
                    if (response.status === 'success') {
                        dt.distributionEntriesTbl.draw();

                        $('#fromDate, #toDate').prop('disabled', true);
                        $('#filterBtn').hide();
                        $('#editDateRange').show();
                        init();

                        swal("Success", "Date range saved.", "success");
                    } else {
                        swal("Error", response.message, "error");
                    }
                },
                error: function () {
                    swal("Error", "Failed to save date range.", "error");
                }
            });
        });

                function toggleMassDateRange() {
                let checkedCount = distTable.$('.rowCheckbox:checked').length;

                if (checkedCount > 0) {
                    $('#massDateRangeSection').fadeIn();
                } else {
                    $('#massDateRangeSection').fadeOut();
                    $('#massFromDate').val('');
                    $('#massToDate').val('');
                }
            }

            $('#distTable tbody').on('change', '.rowCheckbox', function () {
                toggleMassDateRange();
            });

           $('#filterBtnMass').on('click', function () {
            let fromDate = $('#massFromDate').val();
            let toDate   = $('#massToDate').val();

            if (!fromDate || !toDate) {
                swal("Warning", "Please select both dates.", "warning");
                return;
            }

            if (fromDate > toDate) {
                swal("Warning", "From date cannot be greater than To date.", "warning");
                return;
            }

            let checkedBoxes = distTable.$('.rowCheckbox:checked');

            if (checkedBoxes.length === 0) {
                swal("Warning", "No selected rows.", "warning");
                return;
            }

            let selectedMass = [];

            checkedBoxes.each(function () {
                let tr = $(this).closest('tr');
                let row = distTable.row(tr); 
                let rowData = row.data();

                rowData.from_date = fromDate;
                rowData.to_date = toDate;
                row.data(rowData);

                selectedMass.push({
                    move_id: rowData.am_id,
                    total_amount: rowData.amount_total,
                    accounting_date: rowData.accounting_date
                });
            });

            distTable.draw(false);

            $.ajax({
                url: 'ajax/transaction/save_mass_custom_distribution.php',
                type: 'POST',
                dataType: 'json',
                contentType: 'application/json',
                data: JSON.stringify({
                    from_date: fromDate,
                    to_date: toDate,
                    rows: selectedMass
                }),
                success: function (res) {
                    if (res.status === 'success') {
                        swal("Success", "Date range applied and saved.", "success");

                        $('#massDateRangeSection').hide();
                        $('#massFromDate').val('');
                        $('#massToDate').val('');
                        distTable.$('.rowCheckbox').prop('checked', false);
                        init();
                    } else {
                        swal("Error", res.message || "Failed to save.", "error");
                    }
                },
                error: function (xhr) {
                    console.log(xhr.responseText);
                    swal("Error", "Server error while saving.", "error");
                }
            });
        });

        async function init() { 
            try {
                if ($('#yearMonthSelect').val() == '') {
                    curr_month_year = getCurrentMonthYear();
                    $('#yearMonthSelect').val(curr_month_year)
                }
                monthYear = $('#yearMonthSelect').val()

                await fetchAccrual(monthYear);

                initDistTable();
            } catch (error) {
                console.error("Init error:", error);
            }
        }

        init();



        $("#yearMonthSelect").on("change", function() {
            let yearMonth = $(this).val();
            console.log(yearMonth)
            if (!yearMonth) return;
            fetchAccrual(yearMonth)
        });


        $("#distTable tbody").on("click", ".editBtn", function() {

            let id = $(this).data("id");
            let $row = $(this).closest('tr');

            let $btn = $(this);
            let $icon = $btn.find('i');
            // let $row = $btn.closest('tr');
            $row.find('.cancelBtn').toggleClass('d-none');
            if ($btn.data('btn') === 'edit') {
                // enter edit mode
                removeDisabledAttr($row, '.accountSelect'); // function in helpers.js
                removeDisabledAttr($row, '.distribution-input'); // function in helpers.js
                removeDisabledAttr($row, '.disttemplateSelect'); // function in helpers.js
                $btn.data('btn', 'save');
                $icon.removeClass('fa-pencil').addClass('fa-save');


            } else {

                new_credit_to_id = $row.find(".accountSelect").val()
                new_acc_value = $row.find(".distribution-input").val()
                new_template_id = $row.find(".disttemplateSelect").val()

                swal({
                        title: "Are you sure?",
                        text: "once submitted, you cannot revert this action",
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
                            startLoading('body')
                            $.ajax({
                                url: "ajax/transaction/update_accrual.php",
                                type: 'post',
                                dataType: "json", 
                                data: {
                                    new_credit_to_id: new_credit_to_id,
                                    new_acc_value: new_acc_value,
                                    new_template_id: new_template_id,
                                    accrual_id: id
                                },
                                success: function(data) {
                                    stopLoading('body')
                                    swal.close();
                                    // init()

                                }
                            });


                        } else {
                            swal("Saving cancelled", "", "error");
                        }

                    });

            }
        });
        $("#distTable tbody").on("click", ".cancelBtn", function() {


            const row = $(this).closest('tr');
            const rowData = distTable.row(row).data();



            row.find(".accountSelect").val(rowData.credit_to_id).prop("disabled", true).trigger("change");
            row.find(".distribution-input").val(rowData.total_accrual_value).prop("disabled", true);
            row.find(".disttemplateSelect").val(rowData.category_id).prop("disabled", true).trigger("change");


            row.find('.saveEditBtn')
                .text('Edit')
                .removeClass('saveEditBtn')
                .addClass('editBtn');

            $(this).toggleClass('d-none');
            row.find('.editBtn').data('btn', 'edit');
            row.find('.editBtn').find('i').removeClass('fa-save').addClass('fa-pencil');
            // find('.editBtn').toggleClass('d-none');

        });

        $("#distTable tbody").on("click", ".deleteBtn", function() {

            const acc_id = $(this).attr('data-id');

            deleteAccrual(acc_id) 
            //pandelete
        });


        $("#distTable tbody").on("click", ".odooEntriesBtn", function() {

            let journal_entries_id = $(this).data("id");
            let total_amount = $(this).data("total-amount");
            let accounting_date = $(this).data("accounting-date");
            let from_date = $(this).data("from-date");
            let to_date = $(this).data("to-date");

            $("#myModalOdooEntries")
                .data("id", journal_entries_id)
                .data("total-amount", total_amount)
                .data("accounting-date", accounting_date)
                .data("from-date", from_date)
                .data("to-date", to_date)
                .modal("show");

        });


        $('#myModalOdooEntries').on('shown.bs.modal', function() { 

            let am_id = $(this).data("id");
            let totalAmount = $(this).data("total-amount");
            let accountingDate = $(this).data("accounting-date");
            let fromDate = $(this).data("from-date");
            let toDate = $(this).data("to-date");
            // console.log("Modal shown with am_id:", am_id, "totalAmount:", totalAmount, "accountingDate:", accountingDate);
            startLoading('#myModalOdooEntries .modal-content');
            $('.btn-groups').hide();
            $('.modal-title').hide();  
            // $('.status-ribbon').hide();

            $('#distributionEntriesContainer').show();

                $('#fromDate').val(fromDate ? formatInputDate(fromDate) : '');
                $('#toDate').val(toDate ? formatInputDate(toDate) : '');

            reinitializeMoTbl();
            loadOdooEntries(am_id, totalAmount, accountingDate, fromDate, toDate);
            // loadMoList(aml_id);

            // setTimeout(function() {
            //     filterEntryGroup('distribution');
            // }, 200);

        }); // END


        function formatInputDate(dateStr) {
            if (!dateStr) return '';

            let d = new Date(dateStr);
            if (isNaN(d)) return '';

            let month = ('0' + (d.getMonth() + 1)).slice(-2);
            let day   = ('0' + d.getDate()).slice(-2);

            return `${d.getFullYear()}-${month}-${day}`;
        }



        ///////////////////////////////////// FUNCTION SIDE ///////////////////////////////////////////////////////////////////////////////////////////////////
        function getCurrentMonthYear() {
            const d = new Date();
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0');
            return `${year}-${month}`;
        }

        function normalizeText(text) {
            return String(text)
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ' ');
        }



        async function distTemplateList() {
            return $.ajax({
                type: 'get',
                url: 'ajax/fetch/fetch_cust_apv.php',
                dataType: 'json' // 👈 THIS
            });

            // return templates

        }



        function fetchAccrual(yearMonth) {
            $.ajax({
                url: "ajax/fetch/fetch_cust_apv.php",
                method: "POST",
                data: {
                    year_month: yearMonth
                },
                dataType: "json",
                success: function(data) {
                    // $('#btnExcel').show();
                    // console.log(data);


                    // active_acc = data['active_accrual']
                    // date_range_val = data['date_range'];

                    let grouped = {};

                    if (data) {

                        data.forEach(row => {
                            grouped[row.journal_entry] = row;
                        });

                        distTable.clear().rows.add(Object.values(grouped)).draw();

                    }

                    // if (active_acc) {


                    // window.fullData = active_acc;
                    // }

                }
            });
        }

        function loadOdooEntries(am_id, totalAmount, accountingDate) {
            startLoading('#myModalOdooEntries .modal-content');
            $('#distributionEntriesContainer').hide();
            $('#moResultsContainer').hide();
            $('#moResults tbody').empty();
            $('#distributionEntriesTbl_wrapper').show();
            // let dateRangeId = 3;

            // console.log(accruals)
            $.ajax({
                    url: 'ajax/fetch/get_account_move_lines.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        am_id: am_id,
                        totalAmount: totalAmount,
                        accountingDate: accountingDate
                    }
                })
                .done(function(res) {
                    // console.log('AJAX RESPONSE:', res);

                    if (res.status === 'success') {

                        if ($.fn.DataTable.isDataTable('#distributionEntriesTbl')) {
                            dt.distributionEntriesTbl.clear();
                            dt.distributionEntriesTbl.rows.add(res.data).draw();
                        }



                        // console.log(res.data)
                        $('#distributionEntriesContainer').show();
                        $('#myModalOdooEntries #journalEntryNameDisplay').text(res.data[0]['move_name']);

                    } else {
                        alert(res.message || 'Failed to fetch entries.');
                    }

                })
                .fail(function(xhr, status, error) {
                    console.log(error, 'this is the error');
                    alert('Error fetching Odoo entries.');
                })
                .always(function() {
                    setTimeout(function() {
                        stopLoading('#myModalOdooEntries .modal-content');
                        $('.btn-groups').show();
                        $('.modal-title').show();
                        $('.status-ribbon').show();
                    }, 100);
                });
        }

        function initDistTable() { 
            if (distTable) {
                distTable.clear().draw();
                distTable.destroy();
            }
            distTable = $("#distTable").DataTable({
                pageLength: 5,
                columns: [{
                        data: null,
                            render: function(data, type, row) {
                            let checkId = `perRow_${row.am_id}`;
                                return `
                                    <input type="checkbox" id="${checkId}" class="rowCheckbox" value="${row.am_id}">
                                    <label for="${checkId}"></label>
                                `;
                        } 
                    },
                    {
                        data: "am_id"
                    },
                    {
                        data: "journal_entry"
                    },
                    {
                        data: "reference"
                    },
                    {
                        data: "amount_total"
                    },
                    {
                        data: "accounting_date"
                    },
                    {
                        data: "status"
                    },
                    {
                        data: null,
                        render: function(row) {

                            let from = row.from_date ? formatDate(row.from_date) : '';
                            let to   = row.to_date ? formatDate(row.to_date) : '';

                            if (!from && !to) return '';

                            return `
                                <span class="badge bg-blue" style="background-color: #7C7BAD !important; color:#ffffff !important; font-size: 10pt;">
                                    ${from}${from && to ? ' → ' : ''}${to}
                                </span>
                            `;
                        } 
                    },
                    {
                        data: null,
                        render: function(row) {
                            return `
                            <button class="btn btn-primary btn-sm odooEntriesBtn"
                                style="background-color: #23234b !important"
                                data-id="${row.am_id}" data-total-amount="${row.amount_total}" data-accounting-date="${row.accounting_date}" data-from-date="${row.from_date}" data-to-date="${row.to_date}"
                                 s
                                >
                            SETUP
                        </button>
                        `;
                        }
                    },
                ],
                columnDefs: [{
                    targets: 4,
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

        function startLoading(selector) {
            $(selector).waitMe({
                effect: 'win8_linear',
                bg: 'rgba(255,255,255,0.90)',
                color: '#2f2519'
            });
        }

        function stopLoading(selector) {
            $(selector).waitMe('hide');
        }

    });
</script>