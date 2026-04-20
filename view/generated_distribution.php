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

    #distributionEntriesTbl_length {
        display: none;
    }

    #myModalOdooEntries .modal-content {
        position: relative;
        overflow: hidden;
    }

    .status-ribbon {
        position: absolute;
        top: 22px;
        right: -42px;
        width: 170px;
        text-align: center;
        transform: rotate(45deg);
        color: #fff;
        font-weight: 700;
        font-size: 18px;
        letter-spacing: 1px;
        padding: 8px 0;
        z-index: 20;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .18);
    }

    .status-ribbon.draft {
        background: #206f94;
    }

    .status-ribbon.posted {
        background: #17774f;
    }

    .status-ribbon.cancelled {
        background: #c0392b;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- <label for="select">SELECT MONTH YEAR</label> -->
<div class="distTable_wrapper">

    <select id="yearMonthSelect" data-selected="<?= $selectedYM ?>" class="form-control" style="width:250px;">
        <option class="custom-option" value=""></option>
    </select>

    <button class="btn btn-success" id="btnExcel">Download Excel</button>

    <table id="distTable" class="table table-bordered table-striped" style="width:100%">
        <thead>
            <tr>
                <th>Journal ID</th>
                <th>Journal</th>
                <th></th>
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


                <!-- Distribution Containers -->
                <div id="distributionSection">
                    <div class="btn-groups">
                        <!-- <button id="backBtn" style="font-size: 14pt; margin-right: 15px;"><i class="fa fa-arrow-circle-left"></i></button> -->
                        <button id="deptBtn" class="active" style="margin-right: 15px;">Department Distribution</button>
                        <button id="sbuBtn" style="margin-right: 15px;">SBU Distribution</button>
                        <button id="moBtn" style="margin-right: 15px;">MO Distribution</button>
                        <button id="wipBtn" style="margin-right: 15px;">Wip Entries</button>
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

            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModalOdooEntries" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin-left: 8px; margin-top: 5px; float: left; background-color: transparent !important; color:#000000 !important;">
                <span aria-hidden="true" style="color:#000000 !important;">&times;</span>
            </button>
            <div class="status-ribbon">LOADING..</div>
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel" style="letter-spacing: 1px;">
                    JOURNAL ENTRIES <br />
                    <span id="yearMonthDisplay" style="font-size: 8pt; color: #727070; letter-spacing: 1px;"></span>
                </h5>
            </div>
            <div class="modal-body">

                <div id="distributionSection">
                    <div class="btn-groups">
                        <!-- <button id="backBtn" style="font-size: 14pt; margin-right: 15px;"><i class="fa fa-arrow-circle-left"></i></button> -->
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
                    </div>
                    <div style="padding-top:40px;">
                        <div id="distributionEntriesContainer" style="display:none;">
                            <table id="distributionEntriesTbl" class="table table-bordered table-striped" style="width:100%; color: #000000;">
                                <thead>
                                    <tr>
                                        <th>Journal Name</th>
                                        <th>Reference</th>
                                        <th>Account</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Analytic Account</th>
                                        <!-- <th>Status</th> -->
                                        <th>Date</th>
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
                                    </tr>
                                </tfoot>
                            </table>

                            <div id="moResultsContainer" style="display:none;">
                                <button id="backToEntriesBtn" class="btn btn-secondary btn-sm">
                                    Back
                                </button>
                                <select id="selectionFilter" multiple style="width: 250px;">
                                    <option value="customer_name">Customer</option>
                                    <option value="so_no">SO</option>
                                    <option value="item_name">Item</option>
                                </select>

                                <table id="moResults" class="table table-bordered table-striped" style="width:100%; color: #000000;">
                                    <thead>
                                        <tr>
                                            <th>MO #</th>
                                            <th>Percentage</th>
                                            <th>Value</th>
                                            <th>Customer Name</th>
                                            <th>SO</th>
                                            <th>Item</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" style="text-align:right; font-weight:bold;">Total:</td>
                                            <td id="moTotal" style="font-weight:bold;"></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
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

            if (dt.wipTable) {
                dt.wipTable.clear().draw();
                dt.wipTable.destroy();
            }

            if (dt.distributionEntriesTbl) {
                dt.distributionEntriesTbl.clear().draw();
                dt.distributionEntriesTbl.destroy();
            } // Can you add a ribon in the modal like this

            dt.distributionEntriesTbl = $('#distributionEntriesTbl').DataTable({
                destroy: true,
                pageLength: 5,
                processing: true,
                searching: true,
                columns: [{
                        data: 'entry_group',
                        visible: false
                    },
                    {
                        data: 'move_name',
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

                    api.rows({
                        search: 'applied'
                    }).every(function() {
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
            // $('#moTable_length').hide();
            // dt.moModalTable = $('#moModalTable').DataTable({
            //     destroy: true,
            //     pageLength: 5,
            //     dom: 'Bfrtip',
            //     buttons: [{
            //         extend: 'csvHtml5',
            //         text: 'Export',
            //         className: 'btn btn-success btn-design',
            //         title: `MO_Distribution_${today}`
            //     }],
            //     columnDefs: [{
            //         targets: 6,
            //         render: function(data, type, row) {

            //             // RAW value for sorting, filtering, type === 'sort' or 'filter'
            //             if (type !== 'display') {
            //                 return data; // return unchanged 5-decimal raw number
            //             }
            //             // FORMATTED VALUE ONLY ON DISPLAY
            //             return new Intl.NumberFormat('en-PH', {
            //                 style: 'currency',
            //                 currency: 'PHP',
            //                 minimumFractionDigits: 5,
            //                 maximumFractionDigits: 5
            //             }).format(data);
            //         }
            //     }]
            // });

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

        }

        $("#yearMonthSelect").select2({
            placeholder: "Select Year-Month",
            allowClear: true
        });

        loadYearMonth();

        let distTable = $("#distTable").DataTable({
            pageLength: 5,
            columns: [{
                    data: "journal_entry_id"
                },
                {
                    data: "journal"
                },
                {
                    data: null,
                    render: function(row) {
                        return `
                        <button class="btn btn-primary btn-sm viewBtn"
                                style="background-color: #7C7BAD !important"
                                data-id="${row.journal_entry_id}"
                                data-range-id="${row.date_range_id}"
                                >
                            View
                        </button>
                        <button class="btn btn-primary btn-sm odooEntriesBtn"
                                style="background-color: #23234b !important"
                                data-id="${row.journal_entry_id}"
                                >
                            Odoo Entries
                        </button>
                        `;
                    }
                }
            ]
        });

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

        // let distributionEntriesTbl = $("#distributionEntriesTbl").DataTable({
        //     pageLength: 5,
        //     paging: true,
        //     searching: false,
        //     info: false,
        // });

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

        if ($.fn.DataTable.isDataTable('#moResults')) {
            dt.moResultsTbl.destroy();
        }

        dt.moResultsTbl = $('#moResults').DataTable({
            destroy: true,
            pageLength: 10,
            searching: true,
            lengthChange: false,
            info: true,
            paging: true,
            ordering: false,
            columnDefs: [{
                targets: [3, 4, 5],
                visible: false
            }],
            footerCallback: function(row, data, start, end, display) {
                let api = this.api();
                let total = 0;

                api.rows({
                    search: 'applied'
                }).every(function() {
                    let rowData = this.data();

                    let rawValue = rowData[2];

                    if (rawValue) {
                        rawValue = String(rawValue).replace(/[₱,\s]/g, '');
                        total += parseFloat(rawValue) || 0;
                    }
                });

                $('#moTotal').html(
                    '₱ ' + total.toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })
                );
            }
        });

        // ADDED BY IVAN - 04/10/26
        function loadOdooEntries(accruals, defaultGroup = 'distribution') {
            startLoading('#myModalOdooEntries .modal-content');
            $('#distributionEntriesContainer').hide();
            $('#moResultsContainer').hide();
            $('#moResults tbody').empty();
            $('#distributionEntriesTbl_wrapper').show();
            // let dateRangeId = 3;

            let accrualId = accruals;
            // console.log(accruals)
            $.ajax({
                    url: 'ajax/fetch/get_odoo_entries.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        accrual_id: accrualId
                    }
                })
                .done(function(res) {
                    // console.log('AJAX RESPONSE:', res);

                    if (res.status === 'success') {

                        if ($.fn.DataTable.isDataTable('#distributionEntriesTbl')) {
                            dt.distributionEntriesTbl.clear();
                            dt.distributionEntriesTbl.rows.add(res.data).draw();
                        }

                        // updateEntryGroupStatuses(res.data);
                        filterEntryGroup(defaultGroup);

                        if (res.data.length > 0) {
                            updateModalRibbon(res.data[0].status);
                        } else {
                            updateModalRibbon('');
                        }

                        $('#distributionEntriesContainer').show();

                    } else {
                        alert(res.message || 'Failed to fetch entries.');
                    }

                })
                .fail(function(xhr, status, error) {
                    console.log(error);
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


        // function loadMoList(aml_id) {
        //         $.ajax({
        //             url: 'ajax/fetch/get_mo_list_by_aml.php',
        //             type: 'POST',
        //             dataType: 'json',
        //             data: { aml_id: aml_id },
        //             success: function(res) {
        //                 console.log(res);
        //             },
        //             error: function(xhr, status, error) {
        //                 console.log(error);
        //                 alert('Error fetching MO list.');
        //             }
        //         });
        //     }

        $('#selectionFilter').select2({
            placeholder: "Select Filters",
            closeOnSelect: false,
            templateResult: function(data) {
                if (!data.id) return data.text;

                return $(`
                        <span>
                            <input type="checkbox" style="margin-right:8px;" />
                            ${data.text}
                        </span>
                    `);
            }
        });

        function applyFilters() {
            let filters = $('#selectionFilter').val() || [];

            let showCustomer = filters.includes('customer_name');
            let showSO = filters.includes('so_no');
            let showItem = filters.includes('item_name');

            dt.moResultsTbl.column(3).visible(showCustomer);
            dt.moResultsTbl.column(4).visible(showSO);
            dt.moResultsTbl.column(5).visible(showItem);
        }

        $('#selectionFilter').on('change', applyFilters);

        function filterEntryGroup(group) {
            $('#deBtn, #ceBtn, #rdeBtn, #crBtn').removeClass('active');

            if (group === 'distribution') {
                $('#deBtn').addClass('active');
            } else if (group === 'cogs') {
                $('#ceBtn').addClass('active');
            } else if (group === 'reverse_distribution') {
                $('#rdeBtn').addClass('active');
            } else if (group === 'cogs_reverse') {
                $('#crBtn').addClass('active');
            }

            dt.distributionEntriesTbl.column(0).search('^' + group + '$', true, false).draw();

            let filteredData = dt.distributionEntriesTbl.rows({
                search: 'applied'
            }).data().toArray();

            if (filteredData.length > 0) {
                updateModalRibbon(filteredData[0].status);
            } else {
                updateModalRibbon('');
            }
        }

        $('#deBtn').on('click', function() {
            filterEntryGroup('distribution');
            $('#moResultsContainer').hide();
            $('#moResults tbody').empty();
            $('#distributionEntriesTbl_wrapper').show();
        });

        $('#ceBtn').on('click', function() {
            filterEntryGroup('cogs');
            $('#moResultsContainer').hide();
            $('#moResults tbody').empty();
            $('#distributionEntriesTbl_wrapper').show();
        });

        $('#rdeBtn').on('click', function() {
            filterEntryGroup('reverse_distribution');
            $('#moResultsContainer').hide();
            $('#moResults tbody').empty();
            $('#distributionEntriesTbl_wrapper').show();
        });

        $('#crBtn').on('click', function() {
            filterEntryGroup('cogs_reverse');
            $('#moResultsContainer').hide();
            $('#moResults tbody').empty();
            $('#distributionEntriesTbl_wrapper').show();
        });

        $('#backToEntriesBtn').on('click', function() {
            $('#moResultsContainer').hide();
            $('#moResults tbody').empty();
            $('#distributionEntriesTbl_wrapper').show();
        });

        function updateModalRibbon(status) {
            let ribbon = $('.status-ribbon');

            ribbon
                .removeClass('paid draft posted cancelled')
                .text('');

            if (!status) {
                ribbon.hide();
                return;
            }

            let s = String(status).toLowerCase();

            if (s === 'draft') {
                ribbon.addClass('draft').text('DRAFT').show();
            } else if (s === 'posted') {
                ribbon.addClass('posted').text('POSTED').show();
            } else if (s === 'cancel') {
                ribbon.addClass('cancelled').text('CANCELLED').show();
            } else {
                ribbon.text(status.toUpperCase()).show();
            }
        }

        function getStatusBadge(status) {
            let bg = '#206f94';
            let text = 'Draft';

            if (!status) {
                return {
                    text: '',
                    style: ''
                };
            }

            let s = String(status).toLowerCase();

            if (s === 'posted') {
                bg = '#17774f';
                text = 'Posted';
            } else if (s === 'draft') {
                bg = '#206f94';
                text = 'Draft';
            } else if (s === 'cancel' || s === 'cancelled') {
                bg = '#c0392b';
                text = 'Cancelled';
            } else {
                bg = '#6b7280';
                text = status;
            }

            return {
                text: text,
                style: `
                    background-color:${bg};
                    color:#fff;
                    letter-spacing:1px;
                    padding:5px 7px;
                    font-size:8pt;
                    border-radius:5px;
                    margin-left:8px;
                    display:inline-block;
                `
            };
        }

        $('#myModalOdooEntries').on('shown.bs.modal', function() {

            let accrual_id = $(this).data("id");
            startLoading('#myModalOdooEntries .modal-content');
            $('.btn-groups').hide();
            $('.modal-title').hide();
            $('.status-ribbon').hide();
            //    let aml_id = $(thsis).data("aml-id");
            // let amlId = $(this).data('aml-id');
            // console.log(date_range_id)

            // console.log("Modal date_range_id:", date_range_id);

            $('#distributionEntriesContainer').show();

            reinitializeMoTbl();
            loadOdooEntries(accrual_id);
            // loadMoList(aml_id);

            setTimeout(function() {
                filterEntryGroup('distribution');
            }, 200);

        }); // END

        $('#distributionEntriesTbl tbody').on('click', '.viewMosBtn', function() {
            let rowData = dt.distributionEntriesTbl.row($(this).closest('tr')).data();
            let moList = rowData.mo_list || [];

            dt.moResultsTbl.clear();

            if (moList.length > 0) {
                moList.forEach(function(item) {
                    dt.moResultsTbl.row.add([
                        item.monum || '',
                        item.percent && !isNaN(item.percent) ?
                        Number(item.percent).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) + '%' :
                        '',
                        item.value && !isNaN(item.value) ?
                        '₱ ' + Number(item.value).toLocaleString('en-PH', {
                            minimumFractionDigits: 2,
                            maximumFractionDigits: 2
                        }) :
                        '',
                        item.customer_name || '',
                        item.so_no || '',
                        item.item_device || ''
                    ]);
                });
            } else {
                dt.moResultsTbl.row.add([
                    '<div class="text-center">No MO records found.</div>',
                    '',
                    '',
                    '',
                    '',
                    ''
                ]);
            }

            dt.moResultsTbl.draw();
            applyFilters();

            $('#distributionEntriesTbl_wrapper').hide();
            $('#moResultsContainer').show();
        });

        function loadYearMonth() {

            const selectedYM = $("#yearMonthSelect").data("selected");
            // console.log(selectedYM);

            $.ajax({
                url: "ajax/fetch/get_year_month.php",
                method: "POST",
                dataType: "json",
                data: {
                    is_dept_distributed: 'true'
                },
                success: function(data) {

                    $("#yearMonthSelect").empty().append(`<option value=""></option>`);

                    data.forEach(item => {
                        $("#yearMonthSelect").append(`
                            <option 
                                value="${item.year_month}" 
                                data-id="${item.date_range_id}"
                                data-start="${item.start_date}"
                                data-end="${item.end_date}"
                            >
                                ${item.year_month}
                            </option>
                        `);
                    });

                    if (selectedYM) {
                        $("#yearMonthSelect").val(selectedYM).trigger("change");
                    } else {
                        let firstText = $("#yearMonthSelect option:selected").text();
                        $('#yearMonthDisplay').text(firstText);
                    }
                }
            });
        }
        $('#btnExcel').hide();
        $("#yearMonthSelect").on("change", function() {
            let selectedOption = $(this).select2('data')[0];
            // let selectedText = $('#yearMonthSelect option:selected').text();
            let month_id = selectedOption.element.dataset.id;
            let yearMonth = selectedOption.text.trim();
            let selected = $(this).find(':selected');
            let start = selected.data('start');
            let end = selected.data('end');
            let display = `${formatDate(start)} - ${formatDate(end)}`;

            if (!yearMonth) return;

            const url = new URL(window.location);
            url.searchParams.set('ym', yearMonth);
            url.searchParams.set('id', month_id);
            window.history.replaceState({}, '', url);
            $('#yearMonthDisplay').text(display);

            $.ajax({
                url: "ajax/fetch/generated_distribution.php",
                method: "POST",
                data: {
                    year_month: yearMonth
                },
                dataType: "json",
                success: function(data) {
                    $('#btnExcel').show();
                    // console.log(data);
                    let grouped = {};
                    // console.log(grouped);
                    data.forEach(row => {
                        grouped[row.journal_entry_id] = row;
                    });

                    distTable.clear().rows.add(Object.values(grouped)).draw();
                    window.fullData = data;
                }
            });
        });

        $('#btnExcel').on('click', function() {
            let month_id = $('#yearMonthSelect').find(':selected').attr('data-id');

            if (!month_id) {
                swal(
                    "No Date Selected",
                    "Please select date to export report.",
                    "warning"
                )
                return;
            }

            window.location = "export_journal_batch_csv.php?id=" + month_id;
        });

        function formatDate(val) {
            if (!val) return '';

            let d = new Date(val);

            return new Intl.DateTimeFormat('en-PH', {
                year: 'numeric',
                month: 'short',
                day: '2-digit'
            }).format(d);
        }

        // $('#btnInsertToOdoo').on('click', function() {
        //     // console.log('yeahs')

        //     monthId = $('#yearMonthSelect option:selected').data('id');
        //     swal({
        //             title: "Are you sure you want to insert to Odoo?",
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


        //                 $.ajax({
        //                     url: "ajax/transaction/insert_journal_entries_to_odoo.php",
        //                     method: "POST",
        //                     data: {
        //                         month_id: monthId
        //                     },
        //                     dataType: "json",
        //                     success: function(data) {}
        //                 })
        //                 swal.close();
        //             } else {
        //                 swal("Saving cancelled", "", "error");
        //             }

        //         });


        // });

        $("#distTable tbody").on("click", ".odooEntriesBtn", function() {

            let accrual_id = $(this).data("id");

            console.log("Clicked accrual_id:", accrual_id);

            $("#myModalOdooEntries")
                .data("id", accrual_id)
                .modal("show");

        });

        $("#distTable tbody").on("click", ".viewBtn", function() {
            reinitializeMoTbl();
            let id = $(this).data("id");
            let date_range_id = $(this).data("range-id");
            $.ajax({
                url: "ajax/fetch/fetch_mo_dist.php",
                method: "POST",
                data: {
                    accrual_id: id,
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

            $.ajax({
                url: "ajax/fetch/fetch_wip_entries.php",
                method: "POST",
                data: {
                    journal_entries_id: id
                    // date_range_id: date_range_id
                },
                dataType: "json",
                success: function(data) {
                    // console.log(data)
                    // var rows = [];
                    // // Loop through each item
                    // for (var i = 0; i < data.length; i++) {
                    //     var wip_dist = data[i];
                    //     // console.log("Item " + i + ":", item);

                    //     // Example: append data to a table

                    //     rows.push([
                    //         wip_dist['reference'],
                    //         wip_dist['account_name'],
                    //         wip_dist['departmnt'],
                    //         wip_dist['debit'],
                    //         wip_dist['credit'],
                    //         wip_dist['mos']
                    //     ]);
                    // }
                    // dt.wipTable.rows.add(rows).draw();

                    // data.forEach(function(row) {

                    //     if (row.mos) {
                    //         let mosArr = row.mos.split(","); // split by comma

                    //         row.mos = mosArr.map(function(mo) {
                    //             return  `<span class='dblock' style='background-color: #ddd; padding: 0.3rem 1rem;'>${mo.trim()}</span>`;


                    //         }).join(" ");
                    //     }

                    // });

                    // wipTable.clear().rows.add(data).draw();
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
            $("#myModal").modal("show");

        });

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
</script>