$(document).ready(function() {
  console.log('ready');
  start();

  // getColorLegendDT();

  $("#addcolorModal").on("shown.bs.modal", function (e){

    var palette = [
        ["rgb(244, 204, 204)", "rgb(252, 229, 205)", "rgb(255, 242, 204)", "rgb(217, 234, 211)", 
        "rgb(201, 218, 248)", "rgb(207, 226, 243)", "rgb(217, 210, 233)", "rgb(234, 209, 220)"], 
        ["rgb(221, 126, 107)", "rgb(234, 153, 153)", "rgb(249, 203, 156)", "rgb(255, 229, 153)", "rgb(182, 215, 168)", 
        "rgb(162, 196, 201)", "rgb(164, 194, 244)", "rgb(180, 167, 214)", "rgb(213, 166, 189)"]
        ];
    $("#colorInput").spectrum({
        color: palette[0][0],    
        showPaletteOnly: true,
        hideAfterPaletteSelect:true,
        //allowEmpty: true,
        // change: function(color) {
        //     printColor(color);
        // },

        palette: palette
              
        
    });
    $("#addcolorModalBody").waitMe({effect : 'pulse', text : 'Loading...'});
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "ajax/maintenance/mrp_color_coding/select/select_color_legend.php",
        success: function(response){
          data = response["data"];
          for(i=0; i<data.length; i++){
            hexRgbColor = hexToRgb(data[i]['COLOR']);

            for(j=0; j<palette.length; j++){
              for(k=0; k<palette[j].length; k++){
                if(palette[j][k] === hexRgbColor){
                  console.log('ye');
                  palette[j].splice(k,1);
                }
              }
            }
          }

          console.log(palette);
          $("#addcolorModalBody").waitMe("hide");
          $("#colorInput").spectrum({
            color: palette[0][0],    
            showPaletteOnly: true,
            hideAfterPaletteSelect:true,
            //allowEmpty: true,
            // change: function(color) {
            //     printColor(color);
            // },

            palette: palette
              
        
          });

        }
    });

        
  });
  $("#addcolorModal").on("hidden.bs.modal", function (e){
        $(this)
        .find("input,textarea,select")
        .val('')
        .end();

        // start();
        
  });
  $("#tagcolorModal").on("hidden.bs.modal", function (e){
        $(this)
        .find("input,textarea,select")
        .val('')
        .end();
        $("#selSupp").trigger("chosen:updated");
        $("#selCateg").trigger("chosen:updated");
        $("#selStock").trigger("chosen:updated");

        // start();
        
  });

  $("#editcolorModal").on("hidden.bs.modal", function (e){
        $(this)
        .find("input,textarea,select")
        .val('')
        .end();

        // start();
        
  });
  $("#edittagModal").on("hidden.bs.modal", function (e){
        $(this)
        .find("input,textarea,select")
        .val('')
        .end();
        $("#editTagColorDesc").trigger("chosen:updated");

        // start();
        
  });

  $("#colorDescInput").on("keyup change paste", function(event){
    var count = countChar(this,250);
    $("#colorDescInputLabel").html(count);
  });

  $("#editColorDescInput").on("keyup change paste", function(event){
    var count = countChar(this,250);
    $("#editColorDescInputLabel").html(count);
  });

  $("#save_colorLegendBtn").on("click", function(){
    var color = $("#colorInput").spectrum("get").toHexString();
    var descr = $("#colorDescInput").val();
    if(descr == ""){
      alert("Please fill out the blank field/s");
    }
    else{
      swal({
                title: "Are you sure you want to save?",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Yes, I am sure!',
                cancelButtonText: "No, cancel it!",
                closeOnConfirm: false,
                closeOnCancel: false
        },
        function(isConfirm){
           
          if (isConfirm){
            $.ajax({
              type: "POST",
              dataType: "json",
              data:{
                color: color,
                descr: descr
              },
              url: "ajax/maintenance/mrp_color_coding/insert/insert_color_legend.php",
              success: function(response){
                console.log(response);
                if(response == 1){
                  swal({title: "Saved!",
                    text: "",
                    type: "success"},
                  function(isConfirm){
                    if(isConfirm){
                        $("#addcolorModal").modal("hide");
                        loader();
                    }

                  });
                }
              }
            });
          } else {
            swal("Saving cancelled", "", "error");
          }
        });
    }
    
  });

  $("#save_colorTagBtn").on("click", function(){
    tagType = $("#tagType").val();
    if(tagType == "by Supplier"){
      data = $("#selSupp").val();
    }
    else if(tagType == "by Category"){
      data = $("#selCateg").val();
    }
    else{
      data = $("#selStock").val();
    }
    legendId = $("#tagColorDesc").val();
    // remarks = $("#tagRemarks").val();


    if((data == null || data == "") || (legendId == null || legendId == "")){
      alert("Please fill out the blank field/s");
    }
    else if((data == null || data == "") && (legendId == null || legendId == "")){
      alert("Please fill out the blank field/s"); 
    }
    else{
      swal({
            title: "Are you sure you want to save?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Yes, I am sure!',
            cancelButtonText: "No, cancel it!",
            closeOnConfirm: false,
            closeOnCancel: false
      },
      function(isConfirm){
        if (isConfirm){
          $.ajax({
            type: "POST",
            dataType: "json",
            data:{
              tagType: tagType,
              data: data,
              legendId: legendId
              // remarks: remarks
            },
            url: "ajax/maintenance/mrp_color_coding/insert/insert_tag.php",
            success: function(response){
              if(response == 1){
                swal({
                  title: "Saved!",
                  text: "",
                  type: "success"
                },
                function(isConfirm){
                  if(isConfirm){
                    $("#tagcolorModal").modal("hide");
                    loader();
                  }
                });
              }
            }
          });
        } else {
            swal("Saving cancelled", "", "error");
        }
      });
    }

  });

  //edit
  $("#save_editColorLegendBtn").on("click", function(){
    newDesc = $("#editColorDescInput").val();
    legendId = $("input#colorId").val();

    if(newDesc == "" || newDesc == null){
        alert("Please fill out the blank field/s");
    }   
    else{
      swal({
            title: "Are you sure you want to submit changes?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Yes, I am sure!',
            cancelButtonText: "No, cancel it!",
            closeOnConfirm: false,
            closeOnCancel: false
      },
      function(isConfirm){
        if (isConfirm){
          $.ajax({
            type: "POST",
            dataType: "json",
            data:{
              legendId: legendId,
              desc: newDesc
              // remarks: remarks
            },
            url: "ajax/maintenance/mrp_color_coding/update/update_legend.php",
            success: function(response){
              if(response == 1){
                swal({
                  title: "Saved!",
                  text: "",
                  type: "success"
                },
                function(isConfirm){
                  if(isConfirm){
                    $("#editcolorModal").modal("hide");
                    loader();
                  }
                });
              }
            }
          });
        } else {
            swal("Saving cancelled", "", "error");
        }
      });
    }
  });

  $("#save_editTagBtn").on("click", function(){
    newDesc = $("#editTagColorDesc").val();
    invId = $("input#invId").val();

    if(newDesc == "" || newDesc == null){
        alert("Please fill out the blank field/s");
    }   
    else{
      swal({
            title: "Are you sure you want to submit changes?",
            text: "",
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Yes, I am sure!',
            cancelButtonText: "No, cancel it!",
            closeOnConfirm: false,
            closeOnCancel: false
      },
      function(isConfirm){
        if (isConfirm){
          $.ajax({
            type: "POST",
            dataType: "json",
            data:{
              invId: invId,
              desc: newDesc
              // remarks: remarks
            },
            url: "ajax/maintenance/mrp_color_coding/update/update_tag.php",
            success: function(response){
              if(response == 1){
                swal({
                  title: "Saved!",
                  text: "",
                  type: "success"
                },
                function(isConfirm){
                  if(isConfirm){
                    $("#edittagModal").modal("hide");
                    loader();
                  }
                });
              }
            }
          });
        } else {
            swal("Saving cancelled", "", "error");
        }
      });
    }
  });

  $("#editgroupModal").on("shown.bs.modal", function(){
      $("#groupType").chosen({search_contains: true});
      $("#groupChosen").chosen({search_contains: true});
      $("#chooseDesc").chosen({search_contains: true});
  });

  $("#editgroupModal").on("hidden.bs.modal", function(){
      $("#groupType").html("<option value='NULL'>---SELECT ONE---</option><option value='by Supplier'>by Supplier</option><option value='by Category'>by Category</option>");
      $("#groupType").trigger("chosen:updated");
      $("#groupChosen").html("<option value='NULL'>---SELECT ONE---</option>");
      $("#groupChosen").trigger("chosen:updated");
      $("#chooseDesc").html("<option value='NULL'>---SELECT ONE---</option>");
      $("#chooseDesc").trigger("chosen:updated");
  });

  $("#groupType").on("keyup change paste", function(){
    groupType = $("#groupType").val();
    if(groupType != 'NULL'){
      $.ajax({
        type: 'POST',
        dataType: 'json',
        data:{
          type: groupType
        },
        url: "ajax/maintenance/mrp_color_coding/select/select_groups.php",
        success: function(response){
          data = response['data'];
          groupChosenString = "";
          if(data != null){
            groupChosenString = "<option value='NULL'>---SELECT ONE---</option>";
            $(data).each(function(){
              self = this;
              groupChosenString += "<option value='"+ self['GROUPS'] +"'>"+self['GROUPS']+"</option>";
              groupDescString = "<option value='NULL'>---SELECT ONE---</option>";
            });
            
          }
          else{
            groupChosenString = "<option value='NULL'>---SELECT ONE---</option>";
            groupDescString = "<option value='NULL'>---SELECT ONE---</option>";
          }
          $("#groupChosen").html(groupChosenString);
          $("#groupChosen").trigger("chosen:updated");

          $("#chooseDesc").html(groupDescString);
          $("#chooseDesc").trigger("chosen:updated");
        }
      });
    }
    else{
      groupChosenString = "<option value='NULL'>---SELECT ONE---</option>";
      $("#groupChosen").html(groupChosenString);
      $("#groupChosen").trigger("chosen:updated");
      groupDescString = "<option value='NULL'>---SELECT ONE---</option>";
      $("#chooseDesc").html(groupDescString);
      $("#chooseDesc").trigger("chosen:updated");
    }
  });

  $("#groupChosen").on("keyup change paste", function(){
    groupChosen = $("#groupChosen").val();
    groupType = $("#groupType").val();
    if(groupChosen != 'NULL'){
      $.ajax({
        type: 'POST',
        dataType: 'json',
        data:{
          group: groupChosen,
          type: groupType
        },
        url: "ajax/maintenance/mrp_color_coding/select/select_group_desc.php",
        success: function(groupDesc){
          groupDescString = "";
          console.log(groupDesc['data']['LEGEND_ID']);
          if(groupDesc['data'] != null){
            $.ajax({
              type: 'POST',
              dataType: 'json',
              url: "ajax/maintenance/mrp_color_coding/select/select_color_legend.php",
              success: function(desc){
                groupDescString = "";
                if(desc['data'] != null){
                  // groupDescString = "<option value='NULL'>---SELECT ONE---</option>";
                  $(desc['data']).each(function(){
                    self = this;
                    if(groupDesc['data']['LEGEND_ID'] == self['ID']){
                      groupDescString += "<option value='"+ self['ID'] +"' selected>"+self['DESCRIPTION']+"</option>";
                    }
                    else{
                      groupDescString += "<option value='"+ self['ID'] +"'>"+self['DESCRIPTION']+"</option>";
                      
                    }
                  });
                }
                else{
                  groupDescString = "<option value='NULL'>---SELECT ONE---</option>";
                }
                $("#chooseDesc").html(groupDescString);
                $("#chooseDesc").trigger("chosen:updated");
              }
            });
          }
          else{
            groupDescString = "<option value='NULL'>---SELECT ONE---</option>";
          }
          $("#chooseDesc").html(groupDescString);
          $("#chooseDesc").trigger("chosen:updated");
        }
      });
    }
    else{
      groupDescString = "<option value='NULL'>---SELECT ONE---</option>";
      $("#chooseDesc").html(groupDescString);
      $("#chooseDesc").trigger("chosen:updated");
    }
  });

  $("#save_editGroupBtn").on("click", function(){
    groupType = $("#groupType").val();
    groupChosen = $("#groupChosen").val();
    chooseDesc = $("#chooseDesc").val();

    if(groupType == 'NULL' && groupChosen == 'NULL' && chooseDesc == 'NULL'){
      alert("Please fill out the blank field/s");
    }
    else if(groupType == 'NULL' || groupChosen == 'NULL' || chooseDesc == 'NULL'){
      alert("Please fill out the blank field/s");
    }
    else{
      swal({
                title: "Are you sure you want to save?",
                text: "",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: '#DD6B55',
                confirmButtonText: 'Yes, I am sure!',
                cancelButtonText: "No, cancel it!",
                closeOnConfirm: false,
                closeOnCancel: false
        },
        function(isConfirm){
           
          if (isConfirm){
            $.ajax({
              type: "POST",
              dataType: "json",
              data:{
                type: groupType,
                group: groupChosen,
                desc: chooseDesc
              },
              url: "ajax/maintenance/mrp_color_coding/update/update_by_group.php",
              success: function(response){
                console.log(response);
                if(response == 1){
                  swal({title: "Saved!",
                    text: "",
                    type: "success"},
                  function(isConfirm){
                    if(isConfirm){
                        $("#editgroupModal").modal("hide");
                        loader();
                    }

                  });
                }
              }
            });
          } else {
            swal("Saving cancelled", "", "error");
          }
        });
    }
  });
}); 

