$(document).ready(function(){
    
    start();

    $("#generateMrpBtn").on("click", function(){
      swal({
            title: "Are you sure you want to generate an MRP Report?",
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
                type: 'POST',
                dataType: "json",
                url: "ajax/mrp_reports/insert/insert_mrp.php",
                success: function(response){
                  console.log(response);
                  if(response == 1){
                    swal({title: "Success!",
                            text: "",
                            type: "success"},
                        function(isConfirm){
                            if(isConfirm){
                                var mrpSectionTables = $("#mrp_body").find('table');
                                $(mrpSectionTables).each(function(){
                                  var innerTblId = $(this).attr('id');
                                  if($.fn.DataTable.isDataTable( '#'+innerTblId ) ){
                                    $('#'+innerTblId).DataTable().clear().destroy();
                                  }
                                });
                                // tab = $(".nav-tabs .active > a").attr("href");
                                // if(tab != "#others"){
                                //   console.log($(tab).find('table').attr('id'));
                                // }
                                // else{

                                // }
                                // console.log( $(".nav-tabs .active > a").index());
                                start();
                                // 
                            }

                    });
                    
                  }
                  else if(response == 0){
                    swal("A problem has occured. Please call for assistance.", "", "error");
                  }
                  else if(response == 10){
                    console.log('empty data');
                  }
                }
              });
          
            } 
            else {
              swal("Cancelled", "", "error");
            }
      });
    });



    $("#infoModal").on("hidden.bs.modal", function (e){
      //$("#clickedColInfoTable").DataTable().clear().destroy();
      
      if($.fn.DataTable.isDataTable( '#clickedColInfoTable' ) ){
          $('#clickedColInfoTable').DataTable().clear().destroy();
          //console.log('existed');
      }
      var initiate = "<table id='clickedColInfoTable' class='table table-bordered'>"
          +"<thead id='clickedColInfoTableHead'>"
          +"</thead>"
          +"<tbody id='clickedColInfoTableBody'>"
          +"</tbody>"
        +"</table>";
      $("#infoTitle").html("INFO: ");
      //$("#infoDivId").html(initiate);
      //$("#clickedColInfoTable").remove();
    });

  
        //$('#mrpTable2').DataTable().search( 'CONSIGNED' ).draw();
  });

  function start(){
    //get last generation date
    var getGenDate = getGenDateFunc();
    $("#mrpGenDate").html("<b>Report as of: </b>"+getGenDate);

    $('.nav-tabs a[href="#showAll"]').tab('show');
    var responseData = getMrpData();
    console.log(responseData);

    getMrpDataTable("all_Table", responseData);


    $('a[class="mrp-tab"]').on('shown.bs.tab', function (e) {
            var mrpSectionTables = $("#mrp_body").find('table');
            $(mrpSectionTables).each(function(){
              var innerTblId = $(this).attr('id');
              if($.fn.DataTable.isDataTable( '#'+innerTblId ) ){
                $('#'+innerTblId).DataTable().clear().destroy();
              }
            });
              
            var activeTabId = $(this).attr('href');
            var activeTabTables = $(activeTabId).find('table');
            $(activeTabTables).each(function(){
              var activeInnerTblId = $(this).attr('id');
              console.log($(this).attr('id'));
              var activeInnerTblIdSplit = activeInnerTblId.split("_");
              var replaceValue = activeInnerTblIdSplit[0].replace(/-/g, ' ');
              var findValue = replaceValue.toUpperCase();
              console.log(findValue);

              if(findValue === 'ALL'){
                getMrpDataTable(activeInnerTblId, responseData);
              }
              else{
                var data = responseData.filter(function (item) {
                  return item.REMARKS === findValue;
                });
                getMrpDataTable(activeInnerTblId, data);
              }



              
            });
          });  
  }

  function getMrpData(){
    responseData = "";
    $.ajax({
        async: false,
        type: "POST",
        dataType: "json",
        url: "ajax/mrp_reports/select/select_mrp_others.php", 
        success: function(response){
          responseData = response.data;
         
          //return responseData;  
          //returnMrpData(responseData);     
        }
    });
    return responseData;
  }


  function getMrpDataTable(tableId, dataArray){
    console.log(tableId);
    console.log(dataArray);
    if(dataArray.length != 0){
      console.log("not null");
      if($.fn.DataTable.isDataTable( '#'+tableId ) ){
          $('#'+tableId).DataTable().clear().destroy();
          //console.log('existed');
          // getMrpDataTable(tableId, dataArray);
      }
      else{
        console.log('creating table');
         var table = $('#'+tableId).DataTable({
            "data": dataArray,       
            "columns":[
                //{ "data": "REMARKS", render: function ( data,type,row ){
                  //return data;
                //} },
                { "data": "SEGMENT1", render: function ( data,type,row ){
                  //return "<a>"+data+"</a>";
                  if(data.length > 12){
                    subStringSegment1 = data.substr(0,9) + "...";
                  }
                  else{
                    subStringSegment1 = data;
                  }
                  // console.log(subStringSegment1);
                  return "<span class='hover-desc'>"+subStringSegment1+"<span class='hover-desc-text'>"+data+"</span></span>"
                } },
                { "data": "SHORTENED_DESC", render: function ( data,type,row ){
                  //return data;
                  return "<span class='hover-desc'>"+data+"<span class='hover-desc-text'>"+row.DESCRIPTION+"</span></span>"
                } },
                { "data": "UOM", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "TOTAL_ISSUANCE", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "AVERAGE_PAST_3MOS", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "AVERAGE_PAST_6MOS", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "LEAD_TIME", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "LEADTIME_QTY", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "SAFETY_STOCKS", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "INTRANSIT", render: function ( data,type,row ){
                  return "<a>"+data+"</a>";
                } },
                { "data": "IQC", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "IQC_ACCEPT", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "REPACK", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "EOH", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "RESERVED", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "COMMITTED_QTY", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "AVAILABLE_QTY", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "REORDER_QTY", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "REORDER_DATE", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "DAYS_INV", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "QTY_TO_ORDER", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "PROJECTED_EOH", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "COMP_QTY", render: function ( data,type,row ){
                  return "<a>"+data+"</a>";
                } },
                
                { "data": "MOQ", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "ITEM_COST", render: function ( data,type,row ){
                  return data;
                } }
            ],
            "createdRow":function ( row, data, index ) {
              //console.log(data);
              color = data.STATUS_CODE;

              if(color!=null){
                $(row).css({background: 'white'});
              }
                            
            },
            "bSort" : false,
            "bPaginate": false,
            
            //"ordering":false,
            //"scrollCollapse": true,
            "scrollY":"400px",
            
            "scrollX":true,
            "fixedColumns":   {
              leftColumns : 3
            },
            "autoWidth": false,
            // "autoWidth"  : false,
            columnDefs: [
              { className: "dt-body-right", "targets": [3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,19,20,21,22,23] }
              // { width: "10%", "targets": [1] }
            
            ],
            dom: 'Bfrtip',
            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {extend : 'excel',
                    title : function() {
                        return "MRP REPORT";
                    },
                    //exportOptions: { orthogonal: 'export' }
                    exportOptions: {
                         format: {
                            body: function ( data, row, column, node ) {

                                var dataToReturn = table.row(row).data();
                                //if(dataToReturn.REORDER_DATE == 'NOW' || dataToReturn.INV_DEPT_DATE == 'NOW' || dataToReturn.DAYS_INV == "0"){
                                if(column == 0){
                                  return dataToReturn.SEGMENT1;
                                }
                                else if(column == 1){
                                  return dataToReturn.DESCRIPTION;
                                }
                                else if(column == 13){
                                  return dataToReturn.INTRANSIT;
                                }
                                else if(column ==  22){
                                  return dataToReturn.COMP_QTY;
                                }
                                else{
                                  return data;
                                }
                                //}
                            } 

                         }
                        // rows: function ( idx, data, node ) {
                        //     //console.log(data.DAYS_INV);
                        //     return data.REORDER_DATE === 'IMMEDIATE' || data.INV_DATE_DEPT === 'IMMEDIATE' || data.DAYS_INV === "0" ?
                        //         true : false;
                        // }
                     }
                },
                {extend : 'pdfHtml5',
                    title : function() {
                        return "MRP REPORT";
                    },
                    
                    orientation : 'landscape',
                    pageSize :'A2',
                    titleAttr : 'PDF',
                    //exportOptions: { orthogonal: 'export' }
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
                                 //Strip $ from salary column to make it numeric
                                //return row + " " + column;
                                var dataToReturn = table.row(row).data();
                                if(column == 0){
                                  return dataToReturn.SEGMENT1;
                                }
                                else if(column == 1){
                                  return dataToReturn.DESCRIPTION;
                                }
                                else if(column == 13){
                                  return dataToReturn.INTRANSIT;
                                }
                                else if(column ==  22){
                                  return dataToReturn.COMP_QTY;
                                }
                                else{
                                  return data;
                                }
                            } 
                        }
                        // rows: function ( idx, data, node ) {
                        //     //console.log(data.DAYS_INV);
                        //     return data.REORDER_DATE === 'IMMEDIATE' || data.INV_DATE_DEPT === 'IMMEDIATE' || data.DAYS_INV === "0" ?
                        //         true : false;
                        // }
                    }
                }
            ],
            fnInitComplete: function () {
              $("th").tooltip({
                container: 'body'
              });

            }
        }); 
      }
    }
    else{
      console.log('null');
      if($.fn.DataTable.isDataTable( '#'+tableId ) ){
          $('#'+tableId).DataTable().clear().destroy();
      }
      else{
        console.log('creating null table');
        var table = $('#'+tableId).DataTable({"data":dataArray, "bSort":false, "bPaginate": false, "scrollX":true});
      }
    }
    // $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    // table.columns.adjust();

    // table.columns.adjust().draw();

    if(table != null || table != ''){
      //table.columns.adjust().responsive.recalc();
      $($.fn.dataTable.tables( true ) ).css('width', '100%');
        $($.fn.dataTable.tables( true ) ).DataTable().columns.adjust().draw();
      $(table.table().container()).on('click', 'td', function () {
        var cell_clicked    = table.cell(this).data();
        var row_clicked     = $(this).closest('tr');
        var row_object      = table.row(row_clicked).data();
        var idx = table.cell( this ).index().column;
        var title = table.column( idx ).header();
        var columnHeader = $(title).html();
        var modalTitle = row_object['SEGMENT1'];
        console.log(row_object);
        console.log('CLICKED');
        console.log($(title).html());
        var dataTableValue = null;
        if(columnHeader == 'GROSS REQUIREMENT'){
          console.log(row_object['INVENTORY_ITEM_ID']);
          //return false;
          $.ajax({
            type: "POST",
            dataType: "json",
            data:{
              INVENTORY_ITEM_ID: row_object['INVENTORY_ITEM_ID']
            },
            url: "ajax/mrp_reports/select/select_gross_info.php", 
            success: function(response){
              console.log(response);
              var grossData = response.data;
              $("#clickedColInfoTableHead").html("");
              //var clickedColInfoTableHead = "";
              var clickedColInfoTableHead = "<tr>"
                                + "<th>CUSTOMER</th>"
                                + "<th>PKG</th>"
                                + "<th>DEVICE</th>"
                                + "<th>DESCRIPTION</th>"
                                + "<th>MATERIALS</th>"
                                + "<th>COMP</th>"
                                + "<th>COMP_QTY</th>"
                                + "<th>MONTH</th>"
                                + "<th>QTY</th>"
                                + "</tr>";
              $("#clickedColInfoTableHead").html(clickedColInfoTableHead);
              if($("#infoModal").modal('show')){
                $("#infoTitle").html(modalTitle+" GROSS REQUIREMENT INFO:");
                console.log('modal shown');
                if($.fn.DataTable.isDataTable( '#clickedColInfoTable' ) ){
                  $('#clickedColInfoTable').DataTable().clear().destroy();
                  console.log('existing');
                }
                if(grossData.length != 0){
                  var clickedColInfoTable = $('#clickedColInfoTable').DataTable({
                    "data": grossData,       
                    "columns":[
                        { "data": "CUSTOMER", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "PKG", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "DEVICE", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "DESCRIPTION", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "MATERIALS", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "COMP", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "COMP_QTY", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "MONTH", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "QTY", render: function ( data,type,row ){
                         return data;
                        } }
                    ],
                    "bSort" : false,
                    "bPaginate": false,
                    //"scrollCollapse": true,
                    //"ordering":false,
                    "scrollX":true,
                    //"scrollY":"700px",
                    "fixedColumns":   {
                      leftColumns : 4
                    }
                  }); 
                }
                else{
                  //console.log('creating null table');
                  var clickedColInfoTable = $('#clickedColInfoTable').DataTable({"data":grossData, "bSort":false, "bPaginate": false, "scrollX":true});
                }
              }
            }
          });
        }
        else if(columnHeader == 'IN TRANSIT'){
          $.ajax({
            type: "POST",
            dataType: "json",
            data:{
              INVENTORY_ITEM_ID: row_object['INVENTORY_ITEM_ID']
            },
            url: "ajax/mrp_reports/select/select_intransit_info.php", 
            success: function(response){
              console.log(response);
              var intransData = response.data;
              $("#clickedColInfoTableHead").html("");
              //var clickedColInfoTableHead = "";
              var clickedColInfoTableHead = "<tr>"
                                + "<th>STOCK NO</th>"
                                + "<th>SUPPLIER</th>"
                                + "<th>UOM</th>"
                                + "<th>QTY</th>"
                                + "<th>PO NO</th>"
                                + "<th>PO DATE</th>"
                                + "<th>PR NO</th>"
                                + "<th>PR DATE</th>"
                                + "<th>DELIVERY DATE</th>"
                                + "<th>DELIVERED</th>"
                                + "<th>BALANCE</th>"
                                + "</tr>";
              $("#clickedColInfoTableHead").html(clickedColInfoTableHead);
              if($("#infoModal").modal('show')){
                $("#infoTitle").html(modalTitle+" INTRANSIT INFO:");
                console.log('modal shown');
                if($.fn.DataTable.isDataTable( '#clickedColInfoTable' ) ){
                  $('#clickedColInfoTable').DataTable().clear().destroy();
                  console.log('existing');
                }
                if(intransData.length != 0){
                  var clickedColInfoTable = $('#clickedColInfoTable').DataTable({
                    "data": intransData,       
                    "columns":[
                        { "data": "STOCK_NO", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "SUPPLIER", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "UOM", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "QTY", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "PO_NO", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "PO_DATE", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "PR_NO", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "PR_DATE", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "DELIVERY_DATE", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "DELIVERED", render: function ( data,type,row ){
                          return data;
                        } },
                        { "data": "BALANCE", render: function ( data,type,row ){
                         return data;
                        } }
                    ],
                    "bSort" : false,
                    "bPaginate": false,
                    //"scrollCollapse": true,
                    //"ordering":false,
                    "scrollX":true,
                    //"scrollY":"700px",
                    "fixedColumns":   {
                      leftColumns : 4
                    }
                  }); 
                }
                else{
                  //console.log('creating null table');
                  var clickedColInfoTable = $('#clickedColInfoTable').DataTable({"data":intransData, "bSort":false, "bPaginate": false, "scrollX":true});
                }
              }
            }
          });
        }
        //return false;
        else if(columnHeader == 'STOCK NO'){
          $.ajax({
            type: "POST",
            dataType: "json",
            data:{
              INVENTORY_ITEM_ID: row_object['INVENTORY_ITEM_ID']
            },
            url: "ajax/mrp_reports/select/select_stockno_info.php", 
            success: function(response){
              console.log(response);
              $("#clickedColInfoTableHead").html("");
              $("#clickedColInfoTableBody").html("");
              var header = response.header,
              body = response.body;
              //title = response.title;
              if($("#infoModal").modal('show')){
              $("#infoTitle").html(modalTitle+" STOCK NO INFO:");
              if(header.length != 0 && body.length != 0){
                
                var headerString = "<tr>";
                $(header).each(function(){
                  self = this;

                  headerString += "<th>"+self+"</th>";
                });

                headerString += "</tr>";
                $("#clickedColInfoTableHead").html(headerString);

                var bodyString = "<tr>";
                $(body).each(function(){
                  self = this;

                  bodyString += "<td>"+self+"</td>";
                });

                bodyString += "</tr>";
                $("#clickedColInfoTableBody").html(bodyString);


                var clickedColInfoTable = $('#clickedColInfoTable').DataTable({"bSort":false, "bPaginate": false, "scrollX":true});

              }
              else{
                $("#infoDivId").html("nothing to see here.");
              }
              }
            }
          });
        }
      });
    }
   
  }

  

  
  function getGenDateFunc(){
    genDate = "";
    $.ajax({
        async: false,
        type: "POST",
        dataType: "json",
        url: "ajax/mrp_reports/select/select_mrp_gendate.php", 
        success: function(response){
          genDate = response['UPDATED_ON'];
         
          //return responseData;  
          //returnMrpData(responseData);     
        }
    });
    return genDate;

  }