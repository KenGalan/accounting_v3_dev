<?php
$db = new Postgresql();
$conn = $db->getConnection();
?>
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

    #users_wrapper {
        max-width: 100%;
        margin: 40px auto;
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    #usersTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #usersTable thead {
        background: #f8f9fb;
    }

    #usersTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD !important;
    }

    #usersTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
        background-color: #ffffff;
    }

    #usersTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #usersTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #usersTable td:last-child button {
        background: #7C7BAD !important;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #usersTable td:last-child button:hover {
        background: #7C7BAD !important;
    }

    .switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 26px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: 0.4s;
        border-radius: 34px;
    }

    .slider:before {
        position: absolute;
        content: "";
        height: 20px;
        width: 20px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: 0.4s;
        border-radius: 50%;
    }

    input:checked+.slider {
        background-color: #7C7BAD;
    }

    input:checked+.slider:before {
        transform: translateX(24px);
    }
</style>

<body>

    <div class="users_wrapper">
        <table id="usersTable" class="display" style="width:100%">
            <thead>
                <tr>
                    <th>Emp #</th>
                    <th>Fullname</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
        </table>
    </div>

    <script>
        $('#usersTable').DataTable({
            ajax: {
                url: 'ajax/fetch/fetch_all_users.php',
                dataSrc: ''
            },
            columns: [{
                    data: 'employee_id_no',
                    title: 'Emp #'
                },
                {
                    data: 'fullname',
                    title: 'Fullname'
                },
                {
                    data: 'email',
                    title: 'Email'
                },
                {
                    data: 'employee_department',
                    title: 'Department'
                },
                {
                    data: null,
                    title: 'Notification',
                    render: function(data, type, row) {
                        const checked = row.is_notification ? 'checked' : '';
                        return `
                    <label class="switch">
                        <input type="checkbox" class="toggle-notification" data-emp="${row.employee_id_no}" data-email="${row.email}" ${checked}>
                        <span class="slider"></span>
                    </label>
                `;
                    }
                },
                {
                    data: null,
                    title: 'Dashboard',
                    render: function(data, type, row) {
                        const checked = row.is_dashboard ? 'checked' : '';
                        return `
                    <label class="switch">
                        <input type="checkbox" class="toggle-dashboard" data-emp="${row.employee_id_no}" data-email="${row.email}" ${checked}>
                        <span class="slider"></span>
                    </label>
                `;
                    }
                },
                {
                    data: null,
                    title: 'Maintenance',
                    render: function(data, type, row) {
                        const checked = row.is_admin ? 'checked' : '';
                        return `
                    <label class="switch">
                        <input type="checkbox" class="toggle-admin" data-emp="${row.employee_id_no}" data-email="${row.email}" ${checked}>
                        <span class="slider"></span>
                    </label>
                `;
                    }
                },
                {
                    data: null,
                    title: 'System Access',
                    render: function(data, type, row) {
                        const checked = row.is_system ? 'checked' : '';
                        return `
                    <label class="switch">
                        <input type="checkbox" class="toggle-system" data-emp="${row.employee_id_no}" data-email="${row.email}" ${checked}>
                        <span class="slider"></span>
                    </label>
                `;
                    }
                }
            ],
            pageLength: 5,
            responsive: true
        });

        // $('#usersTable').on('change', '.toggle-notification', function() {
        //     const empNo = $(this).data('emp');
        //     const email = $(this).data('email');
        //     const isOn = $(this).is(':checked') ? 1 : 0;

        //     $.ajax({
        //         url: 'ajax/transaction/update_notification.php',
        //         type: 'POST',
        //         data: {
        //             emp_no: empNo,
        //             email: email,
        //             is_notification: isOn
        //         },
        //         success: function(response) {
        //             console.log('Notification updated:', response);
        //         } 
        //     });
        // });

        // $('#usersTable').on('change', '.toggle-notification', function() {
        //     const empNo = $(this).data('emp');
        //     const email = $(this).data('email');
        //     const isOn = $(this).is(':checked') ? 1 : 0;

        //     if (!email || email.trim() === '') { 
        //         swal('Ops! Please add email first to receive email notification');

        //         $(this).prop('checked', !$(this).is(':checked'));
        //         return;
        //     } 

        //     $.ajax({
        //         url: 'ajax/transaction/update_notification.php',
        //         type: 'POST',
        //         data: {
        //             emp_no: empNo,
        //             email: email,
        //             is_notification: isOn
        //         },
        //         success: function(response) {
        //             console.log('Notification updated:', response);
        //         },
        //         error: function(xhr, status, error) {
        //             console.error('Update failed:', error);
        //         }
        //     });
        // });

        $('#usersTable').on('change', '.toggle-notification', function() {
            const empNo = $(this).data('emp');  
            const email = $(this).data('email');
            const isOn = $(this).is(':checked') ? 1 : 0;

            // if (empNo == 10947) {
            //     swal("IN YOUR DREAMS KENDRICK !!!!!");
            //     $(this).prop('checked', !$(this).is(':checked'));
            //     return;
            // }

            if (!email || email.trim() === '') {
                swal('Ops! Please add email first to receive email notification');
                $(this).prop('checked', !$(this).is(':checked'));
                return;
            }

            $.ajax({
                url: 'ajax/transaction/update_notification.php',
                type: 'POST',
                data: {
                    emp_no: empNo,
                    email: email,
                    is_notification: isOn
                },
                success: function(response) {
                    console.log('Notification updated:', response);
                }
            });
        });

        $('#usersTable').on('change', '.toggle-dashboard', function() {
            const empNo = $(this).data('emp');
            const email = $(this).data('email');
            const isOn = $(this).is(':checked') ? 1 : 0;

            //  if (empNo == 10947) {
            //      swal("IN YOUR DREAMS KENDRICK !!!!!");
            //      $(this).prop('checked', !$(this).is(':checked'));
            //      return;
            //  }

            $.ajax({
                url: 'ajax/transaction/update_notification.php',
                type: 'POST',
                data: {
                    emp_no: empNo,
                    email: email,
                    is_dashboard: isOn
                },
                success: function(response) {
                    console.log('Dashboard updated:', response);
                }
            });
        });

            $('#usersTable').on('change', '.toggle-admin', function() {
            const empNo = $(this).data('emp');
            const email = $(this).data('email');
            const isOn = $(this).is(':checked') ? 1 : 0;

            //  if (empNo == 10947) {
            //      swal("IN YOUR DREAMS KENDRICK !!!!!");
            //      $(this).prop('checked', !$(this).is(':checked'));
            //      return;
            //  }

            $.ajax({
                url: 'ajax/transaction/update_notification.php',
                type: 'POST',
                data: {
                    emp_no: empNo,
                    email: email,
                    is_admin: isOn
                },
                success: function(response) {
                    console.log('Dashboard updated:', response);
                }
            });
        });

        $('#usersTable').on('change', '.toggle-system', function() {
            const empNo = $(this).data('emp');
            const email = $(this).data('email');
            const isOn = $(this).is(':checked') ? 1 : 0;

            // if (empNo == 10947) {
            //     swal("IN YOUR DREAMS KENDRICK !!!!!");
            //     $(this).prop('checked', !$(this).is(':checked'));
            //     return;
            // }

            $.ajax({
                url: 'ajax/transaction/update_notification.php',
                type: 'POST',
                data: {
                    emp_no: empNo,
                    email: email,
                    is_system: isOn
                },
                success: function(response) {
                    console.log('Dashboard updated:', response);
                }
            });
        });
        $('#usersTable_length').hide();
    </script>
</body>