<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f9f9f9;
    }

    form {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
        display: none;
    }

    input[type=text],
    select {
        padding: 6px;
        margin: 4px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    button {
        padding: 6px 12px;
        background-color: #4CAF50;
        border: none;
        color: white;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #45a049;
    }

    #addDeptBtn {
        background-color: #007BFF;
        margin-bottom: 15px;
    }

    #addDeptBtn:hover {
        background-color: #0056b3;
    }

    .editBtn {
        background-color: #7C7BAD !important;
    }

    .saveBtn {
        background-color: #28a745;
    }

    .cancelBtn {
        background-color: #dc3545 !important;
    }

    #dept_wrapper {
        max-width: 100%;
        margin: 40px auto;
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    #deptTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #deptTable thead {
        background: #f8f9fb;
    }

    #deptTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD;
    }

    #deptTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #deptTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #deptTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #deptTable td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #deptTable td:last-child button:hover {
        background: #0056d2;
    }
</style>

<body>





    <div class="dept_wrapper" style="width:100%; margin:auto;">
        <table id="deptTable" class="display" style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department</th>
                    <th>Department Code</th>
                    <th>Department Group</th>
                    <th>Added On</th>
                    <th>Added By</th>
                    <th>Changed On</th>
                    <th>Changed By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($dept_list) {
                    foreach ($dept_list as $row) {

                        $added_on = !empty($row['added_on']) ? strtoupper(date("F d, Y", strtotime($row['added_on']))) : "";
                        $changed_on = !empty($row['changed_on']) ? strtoupper(date("F d, Y", strtotime($row['changed_on']))) : "";

                        echo "<tr data-id='{$row['id']}'>
                         <td>{$row['id']}</td>
                         <td class='dept_name' data-old-name='{$row['dept_name']}'>{$row['dept_name']}</td>
                         <td class='dept_code' data-old-code='{$row['dept_code']}'>{$row['dept_code']}</td>
                         <td class='dept_group' data-old-group='{$row['dept_group']}'>{$row['dept_group']}</td>

                         <td>{$added_on}</td>
                         <td>{$row['added_by']}</td>
                         <td>{$changed_on}</td>
                        <td>{$row['changed_by']}</td>

                         <td style='display:flex;'>
                             <button class='editBtn'>Edit</button>
                        </td>
                     </tr>";
                    }
                    // print_r($dept_list);
                    // while ($row = pg_fetch_assoc($result)) {

                    //     $added_on = !empty($row['added_on']) ? strtoupper(date("F d, Y", strtotime($row['added_on']))) : "";
                    //     $changed_on = !empty($row['changed_on']) ? strtoupper(date("F d, Y", strtotime($row['changed_on']))) : "";

                    //     echo "<tr data-id='{$row['id']}'>
                    //     <td>{$row['id']}</td>
                    //     <td class='dept_name' data-old-name='{$row['dept_name']}'>{$row['dept_name']}</td>
                    //     <td class='dept_code' data-old-code='{$row['dept_code']}'>{$row['dept_code']}</td>
                    //     <td class='dept_group' data-old-group='{$row['dept_group']}'>{$row['dept_group']}</td>

                    //     <td>{$added_on}</td>
                    //     <td>{$row['added_by']}</td>
                    //     <td>{$changed_on}</td>
                    //     <td>{$row['changed_by']}</td>

                    //     <td style='display:flex;'>
                    //         <button class='editBtn'>Edit</button>
                    //     </td>
                    // </tr>";
                    // } -->
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function() {
            const table = $('#deptTable').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "Search:",
                    lengthMenu: "Show _MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ departments",
                    infoFiltered: "(filtered from _MAX_ total)"
                }
            });



            $('#deptTable').on('click', '.editBtn', function() {
                const row = $(this).closest('tr');

                const deptGroup = row.find('.dept_group');

                const currentGroup = deptGroup.text();
                const deptGroups = <?php echo json_encode(isset($deptGroups) ? $deptGroups : ''); ?>;

                if (!deptGroups) {
                    // alert('No Department Groups Yet')

                    // swal({
                    //     type: "warning",
                    //     title: "No Department Group Setup!",
                    //     text: "Click the Setup Now button to Redirect to Department Group Maintenance",
                    //     timer: 2000,
                    //     showConfirmButton: true
                    // });

                    swal({ 
                            title: "Missing Required Setup!",
                            text: "Click the Setup Now button to Redirect to Department Group Maintenance",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: "#DD6B55",
                            confirmButtonText: "Setup Now",
                            closeOnConfirm: false
                        },
                        function() {
                            window.location.href = "http://devapps.teamglac.com/accounting_v3/dept_group.php";
                            return;
                        });

                    return;
                }
                let options = `<option value="">Select Department Group</option>`;

                deptGroups.forEach(group => {
                    options += `<option value="${group['id']}" ${group['dept_group'] === currentGroup ? 'selected' : ''}>${group['dept_group']}</option>`;
                });

                deptGroup.html(`
            <select class="editGroup" data-old-group ='${currentGroup}'>
                ${options}
            </select>
            `);

                $(this).replaceWith(`
            <button class='saveBtn'>Save</button>
            <button class='cancelBtn' style='margin-left: 10px;'>Cancel</button>
        `);
            });

            $('#deptTable').on('click', '.cancelBtn', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');

                const groupInput = row.find('.editGroup').attr('data-old-group');


                row.find('.dept_group').text(groupInput);
                row.find('.saveBtn, .cancelBtn').remove();
                row.find('td:last').append("<button class='editBtn'>Edit</button>");
            });

            $('#deptTable').on('click', '.saveBtn', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const newGroup = row.find('.editGroup').val();
                const groupInput = row.find('.editGroup option:selected').text();

                $.ajax({
                    url: 'ajax/transaction/update_department.php',
                    type: 'POST',
                    data: {
                        id: id,
                        dept_group: newGroup
                    },

                    success: function(response) {
                        try {
                            const data = JSON.parse(response);

                            if (data.status === 'success') {

                                swal({
                                    type: "success",
                                    title: "Updated!",
                                    text: "Department updated successfully.",
                                    timer: 2000,
                                    showConfirmButton: false
                                });

                                row.find('.dept_group').text(groupInput);

                            } else {

                                swal({
                                    type: "error",
                                    title: "Update Failed",
                                    text: data.message
                                });

                            }
                        } catch {
                            swal({
                                type: "error",
                                title: "Unexpected Response",
                                text: "Unexpected response from server."
                            });
                        }
                    },

                    error: function() {
                        swal({
                            type: "error",
                            title: "Error",
                            text: "Error updating department."
                        });
                    },

                    complete: function() {
                        row.find('.saveBtn, .cancelBtn').remove();
                        row.find('td:last').append("<button class='editBtn'>Edit</button>");
                    }
                });
            });

        });
    </script>

</body>

</html>