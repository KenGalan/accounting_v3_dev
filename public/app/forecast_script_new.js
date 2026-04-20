$(document).ready(function() {
    //functions
    Array.prototype.contains = function(v) {
      for (var i = 0; i < this.length; i++) {
        if (this[i] === v) return true;
      }
      return false;
    };

    Array.prototype.unique = function() {
      var arr = [];
      for (var i = 0; i < this.length; i++) {
        if (!arr.contains(this[i])) {
          arr.push(this[i]);
        }
      }
      return arr;
    }


    triggerCustomerDisplaySelection();
    
    forecastModalButtons();

    //from forecastModal on show
    
    selectCustomerOnChange();
    selectPkgOnChange();
    // selectPkgVarOnChange();
    // selectDevOnChange();

    //new forecast modal select chosen initialization
    $("#customerInput").chosen({search_contains: true, width: '100%'});
    $("#customerInput").val("");
    $("#customerInput").trigger("chosen:updated");

    $("#pkgInput").chosen({search_contains: true, width: '100%'});
    $("#pkgInput").val("");
    $("#pkgInput").trigger("chosen:updated");

    // $("#pkgVarInput").chosen({search_contains: true, width: '100%'});
    // $("#pkgVarInput").val("");
    // $("#pkgVarInput").trigger("chosen:updated");

    // $("#categoryInput").chosen({search_contains: true, width: '100%'});
    // $("#categoryInput").val("");
    // $("#categoryInput").trigger("chosen:updated");

    // $("#devInput").chosen({search_contains: true, width: '100%'});
    // $("#devInput").val("");
    // $("#devInput").trigger("chosen:updated");

    //forecastmodal on show
    $("#forecastModal").on("shown.bs.modal", function (e){
      selectCustomer();
      // getDeviceHistoryCalendarDataTable(0, 0, 0);
      
      $('#forecastForm').children('div').eq(0).addClass('activeStepDiv');
      forecastModalDisplay();

      //try
      $("#MonthQtyTableBody").on("change", ".selVarMonthClass", function(){
          id = $(this).closest("tr").attr("id").split("-")[1];
          console.log($(this).val());
              pkgVarSelectVal = $("#selectedVarMonth-"+id).val();
              categorySelectVal = $("#selectedCategMonth-"+id).val();
              if(categorySelectVal == '' || categorySelectVal == null || categorySelectVal == undefined){
                categorySelectVal = 'NULL';
              }
              else{
                categorySelectVal = categorySelectVal;
              }

              monthSelectVal = $("#selectedMonth-"+id).val();
              devSelectVal = $("#selectedDevMonth-"+id).val();
              
              var table = $("#MonthQtyTableBody");
              counter = 0;
              table.find('tr').each(function (j, el) {
                  var $tds = $(this).find('td');
                  // console.log($(this).attr("id"));
                  categoryTableVal = $tds.eq(1).find("select").val();
                  if(categoryTableVal == '' || categoryTableVal == null || categoryTableVal == undefined){
                    categoryTableVal = 'NULL';
                  }
                  else{
                    categoryTableVal = categoryTableVal;
                  }
                  if(id !== $(this).attr("id").split("-")[1]){
                    if(monthSelectVal === $tds.eq(3).find("select").val() && devSelectVal === $tds.eq(2).find("select").val() && categorySelectVal === categoryTableVal && pkgVarSelectVal === $tds.eq(0).find("select").val()){
                      counter++;
                    }
                  }
                             
              });

              if(counter === 0){
                categList = selectCategory(id);

                var markup = "<option value='' selected disabled>Please select</option>";

                if(categList != null){
                  $(categList).each(function(){
                      category = this;
                      markup += "<option value='"+category['CATEGORY']+"'>"+category['CATEGORY']+"</option>";
                      // $("#selectedCategMonth-"+id).append("<option value='"+category['CATEGORY']+"'>"+category['CATEGORY']+"</option>");
                  });
                }

                $("#selectedCategMonth-"+id).html(markup);
                $("#selectedCategMonth-"+id).chosen({search_contains: true});
                $("#selectedCategMonth-"+id).val("");
                $("#selectedCategMonth-"+id).trigger("chosen:updated");

                devList = selectDevice(id);

                var devMarkup = "<option value='' selected disabled>Please select</option>";

                if(devList != null){
                  $(devList).each(function(){
                      dev = this;
                      devMarkup += "<option value='"+dev['DEV_FAMILY']+"'>"+dev['DEV_FAMILY']+"</option>";
                      // $("#selectedCategMonth-"+id).append("<option value='"+category['CATEGORY']+"'>"+category['CATEGORY']+"</option>");
                  });
                }

                $("#selectedDevMonth-"+id).html(devMarkup);
                $("#selectedDevMonth-"+id).chosen({search_contains: true});
                $("#selectedDevMonth-"+id).val("");
                $("#selectedDevMonth-"+id).trigger("chosen:updated");

                getOldQty(id);
              }
              else{
                swal("Same Device and Month found!", "Please check Device and Month inputs.", "warning");
                $("#selectedVarMonth-"+id).val('').change();
                $("#selectedVarMonth-"+id).trigger("chosen:updated");
              }
      });

      $("#MonthQtyTableBody").on("change", ".selCategMonthClass", function(){
          id = $(this).closest("tr").attr("id").split("-")[1];

              pkgVarSelectVal = $("#selectedVarMonth-"+id).val();
              categorySelectVal = $("#selectedCategMonth-"+id).val();
              if(categorySelectVal == '' || categorySelectVal == null || categorySelectVal == undefined){
                categorySelectVal = 'NULL';
              }
              else{
                categorySelectVal = categorySelectVal;
              }
              console.log(categorySelectVal);
              monthSelectVal = $("#selectedMonth-"+id).val();
              devSelectVal = $("#selectedDevMonth-"+id).val();
              
              var table = $("#MonthQtyTableBody");
              counter = 0;
              table.find('tr').each(function (j, el) {
                  var $tds = $(this).find('td');
                  // console.log($(this).attr("id"));
                  categoryTableVal = $tds.eq(1).find("select").val();
                  if(categoryTableVal == '' || categoryTableVal == null || categoryTableVal == undefined){
                    categoryTableVal = 'NULL';
                  }
                  else{
                    categoryTableVal = categoryTableVal;
                  }
                  if(id !== $(this).attr("id").split("-")[1]){
                    if(monthSelectVal === $tds.eq(3).find("select").val() && devSelectVal === $tds.eq(2).find("select").val() && categorySelectVal === categoryTableVal && pkgVarSelectVal === $tds.eq(0).find("select").val()){
                      counter++;
                    }
                  }
                             
              });

              if(counter === 0){
                devList = selectDevice(id);

                var markup = "<option value='' selected disabled>Please select</option>";

                if(devList != null){
                  $(devList).each(function(){
                      dev = this;
                      markup += "<option value='"+dev['DEV_FAMILY']+"'>"+dev['DEV_FAMILY']+"</option>";
                      // $("#selectedCategMonth-"+id).append("<option value='"+category['CATEGORY']+"'>"+category['CATEGORY']+"</option>");
                  });
                }

                $("#selectedDevMonth-"+id).html(markup);
                $("#selectedDevMonth-"+id).chosen({search_contains: true});
                $("#selectedDevMonth-"+id).val("");
                $("#selectedDevMonth-"+id).trigger("chosen:updated");

                getOldQty(id);
              }
              else{
                swal("Same Device and Month found!", "Please check Device and Month inputs.", "warning");
                $("#selectedCategMonth-"+id).val('').change();
                $("#selectedCategMonth-"+id).trigger("chosen:updated");
              }
      });

      $("#MonthQtyTableBody").on("change", ".selDevMonthClass", function(){
          id = $(this).closest("tr").attr("id").split("-")[1];

              pkgVarSelectVal = $("#selectedVarMonth-"+id).val();
              categorySelectVal = $("#selectedCategMonth-"+id).val();
              if(categorySelectVal == '' || categorySelectVal == null || categorySelectVal == undefined){
                categorySelectVal = 'NULL';
              }
              else{
                categorySelectVal = categorySelectVal;
              }
              console.log(categorySelectVal);
              monthSelectVal = $("#selectedMonth-"+id).val();
              devSelectVal = $("#selectedDevMonth-"+id).val();
              
              var table = $("#MonthQtyTableBody");
              counter = 0;
              table.find('tr').each(function (j, el) {
                  var $tds = $(this).find('td');
                  // console.log($(this).attr("id"));
                  categoryTableVal = $tds.eq(1).find("select").val();
                  if(categoryTableVal == '' || categoryTableVal == null || categoryTableVal == undefined){
                    categoryTableVal = 'NULL';
                  }
                  else{
                    categoryTableVal = categoryTableVal;
                  }
                  if(id !== $(this).attr("id").split("-")[1]){
                    if(monthSelectVal === $tds.eq(3).find("select").val() && devSelectVal === $tds.eq(2).find("select").val() && categorySelectVal === categoryTableVal && pkgVarSelectVal === $tds.eq(0).find("select").val()){
                      counter++;
                    }
                  }
                             
              });

              if(counter === 0){
                getOldQty(id);
              }
              else{
                swal("Same Device and Month found!", "Please check Device and Month inputs.", "warning");
                $("#selectedDevMonth-"+id).val('').change();
                $("#selectedDevMonth-"+id).trigger("chosen:updated");
              }
      });

      $("#MonthQtyTableBody").on("change", ".selMonthClass", function(){
          id = $(this).closest("tr").attr("id").split("-")[1];

              monthSelectVal = $("#selectedMonth-"+id).val();
              devSelectVal = $("#selectedDevMonth-"+id).val();
              
              var table = $("#MonthQtyTableBody");
              counter = 0;
              table.find('tr').each(function (j, el) {
                  var $tds = $(this).find('td');
                  // console.log($(this).attr("id"));
                  categoryTableVal = $tds.eq(1).find("select").val();
                  if(categoryTableVal == '' || categoryTableVal == null || categoryTableVal == undefined){
                    categoryTableVal = 'NULL';
                  }
                  else{
                    categoryTableVal = categoryTableVal;
                  }
                  if(id !== $(this).attr("id").split("-")[1]){
                    if(monthSelectVal === $tds.eq(3).find("select").val() && devSelectVal === $tds.eq(2).find("select").val() && categorySelectVal === categoryTableVal && pkgVarSelectVal === $tds.eq(0).find("select").val()){
                      counter++;
                    }
                  }
                             
              });

              if(counter === 0){
                getOldQty(id);
              }
              else{
                swal("Same Device and Month found!", "Please check Device and Month inputs.", "warning");
                $("#selectedMonth-"+id).val('').change();
                $("#selectedMonth-"+id).trigger("chosen:updated");
              }
      });

      //
      // return false;
      // $("#MonthQtyTableBody").off("click", "td:first-child");
      // $("#MonthQtyTableBody").on("click", "td:first-child", function() {

      //       id = $(this).closest('tr').attr('id').split("-")[1];

      //       $("#selectedVarMonth-"+id).on("change", function(){
      //         pkgVarSelectVal = $("#selectedVarMonth-"+id).val();
      //         categorySelectVal = $("#selectedCategMonth-"+id).val();
      //         if(categorySelectVal == '' || categorySelectVal == null || categorySelectVal == undefined){
      //           categorySelectVal = 'NULL';
      //         }
      //         else{
      //           categorySelectVal = categorySelectVal;
      //         }
      //         console.log(categorySelectVal);
      //         monthSelectVal = $("#selectedMonth-"+id).val();
      //         devSelectVal = $("#selectedDevMonth-"+id).val();
              
      //         var table = $("#MonthQtyTableBody");
      //         counter = 0;
      //         table.find('tr').each(function (j, el) {
      //             var $tds = $(this).find('td');
      //             // console.log($(this).attr("id"));
      //             categoryTableVal = $tds.eq(1).find("select").val();
      //             if(categoryTableVal == '' || categoryTableVal == null || categoryTableVal == undefined){
      //               categoryTableVal = 'NULL';
      //             }
      //             else{
      //               categoryTableVal = categoryTableVal;
      //             }
      //             if(id !== $(this).attr("id").split("-")[1]){
      //               if(monthSelectVal === $tds.eq(3).find("select").val() && devSelectVal === $tds.eq(2).find("select").val() && categorySelectVal === categoryTableVal && pkgVarSelectVal === $tds.eq(0).find("select").val()){
      //                 counter++;
      //               }
      //             }
                             
      //         });

      //         if(counter === 0){
      //           categList = selectCategory(id);

      //           var markup = "<option value='' selected disabled>Please select</option>";

      //           if(categList != null){
      //             $(categList).each(function(){
      //                 category = this;
      //                 markup += "<option value='"+category['CATEGORY']+"'>"+category['CATEGORY']+"</option>";
      //                 // $("#selectedCategMonth-"+id).append("<option value='"+category['CATEGORY']+"'>"+category['CATEGORY']+"</option>");
      //             });
      //           }

      //           $("#selectedCategMonth-"+id).html(markup);
      //           $("#selectedCategMonth-"+id).chosen({search_contains: true, width: '100%'});
      //           $("#selectedCategMonth-"+id).val("");
      //           $("#selectedCategMonth-"+id).trigger("chosen:updated");

      //           devList = selectDevice(id);

      //           var devMarkup = "<option value='' selected disabled>Please select</option>";

      //           if(devList != null){
      //             $(devList).each(function(){
      //                 dev = this;
      //                 devMarkup += "<option value='"+dev['DEV_FAMILY']+"'>"+dev['DEV_FAMILY']+"</option>";
      //                 // $("#selectedCategMonth-"+id).append("<option value='"+category['CATEGORY']+"'>"+category['CATEGORY']+"</option>");
      //             });
      //           }

      //           $("#selectedDevMonth-"+id).html(devMarkup);
      //           $("#selectedDevMonth-"+id).chosen({search_contains: true, width: '100%'});
      //           $("#selectedDevMonth-"+id).val("");
      //           $("#selectedDevMonth-"+id).trigger("chosen:updated");

      //           getOldQty(id);
      //         }
      //         else{
      //           swal("Same Device and Month found!", "Please check Device and Month inputs.", "warning");
      //           $("#selectedVarMonth-"+id).val('').change();
      //           $("#selectedVarMonth-"+id).trigger("chosen:updated");
      //         }
      //       });
          
          
      // });

      // $("#MonthQtyTableBody").off("click", "td:nth-child(2)");
      // $("#MonthQtyTableBody").on("click", "td:nth-child(2)", function() {
      //       // id = $(this).attr("id");
      //       id = $(this).closest('tr').attr('id').split("-")[1];

      //       $("#selectedCategMonth-"+id).on("change", function(){
      //         pkgVarSelectVal = $("#selectedVarMonth-"+id).val();
      //         categorySelectVal = $("#selectedCategMonth-"+id).val();
      //         if(categorySelectVal == '' || categorySelectVal == null || categorySelectVal == undefined){
      //           categorySelectVal = 'NULL';
      //         }
      //         else{
      //           categorySelectVal = categorySelectVal;
      //         }
      //         console.log(categorySelectVal);
      //         monthSelectVal = $("#selectedMonth-"+id).val();
      //         devSelectVal = $("#selectedDevMonth-"+id).val();
              
      //         var table = $("#MonthQtyTableBody");
      //         counter = 0;
      //         table.find('tr').each(function (j, el) {
      //             var $tds = $(this).find('td');
      //             // console.log($(this).attr("id"));
      //             categoryTableVal = $tds.eq(1).find("select").val();
      //             if(categoryTableVal == '' || categoryTableVal == null || categoryTableVal == undefined){
      //               categoryTableVal = 'NULL';
      //             }
      //             else{
      //               categoryTableVal = categoryTableVal;
      //             }
      //             if(id !== $(this).attr("id").split("-")[1]){
      //               if(monthSelectVal === $tds.eq(3).find("select").val() && devSelectVal === $tds.eq(2).find("select").val() && categorySelectVal === categoryTableVal && pkgVarSelectVal === $tds.eq(0).find("select").val()){
      //                 counter++;
      //               }
      //             }
                             
      //         });

      //         if(counter === 0){
      //           devList = selectDevice(id);

      //           var markup = "<option value='' selected disabled>Please select</option>";

      //           if(devList != null){
      //             $(devList).each(function(){
      //                 dev = this;
      //                 markup += "<option value='"+dev['DEV_FAMILY']+"'>"+dev['DEV_FAMILY']+"</option>";
      //                 // $("#selectedCategMonth-"+id).append("<option value='"+category['CATEGORY']+"'>"+category['CATEGORY']+"</option>");
      //             });
      //           }

      //           $("#selectedDevMonth-"+id).html(markup);
      //           $("#selectedDevMonth-"+id).chosen({search_contains: true, width: '100%'});
      //           $("#selectedDevMonth-"+id).val("");
      //           $("#selectedDevMonth-"+id).trigger("chosen:updated");

      //           getOldQty(id);
      //         }
      //         else{
      //           swal("Same Device and Month found!", "Please check Device and Month inputs.", "warning");
      //           $("#selectedCategMonth-"+id).val('').change();
      //           $("#selectedCategMonth-"+id).trigger("chosen:updated");
      //         }
      //       });
            
      // });
      
      // $("#MonthQtyTableBody").off("click", "td:nth-child(3)");
      // $("#MonthQtyTableBody").on("click", "td:nth-child(3)", function() {
      //       // id = $(this).attr("id");
      //       id = $(this).closest('tr').attr('id').split("-")[1];

      //       $("#selectedDevMonth-"+id).on("change", function(){
      //         pkgVarSelectVal = $("#selectedVarMonth-"+id).val();
      //         categorySelectVal = $("#selectedCategMonth-"+id).val();
      //         if(categorySelectVal == '' || categorySelectVal == null || categorySelectVal == undefined){
      //           categorySelectVal = 'NULL';
      //         }
      //         else{
      //           categorySelectVal = categorySelectVal;
      //         }
      //         console.log(categorySelectVal);
      //         monthSelectVal = $("#selectedMonth-"+id).val();
      //         devSelectVal = $("#selectedDevMonth-"+id).val();
              
      //         var table = $("#MonthQtyTableBody");
      //         counter = 0;
      //         table.find('tr').each(function (j, el) {
      //             var $tds = $(this).find('td');
      //             // console.log($(this).attr("id"));
      //             categoryTableVal = $tds.eq(1).find("select").val();
      //             if(categoryTableVal == '' || categoryTableVal == null || categoryTableVal == undefined){
      //               categoryTableVal = 'NULL';
      //             }
      //             else{
      //               categoryTableVal = categoryTableVal;
      //             }
      //             if(id !== $(this).attr("id").split("-")[1]){
      //               if(monthSelectVal === $tds.eq(3).find("select").val() && devSelectVal === $tds.eq(2).find("select").val() && categorySelectVal === categoryTableVal && pkgVarSelectVal === $tds.eq(0).find("select").val()){
      //                 counter++;
      //               }
      //             }
                             
      //         });

      //         if(counter === 0){
      //           getOldQty(id);
      //         }
      //         else{
      //           swal("Same Device and Month found!", "Please check Device and Month inputs.", "warning");
      //           $("#selectedDevMonth-"+id).val('').change();
      //           $("#selectedDevMonth-"+id).trigger("chosen:updated");
      //         }
      //       });
            
      // });

      // $("#MonthQtyTableBody").off("click", "td:nth-child(4)");
      // $("#MonthQtyTableBody").on("click", "td:nth-child(4)", function() {
      //       // id = $(this).attr("id");
      //       id = $(this).closest('tr').attr('id').split("-")[1];

      //       $("#selectedMonth-"+id).on("change", function(){
      //         monthSelectVal = $("#selectedMonth-"+id).val();
      //         devSelectVal = $("#selectedDevMonth-"+id).val();
              
      //         var table = $("#MonthQtyTableBody");
      //         counter = 0;
      //         table.find('tr').each(function (j, el) {
      //             var $tds = $(this).find('td');
      //             // console.log($(this).attr("id"));
      //             categoryTableVal = $tds.eq(1).find("select").val();
      //             if(categoryTableVal == '' || categoryTableVal == null || categoryTableVal == undefined){
      //               categoryTableVal = 'NULL';
      //             }
      //             else{
      //               categoryTableVal = categoryTableVal;
      //             }
      //             if(id !== $(this).attr("id").split("-")[1]){
      //               if(monthSelectVal === $tds.eq(3).find("select").val() && devSelectVal === $tds.eq(2).find("select").val() && categorySelectVal === categoryTableVal && pkgVarSelectVal === $tds.eq(0).find("select").val()){
      //                 counter++;
      //               }
      //             }
                             
      //         });

      //         if(counter === 0){
      //           getOldQty(id);
      //         }
      //         else{
      //           swal("Same Device and Month found!", "Please check Device and Month inputs.", "warning");
      //           $("#selectedMonth-"+id).val('').change();
      //           $("#selectedMonth-"+id).trigger("chosen:updated");
      //         }
      //       });
            
      // });


        
    });

    //forecastmodal on hide
    $("#forecastModal").on("hidden.bs.modal", function (e){
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

        $("#customerInput").html("");
        $("#customerInput").val("");
        $("#customerInput").trigger("chosen:updated");

        $("#pkgInput").html("");
        $("#pkgInput").val("");
        $("#pkgInput").trigger("chosen:updated");

        $("#pkgVarInput").html("");
        $("#pkgVarInput").val("");
        $("#pkgVarInput").trigger("chosen:updated");

        $("#categoryInput").html("");
        $("#categoryInput").val("");
        $("#categoryInput").trigger("chosen:updated");

        $("#devInput").html("");
        $("#devInput").val("");
        $("#devInput").trigger("chosen:updated");

        $("#byDevice").val("byDevice");
        $("#byBD").val("byBD");
        
    });
    //deviceListTable on click (INPUT manipulation)
    var DELAY = 300, clicks = 0, timer = null;
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
        pkgVar = $("#pkgVarAddMonthForecastId").val();
        category = $("#categAddMonthForecastId").val();

        if(category == null || category == '' || category == '---'){
          category = null;
        }
        else{
          category = category;
        }
        // custDevName = $("input#custdevAddMonthForecastId").val();
        // deviceVariant = $("input#devvarAddMonthForecastId").val();
        // inventory_item_id = $("input#invidAddMonthForecastId").val();
        // device_description = $("input#devdescAddMonthForecastId").val();
        // console.log(inventory_item_id);
        // console.log(package);
        // console.log(device);
        // console.log(device_description);

        inputBlankCounterAddMonth = 0;
        var table = $("#addMonthForecastTableBody");
        table.find('tr').each(function (j, el) {
            var $tds = $(this).find('td'),
            month = $tds.eq(0).find("select").val(),
            // percentage = $tds.eq(1).find("input").val();
            qty = $tds.eq(1).find("input").val();
            capacity = $tds.eq(1).find("input").val();
            if($tds.eq(2).find("textarea").length > 0){
              remarks = $tds.eq(2).find("textarea").val();
              if((month == "" || month == null || month == undefined) || qty == "" || capacity == "" || remarks == ""){
                inputBlankCounterAddMonth++;
              }
            }
            else{
              if((month == "" || month == null || month == undefined) || qty == "" || capacity == ""){
                inputBlankCounterAddMonth++;
              }
            }
            //countqty = (parseInt(dataArray[i]['INPUT'])/100) * qty;
            
                           
        });
        // if(custDevName == '' || custDevName == null){
        //   alert("Customer Device Name field cannot be empty.");
        //   return false;
        // }

        if(inputBlankCounterAddMonth != 0){
          swal("Some fields are empty.", "Please do not leave field/s blank.", "warning");
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
            qty = $tds.eq(1).find("input").val(),
            capacity = $tds.eq(1).find("input").val();
            // percentage = $tds.eq(1).find("input").val();
                          // countqty = (parseInt(dataArray[i]['INPUT'])/100) * qty;
            if($tds.eq(2).find("textarea").length > 0){
              remarks = $tds.eq(2).find("textarea").val();
            }
            else{
              remarks = "";
            }
            dataArray1.push({
              customer: customer,
              pkg: package,
              pkgVar: pkgVar,
              category: category,
              device: device,
              month: month,
              capacity_qty: capacity.replace(/,/g, ''),
              //QTY: countqty
              // QTY: dataArray[i]['INPUT']
              forecast_qty: qty.replace(/,/g, ''),
              // PERCENT: percentage,
              remarks: remarks
            });
                           
          });

          console.log(dataArray1);
          // return false;

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

        console.log(dist_id);

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
          url: "ajax/forecast_new/select/select_toupdate_device.php",
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
                url: "ajax/forecast_new/update/update_forecast_qty.php",
                success: function(response){
                  console.log(response);
                  if(response == 1){
                    var customerValue = $("#customerInput").val();
                    //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
                    var pkgValue = $("#pkgInput").val();
                    //var pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
                    // var searchBy = $("input[name='devSearchType']:checked").val();
                    getDeviceHistoryCalendarDataTable(customerValue, pkgValue, $("#devInput").val());
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
              url: "ajax/forecast_new/update/update_forecast_device_calendar.php", 
              success: function(response){
                if(response == 1){
                  var customerValue = $("#customerInput").val();
                  //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
                  var pkgValue = $("#pkgInput").val();
                  //var pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
                  // var searchBy = $("input[name='devSearchType']:checked").val();
                  getDeviceHistoryCalendarDataTable(customerValue, pkgValue, $("#devInput").val());
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
      var user_access = $("input#user_access").val();
      openApproveForecastModal('main', '', '', '', user_admin, user_access);
    });

    $("#approveForecastModal").on("hidden.bs.modal", function(e){
      // $('#approveForecastTable').DataTable().clear().destroy();
      var approvalSectionTables = $("#approveForecastModalDesc").find('table');
      $(approvalSectionTables).each(function(){
        var innerTblId = $(this).attr('id');
        if($.fn.DataTable.isDataTable( '#'+innerTblId ) ){
          $('#'+innerTblId).DataTable().clear().destroy();
        }
      });
      // $( "#approveForecastModalDesc" ).tabs({ active: 0 });
      var currentTab = $('#approveForecastModalDesc').find('li.active a').attr('href');
      $('#approveForecastModalDesc').find('li.active').removeClass('in active');
      $(currentTab).removeClass('active');
      $('#approveForecastModalDesc').find('li:first').addClass('active');
      $('#approveForecastModalDesc').find('.tab-pane:first').addClass('in active');
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


    $("#reportsModal").on("show.bs.modal", function (e){
      $("#customer_rpt").chosen({search_contains: true, width: '100%'});
      getCustomerList("customer_rpt");
      

    });

    $("#reportsModal").on("hidden.bs.modal", function (e){
      $("#customer_rpt").html("<option value = '' selected disabled>----------SELECT----------</option>");
      $("#customer_rpt").trigger("chosen:updated");

      $("#frm_month_input").val("");
      $("#to_month_input").val("");
    });

    $("#submitReport").on("click", function (){
      nullCounter = 0;
      $(".reportsRequired").remove();

      $("#reportsModalFrm input.required, #reportsModalFrm select.required").each(function(){
          if(this.type != 'hidden'){
            if($(this).val() == "" || $(this).val() == null || $(this).val() == 'NULL'){
              nullCounter++;
              $(this).closest("div").append("<small class='reportsRequired text-danger'>Required Field.</small>");
            }
          }
      });

      if(nullCounter != 0){
        swal("Incomplete information.", "Please fill out required field/s.", "warning");
      }
      else{
        swal({
          title: "Generated!",
          text: "If the report is not generated, call for a support.",
          type: "success"
        },
        function(isConfirm){
              $.post('ajax/forecast_new/reports/prev_forecast_rpt.php', 
                { data: {
                    customer: $("#customer_rpt").val(),
                    month_from: $("#frm_month_input").val(),
                    month_to: $("#to_month_input").val()
                } }, 
                function(){
                  window.open('ajax/forecast_new/reports/prev_forecast_rpt.php', '_blank');
                }
              );
              //$("#reportsModal").modal("hide");
        });
        //$("#reportsModal").waitMe({effect : 'pulse', text : 'Downloading...'});
        //$.ajax({
          //type: "POST",
          //dataType: "json",
          //data: {
            //customer: $("#customer_rpt").val(),
            //month_from: $("#frm_month_input").val(),
            //month_to: $("#to_month_input").val()
          //},
          //url: "ajax/forecast_new/reports/prev_forecast_rpt.php",
          //success: function(response){
            //setTimeout(function(){
              //$("div#poEntryCardId").waitMe("hide");
              //swal({title: "Downloaded",
                  //text:  "If the report is not generated, call for a support.",
                  //type:  "success"},
              //function(isConfirm){
                  //$("#reportsModal").modal("hide");
              //});
            //}, 3000);
            

          //}
        //});
      }
      
    });
  
  
  });

  function getCustomerList(id){
    $.ajax({
          type: "POST",
          dataType: "json",
          url: "ajax/forecast_new/select/select_customers.php", 
          success: function(response){
            customerList = response;
            customerListSelect = "<option value = '' selected disabled>----------SELECT----------</option>";
            if(customerList != null){
              if(id == 'customer_rpt'){
                customerListSelect = "<option value = 'ALL'>ALL</option>";
              }
              $(customerList).each(function(){
                self = this;
                customerListSelect += "<option value = '"+self['CUSTOMER']+"'>"+self['CUSTOMER']+"</option>";
              });
            }
            $("#"+id).html(customerListSelect);
            $("#"+id).trigger("chosen:updated");
            //return responseData;  
            //returnMrpData(responseData);     
          }
    });
  }
  
  function triggerCustomerDisplaySelection(){
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "ajax/forecast_new/select/select_customers.php", 
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
        url: "ajax/forecast_new/select/select_forecast_2_new.php", 
        success: function(response){
          data = response['data'];
          if(data['IS_ARRAY'] == 1){
            customerArr = data['CUSTOMER'];
            monthsArr = data['MONTHS'];
            pkgArr = data['PKG'];
            varArr = data['VARIANT'];
            categArr = data['CATEGORY'];
            deviceArr = data['DEVICE'];
            deviceVarArr = data['DEVICE_VAR'];
            monthlyQtyArr = data['MONTHLY_QTY'];

            indexString = "6";
            index = 6;
            j=1;
            monthsArrCount = monthsArr.length;
            console.log(monthsArrCount);

            var date = new Date();
            var months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            var currentMonth = months[date.getMonth()]+" "+date.getFullYear();
            console.log(currentMonth.toUpperCase());

            headRowString = "<tr>"
            // + "<th rowspan = '2'>Item ID</th>"
            + "<th rowspan = '2'>Customer</th>"
            + "<th rowspan = '2'>Package</th>"
            + "<th rowspan = '2'>Package Variant</th>"
            + "<th rowspan = '2'>Category</th>"
            // + "<th rowspan = '2'>Customer Device Name</th>"
            + "<th rowspan = '2'>Device</th>";
            // + "<th rowspan = '2'>Device Variant</th>"
            // + "<th rowspan = '2'>Device Description</th>";

            $(monthsArr).each(function(){
              headRowString += "<th colspan = '3'>"+this+"</th>";
            });
            headRowString += "<th rowspan = '2'>Action</th>"

            headRowString += "</tr>"
            + "<tr>";

            $(monthsArr).each(function(){
              headRowString += "<th>Forecast Qty</th>"
              // + "<th>Capacity (Planned) Qty</th>"
              + "<th>PO Receipt Qty</th>"
              + "<th>PO Loaded Qty</th>";

                  
              if(j != monthsArrCount){
                index = index + 3;
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
                $(varArr[customerVal][pkgVal]).each(function(){
                  varVal = this;
                  $(categArr[customerVal][pkgVal][varVal]).each(function(){
                    categVal = this;
                    $(deviceVarArr[customerVal][pkgVal][varVal][categVal]).each(function(){
                      // deviceInvId = this['INVENTORY_ITEM_ID'];
                      deviceVal = this['DEVICE'];
                      // deviceInfo = data['DEVICE_INFO'][customerVal][pkgVal][deviceVal];
                      found = 0;
                      for(i=0; i<monthsArr.length; i++){
                        monthVal = monthsArr[i];
                        if(monthlyQtyArr[customerVal][pkgVal][varVal][categVal][deviceVal][monthVal] != null){
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
                        // bodyRowString += "<td>"+deviceInvId+"</td>";
                        bodyRowString += "<td>"+customerVal+"</td>";
                        bodyRowString += "<td>"+pkgVal+"</td>";
                        bodyRowString += "<td>"+varVal+"</td>";
                        bodyRowString += "<td>"+categVal+"</td>";
                        // bodyRowString += "<td>"+deviceCusto+"</td>";
                        bodyRowString += "<td>"+deviceVal+"</td>";
                        // bodyRowString += "<td>"+deviceVar+"</td>";
                        // bodyRowString += "<td>"+deviceDesc+"</td>";
                        $(monthsArr).each(function(){
                          monthVal = this;

                          if(monthlyQtyArr[customerVal][pkgVal][varVal][categVal][deviceVal][monthVal] != null){
                            $(monthlyQtyArr[customerVal][pkgVal][varVal][categVal][deviceVal][monthVal]).each(function(){
                              if(this['FORECAST'] != 0){
                                bodyRowString += "<td>"+numberWithCommas(this['FORECAST'])+"</td>";
                              }
                              else{
                                bodyRowString += "<td>"+"---"+"</td>";
                              }
                              // if(this['PLAN'] != 0){
                              //   bodyRowString += "<td>"+numberWithCommas(parseInt(this['PLAN']))+"</td>";
                              // }
                              // else{
                              //   bodyRowString += "<td>"+"---"+"</td>";
                              // }

                              if(this['AVAILABLE'] != 0){
                                bodyRowString += "<td>"+numberWithCommas(parseInt(this['AVAILABLE']))+"</td>";
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
                            // bodyRowString += "<td>"+"---"+"</td>";
                            bodyRowString += "<td>"+"---"+"</td>";
                            bodyRowString += "<td>"+"---"+"</td>";
                          }
                                          
                        });
                        bodyRowString+="<td>"
                                  +"<button class='btn btn-sm btn-success forecastTblBtns' title='add/edit this forecast individually' id='addMonthForecastBtn'>"
                                  +"<i class='material-icons' style='font-size: 1.5rem;'>open_in_new</i></button>";
                        // if(is_mis == 1){
                        //   bodyRowString+="<button class='btn btn-sm bg-purple forecastTblBtns' title='add/edit this forecast by batch' id='addMonthForecastBatchBtn'>"
                        //             +"<i class='material-icons' style='font-size: 1.5rem;'>open_in_new</i>"
                        //             +"</button>";
                        // }
                        bodyRowString+="</td>";
                      }
                    });
                  });
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
            // + "<th>Customer Device Name</th>"
            + "<th>Device</th>"
            // + "<th>Device Variant</th>"
            // + "<th>Device Description</th>"
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
    console.log(indexString);
    // $("#forecastTable").DataTable({'rowsGroup': [1,2,3, indexString], "scrollX": true});
    var forecastTable = $('#forecastTable').DataTable({
            'rowsGroup': [0,1,2,3, indexString], 
            "bSort" : false, 
            "scrollX": true, 
            "fixedColumns":{leftColumns: 5, rightColumns: 1},
            //"paging":false,
            //"scrollY":400,
            //"order":[[3, "desc"]],
            "columnDefs": [
            // {
            //     "targets": [4,6],
            //     "render": function ( data, type, row ) {
            //       return data.substr( 0, 22 )+"...";
            //     }
            // },
            // {
            //     //"targets": [ 0, 5 ],
            //     "targets": [ 0, 3, 5 ],
            //     "visible": false,
            //     "searchable": false
            // },

            {
                "className": "notexport",
                "targets": [-1]
            },
            {
                "className": "dt-body-left",
                "targets": [0,1,2,3,4]

            },
            {
                "className": "dt-body-right",
                "targets": '_all'

            }
            
            ],
            // dom: 'Bfrtip',
            // buttons: [{
            //   // html : true,
            //   extend: 'csvHtml5',
            //   text:   '<i class="material-icons">file_download</i> EXPORT',
            //   title:  'Forecast Report',
            //   className: 'btn btn-warning m-b-10 exportBtn',
            //   exportOptions:{
            //       columns: ':visible',
            //       format: {
            //         header: function(data, column){
            //           console.log('hi');
            //           console.log(data);
            //         }
            //       }
            //   }
              
            // }],
            "initComplete": function() {
              // alert( 'DataTables has finished its initialisation.' );
              if($("input#user_access").val() == 3 || $("input#user_access").val() == 6 || $("input#user_access").val() == 4 || $("input#user_access").val() == 7){
                // $(".exportBtn")[0].style.visibility = 'hidden';
                $("#forecastTable").DataTable().column(-1).visible(false);
              }
            }
            // buttons: [
            //     //'copy', 'csv', 'excel', 'pdf', 'print'
            //     {extend : 'excel',
            //         title : function() {
            //             return "FORECAST REPORT";
            //         },
            //         //exportOptions: { orthogonal: 'export' }
            //         exportOptions: {
            //              columns: ':visible:not(.notexport)'
            //              // format: {
            //              //      //this isn't working....
            //              //       header:  function (data, columnIdx) {
            //              //       // return columnIdx + ': ' + data + "blah";
            //              //       console.log(data);
            //              //    }
            //              //  }
            //          }
            //     }
            //     // {
            //     //     html: true,
            //     //     extend : 'pdfHtml5',
            //     //     title : function() {
            //     //         return "FORECAST REPORT";
            //     //     },
            //     //     orientation : 'landscape',
            //     //     pageSize :'A2',
            //     //     titleAttr : 'PDF',
            //     //     //exportOptions: { orthogonal: 'export' }
            //     //     exportOptions: {
            //     //          columns: ':visible:not(.notexport)'
            //     //     }
            //     // }
            // ]
            // createdRow: function (row, data, rowIndex) {
            //   //console.log(row);
            //   $('td:eq(0)', row).attr('title', data[4]);
            //   $('td:eq(2)', row).attr('data-toggle', "tooltip");
            //   $('td:eq(2)', row).attr('style', "cursor:pointer;");
            //   $('td:eq(3)', row).attr('title', data[6]);
            //   $('td:eq(3)', row).attr('data-toggle', "tooltip");
            //   $('td:eq(3)', row).attr('style', "cursor:pointer;");
            // },
            // ,fnInitComplete: function () {
            //   // $("[data-toggle='tooltip']").tooltip({
            //   //   container: 'body'
            //   // });
            //   $("thead th:last-child").addClass('notexport');
            //   this.fnDraw();
            //     this.fnAdjustColumnSizing();
            // }
          });

          /* Apply the tooltips */

          if(forecastTable != null || forecastTable != ''){
            $(forecastTable.table().container()).on('click', 'td:nth-child(8)', function () {
                var cell_clicked    = forecastTable.cell(this).data();
                var row_clicked     = $(this).closest('tr');
                var row_object      = forecastTable.row(row_clicked).data();
                console.log(row_object[6]);
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data:{
                      // INVENTORY_ITEM_ID: row_object[0]
                      CUSTOMER: row_object[0],
                      PACKAGE: row_object[1],
                      DEV_FAM: row_object[4]
                    },
                    url: "ajax/forecast_new/select/select_actual_info.php", 
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

                var customer = forecastTable.cell(row_clicked, 0).data();
                var pkg = forecastTable.cell(row_clicked, 1).data();
                var device = forecastTable.cell(row_clicked, 4).data();
                var pkgvar = forecastTable.cell(row_clicked, 2).data();
                var categ = forecastTable.cell(row_clicked, 3).data();
                // var device_variant = forecastTable.cell(row_clicked, 5).data();
                // var inventory_item_id = forecastTable.cell(row_clicked, 0).data();
                // var device_description = forecastTable.cell(row_clicked, 6).data();
                // var customer_dev_name = forecastTable.cell(row_clicked, 3).data();

                if(buttonId == 'addMonthForecastBtn'){
                 
                  
                  if($("#addMonthForecastModal").modal("show")){
                    // console.log(device_variant);
                    string = "<label>CUSTOMER:</label>"
                           + "<input type='text' class='form-control' id='cusAddMonthForecastId' style='width: 50%' value='"+customer+"' readonly>"
                           + "<div style='padding:5px;'></div>"
                           + "<label>PKG:</label>"
                           + "<input type='text' class='form-control' id='pkgAddMonthForecastId' style='width: 50%' value='"+pkg+"' readonly>"
                           + "<div style='padding:5px;'></div>"
                           + "<label>PKG VARIANT:</label>"
                           + "<input type='text' class='form-control' id='pkgVarAddMonthForecastId' style='width: 50%' value='"+pkgvar+"' readonly>"
                           + "<div style='padding:5px;'></div>"
                           + "<label>CATEGORY:</label>"
                           + "<input type='text' class='form-control' id='categAddMonthForecastId' style='width: 50%' value='"+categ+"' readonly>"
                           + "<div style='padding:5px;'></div>"
                           + "<label>DEVICE:</label>"
                           + "<input type='text' class='form-control' id='devAddMonthForecastId' style='width: 50%' value='"+device+"' readonly>"
                           + "<div style='padding:5px;'></div>"
                           // + "<label>CUSTOMER DEVICE NAME:</label>"
                           // + "<input type='text' class='form-control' id='custdevAddMonthForecastId' style='width: 50%' value='"+customer_dev_name+"'>"
                           + "<div style='padding:20px;'></div>"
                           + "<input type='hidden' id='addMonthForecastType' value='fromIndiv'>"
                           // + "<input type='hidden' id='devvarAddMonthForecastId' value='"+device_variant+"'>"
                           // + "<input type='hidden' id='invidAddMonthForecastId' value='"+inventory_item_id+"'>"
                           // + "<input type='hidden' id='devdescAddMonthForecastId' value='"+device_description+"'>"
                           + "<table class='table table-striped table-bordered' id='addMonthForecastTable'>"
                           + "<thead>"
                           + "<tr>"
                           + "<th>Month</th>"
                           // + "<th>Percentage</th>"
                           + "<th>Forecast Qty</th>"
                           // + "<th>Capacity (Planned) Qty</th>"
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
                        $("#addMonthForecastTable").off("click", "td:first-child");
                        $("#addMonthForecastTable").on("click", "td:first-child", function() {
                          //alert($( this ).text());
                          id = $(this).attr("id");
                          $("#selectedMonthForecast-"+id).off("change");
                          $("#selectedMonthForecast-"+id).on("change", function(){
                            
                            monthSelectVal = $("#selectedMonthForecast-"+id).val();
                            
                            var table = $("#addMonthForecastTableBody");
                            counter = 0;
                            table.find('tr').each(function (j, el) {
                                var $tds = $(this).find('td');
                                // console.log($(this).attr("id"));
                                console.log($(this).attr("id"));
                                if(id !== $(this).attr("id").split("-")[1]){
                                  if(monthSelectVal === $tds.eq(0).find("select").val()){
                                    counter++;
                                  }
                                }
                                           
                            });

                            if(counter === 0){
                              getAddMonthOldQty(id);
                            }
                            else{
                              swal("Same Month found!", "Please check Month inputs.", "warning");
                              $("#selectedMonthForecast-"+id).val('').change();
                              $("#selectedMonthForecast-"+id).trigger("chosen:updated");
                            }
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
        // count = $("#deviceListTable").DataTable().rows('.selected').count();
        // if(count == 0){
        //   alert("You have to choose device/s before proceeding!");

        // }
        // else{
          // $('#'+activeStepDivId).removeClass('activeStepDiv');
          // $('#forecastForm').children('div').eq(nextPageId).addClass('activeStepDiv');
          // forecastModalDisplay();
        // }

        var nullCounter = 0;

        $(".forecastModalRequired").remove();

        $("#forecastForm input.required, #forecastForm select.required, #forecastForm textarea.required").each(function(){
            if(this.type != 'hidden'){
              if($(this).val() == "" || $(this).val() == null){
                nullCounter++;
                $(this).closest("div").append("<small class='forecastModalRequired text-danger'>Required Field.</small>");
              }
            }
        });

        console.log(nullCounter);

        if(nullCounter != 0){
          swal("Warning!", "Please complete the form before proceeding.", "warning");

        }
        else{
          $('#'+activeStepDivId).removeClass('activeStepDiv');
          $('#forecastForm').children('div').eq(nextPageId).addClass('activeStepDiv');
          forecastModalDisplay();
        }
      }
      // else if(activeStepDivId == "secondPage"){
      //   inputBlankCounter = 0;
      //   selectedDevices = $("#deviceListTable").DataTable().rows('.selected').data();
      //   $(selectedDevices).each(function(){
      //     self = this;
      //     if(this['INPUT'] == ""){
      //       inputBlankCounter++;
      //     }
      //   });

      //   if(inputBlankCounter != 0){
      //     alert("Please do not leave field/s blank.");
      //   } 
      //   else{
      //     $('#'+activeStepDivId).removeClass('activeStepDiv');
      //     $('#forecastForm').children('div').eq(nextPageId).addClass('activeStepDiv');
      //     forecastModalDisplay();
      //   }
      // }
      else if(activeStepDivId == "secondPage"){
        inputBlankCounterQty = 0;
        var table = $("#MonthQtyTableBody");
        table.find('tr').each(function (j, el) {
            var $tds = $(this).find('td'),
            variant = $tds.eq(0).find("select").val();
            category = $tds.eq(1).find("select").val();
            device = $tds.eq(2).find("select").val();
            console.log(device);
            month = $tds.eq(3).find("select").val();
            qty = $tds.eq(4).find("input").val();
            // capqty = $tds.eq(5).find("input").val();
            if($tds.eq(5).find("textarea").length > 0){
              remarks = $tds.eq(5).find("textarea").val();
              if((variant == "" || variant == null || variant == undefined) || (device == "" || device == null || device == undefined) || (month == "" || month == null || month == undefined) || qty == "" || (remarks == "" || remarks == null || remarks == undefined)){
                inputBlankCounterQty++;
              }
            }
            else{
              if((variant == "" || variant == null || variant == undefined) || (device == "" || device == null || device == undefined) || (month == "" || month == null || month == undefined) || qty == ""){
                inputBlankCounterQty++;
              }
            }
            //countqty = (parseInt(dataArray[i]['INPUT'])/100) * qty;
            
                           
        });

        if(inputBlankCounterQty != 0){
          swal("Warning!", "Please do not leave field/s blank.", "warning");
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
    // else if(activeStepDivId == $("#forecastForm").children('div').eq(1).attr('id')){
    //   $("button#nextButton").show();
    //   $("button#backButton").show();
    //   $("button#save_exitButton").hide();
    //   $("button#save_contButton").hide();
    //   // if ( ! $.fn.DataTable.isDataTable( '#chosenDeviceListTable' ) ) {
    //     getChosenDeviceDataTable();
    //   // }
    //   // else{
    //   //   $("#chosenDeviceListTable").DataTable().draw();
    //   // }
      
    // }
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
          // //console.log(id);
          // deviceList = $("#devInput").val();

          // // console.log($("#devInput"));
          // // console.log(document.getElementById("selectedDevMonth-"+id).options.item());

          // // console.log($('#MonthQtyTableBody #MonthQtyRow-'+id+' td:first-child select#selectedDevMonth-'+id));

          // // options = document.getElementById("selectedDevMonth-"+id);
          // selected = $("#selectedDevMonth-"+id).val();
          // // optionsArr = new Array();
          // // $(options.options).each(function(){
          // //   optionsArr.push(this.text);
          // // }); 
          // // console.log(optionsArr);
          // // $(deviceList).each(function(){
          // //   device = this;
          // //   var contains = optionsArr.some(function(ele){
          // //     return JSON.stringify(ele) === JSON.stringify(device);
          // //   });
          // //   if(!contains){
          // //     console.log(device);
          // //     $("#selectedDevMonth-"+id).append("<option value='"+device+"'>"+device+"</option>");
          // //     // var o = new Option(device, "value");
          // //     // /// jquerify the DOM object 'o' so we can use the html method
          // //     // $(o).html(device);
          // //     // $("#selectedDevMonth"+id).append(o);
          // //     console.log(document.getElementById("selectedDevMonth-"+id).options);
          // //   }
          // // });

          // // return false;


          // markup = "<select class='form-control' id='selectedDevMonth-"+id+"'>";
          // markup += "<option value='' selected disabled>Please select</option>";
          // $(deviceList).each(function(){
          //   dev = this;
          //   markup += "<option value='"+dev+"'>"+dev+"</option>";
          // });
          // markup += "</select>";
          // $('#MonthQtyTableBody #MonthQtyRow-'+id+' td:first-child').html(markup);
          
          // var contains = deviceList.some(function(ele){
          //   return JSON.stringify(ele) === JSON.stringify(selected);
          // });
          // if(!contains){
          //   console.log('hey');
          //   $("#selectedDevMonth-"+id).val("");
          // }
          // else{
          //   console.log('heyo');
          //   $("#selectedDevMonth-"+id).val(selected);
          // }
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
        url: "ajax/forecast_new/select/select_customers.php", 
        success: function(response){
          customerString = "";
          if(response != null){
            $(response).each(function(){
              var self = this;
              customerString += "<option value='"+ self['CUSTOMER'] +"'>"+self['CUSTOMER']+"</option>";
            });
            $("#customerInput").html(customerString);
            $("#customerInput").val("");
            $("#customerInput").trigger("chosen:updated");
          }
        }
    });
  }
  
  function selectCustomerOnChange(){
    $("#customerInput").on("change", function(){
    //   var customerValue = $("#customerInput").val();
    //   var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    //   if(customerId != null){
        selectPackage();
    //   }
    });
  }
  
  function selectPackage(){
    var customerValue = $("#customerInput").val();
    //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    packageString = "<option selected disabled value=''>Select</option>";
    $("#pkgInput").html(packageString);
    $("#pkgInput").val("");
    $("#pkgInput").trigger("chosen:updated");

    pkgVarString = "<option selected disabled value=''>Select</option>";
    $("#pkgVarInput").html(pkgVarString);
    $("#pkgVarInput").val("");
    $("#pkgVarInput").trigger("chosen:updated");
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
        url: "ajax/forecast_new/select/select_packages.php", 
        success: function(response){
          packageString = "";
          if(response != null){
            $(response).each(function(){
              var self = this;
              packageString += "<option value='"+ self['PKG'] +"'>"+self['PKG']+"</option>";
            });
            $("#pkgInput").html(packageString);
            $("#pkgInput").val("");
            $("#pkgInput").trigger("chosen:updated");
          }
        }
      });
    }
  
  }

  function selectPkgOnChange(){
    $("#pkgInput").on("change", function(){
        // if($.fn.DataTable.isDataTable( '#DeviceHistoryCalendarTable' ) ){
        //   $('#DeviceHistoryCalendarTable').DataTable().clear().destroy();
        //   $("#DeviceHistoryCalendarTableBody").html("");
        //   $("#DeviceHistoryCalendarTableHead").html("");
        // }
        $("#MonthQtyTableBody").html("");
        // selectPkgVar();
        // $("#MonthQtyTableBody").html("");
    });
  }

  // function selectPkgVarOnChange(){
  //   $("#pkgVarInput").on("change", function(){
  //       selectCategory();
  //       // $("#MonthQtyTableBody").html("");
  //   });
  // }

  function selectDevice(id){
    var customerValue = $("#customerInput").val();
      //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    var pkgValue = $("#pkgInput").val();
    var pkgVarValue = $("#selectedVarMonth-"+id).val();
    var categoryValue = $("#selectedCategMonth-"+id).val();

    // deviceString = "<option selected disabled value=''>Select</option>";
    // $("#devInput").html(deviceString);
    // $("#devInput").val("");
    // $("#devInput").trigger("chosen:updated");

    responseData = null;

    if((customerValue != null || customerValue != "") && (pkgValue != null || pkgValue != "") && (pkgVarValue != null || pkgVarValue != "")){
      // $("#devInput").val('');
    //   $("#selPkg").html("");
      //selectPackage();
      $.ajax({
        async: false,
        type: "POST",
        dataType: "json", 
        data: {
          customer: customerValue,
          pkg: pkgValue,
          pkgVar: pkgVarValue,
          category: categoryValue

        },
        url: "ajax/forecast_new/select/select_devices_2.php", 
        success: function(response){
          deviceString = "";
          if(response['data'] != null){
            // $(response['data']).each(function(){
            //   var self = this;
            //   deviceString += "<option value='"+ self['DEV_FAMILY'] +"'>"+self['DEV_FAMILY']+"</option>";
            // });
            // // console.log(deviceString);
            // $("#devInput").html(deviceString);
            // $("#devInput").val("");
            // $("#devInput").trigger("chosen:updated");
            responseData = response['data'];
          }
          else{
            responseData = null;
          }
        }
      });
    }

    return responseData;
  }


  function selectPkgVar(){
    var customerValue = $("#customerInput").val();
    var pkgValue = $("#pkgInput").val();
    //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    // packageVarString = "<option selected disabled value=''>Select</option>";
    // $("#pkgVarInput").html(packageString);
    // $("#pkgVarInput").val("");
    // $("#pkgVarInput").trigger("chosen:updated");

    // categoryString = "<option selected disabled value=''>Select</option>";
    // $("#categoryInput").html(categoryString);
    // $("#categoryInput").val("");
    // $("#categoryInput").trigger("chosen:updated");

    responseData = null;

    if(customerValue != null && pkgValue != null){
      // $("#pkgInput").val('');
    //   $("#selPkg").html("");
      //selectPackage();
      $.ajax({
        async: false,
        type: "POST",
        dataType: "json", 
        data: {
          customer: customerValue,
          pkg: pkgValue
        },
        url: "ajax/forecast_new/select/select_package_variants.php", 
        success: function(response){
          packageVarString = "";
          if(response != null){
            // $(response).each(function(){
            //   var self = this;
            //   packageVarString += "<option value='"+ self['VARIANT'] +"'>"+self['VARIANT']+"</option>";
            // });
            // $("#pkgVarInput").html(packageVarString);
            // $("#pkgVarInput").val("");
            // $("#pkgVarInput").trigger("chosen:updated");
            // console.log(response);
            responseData = response;
          }
          else{
            responseData = null;
          }
        }
      });
    }

    // console.log(responseData);

    return responseData;
  }

  function selectCategory(id){
    var customerValue = $("#customerInput").val();
    var pkgValue = $("#pkgInput").val();
    var pkgVarValue = $("#selectedVarMonth-"+id).val();

    responseData = null;
    //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    // categoryString = "<option selected disabled value=''>Select</option>";
    // $("#categoryInput").html(categoryString);
    // $("#categoryInput").val("");
    // $("#categoryInput").trigger("chosen:updated");

    // deviceString = "<option selected disabled value=''>Select</option>";
    // $("#devInput").html(deviceString);
    // $("#devInput").val("");
    // $("#devInput").trigger("chosen:updated");
    if(customerValue != null && pkgValue != null && pkgVarValue != null){
      // $("#pkgInput").val('');
    //   $("#selPkg").html("");
      //selectPackage();
      $.ajax({
        async: false,
        type: "POST",
        dataType: "json", 
        data: {
          customer: customerValue,
          pkg: pkgValue,
          pkgVar: pkgVarValue
        },
        url: "ajax/forecast_new/select/select_pkgvar_categories.php", 
        success: function(response){
          categoryString = "";
          if(response != null){
            responseData = response;
          }
          else{
            responseData = null;
          }
        }
      });
    }

    return responseData;
  }
  
  function selectDevOnChange(){
    $("#devInput").on("change", function(){ 
      console.log('ok');
      var customerValue = $("#customerInput").val();
      //var customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
      var pkgValue = $("#pkgInput").val();
      //var pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
      // var searchBy = $("input[name='devSearchType']:checked").val();
  
      if(customerValue != null && pkgValue != null){
        // $('#deviceListTable').DataTable().clear().destroy();
        // getDeviceDataTable(customerValue, pkgValue, searchBy, $("#bdInput").val());
        //getDeviceHistoryDataTable(customerValue, pkgValue, searchBy, $("#bdInput").val());
        // getDeviceHistoryCalendarDataTable(customerValue, pkgValue, $("#devInput").val());
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
    
    // var duplicates = [1, 3, 4, 2, 1, 2, 3, 8];
    // var uniques = duplicates.unique(); // result = [1,3,4,2,8]

    // console.log(uniques);
    // return false;
    // dataArray = $("#deviceListTable").DataTable().rows('.selected').data();
    var months = new Array(), devices =  new Array(), categories = new Array(), variants = new Array();

    var table = $("#MonthQtyTableBody");
  
    tableHeader = new Array('Customer', 'Package', 'Package Variant', 'Category', 'Device');
    
    
    table.find('tr').each(function (j, el) {
        var $tds = $(this).find('td');

        months.push($tds.eq(3).find("select").val());
        devices.push($tds.eq(2).find("select").val()); 
        if($tds.eq(1).find("select").val() == '' || $tds.eq(1).find("select").val() == null || $tds.eq(1).find("select").val() == undefined){
          category = 'null';
        }
        else{
          category = $tds.eq(1).find("select").val();
        }
        categories.push(category);

        variants.push($tds.eq(0).find("select").val());

    });

    months = months.unique();
    months = months.sort(function(a,b) {
      // a = a.split(" ");
      // b = b.split(" ");

      a = new Date(a);
      b = new Date(b);

      return new Date(a.getFullYear(), a.getMonth() + 1, 1) - new Date(b.getFullYear(), b.getMonth() + 1, 1);
    });
    devices = devices.unique();
    categories = categories.unique();
    variants = variants.unique();

    $(months).each(function(){
      tableHeader.push(this.toString());
    });

    // dataArray1 = new Array();
    dataArray = [];

    var customer = $("#customerInput").val(),
    pkg = $("#pkgInput").val();
    // pkgVar = $("#pkgVarInput").val(),
    // category = $("#categoryInput").val();
    $(variants).each(function(){
      pkgVar = this.toString();

      $(categories).each(function(){
        category = this.toString();

        $(devices).each(function(){
          device = this.toString();

          deviceInside = new Array;
          $(months).each(function(){
            month = this.toString();
            table.find('tr').each(function(j, el){
              $tds = $(this).find('td');
              // pkgVar = $tds.eq(0).find("select").val();
              // category = $tds.eq(1).find("select").val();
              categoryTable = $tds.eq(1).find("select").val();
              if(categoryTable == null || categoryTable == '' || categoryTable == undefined){
                categoryTable = 'null';
              }
              else{
                categoryTable = categoryTable;
              }

              if(pkgVar == $tds.eq(0).find("select").val() && category == categoryTable && device == $tds.eq(2).find("select").val() && month == $tds.eq(3).find("select").val()){
                deviceInside[month] = [];
                deviceInside[month] = {
                  // customer: $("#customerInput").val(),
                  // pkg: $("#pkgInput").val(),
                  forecast: $tds.eq(4).find("input").val(),
                  plan: $tds.eq(4).find("input").val()
                };
              }
            });
          });
          console.log(deviceInside);
          if(Object.keys(deviceInside).length != 0){
            dataArray[customer+"~"+pkg+"~"+pkgVar+"~"+category+"~"+device] = [];
            dataArray[customer+"~"+pkg+"~"+pkgVar+"~"+category+"~"+device] = deviceInside;
          }
        });
      });
    });
    
    // console.log(dataArray);
    // return false;
          
    iPrevTblHead = 0;
    countPrevTblHead = 0;


    previewTableHead = "<tr>";
    forecastIndexStr = "2"; 
    forecastIndex = 0;
    $(tableHeader).each(function(){
      self = this;
      if(iPrevTblHead < 5){
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
      previewTableHead += "<th>Capacity</th>";
      //previewTableHead += "<th>Capacity</th>";
    }
    previewTableHead += "</tr>";
    $("#previewTableHead").html(previewTableHead);
  
    previewTableBody ="";

    var dataArrayOuterKeys = Object.keys(dataArray);
    
    for (var i = 0; i < dataArrayOuterKeys.length; i++) {
      var dataArrayInnerKeys = Object.keys(dataArray[dataArrayOuterKeys[i]]);
      previewTableBody += "<tr>";
      previewTableBody += "<td>"+dataArrayOuterKeys[i].split("~")[0]+"</td>";
      previewTableBody += "<td>"+dataArrayOuterKeys[i].split("~")[1]+"</td>";
      previewTableBody += "<td>"+dataArrayOuterKeys[i].split("~")[2]+"</td>";
      previewTableBody += "<td>"+dataArrayOuterKeys[i].split("~")[3]+"</td>";
      previewTableBody += "<td>"+dataArrayOuterKeys[i].split("~")[4]+"</td>";
      // for (var z = 0; z < dataArrayInnerKeys.length; z++) {
      for(z = 0; z < months.length; z++){
        // console.log(dataArray[keyssss[i]][keysaloob[z]])
        if(dataArray[dataArrayOuterKeys[i]][months[z]] != null){
          previewTableBody += "<td>"+numberWithCommas(dataArray[dataArrayOuterKeys[i]][months[z]]['forecast'])+"</d>";
          previewTableBody += "<td>"+numberWithCommas(dataArray[dataArrayOuterKeys[i]][months[z]]['plan'])+"</td>";
        }
        else{
          previewTableBody += "<td>"+0+"</td>";
          previewTableBody += "<td>"+0+"</td>";
        }
        
        
      }
      previewTableBody += "</tr>";
    }

   
    $("#previewTableBody").html(previewTableBody);
    $('#previewTable').DataTable({ "rowsGroup":[forecastIndexStr], "bSort" : false, "searching": false, "scrollX": true,
        "columnDefs": [
            {
                "className": "dt-body-left",
                "targets": [0, 1, 2, 3, 4],

            },
            {
                "className": "dt-body-right",
                "targets": '_all',

            }
        ],
        scrollX: true
    });
  }
  
  function save(savingType){
    dataArray = new Array();

    var table = $("#MonthQtyTableBody");
    var customer = $("#customerInput").val();
    var pkg = $("#pkgInput").val();
    // var pkgVar = $("#pkgVarInput").val();
    // var category = $("#categoryInput").val();

    table.find('tr').each(function (j, el) {
      var $tds = $(this).find('td');
      if($tds.eq(5).find("textarea").length > 0){
        remarks = $tds.eq(5).find("textarea").val();
      }
      else{
        remarks = "";
      }
      forecast_qty = $tds.eq(4).find("input").val();
      // capacity_qty = $tds.eq(5).find("input").val();
      capacity_qty = forecast_qty;
      dataArray.push({
        customer: customer,
        pkg: pkg, 
        pkgVar: $tds.eq(0).find("select").val(),
        category: $tds.eq(1).find("select").val(),
        device: $tds.eq(2).find("select").val(),
        month: $tds.eq(3).find("select").val(),
        forecast_qty: forecast_qty.replace(/,/g, ''),
        capacity_qty: capacity_qty.replace(/,/g, ''),
        remarks: remarks
      });
    });

    console.log(dataArray);

    // return false;
    
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
            array: JSON.stringify(dataArray)
          },
          url: "ajax/forecast_new/insert/insert_forecast.php", 
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
      url: "ajax/forecast_new/select/month_options.php", 
      success: function(response){
        if(response != null){
          // deviceList = $("#devInput").val();
          variantList = selectPkgVar();

          console.log(variantList);

          var markup = "<tr id='MonthQtyRow-"+id+"'>";

          markup += "<td width='15%'><select class='form-control selVarMonthClass' id='selectedVarMonth-"+id+"'>";
          markup += "<option value='' selected disabled>Please select</option>";
          if(variantList != null){
            $(variantList).each(function(){
              variant = this;
              // console.log(variant);
              // console.loog
              markup += "<option value='"+variant['VARIANT']+"'>"+variant['VARIANT']+"</option>";
            });
          }

          //category
          markup += "<td width='12%'><select class='form-control selCategMonthClass' id='selectedCategMonth-"+id+"'>";
          markup += "<option value='' selected disabled>Please select</option>";

          markup += "<td width='17%'><select class='form-control selDevMonthClass' id='selectedDevMonth-"+id+"'>";
          markup += "<option value='' selected disabled>Please select</option>";
          markup += "</select></td>";
          // if(deviceList != null){
          //   $(deviceList).each(function(){
          //     dev = this;
          //     markup += "<option value='"+dev['DEV_FAMILY']+"'>"+dev['DEV_FAMILY']+"</option>";
          //   });
          // }
          markup += "</select></td>";
          markup += "<td id='"+id+"'  width='17%'><select class='form-control selMonthClass' id='selectedMonth-"+id+"'>";
          markup += "<option value='' selected disabled>Please select</option>";
          // i = 0;
          $(response).each(function(){
            self =  this;
            // if(i == 0){
              markup += "<option value='"+self+"'>"+self+"</option>";
            // }
            // else{
            //   markup += "<option value='"+self+"'>"+self+"</option>";
            // }
            // i++;
            
          });
          markup += "</select></td>" 
                 +"<td width='10%'><input type='text' class='form-control' onkeypress='return forceNumber(event);' onkeyup='this.value=numberWithCommas(this.value);'></td>"
                 // +"<td width='10%'><input type='text' class='form-control' onkeypress='return forceNumber(event);' onkeyup='this.value=numberWithCommas(this.value);'></td>"
                 +"<td width='12%'></td>";
          if(entryNum == 'firstEntry'){
            markup += "<td width='5%'></td>";
          } 
          else{
            markup += "<td width='5%'><button type='button' class='btn btn-danger btn-sm' onclick='removeMonthQtyRow("+id+")'><i class='material-icons'>remove</i></button></td>" ;
          }

          markup += "<td width='12%'>Forecast Qty: 0<br/>Capacity (Planned) Qty: 0</td>";
                 // +"<td></td>" 
          markup += "</tr>";
          $("#MonthQtyTableBody").append(markup);
          // // $("#selectedDevMonth-"+id).chosen({search_contains: true, width: '100%'});
          // // $("#selectedDevMonth-"+id).trigger("chosen:updated");
          // $("#selectedMonth-"+id).val('').change();
          // $("#selectedDevMonth-"+id).val('').change();

          $("#selectedMonth-"+id).chosen({search_contains: true});
          // $("#selectedMonth-"+id).val("").change();
          $("#selectedMonth-"+id).trigger("chosen:updated");

          $("#selectedDevMonth-"+id).chosen({search_contains: true});
          // $("#selectedDevMonth-"+id).val("").change();
          $("#selectedDevMonth-"+id).trigger("chosen:updated");

          $("#selectedVarMonth-"+id).chosen({search_contains: true});
          // $("#selectedVarMonth-"+id).val("").change();
          $("#selectedVarMonth-"+id).trigger("chosen:updated");

          $("#selectedCategMonth-"+id).chosen({search_contains: true});
          // $("#selectedCategMonth-"+id).val("").change();
          $("#selectedCategMonth-"+id).trigger("chosen:updated");

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
      url: "ajax/forecast_new/select/month_options.php", 
      success: function(response){
        if(response != null){

          var markup = "<tr id='MonthForecastRow-"+id+"'>"
                     +"<td id='"+id+"'><select class='form-control' id='selectedMonthForecast-"+id+"'>"
                     +"<option value = '' selected disabled>Please Select Month </option>";
          // i = 0;
          $(response).each(function(){
            self =  this;
            // if(i == 0){
              // markup += "<option value='"+self+"'>"+self+"</option>";
            // }
            // else{
              markup += "<option value='"+self+"'>"+self+"</option>";
            // }
            // i++;
            
          });
          markup += "</select></td>" 
                 // +"<td><input type='number' class='form-control'></td>"
                 +"<td><input type='text' class='form-control' onkeypress='return forceNumber(event);' onkeyup='this.value=numberWithCommas(this.value);'></td>"
                 // +"<td><input type='text' class='form-control' onkeypress='return forceNumber(event);' onkeyup='this.value=numberWithCommas(this.value);'></td>"
                 +"<td></td>";
          if(entryNum == 'firstEntry'){
            markup += "<td></td>";
          } 
          else{
            markup += "<td><button type='button' class='btn btn-danger btn-sm' onclick='removeMonthForecastRow("+id+")'><i class='material-icons'>remove</i></button></td>" ;
          }

          markup += "<td>Forecast Qty: 0<br/>Capacity (Planned) Qty: 0</td>";
                 // +"<td></td>" 
          markup += "</tr>";
          $("#addMonthForecastTableBody").append(markup);

          $("#selectedMonthForecast-"+id).chosen({search_contains: true, width: '100%'});
          // $("#selectedMonthForecast-"+id).val("").change();
          $("#selectedMonthForecast-"+id).trigger("chosen:updated");
          getAddMonthOldQty(id);
        }
      }
    });
    
    
   
  }

  function getOldQty(id){
    // dataArray = $("#deviceListTable").DataTable().rows('.selected').data();
    array = new Array();
    // i = 0;
    // $(dataArray).each(function(){
    //   array.push({
    //     CUSTOMER: dataArray[i]['CUSTOMER'],
    //     PKG: dataArray[i]['PKG'],
    //     // DEVICE_VARIANT: dataArray[i]['BD_NO'],
    //     DEVICE: dataArray[i]['DEVICE']
    //   });
    //   i++;
    // }); 

    array.push({
      CUSTOMER: $("#customerInput").val(),
      PKG: $("#pkgInput").val(),
      PKGVAR: $("#selectedVarMonth-"+id).val(),
      CATEGORY: $("#selectedCategMonth-"+id).val(),
      DEVICE: $("#selectedDevMonth-"+id).val(),
      MONTH: $("#selectedMonth-"+id).val(),
      QTY: 0
    });

    var Qty = 0;
    // var customer = $("#customerInput").val(),
    // pkg = $("#pkgInput").val(),
    // selectedDevice = $("#selectedDevMonth-"+id).val(),
    // selectedMonth = $('#selectedMonth-'+id).val(),
    // Qty = 0;
    // customerValue = $("#customerInput").val();
    // //customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    // //   if(customerId != null){
    // pkgValue = $("#pkgInput").val();
    // //pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
    // device = $("#bdInput").val();

    $.ajax({
      type: "POST",
      dataType: "json", 
      data: {
        array: JSON.stringify(array)
        // action: 'FORECAST'
      },
      url: "ajax/forecast_new/select/select_oldqty.php", 
      success: function(response){
        if(response['CAPACITY_QTY'] != 0 || response['FORECAST_QTY'] != 0){
          console.log($("textarea#monthQtyRem-"+id).val());
          // Qty = response['CAPACITY_QTY'];
          //remarksInput = "<textarea class='form-control' id='monthForecastRem' maxlength='250'></textarea>";
          if($("textarea#monthQtyRem-"+id).val() == null || $("textarea#monthQtyRem-"+id).val() == undefined || $("textarea#monthQtyRem-"+id).val() == ''){
            remarksVal = '';
            remarksLength = 250;
          }
          else{
            remarksVal = $("#monthQtyRem-"+id).val();
            remarksLength = countChar("textarea#monthQtyRem-"+id, 250);
          }
          remarksInput = "<textarea class='form-control' maxlength='250' id='monthQtyRem-"+id+"'>"+remarksVal+"</textarea><label id='monthQtyRemLabel-"+id+"'></label>";
          $('#MonthQtyTableBody #MonthQtyRow-'+id+' td:eq(5)').html(remarksInput);
          $("#monthQtyRemLabel-"+id).html(remarksLength);
        }
        else{ 
          // Qty = "0";
          remarksInput = "";
          $('#MonthQtyTableBody #MonthQtyRow-'+id+' td:eq(5)').html(remarksInput);
          // remarksLength = 250;
        }

        Qty = "Forecast Qty: "+response['FORECAST_QTY']+"<br/>"+"Capacity (Planned) Qty: "+response['CAPACITY_QTY'];
        $('#MonthQtyTableBody #MonthQtyRow-'+id+' td:last-child').html(Qty);
        $("textarea#monthQtyRem-"+id).off("keyup change paste");
        $("textarea#monthQtyRem-"+id).on("keyup change paste", function(event){
          // console.log('nagalaw');
          console.log(this);
          var count = countChar(this,250);
          $("#monthQtyRemLabel-"+id).html(count);
        });
      }
    });


    // return Qty;
  }

   function getAddMonthOldQty(id){
    // dataArray = $("#deviceListTable").DataTable().rows('.selected').data();
    array = new Array();
    // i = 0;
    // $(dataArray).each(function(){
    //   array.push({
    //     CUSTOMER: dataArray[i]['CUSTOMER'],
    //     PKG: dataArray[i]['PKG'],
    //     // DEVICE_VARIANT: dataArray[i]['BD_NO'],
    //     DEVICE: dataArray[i]['DEVICE']
    //   });
    //   i++;
    // }); 

    array.push({
      CUSTOMER: $("#cusAddMonthForecastId").val(),
      PKG: $("#pkgAddMonthForecastId").val(),
      PKGVAR: $("#pkgVarAddMonthForecastId").val(),
      CATEGORY: $("#categAddMonthForecastId").val(),
      DEVICE: $("#devAddMonthForecastId").val(),
      MONTH: $('#selectedMonthForecast-'+id).val(),
      QTY: 0
    });

    var Qty = 0;
    // var customer = $("#customerInput").val(),
    // pkg = $("#pkgInput").val(),
    // selectedDevice = $("#selectedDevMonth-"+id).val(),
    // selectedMonth = $('#selectedMonth-'+id).val(),
    // Qty = 0;
    // customerValue = $("#customerInput").val();
    // //customerId = $("#selCustomer").find('option[value="' + customerValue + '"]').attr('id');
    // //   if(customerId != null){
    // pkgValue = $("#pkgInput").val();
    // //pkgId = $("#selPkg").find('option[value="' + pkgValue + '"]').attr('id');
    // device = $("#bdInput").val();

    $.ajax({
      type: "POST",
      dataType: "json", 
      data: {
        array: JSON.stringify(array)
        // action: 'ADDINGMONTH'
      },
      url: "ajax/forecast_new/select/select_oldqty.php", 
      success: function(response){
        if(response['CAPACITY_QTY'] != 0){
          // Qty = response['CAPACITY_QTY'];
          //remarksInput = "<textarea class='form-control' id='monthForecastRem' maxlength='250'></textarea>";
          remarksInput = "<textarea class='form-control' maxlength='250' id='addMonthQtyRem-"+id+"'></textarea><label id='addMonthQtyRemLabel-"+id+"''>250</label>";
        }
        else{
          // Qty = "0";
          remarksInput = "";
        }
        Qty = "Forecast Qty: "+response['FORECAST_QTY']+"<br/>"+"Capacity (Planned) Qty: "+response['CAPACITY_QTY'];
        // $('#MonthQtyTableBody #MonthQtyRow-'+id+' td:last-child').html(Qty);
        // $('#MonthQtyTableBody #MonthQtyRow-'+id+' td:eq(4)').html(remarksInput);
        $('#addMonthForecastTableBody #MonthForecastRow-'+id+' td:last-child').html(Qty);
        $('#addMonthForecastTableBody #MonthForecastRow-'+id+' td:eq(2)').html(remarksInput);
        $("textarea#addMonthQtyRem-"+id).on("keyup change paste", function(event){
          console.log('nagalaw');
          var count = countChar(this,250);
          $("#addMonthQtyRemLabel-"+id).html(count);
        });
      }
    });


    // return Qty;
  }

  function getDeviceHistoryCalendarDataTable(customerValue, pkgValue, deviceValue){

    //var row_group_index = 0;
    //dataHistArray = new Array();

    

    $.ajax({
      type: "POST",
      dataType: "json", 
      data: {
        customer: customerValue,
        pkg: pkgValue,
        device: JSON.stringify(deviceValue)
      },
      url: "ajax/forecast_new/select/select_device_hist_calendar_view.php", 
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
              if(j < 3){
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
            // {
            //     "targets": [ 0,1,2,3 ],
            //     "visible": false,
            //     "searchable": false
            // },
            {
                "className": "dt-body-left",
                "targets": [0,1,2,-1],

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
          var table = $('#DeviceHistoryCalendarTable').DataTable({
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

      }
    });

    


    
  }

  function approveForecastDataTable(approveBtnType, customer, pkg, device, user_admin, user_access, status, table){
    console.log(user_admin);
    if(status == 'For Approval'){
      newStatus = 'Approved';
      textAlert = 'Approve';
      if(user_admin == 1 || user_access == 2){
        access = true;
        removeBtnAccess = true;
      }
      else if(user_access == 5){
        newStatus = 'Removed Forecast';
        textAlert = 'Remove Forecast';
        access = true;
        removeBtnAccess = false;
      }
      else{
        access = false;
        removeBtnAccess = false;
      }
    }
    else if(status == 'Approved'){
      newStatus = 'Cancelled Approval';
      textAlert = 'Cancel Approval';
      if(user_admin == 1 || user_access == 2){
        access = true;
        removeBtnAccess = false;
      }
      else{
        access = false;
        removeBtnAccess = false;
      }
    }
    else if(status == 'Cancelled Approval'){
      newStatus = 'Removed Forecast';
      textAlert = 'Remove Forecast';
      if(user_admin == 1){
        access = true;
        removeBtnAccess = false;
      }
      else{
        access = false;
        removeBtnAccess = false;
      } 
    }
    else{
      newStatus = 'For Approval';
      textAlert = 'Re-insert Forecast';
      if(user_admin == 1){
        access = true;
        removeBtnAccess = false;
      }
      else{
        access = false;
        removeBtnAccess = false;
      } 
    }
    
    approveTable = $(table).DataTable({
      "ajax":{
              "url":"ajax/forecast_new/select/select_device_hist_approve.php",
              "type":"post",
              "data":{
                customer:customer,
                pkg:pkg,
                device:device,
                type: approveBtnType,
                user_admin: user_admin,
                user_access: user_access,
                status: status

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
                { "data": "VARIANT", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "CATEGORY", render: function ( data,type,row ){
                  return data;
                } },
                { "data": "DEVICE", render: function ( data,type,row ){
                  return data;
                } },
                // { "data": "DEVICE_DESCRIPTION", render: function ( data,type,row ){
                //   return data;
                // } },
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
                // { "data": "ACTION", "bSortable": false, render: function ( data,type,row ){
                //   return data;
                  
                // } },
                { "data": "", "bSortable": false, render: function ( data,type,row ){
                  return "";
                  
                } }
      ],
      "pageLength": 5,
      // "rowsGroup":[0,1,2,3,4,5],
      "order":[[0, "asc"], [1, "asc"], [2, "asc"], [3, "asc"],[4, "asc"], [5, "asc"]],
      "dom": 'Bfrtip',
      "buttons": [
        {
          text: textAlert,
          action: function ( e, dt, node, config ) {
            console.log("approving all");
            if(approveTable.rows('.selected').count() != 0){

                rowsData = approveTable.rows('.selected').data();
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
                        arr: JSON.stringify(array),
                        status: newStatus
                      },
                      url: "ajax/forecast_new/update/update_selforecast_status.php", 
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
            }
            else{
                swal("No selected data", "Please select row/s to do this action.", "warning");
            }   
          },
          className: 'mainBtn',
          enabled:  access
        },
        {
          text: 'Remove Forecast',
          action: function ( e, dt, node, config ) {
            console.log("remove all");
            if(approveTable.rows('.selected').count() != 0){

                rowsData = approveTable.rows('.selected').data();
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
                        arr: JSON.stringify(array),
                        status: 'Removed Forecast'
                      },
                      url: "ajax/forecast_new/update/update_selforecast_status.php", 
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
            }
            else{
                swal("No selected data", "Please select row/s to do this action.", "warning");
            }   
          },
          className: 'removeBtn',
          enabled:  removeBtnAccess
        }
      ],
      "columnDefs": [
      //   { "width": "23%", "targets": [0] },
      //   { "width": "23%", "targets": [1] },
      //   { "width": "12%", "targets": [2] },
      //   { "width": "12%", "targets": [3] },
      //   { "width": "15%", "targets": [4] },
      //   { "width": "15%", "targets": [5] },
        {
            orderable: false,
            className: 'select-checkbox',
            targets:   -1
        }
      ],
      "select": {
            selector: 'td:last-child',
            style: 'multi'
      },
      "scrollX":true,
      "initComplete": function(settings, json){
          $("div#approveForecastModalBody").waitMe("hide");

          if(removeBtnAccess == true){
            $(".removeBtn")[0].style.visibility = 'visible';
          }
          else{
            $(".removeBtn")[0].style.visibility = 'hidden';
          }

          if(access == true){
            $(".mainBtn")[0].style.visibility = 'visible';
          }
          else{
            $(".mainBtn")[0].style.visibility = 'hidden';
          }
      }

    });

    if ($(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").hasClass("selected")) {
        console.log('hasClass');
        $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").removeClass("selected");
    }

    if(approveTable.rows('.selected').count() > 0){
        // $("button.actionBtn").attr('disabled', false);
    }
    else{
        // $("button.actionBtn").attr('disabled', true);
    }

    if(approveTable != null || approveTable != ''){

      //datatable filter
      $('#approveForecastTable_filter input').off('keyup');
      $('#approveForecastTable_filter input').on('keyup', function(e) {
          // if(e.keyCode == 13) {
          //   oTable.fnFilter(this.value);   
          // }
          // approveTable.fnFilter(this.value);
          console.log(approveTable.rows({ search: 'applied', selected: true }).count());

          if(approveTable.rows({ search: 'applied', selected: true }).count() == approveTable.rows({ search: 'applied'}).count()){
            if ($(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").hasClass("selected")) {
                // $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").removeClass("selected");
            }
            else{
                $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").addClass("selected");
            }
          }
          else{
            if ($(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").hasClass("selected")) {
                $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").removeClass("selected");
            }
            else{
                // $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").addClass("selected");
            }
          }

      });  


      // select check-box
      $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").off("click");
      $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").on("click", function(event) { 
        event.preventDefault();
        console.log('clicked');
          if ($(this).hasClass("selected")) {
              approveTable.rows({ search: 'applied' }).deselect();
              $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").removeClass("selected");
          } else {
              // approveTable.row(':eq(2)', {page: 'current'}).select();
              approveTable.rows({ search: 'applied' }).select();
              $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").addClass("selected");
          }

          if(approveTable.rows('.selected').count() > 0){
            // $("button.actionBtn").attr('disabled', false);
          }
          else{
            // $("button.actionBtn").attr('disabled', true);
          }
      });

      approveTable.on("deselect", function() {
          // ("Some selection or deselection going on")
          if (approveTable.rows({
                  selected: true
              }).count() !== approveTable.rows().count()) {
              $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").removeClass("selected");
          } else {
              $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").addClass("selected");
          }

          if(approveTable.rows('.selected').count() > 0){
            // $("button.actionBtn").attr('disabled', false);
          }
          else{
            // $("button.actionBtn").attr('disabled', true);
          }
      }).on("select", function() {
          if (approveTable.rows({
                  selected: true
              }).count() !== approveTable.rows().count()) {
              $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").removeClass("selected");
          } else {
              $(".dataTables_scrollHeadInner>table>thead>tr>th.select-checkbox").addClass("selected");
          }

          if(approveTable.rows('.selected').count() > 0){
            // $("button.actionBtn").attr('disabled', false);
          }
          else{
            // $("button.actionBtn").attr('disabled', true);
          }
      });

      //buttons
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
                        url: "ajax/forecast_new/update/update_indivforecast_status.php", 
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
                            url: "ajax/forecast_new/update/update_indivforecast_status.php", 
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
            array: JSON.stringify(dataArray1),
            // input: custDevName
          },
          url: "ajax/forecast_new/insert/insert_forecast.php",
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

  function openApproveForecastModal(approveBtnType, customer, pkg, device, user_admin, user_access){
    if($("#approveForecastModal").modal("show")){
        $("div#approveForecastModalBody").waitMe({effect : 'pulse', text : 'Loading...'});
        approveForecastDataTable(approveBtnType, customer, pkg, device, user_admin, user_access, 'For Approval', '#approvalForecastTable');

        $("a[class='approval-tab']").off("click");
        $("a[class='approval-tab']").on("click", function(){
            $("div#approveForecastModalBody").waitMe({effect : 'pulse', text : 'Loading...'});
        });

        $('a[class="approval-tab"]').on('shown.bs.tab', function (e) {
            var approvalSectionTables = $("#approveForecastModalDesc").find('table');
                $(approvalSectionTables).each(function(){
                  var innerTblId = $(this).attr('id');
                  if($.fn.DataTable.isDataTable( '#'+innerTblId ) ){
                    $('#'+innerTblId).DataTable().clear().destroy();
                  }
            });

            var activeTab = $(this).attr('href');

            if(activeTab == '#forApproval'){
                status = 'For Approval';
                tblId = '#approvalForecastTable';
            }
            else if(activeTab == '#approved'){
                status = 'Approved';
                tblId = "#approvedForecastTable";
            }
            else if(activeTab == '#cancelled'){
                status = 'Cancelled Approval';
                tblId = "#cancelledForecastTable";
            }
            else{
                status = 'Removed Forecast';
                tblId = "#removedForecastTable";
            }

            approveForecastDataTable(approveBtnType, customer, pkg, device, user_admin, user_access, status, tblId);
        });
         // $("div#approveForecastModalBody").waitMe("hide");
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
                        url: "ajax/forecast_new/select/select_device_batch.php", 
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
                              // + "<th>Capacity (Planned) Qty</th>"
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
                    // + "<th>Capacity (Planned) Qty</th>"
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
                  // console.log('hi');
                  $("#selectedMonthForecast-"+id).off("change");
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
          url: "ajax/forecast_new/select/select_device_info.php",
          success: function(response){
            dataToReturn = response;
            // console.log(response);
          }
        });
    return dataToReturn;
  }

  // function numberWithCommas(x) {
  //   return x.toString().replace(/\B(?<!\.\d*)(?=(\d{3})+(?!\d))/g, ",");
  // }

  function numberWithCommas(n){
  // console.log(n);
    // console.log(n.toString().replace(/,/g, ""));
    // return faltoString()se;
    if(n != null || n != ''){
      n = n.toString().replace(/,/g, "");
      var s=n.split('.')[1];
      (s) ? s="."+s : s="";
      n=n.split('.')[0];
      while(n.length>3){
          s=","+n.substr(n.length-3,3)+s;
          n=n.substr(0,n.length-3)
      }
      return n+s;
    }
    else{
      return n;
    }
    
  }

  function forceNumber(e) {
    var keyCode = e.keyCode ? e.keyCode : e.which;
    if((keyCode < 48 || keyCode > 58) && keyCode != 188) {
        return false;
    }
    return true;
  }