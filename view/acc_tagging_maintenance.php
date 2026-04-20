<style>
    body {
        font-family: Arial, sans-serif;
        padding: 20px;
        background: #f9f9f9;
    }

    /* form {
        margin-bottom: 20px;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 0 4px rgba(0, 0, 0, 0.1);
        display: none;
    } */

    input[type=text],
    select {
        padding: 6px;
        margin: 4px;
        border: 1px solid #ccc;
        border-radius: 4px;
        width: 100%;
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

    .saveBtn {
        background-color: #28a745;
    }

    .cancelBtn {
        background-color: #dc3545 !important;
    }

    #tagging_wrapper {
        max-width: 100%;
        margin: 40px auto;
        background: #ffffff;
        padding: 20px 25px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        font-family: "Inter", "Segoe UI", Roboto, sans-serif;
    }

    #accTaggingTbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #accTaggingTbl thead {
        background: #f8f9fb;
    }

    #accTaggingTbl th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD;
    }

    #accTaggingTbl tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #accTaggingTbl tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #accTaggingTbl td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #accTaggingTbl td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #accTaggingTbl td:last-child button:hover {
        background: #0056d2;
    }

    #accManuSupTbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #accManuSupTbl thead {
        background: #f8f9fb;
    }

    #accManuSupTbl th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD;
    }

    #accManuSupTbl tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #accManuSupTbl tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #accManuSupTbl td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #accManuSupTbl td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #accManuSupTbl td:last-child button:hover {
        background: #0056d2;
    }


    #accManuProdLineTbl {
        width: 100%;
        border-collapse: collapse;
        font-size: 16px;
        color: #333;
    }

    #accManuProdLineTbl thead {
        background: #f8f9fb;
    }

    #accManuProdLineTbl th {
        text-align: left;
        padding: 14px 16px;
        font-weight: 600;
        color: #ffffff;
        border-bottom: 2px solid #e5e7eb;
        background-color: #7C7BAD;
    }

    #accManuProdLineTbl tbody tr {
        transition: background 0.2s ease, transform 0.1s ease;
    }

    #accManuProdLineTbl tbody tr:nth-child(even) {
        background: #fafafa;
    }

    #accManuProdLineTbl td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
    }

    #accManuProdLineTbl td:last-child button {
        background: #007bff;
        color: white;
        border: none;
        padding: 8px 14px;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
        transition: background 0.2s ease;
    }

    #accManuProdLineTbl td:last-child button:hover {
        background: #0056d2;
    }

    #accTaggingBtn,
    #accWWip {
        background-color: #7C7BAD !important;
        float: right;
    }

    .modal-body {
        padding-bottom: 0 !important;
        border-bottom: 1px solid #eee;
    }

    .selection {
        float: inline-start;
    }

    .accGroupBtn {
        margin: 5px;
    }
</style>

<body>
    <div id="tagging_wrapper">
        <button id="accTaggingBtn"><i class="material-icons">add</i></button>
        <div class="selection">
            <button class="accGroupBtn" id="accG&A" data-group-name="GENERAL & ADMIN">GENERAL & ADMIN</button>
            <button class="accGroupBtn" id="accManuSup" data-group-name="MANUFACTURING SUPPORT">MANUFACTURING SUPPORT</button>
            <button class="accGroupBtn" id="accManuProdLine" data-group-name="MANUFACTURING/PRODUCT LINE">MANUFACTURING/PRODUCT LINE</button>
        </div>
        <br>
        <br>
        <div>
            <table style="width: 100%" id="accTaggingTbl">
                <thead>
                    <th>Group Department</th>
                    <th>To Account</th>
                    <th>From Account</th>
                    <!-- <th>To Wip</th> -->
                    <th></th>
                </thead>
                <tbody>
                    <!-- Append Here -->
                </tbody>
            </table>
        </div>
        <div>

            <table style="width: 100%; display: none;" id="accManuSupTbl">
                <thead>
                    <th>Group Department</th>
                    <th>To Account</th>
                    <th>From Account</th>
                    <th>Wip</th>
                    <th></th>
                </thead>
                <tbody>
                    <!-- Append Here -->
                </tbody>
            </table>
        </div>
        <div>
            <table style="width: 100%; display: none;" id="accManuProdLineTbl">
                <thead>
                    <th>Group Department</th>
                    <th>To Account</th>
                    <th>From Account</th>
                    <th>Wip</th>
                    <th></th>
                </thead>
                <tbody>
                    <!-- Append Here -->
                </tbody>
            </table>
        </div>
    </div>
    </div>

    <div class="modal" id="accTaggingModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="border-bottom: 1px solid #eee; padding: 0 10px">
                    <h4 id="modalHeader">Account Tagging</h4>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered" id="formTbl">
                        <thead>
                            <tr>
                                <th>Dept. Groups</th>
                                <th>To Account</th>
                                <th>From Account</th>
                                <th class="wip-col">Wip</th>
                                <!-- <th>To Wip</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td> <select name="" class="deptGroupSel" id="txtDeptGroupSel"></select></td>
                                <td><select name="" class="accountSel" id="txtToAcc"></select></td>
                                <td><select class="accountSel" id="txtFromAcc"></select></td>
                                <td class="wip-col">
                                    <select class="accountWip" id="txtToWip"></select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="cancelBtn" data-dismiss="modal">Cancel</button>
                    <button class="saveBtn" id="saveAccTagBtn">Save</button>
                    <button class="saveBtn" id="upAccTagBtn" style="display: none;">Save</button>
                </div>
            </div>
        </div>
    </div>
