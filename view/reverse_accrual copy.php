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
            <button class="btn btn-success" id="btnRunReversal" data-id="normal">Run Reversal</button>



            <?php if ($_SESSION['ppc']['emp_no'] == '10929' || $_SESSION['ppc']['emp_no'] == '8228'  || $_SESSION['ppc']['emp_no'] == '10768' || $_SESSION['ppc']['emp_no'] == '10947') { ?>
                <label for="auto-switch">AUTO INSERT TO ODOO</label>
                <label class="switch">

                    <input name="auto-switch" id='auto-switch' type="checkbox" class="toggle-notification">
                    <span class="slider"></span>
                </label>
            <?php } ?>

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
                <th>AVP</th>
                <th>Dist Category</th>
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
            initDistTable();
            fetchAPV();
            fetchAccrual();

            // fetchAccrual()
        }

        init();

        $("#yearMonthSelect").select2({
            placeholder: "Select Year-Month",
            allowClear: true
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
            //    if($this.prop('checked'))
            let $input = $(this).closest('tr').find('.avpSelect');

            // Enable or disable based on checkbox

            if ($(this).is(':checked')) {
                $input.prop('disabled', !$(this).is(':checked'));
            } else {

                const original = $input.data('original');
                $input.val(original)
                    .prop('disabled', true)
                    .trigger('change.select2');
            }


        })

        $("#btnRunReversal").on("click", function() {
            // let yearMonth = $('#yearMonthSelect').val();
            // let month_id = $('#yearMonthSelect').find(':selected').data('id');
            $process = $(this).attr('data-id');



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
                        if ($process == 'odoo') {
                            //inser to odoo
                            console.log('insert to odoo')

                        } else {
                            $trty = getCheckedData();
                            // console.log($trty)
                            reverseAccrual($trty)
                        }

                        swal.close();

                    } else {
                        swal("Saving cancelled", "", "error");
                    }

                });
        })

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


        // function loadYearMonth() {

        //     const selectedYM = $("#yearMonthSelect").data("selected");
        //     // console.log(selectedYM);

        //     $.ajax({
        //         url: "ajax/fetch/get_year_month.php",
        //         method: "GET",
        //         dataType: "json",
        //         success: function(data) {

        //             $("#yearMonthSelect").empty().append(`<option value=""></option>`);

        //             data.forEach(item => {
        //                 $("#yearMonthSelect").append(`
        //             <option value="${item.year_month}" data-id="${item.date_range_id}">
        //                 ${item.year_month}
        //             </option>
        //         `);
        //             });

        //             if (selectedYM) {
        //                 $("#yearMonthSelect").val(selectedYM).trigger("change");
        //             }
        //         }
        //     });
        // }
        function getCheckedData() {
            let data = [];
            let hasError = false;

            $('.row-checkbox:checked').each(function() {
                let $row = $(this).closest('tr');
                let id = $(this).data('id');
                let selectedValue = $row.find('.avpSelect').val();

                if (selectedValue === '') {
                    // Show error message
                    alert(`Please select a value for ID ${id}`);
                    hasError = true;
                    return false; // stop the loop
                }

                data.push({
                    accrual_id: id,
                    apv_id: selectedValue
                });
            });

            if (hasError) return false; // Stop further processing if error

            return data;
        }

        function fetchAccrual() {
            $.ajax({
                url: "ajax/fetch/fetch_accrual.php",
                method: "POST",
                dataType: "json",
                success: function(data) {
                    $('#btnExcel').show();

                    if (data['reversal_accrual']) {
                        rev_acc = data['reversal_accrual']

                        let grouped = {};
                        rev_acc.forEach(row => {
                            grouped[row.id] = row;
                        });

                        distTable.clear()
                            .rows.add(Object.values(grouped))
                            .draw();

                        window.fullData = rev_acc;
                    }

                }
            });
        }

        function fetchAPV() {
            $.ajax({
                url: "ajax/fetch/fetch_accrual_avp.php",
                method: "POST",
                dataType: "json",
                success: function(data) {
                    avp_global = data;

                    $('.avpSelect').each(function() {
                        const rowId = $(this).data('id');
                        const row = window.fullData?.find(r => r.id == rowId);

                        let options = `<option value="">Select APV</option>`;

                        avp_global.forEach(avp => {
                            options += `
                                <option value="${avp.id}"
                                    ${row?.avp_move_id == avp.id ? 'selected' : ''}>
                                    ${avp.name}
                                </option>`;
                        });

                        $(this).html(options).trigger('change.select2');
                    });
                }
            });
        }




        function insertReversalToOdoo(month_id, yearMonth) {
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
                                    url: "ajax/transaction/insert_reversal_to_odoo.php",
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





                        }
                    });

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
                rowCallback: function(row, data, index) {
                    // data = row data object
                    if (data.apv !== null && data.apv !== '') {
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

                    if (totalRows === greenRows && totalRows > 0) {
                        $('#btnRunReversal')
                            .text('Insert to Odoo')
                            .removeClass('btn-success')
                            .addClass('btn-warning')
                            .attr('data-id', 'odoo');
                    }


                },
                columns: [{
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {

                            const checkboxId = `selectRow_${row.id}`;
                            if (row.apv_id) {

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
                            } else {


                                html = `
                                        <div class="avpWrap" style="display:flex; align-items:center; gap:8px;">
                                            <select class="avpSelect" data-id="${row.id}" disabled>
                                                <option value="">Select APV</option>
                                    `;

                                avp_global.forEach(avp => {
                                    html += `
                                            <option value="${avp.id}"
                                                ${row.apv_id == avp.id ? 'selected' : ''}>
                                                ${avp.name}
                                            </option>`;
                                });

                                html += `
                                            </select>

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




        // Per row checkbox
        // $(document).on('change', '.rowCheck', function() {
        //     const total = $('.selectRow').length;
        //     const checked = $('.selectRow:checked').length;

        //     $('#selectAll').prop('checked', total === checked);
        // }); // END

        // $(document).on('click', '.cancelBtn', function () {
        //     const $row = $(this).closest('tr');

        //     $row.find('.avpSelect')
        //         .prop('disabled', true)
        //         .trigger('change.select2');

        //     $(this).addClass('d-none');
        // });


        function reverseAccrual(acc_data) {
            // console.log($('#auto_insert_switch').prop('checked'));
            var autoInsertChecked = $('#auto_insert_switch').prop('checked');

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
                    init();

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
                                    url: "ajax/transaction/insert_reversal_to_odoo.php",
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





                        }
                    });

                }
            });
        }

    });
</script>