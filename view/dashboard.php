<style>
    body {
        font-family: Arial, sans-serif;
        padding: 0px 50px;
        background: #e6e4e4ff;
    }

    .card {
        box-shadow: none !important;
        border-radius: 10px;
        padding: 30px;
    }

    #templateTbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }


    #templateTbl thead {
        background: #f8f9fb;
    }

    #templateTbl th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD !important;
    }

    #templateTbl tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
        background-color: #ffffff;
    }

    #templateTbl tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #templateTbl td {
        /* padding: 12px 16px; */
        border-bottom: 1px solid #e5e7eb;
    }


    .count-wrapper {
        display: flex;
        gap: 20px;
        margin-bottom: 35px;
    }

    .count-card {
        flex: 1;
        background: #ffffff;
        border-radius: 14px;
        padding: 22px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        position: relative;
        overflow: hidden;
        transition: transform .2s ease;
    }

    .count-card:hover {
        transform: translateY(-4px);
    }

    .count-title {
        font-size: 13px;
        color: #888;
        letter-spacing: 1px;
    }

    .count-value {
        font-size: 30px;
        font-weight: bold;
        color: #7C7BAD;
        margin-top: 6px;
    }

    .infobox {
        display: inline-block;
        width: 210px;
        height: 66px;
        color: #555;
        background-color: #FFF;
        box-shadow: none;
        border-radius: 0;
        margin: -1px 0 0 -1px;
        padding: 8px 3px 6px 9px;
        border: 1px dotted;
        border-color: #D8D8D8 !important;
        vertical-align: middle;
        text-align: left;
        position: relative;
    }












    @media (min-width: 712px) and (max-width: 1138px),
    (min-width: 820px) and (max-width: 1180px) {
        .card {
            /* overflow: hidden; */
        }
    }







    .swal-wide {
        width: 400px;
        font-size: 1.2rem !important;
    }

    .swal-ribbon::before {
        content: 'Validated';
        position: absolute;
        top: 10px;
        right: -25px;
        color: white;
        font-weight: bold;
        font-size: 12px;
        padding: 10px 20px;
        transform: rotate(45deg);
        transform-origin: center;
        box-shadow: 10px 10px;
        /* z-index: 10; */
        text-align: center;
        width: 100px;
        height: 30px;
        background-color: #3498db;
        clip-path: polygon(30% 0, 70% 0, 100% 100%, 0% 100%);
    }

    /* .icon {
        width: 24px;
        height: 24px;
        stroke-width: 1.5;
        fill: none;
    } */

    .icon {
        position: absolute;
        right: 18px;
        top: 18px;
        width: 55px;
        height: 55px;
        fill: none;
        /* opacity: .55; */
    }

    .icon svg {
        width: 100%;
        height: 100%;
        /* fill: currentColor;
        stroke: currentColor; */
    }

    /* Colors */
    .icon-open {
        stroke: #3e73ca;
        /* blue */
    }

    .icon-pending {
        stroke: #f59e0b;
        /* orange */
    }

    .icon-complete {
        stroke: #10b981;
        /* green */
    }

    .icon-incomplete {
        stroke: #f59e0b;
        /* orange */
    }

    /* 
    .doc-warning {
        stroke: #f59e0b !important;
    
        fill: none !important;
        stroke-width: 2 !important;
    }

    .doc-open {
        stroke: #3b82f6 !important;
       
        fill: none !important;
        stroke-width: 2 !important;
    }

    .doc-pending-reversal {
        stroke: #f59e0b !important;
      
        fill: none !important;
        stroke-width: 2 !important;
    } */

    /* .progress-wrapper {
        width: 120px;
        height: 8px;
        background: #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        display: inline-block;
        vertical-align: middle;
    }

    .progress-bar {
        height: 100%;
        background: #4CAF50;
        border-radius: 6px;
        transition: width 0.3s ease;
    }

    .progress-text {
        margin-left: 8px;
        font-size: 12px;
        color: #333;
    } */

    .circle-progress {
        width: 70px;
        height: 70px;
        display: inline-block;
    }

    .circle-progress svg {
        width: 100%;
        height: 100%;
    }


    .circle-progress .bg {
        fill: none;
        stroke: #e0e0e0;
        stroke-width: 3;
    }

    .circle-progress .progress {
        fill: none;
        stroke-width: 3;
        stroke-linecap: round;
        transform: rotate(-90deg);
        transform-origin: center;
    }

    .circle-progress text {
        font-size: 7px;
        fill: #333;
        font-weight: bold;
    }

    .fit-col {
        width: 1%;
        white-space: nowrap;
        text-align: center;
    }

    .row-danger {
        background: #ff5b5b !important;
    }
