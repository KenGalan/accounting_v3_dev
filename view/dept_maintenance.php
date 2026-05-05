<?php
$db = new Postgresql();
$conn = $db->getConnection();

$query = "SELECT id, dept_group, added_on, added_by, changed_on, changed_by, active
          FROM M_ACC_DEPARTMENT_GROUPS
          WHERE ACTIVE
          ORDER BY id ASC";
$result = pg_query($conn, $query);
?>
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

    /* button:hover {
        background-color: #45a049;
    } */

    #addDeptBtn {
        background-color: #007BFF;
        margin-bottom: 15px;
    }

    /* #addDeptBtn:hover {
        background-color: #0056b3;
    } */

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

    /* #deptTable td:last-child button:hover {
        background: #0056d2;
    } */

    #deptGroupTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #deptGroupTable thead {
        background: #f8f9fb;
    }

    #deptGroupTable th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD;
    }

    #deptGroupTable tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #deptGroupTable tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #deptGroupTable td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #deptGroupTable td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    /* #deptGroupTable td:last-child button:hover {
        background: #0056d2;
    } */
</style>

<body>
<div class="container-btn" style="padding:20px;">
    <button id="viewDeptBtn" class="btn active">Department</button>
    <button id="viewDeptGrpBtn" class="btn" style="margin-left:15px;">Department Group</button>

    <p id="deptTitle" style="float:right; border:4px solid #7C7BAD; padding:5px; font-size: 12pt;"><i class="material-icons" style="color:#750728;">warning</i> You must tag a department group in the department to use it in the distribution.</p>
    <p id="depGrouptTitle" style="float:right; border:4px solid #7C7BAD; padding:5px; font-size: 12pt;  display:none;"><i class="material-icons" style="color:#750728;">warning</i> Add a department group to tag it in the department.</p>
</div>
<hr/>

<div id="departmentSection">
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
</div>