function start(){
  $('.nav-tabs a[href="#colorlegends"]').tab('show');
  // getColorLegendDT();

  var tabTables = $("#pageBody").find('table');
  $(tabTables).each(function(){
      var innerTblId = $(this).attr('id');
      if($.fn.DataTable.isDataTable( '#'+innerTblId ) ){
        $('#'+innerTblId).DataTable().clear().destroy();
      }
  });

  colorLegendData = getColorLegendData();
  colorTagData = getColorTagData();

  getColorLegendDT("colorlegends_Table", colorLegendData);

  $('a[class="mrp-color-tab"]').on("click", function(e){
      $("div#divCardId").waitMe({effect : 'pulse', text : 'Loading...'});
  });

  $('a[class="mrp-color-tab"]').on('shown.bs.tab', function (e) {

      var tabTables = $("#pageBody").find('table');
      $(tabTables).each(function(){
        var innerTblId = $(this).attr('id');
        if($.fn.DataTable.isDataTable( '#'+innerTblId ) ){
          $('#'+innerTblId).DataTable().clear().destroy();
        }
      });

      var activeTabId = $(this).attr('href');
      console.log(activeTabId);
      if(activeTabId == "#colorlegends"){
        getColorLegendDT("colorlegends_Table", colorLegendData);
        $("div#divCardId").waitMe('hide');
      }
      else{
        getColorTagDT("colortags_Table", colorTagData);
        $("div#divCardId").waitMe('hide');
      }
            
            

          
  }); 

}

