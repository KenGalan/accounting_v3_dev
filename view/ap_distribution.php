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
        /* box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); */
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

    /* .totalVal{
        width: 350px;
    } */
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
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx/dist/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- <label for="select">SELECT MONTH YEAR</label> -->
<div class="distTable_wrapper">
    <!-- 
    <select id="yearMonthSelect" data-selected="<?= $selectedYM ?>" class="form-control" style="width:250px;">
        <option class="custom-option" value=""></option>
    </select> -->
    <input type="month" name="month_year" id="yearMonthSelect" class="form-control" style="width:250px; display:unset !important">
    <button class="btn btn-success" id="btnRunDist">Run Distribution</button>
    <button class="btn btn-primary" id="btnModalAccrual">Add Item</button>

    <button class="btn btn-warning" id="btnInsertToOdoo">Insert to Odoo</button>


    <!-- <?php if ($_SESSION['ppc']['emp_no'] == '10929' || $_SESSION['ppc']['emp_no'] == '8228'  || $_SESSION['ppc']['emp_no'] == '10768' || $_SESSION['ppc']['emp_no'] == '10947') { ?>
        <label for="auto-switch">AUTO INSERT TO ODOO</label>
        <label class="switch">

            <input name="auto-switch" id='auto_insert_switch' type="checkbox" class="toggle-notification">
            <span class="slider"></span>
        </label>
    <?php } ?> -->
    <table id="distTable" class="table table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Item ID</th>
                <th>Credit Account</th>

                <th>Total Value</th>
                <th>Dist Category</th>
                <th>Journal Account</th>
                <th>Debit Account</th>
                <th>From Date</th>
                <th>To Date</th>
                <th>Action</th>
                <!-- <th>Account Name</th>
                <th>Dept Name</th>
                <th>Dept Code</th>
                <th>Item Label</th>
                <th>Distribution</th>
                <th>Debit</th>
                <th>Credit</th> -->
            </tr>
        </thead>
        <tbody>
            <!-- <tfoot id="totalSumDist">
            <tr>
                <td colspan="7" style="text-align:right; font-weight:bold; color: #000000;">Total:</td>
                <td id="totalSumDebit" style="font-weight:bold; color: #000000;"></td>
                <td id="totalSumCredit" style="font-weight:bold; color: #000000;"></td>
            </tr>
        </tfoot> -->

        </tbody>
    </table>
</div>
<!-- Modal -->
<div class="modal fade" id="previewInsertToOdoo" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color:#000000 !important;">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <button type="button" class="btn btn-secondary" id="backFormTbl" style="display: none; margin-bottom: 35px; margin-top: -15px;">
                    ← Back
                </button>
                <!-- Distribution Containers -->
                <div id="distributionSection" hidden>
                    <div class="btn-groups">
                        <!-- <button id="backBtn" style="font-size: 14pt; margin-right: 15px;"><i class="fa fa-arrow-circle-left"></i></button> -->
                        <button id="deptBtn" class="active" style="margin-right: 15px;">Department Distribution</button>
                        <button id="sbuBtn" style="margin-right: 15px;">SBU Distribution</button>
                        <button id="moBtn" style="margin-right: 15px;">MO Distribution</button>
                        <button id="wipBtn" style="margin-right: 15px;">Cogs Entries</button>
                    </div>
                    <div style="padding-top:40px;">
                        <div id="sbuTableContainer" style="display:none; ">
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
                                        <!-- <th></th> -->
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
                                        <!-- <th>Quantity Done</th> -->
                                        <th>Earned Hours</th>
                                        <th>Allocation</th>
                                    </tr>
                                </thead>
                                <tbody id='moTable_tbody'></tbody>
                                <tfoot id="totalSaMo">
                                    <tr>
                                        <td colspan="5" style="text-align:right; font-weight:bold">Total:</td>
                                        <td id="totalMOAllocationCell" style="font-weight:bold"></td>
                                        <!-- <td></td> -->
                                    </tr>
                                </tfoot>
                            </table>

                        </div>

                        <div id="deptTableContainer">
                            <table id="modalTable" class="table table-bordered table-striped" style="width:100%; color: #000000;">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Account Name</th>
                                        <th>Dept Name</th>
                                        <th>Dept Code</th>
                                        <th>Item Label</th>
                                        <th>Distribution %</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" style="text-align:right; font-weight:bold;">Total:</td>
                                        <td id="modalTotalDebit" style="font-weight:bold;"></td>
                                        <td id="modalTotalCredit" style="font-weight:bold;"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div id="wipTableContainer" style="display:none;">
                            <table id="wipTable" class="table table-bordered table-striped" style="width:100%; color: #000000;">
                                <thead>
                                    <tr>
                                        <th>Reference</th>
                                        <th>Account Name</th>
                                        <th>Department</th>
                                        <!-- <th>Dept Code</th> -->
                                        <!-- <th>Item Label</th> -->

                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>MOS</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="text-align:right; font-weight:bold;">Total:</td>
                                        <td id="wipTotalDebit" style="font-weight:bold;"></td>
                                        <td id="wipTotalCredit" style="font-weight:bold;"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="divFormTbl">
                    <table class="table table-bordered" id="formTbl">
                        <thead>
                            <tr>
                                <th>Item ID</th>
                                <th>Credit Account</th>

                                <th>Total Value</th>
                                <th>Dist Category</th>
                                <th>Journal Name</th>
                                <th>Debit Account</th>
                                <th>Action</th>
                                <!-- <th>Account Name</th>
                <th>Dept Name</th>
                <th>Dept Code</th>
                <th>Item Label</th>
                <th>Distribution</th>
                <th>Debit</th>
                <th>Credit</th> -->
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="d-flex jc-fe">

                    <button class="btn btn-danger" id="btnRevert" style="margin-right: 15px;">Revert</button>
                    <button class="btn btn-primary" id="btnSubmitToOdoo">Submit</button>
                </div>

            </div> <!-- modal body -->
        </div>
    </div>
</div>

