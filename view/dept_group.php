<?php
$db = new Postgresql();
$conn = $db->getConnection();

$query = "SELECT id, dept_group, added_on, added_by, changed_on, changed_by, active
          FROM M_ACC_DEPARTMENT_GROUPS
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

    button:hover {
        background-color: #45a049;
    }

    #addDeptBtn {
        background-color: #7C7BAD !important;
        margin-bottom: 15px;
    }

    #addDeptBtn:hover {
        background-color: #7C7BAD !important;
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

    #deptGroupTable {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #deptGroupTable  thead {
        background: #f8f9fb;
    }

    #deptGroupTable  th {
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

    #deptGroupTable td:last-child button:hover {
        background: #0056d2;
    }
</style>

<body>

    <button id="addDeptBtn">Add Department Group</button>

    <form id="addDeptGroupForm">
        <label>Department Group:</label>
        <input type="text" id="dept_group" name="dept_group" required>
        <!-- <label>Department Code:</label>
    <input type="text" id="dept_code" name="dept_code" required> -->
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

    <script>
        $(document).ready(function() {
            const table = $('#deptGroupTable').DataTable({
                pageLength: 10,
                order: [
                    [0, 'asc']
                ],
                language: {
                    search: "Search :",
                    lengthMenu: "Show _MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ Group departments",
                    infoFiltered: "(filtered from _MAX_ total)"
                }
            });

            $('#addDeptBtn').on('click', function() {
                $('#addDeptGroupForm').slideToggle();
            });

            $('#addDeptGroupForm').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'ajax/transaction/insert_department_groups.php',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                swal('Department Group added successfully!');
                                table.row.add([
                                    data.new.id,
                                    data.new.dept_group,
                                    data.new.added_on,
                                    data.new.added_by,
                                    '',
                                    '',
                                    data.new.active === 't' ? 'Yes' : 'No',
                                    // data.new.employee_no,
                                    data.new.dept_code,
                                    "<button class='editBtn'>Edit</button>"
                                ]).draw(false);
                                $('#addDeptGroupForm')[0].reset();
                                $('#addDeptGroupForm').slideUp();
                                // location.reload();
                            } else {
                                swal('Failed to add department group: ' + data.message);
                            }
                        } catch {
                            swal('Unexpected response from server.');
                        }
                    }
                });
            });

            $('#deptGroupTable').on('click', '.editBtn', function() {
                const row = $(this).closest('tr');
                const deptGroup = row.find('.dept_group');
                // const deptCode = row.find('.dept_code');
                const currentGroup = deptGroup.text();
                // const currentCode = deptCode.text();

                deptGroup.html(`<input type='text' value='${currentGroup}' class='editGroup'>`);
                // deptCode.html(`<input type='text' value='${currentCode}' class='editCode'>`);
                $(this).replaceWith(`
            <button class='saveBtn'>Save</button>
            <button class='cancelBtn' style='margin-left: 10px;'>Cancel</button>
        `);
            });

            $('#deptGroupTable').on('click', '.cancelBtn', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const groupInput = row.find('.editGroup').val();
                // const codeInput = row.find('.editCode').val();

                row.find('.dept_group').text(groupInput);
                // row.find('.dept_code').text(codeInput);
                row.find('.saveBtn, .cancelBtn').remove();
                row.find('td:last').append("<button class='editBtn'>Edit</button>");
            });

            $('#deptGroupTable').on('click', '.saveBtn', function() {
                const row = $(this).closest('tr');
                const id = row.data('id');
                const newGroup = row.find('.editGroup').val();

                $.ajax({
                    url: 'ajax/transaction/update_department_group.php',
                    type: 'POST',
                    data: { id: id, dept_group: newGroup },
                    dataType: 'json',
                    success: function(data) {

                        if (data.success) {
                            swal('Department Group updated successfully!');
                            row.find('.dept_group').text(newGroup);
                        } else {
                            swal('Failed to update department group: ' + data.message);
                        }
                    },
                    error: function() {
                        swal('Error updating department group.');
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