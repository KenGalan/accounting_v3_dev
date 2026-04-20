<style>
    body {
        font-family: Arial, Helvetica, sans-serif;
        background-color: #FAF7F3;
    }

    table.dataTable {
        width: 100%;
        border-collapse: collapse !important;
        background: #fff;
    }

    th {
        background-color: #7C7BAD !important;
        color: #ffffff !important;
        text-align: center !important;
        padding: 10px;
        font-weight: bold !important;
        font-size: 12pt;
    }

    td {
        vertical-align: middle !important;
        padding: 8px 10px;
        border: 1px solid #3b3b3b !important;
        font-size: 14px;
    }

    table.dataTable thead th {
        border: 1px solid #3b3b3b !important;
    }

    .search_input {
        margin-bottom: 15px;
        width: 35%;
    }

    .btn-primary {
        background-color: #7C7BAD !important;
        border-color: #5a7eb0 !important;
        color: #ffffff !important;
    }

    .btn-primary-customize {
        background-color: #7C7BAD !important;
        color: #ffffff !important;
    }

    /* Custom pagination styling */
    #pagination {
        display: flex;
        justify-content: flex-end;
        gap: 5px;
        margin-top: 10px;
    }

    #pagination button {
        min-width: 40px;
        padding: 5px 10px;
        border: 1px solid #7C7BAD;
        background-color: #fff;
        cursor: pointer;
        border-radius: 4px;
    }

    #pagination button.active {
        background-color: #4a6ea9;
        color: #fff;
        border-color: #4a6ea9;
        font-weight: bold;
    }
</style>

<table id="arTable" class="display" style="width:100%" class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>Vendor</th>
            <th>Reference</th>
            <th>Invoice Date</th>
            <th>Due Date</th>
            <th>Amount</th>
            <th>Not Due</th>
            <th>1-30 Days</th>
            <th>31-60 Days</th>
            <th>61-90 Days</th>
            <th>91-120 Days</th>
            <th>121-150 Days</th>
            <th>150 Days</th>
        </tr>
    </thead>
</table>
<script>
    let today = new Date().toISOString().split('T')[0];
    console.log(today);     

    $(document).ready(function () {
        $('#arTable').DataTable({
            ajax: {
                url: 'ajax/fetch/fetch_ar.php',
                type: 'GET',
                dataSrc: 'data'
            },
            pageLength: 10,

            dom: 'Bfrtip',
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'EXPORT',
                    title: 'AR_Report',
                    className: 'btn btn-primary export-csv-btn',
                    bom: true, 
                    charset: 'utf-8',
                    fieldSeparator: ',',
                    filename: 'AR_Report',
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],

            columns: [
                { data: 'vendor' },
                { data: 'reference' },

                { 
                    data: 'invoice_date',
                    render: function (d) {
                        return d ? new Date(d).toLocaleDateString('en-US') : "";
                    }
                },

                { 
                    data: 'invoice_date_due',
                    render: function (d) {
                        return d ? new Date(d).toLocaleDateString('en-US') : "";
                    }
                },

                { 
                    data: 'amount',
                    render: function (d) {
                        return parseFloat(d).toLocaleString('en-US', {
                            style: 'currency',
                            currency: 'PHP'
                        });
                    }
                },

                { data: 'not_due',
                    render: moneyRender
                },
                { data: 'days_1_30',
                    render: moneyRender
                },
                { data: 'days_31_60',
                    render: moneyRender
                },
                { data: 'days_61_90',
                    render: moneyRender
                },
                { data: 'days_91_120',
                    render: moneyRender
                },
                { data: 'days_121_150',
                    render: moneyRender
                },
                { data: 'days_over_150',
                    render: moneyRender
                }
            ]
        });

        function moneyRender(d) {
            return parseFloat(d).toLocaleString('en-US', {
                style: 'currency',
                currency: 'PHP'
            });
        }

});

</script>