function getColorLegendData(){
  colorLegendData = "";
  $.ajax({
    async: false,
    type: "POST",
    dataType: "json",
    url: "ajax/maintenance/mrp_color_coding/select/select_color_legend.php",
    success: function(response){
      colorLegendData = response['data'];
    }
  });

  return colorLegendData;
}

function getColorLegendDT(tableId, dataArray){
  var colorLegendTbl = $('#'+tableId).DataTable({
      // "ajax":{
      //         "url":"ajax/maintenance/mrp_color_coding/select/select_color_legend.php",
      //         "type":"post"
      //       },
      "data": dataArray,
      "columns":[
                { "data": "DESCRIPTION", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "COLOR", render: function ( data,type,row ){
                  //return data;
                  return "<div style='height: 17px; width: 150px; background-color:"+data+";'>Sample Text</div>";
                } },
                { "data": "", "bSortable": false, render: function ( data,type,row ){
                  return "<button class='btn btn-xs bg-indigo colorLegendActionBtn' id='editColorLegendBtn'><i class='material-icons' style='font-weight: bold;' title='edit'>mode_edit</i></button> <button class='btn btn-xs btn-danger colorLegendActionBtn' id='deleteColorLegendBtn'><i class='material-icons' style='font-weight: bold;' title='delete'>delete</i></button>";
                } }
      ]
      
      

  });

  if(colorLegendTbl != null || colorLegendTbl != ''){
      $(colorLegendTbl.table().container()).on('click', 'button.colorLegendActionBtn', function () {
                //var data = forecastTable.row( $(this).parents('tr') ).data();
                buttonId = $(this).attr('id');
                var cell_clicked    = colorLegendTbl.cell(this).data();
                var row_clicked     = $(this).closest('tr');
                var row_object      = colorLegendTbl.row(row_clicked).data();

                console.log(row_object['ID']);
                console.log(buttonId);

                if(buttonId == 'editColorLegendBtn'){
                  if($("#editcolorModal").modal('show')){
                      $("input#colorId").val(row_object['ID']);
                      $("#editColorDescInput").val(row_object['DESCRIPTION']);
                      var count = countChar("#editColorDescInput",250);
                      $("#editColorDescInputLabel").html(count);
                  }
                }
                else if(buttonId == 'deleteColorLegendBtn'){
                  swal({
                        title: "Are you sure you want to delete this legend?",
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Yes, I am sure!',
                        cancelButtonText: "No, cancel it!",
                        closeOnConfirm: false,
                        closeOnCancel: false
                  },
                  function(isConfirm){
                    if (isConfirm){
                      $.ajax({
                        type: "POST",
                        dataType: "json",
                        data:{
                          legendId: row_object['ID']
                          // remarks: remarks
                        },
                        url: "ajax/maintenance/mrp_color_coding/delete/delete_legend.php",
                        success: function(response){
                          if(response == 1){
                            swal({
                              title: "Deleted!",
                              text: "",
                              type: "success"
                            },
                            function(isConfirm){
                              if(isConfirm){
                                // $("#editcolorModal").modal("hide");
                                loader();
                              }
                            });
                          }
                        }
                      });
                    } else {
                        swal("Saving cancelled", "", "error");
                    }
                  });
                }
      });
  }
}

