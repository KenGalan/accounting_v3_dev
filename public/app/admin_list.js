$(function(){
adminList("1");

$("#liToggleAdmin").on("click", function(){
	var spanVal = $("#spnInactiveAdmin").html();
	
	if(spanVal == "Show"){
		$("#spnInactiveAdmin").html("Hide");
		$("#spnCaption").html("Inactive");
		adminList("0");
	}else{
		$("#spnInactiveAdmin").html("Show");
		$("#spnCaption").html("Active");
		adminList("1");
	}
});

$("#liAddAdmin").on("click", function(){
	$("#txtEmpNo").val("");
	$("#txtEmpName").val("");

	$("#txtEmpNo").autocomplete({
		source: "ajax/maintenance/select/autocomplete_active_employee_empno.php",
		minLength: 2,
		select: function( event, ui ) {
			$(this).val(ui.item.id);
			$("#txtEmpName").val(ui.item.label);
		}
	});
		
	$("#txtEmpName").autocomplete({
		source: "ajax/maintenance/select/autocomplete_active_employee_empname.php",
		minLength: 2,
		select: function( event, ui ) {
			$(this).val(ui.item.label);
			$("#txtEmpNo").val(ui.item.id);
		}
	});

	inputTextNumberOnly("#txtEmpNo");

	$("#addAdminModal").modal("show");
});

$("#btnSaveAdmin").on("click", function(){
	var empNo = $("#txtEmpNo").val();
	var empName = $("#txtEmpName").val();

	if(empNo == "" || empName == ""){
		swal("Error!","Kindly complete all information","error");

		$(".addAdminClass").each(function(){
			if(this.value == ""){
				errorLine($(this).parent(".form-line"));
			}
		});
	}else{
		swal({
				title: "Are you sure?",
				text: "Kindly check if not. Thank you!",
				type: "warning",
				showCancelButton: true,
				closeOnConfirm: false,
				showLoaderOnConfirm: true
			}, function (isConfirm) {
				if(isConfirm){

					$.ajax({
						type: "post",
						dataType: "json",
						data: {
							empNo : empNo,
							empName : empName
						},
						url: "ajax/maintenance/insert/save_admin.php",
						success: function(data){
							if(data.flag == true){
								swal({title:"Success!", text:"Successfully save new admin", type:"success"},
								function(){
									$("#spnInactiveAdmin").html("Show");
									$("#spnCaption").html("Active");
									adminList("1");
									$("#addAdminModal").modal("hide");
								});
							}else{
								if(data.msg == "duplicate"){
									duplicate_entry();
								}else{
									end_session();
								}
							}
						}
					});
					
				}	
			});
	}
});

$(".addAdminClass").on("keyup paste change", function(){
	var thisParent = $(this).parent(".form-line");
	if(thisParent.hasClass("error")){
		removeErrorLine($(this), thisParent); /* HELPER JS*/
	}
});

$("#frmAdmin").on("click", ".btnActiveDeacAdmin",function(){
	var id = this.getAttribute("id-attr");
	var active = this.getAttribute("active-attr");
	var titleSwal = "";

	if(active == "1"){
		titleSwal = "Are you sure you want to activate?";
	}else{
		titleSwal = "Are you sure you want to deactivate?";
	}
	
	swal({
		title: titleSwal,
		text: "Kindly check if not. Thank you!",
		type: "warning",
		showCancelButton: true,
		closeOnConfirm: false,
		showLoaderOnConfirm: true
	}, function (isConfirm) {
		if(isConfirm){
			$.ajax({
				type: "post",
				dataType: "json",
				data: {
					active : active,
					id : id
				},
				url: "ajax/maintenance/update/update_active_admin.php",
				success: function(data){
					if (data.flag == true){
						swal({title:"Successful", text:"Save Sucessfully.", type:"success"},
						function(){
							$("#spnInactiveAdmin").html("Show");
							$("#spnCaption").html("Active");
							adminList("1");
						});
					}else{
						end_session();/*HELPER JS*/
					}
				}
			});

		}	
	});

});

});

function adminList(active){
	xArray = {};
	xArray['active'] = [];
	xArray['active'] = active;
	
	ajaxTblDisplay("ajax/maintenance/select/sel_admin_list.php","#tbodyAdmin","","#tblAdmin","#frmAdmin","no",xArray,"yes","yes");/*HELPER JS*/
}