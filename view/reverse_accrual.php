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

    #distTable input[type="checkbox"] {
        display: inline-block !important;
        visibility: visible !important;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- <label for="select">SELECT MONTH YEAR</label> -->
<div class="distTable_wrapper">

    <!-- <select id="yearMonthSelect" data-selected="<?= $selectedYM ?>" class="form-control" style="width:250px;">
        <option class="custom-option" value=""></option>
    </select> -->



    <div class="d-flex jc-sb">
        <div>
            <button class="btn btn-success" id="btnRunReversal">Run Reversal</button>



            <!--
                <label for="auto-switch">Old Way Reversal</label>
                <label class="switch">

                    <input name="auto-switch" id='auto_insert_switch' type="checkbox" class="toggle-notification">
                    <span class="slider"></span>
                </label>
           -->

        </div>
        <div class="d-flex">
            <h3>Accrual With Tagged APV: </h3>
            <h3 id="rowCount">
        </div>
        </h3>
    </div>
    <table id="distTable" class="table table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th style="width:40px; text-align:center;">
                    <input type="checkbox" id="selectAll">
                    <label for="selectAll"></label>
                </th>
                <th style="width: 2%;">Accrual ID</th>
                <th>Credit Account</th>

                <th>Total Accrual Value</th>
                <th>Reversal Option</th>
                <th>Dist Category</th>
                <th>Accrual Date Range</th>
                <th>Debit Account</th>
                <!-- <th>Action</th> -->
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
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color:#000000 !important;">&times;</span>
                </button>
            </div>
            <div class="modal-body">



            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="previewReverse" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true" style="color:#000000 !important;">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <button type="button" class="btn btn-secondary" id="backFormTbl" style="display: none; margin-bottom: 15px; margin-top: -15px;">
                    ← Back
                </button>
                <!-- Distribution Containers -->
                <!-- <div id="distributionSection" hidden>
                    <table class="table table-bordered" id="reverse_tbl">
                        <thead>
                            <tr>
                                <th>Account </th>
                                <th>Label</th>
                                <th>Analytic Account</th>
                                <th>Debit</th>
                                <th>Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div> -->

                <div id="distributionSection" hidden="hidden">
                    <div class="btn-groups">
                        <!-- <button id="backBtn" style="font-size: 14pt; margin-right: 15px;"><i class="fa fa-arrow-circle-left"></i></button> -->
                        <button id="revAccBtn" class="active" style="margin-right: 15px;">Reversed Accrual</button>
                        <button id="revWipBtn" style="margin-right: 15px;">Reversed Cogs</button>
                    </div>
                    <div style="padding-top:40px;">
                        <div id="revAccTableContainer">
                            <table id="revAccTable" class="display" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>Account </th>
                                        <th>Label</th>
                                        <th>Analytic Account</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                    </tr>
                                </thead>
                                <tbody id='revAccTable_tbody'></tbody>
                                <tfoot>
                                    <tr id="totalSaRevAcc">
                                        <td colspan="3" style="text-align:right; font-weight:bold">Total:</td>
                                        <td id="totalRevWipDebit" style="font-weight:bold;"></td>
                                        <td id="totalRevWipCredit" style="font-weight:bold;"></td>
                                        <!-- <td id="totalSBUAllocationCell" style="font-weight:bold"></td> -->
                                        <!-- <td></td> -->
                                    </tr>
                                </tfoot>
                            </table>

                        </div>

                        <div id="revWipTableContainer" style="display:none;">
                            <table id="revWipTable" class="display" style="width:100%; color: #000000;">
                                <thead>
                                    <tr>
                                        <th>Reference </th>
                                        <th>Account Name</th>
                                        <th>Department</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>MOS</th>
                                    </tr>
                                </thead>
                                <tbody id='revWipTable_tbody'></tbody>
                                <tfoot id="totalSaRevWip">
                                    <tr>
                                        <td colspan="3" style="text-align:right; font-weight:bold;">Total:</td>
                                        <td id="totalRevWipDebit" style="font-weight:bold;"></td>
                                        <td id="totalRevWipCredit" style="font-weight:bold;"></td>
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
                                <th>Accrual ID</th>
                                <th>Accrual Total </th>
                                <th>Dist Category</th>
                                <th>APV ID</th>
                                <th>APV Total</th>
                                <th>Diff</th>
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

                    <button class="btn btn-primary" id="btnSubmitToOdoo">Submit</button>
                </div>

            </div> <!-- modal body -->
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        var dt = {};
        let distTable;
        let accounts_tagged_global = [];
        let dist_template_global = [];
        let avp_global = [];

        async function init() {
            accounts_tagged_global = await accountList();
            dist_template_global = await distTemplateList();
            avp_global = await fetchAPV();

            initDistTable();
            // fetchAccrual();
            await fetchAccrual();

            // startLoading('body');
            // fetchAccrual()
        }

        window.onload = function() {
            init();
        };

        let revWipTable = $("#revWipTable").DataTable({
            pageLength: 5,
            paging: true,
            searching: false,
            info: false,
            drawCallback: function(settings) {
                showMoreSpan({

                    tableID: "#revWipTable",
                    spanClass: ".dblock",
                    tdClass: ".moreMO",
                    showSpan: "3"
                });
            },
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


        $('#btnExcel').hide();

        $("#yearMonthSelect").on("change", function() {
            let selectedOption = $(this).select2('data')[0];
            let month_id = selectedOption.element.dataset.id;
            let yearMonth = selectedOption.text.trim();

            if (!yearMonth) return;

            const url = new URL(window.location);
            url.searchParams.set('ym', yearMonth);
            url.searchParams.set('id', month_id);
            window.history.replaceState({}, '', url);

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
                // save logic
                // saveRow($row);
                new_credit_to_id = $row.find(".accountSelect").val()
                new_acc_value = $row.find(".distribution-input").val()
                new_template_id = $row.find(".disttemplateSelect").val()
                // console.log(new_template_id, new_acc_value, new_credit_to_id)
                // return;
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


            row.find('.saveEditBtn')
                .text('Edit')
                .removeClass('saveEditBtn')
                .addClass('editBtn');

            $(this).toggleClass('d-none');
            row.find('.editBtn').data('btn', 'edit');
            row.find('.editBtn').find('i').removeClass('fa-save').addClass('fa-pencil');
            // find('.editBtn').toggleClass('d-none');

        });

        // To select lahat ng mga accounts
        $(document).on('change', '#selectAll', function() {
            const checked = this.checked;
            if ($(this).is(':checked')) {
                $('.row-checkbox').prop('checked', checked);
                $('.avpSelect').prop('disabled', false)
                    .trigger('change.select2');
            } else {
                $('.row-checkbox').prop('checked', false);
                $('.avpSelect').val('').prop('disabled', true)
                    .trigger('change.select2');
            }
        }); // END

        $('#distTable').on('change', '.row-checkbox', function() {
            let $row = $(this).closest('tr');
            let $input = $row.find('.avpSelect');
            let $auto = $row.find('.auto_insert_switch');
            let $note = $row.find('.reversal-note')

            let isChecked = $(this).is(':checked');
            let isAuto = $auto.is(':checked');

            $auto.prop('disabled', !isChecked);
            $input.prop('disabled', !isChecked || (isChecked && isAuto) ? true : false);

        })

        $('#distTable').on('change', '.auto_insert_switch', function() {
            let $row = $(this).closest('tr');
            let isChecked = $(this).is(':checked');
            let $inputDiv = $row.find('.avpWrap');
            let $input = $row.find('.avpSelect');
            let $note = $row.find('.reversal-note')
            if (isChecked) {
                $inputDiv.hide();
                $note.show()
            } else {
                $inputDiv.show();
                $note.hide()
            }
            $input.prop('disabled', isChecked ? true : false);

        })

        $("#btnRunReversal").on("click", function() {
            // let yearMonth = $('#yearMonthSelect').val();
            // let month_id = $('#yearMonthSelect').find(':selected').data('id');
            $date_range = $(this).attr('data-id');
            $year_month = $(this).attr('data-yearmonth');
            $('#previewReverse #btnSubmitToOdoo').attr('data-id', $date_range);
            $('#previewReverse #btnSubmitToOdoo').attr('data-yearmonth', $year_month);


            checked_data = getCheckedData();
            if (!checked_data) {
                console.log('may error')
            } else {
                if (checked_data.length === 0) {
                    swal("Please Select Accrual To Reverse", "", "warning");
                } else {
                    $('#previewReverse').modal('show')

                    $('#formTbl').DataTable({
                        data: checked_data, // your array of objects
                        destroy: true, // allows re-initialization if modal opened multiple times
                        autoWidth: false,
                        columns: [{
                                data: 'accrual_id',
                                title: 'Accrual ID'
                            },
                            {
                                data: 'accrual_total',
                                title: 'Accrual Total'
                            },
                            {
                                data: 'dist_template',
                                title: 'Template'
                            },
                            {
                                data: 'apv_id',
                                title: 'APV ID'
                            },
                            {
                                data: 'apv_total',
                                title: 'APV Total'
                            },
                            {
                                data: null, // 'null' because we will compute it
                                title: 'Difference',
                                render: function(data, type, row) {
                                    // Convert to float just in case
                                    var apv = parseFloat(row.apv_total) || 0;
                                    var accrual = parseFloat(row.accrual_total) || 0;

                                    return Math.abs(apv - accrual).toFixed(2); // returns string with 2 decimals
                                }
                            }, {
                                data: 'action',
                                title: 'Action',
                                render: function(data, type, row) {
                                    // optional: you can put buttons here
                                    return `<button class="btn btn-sm viewBtn" data-apv-id= "${row.apv_id}" data-accrual-id= "${row.accrual_id}">View Reversed Entry</button>`;
                                }
                            }
                        ]
                    });
                }
            }





            // swal({
            //         title: "Are you sure you want to generate?",
            //         text: "once submitted, you cannot revert this transaction",
            //         type: "warning",
            //         showCancelButton: true,
            //         confirmButtonColor: '#DD6B55',
            //         confirmButtonText: 'Yes, I am sure!',
            //         cancelButtonText: "No, cancel it!",
            //         closeOnConfirm: false,
            //         closeOnCancel: false
            //     },
            //     function(isConfirm) {

            //         if (isConfirm) {
            //             // generateJournalEntries(month_id, yearMonth);
            //             if ($process == 'odoo') {
            //                 //inser to odoo
            //                 console.log('insert to odoo')

            //             } else {
            //                 $trty = getCheckedData();
            //                 // console.log($trty)
            //                 reverseAccrual($trty)
            //             }

            //             swal.close();

            //         } else {
            //             swal("Saving cancelled", "", "error");
            //         }

            //     });
        })


        $('#previewReverse').on('click', '#backFormTbl', function() {
            $(this).hide()
            $('#divFormTbl').attr('hidden', false)
            $('#distributionSection').attr('hidden', true)

        })

        $('#previewReverse #formTbl').on('click', '.viewBtn', function() {
            $('#divFormTbl').attr('hidden', true)
            $('#distributionSection').attr('hidden', false)
            $('#backFormTbl').show()

            accrual_id = $(this).attr('data-accrual-id');
            apv_id = $(this).attr('data-apv-id');
            // new_way_reversal = $('#auto_insert_switch').prop('checked');
            // console.log(new_way_reversal)
            ////goback
            $.ajax({
                url: "ajax/fetch/fetch_reverse_preview.php",
                method: "POST",
                dataType: "json",
                data: {
                    accrual_id: accrual_id,
                    apv_id: apv_id //,
                    // new_way_reversal: new_way_reversal
                },
                success: function(data) {


                    $('#revAccTable').DataTable({
                        data: data['result'], // your array of objects
                        destroy: true, // allows re-initialization if modal opened multiple times
                        autoWidth: false,
                        columns: [{
                                data: 'account',
                                title: 'Account'
                            },
                            {
                                data: null,
                                title: 'Label',
                                render: function() {
                                    return '';
                                }
                            },
                            {
                                data: 'analytic_account',
                                title: 'Analytic Account'
                            },
                            {
                                data: 'debit',
                                title: 'Debit'
                            },
                            {
                                data: 'credit',
                                title: 'Credit'
                            } //,
                            // {
                            //     data: null, // 'null' because we will compute it
                            //     title: 'Difference',
                            //     render: function(data, type, row) {
                            //         // Convert to float just in case
                            //         var apv = parseFloat(row.apv_total) || 0;
                            //         var accrual = parseFloat(row.accrual_total) || 0;

                            //         return Math.abs(apv - accrual).toFixed(2); // returns string with 2 decimals
                            //     }
                            // }, {
                            //     data: 'action',
                            //     title: 'Action',
                            //     render: function(data, type, row) {
                            //         // optional: you can put buttons here
                            //         return `<button class="btn btn-sm viewBtn" data-apv-id= "${row.apv_id}" data-accrual-id= "${row.accrual_id}">View Reversed Entry</button>`;
                            //     }
                            // }
                        ]
                    });


                    // let totalRevWipDebit = 0,
                    //     totalRevWipCredit = 0;

                    //     // console.log('TOTAL', totalRevWipDebit)
                    // // console.log('CHECK'. data2); 

                    // data2 = data['result_reverse_wip'];
                    // data2.forEach(r => {
                    //     totalRevWipDebit += parseFloat(r.debit || 0);
                    //     totalRevWipCredit += parseFloat(r.credit || 0);
                    // });

                    // revWipTable.clear().rows.add(data2).draw();
                    // // xdataTD = {
                    // //     tableID: "#revWipTable",
                    // //     spanClass: ".dblock",
                    // //     tdClass: ".moreMO",
                    // //     showSpan: "3"
                    // // };
                    // // showMoreSpan(xdataTD);

                    // // Commented by Ivan 03/30/26
                    // // $("#revWipTotalDebit").text(
                    // //     "₱" + totalRevWipDebit.toLocaleString(undefined, {
                    // //         minimumFractionDigits: 2
                    // //     })
                    // // );

                    // // $("#revWipTotalCredit").text(
                    // //     "₱" + totalRevWipCredit.toLocaleString(undefined, {
                    // //         minimumFractionDigits: 2
                    // //     })
                    // // ); // End of comment

                    // $("#totalRevWipDebit").text(
                    //     "₱" + totalRevWipDebit.toLocaleString(undefined, {
                    //         minimumFractionDigits: 2
                    //     })
                    // );

                    // $("#totalRevWipCredit").text(
                    //     "₱" + totalRevWipCredit.toLocaleString(undefined, {
                    //         minimumFractionDigits: 2
                    //     })
                    // );

                    let totalDebit = 0;
                    let totalCredit = 0;

                    // let data2 = data['result_reverse_wip']; 

                    let data2 = data['result'];

                    data2.forEach(r => {
                        totalDebit += parseFloat(r.debit) || 0;
                        totalCredit += parseFloat(r.credit) || 0;
                    });

                    $("#totalRevWipDebit").text(
                        "₱" + totalDebit.toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        })
                    );

                    $("#totalRevWipCredit").text(
                        "₱" + totalCredit.toLocaleString(undefined, {
                            minimumFractionDigits: 2
                        })
                    );

                    // swal(
                    //     "Success",
                    //     "Journal Generated",
                    //     "success"
                    // );
                    // window.location =
                    //     "generated_distribution.php?id=" + month_id + "&ym=" + yearMonth;
                }
            })



        })

        // Edit and cancel for APV
        $(document).on('click', '.avpEdit, .avpCancel', function() {
            const $wrap = $(this).closest('.avpWrap');
            const $select = $wrap.find('.avpSelect');

            if ($(this).hasClass('avpEdit')) {

                $select.data('original', $select.val());

                $select.prop('disabled', false)
                    .trigger('change.select2');

                $wrap.find('.avpEdit').addClass('d-none');
                $wrap.find('.avpCancel').removeClass('d-none');
            } else {
                const original = $select.data('original');
                $select.val(original)
                    .prop('disabled', true)
                    .trigger('change.select2');

                $wrap.find('.avpCancel').addClass('d-none');
                $wrap.find('.avpEdit').removeClass('d-none');
            }
        }); // END


        $('#previewReverse').on('click', '#btnSubmitToOdoo', function() {
            // console.log('mamamam')

            $date_range = $(this).attr('data-id');
            $year_month = $(this).attr('data-yearmonth');

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
                        // generateJournalEntries(month_id, yearMonth);

                        $trty = getCheckedData();
                        // console.log($trty)
                        reverseAccrual($trty, $date_range, $year_month)


                        swal.close();
                        $('#previewReverse').modal('hide')


                    } else {
                        swal("Saving cancelled", "", "error");
                    }

                });
        })

        //buttonbutton
        $('#revAccBtn').on('click', function() {
            const amount = $('.infoBtn').data('amount');
            const fromDate = $('.infoBtn').data('from');
            const toDate = $('.infoBtn').data('to');

            $('#revAccBtn').addClass('active');
            $('#revWipBtn').removeClass('active');

            $('#revWipTableContainer').hide();
            $('#revAccTableContainer').show();

            // loadMoTable(fromDate, toDate, amount);
        });

        $('#revWipBtn').on('click', function() {
            const amount = $('.infoBtn').data('amount');
            const fromDate = $('.infoBtn').data('from');
            const toDate = $('.infoBtn').data('to');

            $('#revWipBtn').addClass('active');
            $('#revAccBtn').removeClass('active');

            $('#revAccTableContainer').hide();

            $('#revWipTableContainer').show();

            // loadMoTable(fromDate, toDate, amount);
        });
        /////////////////////////////////////////////////////FUNCTIONS///////////////////////////////////////////////////////////////////////////////////////
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

        function getCheckedData() {
            let data = [];
            let hasError = false;

            $('.row-checkbox:checked').each(function() {
                let $row = $(this).closest('tr');
                let id = $(this).data('id');
                let selectedValue = $row.find('.avpSelect').val();
                let standardReverse = $row.find('.auto_insert_switch').prop('checked')

                let apvTotal = $row.find('.avpSelect option:selected').attr('data-id');
                let distTemplate = $row.find('.disttemplateSelect option:selected').text();
                let accrualTotal = $row.find('.distribution-input').val();


                if (selectedValue === '' && !standardReverse) {
                    // Show error message
                    alert(`Please select a value for ID ${id}`);
                    hasError = true;
                    return false; // stop the loop
                }

                data.push({
                    accrual_id: id,
                    accrual_total: accrualTotal,
                    dist_template: distTemplate,
                    standardReverse: standardReverse,
                    apv_id: standardReverse ? null : selectedValue,
                    apv_total: standardReverse ? null : apvTotal,
                    action: ''
                });
            });

            if (hasError) return false; // Stop further processing if error

            return data;
        }

        function fetchAccrual() {
            startLoading('#distTable');

            return $.ajax({
                url: "ajax/fetch/fetch_accrual.php",
                method: "POST",
                dataType: "json",
                success: function(data) {

                    $('#btnExcel').show();

                    if (data['date_range']) {
                        $('#btnRunReversal')
                            .attr('data-id', data['date_range']['id'])
                            .attr('data-yearmonth', data['date_range']['year_month']);
                    }

                    if (data['reversal_accrual']) {

                        let grouped = {};

                        data['reversal_accrual'].forEach(row => {
                            grouped[row.id] = row;
                        });

                        let rows = Object.values(grouped);

                        if (!$.fn.DataTable.isDataTable('#distTable')) {
                            initDistTable();
                        }

                        distTable.clear().rows.add(rows).draw(false);

                        distTable.columns.adjust().draw(false);

                        window.fullData = data['reversal_accrual'];
                    }
                },
                complete: function() {
                    stopLoading('#distTable');
                },
                error: function(err) {
                    console.error(err);
                }
            });
        }

        function fetchAPV() {
            return $.ajax({
                url: "ajax/fetch/fetch_accrual_avp.php",
                method: "POST",
                dataType: "json"
            });
        }

        // function insertReversalToOdoo(month_id, yearMonth) {
        //     // console.log($('#auto_insert_switch').prop('checked'));
        //     var autoInsertChecked = $('#auto_insert_switch').prop('checked');

        //     $.ajax({
        //         type: 'POST',
        //         url: 'ajax/transaction/save_distributed_journal.php',
        //         dataType: 'json',
        //         data: {
        //             month_id: month_id
        //         },

        //         success: function(response) {

        //             $.ajax({
        //                 type: 'POST',
        //                 url: 'ajax/transaction/send_journal_email.php',
        //                 dataType: 'json',
        //                 data: {
        //                     yearMonth: yearMonth,
        //                     month_id: month_id,
        //                 },

        //                 success: function(mailRes) {

        //                     if (autoInsertChecked) {
        //                         $.ajax({
        //                             url: "ajax/transaction/insert_reversal_to_odoo.php",
        //                             method: "POST",
        //                             dataType: "json",
        //                             data: {
        //                                 month_id: month_id
        //                             },
        //                             success: function(data) {

        //                                 swal(
        //                                     "Success",
        //                                     "Journal Generated",
        //                                     "success"
        //                                 );
        //                                 // window.location =
        //                                 //     "generated_distribution.php?id=" + month_id + "&ym=" + yearMonth;
        //                             }
        //                         })
        //                     } else {
        //                         swal(
        //                             "Success",
        //                             "Journal Generated",
        //                             "success"
        //                         );
        //                         // window.location =
        //                         //     "generated_distribution.php?id=" + month_id + "&ym=" + yearMonth;
        //                     }





        //                 }
        //             });

        //         }
        //     });
        // }

        function initDistTable() {
            if (distTable) {
                distTable.clear().draw();
                distTable.destroy();
            }
            distTable = $("#distTable").DataTable({
                pageLength: 5,
                rowCallback: function(row, data, index) {
                    // data = row data object
                    if (data.is_reversed !== null && data.is_reversed == 't') {
                        $(row).addClass('row-green').css('background-color', '#4CAF50'); // orange
                    }
                },

                drawCallback: function() {
                    $('.accountSelect').select2({
                        width: 'resolve',
                        placeholder: 'Select Account'
                    });

                    $('.disttemplateSelect').select2({
                        width: 'resolve',
                        placeholder: 'Select Templates'
                    });

                    $('.avpSelect').select2({
                        placeholder: 'Select APV',
                        width: '150px'
                    });

                    let api = this.api();

                    let totalRows = api.rows({
                        page: 'current'
                    }).count();
                    let greenRows = api.rows('.row-green', {
                        page: 'current'
                    }).count();

                    $('#rowCount').text(greenRows + ' / ' + totalRows);

                    // if (totalRows === greenRows && totalRows > 0) {
                    //     $('#btnRunReversal')
                    //         .text('Insert to Odoo')
                    //         .removeClass('btn-success')
                    //         .addClass('btn-warning')
                    //         .attr('data-id', 'odoo');
                    // }


                },
                columns: [{
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {

                            const checkboxId = `selectRow_${row.id}`;
                            if (row.is_reversed == 't') {

                                return '';

                            } else {
                                return `
                            <div style="margin-left: 5px;">
                                <input type="checkbox"
                                    id="${checkboxId}"
                                    class="row-checkbox"
                                    data-id="${row.id}">
                                <label for="${checkboxId}"></label>
                            </div>`;
                            }
                        }
                    },
                    {
                        data: "id"
                    },

                    {
                        data: null,
                        render: function(row) {

                            html = '';
                            html += '<select class="accountSelect" disabled>';
                            html += `<option value="">Select Account</option>`;

                            accounts_tagged_global.forEach(j => {

                                html += `<option value="${j.id}" ${row.credit_to_id== j.id ? 'selected' : ''}>${j.full_name}</option>`;

                            });
                            // console.log(accounts_tagged_global)

                            html += `</select>`;

                            return html;
                        }
                    },
                    {
                        data: null,
                        render: function(row) {
                            return `<input type="text" class="form-control distribution-input" value="${row.total_accrual_value}" style="width:100%;" disabled>`;
                        }
                    },

                    {
                        data: null,
                        render: function(row) {
                            let html = '';

                            if (row.apv) {
                                html = `<p>${row.apv}</p>`;
                            } else if (row.is_reversed == 't' && !row.apv) {
                                html = `      <label for="auto-switch">Standard Reversal</label>
            <label class="switch">
                <input name="auto-switch"  type="checkbox" class="auto_insert_switch" disabled checked>
                <span class="slider"></span>
            </label> `;
                            } else {
                                html = `
                                <div style="display:flex; flex-direction:column">
                                <label for="auto-switch">Standard Reversal</label>
            <label class="switch">
                <input name="auto-switch"  type="checkbox" class="auto_insert_switch" disabled>
                <span class="slider"></span>
            </label>
                               
                                    <div class="avpWrap" style="display:flex; align-items:center; gap:8px;">
                                    
                                        <select class="avpSelect" data-id="${row.id}" disabled>
                                            <option value="">Select APV</option>
                                       
                                `;

                                console.log("row.credit_to_id:", row.credit_to_id);
                                console.log("avp_global:", avp_global);

                                const filtered = Array.isArray(avp_global) ?
                                    avp_global.filter(avp => Number(row.credit_to_id) === Number(avp.account_id)) : [];

                                console.log("Filtered AVPs:", filtered);

                                filtered.forEach(avp => {
                                    html += `<option value="${avp.id}" 
                                        data-id="${avp.amount_untaxed}" 
                                        ${row.apv_id == avp.id ? 'selected' : ''} 
                                        ${avp.max_label ? 'title="' + avp.max_label + '"' : ''}>
                                        ${avp.name}
                                    </option>`;
                                });

                                html += `
                                        </select>
                                        
                                    </div> </div>
                                    <div style="padding:10px; background:#e7f3ff; border-left:4px solid #2196F3; display:none; min-width:250px;" class="reversal-note">
  <strong>ℹ️ Note:</strong> This will create a reversal entry where the debit and credit are switched.
</div>
                                `;
                            }

                            return html;
                        }
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

                            let from = row.from_date ? formatDate(row.from_date) : '';
                            let to = row.to_date ? formatDate(row.to_date) : '';

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
                    //     data: null,
                    //     render: function(row) {
                    //         return `
                    //     <button class="btn btn-primary btn-sm editBtn" data-btn="edit"
                    //             style="background-color: #7C7BAD !important"
                    //             data-id="${row.id}"
                    //             data-range-id="${row.debit_to}"
                    //             >
                    //         <i class="fa fa-pencil"></i>
                    //     </button>
                    //     <button class="btn btn-primary btn-sm cancelBtn d-none" 
                    //             style="background-color: #7C7BAD !important"
                    //             data-id="${row.id}"
                    //             data-range-id="${row.debit_to}"
                    //             >
                    //         <i class="fa fa-window-close"></i>
                    //     </button>
                    //     <button class="btn btn-primary btn-sm deleteBtn" 
                    //             style="background-color: #7C7BAD !important"
                    //             data-id="${row.id}"
                    //             data-range-id="${row.debit_to}"
                    //             >
                    //         <i class="fa fa-trash"></i>
                    //     </button>
                    //     `;
                    //     }
                    // },
                ]
            });
        }

        function reverseAccrual(acc_data, month_id, yearMonth) {
            // console.log($('#auto_insert_switch').prop('checked'));
            // var autoInsertChecked = $('#auto_insert_switch').prop('checked');
            startLoading('body');
            $.ajax({
                type: 'POST',
                url: 'ajax/transaction/reverse_accrual.php',
                // dataType: 'json',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(acc_data),

                //  {
                //     acc_data: acc_data
                // },
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

                            $.ajax({
                                url: "ajax/transaction/insert_reversal_to_odoo.php",
                                method: "POST",
                                dataType: "json",
                                data: {
                                    month_id: month_id
                                },
                                success: function(data) {
                                    init();
                                    stopLoading('body');
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
                    });

                }
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

        function formatDate(dateStr) {
            if (!dateStr) return '';

            let d = new Date(dateStr);

            return new Intl.DateTimeFormat('en-PH', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            }).format(d);
        }

    });
</script>