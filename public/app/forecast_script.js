$(document).ready(function() {
    triggerCustomerDisplaySelection();
    
    forecastModalButtons();

    //from forecastModal on show
    selectCustomer();
    selectCustomerOnChange();
    selectPkgOnChange();
    selectBdNoOnChange();
    //forecastmodal on show
    $("#forecastModal").on("shown.bs.modal", function (e){
      getDeviceHistoryCalendarDataTable(0, 0, 'byDevice', 0);
      getDeviceDataTable(0, 0, 'byDevice', 0);
      
      $('#forecastForm').children('div').eq(0).addClass('activeStepDiv');
      forecastModalDisplay();
      
      
      $("#MonthQtyTableBody").on("click", "td:first-child", function() {
            id = $(this).attr("id");
            $("#selectedMonth-"+id).on("change", function(){
              getOldQty(id);
            });
            
      });


        
    });
    //forecastmodal on hide
    $("#forecastModal").on("hidden.bs.modal", function (e){
        if($.fn.DataTable.isDataTable( '#deviceListTable' ) ){
          $('#deviceListTable').DataTable().clear().destroy();
        }
        if($.fn.DataTable.isDataTable( '#chosenDeviceListTable' ) ){
          $('#chosenDeviceListTable').DataTable().clear().destroy();
        }
        if($.fn.DataTable.isDataTable( '#previewTable' ) ){
          $('#previewTable').DataTable().clear().destroy();
        }
        $("#MonthQtyTableBody").html("");
        
        if($.fn.DataTable.isDataTable( '#DeviceHistoryCalendarTable' ) ){
          $('#DeviceHistoryCalendarTable').DataTable().clear().destroy();
          $("#DeviceHistoryCalendarTableBody").html("");
          $("#DeviceHistoryCalendarTableHead").html("");
        }
        if($.fn.DataTable.isDataTable( '#forecastTable' ) ){
          $('#forecastTable').DataTable().clear().destroy();
            
          
        }
        getForecastDataTable();
        
        
        $(this)
        .find("input,textarea,select")
        .val('')
        .end();
        $("#byDevice").val("byDevice");
        $("#byBD").val("byBD");
        
    });
    //deviceListTable on click (INPUT manipulation)
    var DELAY = 300, clicks = 0, timer = null;
    $("#deviceListTable").on('click', 'td:last-child', function () {
        
     tr = $(this.parentNode);
      clicks++;  //count clicks

      if(clicks === 1) {
      
        timer = setTimeout(function() {
        
          // tr = $(this);
          console.log(tr);
          dataArray = $("#deviceListTable").DataTable().rows().data();
          console.log(dataArray);
          if(tr.hasClass('selected')){
            data = $("#deviceListTable").DataTable().row(tr.index()).data();
            dataArray[data.ARRAY_NO]['INPUT'] = "";
          }
          clicks = 0;             //after action performed, reset counter
        }, DELAY);
      } 
      else {
        clearTimeout(timer);    //prevent single-click action
        //perform double-click action
        //clicks = 0;             //after action performed, reset counter
      }

    });
    //add month in adding forecast
    $("#addMonthQtyBtn").on("click", function(){
      var row_id = $("#MonthQtyTable tr:last").attr("id");
      row_idSplit = row_id.split("-");
      var last_id = row_idSplit[1];
      new_id = parseInt(last_id) + 1;
  
      monthOptions(new_id, "addEntry");
    });
    //add forecast saving buttons/options
    $("#save_exitButton").on("click", function(){
      save('saveAndExit');
    });
    $("#save_contButton").on("click", function(){
      save('saveAndCont');
    });

    //save add month forecast button on click
    $("#saveAddMonthForecastBtn").on("click", function(){
        console.log('save');
        customer = $("#cusAddMonthForecastId").val();
        package = $("#pkgAddMonthForecastId").val();
        device = $("#devAddMonthForecastId").val();
        custDevName = $("input#custdevAddMonthForecastId").val();
        deviceVariant = $("input#devvarAddMonthForecastId").val();
        inventory_item_id = $("input#invidAddMonthForecastId").val();
        device_description = $("input#devdescAddMonthForecastId").val();
        console.log(inventory_item_id);
        console.log(package);
        console.log(device);
        console.log(device_description);

        inputBlankCounterAddMonth = 0;
        var table = $("#addMonthForecastTableBody");
        table.find('tr').each(function (j, el) {
            var $tds = $(this).find('td'),
            //month = $tds.eq(0).find("select").val(),
            percentage = $tds.eq(1).find("input").val();
            qty = $tds.eq(2).find("input").val();
            capacity = $tds.eq(3).find("input").val();
            if($tds.eq(4).find("textarea").length > 0){
              remarks = $tds.eq(4).find("textarea").val();
              if(qty == "" || capacity == "" || remarks == ""){
                inputBlankCounterAddMonth++;
              }
            }
            else{
              if(qty == "" || capacity == ""){
                inputBlankCounterAddMonth++;
              }
            }
            //countqty = (parseInt(dataArray[i]['INPUT'])/100) * qty;
            
                           
        });
        if(custDevName == '' || custDevName == null){
          alert("Customer Device Name field cannot be empty.");
          return false;
        }

        if(inputBlankCounterAddMonth != 0){
          alert("Please do not leave field/s blank.");
          return false;
        }
        else{
        
  
        //previewTableHead = '';
        //i = 0;
        // j = 0;
          dataArray1 = new Array();
  
        
          table.find('tr').each(function (j, el) {
            var $tds = $(this).find('td'),
            month = $tds.eq(0).find("select").val(),
            qty = $tds.eq(2).find("input").val(),
            capacity = $tds.eq(3).find("input").val(),
            percentage = $tds.eq(1).find("input").val();
                          // countqty = (parseInt(dataArray[i]['INPUT'])/100) * qty;
            if($tds.eq(4).find("textarea").length > 0){
              remarks = $tds.eq(4).find("textarea").val();
            }
            else{
              remarks = "";
            }
            dataArray1.push({
              INVENTORY_ITEM_ID: inventory_item_id,
              CUSTOMER: customer,
              PKG: package,
              DEVICE_VARIANT: deviceVariant,
              DEVICE: device,
              DEVICE_DESCRIPTION: device_description,
              MONTH: month,
              CAPACITY: capacity,
              //QTY: countqty
              // QTY: dataArray[i]['INPUT']
              QTY: qty,
              PERCENT: percentage,
              REMARKS: remarks
            });
                           
          });

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

              saveAddMonth(dataArray1);
            } else {
              swal("Saving cancelled", "", "error");
            }
          });
        }

        
       
    });
    //close addmonthforecastmodal
    $("#addMonthForecastModal").on("hidden.bs.modal", function (e){
      type = $('input#addMonthForecastType').val();
      if(type == 'fromIndiv'){
        if($.fn.DataTable.isDataTable( '#forecastTable' ) ){
          $('#forecastTable').DataTable().clear().destroy();
        }
        getForecastDataTable(); 
      }
      else{
        headRowString = "<tr>"
        + "<th>Customer</th>"
        + "<th>Package</th>"
        + "<th>Device</th>"
        + "<th>Action</th>"
        + "</tr>";
        if($.fn.DataTable.isDataTable( '#addMonthForecastByBatchDispTable' ) ){
            $('#addMonthForecastByBatchDispTable').DataTable().clear().destroy();
        }
        $("#addMonthForecastByBatchDispTableHead").html(headRowString);
        $("#addMonthForecastByBatchDispTableBody").html("");
        dataToPass = {
          customer: $("input#addMonthForecastByBatchDispCustomer").val(),
          pkg: $("input#addMonthForecastByBatchDispPkg").val(),
          device: $("input#addMonthForecastByBatchDispDevice").val()
        };
        $("#addMonthForecastByBatchDispModalBody").waitMe({effect : 'pulse', text : 'Loading...'});
        getDeviceBatchTbl(dataToPass);
      }
      
    });
    //close addMonthForecastByBatchDispModal
    $("#addMonthForecastByBatchDispModal").on("hidden.bs.modal", function(e){
      if($.fn.DataTable.isDataTable( '#forecastTable' ) ){
        $('#forecastTable').DataTable().clear().destroy();
      }
      getForecastDataTable(); 
    });
    //device history and changing qty
    $("#DeviceHistoryCalendarTable").on("click","a.changeMonthQty", function(){
        dist_id = $(this).attr('id');

        row_index = dist_id.split("-")[0];
        col_index = dist_id.split("-")[1];
        inventory_id = $("#DeviceHistoryCalendarTable").DataTable().cell(row_index, 0).data();
        customer = $("#DeviceHistoryCalendarTable").DataTable().cell(row_index, 1).data();
        pkg = $("#DeviceHistoryCalendarTable").DataTable().cell(row_index, 2).data();
        device_variant = $("#DeviceHistoryCalendarTable").DataTable().cell(row_index, 3).data();
        device = $("#DeviceHistoryCalendarTable").DataTable().cell(row_index, 5).data();
        month = $($("#DeviceHistoryCalendarTable").DataTable().columns(col_index).header()).html();
        console.log(month);
        //return false;

        $.ajax({
          type: "POST",
          dataType: "json",
          data:{
            inventory_id: inventory_id,
            customer: customer,
            pkg: pkg,
            device_variant: device_variant,
            device: device,
            month: month
          },
          url: "ajax/forecast/select/select_toupdate_device.php",
          success: function(response){
            console.log(response);
            if($("#changeQtyModal").modal("show")){
              changeQtyString = "<input type='hidden' id='distIdOfForecastQtyToChange' value='"+response.DIST_ID+"'>"
              +"<b>DEVICE: </b>"+response.DEVICE+"<br/><b>MONTH: </b>"+response.MONTH+"<br/><b>FORECAST QTY: </b>"
              +response.QTY+"<br/><b>PLAN QTY: </b>"+response.CAPACITY+"<br/><b>PERCENTAGE: </b>"+response.MTL_ALLOCATION_PERCENTAGE
              +"<div style='padding:20px;'></div>"
              +"<label>NEW FORECAST QTY</label>"
              +"<input type='number' class='form-control' id='changeMonthForecastQtyInput' value='"+response.QTY+"'>"
              +"<br/>"
              +"<label>NEW PLAN QTY</label>"
              +"<input type='number' class='form-control' id='changeMonthPlanQtyInput' value='"+response.CAPACITY+"'>"
              +"<br/>"
              +"<label>NEW PERCENTAGE</label>"
              +"<input type='number' class='form-control' id='changeMonthPercentageInput' value='"+response.MTL_ALLOCATION_PERCENTAGE+"'>"
              +"<br/>"
              +"<label>REMARKS</label>"
              +"<textarea class='form-control' id='changeMonthForecastRem' maxlength='250'></textarea>"
              +"<label id='changeMonthRemLabel'>250</label>";
              $("#changeQtyModalDesc").html(changeQtyString);

              $("textarea#changeMonthForecastRem").on("keyup change paste", function(event){
                    // console.log('nagalaw');
                    var count = countChar(this,250);
                    $("#changeMonthRemLabel").html(count);
              });
            }
          }
        });
    });



    $("#saveChangedMonthForecastQty").on("click", function(){
        qtyInputValue = $("#changeMonthForecastQtyInput").val();
        dist_idHidden = $("input#distIdOfForecastQtyToChange").val();
        changeMonthForecastRem = $("#changeMonthForecastRem").val();
        planInputValue = $("#changeMonthPlanQtyInput").val();
        percentageInputValue = $("#changeMonthPercentageInput").val();
        console.log(changeMonthForecastRem);
        if((qtyInputValue == "" || qtyInputValue == null) && (changeMonthForecastRem == "" || changeMonthForecastRem == null) && (planInputValue == "" || planInputValue == null) && (percentageInputValue == "" || percentageInputValue == null)){
          alert("You cannot leave the input field blank.");
        }
        else if((qtyInputValue == "" || qtyInputValue == null) || (changeMonthForecastRem == "" || changeMonthForecastRem == null) || (planInputValue == "" || planInputValue == null) || (percentageInputValue == "" || percentageInputValue == null)){
          alert("You cannot leave the input field blank.");
        }
        else{
          console.log(qtyInputValue);
          console.log(dist_idHidden);

          swal({
            title: "Are you sure you want to change the quantity on this month and device?",
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
                data:{
                  dist_id: dist_idHidden,
                  qty: qtyInputValue,
                  plan: planInputValue,
                  percentage: percentageInputValue,
                  remarks: changeMonthForecastRem
                },
                url: "ajax/forecast/update/update_forecast_qty.php",
                success: function(response){
                  console.log(response);
                  if(response == 1){
                    var customerValue = $("#customerInput").val();
                    //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
                    var pkgValue = $("#pkgInput").val();
                    //var pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
                    var searchBy = $("input[name='devSearchType']:checked").val();
                    getDeviceHistoryCalendarDataTable(customerValue, pkgValue, searchBy, $("#bdInput").val());
                    swal("Success", "", "success");
                    $("#changeQtyModalDesc").html("");
                    $("#changeQtyModal").modal('hide');
                  }
                }
              });
          
            } 
            else {
                    swal("Cancelled", "", "error");
            }
          });

        }

    });

    $("#changeQtyModal").on("hidden.bs.modal", function(e){
      $("#changeQtyModalDesc").html("");
    });
      

    $("#DeviceHistoryCalendarTable").on("click","a.zeroOutQtyCalendarFunc", function(){
        index =$(this).attr('id');
        deviceHistCalendarData = $("#DeviceHistoryCalendarTable").DataTable().row(index).data();
        
        i=0;
        deviceHistCalendarFinalData = {};
        monthsArray = [];

        $(deviceHistCalendarData).each(function(){
          if(i < 6){
            deviceHistCalendarFinalData[$($("#DeviceHistoryCalendarTable").DataTable().columns(i).header()).html()] = $("#DeviceHistoryCalendarTable").DataTable().cell(index, i).data();
          }
          else if(i == (deviceHistCalendarData.length - 1)){
            //deviceHistCalendarFinalData[$($("#DeviceHistoryCalendarTable").DataTable().columns(i).header()).html()] = $($("#DeviceHistoryCalendarTable").DataTable().cell(index, i).node()).find('a').html();
          }
          else{
            if($($("#DeviceHistoryCalendarTable").DataTable().cell(index, i).node()).find('a').html() != "---"){
              //monthsArray[$($("#DeviceHistoryCalendarTable").DataTable().columns(i).header()).html()] = $($("#DeviceHistoryCalendarTable").DataTable().cell(index, i).node()).find('a').html();
              monthsArray.push($($("#DeviceHistoryCalendarTable").DataTable().columns(i).header()).html());
            }
            
          }
          i++;
        });
        deviceHistCalendarFinalData['Months'] = monthsArray;
        console.log(deviceHistCalendarFinalData);

        swal({
          title: "Are you sure you want to zero out quantities on this device?",
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
              data: {
                array: deviceHistCalendarFinalData
              },
              url: "ajax/forecast/update/update_forecast_device_calendar.php", 
              success: function(response){
                if(response == 1){
                  var customerValue = $("#customerInput").val();
                  //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
                  var pkgValue = $("#pkgInput").val();
                  //var pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
                  var searchBy = $("input[name='devSearchType']:checked").val();
                  getDeviceHistoryCalendarDataTable(customerValue, pkgValue, searchBy, $("#bdInput").val());
                  swal("Success", "", "success");
                }
                console.log(response);
              }
            });
          
          } 
          else {
                  swal("Cancelled", "", "error");
          }
        });
    });
    //forecast approval
    $("#mainPageApproveBtn").on("click", function(e){
      // console.log($("input#user").val());
      var user_admin = $("input#user_admin").val();
      openApproveForecastModal('main', '', '', '', user_admin);
    });
    $("#approveForecastModal").on("hidden.bs.modal", function(e){
      $('#approveForecastTable').DataTable().clear().destroy();
      if($.fn.DataTable.isDataTable( '#forecastTable' ) ){
          $('#forecastTable').DataTable().clear().destroy();
      }
        getForecastDataTable();
    });
    //inquiry and info modal
    $("#inquiryModal").on("hidden.bs.modal", function(e){
      if($.fn.DataTable.isDataTable( '#materialsInfoTable' ) ){
          $('#materialsInfoTable').DataTable().clear().destroy();
      }
      if($.fn.DataTable.isDataTable( '#checkedColInfoTable' ) ){
          $('#checkedColInfoTable').DataTable().clear().destroy();
      }

    });
    $("#infoModal").on("hidden.bs.modal", function (e){
      if($.fn.DataTable.isDataTable( '#clickedColInfoTable' ) ){
          $('#clickedColInfoTable').DataTable().clear().destroy();
      }
      var initiate = "<table id='clickedColInfoTable' class='table table-bordered'>"
          +"<thead id='clickedColInfoTableHead'>"
          +"</thead>"
          +"<tbody id='clickedColInfoTableBody'>"
          +"</tbody>"
        +"</table>";
      $("#infoTitle").html("INFO: ");
    });


  
  
  });
  
  function triggerCustomerDisplaySelection(){
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "ajax/forecast/select/select_customers.php", 
        success: function(response){
         
          customerOptions = response;
          customerOptionsString= "";
          i = 0;
          customerOptionsString+= "<select class='form-control' style='width:25%;' id='customerSelectDisplay'>";
          customerOptionsString+= "<option value='NULL' selected>ALL</option>";
          $(customerOptions).each(function(){
            self = this;
            if(i == 0){
              customerOptionsString += "<option value='"+self.CUSTOMER+"'>"+self.CUSTOMER+"</option>";
            }
            else{
              customerOptionsString += "<option value='"+self.CUSTOMER+"'>"+self.CUSTOMER+"</option>";
            }
            i++;
            
          });
          customerOptionsString+= "</select>";
          $("#divForCustomerSelectDisplay").html(customerOptionsString);
          getForecastDataTable();
          $("#customerSelectDisplay").on("change keyup", function(e){
            if($.fn.DataTable.isDataTable( '#forecastTable' ) ){
               $('#forecastTable').DataTable().clear().destroy();
            }
            //$('#forecastTable').DataTable().clear().destroy();
            getForecastDataTable();
          });
        }
    });
  }

  function getForecastDataTable(){
    $("div#forecastCardDivId").waitMe({effect : 'pulse', text : 'Loading...'});
    $("#forecastTableHead").html("");
    $("#forecastTableBody").html("");

    is_mis = $("input#is_mis").val();

    selectedCustomer = $("#customerSelectDisplay").val();
    $.ajax({
        type: "POST",
        dataType: "json",
        data: {customer:selectedCustomer},
        url: "ajax/forecast/select/select_forecast_2.php", 
        success: function(response){
          data = response['data'];
          if(data['IS_ARRAY'] == 1){
            customerArr = data['CUSTOMER'];
            monthsArr = data['MONTHS'];
            pkgArr = data['PKG'];
            deviceArr = data['DEVICE'];
            monthlyQtyArr = data['MONTHLY_QTY'];

            indexString = "7";
            index = 7;
            j=1;
            monthsArrCount = monthsArr.length;
            console.log(monthsArrCount);

            var date = new Date();
            var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            var currentMonth = months[date.getMonth()]+" "+date.getFullYear();
            console.log(currentMonth.toUpperCase());

            headRowString = "<tr>"
            + "<th rowspan = '2'>Item ID</th>"
            + "<th rowspan = '2'>Customer</th>"
            + "<th rowspan = '2'>Package</th>"
            + "<th rowspan = '2'>Customer Device Name</th>"
            + "<th rowspan = '2'>Device</th>"
            + "<th rowspan = '2'>Device Variant</th>"
            + "<th rowspan = '2'>Device Description</th>";

            $(monthsArr).each(function(){
              headRowString += "<th colspan = '3'>"+this+"</th>";
            });
            headRowString += "<th rowspan = '2'>Action</th>"

            headRowString += "</tr>"
            + "<tr>";

            $(monthsArr).each(function(){
              headRowString += "<th>Forecast Qty</th>"
              + "<th>Plan Qty</th>"
              + "<th>Actual Qty</th>";

                  
              if(j != monthsArrCount){
                index = index + 4;
                indexString += ", "+index;
              }
              j++;
            });
            headRowString += "</tr>";

            bodyRowString = "";

            $(customerArr).each(function(){
              customerVal = this;
              $(pkgArr[customerVal]).each(function(){
                pkgVal = this;
                $(deviceArr[customerVal][pkgVal]).each(function(){
                  deviceInvId = this['INVENTORY_ITEM_ID'];
                  deviceVal = this['DEVICE'];
                  deviceVar = this['DEVICE_VARIANT'];
                  deviceCusto = this['CUST_DEV_NAME'];
                  deviceDesc = this['DEVICE_DESCRIPTION'];
                  // deviceInfo = data['DEVICE_INFO'][customerVal][pkgVal][deviceVal];
                  found = 0;
                  for(i=0; i<monthsArr.length; i++){
                    monthVal = monthsArr[i];
                    if(monthlyQtyArr[customerVal][pkgVal][deviceVal][monthVal] != null){
                      found = 1;
                      break;
                    }
                    else{
                      found = 0;
                      continue;
                    }
                  }
                  if(found == 1){
                                    
                    bodyRowString += "<tr>";
                    bodyRowString += "<td>"+deviceInvId+"</td>";
                    bodyRowString += "<td>"+customerVal+"</td>";
                    bodyRowString += "<td>"+pkgVal+"</td>";
                    bodyRowString += "<td>"+deviceCusto+"</td>";
                    bodyRowString += "<td>"+deviceVal+"</td>";
                    bodyRowString += "<td>"+deviceVar+"</td>";
                    bodyRowString += "<td>"+deviceDesc+"</td>";
                    $(monthsArr).each(function(){
                      monthVal = this;

                      if(monthlyQtyArr[customerVal][pkgVal][deviceVal][monthVal] != null){
                        $(monthlyQtyArr[customerVal][pkgVal][deviceVal][monthVal]).each(function(){
                          if(this['FORECAST'] != 0){
                            bodyRowString += "<td>"+numberWithCommas(this['FORECAST'])+"</td>";
                          }
                          else{
                            bodyRowString += "<td>"+"---"+"</td>";
                          }
                          if(this['PLAN'] != 0){
                            bodyRowString += "<td>"+numberWithCommas((parseInt(this['PLAN'])*parseInt(this['PERCENTAGE']))/100)+"</td>";
                          }
                          else{
                            bodyRowString += "<td>"+"---"+"</td>";
                          }
                         
                          if(monthVal.replace(/\s/g,'') == currentMonth.replace(/\s/g,'')){
                            if(this['ACTUAL'] == 0){
                              bodyRowString += "<td>"+"---"+"</td>";
                            }
                            else{
                              bodyRowString += "<td><a style='cursor:pointer;'>"+numberWithCommas(this['ACTUAL'])+"</a></td>";
                            }
                            
                          }
                          else{
                            if(this['ACTUAL'] == 0){
                              bodyRowString += "<td>"+"---"+"</td>";
                            }
                            else{
                              bodyRowString += "<td>"+numberWithCommas(this['ACTUAL'])+"</td>";
                            }
                          }
                          
                        });
                      }
                      else{
                        bodyRowString += "<td>"+"---"+"</td>";
                        bodyRowString += "<td>"+"---"+"</td>";
                        bodyRowString += "<td>"+"---"+"</td>";
                      }
                                      
                    });
                    bodyRowString+="<td>"
                              +"<button class='btn btn-sm btn-success forecastTblBtns' title='add/edit this forecast individually' id='addMonthForecastBtn'>"
                              +"<i class='material-icons' style='font-size: 1.5rem;'>open_in_new</i></button>";
                    if(is_mis == 1){
                      bodyRowString+="<button class='btn btn-sm bg-purple forecastTblBtns' title='add/edit this forecast by batch' id='addMonthForecastBatchBtn'>"
                                +"<i class='material-icons' style='font-size: 1.5rem;'>open_in_new</i>"
                                +"</button>";
                    }
                    bodyRowString+="</td>";
                  }
                });
              });
            });
            // if($.fn.DataTable.isDataTable( '#forecastTable' ) ){
            //     $('#forecastTable').DataTable().clear().destroy();
            // }
            $("#forecastTableHead").html(headRowString);
            $("#forecastTableBody").html(bodyRowString);
            forecastDataTable(indexString);
            // console.log(indexString);
            $("div#forecastCardDivId").waitMe("hide");

          }
          else{
            $("div#forecastCardDivId").waitMe("hide");
            headRowString = "<tr>"
            + "<th>Customer</th>"
            + "<th>Package</th>"
            + "<th>Customer Device Name</th>"
            + "<th>Device</th>"
            + "<th>Device Variant</th>"
            + "<th>Device Description</th>"
            + "</tr>";

            $("#forecastTableHead").html(headRowString);
            // $("#forecastTableBody").html("<tr></tr>");

            if($.fn.DataTable.isDataTable( '#forecastTable' ) ){
              $('#forecastTable').DataTable().clear().destroy();
            }
            $('#forecastTable').DataTable();
          }
        }
    });

  }

  function forecastDataTable(indexString){
    if($.fn.DataTable.isDataTable( '#forecastTable' ) ){
              $('#forecastTable').DataTable().clear().destroy();
    }
    // console.log(indexString);
    // $("#forecastTable").DataTable({'rowsGroup': [1,2,3, indexString], "scrollX": true});
    var forecastTable = $('#forecastTable').DataTable({'rowsGroup': [1,2,3, indexString], "bSort" : false, "scrollX": true, "fixedColumns":{leftColumns: 7, rightColumns: 1},
            //"paging":false,
            //"scrollY":400,
            //"order":[[3, "desc"]],
            "columnDefs": [
            {
                "targets": [4,6],
                "render": function ( data, type, row ) {
                  return data.substr( 0, 22 )+"...";
                }
            },
            {
                //"targets": [ 0, 5 ],
                "targets": [ 0, 3, 5 ],
                "visible": false,
                "searchable": false
            },
            {
                "className": "dt-body-left",
                "targets": [1,2,3,4,5,6],

            },
            {
                "className": "dt-body-right",
                "targets": '_all',

            }
            
            ],
            dom: 'Bfrtip',
            buttons: [
                //'copy', 'csv', 'excel', 'pdf', 'print'
                {extend : 'excel',
                    title : function() {
                        return "FORECAST REPORT";
                    },
                    //exportOptions: { orthogonal: 'export' }
                    exportOptions: {
                         columns: ':visible',
                         format: {
                              //this isn't working....
                               header:  function (data, columnIdx) {
                               return columnIdx + ': ' + data + "blah";
                            }
                          }
                     }
                },
                {extend : 'pdfHtml5',
                    title : function() {
                        return "FORECAST REPORT";
                    },
                    orientation : 'landscape',
                    pageSize :'A2',
                    titleAttr : 'PDF',
                    //exportOptions: { orthogonal: 'export' }
                    exportOptions: {
                         columns: ':visible'
                    }
                }
            ],
            createdRow: function (row, data, rowIndex) {
              //console.log(row);
              $('td:eq(2)', row).attr('title', data[4]);
              $('td:eq(2)', row).attr('data-toggle', "tooltip");
              $('td:eq(2)', row).attr('style', "cursor:pointer;");
              $('td:eq(3)', row).attr('title', data[6]);
              $('td:eq(3)', row).attr('data-toggle', "tooltip");
              $('td:eq(3)', row).attr('style', "cursor:pointer;");
            },
            fnInitComplete: function () {
              $("[data-toggle='tooltip']").tooltip({
                container: 'body'
              });
            }
          });

          /* Apply the tooltips */

          if(forecastTable != null || forecastTable != ''){
            $(forecastTable.table().container()).on('click', 'td:nth-child(7)', function () {
                var cell_clicked    = forecastTable.cell(this).data();
                var row_clicked     = $(this).closest('tr');
                var row_object      = forecastTable.row(row_clicked).data();
                console.log(row_object[6]);
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data:{
                      INVENTORY_ITEM_ID: row_object[0]
                    },
                    url: "ajax/forecast/select/select_actual_info.php", 
                    success: function(response){
                      var actualData = response.data;
                      $("#clickedColInfoTableHead").html("");
                      //var clickedColInfoTableHead = "";
                      var clickedColInfoTableHead = "<tr>"
                                + "<th>MO NO.</th>"
                                + "<th>DATE</th>"
                                + "<th>QTY</th>"
                                + "</tr>";
                      $("#clickedColInfoTableHead").html(clickedColInfoTableHead);
                      if($("#infoModal").modal('show')){
                        if($.fn.DataTable.isDataTable( '#clickedColInfoTable' ) ){
                          $('#clickedColInfoTable').DataTable().clear().destroy();
                        }
                        if(actualData.length != 0){
                          var clickedColInfoTable = $('#clickedColInfoTable').DataTable({
                            "data": actualData,       
                            "columns":[
                                { "data": "MO", render: function ( data,type,row ){
                                  return data;
                                } },
                                { "data": "CREATION_DATE", render: function ( data,type,row ){
                                  return data;
                                } },
                                { "data": "START_QUANTITY", render: function ( data,type,row ){
                                  return numberWithCommas(data);
                                } }
                            ],
                            "columnDefs":[
                            {
                                    className: 'dt-body-right',
                                    targets: 2
                            }],
                            "bSort" : false,
                            //"scrollCollapse": true,
                            //"ordering":false,
                            "scrollX":true
                          }); 
                        }
                        else{
                          var clickedColInfoTable = $('#clickedColInfoTable').DataTable({"data":actualData, "bSort":false, "bPaginate": false, "scrollX":true});
                        }
                      }
                    }
                  });
            });

            $(forecastTable.table().container()).on('click', 'button.forecastTblBtns', function () {
                // return false;
                //var data = forecastTable.row( $(this).parents('tr') ).data();
                buttonId = $(this).attr('id');
                var cell_clicked    = forecastTable.cell(this).data();
                var row_clicked     = $(this).closest('tr');
                var row_object      = forecastTable.row(row_clicked).data();

                var customer = forecastTable.cell(row_clicked, 1).data();
                var pkg = forecastTable.cell(row_clicked, 2).data();
                var device = forecastTable.cell(row_clicked, 4).data();
                var device_variant = forecastTable.cell(row_clicked, 5).data();
                var inventory_item_id = forecastTable.cell(row_clicked, 0).data();
                var device_description = forecastTable.cell(row_clicked, 6).data();
                var customer_dev_name = forecastTable.cell(row_clicked, 3).data();

                if(buttonId == 'addMonthForecastBtn'){
                 
                  
                  if($("#addMonthForecastModal").modal("show")){
                    console.log(device_variant);
                    string = "<label>CUSTOMER:</label>"
                           + "<input type='text' class='form-control' id='cusAddMonthForecastId' style='width: 50%' value='"+customer+"' readonly>"
                           + "<div style='padding:5px;'></div>"
                           + "<label>PKG:</label>"
                           + "<input type='text' class='form-control' id='pkgAddMonthForecastId' style='width: 50%' value='"+pkg+"' readonly>"
                           + "<div style='padding:5px;'></div>"
                           + "<label>DEVICE:</label>"
                           + "<input type='text' class='form-control' id='devAddMonthForecastId' style='width: 50%' value='"+device+"' readonly>"
                           + "<div style='padding:5px;'></div>"
                           + "<label>CUSTOMER DEVICE NAME:</label>"
                           + "<input type='text' class='form-control' id='custdevAddMonthForecastId' style='width: 50%' value='"+customer_dev_name+"'>"
                           + "<div style='padding:20px;'></div>"
                           + "<input type='hidden' id='addMonthForecastType' value='fromIndiv'>"
                           + "<input type='hidden' id='devvarAddMonthForecastId' value='"+device_variant+"'>"
                           + "<input type='hidden' id='invidAddMonthForecastId' value='"+inventory_item_id+"'>"
                           + "<input type='hidden' id='devdescAddMonthForecastId' value='"+device_description+"'>"
                           + "<table class='table table-striped table-bordered' id='addMonthForecastTable'>"
                           + "<thead>"
                           + "<tr>"
                           + "<th>Month</th>"
                           + "<th>Percentage</th>"
                           + "<th>Forecast Qty</th>"
                           + "<th>Plan Qty</th>"
                           + "<th>Remarks</th>"
                           + "<th>Action</th>"
                           + "<th>Qty History</th>"
                           + "</tr>"
                           + "</thead>"
                           + "<tbody id='addMonthForecastTableBody'>"
                           + "</tbody>"
                           + "</table>"
                           + "<button type='button' class='btn btn-success' id='addMonthRowForecastBtn' onclick='addMonthRowForecastBtn()'>Add Month and Qty</button>";

                    $("#addMonthForecastModalDesc").html(string);
                    var tbody = $("#addMonthForecastTable tbody");

                    if (tbody.children().length == 0) {
                        // tbody.html("<tr>message foo</tr>");
                        addMonthForecastOptions(0, 'firstEntry');
                        $("#addMonthForecastTable").on("click", "td:first-child", function() {
                          //alert($( this ).text());
                          id = $(this).attr("id");
                          $("#selectedMonthForecast-"+id).on("change", function(){
                            getAddMonthOldQty(id);
                          });
              
                        });
                    }

                    
                  }
                }
                else if(buttonId == 'addMonthForecastBatchBtn'){
                  if($("#addMonthForecastByBatchDispModal").modal('show')){
                    // console.log('opened');
                    $("#addMonthForecastByBatchDispCustomer").val(customer);
                    $("#addMonthForecastByBatchDispPkg").val(pkg);
                    $("#addMonthForecastByBatchDispDevice").val(device);
                    $("#addMonthForecastByBatchDispModalBody").waitMe({effect : 'pulse', text : 'Loading...'});
                    dataToPass = {
                      customer: customer,
                      pkg: pkg,
                      device: device
                    }
                    getDeviceBatchTbl(dataToPass);
                  }
                }
            });
          }
  }
  
  function forecastModalButtons(){
    
    $("button#nextButton").on("click", function(e){
      activeStepDivId = $(".activeStepDiv").attr('id');
      i = 0;
      count = $('#forecastForm > div').length;
      $('#forecastForm > div').each(function(){
        var innerDivId = $(this).attr('id');
        
        if(innerDivId == activeStepDivId){
          page = i+1;
          if(page < count){
            nextPageId = page;
          }
          return false;
        } 
        else{
          i = i+1;
        }
      });
      
      if(activeStepDivId == "firstPage"){
        count = $("#deviceListTable").DataTable().rows('.selected').count();
        if(count == 0){
          alert("You have to choose device/s before proceeding!");

        }
        else{
          $('#'+activeStepDivId).removeClass('activeStepDiv');
          $('#forecastForm').children('div').eq(nextPageId).addClass('activeStepDiv');
          forecastModalDisplay();
        }
      }
      else if(activeStepDivId == "secondPage"){
        inputBlankCounter = 0;
        selectedDevices = $("#deviceListTable").DataTable().rows('.selected').data();
        $(selectedDevices).each(function(){
          self = this;
          if(this['INPUT'] == ""){
            inputBlankCounter++;
          }
        });

        if(inputBlankCounter != 0){
          alert("Please do not leave field/s blank.");
        } 
        else{
          $('#'+activeStepDivId).removeClass('activeStepDiv');
          $('#forecastForm').children('div').eq(nextPageId).addClass('activeStepDiv');
          forecastModalDisplay();
        }
      }
      else if(activeStepDivId == "thirdPage"){
        inputBlankCounterQty = 0;
        var table = $("#MonthQtyTableBody");
        table.find('tr').each(function (j, el) {
            var $tds = $(this).find('td'),
            //month = $tds.eq(0).find("select").val(),
            qty = $tds.eq(1).find("input").val();
            capqty = $tds.eq(2).find("input").val();
            if($tds.eq(3).find("textarea").length > 0){
              remarks = $tds.eq(3).find("textarea").val();
              if(qty == "" || capqty == "" || remarks == ""){
                inputBlankCounterQty++;
              }
            }
            else{
              if(qty == "" || capqty == ""){
                inputBlankCounterQty++;
              }
            }
            //countqty = (parseInt(dataArray[i]['INPUT'])/100) * qty;
            
                           
        });

        if(inputBlankCounterQty != 0){
          alert("Please do not leave field/s blank.");
        }
        else{
          $('#'+activeStepDivId).removeClass('activeStepDiv');
          $('#forecastForm').children('div').eq(nextPageId).addClass('activeStepDiv');
          forecastModalDisplay();
        }
      }
      else{
      $('#'+activeStepDivId).removeClass('activeStepDiv');
      $('#forecastForm').children('div').eq(nextPageId).addClass('activeStepDiv');
      forecastModalDisplay();
      }
    });
  
    $("button#backButton").on("click", function(e){
      activeStepDivId = $(".activeStepDiv").attr('id');
      i = 0;
      count = $('#forecastForm > div').length;
      $('#forecastForm > div').each(function(){
        var innerDivId = $(this).attr('id');
        
        if(innerDivId == activeStepDivId){
          page = i-1;
          if(page >= 0){
            backPageId = page;
          }
          return false;
        } 
        else{
          i = i+1;
        }
      });
      $('#'+activeStepDivId).removeClass('activeStepDiv');
      $('#forecastForm').children('div').eq(backPageId).addClass('activeStepDiv');
      forecastModalDisplay();
    });
  }
  
  
  function forecastModalDisplay(){
    var activeStepDivId = $("#forecastForm .activeStepDiv").attr('id');
    i = 0;
    $('#forecastForm > div').each(function(){
      var innerDivId = $(this).attr('id');
      
      if(innerDivId == activeStepDivId){
        $("#"+innerDivId).show();
      } 
      else{
        $("#"+innerDivId).hide();
      }
    });
  
    if(activeStepDivId == $("#forecastForm").children('div').first().attr('id')){
      $("button#nextButton").show();
      $("button#backButton").hide();
      $("button#save_exitButton").hide();
      $("button#save_contButton").hide();
    }
    else if(activeStepDivId == $('#forecastForm').children('div').last().attr('id')){
      $("button#nextButton").hide();
      $("button#backButton").show();
      $("button#save_exitButton").show();
      $("button#save_contButton").show();
      if($.fn.DataTable.isDataTable( '#previewTable' ) ){
        $('#previewTable').DataTable().clear().destroy();
      }
      getPreviewData();
    }
    else if(activeStepDivId == $("#forecastForm").children('div').eq(1).attr('id')){
      $("button#nextButton").show();
      $("button#backButton").show();
      $("button#save_exitButton").hide();
      $("button#save_contButton").hide();
      // if ( ! $.fn.DataTable.isDataTable( '#chosenDeviceListTable' ) ) {
        getChosenDeviceDataTable();
      // }
      // else{
      //   $("#chosenDeviceListTable").DataTable().draw();
      // }
      
    }
    else{
      $("button#nextButton").show();
      $("button#backButton").show();
      $("button#save_exitButton").hide();
      $("button#save_contButton").hide();
      var tbody = $("#MonthQtyTable tbody");

      if (tbody.children().length == 0) {
          // tbody.html("<tr>message foo</tr>");
          monthOptions(0, 'firstEntry');
      }
      else{
        $('#MonthQtyTable > tbody  > tr').each(function() {
          //console.log($(this).attr('id'));
          var row_id = $(this).attr('id'),
          id= row_id.split("-")[1];
          //console.log(id);
          getOldQty(id);
        });
        //getOldQty(id);
      }
      // inputs= $("#chosenDeviceListTable").DataTable().$('input').serialize();
      // inputsSplit = inputs.split('&');
      // dataArray = $("#chosenDeviceListTable").DataTable().rows().data();
      // $(dataArray).each(function(){
      //   $()
      // });
    }
    //forecastModalButtons();
  }
  
  function selectCustomer(){
    $.ajax({
        type: "POST",
        dataType: "json", 
        url: "ajax/forecast/select/select_customers.php", 
        success: function(response){
          customerString = "";
          if(response != null){
            $(response).each(function(){
              var self = this;
              customerString += "<option value='"+ self['CUSTOMER'] +"'>";
            });
            $("#selCustomer").html(customerString);
          }
        }
    });
  }
  
  function selectCustomerOnChange(){
    $("#customerInput").on("keyup change paste", function(){
    //   var customerValue = $("#customerInput").val();
    //   var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    //   if(customerId != null){
        selectPackage();
    //   }
    });
  }

  function selectPkgOnChange(){
    $("#pkgInput").on("change", function(){
      var customerValue = $("#customerInput").val();
      //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
      var pkgValue = $("#pkgInput").val();
      //var pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
      var searchBy = $("input[name='devSearchType']:checked").val();
  
      if(customerValue != null && pkgValue != null){
        $('#deviceListTable').DataTable().clear().destroy();
        getDeviceDataTable(customerValue, pkgValue, searchBy, $("#bdInput").val());
      }
    });
  }
  
  
  function selectPackage(){
    var customerValue = $("#customerInput").val();
    //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    if(customerValue != null){
      $("#pkgInput").val('');
    //   $("#selPkg").html("");
      //selectPackage();
      $.ajax({
        type: "POST",
        dataType: "json", 
        data: {
          customer: customerValue
        },
        url: "ajax/forecast/select/select_packages.php", 
        success: function(response){
          packageString = "";
          if(response != null){
            $(response).each(function(){
              var self = this;
              packageString += "<option value='"+ self['PKG'] +"'>";
            });
            $("#selPkg").html(packageString);
          }
        }
      });
    }
  
  }
  
  function selectBdNoOnChange(){
    $("#bdInput").on("keyup", function(){ 
      var customerValue = $("#customerInput").val();
      //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
      var pkgValue = $("#pkgInput").val();
      //var pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
      var searchBy = $("input[name='devSearchType']:checked").val();
  
      if(customerValue != null && pkgValue != null && searchBy != null){
        $('#deviceListTable').DataTable().clear().destroy();
        getDeviceDataTable(customerValue, pkgValue, searchBy, $("#bdInput").val());
        //getDeviceHistoryDataTable(customerValue, pkgValue, searchBy, $("#bdInput").val());
        getDeviceHistoryCalendarDataTable(customerValue, pkgValue, searchBy, $("#bdInput").val());
      }
    });
  }
  
  function getDeviceDataTable(customerValue, pkgValue, searchBy, deviceId){
    console.log('entered');
    //deviceListTable
    var deviceListTable = $('#deviceListTable').DataTable({
      "ajax":{
              "url":"ajax/forecast/select/select_devices.php",
              "type":"post",
              "data":{
                customer:customerValue,
                pkg:pkgValue,
                searchBy:searchBy,
                device:deviceId
              }
      },
      "columns":[
                  { "data": "NO", "bSortable": false, render: function ( data,type,row ){
                    return data;
                  }},
                  { "data": "DEVICE", "bSortable": false, render: function ( data,type,row ){
                    return data;
                  }},
                  { "data": "DESCRIPTION", "bSortable": false, render: function ( data,type,row ){
                    return data;
                  }},
                  { "data": "VOLUME", "bSortable": false, render: function ( data,type,row ){
                    //return data;
                    if(data == 0 || data == null || data === ""){
                      return  0;
                    }
                    else{
                      return numberWithCommas(data);
                    }
                  }},
                  { "data": "", "bSortable": false, render: function ( data,type,row ){
                    return "";
                  }}
                ],
      "columnDefs":[{
              orderable: false,
              className: 'select-checkbox',
              targets:   4
      },
      {
              className: 'dt-body-right',
              targets: 3
      }],
      "select": {
            selector: 'td:last-child',
            style: 'multi'
        },
      "dom": 'Bfrtip',
      "buttons": [{
              text: "Test",
              action: function ( e, dt, node, config ) {
                //dt.ajax.reload();
                selectedRowsCount = this.rows('.selected').count();
                if(selectedRowsCount == 0){
                  alert('Please select device/s first to test');
                }
                else{
                  selectedData = this.rows('.selected').data();
                  dataToPassInq = new Array();
                  //i = 0;
                  $(selectedData).each(function(){
                    self = this;
                    dataToPassInq.push(self);
                    //dataToPassInq[]
                  });
                  if( $("#inquiryModal").modal("show")){
                    materialsInqDataTable(passData = new Array());
                    dataPassInqIndex = 0;
                    $(dataToPassInq).each(function(){
                      dataToPassInq[dataPassInqIndex]['QTY'] = 0;
                      dataPassInqIndex++;
                    });
                    $.ajax({
                              type: "POST",
                              dataType: "json", 
                              data: {
                                selectedData: dataToPassInq
                              },
                              url: "ajax/forecast/select/select_materialsinq.php", 
                              success: function(response){
                                //if( $("#inquiryModal").modal("show")){
                                //return false;
                                materialsInqDataTable(response);
                            }
                            //}

                          });
                    //$*
                    $("#checkedColInfoTable").DataTable({
                      "data": dataToPassInq,
                      "columns":[
                        {"data":"DEVICE", "bSortable": false, render: function (data,type,row){
                          return data;
                        }},
                        {"data":"", "bSortable":false, render:function(data,type,row){
                          return "<input type='number' class='form-control'>";
                        }}
                      ],
                      "dom": 'Bfrtip',
                      "buttons":[{
                        text: "Test Devices",
                        action: function (e,dt,node,config){
                          emptyInputCounter = 0;

                          i = 0;
                          $(dataToPassInq).each(function(){
                            j = 0;
                            $('#checkedColInfoTableBody').find('tr').each(function (j, el) {
                              if(i == j){
                                var $tds = $(this).find('td'),
                                qty = $tds.eq(1).find("input").val();
                                if(qty == '' || qty == null){
                                  qty = 0;
                                  emptyInputCounter++;
                                }
                                dataToPassInq[i]['QTY'] = qty;
                              }
                              j++;
                           
                            });
                            i++;
                          });
                          //return false;
                          if(emptyInputCounter == 0){
                          $.ajax({
                              type: "POST",
                              dataType: "json", 
                              data: {
                                selectedData: dataToPassInq
                              },
                              url: "ajax/forecast/select/select_materialsinq.php", 
                              success: function(response){
                                //if( $("#inquiryModal").modal("show")){
                                //return false;
                                materialsInqDataTable(response);
                            }
                            //}

                          });
                          }
                          else{
                            alert("Please input qty per device to test.");
                          }
                        }
                      }],
                      "ordering":false,
                      "searching":false,
                      "paging":false,
                      //"scrollY":"130px",
                      "info":false
                    });
                  }
                  
                }
                
              },
              className: 'inquiryBtn'
      }],
      "pageLength": 5,
      "info": false,
      fnInitComplete: function() {
          //if($.fn.DataTable.isDataTable('#deviceListTable')){
              // var hasRows = this.api().rows({ filter: 'applied' }).data().length > 0;
              // $('.inquiryBtn')[0].style.visibility = hasRows ? 'visible' : 'hidden';
              if(this.api().rows({ filter: 'applied' }).data().length > 0){
                $('.inquiryBtn')[0].style.visibility = 'visible';
              }
              else{
                // $('.inquiryBtn')[0].style.visibility = 'hidden';
                if($.fn.DataTable.isDataTable('#deviceListTable')){
                  $('.inquiryBtn')[0].style.visibility = 'hidden';
                }
              }
              
          //}
      }
      
    });

    
  }

  function materialsInqDataTable(response){
    if($.fn.DataTable.isDataTable( '#materialsInfoTable' ) ){
        $('#materialsInfoTable').DataTable().clear().destroy();
    }
    $("#materialsInfoTable").DataTable({
      "data": response.data,
      "columns":[
          { "data": "DEVICE", "bSortable": false, render: function ( data,type,row ){
            if(data.length > 25){
              subStringDevMat = data.substr(0,22) + "...";
            }
            else{
              subStringDevMat = data;
            }
            return subStringDevMat;
          }},
          { "data": "MATERIAL", "bSortable": false, render: function ( data,type,row ){
            return data;
          }},
          { "data": "MATERIAL_DESCRIPTION", "bSortable": false, render: function ( data,type,row ){
            if(data.length > 25){
              subStringDescMat = data.substr(0,22) + "...";
            }
            else{
              subStringDescMat = data;
            }
            return subStringDescMat;
          }},
          { "data": "MTL_REQT", "bSortable": false, render: function ( data,type,row ){
            return data;
          }},
          { "data": "NET_REQ", "bSortable": false, render: function ( data,type,row ){
            return data;
          }},
          { "data": "COM", "bSortable": false, render: function ( data,type,row ){
            return data;
          }},
          { "data": "GR_REQ", "bSortable": false, render: function ( data,type,row ){
            return data;
          }}
      ],
      "createdRow": function (row, data, rowIndex) {
          // Per-cell function to do whatever needed with cells
          $.each(
            $('td', row), function (colIndex, data) {
              // For example, adding data-* attributes to the cell
              if(colIndex == 0 || colIndex == 2){
                //console.log($("#materialsInfoTable").DataTable().cells(rowIndex, colIndex).data()[0]);
                $(this).attr('title', $("#materialsInfoTable").DataTable().cells(rowIndex, colIndex).data()[0]);
                $(this).attr('data-toggle', "tooltip");
              }
                                        
            }
          );
      },
      order: [[0, 'asc']],
      rowGroup: {
          dataSrc: "DEVICE",
          startRender: function ( rows, group ) {
            return group;

          }
      },
      scrollX: true,
      "scrollY":"200px",
      scrollCollapse: true,
      fnInitComplete: function () {
        $("[data-toggle='tooltip']").tooltip({
          container: 'body'
        });
      }
    });
  }
  
  function getChosenDeviceDataTable(){
    $('#chosenDeviceListTable').DataTable().clear().destroy();
    dataArray = $("#deviceListTable").DataTable().rows('.selected').data();
    var i = 0;
    $(dataArray).each(function(){
      dataArray[i]['ARRAY_NO'] = i;
      dataArray[i]['NO'] = i+1;
      i++;
    });
    //chosenDeviseListTable
    $('#chosenDeviceListTable').DataTable({
      "data": dataArray,
      "columns":[
                  { "data": "NO", "bSortable": false, render: function ( data,type,row ){
                    return data;
                  }},
                  { "data": "DEVICE", "bSortable": false, render: function ( data,type,row ){
                    return data;
                  }},
                  { "data": "DESCRIPTION", "bSortable": false, render: function ( data,type,row ){
                    return data;
                  }},
                  { "data": "INPUT", "bSortable": false, render: function ( data,type,row ){
                    return "<input type='number' class='form-control' id='inputNo"+row.NO+"' name='inputNo"+row.NO+"' value='"+data+"'>";
                    
                  }}
                ],
      "columnDefs": [{
              orderable: false,
              targets: 3
          }],
      "paging": false,
      "searching": false,
      "info": false
    });
  
    $('#chosenDeviceListTable').on( 'click', 'tbody td:last-child', function (e) {
          data = $("#chosenDeviceListTable").DataTable().row(this).data();
          $( '#inputNo'+data.NO ).on( 'keyup', function () {
                    dataArray[data.ARRAY_NO]['INPUT'] = $("#inputNo"+data.NO).val();
          });
      } );
  }
  
  
  function getPreviewData(){
    dataArray = $("#deviceListTable").DataTable().rows('.selected').data();
    var table = $("#MonthQtyTableBody");
  
    previewTableHead = '';
    i = 0;
    j = 0;
    tableHeader = new Array('Device', 'Device Description');
    dataArray1 = new Array();
    
    table.find('tr').each(function (j, el) {
                      var $tds = $(this).find('td'),
                          month = $tds.eq(0).find("select").val();
                      tableHeader.push(month);
                           
    });
    $(dataArray).each(function(){
      dataArray2 = new Array();
      dataArray2.push(dataArray[i]['DEVICE']);
      dataArray2.push(dataArray[i]['DESCRIPTION']);
      table.find('tr').each(function (j, el) {
                      var $tds = $(this).find('td'),
                          month = $tds.eq(0).find("select").val(),
                          qty = $tds.eq(1).find("input").val(),
                          capqty = $tds.eq(2).find("input").val();;
                      computeperc = (parseInt(dataArray[i]['INPUT'])/100) * qty;
                      countqty = qty;
                      capacity = (parseInt(dataArray[i]['INPUT'])/100) * capqty;
                      countqty = qty;
                      dataArray2.push(countqty);
                      //dataArray2.push(computeperc);
                      dataArray2.push(capacity);
                           
      });
  
      dataArray1.push(dataArray2);
     i++;
    });
  
          
    iPrevTblHead = 0;
    countPrevTblHead = 0;

    previewTableHead += "<tr>";
    forecastIndexStr = "2"; 
    forecastIndex = 0;
    $(tableHeader).each(function(){
      self = this;
      if(iPrevTblHead < 2){
        previewTableHead += "<th rowspan='2'>"+self+"</th>";
      }
      else{
        previewTableHead += "<th colspan='2'>"+self+"</th>";
        countPrevTblHead++;
        forecastIndex +=  2;
          forecastIndexStr += ", "+forecastIndex;
      }
      iPrevTblHead++;
    });
    previewTableHead += "</tr>";
    previewTableHead += "<tr>";
    
    
    for(j=0;j<countPrevTblHead;j++){
      previewTableHead += "<th>Forecast</th>";
      previewTableHead += "<th>Plan</th>";
      //previewTableHead += "<th>Capacity</th>";
    }
    console.log(forecastIndexStr);
    previewTableHead += "</tr>";
    $("#previewTableHead").html(previewTableHead);
  
    previewTableBody ="";
    
    $(dataArray1).each(function(){
      previewTableBody += "<tr>"; 
      self = this;
      i = 0;
      $(self).each(function(){
        self1 = this;
        if(i > 1){
          previewTableBody += "<td>"+numberWithCommas(self1)+"</td>";
        }
        else{
          previewTableBody += "<td>"+self1+"</td>";
        }
        i++;
      });
      
      previewTableBody += "</tr>";

    });
   
    $("#previewTableBody").html(previewTableBody);
    $('#previewTable').DataTable({ "rowsGroup":[forecastIndexStr], "bSort" : false, "searching": false, "scrollX": true,
        "columnDefs": [
            {
                "className": "dt-body-left",
                "targets": [0,1],

            },
            {
                "className": "dt-body-right",
                "targets": '_all',

            }
        ],
    });
  }
  
  function save(savingType){
    dataArray = $("#deviceListTable").DataTable().rows('.selected').data();
    var table = $("#MonthQtyTableBody");
  
    previewTableHead = '';
    i = 0;
    // j = 0;
    dataArray1 = new Array();
  
    $(dataArray).each(function(){
      table.find('tr').each(function (j, el) {
                      var $tds = $(this).find('td'),
                          month = $tds.eq(0).find("select").val(),
                          qty = $tds.eq(1).find("input").val(),
                          capqty = $tds.eq(2).find("input").val();
                          if($tds.eq(3).find("textarea").length > 0){
                            remarks = $tds.eq(3).find("textarea").val();
                          }
                          else{
                            remarks = "";
                          }
                          capacity = (parseInt(dataArray[i]['INPUT'])/100) * capqty;
                      countqty = (parseInt(dataArray[i]['INPUT'])/100) * qty;
                      dataArray1.push({
                        INVENTORY_ITEM_ID: dataArray[i]['INVENTORY_ITEM_ID'],
                        CUSTOMER: dataArray[i]['CUSTOMER'],
                        PKG: dataArray[i]['PKG'],
                        DEVICE_VARIANT: dataArray[i]['BD_NO'],
                        DEVICE: dataArray[i]['DEVICE'],
                        DEVICE_DESCRIPTION: dataArray[i]['DESCRIPTION'],
                        MONTH: month,
                        //QTY: countqty
                       // QTY: dataArray[i]['INPUT']
                        QTY: qty,
                        PERCENT: dataArray[i]['INPUT'],
                        CAPACITY: capqty,
                        REMARKS: remarks
                      });
                           
      });
     i++;
    });
    
    if(savingType == 'saveAndCont'){
      title = "Are you sure you want to save this and continue?";
      text = "A new modal form will be opened.";
    }
    else{
      title = "Are you sure you want to save this and exit?";
      text = "";
    }
  
    swal({
          title: title,
          text: text,
          type: "warning",
          showCancelButton: true,
          confirmButtonColor: '#DD6B55',
          confirmButtonText: 'Yes, I am sure!',
          cancelButtonText: "No, cancel it!",
          closeOnConfirm: false,
          closeOnCancel: false,
          showLoaderOnConfirm: true
    },
    function(isConfirm){
          
      if (isConfirm){
        $.ajax({
          type: "POST",
          dataType: "json", 
          data: {
            array: dataArray1,
            input: $("#bdInput").val()
          },
          url: "ajax/forecast/insert/insert_forecast.php", 
          success: function(response){
            if(response == 1){
              // $('#forecastTable').DataTable().clear().destroy();
              if(savingType == 'saveAndCont'){
                swal({title: "Saved!",
                    text: "",
                    type: "success"},
                function(isConfirm){
                    if(isConfirm){
                        $("#forecastModal").modal("hide");
                        $("#forecastModal").modal("show");
                    }

                });
                
              }
              else{
                swal({title: "Saved!",
                    text: "",
                    type: "success"},
                function(isConfirm){
                    if(isConfirm){
                        $("#forecastModal").modal("hide");
                        // $("#forecastModal").modal("show");
                    }

                });
              }
            }
          }
        });
          
      } else {
              swal("Saving cancelled", "", "error");
              }
    });
  }  
  
  function removeMonthQtyRow(row_id){
    $("#MonthQtyRow-"+row_id).remove();
  }

  function removeMonthForecastRow(row_id){
    $("#MonthForecastRow-"+row_id).remove();
  }

  function monthOptions(id, entryNum){
    

    $.ajax({
      type: "POST",
      dataType: "json", 
      url: "ajax/forecast/select/month_options.php", 
      success: function(response){
        if(response != null){

          var markup = "<tr id='MonthQtyRow-"+id+"'>"
                     +"<td id='"+id+"'><select class='form-control' id='selectedMonth-"+id+"'>";
          i = 0;
          $(response).each(function(){
            self =  this;
            if(i == 0){
              markup += "<option value='"+self+"' selected>"+self+"</option>";
            }
            else{
              markup += "<option value='"+self+"'>"+self+"</option>";
            }
            i++;
            
          });
          markup += "</select></td>" 
                 +"<td><input type='number' class='form-control'></td>"
                 +"<td><input type='number' class='form-control'></td>"
                 +"<td></td>";
          if(entryNum == 'firstEntry'){
            markup += "<td></td>";
          } 
          else{
            markup += "<td><button type='button' class='btn btn-danger btn-sm' onclick='removeMonthQtyRow("+new_id+")'><i class='material-icons'>remove</i></button></td>" ;
          }

          markup += "<td>"+0+"</td>";
                 // +"<td></td>" 
          markup += "</tr>";
          $("#MonthQtyTableBody").append(markup);
          getOldQty(id);
          //getOldQty();
        }
      }
    });


   
  }

  function addMonthForecastOptions(id, entryNum){
    

    $.ajax({
      type: "POST",
      dataType: "json", 
      url: "ajax/forecast/select/month_options.php", 
      success: function(response){
        if(response != null){

          var markup = "<tr id='MonthForecastRow-"+id+"'>"
                     +"<td id='"+id+"'><select class='form-control' id='selectedMonthForecast-"+id+"'>";
          i = 0;
          $(response).each(function(){
            self =  this;
            if(i == 0){
              markup += "<option value='"+self+"' selected>"+self+"</option>";
            }
            else{
              markup += "<option value='"+self+"'>"+self+"</option>";
            }
            i++;
            
          });
          markup += "</select></td>" 
                 +"<td><input type='number' class='form-control'></td>"
                 +"<td><input type='number' class='form-control'></td>"
                 +"<td><input type='number' class='form-control'></td>"
                 +"<td></td>";
          if(entryNum == 'firstEntry'){
            markup += "<td></td>";
          } 
          else{
            markup += "<td><button type='button' class='btn btn-danger btn-sm' onclick='removeMonthForecastRow("+new_id+")'><i class='material-icons'>remove</i></button></td>" ;
          }

          markup += "<td>"+0+"</td>";
                 // +"<td></td>" 
          markup += "</tr>";
          $("#addMonthForecastTableBody").append(markup);
          getAddMonthOldQty(id);
        }
      }
    });
    
    
   
  }

  function getOldQty(id){
    dataArray = $("#deviceListTable").DataTable().rows('.selected').data();
    array = new Array();
    i = 0;
    $(dataArray).each(function(){
      array.push({
        CUSTOMER: dataArray[i]['CUSTOMER'],
        PKG: dataArray[i]['PKG'],
        DEVICE_VARIANT: dataArray[i]['BD_NO'],
        DEVICE: dataArray[i]['DEVICE']
      });
      i++;
    });

    var selectedMonth = $('#selectedMonth-'+id).val();
    var Qty = 0;
    customerValue = $("#customerInput").val();
    //customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    //   if(customerId != null){
    pkgValue = $("#pkgInput").val();
    //pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
    device = $("#bdInput").val();

    $.ajax({
      type: "POST",
      dataType: "json", 
      data: {
        array: array,
        month: selectedMonth,
        action: 'FORECAST'
      },
      url: "ajax/forecast/select/select_oldqty.php", 
      success: function(response){
        if(response['SUM'] != 0){
          Qty = response['SUM']+" (%: "+response['PERCENTAGE']+")";
          //remarksInput = "<textarea class='form-control' id='monthForecastRem' maxlength='250'></textarea>";
          remarksInput = "<textarea class='form-control' maxlength='250' id='monthQtyRem'></textarea><label id='monthQtyRemLabel'>250</label>";
        }
        else{
          Qty = "0 (%: 0)";
          remarksInput = "";
        }
        $('#MonthQtyTableBody #MonthQtyRow-'+id+' td:last-child').html(Qty);
        $('#MonthQtyTableBody #MonthQtyRow-'+id+' td:eq(3)').html(remarksInput);
        $("textarea#monthQtyRem").on("keyup change paste", function(event){
          // console.log('nagalaw');
          var count = countChar(this,250);
          $("#monthQtyRemLabel").html(count);
        });
      }
    });


    // return Qty;
  }

   function getAddMonthOldQty(id){
    customer = $("#cusAddMonthForecastId").val();
    package = $("#pkgAddMonthForecastId").val();
    device = $("#devAddMonthForecastId").val();
    deviceVariant = $("input#devvarAddMonthForecastId").val();
    array = new Array();
   
      array.push({
        CUSTOMER: customer,
        PKG: package,
        DEVICE_VARIANT: deviceVariant,
        DEVICE: device
      });

    var selectedMonth = $('#selectedMonthForecast-'+id).val();
    var Qty = 0;

    $.ajax({
      type: "POST",
      dataType: "json", 
      data: {
        array: array,
        month: selectedMonth,
        action: 'ADDINGMONTH'
      },
      url: "ajax/forecast/select/select_oldqty.php", 
      success: function(response){
        if(response['SUM'] != 0){
          Qty = response['SUM']+" (%: "+response['PERCENTAGE']+")";
          //remarksInput = "<textarea class='form-control' id='addMonthForecastRem' maxlength='250'></textarea>";
          remarksInput = "<textarea class='form-control' maxlength='250' id='addMonthQtyRem'></textarea><label id='addMonthQtyRemLabel'>250</label>";
        }
        else{
          Qty = "0 (%: 0)";
          remarksInput = "";
        }
        $('#addMonthForecastTableBody #MonthForecastRow-'+id+' td:last-child').html(Qty);
        $('#addMonthForecastTableBody #MonthForecastRow-'+id+' td:eq(4)').html(remarksInput);

        $("textarea#addMonthQtyRem").on("keyup change paste", function(event){
          // console.log('nagalaw');
          var count = countChar(this,250);
          $("#addMonthQtyRemLabel").html(count);
        });
      }
    });


    // return Qty;
  }

  function getDeviceHistoryCalendarDataTable(customerValue, pkgValue, searchBy, deviceId){

    //var row_group_index = 0;
    //dataHistArray = new Array();

    

    $.ajax({
      type: "POST",
      dataType: "json", 
      data: {
        customer: customerValue,
        pkg: pkgValue,
        searchBy: searchBy,
        device: deviceId
      },
      url: "ajax/forecast/select/select_device_hist_calendar_view.php", 
      success: function(response){
        if($.fn.DataTable.isDataTable( '#DeviceHistoryCalendarTable' ) ){
            $('#DeviceHistoryCalendarTable').DataTable().clear().destroy();
            $("#DeviceHistoryCalendarTableHead").html("");
            $("#DeviceHistoryCalendarTableBody").html("");
        }
        //$("#DeviceHistoryCalendarTableHead").html("");
        deviceHistHeader = response.header;
        deviceHistHeaderString = "";
        deviceHistHeaderString += "<tr>";
        $(deviceHistHeader).each(function(){
          self = this;
          deviceHistHeaderString += "<th>"+self+"</th>";
            
        });

        deviceHistHeaderString += "<th>Action</th>";

        deviceHistHeaderString += "</tr>";
        $("#DeviceHistoryCalendarTableHead").html(deviceHistHeaderString);
        //return false;
        deviceHistBody = response.body;
        if(deviceHistBody != null){
          deviceHistBodyString= "";
          i = 0;
          $(deviceHistBody).each(function(){
            self = this;
            deviceHistBodyString+="<tr>";
            j=0;
            counter=0;
            counter1=0;
            $(self).each(function(){
              self1 = this;
              if(j < 6){
                deviceHistBodyString+= "<td>"+self1+"</td>";
              }
              else{
                if(self1 == '---'){
                  deviceHistBodyString+= "<td>"+self1+"</td>";
                  counter1++;
                }
                else{
                  deviceHistBodyString+= "<td><a class='changeMonthQty' id='"+i+"-"+j+"'>"+self1+"</a></td>";
                }
                counter++;
                
              }
              
              j++;
            });
            if(counter1 == counter){
              deviceHistBodyString+="<td>Remove</td>";
            }
            else{
              deviceHistBodyString+="<td><a href = 'javascript:void(0)' style='color:red;' id='"+i+"' class='zeroOutQtyCalendarFunc'>Remove</a></td>";
            }
            
            deviceHistBodyString+="</tr>";
            i++;
          });
          $("#DeviceHistoryCalendarTableBody").html(deviceHistBodyString);
          var table = $('#DeviceHistoryCalendarTable').DataTable({
            "columnDefs": [
            {
                "targets": [ 0,1,2,3 ],
                "visible": false,
                "searchable": false
            },
            {
                "className": "dt-body-left",
                "targets": [4, 5, -1],

            },
            {
                "className": "dt-body-right",
                "targets": '_all',

            }
            ],
            "paging": false,
            "searching": false,
            "info": false,
            "bSort": false,
            "scrollCollapse": true,
            "scrollX": true,
            "scrollY": 250
            //"pageLength": 5
      
      

          });
        }
        else{
          $("#DeviceHistoryCalendarTableBody").html("");
        }
      }
    });

    


    
  }

  function approveForecastDataTable(approveBtnType, customer, pkg, device, user_admin){
    if(user_admin == 1){
      access = true;
    }
    else{
      access = false;
    }
    console.log(user_admin);
    
    approveTable = $("#approveForecastTable").DataTable({
      "ajax":{
              "url":"ajax/forecast/select/select_device_hist_approve.php",
              "type":"post",
              "data":{
                customer:customer,
                pkg:pkg,
                device:device,
                type: approveBtnType,
                user_admin: user_admin

              }
      },
      "columns":[
                
                { "data": "CUSTOMER", render: function ( data,type,row ){
                  //return data;
                  return data;
                } },
                { "data": "PKG", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "DEVICE", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "DEVICE_DESCRIPTION", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "MONTH", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "QTY", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "PLAN", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "STATUS", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "ACTION", "bSortable": false, render: function ( data,type,row ){
                  return data;
                  
                } }
      ],
      "pageLength": 5,
      "rowsGroup":[0,1,2,3],
      "dom": 'Bfrtip',
      "buttons": [{
              text: "Approve All",
              action: function ( e, dt, node, config ) {
                console.log("approving all");
                rowsData = approveTable.rows().data();
                array = new Array();
                $(rowsData).each(function(){
                  array.push(this);
                });

                console.log(array);
                swal({
                      title: "Are you sure you want to save?",
                      text: "",
                      type: "warning",
                      showCancelButton: true,
                      confirmButtonColor: '#DD6B55',
                      confirmButtonText: 'Yes, I am sure!',
                      cancelButtonText: "No, cancel it!",
                      closeOnConfirm: false,
                      closeOnCancel: false,
                      showLoaderOnConfirm: true
                },
                function(isConfirm){
           
                  if (isConfirm){

                    //$("#approveForecastModalBody").waitMe({effect : 'pulse', text : 'Loading...'});
                    //return false;
                    $.ajax({
                      type: "POST",
                      dataType: "json", 
                      data: {
                        arr: JSON.stringify(array)
                      },
                      url: "ajax/forecast/update/update_allforecast_status.php", 
                      //url: "ajax/forecast/update/try.php", 
                      success: function(response){
                        console.log(response);
                        if(response == 1){
                          swal("Success!", "", "success");
                          approveTable.ajax.reload();
                        }
                        else if(response == 2){
                          swal("You are not authorised to do this function", "", "error");
                        }
                        else{
                          swal("Error occured. Please ask for support", "", "error");
                        }
                        //$("#approveForecastModalBody").waitMe('hide');
                      }
                    });
                  } else {
                    swal("Cancelled", "", "error");
                  }
                });
                
              },
              enabled:  access
      }],
      "scrollX":true

    });

    if(approveTable != null || approveTable != ''){
      // approveTable.button(0).disabled();
      $(approveTable.table().container()).on('click', 'button.statusActionBtn', function () {
                //var data = forecastTable.row( $(this).parents('tr') ).data();
                buttonId = $(this).attr('id');
                var cell_clicked    = approveTable.cell(this).data();
                var row_clicked     = $(this).closest('tr');
                var row_object      = approveTable.row(row_clicked).data();

                console.log(row_object['DIST_ID']);
                console.log(buttonId);
                //return false;

                if(buttonId == "approveForecast"){
                  status = "Approved";
                  title = "Are you sure you want to approve this forecast?";
                }
                else if(buttonId == "cancelForecast"){
                  status = "Cancelled Approval";
                  title = "Are you sure you want to cancel this forecast?";
                }
                else if(buttonId == "reApproveForecast"){
                  status = "Approved";
                  title = "Are you sure you want to re-approve this forecast?";
                }
                else if(buttonId == "removeForecast"){
                  status = "Removed Forecast";
                  title = "Are you sure you want to remove this forecast?";
                }
                else if(buttonId == "reinsertForecast"){
                  status = "For Approval";
                  title = "Are you sure you want to re-insert this forecast?";
                }

                console.log(status);
                if(buttonId == "approveForecast"){
                  swal({
                    title: title,
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
                        data: {
                          dist_id: row_object['DIST_ID'],
                          status: status,
                          remarks: ""
                        },
                        url: "ajax/forecast/update/update_indivforecast_status.php", 
                        success: function(response){
                            console.log(response);
                            if(response = 1){
                                swal("Success", "", "success");
                                approveTable.ajax.reload();
                            }
                        }
                      });
                    } else {
                      swal("Cancelled", "", "error");
                    }
                  });
                }
                else{
                  swal({
                    title: "Please input remarks:",
                    text: "<textarea class='form-control' maxlength='250' id='approvalRem' onclick='approvalRemCharCounter()'></textarea><label id='approvalRemLabel'>250</label>",
                    // --------------^-- define html element with id
                    html: true,
                    showCancelButton: true,
                    closeOnConfirm: false,
                    //showLoaderOnConfirm: true,
                    //animation: "slide-from-top",
                    inputPlaceholder: "Write something"
                  }, function(isConfirm) {
                    if (isConfirm === false) return false;
                    var val = document.getElementById('approvalRem').value;
                    if (val === "") {
                      swal.showInputError("You need to write something!");
                      return false;
                    }
                    else{
                      swal({
                        title: title,
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
                            data: {
                              dist_id: row_object['DIST_ID'],
                              status: status,
                              remarks: val
                            },
                            url: "ajax/forecast/update/update_indivforecast_status.php", 
                            success: function(response){
                                console.log(response);
                                if(response = 1){
                                    swal("Success", "", "success");
                                    approveTable.ajax.reload();
                                }
                            }
                          });
                        } else {
                          swal("Cancelled", "", "error");
                        }
                      });
                    }
                    // get value using textarea id
                    
                    //swal("Nice!", "You wrote: " + val, "success");
                  });
                }

                

                
      });
    }
  }

  function approvalRemCharCounter(){
    $('#approvalRem').on('keyup change paste', function(event){
      var count = countChar(this,250);
      $("#approvalRemLabel").html(count);
    });
  }

  function saveAddMonth(dataArray1){
    $.ajax({
          type: "POST",
          dataType: "json",
          data: {
            array: dataArray1,
            input: custDevName
          },
          url: "ajax/forecast/insert/insert_forecast.php",
          success: function(response){
            console.log(response);
            
            // $('#forecastTable').DataTable().clear().destroy();
            // swal("Saved!", "", "success");
            swal({title: "Saved!",
                    text: "",
                    type: "success"},
                function(isConfirm){
                    if(isConfirm){
                        // getForecastDataTable();
                        $("#addMonthForecastModal").modal("hide");
                    }

            });
          }
    });
  }

  function addMonthRowForecastBtn(){
    console.log('click');
    var row_id = $("#addMonthForecastTable tr:last").attr("id");
    row_idSplit = row_id.split("-");
    var last_id = row_idSplit[1];
    new_id = parseInt(last_id) + 1;
  
    addMonthForecastOptions(new_id, "addEntry");
  }

  function openApproveForecastModal(approveBtnType, customer, pkg, device, user_admin){
    if($("#approveForecastModal").modal("show")){
        approveForecastDataTable(approveBtnType, customer, pkg, device, user_admin);
    }
  }

  function getDeviceBatchTbl(data){
    customer = data['customer'];
    pkg = data['pkg'];
    device = data['device'];
    $("#addMonthForecastByBatchDispTable").DataTable();
                    $.ajax({
                        type: "POST",
                        dataType: "json",
                        data: {
                          customer: customer,
                          pkg: pkg,
                          device: device
                        },
                        url: "ajax/forecast/select/select_device_batch.php", 
                        success: function(response){
                          data = response['data'];
                          if(data['IS_ARRAY'] === 1){
                            customerArr = data['CUSTOMER'];
                            monthsArr = data['MONTHS'];
                            pkgArr = data['PKG'];
                            deviceArr = data['DEVICE'];
                            monthlyQtyArr = data['MONTHLY_QTY'];

                            headRowString = "<tr>"
                            + "<th rowspan = '2'>Customer</th>"
                            + "<th rowspan = '2'>Package</th>"
                            + "<th rowspan = '2'>Device</th>";

                            $(monthsArr).each(function(){
                              headRowString += "<th colspan = '3'>"+this+"</th>";
                            });
                            headRowString += "<th rowspan = '2'>Action</th>"

                            headRowString += "</tr>"
                            + "<tr>";

                            $(monthsArr).each(function(){
                              headRowString += "<th>Forecast Qty</th>"
                              + "<th>Plan Qty</th>"
                              + "<th>Percentage</th>";
                            });
                            headRowString += "</tr>";

                            bodyRowString = "";

                            $(customerArr).each(function(){
                              customerVal = this;
                              $(pkgArr[customerVal]).each(function(){
                                pkgVal = this;
                                $(deviceArr[customerVal][pkgVal]).each(function(){
                                  deviceVal = this['DEVICE'];
                                  found = 0;
                                  for(i=0; i<monthsArr.length; i++){
                                    monthVal = monthsArr[i];
                                    if(monthlyQtyArr[customerVal][pkgVal][deviceVal][monthVal] != null){
                                      found = 1;
                                      break;
                                    }
                                    else{
                                      found = 0;
                                      continue;
                                    }
                                  }
                                  if(found == 1){
                                    
                                    bodyRowString += "<tr>";
                                    bodyRowString += "<td>"+customerVal+"</td>";
                                    bodyRowString += "<td>"+pkgVal+"</td>";
                                    bodyRowString += "<td>"+deviceVal+"</td>";
                                    $(monthsArr).each(function(){
                                      monthVal = this;
                                      if(monthlyQtyArr[customerVal][pkgVal][deviceVal][monthVal] != null){
                                        $(monthlyQtyArr[customerVal][pkgVal][deviceVal][monthVal]).each(function(){
                                          bodyRowString += "<td>"+this['FORECAST']+"</td>";
                                          bodyRowString += "<td>"+this['PLAN']+"</td>";
                                          bodyRowString += "<td>"+this['PERCENTAGE']+"</td>";
                                        });
                                      }
                                      else{
                                        bodyRowString += "<td>"+0+"</td>";
                                        bodyRowString += "<td>"+0+"</td>";
                                        bodyRowString += "<td>"+0+"</td>";
                                      }
                                      
                                    });
                                    bodyRowString += "<td>"
                                    +"<button class='btn btn-sm btn-success devBatchTblBtns' title='add/edit this forecast individually' id='addMonthDevBatchBtn'>"
                                    +"<i class='material-icons' style='font-size: 1.5rem;'>open_in_new</i></button>"
                                    +"</td>";
                                    bodyRowString += "</tr>";
                                  }
                                });
                              });
                            });
                            if($.fn.DataTable.isDataTable( '#addMonthForecastByBatchDispTable' ) ){
                                $('#addMonthForecastByBatchDispTable').DataTable().clear().destroy();
                            }
                            $("#addMonthForecastByBatchDispTableHead").html(headRowString);
                            $("#addMonthForecastByBatchDispTableBody").html(bodyRowString);
                            $("#addMonthForecastByBatchDispModalBody").waitMe('hide');
                          }
                          else{
                            headRowString = "<tr>"
                            + "<th>Customer</th>"
                            + "<th>Package</th>"
                            + "<th>Device</th>"
                            + "<th>Action</th>"
                            + "</tr>";
                            if($.fn.DataTable.isDataTable( '#addMonthForecastByBatchDispTable' ) ){
                                $('#addMonthForecastByBatchDispTable').DataTable().clear().destroy();
                            }
                            $("#addMonthForecastByBatchDispTableHead").html(headRowString);
                            $("#addMonthForecastByBatchDispTableBody").html("");
                            // addMonthForecastByBatchDispDT();
                            $("#addMonthForecastByBatchDispModalBody").waitMe('hide');
                          } 
                          addMonthForecastByBatchDispDT();
                          
                        }
                    });
  }

  function addMonthForecastByBatchDispDT(){
    // if($.fn.DataTable.isDataTable( '#addMonthForecastByBatchDispTable' ) ){
    //     $('#addMonthForecastByBatchDispTable').DataTable().clear().destroy();
    // }
    tableBodyRowCount = $("#addMonthForecastByBatchDispTableBody tr").length;
    if(tableBodyRowCount != 0){
      deviceBatchDT = $("#addMonthForecastByBatchDispTable").DataTable({
        "scrollX": true,
        "pageLength": 5,
        "rowsGroup": [0,1,2],
        "fixedColumns":{leftColumns: 3, rightColumns: 1}
      });
    }
    else{
      deviceBatchDT = $("#addMonthForecastByBatchDispTable").DataTable({
        "scrollX": true,
        "pageLength": 5,
        "rowsGroup": [0,1,2]
      });
    }

    $(deviceBatchDT.table().container()).on('click', 'button.devBatchTblBtns', function () {
        //var data = forecastTable.row( $(this).parents('tr') ).data();
        buttonId = $(this).attr('id');
        var cell_clicked    = deviceBatchDT.cell(this).data();
        var row_clicked     = $(this).closest('tr');
        var row_object      = deviceBatchDT.row(row_clicked).data();

        var customer = deviceBatchDT.cell(row_clicked, 0).data();
        var pkg = deviceBatchDT.cell(row_clicked, 1).data();
        var device = deviceBatchDT.cell(row_clicked, 2).data();

        dataToPass = {
          customer: customer,
          pkg: pkg,
          device: device
        }
        response = getDeviceInfo(dataToPass);
        device_variant = response['DEVICE_VARIANT'];
        device_description = response['DEVICE_DESCRIPTION'];
        customer_dev_name = response['CUST_DEV_NAME'];
        inventory_item_id = response['INVENTORY_ITEM_ID'];
        

        if(buttonId == 'addMonthDevBatchBtn'){
                 
                  
          if($("#addMonthForecastModal").modal("show")){
            console.log(device_variant);
            string = "<label>CUSTOMER:</label>"
                    + "<input type='text' class='form-control' id='cusAddMonthForecastId' style='width: 50%' value='"+customer+"' readonly>"
                    + "<div style='padding:5px;'></div>"
                    + "<label>PKG:</label>"
                    + "<input type='text' class='form-control' id='pkgAddMonthForecastId' style='width: 50%' value='"+pkg+"' readonly>"
                    + "<div style='padding:5px;'></div>"
                    + "<label>DEVICE:</label>"
                    + "<input type='text' class='form-control' id='devAddMonthForecastId' style='width: 50%' value='"+device+"' readonly>"
                    + "<div style='padding:5px;'></div>"
                    + "<label>CUSTOMER DEVICE NAME:</label>"
                    + "<input type='text' class='form-control' id='custdevAddMonthForecastId' style='width: 50%' value='"+customer_dev_name+"'>"
                    + "<div style='padding:20px;'></div>"
                    + "<input type='hidden' id='addMonthForecastType' value='fromBatch'>"
                    + "<input type='hidden' id='devvarAddMonthForecastId' value='"+device_variant+"'>"
                    + "<input type='hidden' id='invidAddMonthForecastId' value='"+inventory_item_id+"'>"
                    + "<input type='hidden' id='devdescAddMonthForecastId' value='"+device_description+"'>"
                    + "<table class='table table-striped table-bordered' id='addMonthForecastTable'>"
                    + "<thead>"
                    + "<tr>"
                    + "<th>Month</th>"
                    + "<th>Percentage</th>"
                    + "<th>Forecast Qty</th>"
                    + "<th>Plan Qty</th>"
                    + "<th>Remarks</th>"
                    + "<th>Action</th>"
                    + "<th>Qty History</th>"
                    + "</tr>"
                    + "</thead>"
                    + "<tbody id='addMonthForecastTableBody'>"
                    + "</tbody>"
                    + "</table>"
                    + "<button type='button' class='btn btn-success' id='addMonthRowForecastBtn' onclick='addMonthRowForecastBtn()'>Add Month and Qty</button>";

            $("#addMonthForecastModalDesc").html(string);
            var tbody = $("#addMonthForecastTable tbody");

            if (tbody.children().length == 0) {
                // tbody.html("<tr>message foo</tr>");
                addMonthForecastOptions(0, 'firstEntry');
                $("#addMonthForecastTable").on("click", "td:first-child", function() {
                  //alert($( this ).text());
                  id = $(this).attr("id");
                  $("#selectedMonthForecast-"+id).on("change", function(){
                    getAddMonthOldQty(id);
                  });
              
                });
            }

                    
          }
        }
    });
  }

  function getDeviceInfo(data){
    dataToReturn = [];
    $.ajax({
          async: false,
          type: "POST",
          dataType: "json",
          data: {
            array: dataToPass
          },
          url: "ajax/forecast/select/select_device_info.php",
          success: function(response){
            dataToReturn = response;
            // console.log(response);
          }
        });
    return dataToReturn;
  }

  function numberWithCommas(x) {
    return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
  }