</style>



<div class="count-wrapper">

    <div class="count-card" id="open-card">
        <div class="icon">
            <svg viewBox="0 0 24 24" width="48" height="48">

                <!-- green circle background -->
                <circle cx="12" cy="12" r="11" fill="#8BC34A" />

                <!-- document -->
                <path d="M9 6h5l3 3v9H9z" fill="white" />

                <!-- warning dot (incomplete indicator) -->
                <circle cx="15.5" cy="14.5" r="1.2" fill="#8BC34A" />
                <path d="M15.5 11.5v2" stroke="#8BC34A" stroke-width="1.2" stroke-linecap="round" />

            </svg>
        </div>
        <div class="count-title">INCOMPLETE TEMPLATE SETUP</div>
        <div class="count-value" id="countIncSetup">0</div>

    </div>
    <div class="count-card" id="open-card">
        <div class="icon">
            <svg viewBox="0 0 24 24" width="48" height="48">

                <!-- green circle background (active) -->
                <circle cx="12" cy="12" r="11" fill="#4CAF50" />

                <!-- document -->
                <path d="M9 6h5l3 3v9H9z" fill="white" />

                <!-- active pulse indicator -->
                <path d="M7.5 13
         h2l1-2l1.2 4l1-2h2.8" fill="none" stroke="#2E7D32" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />

            </svg>
        </div>
        <div class="count-title">ACTIVE ACCRUALS</div>
        <div class="count-value" id="countActiveAcc">0</div>

    </div>

    <div class="count-card" id="progress-card">
        <div class="icon">

            <svg viewBox="0 0 24 24" width="48" height="48">

                <!-- yellow circle background (pending) -->
                <circle cx="12" cy="12" r="11" fill="#FFC107" />

                <!-- document -->
                <path d="M9 6h5l3 3v9H9z" fill="white" />

                <!-- reversal arrow (top-right overlay) -->
                <path d="M14.5 9
         a3 3 0 1 0 2 2.8" fill="none" stroke="#D32F2F" stroke-width="1.3" stroke-linecap="round" />

                <!-- arrow head -->
                <path d="M16.8 11.8l0.2-1.8l-1.7 0.3" fill="none" stroke="#D32F2F" stroke-width="1.3" stroke-linecap="round" stroke-linejoin="round" />

            </svg>
        </div>
        <div class="count-title">PENDING REVERSALS</div>
        <div class="count-value" id="countPendingRev">0</div>
    </div>

    <div class="count-card" id="validation-card">
        <div class="icon icon-validate">
            <svg viewBox="0 0 24 24" width="48" height="48">

                <!-- gray circle background (neutral / no activity) -->
                <circle cx="12" cy="12" r="11" fill="#9E9E9E" />

                <!-- document -->
                <path d="M9 6h5l3 3v9H9z" fill="white" />

                <!-- minus indicator (no transaction) -->
                <line x1="10.5" y1="14.5" x2="15.5" y2="14.5" stroke="#616161" stroke-width="1.6" stroke-linecap="round" />

            </svg>
        </div>

        <div class="count-title">UNPROCESSED TEMPLATES</div>
        <div class="count-value" id="countUnprocessed">0</div>
    </div>
</div>

