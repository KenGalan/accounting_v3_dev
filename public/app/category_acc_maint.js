     $(document).ready(function() {
            let journals = [];
            let deptTable2;

            let moDistList = [];

            $.getJSON('ajax/json/mo_distribution.json', function (data) {
                moDistList = data;
                // initDeptTable();
            });

            $.ajax({
                url: "ajax/fetch/get_journals.php",
                type: "GET",
                dataType: "json",
                success: function(data) {
                    journals = data;
                    console.log("Loaded journals:", journals);

                    initDeptTable2();
                },
                error: function(err) {
                    console.error("Failed to load journals:", err);
                }
            });

            function initDeptTable2() {
                deptTable2 = $('#deptTable2').DataTable({
                    ajax: {
                        url: 'ajax/fetch/categories.php',
                        dataSrc: ''
                    },
                    columns: [{
                            data: 'acc_category'
                        },
                        {
                            data: 'added_on',
                            render: function(data) {
                                if (!data) return "";

                                let dateObj = new Date(data);
                                let formatted = dateObj.toLocaleDateString('en-US', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: '2-digit'
                                });

                                return formatted.toUpperCase();
                            }
                        },
                        {
                            data: 'added_by'
                        },
                        // { data: 'changed_on' },
                        // { data: 'changed_by' },
                        {
                            data: 'journal_id',
                            render: function(journal_id) {
                                let html = `<select class="journalSelect" disabled>`;
                                html += `<option value="">Select Journal</option>`;

                                journals.forEach(j => {
                                    html += `<option value="${j.id}" ${journal_id == j.id ? 'selected' : ''}>${j.name}</option>`;
                                });

                                html += `</select>`;
                                return html;
                            }
                        },
                        {
                            data: 'mo_pct_ref',
                            render: function(mo_pct_ref, type, row) {

                                let html = `<select class="pct_ref_select" disabled>`;
                                html += `<option value="">Select Option</option>`;

                                moDistList.forEach(item => {
                                    html += `<option value="${item.value}" ${mo_pct_ref == item.value ? 'selected' : ''}>
                                                ${item.name}
                                            </option>`;
                                });

                                html += `</select>`;
                                return html;
                            }
                        },
                        {
                            data: null,
                            render: data => `
                        <button class="editBtn" 
                            data-id="${data.id}" 
                            data-category="${data.acc_category}" 
                            data-journal_id="${data.journal_id}">
                            Edit
                        </button>`
                        }
                    ],
                    pageLength: 5,
                    lengthChange: false
                });

                enableCategoryEvents();
            }

            $('#addCategoryBtn').on('click', function() {
                $('#addCategoryContainer').show();
                $('#newCategoryInput').focus();
            });

            $('#cancelCategoryBtn').on('click', function() {
                $('#newCategoryInput').val('');
                $('#addCategoryContainer').hide();
            });

            $('#saveCategoryBtn').on('click', function() {
                const newCategory = $('#newCategoryInput').val().trim();
                if (!newCategory) return alert('Please enter a category name.');

                $.ajax({
                    url: 'ajax/transaction/save_category.php',
                    method: 'POST',
                    data: {
                        category: newCategory
                    },
                    success: function(res) {
                        const data = typeof res === 'string' ? JSON.parse(res) : res;
                        if (data.success) {
                            alert('Category added successfully!');
                            $('#newCategoryInput').val('');
                            $('#addCategoryContainer').hide();
                            deptTable.ajax.reload(null, false);
                        } else {
                            swal('Error: ' + data.message);
                        }
                    },
                    error: function(err) {
                        swal('Failed to save category.');
                        console.error(err);
                    }
                });
            });

            function enableCategoryEvents() {

                $('#deptTable2 tbody').on('click', '.editBtn', function() {
                    const row = $(this).closest('tr');
                    const rowData = deptTable2.row(row).data();

                    if (row.find('input.editInput').length > 0) return;
                    row.find(".journalSelect").prop("disabled", false);
                    row.find(".pct_ref_select").prop("disabled", false);
                    const originalCategory = rowData.acc_category;
                    row.find('td').eq(0).html(
                        `<input type="text" class="editInput" value="${originalCategory}" style="width:90%;" />`
                    );

                    $(this).text('Save').removeClass('editBtn').addClass('saveEditBtn');

                    const actionCell = row.find('td').eq(5);
                    if (actionCell.find('.cancelEditBtn').length === 0) {
                        actionCell.append(`<button class="cancelEditBtn btn-danger"><i class="fa fa-remove"></i></button>`);
                    }
                });

                $('#deptTable2 tbody').on('click', '.saveEditBtn', function() {
                    const row = $(this).closest('tr');
                    const rowData = deptTable2.row(row).data();

                    const id = rowData.id;
                    const newCategory = row.find('input.editInput').val().trim();
                    const journal_id = row.find('.journalSelect').val() || null;
                    const mo_pct_ref = row.find('.pct_ref_select').val() || null;

                    if (!newCategory) return alert('Category cannot be empty.');

                    $.ajax({
                        url: 'ajax/transaction/update_categories.php',
                        method: 'POST',
                        data: {
                            id: id,
                            category: newCategory,
                            journal_id: journal_id,
                            mo_pct_ref: mo_pct_ref
                        },
                        success: function(res) {
                            const data = typeof res === 'string' ? JSON.parse(res) : res;

                            if (data.success) {
                                swal('Category updated successfully!');
                                deptTable2.ajax.reload(null, false);
                            } else {
                                swal('Error: ' + data.message);
                            }
                        },
                        error: function(err) {
                            swal('Failed to update category.');
                            console.error(err);
                        }
                    });
                });
                $('#deptTable2 tbody').on('click', '.cancelEditBtn', function() {
                    const row = $(this).closest('tr');
                    const rowData = deptTable2.row(row).data();

                    row.find('td').eq(0).text(rowData.acc_category);

                    row.find(".journalSelect").val(rowData.journal_id).prop("disabled", true);
                    row.find(".pct_ref_select").val(rowData.mo_pct_ref).prop("disabled", true);

                    row.find('.saveEditBtn')
                        .text('Edit')
                        .removeClass('saveEditBtn')
                        .addClass('editBtn');

                    $(this).remove();
                });
            }

        });