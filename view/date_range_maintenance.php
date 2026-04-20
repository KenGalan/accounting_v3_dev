   <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
   <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #e6e4e4ff;
        }

        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        h2 {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            margin-right: 10px;
        }

        input[type=date] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            padding: 6px 12px;
            background-color: #7C7BAD !important;
            border: none;
            color: white;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #7C7BAD !important;
        }

        button.active {
            background-color: #7C7BAD !important;
            box-shadow: inset 0 0 3px rgba(0, 0, 0, 0.3);
            color: #ffffff !important;
            font-weight: 550;
            transform: scale(1.1);
        }

        table.dataTable thead th {
            background-color: #7C7BAD !important;
            color: white;
        }

        #moTableContainer,
        #deptTableContainer {
            margin-top: 20px;
        }

        .backBtn {
            background-color: #6c757d;
            margin-right: 10px;
            margin-bottom: 15px;
        }

        .btn-groups {
            margin-bottom: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-primary {
            background-color: #7C7BAD !important;
            color: #000000;
            font-size: 14pt;
        }

        #dateRangeTbl_wrapper {
            max-width: 100%;
            margin: 40px auto;
            background: #ffffff;
            padding: 20px 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            font-family: "Inter", "Segoe UI", Roboto, sans-serif;
        } 

        #dateRangeTbl {
            width: 100%;
            border-collapse: collapse;
            font-size: 16px;
            color: #333;
        }

        #dateRangeTbl thead {
            background: #f8f9fb;
        }

        #dateRangeTbl th {
            text-align: left;
            padding: 14px 16px;
            font-weight: 600;
            color: #ffffff;
            border-bottom: 2px solid #e5e7eb;
            background-color: #7C7BAD !important;
        }

        #dateRangeTbl tbody tr {
            transition: background 0.2s ease, transform 0.1s ease;
            background-color: #ffffff;
        }

        #dateRangeTbl tbody tr:nth-child(even) {
            background: #fafafa;
        }

        #dateRangeTbl td {
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        #dateRangeTbl td:last-child button {
            background: #7C7BAD !important;
            color: white;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        #dateRangeTbl td:last-child button:hover {
            background: #7C7BAD !important;
        }
        #dateRangeTbl .saveRowBtn td:last-child button{
            background-color: darkgreen !important;
        }
    </style>
    <div id="filterSection" style="margin-bottom: 20px;">
        <label>From:</label>
        <input type="date" id="fromDate">
        <label>To:</label>
        <input type="date" id="toDate">
        <button id="filterBtn">save</button>
        <button id="cancelFilterBtn" class="btn-warning">reset</button>
    </div>
    <div class="dateRangeTbl_wrapper">
        <table id="dateRangeTbl" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Year Month</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Added By</th>
                    <th>Changed By</th>
                    <th>Changed On</th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>
    <script>
        $(document).ready(function() {

            let table = $("#dateRangeTbl").DataTable({
                ajax: {
                    url: "ajax/fetch/fetch_range.php",
                    type: "GET",
                    dataSrc: ""
                },
                pageLength: 5,

                rowCallback: function(row, data) {
                    if (data.is_dept_distributed === "t") {
                        $(row).css("background-color", "lightgreen");
                    }
                },

                columns: [
                    { data: "year_month" },
                    { data: "start_date", render: d => `<span class="startText">${d}</span>` },
                    { data: "end_date", render: d => `<span class="endText">${d}</span>` },
                    { data: "added_by" },
                    { data: "change_by" },
                    {
                            data: "changed_on",
                            render: function (data) {
                                if (!data) return "";

                                let dateObj = new Date(data);

                                let formatted = dateObj.toLocaleDateString("en-US", {
                                    year: "numeric",
                                    month: "long",
                                    day: "2-digit"
                                });

                                return formatted.toUpperCase(); 
                            }
                        },
                    {
                        data: null,
                        render: row => {
                            const hideButtons = row.is_dept_distributed === "t";


                            return `
                                ${!hideButtons ? `
                                    <button class="btn btn-primary btn-sm editRowBtn" 
                                            data-id="${row.id}" 
                                            data-isdistributed="${row.is_dept_distributed}">
                                        Edit
                                    </button>

                                    <button class="btn btn-danger btn-sm delRowBtn" 
                                            data-id="${row.id}" 
                                            data-isdistributed="${row.is_dept_distributed}">
                                        Delete
                                    </button>
                                ` : ''}

                                <button class="btn btn-success btn-sm saveRowBtn" 
                                        style="display:none;" 
                                        data-id="${row.id}">
                                    Save
                                </button>

                                <button class="btn btn-secondary btn-sm cancelRowBtn" 
                                        style="display:none;">
                                    Cancel
                                </button>
                            `;
                        }
                    }
                ]
            });

            table.on('draw', function () {
                if (!$('#dateRangeNote').length) {
                    $('#dateRangeTbl_wrapper').prepend(`
                        <div id="dateRangeNote" style="margin-bottom: -27px; font-size:14px;">
                            <span style="margin-left: 5px;">Legend :</span>
                            <i class="fa fa-circle" style="color: lightgreen;"></i>
                            <span style="margin-left: 5px;">Date range has already used in distribution.</span>
                        </div>
                    `);
                }
            }); 

            $("#dateRangeTbl").on("click", ".editRowBtn", function() {

                const isDistributed = $(this).data('isdistributed');

                if (isDistributed === true || isDistributed === "t" || isDistributed == 1) {
                    alert('Unable to edit: This month is already used in distribution.');
                    return;
                }

                let tr = $(this).closest("tr"); 
                let rowData = table.row(tr).data();

                tr.find(".startText").html(`<input type="date" class="editStart" value="${rowData.start_date}">`);
                tr.find(".endText").html(`<input type="date" class="editEnd" value="${rowData.end_date}">`);

                tr.find(".saveRowBtn, .cancelRowBtn").show();
                tr.find(".delRowBtn").hide();
                $(this).hide(); 
            });

            $("#dateRangeTbl").on("click", ".cancelRowBtn", function() {
                let tr = $(this).closest("tr");
                let rowData = table.row(tr).data();

                tr.find(".startText").text(rowData.start_date);
                tr.find(".endText").text(rowData.end_date);

                tr.find(".saveRowBtn, .cancelRowBtn").hide();
                tr.find(".editRowBtn, .delRowBtn").show();
            });

            $("#dateRangeTbl").on("click", ".saveRowBtn", function() {
                let tr = $(this).closest("tr");
                let rowData = table.row(tr).data();

                let id = $(this).data("id");
                let newStart = tr.find(".editStart").val();
                let newEnd = tr.find(".editEnd").val();

                if (!newStart || !newEnd) {
                    alert("Please enter both dates.");
                    return; 
                }

                if (newStart > newEnd) {
                    alert("Start date cannot be after end date.");
                    return;
                }

                let oldYearMonth = rowData.year_month;

                $.ajax({
                    url: "ajax/transaction/update_date_range.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        id: id,
                        old_year_month: oldYearMonth,
                        start_date: newStart,
                        end_date: newEnd
                    },
                    success: function(response) {

                            if (response.status === "silos") {
                            swal({
                                type: "warning",
                                title: "Uy ano na ginagawa mo?",
                                text: response.message,
                                confirmButtonColor: "#d33"
                            });
                            return;
                        }

                        alert(response.message);
                        if (response.status === "success") {
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function() {
                        alert("Error updating date range.");
                    }
                });
            });

        $("#dateRangeTbl").on("click", ".delRowBtn", function () {

            let id = $(this).data("id");
            const isDistributed = $(this).data("isdistributed");

            if (isDistributed === true || isDistributed === "t" || isDistributed == 1) {
                swal({
                    type: "warning",
                    title: "Unable to delete",
                    text: "This month is already used in distribution.",
                    confirmButtonColor: "#d33"
                });
                return;
            }

            swal({
                title: "Are you sure?",
                text: "This record will be permanently deleted.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it"
            }, function (isConfirm) {

                if (isConfirm) {

                    $.ajax({
                        url: "ajax/transaction/delete_date_range.php",
                        type: "POST",
                        dataType: "json",
                        data: { id: id },

                        success: function (response) {

                            swal({
                                type: response.status === "success" ? "success" : "error",
                                title: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            if (response.status === "success") {
                                table.ajax.reload(null, false);
                            }
                        },

                        error: function () {
                            swal({
                                type: "error",
                                title: "Error",
                                text: "Error deleting record."
                            });
                        }
                    });

                }

            });

        });

            $("#cancelFilterBtn").on("click", function() {
                $("#fromDate").val('');
                $("#toDate").val('');
            });

            $("#filterBtn").on("click", function () {

                let startDate = $("#fromDate").val();
                let endDate = $("#toDate").val();

                if (!startDate || !endDate) {
                    swal({
                        type: "warning",
                        title: "Missing Dates",
                        text: "Please select both From and To dates.",
                        confirmButtonColor: "#d33"
                    });
                    return;
                }

                if (startDate > endDate) {
                    swal({
                        type: "error",
                        title: "Invalid Date Range",
                        text: "Start date cannot be greater than End date.",
                        confirmButtonColor: "#d33"
                    });
                    return;
                }

                let yearMonth = startDate.substring(0, 7);

                $.ajax({
                    url: "ajax/transaction/save_date_range.php",
                    type: "POST",
                    dataType: "json",
                    data: {
                        year_month: yearMonth,
                        start_date: startDate,
                        end_date: endDate
                    },

                    success: function (response) {

                        if (response.status === "silos") {
                            swal({
                                type: "warning",
                                title: "Uy ano na ginagawa mo?",
                                text: response.message,
                                confirmButtonColor: "#d33"
                            });
                            return;
                        }

                        swal({
                            type: response.status === "success" ? "success" : "error",
                            title: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });

                        if (response.status === "success") {
                            table.ajax.reload(null, false);
                            $("#fromDate").val('');
                            $("#toDate").val('');
                        }
                    },

                    error: function () {
                        swal({
                            type: "error",
                            title: "Error",
                            text: "Error saving date range."
                        });
                    }
                });
            });

            $('#dateRangeTbl_length').hide();
        });
    </script>