<div class="card">
    <button id="resetFilter" class="cs-btn" style="margin-bottom: 15px; display: none">RESET</button>

    <table id="templateTbl" class="table">
        <thead>
            <th style="text-align: center">TEMPLATE</th>
            <th style="text-align: center">DEBIT ACCOUNTS</th>
            <th style="text-align: center">DISTRIBUTION PERCENTAGE</th>
            <th style="text-align: center">ACTIVE ENTRIES</th>
            <!-- <th>ACTIONS</th> -->
            <!-- <th>REASON</th>
            <th>CREATED BY</th>
            <th>RESPONSIBLE</th>
            <th>CREATED DATE</th>
            <th>STATUS</th> -->
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(function() {
        table = $('#templateTbl').DataTable({
            ordering: false,
            "pageLength": 5,
            // drawCallback: function() {
            //     showMoreSpan({
            //         tableID: "#templateTbl",
            //         tdClass: ".tags",
            //         spanClass: ".tag",
            //         showSpan: 1
            //     });
            // },
            createdRow: function(row, data, dataIndex) {
                $('td:last', row).removeClass('cell-danger cell-warning cell-success');

                let $lastCell = $('td:last', row);
                // example condition
                if (data.active_accruals == 0 && data.ap_dist == 0) {
                    $($lastCell).addClass('row-danger');
                }


            },
            columnDefs: [{

                    targets: 2,
                    className: "fit-col dt-center"
                },
                {
                    targets: '_all',
                    className: 'dt-center'
                }
            ],
            columns: [{
                    data: null,
                    render: function(row) {
                        return `<strong> ${row.acc_category} </strong>`;
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
                // Commented by Ivan - 05/04/26
                // {
                //     data: null,
                //     render: function(row) { 

                //         return `
                // <div class="circle-progress">
                //     <svg viewBox="0 0 36 36">

                //         <path class="bg"
                //             d="M18 2.5a15.5 15.5 0 1 1 0 31a15.5 15.5 0 1 1 0 -31" />

                //         <path class="progress"
                //             stroke="${getProgressColor(row.acc_categ_percentage)}"
                //             stroke-dasharray="${row.acc_categ_percentage}, 100"
                //             d="M18 2.5a15.5 15.5 0 1 1 0 31a15.5 15.5 0 1 1 0 -31" />

                //         <text x="18" y="20.5" text-anchor="middle">
                //             ${row.acc_categ_percentage}%
                //         </text>

                //     </svg>
                // </div>
                // `;
                //     } 
                // },
                {
                    data: null,
                    render: function(row) {
                        return `
                            <div class="circle-progress progress-click"
                                data-category-id="${row.act_id}"
                                style="cursor:pointer;">
                                <svg viewBox="0 0 36 36">
                                    <path class="bg"
                                        d="M18 2.5a15.5 15.5 0 1 1 0 31a15.5 15.5 0 1 1 0 -31" />

                                    <path class="progress"
                                        stroke="${getProgressColor(row.acc_categ_percentage)}"
                                        stroke-dasharray="${row.acc_categ_percentage}, 100"
                                        d="M18 2.5a15.5 15.5 0 1 1 0 31a15.5 15.5 0 1 1 0 -31" />

                                    <text x="18" y="20.5" text-anchor="middle">
                                        ${row.acc_categ_percentage}%
                                    </text>
                                </svg>
                            </div>
                        `;
                    }
                },
                {
                    data: null,
                    render: function(row) {
                        let active_entries = `
                                        <div style="display:flex; flex-direction:column; gap:5px;">
                                    `;
                        if (row.active_accruals != 0) {
                            active_entries += `
                                            <span class="dblock" style="
                                                padding:0.3rem 1rem;
                                                background:#4cbb51;
                                        
                                            ">
                                               ACCRUAL
                                            </span>
                                        `;
                        }

                        if (row.ap_dist != 0) {
                            active_entries += `
                                            <span class="dblock" style="
                                                padding:0.3rem 1rem;
                                                background:#4cbb51;
                                            ">
                                               AP DISTRIBUTION
                                            </span>
                                        `;
                        }
                        active_entries += "</div>";
                        return active_entries;
                    }
                },



                // {
                //     data: null,
                //     render: function(row) {
                //         // Added by Ivan Christian Afan
                //         let disabled = row.is_dept_distributed === 't' ? 'disabled' : '';
                //         return `
                //         <button class="btn btn-primary btn-sm editBtn" data-btn="edit"
                //                 ${disabled}
                //                 style="background-color: #7C7BAD !important"
                //                 data-id="${row.id}"
                //                 data-range-id="${row.debit_to}"
                //                 >
                //             <i class="fa fa-pencil"></i>
                //             Add Transaction
                //         </button>
                //         <button class="btn btn-primary btn-sm cancelBtn" 
                //                 style="background-color: #7C7BAD !important"
                //                 data-id="${row.id}"
                //                 data-range-id="${row.debit_to}"
                //                 >
                //             <i class="fa fa-window-close"></i>
                //          View Setup
                //         </button>
                //         `;
                //     }
                // },
            ]
        });

        getTemplates();


        // Added by Ivan - 05/04/26
        $(document).on('click', '.progress-click', function() {
            let categoryId = $(this).data('category-id');

            $.ajax({
                url: 'ajax/fetch/fetch_distribution_percentage.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    category_id: categoryId
                },
                success: function(res) {

                    if (!res || res.length === 0) {
                        swal({
                            title: 'No Data',
                            text: 'No distribution found.',
                            type: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Go to Setup',
                            cancelButtonText: 'Cancel'
                        }, function(isConfirm) {
                            if (isConfirm) {
                                window.location.href = "distribution_cost_maintenance.php?category_id=" + categoryId;
                            }
                        });
                        return;
                    }

                    let html = `
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Distribution %</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                    res.forEach(function(row) {
                        html += `
                                <tr>
                                    <td>${row.dept_name || ''}</td>
                                    <td>${row.distribution_percentage || 0}%</td>
                                </tr>
                            `;
                    });

                    html += `</tbody></table>`;

                    swal({
                        title: 'Distribution',
                        text: html,
                        html: true,
                        showCancelButton: true,
                        confirmButtonText: 'Go to Setup',
                        cancelButtonText: 'Close'
                    }, function(isConfirm) {
                        if (isConfirm) {
                            window.location.href = "distribution_cost_maintenance.php"
                        }
                    });
                }
            });
        }); // END

        // $(document).on('keyup', function(e) {
        //     if (e.ctrlKey && e.which === 77) {
        //         // log(e);
        //         showLogInModal();
        //     }
        // });

        $(document).on('click', '#open-card', function() {
            getTemplates('open');
            nextPrev('open');
            table.rows.add($('#templateTbl tbody tr')).draw();

            $('#resetFilter').show();
        })

        $(document).on('click', '#progress-card', function() {
            getTemplates('progress');
            nextPrev('progress');
            table.rows.add($('#templateTbl tbody tr')).draw();

            $('#resetFilter').show();
        })

        $(document).on('click', '#validation-card', function() {
            getTemplates('validation');
            nextPrev('validation');
            table.rows.add($('#templateTbl tbody tr')).draw();

            $('#resetFilter').show();
        })

        $('#resetFilter').on('click', function() {
            getTemplates();
            nextPrev();
            table.rows.add($('#templateTbl tbody tr')).draw();

            $('#resetFilter').hide();
        })

        // VALIDATION
        $('#templateTbl').on('click', '.validateBtn', function() {
            stopAuto();

            const minor = $(this).attr('minor-attr');
            const engg = $(this).attr('engg-attr');
            const engg_validated = $(this).attr('engg-validated-attr');
            const ticket_no = $(this).closest('tr').find('td:eq(0)').text();

            htmlForm =
                `<p style="font-weight: bold">Ticket: ${ticket_no}</p>
                <div style="display:flex;flex-direction:column;text-align:left">
                    <input type="password" id="badgeNo" style="height: 30px" placeholder="Enter BadgeNo">
                    <br>
                    <textarea id="swal_remarks"
                        style="min-width:100%;max-width:100%;min-height:80px;max-height:150px;padding:8px;border:1px solid #dad9d9ff !important;"
                        placeholder="Leave it blank if no remarks"></textarea>
                </div>`;

            Swal.fire({
                title: 'Validation Form',
                html: htmlForm,
                showCancelButton: true,
                showDenyButton: true,
                confirmButtonText: 'Pass',
                confirmButtonColor: '#398439',
                denyButtonText: 'Fail',
                cancelButtonText: 'Cancel',
                customClass: {
                    popup: 'swal-wide'
                },
                willClose: () => {
                    nextPrev();
                }
            }).then((result) => {
                let action;
                if (result.isConfirmed) {
                    action = 'pass';
                } else if (result.isDenied) {
                    action = 'fail';
                } else {
                    return;
                }

                const ticket_id = $(this).attr('ticket-id-attr');
                const job_id = $(this).attr('job-id-attr');
                const remarks = $('#swal_remarks').val();
                const badgeNo = $('#badgeNo').val() || '';

                if (!badgeNo) {
                    Swal.fire('Error!', 'Badge no is required to validate', 'error')
                }


                if (minor === 't') {
                    // minor repair only
                    $.ajax({
                        url: 'ajax/validation_operator.php',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            action,
                            job_id,
                            ticket_id,
                            remarks,
                            badgeNo
                        },
                        success: function(res) {
                            // log(res)
                            if (res.flag) {
                                Swal.fire('Success!', res.msg, 'success');
                                getTemplates();
                            } else {
                                Swal.fire('Error!', res.msg, 'error');
                            }
                        }
                    })
                } else if (engg === 't') {
                    // Engg
                    $.ajax({
                        url: 'ajax/validation_engineer.php',
                        type: 'post',
                        dataType: 'json',
                        data: {
                            action,
                            job_id,
                            ticket_id,
                            remarks,
                            badgeNo,
                            engg_validated
                        },
                        success: function(res) {
                            // log(res)
                            if (res.flag) {
                                Swal.fire('Success!', res.msg, 'success');
                                getTemplates();
                            } else {
                                Swal.fire('Error!', res.msg, 'error');
                            }
                        }
                    })
                } else {
                    // Swal.fire('Maintenance', 'Function is under development', 'info');
                    // return;

                    $.post('ajax/validation_norm.php', {
                        action,
                        job_id,
                        ticket_id,
                        remarks,
                        badgeNo
                    }, function(res) {
                        // log(res)
                        if (res.flag) {
                            Swal.fire('Success!', res.msg, 'success');
                            getTemplates();
                        } else {
                            Swal.fire('Error!', res.msg, 'error');
                        }
                    }, 'json')
                }


            });

            if (engg_validated) {
                $('.swal2-popup').addClass('swal-ribbon');
            }
        })


    }) // END DOCUMENT LOAD

    function getTemplates(type = "") {
        let grouped = {};
        $.ajax({
            url: 'ajax/fetch/fetch_all_templates.php',
            type: 'post',
            data: {
                type
            },
            success: function(data) {
                // $('#templateTbl tbody').html(html);


                $('#countActiveAcc').text(data['active_acc'] || 0);
                $('#countIncSetup').text(data['inc_setup'] || 0);
                $('#countUnprocessed').text(data['unprocessed_temp'] || 0);
                $('#countPendingRev').text(data['pending_rev'] || 0);






                temp_details = data['temp_details']
                // table.clear();

                // temp_details.forEach(row => {
                //     grouped[row.act_id] = row;
                // });
                table.clear().rows.add(temp_details).draw();
                // table.clear().rows.add(Object.values(grouped)).draw();
                // $('#templateTbl tbody').html(html);
                // table.rows.add($('#templateTbl tbody tr')).draw(false);
            }
        })
    }

    // function loadCounts() {
    //     $.ajax({
    //         url: 'ajax/get_dashboard_data.php',
    //         type: 'GET',
    //         dataType: 'json',
    //         success: function(res) {

    //             $('#countOpen').text(res.open_count || 0);
    //             $('#countProgress').text(res.progress_count || 0);
    //             $('#countForValidation').text(res.for_validation_count || 0);

    //         }
    //     });
    // } // END

    function getProgressColor(percent) {
        if (percent <= 30) return "#F44336"; // red
        if (percent <= 99) return "#FFC107"; // yellow
        return "#4CAF50"; // green
    }
</script>
<script src="public/app/sweetalert2@11.js"></script>