function getColorTagData(){
  colorTagData = "";
  $.ajax({
    async: false,
    type: "POST",
    dataType: "json",
    url: "ajax/maintenance/mrp_color_coding/select/select_color_tags.php",
    success: function(response){
      colorTagData = response['data'];
    }
  });

  return colorTagData;
}

function getColorTagDT(tableId, dataArray){
  var colorTagTbl = $('#'+tableId).DataTable({
      // "ajax":{
      //         "url":"ajax/maintenance/mrp_color_coding/select/select_color_legend.php",
      //         "type":"post"
      //       },
      "data": dataArray,
      "columns":[
                { "data": "STOCK_NO", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "DESCRIPTION", render: function ( data,type,row ){
                  //return data;
                  return data;
                } },
                { "data": "TAG_TYPE", render: function ( data,type,row ){
                  //return data;
                  return data;
                } },
                { "data": "SUPPLIER", render: function ( data,type,row ){
                  //return data;
                  if(data == "" || data == null){
                    return "---";
                  }
                  else{
                    return data;
                  }
                  
                } },
                { "data": "CATEGORY", render: function ( data,type,row ){
                  //return data;
                  if(data == "" || data == null){
                    return "---";
                  }
                  else{
                    return data;
                  }
                } },
                { "data": "", "bSortable": false, render: function ( data,type,row ){
                  return "<button class='btn btn-xs bg-indigo colorTagActionBtn' id='editColorTagBtn'><i class='material-icons' style='font-weight: bold;' title='edit'>mode_edit</i></button><button class='btn btn-xs btn-danger colorTagActionBtn' id='deleteColorTagBtn'><i class='material-icons' style='font-weight: bold;' title='delete'>delete</i></button>";
                } }
      ],
      "createdRow":function ( row, data, index ) {
        //console.log(data);
        color = data.COLOR;

        if(color!=null){
          $(row).css({background: color});
        }
                            
      }
      
      

  });

  if(colorTagTbl != null || colorTagTbl != ''){
      $(colorTagTbl.table().container()).on('click', 'button.colorTagActionBtn', function () {
                //var data = forecastTable.row( $(this).parents('tr') ).data();
                buttonId = $(this).attr('id');
                var cell_clicked    = colorTagTbl.cell(this).data();
                var row_clicked     = $(this).closest('tr');
                var row_object      = colorTagTbl.row(row_clicked).data();

                console.log(row_object['ID']);
                console.log(buttonId);

                if(buttonId == 'editColorTagBtn'){
                  if($("#edittagModal").modal('show')){
                    $("input#invId").val(row_object['INVENTORY_ITEM_ID']);
                    $("#editTagColorDesc").val(row_object['DESCRIPTION']);
                    $("#editTagColorDesc").chosen({search_contains: true});
                    $.ajax({
                      type: "POST",
                      dataType: "json",
                      url: "ajax/maintenance/mrp_color_coding/select/select_color_legend.php",
                      success: function(response){
                        editdescString = "";
                        if(response["data"] != null){
                          $(response["data"]).each(function(){
                            var self = this;
                            console.log(self);
                            if(row_object['LEGEND_ID'] == self['ID']){
                              editdescString += "<option value='"+ self['ID'] +"' selected>"+self['DESCRIPTION']+"</option>";
                            }
                            else{
                              editdescString += "<option value='"+ self['ID'] +"'>"+self['DESCRIPTION']+"</option>";
                            }
                          });
                          $("#editTagColorDesc").html(editdescString);
                          $("#editTagColorDesc").trigger("chosen:updated");
                        }
            
                        // $("#tagcolorModalBody").waitMe("hide");


                      }
                    });
                  }

                  
                }
                else if(buttonId == 'deleteColorTagBtn'){
                  swal({
                        title: "Are you sure you want to delete this tagged material?",
                        text: "",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: '#DD6B55',
                        confirmButtonText: 'Yes, I am sure!',
                        cancelButtonText: "No, cancel it!",
                        closeOnConfirm: false,
                        closeOnCancel: false
                  },
                  function(isConfirm){
                    if (isConfirm){
                      $.ajax({
                        type: "POST",
                        dataType: "json",
                        data:{
                          INVENTORY_ITEM_ID: row_object['INVENTORY_ITEM_ID']
                          // remarks: remarks
                        },
                        url: "ajax/maintenance/mrp_color_coding/delete/delete_tag.php",
                        success: function(response){
                          if(response == 1){
                            swal({
                              title: "Deleted!",
                              text: "",
                              type: "success"
                            },
                            function(isConfirm){
                              if(isConfirm){
                                // $("#editcolorModal").modal("hide");
                                loader();
                              }
                            });
                          }
                        }
                      });
                    } else {
                        swal("Saving cancelled", "", "error");
                    }
                  });
                }
      });
  }
}