<div class="modal" id="accrualTagging">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 0 10px">
                <!-- <h4 id="modalHeader"></h4> -->
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="btn-sm mb-0">
                        Import Excel
                        <input type="file" id="importExcel" hidden>
                    </label>
                </div>
                <div class="excelFormat" style="margin-left: 8px; margin-bottom: 15px; display:inline-block !important;">
                    <a id="downloadExcelBtn">Download Excel Format</a>
                </div>
                <div class="mb-2 text-end">
                    <button type="button" class="btn btn-primary btn-sm" id="addAccRow" style="background-color: #7C7BAD !important;">
                        <i class="fa fa-plus"></i>
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" id="resetAccrualTagging">
                        RESET TABLE
                    </button>

                    <span id="addRowMsg" class="text-danger ms-2" style="display:none; margin-left: 15px;">
                        Complete the first row before adding new one.
                    </span>
                </div>
                <table class="table table-bordered" id="addAccrualTbl">
                    <thead>
                        <tr>
                            <th>Credit Account</th>
                            <th>Total Value</th>
                            <th>Dist Category</th>
                            <th>Journal Account</th>
                            <th>From Date</th>
                            <th>To Date</th>
                            <th></th>
                            <!-- <th></th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- <tr>
                            <td> <select name="" class="form-control creditAcc" id="txtDeptGroupSel"></select></td>
                            <td><input type="number" class="form-control totalVal" id="totalAccrualVal"></td>
                            <td><select class="form-control distCat" id="distCat"></select></td>
                        </tr> -->
                    </tbody>
                </table>
            </div>

            <div class="modal-footer">
                <button class="cancelBtn" data-dismiss="modal">Cancel</button>
                <button class="saveBtn" id="saveAccTagBtn">Save</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var dt = {};
        let distTable;
        let PreviewDistTable;
        let addAccrualTbl;
        let accounts_tagged_global = [];
        let dist_template_global = [];
        let journal_acc_global = [];
        let accrualTableInitialized = false;
        let currentMonthYearValue = $('#yearMonthSelect').val();

        async function init() {
            accounts_tagged_global = await accountList();
            dist_template_global = await distTemplateList();
            journal_acc_global = await journalAccList();
            initDistTable();
            // loadYearMonth();
            fetchAccrual('')
        }

        init();


        $('#btnExcel').hide();


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
                removeDisabledAttr($row, '.journalAccTemp');
                removeDisabledAttr($row, '.fromDateInput');
                removeDisabledAttr($row, '.toDateInput');
                $btn.data('btn', 'save');
                $icon.removeClass('fa-pencil').addClass('fa-save');


            } else {
                // save logic
                // saveRow($row);
                new_credit_to_id = $row.find(".accountSelect").val()
                new_acc_value = $row.find(".distribution-input").val()
                new_template_id = $row.find(".disttemplateSelect").val()
                new_journal_id = $row.find(".journalAccTemp").val()
                let new_from_date = $row.find(".fromDateInput").val();
                let new_to_date = $row.find(".toDateInput").val();
                // console.log(new_template_id, new_acc_value, new_credit_to_id)
                // return; 

                if (new_from_date && new_to_date && new_from_date > new_to_date) {
                    swal("Warning", "Invalid date range.", "warning");
                    return;
                }
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
                                url: "ajax/transaction/update_ap_accrual.php",
                                type: 'post',
                                dataType: "json",
                                data: {
                                    new_credit_to_id: new_credit_to_id,
                                    new_acc_value: new_acc_value,
                                    new_template_id: new_template_id,
                                    new_journal_id: new_journal_id,
                                    new_from_date: new_from_date,
                                    new_to_date: new_to_date,
                                    accrual_id: id
                                },
                                success: function(data) {
                                    stopLoading('body')
                                    swal.close();
                                    init()

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
            row.find(".journalAccTemp").val(rowData.journal_id).prop("disabled", true).trigger("change");


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
            // const row = $(this).closest('tr');
            // const rowData = distTable.row(row).data();

            // console.log(acc_id)
            deleteAccrual(acc_id)
            //pandelete
        });


        $("#btnRunDist").on("click", function() {
            let yearMonth = '02-17-2026';
            let month_id = $(this).data('id');

            console.log('MONTH ID', month_id);
            // console.log('YEAR MONTH', yearMonth);

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
                        startLoading('body')
                        generateJournalEntries(month_id, yearMonth);
                        swal.close();
                    } else {
                        swal("Saving cancelled", "", "error");
                    }

                });
        })

        // Added by Ivan 03/23/26 - To check if 100% na ang distribution category.
        function validateDistRow($row) {

            $select = $row.find('.distCat');
            selectedID = $select.val();

            $container = $select.next('.select2-container');

            $container.find('.select2-selection').removeClass('dist-error');
            $row.find('.dist-warning').remove();

            if (!selectedID) return;

            tpl = dist_template_global.find(t => t.id == selectedID);
            if (!tpl) return;

            if (parseInt(tpl.total_percentage) !== 100) {

                $container.find('.select2-selection').addClass('dist-error');

                $select.closest('td').append(`
                        <div class="dist-warning text-danger" style="font-size:11px;">
                            Warning: Selected distribution is not complete. (${tpl.total_percentage}%)
                        </div>
                    `);
            }

        }

        $('#addAccRow').on('click', function() {
            addAccrualRow();
            // $container = $select.next('.select2-container');
            // container.find('.select2-selection').addClass('dist-error');
        }); // END OF ADDING NEW ROW

        $('#addAccrualTbl').on('change keyup', '.creditAcc, .distCat, .totalVal', function() {
            validateFirstRow(false);
            validateDistRow($(this).closest('tr'));
            // Added by Ivan 03/24/26 - To remove error in totalVal once the user interact with the input.
            let $input = $(this);
            $input.removeClass('input-error');

        }); // END OF VALIDATION 

        // $(document).on('input', '.totalVal', function () {

        // });

        // REMOVE ADDED ROW <button class="btn btn-danger btn-sm removeRow">X</button>
        //     $(document).on('click', '.removeRow', function () {

        //     const table = $('#formTbl').DataTable();

        //     const row = $(this).closest('tr');

        //     table.row(row).remove().draw(false);
        // }); // END OF REMOVE ADDED ROW

        // BUTTON NG ACCRUAL
        $('#btnModalAccrual').on('click', async function() {
            month_id = $(this).attr('data-id');
            year_month = $(this).attr('data-yearmonth-id');
            // if (!month_id) {
            //     console.log('wala ngani')
            //     swal("Please setup date range first!", "", "error");

            // } else {
            $('#accrualTagging #saveAccTagBtn').attr('data-id', month_id)
            $('#accrualTagging #saveAccTagBtn').attr('data-yearmonth-id', year_month)
            await accountList();
            await distTemplateList();
            await journalAccList();
            $('#accrualTagging').modal('show');
            // }

        }); // END

        // Added by Ivan 03/23/26 - Categories will be selecetd if distribution is not 100%
        // $(document).on('change', '.distCat', function () {

        //      selectedID = $(this).val();
        //      // console.log('Nakukuha', selectedID)
        //      if (!selectedID) return;

        //    selectedTemplate = dist_template_global.find(t => t.id == selectedID);
        //    console.log('ano percentage', selectedTemplate)

        //      if (!selectedTemplate) return;

        //      if (parseInt(selectedTemplate.total_percentage) !== 100) {

        //         swal(
        //             "Incomplete Distribution Template",
        //             selectedTemplate.acc_category + " percentage is not yet 100%.",
        //             "warning"
        //          );

        //         $(this).val('').trigger('change.select2');

        //         return false;
        //      }

        // }); // END


        $('#yearMonthSelect').on('change', function() {
            const newMonthSelected = $(this).val();


            // // check if may laman DataTable
            // if (distTable.rows().count() > 0) {
            //     alert("You can't change date when you have active accrual.");

            //     // revert selection
            //     $(this).val(currentMonthYearValue);

            //     return;
            // }

            // update current value
            // currentMonthYearValue = newValue;

            // proceed
            fetchAccrual(newMonthSelected);
        });
        // BUTTON NG ACCRUAL
        $('#btnInsertToOdoo').on('click', async function() {
            // fetchAccrual()
            date_range_id = $(this).data('id');
            year_month = $(this).data('yearmonth-id');
            // console.log(date_range_id)

            previewJournalEntries(date_range_id, year_month)
            $('#previewInsertToOdoo').modal('show');
            $('#previewInsertToOdoo #btnSubmitToOdoo').attr('data-id', date_range_id)
        }); // END
        // ANG MODAL
        $('#accrualTagging').on('shown.bs.modal', function() {


            if (addAccrualTbl) {
                addAccrualTbl.clear().draw();
                addAccrualTbl.destroy();

            }

            addAccrualTbl = $("#addAccrualTbl").DataTable({
                pageLength: 5,
                // drawCallback: function() {
                //     $('.creditAcc').select2({
                //         width: 'resolve',
                //         placeholder: 'Select Account'
                //     });

                //     $('.distCat').select2({
                //         width: 'resolve',
                //         placeholder: 'Select Templates'
                //     });

                //     $('.distCat.dist-error').each(function () {

                //     $(this)
                //         .next('.select2-container')
                //         .find('.select2-selection')
                //         .addClass('dist-error');

                // }); 

                // },


                drawCallback: function() {
                    $('.creditAcc').select2({
                        width: 'resolve',
                        placeholder: 'Select Account'
                    });

                    $('.distCat').select2({
                        width: 'resolve',
                        placeholder: 'Select Templates'
                    });
                    $('.journalAccount').select2({
                        width: 'resolve',
                        placeholder: 'Select Journal'
                    });
                    $('#addAccrualTbl tbody tr').each(function() {
                        validateDistRow($(this));
                    });
                },
                columns: [{
                        data: null,
                        render: function(row) {


                            html = '';
                            html += '<select class="creditAcc" id="txtDeptGroupSel" >';
                            html += `<option value="">Select Account</option>`;

                            accounts_tagged_global.forEach(j => {


                                if (j.full_name) {


                                    html += `<option value="${j.id}" ${row.credit_to_id== j.id ? 'selected' : ''}>${j.full_name}</option>`;
                                }

                            });

                            html += `</select>`;


                            return html;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {
                            return `
                            <input type="text"
                                class="form-control distribution-input totalVal"
                                value="${row.total_accrual_value}"
                                id="totalAccrualVal"
                                style="width:100%;"
                                oninput="
                                        let v = this.value;
                                        v = v.replace(/[^0-9.]/g,'');
                                        const parts = v.split('.');
                                        if(parts.length > 2){
                                            v = parts[0] + '.' + parts.slice(1).join('');
                                        }
                                        this.value = v;
                                ">
                        `;
                        }
                        // data: "total_accrual_value"

                        // ,
                    },



                    {
                        data: null,
                        render: function(row) {

                            html = '';

                            // isError = '';

                            // if (row.category_id) {

                            //     tpl = dist_template_global.find(t => t.id == row.category_id);

                            //     if (tpl && parseInt(tpl.total_percentage) !== 100) {
                            //         isError = 'dist-error';
                            //     }
                            // }

                            html += `<select class="disttemplateSelect distCat">`;
                            html += `<option value="">Select Template</option>`;

                            dist_template_global.forEach(j => {
                                html += `<option value="${j.id}" ${row.category_id== j.id ? 'selected' : ''}>
                                            ${j.acc_category}
                                        </option>`;
                            });

                            html += `</select>`;

                            return html;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {

                            html = '';

                            // isError = '';

                            // if (row.category_id) {

                            //     tpl = dist_template_global.find(t => t.id == row.category_id);

                            //     if (tpl && parseInt(tpl.total_percentage) !== 100) {
                            //         isError = 'dist-error';
                            //     }
                            // }

                            html += `<select class="journalAccTemp journalAccount">`;
                            html += `<option value="">Select Template</option>`;

                            journal_acc_global.forEach(j => {
                                html += `<option 
                                        value="${j.journal_id}" 
                                        data-name="${j.journal_acc}"
                                        ${row.journal_id == j.journal_id ? 'selected' : ''}>
                                        ${j.journal_acc}
                                    </option>`;
                            });

                            html += `</select>`;

                            return html;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {
                            return `
                            <input type="date"
                                class="form-control from-date"
                                value="${row.from_date}"
                                id="fromDate"
                                style="width:100%;"
                            >
                        `;
                        }

                    },
                    {
                        data: null,
                        render: function(row) {
                            return `
                            <input type="date"
                                class="form-control to-date"
                                value="${row.to_date}"
                                id="toDate"
                                style="width:100%;"
                            >
                        `;
                        }

                    },
                    {
                        data: null,
                        render: function() {

                            return `
                                <button class="btn btn-danger btn-sm removeRow">
                                    X
                                </button>
                            `;
                        }
                    }
                ]
            });

            addAccrualTbl.row.add({
                credit_to_id: "",
                total_accrual_value: "",
                category_id: "",
                from_date: "",
                to_date: ""
            }).draw(false);
            // selectAllAccounts($('#addAccrualTbl tbody tr:first .creditAcc'));
            // selectDistCat($('#addAccrualTbl tbody tr:first .distCat'));

        }); // END MODAL

        // Added by Ivan 03/23/26 - To remove row in accrual tagging inside modal
        $(document).on('click', '.removeRow', function() {

            if (addAccrualTbl.rows().count() === 1) {
                // Wala nalang siyang gagawin
                return;
            }

            CheckRow = addAccrualTbl.row($(this).closest('tr'));

            swal({
                title: "Remove Row?",
                text: "This row will be deleted.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Yes, remove it"
            }, function(isConfirm) {

                if (isConfirm) {
                    CheckRow.remove().draw(false);
                }

            });

        });

        // Added by Ivan 03/23/26 - To reset accrual tagging table inside modal
        $(document).on('click', '#resetAccrualTagging', function() {

            rowCount = addAccrualTbl.rows().count();

            // if (rowCount <= 1) {
            //     return;
            // }

            swal({
                title: "Reset Accrual Tagging?",
                text: "All added accrual rows will be removed.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Yes, reset it"
            }, function(isConfirm) {

                if (isConfirm) {

                    addAccrualTbl.clear().draw();

                    addAccrualTbl.row.add({
                        credit_to_id: "",
                        total_accrual_value: "",
                        category_id: ""
                    }).draw(false);

                }

            });

        });

        // WALA ERROR PA ITO GAR
        // $('#importExcel').on('change', function(e) {
        //     // ANO BA FORMART?
        // });  // END

        $('#importExcel').on('change', async function(e) {

            let file = e.target.files[0];
            if (!file) return;

            startLoading('body');
            // console.log('daan')

            try {

                accounts = await accountList();
                templates = await distTemplateList();
                journalAcc = await journalAccList();

                let reader = new FileReader();

                reader.onload = function(evt) {

                    let data = new Uint8Array(evt.target.result);
                    let workbook = XLSX.read(data, {
                        type: 'array',
                        cellFormula: true,
                        cellValue: true
                    });

                    let sheet = workbook.Sheets[workbook.SheetNames[0]];
                    let rows = XLSX.utils.sheet_to_json(sheet, {
                        header: 1,
                        raw: false
                    });

                    total = 0;
                    let lastAccountCode = '';

                    rows.forEach((row, index) => {
                        if (index === 0) return;
                        if (!row || row.every(cell => cell === undefined || cell === "")) return;

                        let credit = row[0];

                        if (credit) {
                            lastAccountCode = credit;
                        } else {
                            credit = lastAccountCode;
                        }

                        let rawAmount = row[2];
                        let distId = parseInt(row[4]);

                        let amount = parseFloat(rawAmount);

                        // console.log('t', amount)

                        // if(amount === 0){
                        //     amount = '';
                        // }

                        // if (isNaN(amount)) {

                        //     setTimeout(function () {
                        //         $amountInput
                        //             .val('')
                        //             .addClass('input-error');

                        //         $amountInput.closest('td').append(
                        //             `<div class="text-danger amount-warning" style="font-size:11px">
                        //                 Invalid amount
                        //             </div>`
                        //         );
                        //     }, 50);

                        // } else { 
                        //     total += amount;
                        // }

                        addAccrualRow();

                        lastIndex = addAccrualTbl.rows().count() - 1;
                        lastRowNode = addAccrualTbl.row(lastIndex).node();
                        $lastRow = $(lastRowNode);
                        // let $amountInput = $lastRow.find('.totalVal');

                        if (isNaN(amount)) {
                            $lastRow.find('.totalVal')
                                .val('')
                                .addClass('input-error');
                        } else {
                            $lastRow.find('.totalVal')
                                .val(amount)
                                .removeClass('input-error');

                            total += amount;
                        }

                        //  console.log('Nakuha', lastRow) 

                        // accMatch = accounts.find(a => {
                        //     let dbCode = normalizeText(a.full_name.split(' ')[0]);
                        //     let excelCode = normalizeText(credit);
                        //     return dbCode === excelCode; 
                        // });

                        // if (accMatch) { 
                        //     $lastRow.find('.creditAcc')
                        //         .val(accMatch.id)
                        //         .trigger('change.select2');
                        // } 

                        let $accSelect = $lastRow.find('.creditAcc');

                        let accMatch = accounts.find(a => {
                            let dbCode = normalizeText(a.full_name.split(' ')[0]);
                            let excelCode = normalizeText(credit);
                            return dbCode === excelCode;
                        });

                        if (accMatch) {
                            $accSelect
                                .val(accMatch.id)
                                .trigger('change.select2');
                        } else {
                            $accSelect
                                .val('')
                                .trigger('change.select2');
                        }

                        // distMatch = templates.find(t => {

                        //     let dbCat = normalizeText(t.acc_category);
                        //     let excelCat = normalizeText(dist);

                        //     return dbCat === excelCat;
                        // });

                        let distMatch = templates.find(t => parseInt(t.id) === distId);

                        // Hindi mag display yung category pag hindi 100%
                        // if (distMatch) {

                        //     if (parseInt(distMatch.total_percentage) !== 100) {

                        //         stopLoading('body');

                        //         swal(
                        //             "Need Action!",
                        //             distMatch.acc_category + " percentage is not complete.",
                        //             "error"
                        //         );
                        //          $lastRow.find('.totalVal').val(amount);
                        //         throw new Error("Distribution percentage not 100");
                        //     }

                        //     $lastRow.find('.distCat')
                        //         .val(distMatch.id)
                        //         .trigger('change.select2');
                        // }

                        // Display pero may warning if not 100%
                        if (distMatch) {

                            let $distSelect = $lastRow.find('.distCat');

                            $distSelect
                                .val(distMatch.id)
                                .trigger('change.select2');

                            validateDistRow($lastRow);

                        }
                        // $lastRow.find('.totalVal').val(amount);

                        // if (distMatch) {
                        //     $lastRow.find('.distCat') 
                        //         .val(distMatch.id)
                        //         .trigger('change.select2');
                        // }


                    });

                    // $('#grandTotal').val(total.toFixed(2));

                    // $('#grandTotal').val(total);

                    stopLoading('body');

                    swal("Success", "Excel imported.", "success");

                };

                reader.readAsArrayBuffer(file);

            } catch (err) {

                stopLoading('body');
                console.error(err);

                swal("Error", "Import failed.", "error");
            }

        });

        function normalizeText(text) {
            return String(text)
                .toLowerCase()
                .trim()
                .replace(/\s+/g, ' ');
        }

        $(document).on('click', '#downloadExcelBtn', function() {
            const link = document.createElement('a');
            link.href = 'public/assets/template/accrual_template.xlsx';
            link.download = 'accrual_template.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // $(document).on('click', '#downloadExcelBtn', function() {
        //     window.location.href = 'ajax/fetch/download_accrual_template.php';
        // });

        $('#previewInsertToOdoo #formTbl').on('click', '.viewBtn', function() {
            $('#divFormTbl').attr('hidden', true)
            $('#distributionSection').attr('hidden', false)
            $('#backFormTbl').show()
            ////goback

            reinitializeMoTbl();
            let id = $(this).data("id");
            let date_range_id = $(this).data("range-id");

            fetchMoDist(id, date_range_id);
            fetchWipEntries(id, date_range_id);

            let records = window.fullData.filter(r => r.journal_entry_id == id);

            modalTable.clear().rows.add(records).draw();


            let totalDebit = 0,
                totalCredit = 0;
            records.forEach(r => {
                totalDebit += parseFloat(r.debit || 0);
                totalCredit += parseFloat(r.credit || 0);
            });
            $("#modalTotalDebit").text(
                "₱" + totalDebit.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                })
            );

            $("#modalTotalCredit").text(
                "₱" + totalCredit.toLocaleString(undefined, {
                    minimumFractionDigits: 2
                })
            );
            // $("#myModal").modal("show");

        })
        $('#previewInsertToOdoo').on('click', '#backFormTbl', function() {
            $(this).hide()
            $('#divFormTbl').attr('hidden', false)
            $('#distributionSection').attr('hidden', true)

        })
        $('#moBtn').on('click', function() {
            const amount = $('.infoBtn').data('amount');
            const fromDate = $('.infoBtn').data('from');
            const toDate = $('.infoBtn').data('to');

            $('#moBtn').addClass('active');
            $('#deptBtn').removeClass('active');
            $('#sbuBtn').removeClass('active');
            $('#wipBtn').removeClass('active');
            $('#deptTableContainer').hide();
            $('#wipTableContainer').hide();
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
            $('#wipBtn').removeClass('active');
            $('#deptTableContainer').hide();
            $('#moTableContainer').hide();
            $('#wipTableContainer').hide();
            $('#sbuTableContainer').show();

            // loadMoTable(fromDate, toDate, amount);
        });

        $('#deptBtn').on('click', function() {
            const amount = $('.infoBtn').data('amount');

            $('#deptBtn').addClass('active');
            $('#moBtn').removeClass('active');
            $('#sbuBtn').removeClass('active');
            $('#wipBtn').removeClass('active');
            $('#moTableContainer').hide();
            $('#deptTableContainer').show();
            $('#sbuTableContainer').hide();
            $('#wipTableContainer').hide();
        });
        $('#wipBtn').on('click', function() {
            const amount = $('.infoBtn').data('amount');

            $('#wipBtn').addClass('active');
            $('#moBtn').removeClass('active');
            $('#sbuBtn').removeClass('active');
            $('#deptBtn').removeClass('active');
            $('#moTableContainer').hide();
            $('#deptTableContainer').hide();
            $('#sbuTableContainer').hide();
            $('#wipTableContainer').show();
        });

        $('#distTable_length').hide(); // hide datatable rows to display
        $('#modalTable_length').hide(); // hide datatable rows to display
        $('#wipTable_length').hide(); // hide datatable rows to display
        $('#moTable_length').hide(); // hide datatable rows to display

        $('#previewInsertToOdoo').on('click', '#btnSubmitToOdoo', function() {
            date_range_id = $(this).attr('data-id');
            // console.log(date_range_id);
            // return
            swal({
                    title: "Are you sure you want to insert to Odoo?",
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

                        startLoading('body');
                        insertToOdoo(date_range_id) //lagay mo month_id or date range id
                        swal.close();
                    } else {
                        swal("Saving cancelled", "", "error");
                    }

                });

        })

        $('#accrualTagging').on('click', '#saveAccTagBtn', function() {
            month_id = $(this).attr('data-id')
            year_month = $(this).attr('data-yearmonth-id')
            new_accrual = getAccrualToInsertData()
            if (!new_accrual) return;
            // console.log(new_accrual)
            insertAccrual(new_accrual, month_id, year_month)
        })




        ///////////////////////////////////// FUNCTION SIDE ///////////////////////////////////////////////////////////////////////////////////////////////////

        // function getAccrualToInsertData() {
        //     let data = [];
        //     let hasError = false;

        //     $('#accrualTagging #addAccrualTbl tbody tr').each(function() {
        //         // let $row = $(this).closest('tr');
        //         // let id = $(this).data('id');
        //         let credit_account = $(this).find('.creditAcc').val();
        //         let accrual_value = $(this).find('.totalVal').val();
        //         let dist_template = $(this).find('.distCat').val();

        //         // if (selectedValue === '') {
        //         //     // Show error message
        //         //     alert(`Please select a value for ID ${id}`);
        //         //     hasError = true;
        //         //     return false; // stop the loop
        //         // }

        //         data.push({
        //             credit_account: credit_account,
        //             accrual_value: accrual_value,
        //             dist_template: dist_template
        //         });
        //     });

        //     if (hasError) return false; // Stop further processing if error

        //     return data;
        // }

        function getAccrualToInsertData() {
            let data = [];
            let hasError = false;

            $('#accrualTagging #addAccrualTbl tbody tr').each(function(index) {

                let credit_account = $(this).find('.creditAcc').val();
                let accrual_value = $(this).find('.totalVal').val();
                let dist_template = $(this).find('.distCat').val();
                let from_date = $(this).find('.from-date').val();
                let to_date = $(this).find('.to-date').val();
                let journal_id = $(this).find('.journalAccount').val();
                let journal_acc = $(this).find('.journalAccount option:selected').data('name');

                if (!credit_account || !accrual_value || !dist_template || accrual_value === '.' || !from_date || !to_date || !journal_id || !journal_acc) {

                    swal(
                        "Incomplete Fields",
                        "Please complete all fields.",
                        "warning"
                    );

                    if (!credit_account) $(this).find('.creditAcc').addClass('is-invalid');
                    if (!accrual_value || accrual_value === '.') $(this).find('.totalVal').addClass('is-invalid');
                    if (!dist_template) $(this).find('.distCat').addClass('is-invalid');
                    if (!from_date) $(this).find('.from-date').addClass('is-invalid');
                    if (!to_date) $(this).find('.to-date').addClass('is-invalid');
                    if (!journal_id) $(this).find('.journalAccount').addClass('is-invalid');
                    if (!journal_acc) $(this).find('.journalAccount').addClass('is-invalid');

                    hasError = true;
                    return false;
                }

                $(this).find('.creditAcc').removeClass('is-invalid');
                $(this).find('.totalVal').removeClass('is-invalid');
                $(this).find('.distCat').removeClass('is-invalid');
                $(this).find('.from-date').removeClass('is-invalid');
                $(this).find('.to-date').removeClass('is-invalid');
                $(this).find('.journalAccount').removeClass('is-invalid');

                data.push({
                    credit_account: credit_account,
                    accrual_value: accrual_value,
                    dist_template: dist_template,
                    from_date: from_date,
                    to_date: to_date,
                    journal_id: journal_id,
                    journal_acc: journal_acc
                });

            });

            if (hasError) return false;

            return data;
        }



        function insertAccrual(acc_data, month_id, year_month) {

            hasError = false;
            // invalidCount = 0;
            // console.log(invalidCount)

            $('#addAccrualTbl tbody tr').each(function() {

                $row = $(this);
                selectedID = $row.find('.distCat').val();

                if (!selectedID) return;

                tpl = dist_template_global.find(t => t.id == selectedID);

                if (tpl && parseInt(tpl.total_percentage) !== 100) {
                    hasError = true;
                    // invalidCount++;
                }

            });

            if (hasError) {

                swal(
                    "Incomplete Distribution Percentage",
                    "Please complete distribution first before saving.",
                    "warning"
                );

                return;
            }

            $.ajax({
                type: 'POST',
                url: 'ajax/transaction/insert_accrual_ap.php',
                dataType: 'json',
                data: {
                    month_id: month_id,
                    year_month: year_month,
                    acc_data: JSON.stringify(acc_data),
                },
                success: function(response) {

                    init();

                    swal(
                        "Success",
                        "Accrual Generated",
                        "success"
                    );

                    $('#accrualTagging').modal('hide');
                }
            });
        }
        async function accountList() {
            return $.ajax({
                type: 'get',
                url: 'ajax/fetch/fetch_all_accounts.php',
                dataType: 'json' // 👈 THIS
            });
        }

        async function distTemplateList() {
            return $.ajax({
                type: 'get',
                url: 'ajax/fetch/fetch_dist_template.php',
                dataType: 'json' // 👈 THIS
            });
        }
        async function journalAccList() {
            return $.ajax({
                type: 'get',
                url: 'ajax/fetch/fetch_journal_acc.php',
                dataType: 'json' // 👈 THIS
            });

            // return templates

        }




        function fetchAccrual(newMonthSelected) {
            $.ajax({
                url: "ajax/fetch/fetch_ap_accrual.php",
                method: "POST",
                data: {
                    year_month: newMonthSelected
                },
                dataType: "json",
                success: function(data) {
                    $('#btnExcel').show();
                    // console.log(data);

                    initDistTable();
                    active_acc = data['active_accrual']
                    date_range_val = data['date_range'];
                    let grouped = {};

                    if (date_range_val) {
                        month_id = date_range_val['id'];
                        year_month = date_range_val['year_month'];

                        currentMonthYearValue = newMonthSelected !== '' ? newMonthSelected : year_month;
                        // console.log(newMonthSelected)
                        $('#yearMonthSelect').val(currentMonthYearValue)


                        $('#btnRunDist').attr('data-id', month_id);
                        $('#btnInsertToOdoo').attr('data-id', month_id);
                        $('#btnModalAccrual').attr('data-id', month_id);
                        $('#btnRunDist').attr('data-yearmonth-id', currentMonthYearValue);
                        $('#btnInsertToOdoo').attr('data-yearmonth-id', currentMonthYearValue);
                        $('#btnModalAccrual').attr('data-yearmonth-id', currentMonthYearValue);


                        $('#btnRevert').attr('data-id', month_id);


                        const distributed = date_range_val['is_ap_distributed'] === 't';
                        $('#btnRunDist').toggle(!distributed);
                        $('#btnInsertToOdoo').toggle(date_range_val['odoo_inserted'] !== 'True' && date_range_val['is_ap_distributed'] === 't');

                        $('#btnModalAccrual').toggle(!distributed);
                    } else {
                        console.log('bat wala')
                        $('#btnRunDist').attr('data-id', '');
                        $('#btnInsertToOdoo').attr('data-id', '');
                        $('#btnModalAccrual').attr('data-id', '');
                        $('#btnRevert').attr('data-id', '');
                        $('#btnRunDist').attr('data-yearmonth-id', newMonthSelected);
                        $('#btnInsertToOdoo').attr('data-yearmonth-id', newMonthSelected);
                        $('#btnModalAccrual').attr('data-yearmonth-id', newMonthSelected);
                        // $('#btnInsertToOdoo').toggle();
                        $('#btnRunDist, #btnInsertToOdoo').css('display', 'none');
                    }

                    if (active_acc) {

                        // $('#yearMonthSelect').prop('readonly', true);
                        active_acc.forEach(row => {
                            grouped[row.id] = row;
                        });

                        distTable.clear().rows.add(Object.values(grouped)).draw();
                        // window.fullData = active_acc;
                    }

                }
            });
        }

        function initDistTable() {
            if (distTable) {
                distTable.clear().draw();
                distTable.destroy();
            }
            distTable = $("#distTable").DataTable({
                pageLength: 5,
                drawCallback: function() {
                    $('.accountSelect').select2({
                        width: 'resolve',
                        placeholder: 'Select Account'
                    });

                    $('.disttemplateSelect').select2({
                        width: 'resolve',
                        placeholder: 'Select Templates'
                    });
                    $('.journalAccTemp').select2({
                        width: 'resolve',
                        placeholder: 'Select Journal'
                    });

                },
                columns: [{
                        data: "id"
                    },
                    {
                        data: null,
                        render: function(row) {


                            html = '';
                            html += '<select class="accountSelect" disabled>';
                            html += `<option value="">Select Account</option>`;

                            accounts_tagged_global.forEach(j => {

                                if (j.full_name) {
                                    html += `<option value="${j.id}" ${row.credit_to_id== j.id ? 'selected' : ''}>${j.full_name}</option>`;
                                }

                            });

                            html += `</select>`;


                            return html;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {
                            return `<input type="text" class="form-control distribution-input" value="${row.total_accrual_value}" style="width:100%;" disabled>`;
                        }

                        // data: "total_accrual_value"

                        // ,
                    },
                    {
                        data: null,
                        render: function(row) {


                            html = '';
                            html += '<select class="disttemplateSelect" disabled>';
                            html += `<option value="">Select Account</option>`;

                            dist_template_global.forEach(j => {

                                html += `<option value="${j.id}" ${row.category_id== j.id ? 'selected' : ''}>${j.acc_category}</option>`;


                            });

                            html += `</select>`;


                            return html;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {


                            html = '';
                            html += '<select class="journalAccTemp" disabled>';
                            html += `<option value="">Select Account</option>`;

                            journal_acc_global.forEach(j => {

                                html += `<option value="${j.journal_id}" ${row.journal_id== j.journal_id ? 'selected' : ''}>${j.journal_acc}</option>`;


                            });

                            html += `</select>`;


                            return html;
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let account_per_accrual = `
                                        <div style="display:flex; flex-direction:column; gap:5px;">
                                    `;

                            let input = row.debit_to || "";
                            let am = input.split(',');
                            hasPlus = 0;

                            am.forEach(v => {
                                let cleanText = v.trim();
                                let isPlus = cleanText.endsWith("+");

                                if (isPlus) {
                                    hasPlus += 1;
                                    cleanText = cleanText.slice(0, -1);
                                }

                                account_per_accrual += `
                                            <span class="dblock" style="
                                                padding:0.3rem 1rem;
                                                background-color:${isPlus ? 'red' : '#ddd'};
                                                color:${isPlus ? 'white' : 'black'};
                                            ">
                                                ${cleanText}
                                            </span>
                                        `;
                            });

                            account_per_accrual += "</div>";
                            return account_per_accrual;
                        }
                    },
                    // {
                    //     data: 'from_date'
                    // },
                    // {
                    //     data: 'to_date'
                    // },
                    // Added by Ivan
                    {
                        data: 'from_date',
                        render: function(data, type, row) {
                            let value = data ? data : '';
                            return `<input type="date" class="form-control fromDateInput" value="${value}" disabled>`;
                        }
                    },
                    {
                        data: 'to_date',
                        render: function(data, type, row) {
                            let value = data ? data : '';
                            return `<input type="date" class="form-control toDateInput" value="${value}" disabled>`;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {
                            // Added by Ivan Christian Afan
                            let disabled = row.is_ap_distributed === 't' ? 'disabled' : '';
                            return `
                        <button class="btn btn-primary btn-sm editBtn" data-btn="edit"
                                ${disabled}
                                style="background-color: #7C7BAD !important"
                                data-id="${row.id}"
                                data-range-id="${row.debit_to}"
                                >
                            <i class="fa fa-pencil"></i>
                        </button>
                        <button class="btn btn-primary btn-sm cancelBtn d-none" 
                                style="background-color: #7C7BAD !important"
                                data-id="${row.id}"
                                data-range-id="${row.debit_to}"
                                >
                            <i class="fa fa-window-close"></i>
                        </button>
                        <button class="btn btn-primary btn-sm deleteBtn" 
                                ${disabled}
                                style="background-color: #7C7BAD !important"
                                data-id="${row.id}"
                                data-range-id="${row.debit_to}"
                                >
                            <i class="fa fa-trash"></i>
                        </button>
                        `;
                        }
                    },
                ]
            });
        }

        function generateJournalEntries(month_id, yearMonth) {

            $.ajax({
                type: 'POST',
                url: 'ajax/transaction/save_distributed_journal.php',
                dataType: 'json',
                data: {
                    month_id: month_id,
                    is_accrual: false
                },

                success: function(response) {
                    console.log(response);


                    init();
                    previewJournalEntries(month_id, yearMonth)
                    $('#previewInsertToOdoo').modal('show');
                    $('#previewInsertToOdoo #btnSubmitToOdoo').attr('data-id', month_id)

                    stopLoading('body')

                }
            });
        }


        // ADDING NEW ROW 
        function addAccrualRow() {
            if (!validateFirstRow(true)) return;

            //    $container = $select.next('.select2-container');
            //    $container.find('.select  2-selection').removeClass('dist-error');

            addAccrualTbl.row.add({
                credit_to_id: "",
                total_accrual_value: "",
                category_id: ""
            }).draw(false);



            // selectAllAccounts($row.find('.creditAcc'));
            // selectDistCat($row.find('.distCat'));


        }


        // VALIDATION IF THE ROW SA TABLE AY EMPTY, HINDI MAKAKAPAG ADD NG NEW ROW
        function validateFirstRow(showMsg = false) {
            const $firstRow = $('#addAccrualTbl tbody tr:last');

            if (!$firstRow.length) {
                $('#addRowMsg').hide();
                return true;
            }

            const creditAcc = $firstRow.find('.creditAcc').val();
            const distCat = $firstRow.find('.distCat').val();
            const totalVal = $firstRow.find('.totalVal').val();

            const isValid = creditAcc && distCat && totalVal;

            if (!isValid && showMsg) {
                $('#addRowMsg').fadeIn();
            } else if (isValid) {
                $('#addRowMsg').fadeOut();
            }

            return isValid;
        }

        function reinitializeMoTbl() {
            if (dt.moTable) {
                dt.moTable.clear().draw();
                dt.moTable.destroy();
            }
            if (dt.modalTable) {
                dt.moTable.clear().draw();
                dt.moTable.destroy();
            }
            if (dt.sbuTable) {
                dt.sbuTable.clear().draw();
                dt.sbuTable.destroy();
            }

            if (dt.wipTable) {
                dt.wipTable.clear().draw();
                dt.wipTable.destroy();
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
                    title: `MO_Distribution_}`,
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
                    targets: 5,
                    render: function(data, type, row) {

                        // RAW value for sorting, filtering, type === 'sort' or 'filter'
                        if (type !== 'display') {
                            return data; // return unchanged 5-decimal raw number
                        }

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
                    }
                    // {
                    //     title: 'Action'
                    // }
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

            // let distTable = $("#distTable").DataTable({
            //     pageLength: 5,
            //     columns: [{
            //             data: "journal_entry_id"
            //         },
            //         {
            //             data: "journal"
            //         },
            //         {
            //             data: null,
            //             render: function(row) {
            //                 return `
            //             <button class="btn btn-primary btn-sm viewBtn"
            //                     style="background-color: #7C7BAD !important"
            //                     data-id="${row.journal_entry_id}"
            //                     data-range-id="${row.date_range_id}"
            //                     >
            //                 View
            //             </button>`;
            //             }
            //         }
            //     ]
            // });


        }
        let modalTable = $("#modalTable").DataTable({
            pageLength: 5,
            paging: true,
            searching: false,
            info: false,
            columns: [{
                    data: "reference"
                },
                {
                    data: "account_name"
                },
                {
                    data: "dept_name"
                },
                {
                    data: "dept_code"
                },
                {
                    data: "item_label"
                },
                {
                    data: "distribution_percentage",
                    render: function(data) {
                        if (data === null || data === undefined) {
                            return '';
                        } else {
                            return data + '%';
                        }
                    }
                },
                {
                    data: "debit"
                },
                {
                    data: "credit",
                    render: function(data) {
                        return "₱" + parseFloat(data).toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        });
                    }
                }
            ],
            columnDefs: [{
                targets: 6,
                render: function(data, type, row) {

                    if (type !== 'display') {
                        return data; // return unchanged 5-decimal raw number
                    }

                    // FORMATTED VALUE ONLY ON DISPLAY
                    return new Intl.NumberFormat('en-PH', {
                        style: 'currency',
                        currency: 'PHP',
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    }).format(data);
                }
            }]
        });


        let wipTable = $("#wipTable").DataTable({
            pageLength: 5,
            paging: true,
            searching: false,
            info: false,
            columns: [{
                    data: "reference"
                },
                {
                    data: "account_name"
                },
                {
                    data: "department"
                },
                {
                    data: "debit"
                },
                {
                    data: "credit",
                    render: function(data) {
                        return "₱" + parseFloat(data).toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        });
                    }
                },
                {
                    data: "mos",
                    render: function(data) {
                        if (!data) return "";

                        const spans = data.split(",").map(mo =>
                            `<span class='dblock' style='background-color: #ddd; padding: 0.3rem 1rem;'>${mo.trim()}</span>`
                        ).join("");

                        return `<div style='display:flex; flex-direction:column; gap:5px;'>${spans}</div>`;
                    }
                }
            ],
            columnDefs: [{
                    targets: 3,
                    render: function(data, type, row) {

                        if (type !== 'display') {
                            return data; // return unchanged 5-decimal raw number
                        }

                        // FORMATTED VALUE ONLY ON DISPLAY
                        return new Intl.NumberFormat('en-PH', {
                            style: 'currency',
                            currency: 'PHP',
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }).format(data);
                    }
                },

                {
                    targets: 5, // index of MOS column (0-based)
                    className: 'moreMO'
                }


            ]
        });

        function fetchMoDist(accrual_id, date_range_id) {
            $.ajax({
                url: "ajax/fetch/fetch_mo_dist.php",
                method: "POST",
                data: {
                    accrual_id: accrual_id,
                    date_range_id: date_range_id
                },
                dataType: "json",
                success: function(data) {
                    // console.log(data)
                    var rows = [];
                    // Loop through each item
                    for (var i = 0; i < data.length; i++) {
                        var mo_dist = data[i];
                        // console.log("Item " + i + ":", item);

                        // Example: append data to a table

                        rows.push([
                            mo_dist['mo'],
                            mo_dist['device'],
                            mo_dist['category'],
                            mo_dist['customer_name'],
                            mo_dist['earned_hrs'],
                            mo_dist['allocation']
                        ]);
                    }
                    dt.moTable.rows.add(rows).draw();

                    if (data) {
                        window.cachedMOData = data;
                        let raw = data;
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
                                row.allocation
                            ]).draw(false);
                        });

                        $('#totalSBUAllocationCell').text(
                            '₱' + totalAllocation.toLocaleString('en-US', {
                                minimumFractionDigits: 2
                            })
                        );
                        let formattedMOTotalAllocation = '₱' + totalAllocation.toLocaleString('en-US', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        });
                        $('#totalMOAllocationCell').text(formattedMOTotalAllocation);
                    }
                }
            });
        }

        function fetchWipEntries(journal_entries_id, date_range_id) {
            $.ajax({
                url: "ajax/fetch/fetch_wip_entries.php",
                method: "POST",
                data: {
                    journal_entries_id: journal_entries_id,
                    date_range_id: date_range_id
                },
                dataType: "json",
                success: function(data) {

                    let totalWipDebit = 0,
                        totalWipCredit = 0;
                    data.forEach(r => {
                        totalWipDebit += parseFloat(r.debit || 0);
                        totalWipCredit += parseFloat(r.credit || 0);
                    });

                    wipTable.clear().rows.add(data).draw();
                    xdataTD = {
                        tableID: "#wipTable",
                        spanClass: ".dblock",
                        tdClass: ".moreMO",
                        showSpan: "3"
                    };
                    showMoreSpan(xdataTD);

                    $("#wipTotalDebit").text(
                        "₱" + totalWipDebit.toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        })
                    );

                    $("#wipTotalCredit").text(
                        "₱" + totalWipCredit.toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        })
                    );

                }
            });
        }

        function insertToOdoo(date_range_id) {
            $.ajax({
                url: "ajax/transaction/insert_journal_entries_to_odoo.php",
                method: "POST",
                dataType: "json",
                data: {
                    month_id: date_range_id,
                    is_accrual: false
                },
                success: function(data) {

                    if (data.status === 'exists') {
                        swal(
                            "Success",
                            "Already Exists",
                            "success"
                        );
                        return;

                    }
                    stopLoading('body');
                    init();

                    $('#previewInsertToOdoo').modal('hide');
                    swal(
                        "Success",
                        "Journal Generated",
                        "success"
                    );
                    // window.location =
                    //     "generated_distribution.php?id=" + month_id + "&ym=" + yearMonth;
                }
            })
        }

        function previewJournalEntries(date_range_id, yearMonth) {
            if (PreviewDistTable) {
                PreviewDistTable.clear().draw();
                PreviewDistTable.destroy();
            }
            PreviewDistTable = $("#formTbl").DataTable({
                pageLength: 5,
                drawCallback: function() {
                    $('.accountSelect').select2({
                        width: 'resolve',
                        placeholder: 'Select Account'
                    });

                    $('.disttemplateSelect').select2({
                        width: 'resolve',
                        placeholder: 'Select Templates'
                    });
                    $('.journalAccTemp').select2({
                        width: 'resolve',
                        placeholder: 'Select Templates'
                    });

                },
                columns: [{
                        data: "id"
                    },
                    {
                        data: "credit_to"
                    },
                    {

                        data: "total_accrual_value"


                    },
                    {
                        data: "distribution_category"
                    },
                    {
                        data: "journal_name"
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            let account_per_accrual = `
                                        <div style="display:flex; flex-direction:column; gap:5px;">
                                    `;

                            let input = row.debit_to || "";
                            let am = input.split(',');
                            hasPlus = 0;

                            am.forEach(v => {
                                let cleanText = v.trim();
                                let isPlus = cleanText.endsWith("+");

                                if (isPlus) {
                                    hasPlus += 1;
                                    cleanText = cleanText.slice(0, -1);
                                }

                                account_per_accrual += `
                                            <span class="dblock" style="
                                                padding:0.3rem 1rem;
                                                background-color:${isPlus ? 'red' : '#ddd'};
                                                color:${isPlus ? 'white' : 'black'};
                                            ">
                                                ${cleanText}
                                            </span>
                                        `;
                            });

                            account_per_accrual += "</div>";
                            return account_per_accrual;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {
                            return `
                            <button class="btn btn-primary btn-sm viewBtn"
                                style="background-color: #7C7BAD !important"
                                data-id="${row.id}"
                                data-range-id="${date_range_id}"
                                >
                            View
                        </button>
                        `;
                        }
                    },
                ]
            });

            $.ajax({
                url: "ajax/fetch/fetch_ap_accrual.php",
                method: "POST",
                data: {
                    year_month: yearMonth
                },
                dataType: "json",
                success: function(data) {
                    $('#btnExcel').show();
                    // console.log(data);


                    active_acc = data['active_accrual']
                    date_range_val = data['date_range'];
                    let grouped = {};


                    if (date_range_val) {
                        month_id = date_range_val['id'];
                        $('#btnRunDist').attr('data-id', month_id);
                        $('#btnInsertToOdoo').attr('data-id', month_id);
                        $('#btnModalAccrual').attr('data-id', month_id);

                        if (date_range_val['is_ap_distributed'] == 't') {
                            $('#btnRunDist').hide();
                            $('#btnModalAccrual').hide();


                        } else {
                            $('#btnRunDist').show();
                            $('#btnModalAccrual').show();
                        }
                    }

                    if (active_acc) {

                        // month_id = active_acc[0]['date_range_id'];
                        // $('#btnRunDist').attr('data-id', month_id);

                        active_acc.forEach(row => {
                            grouped[row.id] = row;
                        });

                        PreviewDistTable.clear().rows.add(Object.values(grouped)).draw();
                        // window.fullData = active_acc;
                    }

                }
            });
            $.ajax({
                url: "ajax/fetch/generated_distribution.php",
                method: "POST",
                data: {
                    date_range_id: date_range_id
                },
                dataType: "json",
                success: function(data) {
                    // $('#btnExcel').show();
                    // // console.log(data);
                    // let grouped = {};
                    // // console.log(grouped);
                    // data.forEach(row => {
                    //     grouped[row.journal_entry_id] = row;
                    // });

                    // distTable.clear().rows.add(Object.values(grouped)).draw();
                    window.fullData = data;
                }
            });
        }

        function deleteAccrual(acc_id) {


            swal({
                    title: "Are you sure you want to Delete this?",
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

                        $.ajax({
                            url: "ajax/transaction/delete_accrual.php",
                            method: "POST",
                            data: {
                                acc_id: acc_id
                            },
                            dataType: "json",
                            success: function(data) {



                                swal('Success', 'Deleted Successfully', 'success');
                                init()

                            }
                        });
                    } else {
                        swal("Transaction cancelled", "", "error");
                    }

                });


        } //

        // Added By Ivan Christian Afan 03/20/26
        $('#btnRevert').click(function() {

            let date_range_id = $(this).attr('data-id');

            swal({
                    title: "Are you sure you want to Revert?",
                    text: "Once reverted, the department distribution will be undone.",
                    type: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#DD6B55',
                    confirmButtonText: 'Yes, Revert it!',
                    cancelButtonText: "No, cancel!",
                    closeOnConfirm: false,
                    closeOnCancel: false
                },
                function(isConfirm) {

                    if (isConfirm) {

                        $('#btnRevert').prop('disabled', true);

                        $.ajax({
                            url: "ajax/transaction/revert_distribution.php",
                            method: "POST",
                            data: {
                                date_range_id: date_range_id
                            },
                            dataType: "json",
                            success: function(data) {

                                swal('Success', 'Distribution Reverted Successfully', 'success');
                                $('#previewInsertToOdoo').modal('hide');
                                $('#btnRevert').prop('disabled', false);
                                init();

                            },
                            error: function() {

                                swal('Error', 'Something went wrong', 'error');
                                $('#btnRevert').prop('disabled', false);

                            }
                        });

                    } else {
                        swal("Cancelled", "Revert transaction was cancelled", "error");
                    }

                });

        });

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