<div id="departmentGroupSection" style="display:none;">
    <button id="addDeptBtn">Add Department Group</button>

    <form id="addDeptGroupForm">
        <label>Department Group:</label>
        <input type="text" id="dept_group" name="dept_group" required>
        <button type="submit">Save</button>
    </form>

    <div class="dept_wrapper" style="width:100%; margin:auto;">
        <table id="deptGroupTable" class="display" style="width:100%;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Department Group</th>
                    <!-- <th>Department Code</th> -->
                    <th>Added On</th>
                    <th>Added By</th>
                    <th>Changed On</th>
                    <th>Changed By</th>
                    <!-- <th>Active</th> -->
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result) {
                    while ($row = pg_fetch_assoc($result)) {
                        $added_on = !empty($row['added_on']) ? strtoupper(date("F d, Y", strtotime($row['added_on']))) : "";
                        $changed_on = !empty($row['changed_on']) ? strtoupper(date("F d, Y", strtotime($row['changed_on']))) : "";
                        echo "<tr data-id='{$row['id']}'>
                        <td>{$row['id']}</td>
                        <td class='dept_group'>{$row['dept_group']}</td>
                        <td>$added_on</td>
                        <td>{$row['added_by']}</td>
                        <td>$changed_on</td>
                        <td>{$row['changed_by']}</td>
                        <td style='display:flex;'>
                            <button class='editBtn'>Edit</button>
                        </td>
                    </tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>


    <script>
        $(document).ready(function () {
        let deptGroups = <?php echo json_encode(isset($deptGroups) ? $deptGroups : []); ?>;
    
    const deptTable = $('#deptTable').DataTable({
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ departments",
            infoFiltered: "(filtered from _MAX_ total)"
        }
    });

    const deptGroupTable = $('#deptGroupTable').DataTable({
        pageLength: 10,
        order: [[0, 'asc']],
        language: {
            search: "Search :",
            lengthMenu: "Show _MENU_ records per page",
            info: "Showing _START_ to _END_ of _TOTAL_ Group departments",
            infoFiltered: "(filtered from _MAX_ total)"
        }
    });


    $('#viewDeptBtn').on('click', function () {
        $('#departmentSection').show();
        $('#departmentGroupSection').hide();
        $('#depGrouptTitle').hide();
        $('#deptTitle').show();

        $('#viewDeptBtn').addClass('active');
        $('#viewDeptGrpBtn').removeClass('active');

        deptTable.columns.adjust().draw(false);
    });

    $('#viewDeptGrpBtn').on('click', function () {
        $('#departmentSection').hide();
        $('#departmentGroupSection').show();
        $('#deptTitle').hide();
        $('#depGrouptTitle').show();

        $('#viewDeptGrpBtn').addClass('active');
        $('#viewDeptBtn').removeClass('active');

        deptGroupTable.columns.adjust().draw(false);
    });


    $('#deptTable').on('click', '.editBtn', function () {
        const row = $(this).closest('tr');
        const deptGroup = row.find('.dept_group');
        const currentGroup = deptGroup.text().trim();

        if (!deptGroups || deptGroups.length === 0) {
            swal({
                title: "Missing Required Setup!",
                text: "Please add Department Group first.",
                type: "warning",
                showCancelButton: true,
                confirmButtonText: "Setup Now",
                closeOnConfirm: true
            }, function () {
                $('#viewDeptGrpBtn').trigger('click');
            });

            return;
        }

        let options = `<option value="">Select Department Group</option>`;

        deptGroups.forEach(group => {
            options += `
                <option value="${group.id}" ${group.dept_group === currentGroup ? 'selected' : ''}>
                    ${group.dept_group}
                </option>
            `;
        });

        deptGroup.html(`
            <select class="editGroup" data-old-group="${currentGroup}">
                ${options}
            </select>
        `);

        $(this).replaceWith(`
            <button class="saveBtn">Save</button>
            <button class="cancelBtn" style="margin-left:10px;">Cancel</button>
        `);
    });

    $('#deptTable').on('click', '.cancelBtn', function () {
        const row = $(this).closest('tr');
        const oldGroup = row.find('.editGroup').attr('data-old-group');

        row.find('.dept_group').text(oldGroup);
        row.find('.saveBtn, .cancelBtn').remove();
        row.find('td:last').append(`<button class="editBtn">Edit</button>`);
    });

    $('#deptTable').on('click', '.saveBtn', function () {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const newGroup = row.find('.editGroup').val();
        const groupText = row.find('.editGroup option:selected').text();

        $.ajax({
            url: 'ajax/transaction/update_department.php',
            type: 'POST',
            data: {
                id: id,
                dept_group: newGroup
            },
            success: function (response) {
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

                        row.find('.dept_group').text(groupText);
                    } else {
                        swal({
                            type: "error",
                            title: "Update Failed",
                            text: data.message
                        });
                    }

                } catch (e) {
                    swal({
                        type: "error",
                        title: "Unexpected Response",
                        text: "Unexpected response from server."
                    });
                }
            },
            error: function () {
                swal({
                    type: "error",
                    title: "Error",
                    text: "Error updating department."
                });
            },
            complete: function () {
                row.find('.saveBtn, .cancelBtn').remove();
                row.find('td:last').append(`<button class="editBtn">Edit</button>`);
            }
        });
    });


    $('#addDeptBtn').on('click', function () {
        $('#addDeptGroupForm').slideToggle();
    });

    $('#addDeptGroupForm').on('submit', function (e) {
        e.preventDefault();

        $.ajax({
            url: 'ajax/transaction/insert_department_groups.php',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function (data) {
                if (data.status === 'success') {
                    swal('Department Group added successfully!');

                    deptGroupTable.row.add([
                        data.new.id,
                        data.new.dept_group,
                        data.new.added_on,
                        data.new.added_by,
                        '',
                        '',
                        `<button class="editBtn">Edit</button>`
                    ]).draw(false);

                    deptGroups.push({
                        id: data.new.id,
                        dept_group: data.new.dept_group
                    });

                    $('#addDeptGroupForm')[0].reset();
                    $('#addDeptGroupForm').slideUp();

                } else {
                    swal('Failed to add department group: ' + data.message);
                }
            },
            error: function () {
                swal('Error adding department group.');
            }
        });
    });


    $('#deptGroupTable').on('click', '.editBtn', function () {
        const row = $(this).closest('tr');
        const deptGroup = row.find('.dept_group');
        const currentGroup = deptGroup.text().trim();

        deptGroup.html(`
            <input type="text" value="${currentGroup}" class="editGroup" data-old-group="${currentGroup}">
        `);

        $(this).replaceWith(`
            <button class="saveBtn">Save</button>
            <button class="cancelBtn" style="margin-left:10px;">Cancel</button>
        `);
    });

    $('#deptGroupTable').on('click', '.cancelBtn', function () {
        const row = $(this).closest('tr');
        const oldGroup = row.find('.editGroup').attr('data-old-group');

        row.find('.dept_group').text(oldGroup);
        row.find('.saveBtn, .cancelBtn').remove();
        row.find('td:last').append(`<button class="editBtn">Edit</button>`);
    });

    $('#deptGroupTable').on('click', '.saveBtn', function () {
        const row = $(this).closest('tr');
        const id = row.data('id');
        const newGroup = row.find('.editGroup').val();

        $.ajax({
            url: 'ajax/transaction/update_department_group.php',
            type: 'POST',
            data: {
                id: id,
                dept_group: newGroup
            },
            dataType: 'json',
            success: function (data) {
                if (data.success) {
                    swal('Department Group updated successfully!');

                    row.find('.dept_group').text(newGroup);

                    deptGroups = deptGroups.map(group => {
                        if (String(group.id) === String(id)) {
                            group.dept_group = newGroup;
                        }
                        return group;
                    });

                } else {
                    swal('Failed to update department group: ' + data.message);
                }
            },
            error: function () {
                swal('Error updating department group.');
            },
            complete: function () {
                row.find('.saveBtn, .cancelBtn').remove();
                row.find('td:last').append(`<button class="editBtn">Edit</button>`);
            }
        });
    });

});


    </script>

</body>

</html>