function tagColorFunc(type){
  console.log(type);
  
  
  $("#tagcolorModal").on("shown.bs.modal", function (e){
    $("#tagcolorModalBody").waitMe({effect : 'pulse', text : 'Loading...'});
    // return false;
    $("#tagType").val(type);
    var bySupplierDiv = document.getElementById("bySupplierDiv");
    var byCategoryDiv = document.getElementById("byCategoryDiv");
    var byStockNoDiv = document.getElementById("byStockNoDiv");
    if(type == 'by Supplier'){
      console.log('opened')
      bySupplierDiv.style.display = "block";
      byCategoryDiv.style.display = "none";
      byStockNoDiv.style.display = "none";

      $("#tagcolorModalTitle").html("TAG COLOR BY SUPPLIER");

      //get list
      $("#selSupp").chosen({search_contains: true});
      $.ajax({
        type: "POST",
        dataType: "json",
        url: "ajax/maintenance/mrp_color_coding/select/select_suppliers.php",
        success: function(response){
          suppString = "";
          if(response["data"] != null){
            $(response["data"]).each(function(){
              var self = this;
              suppString += "<option value='"+ self['VENDOR_ID'] +"'>"+self['VENDOR_NAME']+"</option>";
            });
            $("#selSupp").html(suppString);
            $("#selSupp").trigger("chosen:updated");
          }
        }
      });
    }
    else if(type == 'by Category'){
      bySupplierDiv.style.display = "none";
      byCategoryDiv.style.display = "block";
      byStockNoDiv.style.display = "none";

      $("#tagcolorModalTitle").html("TAG COLOR BY CATEGORY");
      $("#selCateg").chosen({search_contains: true});

      //get list
    }
    else if(type == 'by Stock No'){
      bySupplierDiv.style.display = "none";
      byCategoryDiv.style.display = "none";
      byStockNoDiv.style.display = "block";

      $("#tagcolorModalTitle").html("TAG COLOR BY STOCK NO");

      //get list
      $("#selStock").chosen({search_contains: true});
      $.ajax({
        type: "POST",
        dataType: "json",
        url: "ajax/maintenance/mrp_color_coding/select/select_stocknos.php",
        success: function(response){
          stockString = "";
          if(response["data"] != null){
            $(response["data"]).each(function(){
              var self = this;
              stockString += "<option value='"+ self['STOCK_NO'] + "->" + self["INVENTORY_ITEM_ID"] +"'>"+self['STOCK_NO']+"</option>";
            });
            $("#selStock").html(stockString);
            $("#selStock").trigger("chosen:updated");
          }
        }
      });
    }
    $("#tagColorDesc").chosen({search_contains: true});
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "ajax/maintenance/mrp_color_coding/select/select_color_legend.php",
        success: function(response){
          descString = "";
          if(response["data"] != null){
            $(response["data"]).each(function(){
              var self = this;
              console.log(self);
              descString += "<option value='"+ self['ID'] +"'>"+self['DESCRIPTION']+"</option>";
            });
            $("#tagColorDesc").html(descString);
            $("#tagColorDesc").trigger("chosen:updated");
          }
            
          $("#tagcolorModalBody").waitMe("hide");


        }
    });
    // 


  });
  $("#tagcolorModal").modal("show");
  

}

function loader(){
    $("div#divCardId").waitMe({effect : 'pulse', text : 'Loading...'});
    setTimeout(start, 275);
    setTimeout(stopLoader, 275);
    
    // console.log('hey');
    // $("#divCardId").waitMe('hide');

}
function stopLoader(){
    $("div#divCardId").waitMe('hide');
}

function hexToRgb(hex) {
  var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
  return result ? 
  // {
  //   r: parseInt(result[1], 16),
  //   g: parseInt(result[2], 16),
  //   b: parseInt(result[3], 16)
  // } 
  "rgb("+parseInt(result[1], 16)+", "+parseInt(result[2], 16)+", "+parseInt(result[3], 16)+")"
  : null;
}