</body>

<script>
    $(document).ready(function() {
        accountTaggingData();
        // accountTaggingWip();
        // accountManuSupData(); 

        $('#accTaggingBtn').on('click', function() {
            $('#accTaggingModal').modal('show');
            $('.wip-col').show();
            $('#txtDeptGroupSel').attr('disabled', false);
            $('#txtFromAcc').attr('disabled', false)
            $('#upAccTagBtn').hide();
            $('#saveAccTagBtn').show();
            getDepartmentGroups();
            accountList('.accountSel');
            accountWip();

            $('#modalHeader').text('Account Tagging')
        })

        $('#saveAccTagBtn').on('click', function() {
            const fromAccount = $('#txtFromAcc').val();
            const toAccount = $('#txtToAcc').val();
            const deptGroups = $('#txtDeptGroupSel').val();
            const toWip = $('.wip-col:visible').length ?
                $('#txtToWip').val() :
                null;

            swal({
                title: 'Are you sure to submit your request?',
                text: 'This action is no turning back',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#4caf50",
                confirmButtonText: "Yes, i'm sure!",
                closeOnConfirm: false
            }, function() {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: 'ajax/transaction/save_acc_tagging.php',
                    data: {
                        from: fromAccount,
                        to: toAccount,
                        dept: deptGroups,
                        to_wip: toWip
                    },
                    success: function(res) {
                        if (res.flag) {
                            swal('Success!', res.msg, 'success');
                            $('#accTaggingModal').modal('hide');
                        } else {
                            swal('Error!', res.msg, 'error');
                        }
                    },
                    complete: function() {
                        accountTaggingData();
                        accountTaggingWip();
                        // accountManuSupData();
                    }
                });
            });
        });

        // REMOVING ITEM FROM TO ACC SELECT 
        $("#txtFromAcc").on("change", function() {
            selectedVal = $(this).val();

            // $('#txtToAcc option[value="' + selectedVal + '"]').remove() //commented by ken jan-20-2026
        });

        // REMOVING ITEM FROM FROM ACC SELECT
        $("#txtToAcc").on("change", function() {
            selectedVal = $(this).val();

            // $('#txtFromAcc option[value="' + selectedVal + '"]').remove() //commented by ken jan-20-2026
        });

        // DELETE ITEM FROM ACC TAGGING
        $('#accTaggingTbl').on('click', '.delAccTagBtn', function() {
            tagId = $(this).attr('id-attr');

            swal({
                title: 'Are you sure you want to delete this item?',
                text: 'This action is no turning back',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#4caf50",
                confirmButtonText: "Yes, i'm sure!",
                closeOnConfirm: false
            }, function() {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: {
                        id: tagId
                    },
                    url: 'ajax/transaction/delete_acc_tagging.php',
                    success: function(res) {
                        log(res)
                        if (res.flag) {
                            swal('Success!', res.msg, 'success');
                        } else {
                            swal('Error!', res.msg, 'error');
                        }
                    }
                })
            })
        })

        $('#accManuSupTbl').on('click', '.delAccTagBtn', function() {
            tagId = $(this).attr('id-attr');

            swal({
                title: 'Are you sure you want to delete this item?',
                text: 'This action is no turning back',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#4caf50",
                confirmButtonText: "Yes, i'm sure!",
                closeOnConfirm: false
            }, function() {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    data: {
                        id: tagId
                    },
                    url: 'ajax/transaction/delete_acc_tagging.php',
                    success: function(res) {
                        log(res)
                        if (res.flag) {
                            swal('Success!', res.msg, 'success');
                        } else {
                            swal('Error!', res.msg, 'error');
                        }
                    }
                })
            })
        })

        // OPEN MODAL
        $('#accTaggingTbl').on('click', '.upAccTagBtn', function() {
            tagId = $(this).attr('id-attr');

            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    id: tagId
                },
                url: 'ajax/fetch/fetch_acc_tag_item.php',
                beforeSend: function() {

                },
                success: function(res) {
                    // log(res)

                    setTimeout(function() {
                        $('#txtFromAcc').val(res.from_account_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtDeptGroupSel').val(res.dept_group_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtToAcc').val(res.to_account_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtToWip').val(res.to_wip_account_id).trigger('change');
                    }, 100)
                },
                complete: function() {
                    $('#modalHeader').prepend('Update ')
                    $('#accTaggingModal').modal('show');
                    $('#txtDeptGroupSel').attr('disabled', true);
                    $('#upAccTagBtn').show();
                    $('#saveAccTagBtn').hide();
                    getDepartmentGroups();
                    accountList('.accountSel');
                    accountWip();
                    $('#txtFromAcc').attr('disabled', true)
                    $('#upAccTagBtn').attr('id-attr', tagId);
                }
            })

        })

        $('#accManuSupTbl').on('click', '.upAccTagBtn', function() {
            tagId = $(this).attr('id-attr');

            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    id: tagId
                },
                url: 'ajax/fetch/fetch_acc_tag_item.php',
                beforeSend: function() {

                },
                success: function(res) {
                    // log(res)

                    setTimeout(function() {
                        $('#txtFromAcc').val(res.from_account_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtDeptGroupSel').val(res.dept_group_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtToAcc').val(res.to_account_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtToWip').val(res.to_wip_account_id).trigger('change');
                    }, 100)
                },
                complete: function() {
                    $('#modalHeader').prepend('Update ')
                    $('#accTaggingModal').modal('show');
                    $('#txtDeptGroupSel').attr('disabled', true);
                    $('#upAccTagBtn').show();
                    $('#saveAccTagBtn').hide();
                    getDepartmentGroups();
                    accountList('.accountSel');
                    accountWip();
                    $('#txtFromAcc').attr('disabled', true)
                    $('#upAccTagBtn').attr('id-attr', tagId);
                }
            })

        })

        $('#accManuProdLineTbl').on('click', '.upAccTagBtn', function() {
            tagId = $(this).attr('id-attr');

            $.ajax({
                type: 'post',
                dataType: 'json',
                data: {
                    id: tagId
                },
                url: 'ajax/fetch/fetch_acc_tag_item.php',
                beforeSend: function() {

                },
                success: function(res) {
                    // log(res)

                    setTimeout(function() {
                        $('#txtFromAcc').val(res.from_account_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtDeptGroupSel').val(res.dept_group_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtToAcc').val(res.to_account_id).trigger('change');
                    }, 100)
                    setTimeout(function() {
                        $('#txtToWip').val(res.to_wip_account_id).trigger('change');
                    }, 100)
                },
                complete: function() {
                    $('#modalHeader').prepend('Update ')
                    $('#accTaggingModal').modal('show');
                    $('#txtDeptGroupSel').attr('disabled', true);
                    $('#upAccTagBtn').show();
                    $('#saveAccTagBtn').hide();
                    getDepartmentGroups();
                    accountList('.accountSel');
                    accountWip();
                    $('#txtFromAcc').attr('disabled', true)
                    $('#upAccTagBtn').attr('id-attr', tagId);
                }
            })

        })

        // UPDATE ACCOUNT TAGGING
        $('#upAccTagBtn').on('click', function() {
            const fromAccount = $('#txtFromAcc').val();
            const toAccount = $('#txtToAcc').val();
            const deptGroups = $('#txtDeptGroupSel').val();
            const tagId = $(this).attr('id-attr');
            const toWip = $('.wip-col:visible').length ?
                $('#txtToWip').val() :
                null;

            swal({
                title: 'Are you sure you want to update this item?',
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: "#4caf50",
                confirmButtonText: "Yes, i'm sure!",
                closeOnConfirm: false
            }, function() {
                $.ajax({
                    type: 'post',
                    dataType: 'json',
                    url: 'ajax/transaction/update_acc_tagging.php',
                    data: {
                        id: tagId,
                        from: fromAccount,
                        to: toAccount,
                        dept: deptGroups,
                        to_wip: toWip
                    },
                    success: function(res) {
                        if (res.flag) {
                            swal('Success!', res.msg, 'success');
                        } else {
                            swal('Error!', res.msg, 'error');
                        }
                    },
                    complete: function() {
                        accountTaggingData();
                        accountTaggingWip();
                    }
                });
            });
        });
    })

    function getDepartmentGroups() {
        $.ajax({
            type: 'get',
            url: 'ajax/fetch/fetch_department_groups.php',
            success: function(data) {
                $('.deptGroupSel').html(data).select2({
                    placeholder: "Select Department",
                    width: "180px"
                });
                // log(data)
            }
        })
    }

    function accountList(class_name) {
        $.ajax({
            type: 'get',
            url: 'ajax/fetch/fetch_account_account_list.php',
            success: function(data) {
                $(class_name).html(data).select2({
                    placeholder: "Select Account",
                    width: "160px"
                });
            }
        })
    }

    function accountWip() {
        $.ajax({
            type: 'get',
            url: 'ajax/fetch/fetch_account_wip.php',
            success: function(data) {
                $('.accountWip').html(data).select2({
                    placeholder: "Select Wip",
                    width: "160px"
                });
            }
        })
    }

    function accountTaggingData() {
        $.ajax({
            type: 'get',
            url: 'ajax/fetch/get_account_tagging.php',
            beforeSend: function() {
                $('#accTaggingTbl').DataTable().destroy()
            },
            success: function(html) {
                $('#accTaggingTbl tbody').html(html);
                $('#accManuSupTbl').hide();
                $('#accManuSupTbl_wrapper').hide();
                $('#accTaggingTbl').show();
                $('#accTaggingTbl_wrapper').show();
                $('#accManuProdLineTbl_wrapper').hide();
                $('#accManuProdLineTbl').hide();
            },
            complete: function() {
                $('#accTaggingTbl').DataTable();
            }
        })
    }

    function accountTaggingWip() {
        $.ajax({
            type: 'get',
            url: 'ajax/fetch/get_acc_wip.php',
            beforeSend: function() {
                $('#accManuSupTbl').DataTable().destroy()
            },
            success: function(html) {
                $('#accManuSupTbl tbody').html(html);
                $('#accManuSupTbl').DataTable();
                $('#accManuSupTbl').show();
                $('#accTaggingTbl').hide();
                $('#accManuProdLineTbl').hide();
                $('#accTaggingTbl_wrapper').hide();
                $('#accManuSupTbl_wrapper').show();
                $('#accManuProdLineTbl_wrapper').hide();
            },
            complete: function() {
                $('#accManuSupTbl').DataTable();
            }
        })
    }

    function accountTaggingCongsProduct() {
        $.ajax({
            type: 'get',
            url: 'ajax/fetch/get_acc_wip_product.php',
            beforeSend: function() {
                $('#accManuProdLineTbl').DataTable().destroy()
            },
            success: function(html) {
                $('#accManuProdLineTbl tbody').html(html);
                $('#accManuProdLineTbl').DataTable();
                $('#accManuProdLineTbl').show();
                $('#accTaggingTbl').hide();
                $('#accManuSupTbl').hide();
                $('#accTaggingTbl_wrapper').hide();
                $('#accManuSupTbl_wrapper').hide();
            },
            complete: function() {
                $('#accManuProdLineTbl').DataTable();
            }
        })
    }

    function setActive(btn) {
        $('#accG\\&A, #accManuSup, #accManuProdLine').removeClass('active');
        $(btn).addClass('active');
    }

    $('#accG\\&A').on('click', function() {
        setActive(this);
        accountTaggingData();
    });

    $('#accManuSup').on('click', function() {
        setActive(this);
        accountTaggingWip();
    });

    $('#accManuProdLine').on('click', function() {
        setActive(this);
        accountTaggingCongsProduct();
    });

    $('.accGroupBtn').on('click', function() {
        const group = $(this).data('group-name');
    });

    $(document).on('change', '#txtDeptGroupSel', function() {
        const selected = $(this).val();
        console.log(selected);

        if (!selected || !selected.length) {
            $('.wip-col').show();
            return;
        }

        const isGA = selected.includes('2');

        if (isGA) {
            $('.wip-col').hide();
        } else {
            $('.wip-col').show();
        }
    });
</script>