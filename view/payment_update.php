<!DOCTYPE html>
<html>
<head>
<style>
body {
    font-family: Arial, sans-serif;
    padding: 20px;
    background: #e6e4e4ff;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 25px;
}

th, td {
    padding: 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    background-color: #ffffff;
}

th {
    background-color: #7C7BAD;
    color: #ffffff;
}
.containered{
    background-color: #ffffff;
    padding: 15px;
    border-radius: 5px;
}
.processed{
    margin-top: 85px;
}
</style>
</head>
<body>

<div class="containered">
    <h4>Payment Account</h4><br/>
    <button id="updatePaymentBtn" class="btn btn-primary mb-3" disabled>Update Status</button><br/><br/>
    <div class="filter-status" style="float:right; margin-right: 15px;">
        <input type="checkbox" id="released">
        <label for="released">Released</label>
        <input type="checkbox" id="cleared">
        <label for="cleared">Cleared</label>
    </div><br/>
<!-- <h4 style="float: right;">Selected Payments: <span id="selectedCount></span></h4> -->
<table id="distTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th style="width:40px;text-align:center">
                <!-- <input type="checkbox" id="selectAll">
                <label for="selectAll"></label> -->
            </th>
            <th>Payment Name</th>
            <th>Amount</th>
            <th>Customer</th>
            <th>Payment Creation Date</th>
            <th>State</th>  
            <!-- <th></th> -->
        </tr>
    </thead>
</table>
</div>

<div class="containered processed">
    <h4>Processed Payment</h4><br/>
<!-- <h4 style="float: right;">Selected Payments: <span id="selectedCount></span></h4> -->
<table id="histTable" class="display" style="width:100%">
    <thead>
        <tr>
            <th>Transacted By</th>
            <th>Payment Name</th>
            <th>Amount</th>
            <th>Customer</th>
            <th>Transaction Date</th>
            <th>State</th>  
            <!-- <th></th> -->
        </tr>
    </thead>
</table>
</div>

<script>

let table = $('#distTable').DataTable({
  ajax: {
        url: 'ajax/fetch/fetch_payment.php',
        dataSrc: 'data',
        data: function(d){

            d.released = $('#released').is(':checked') ? 1 : 0;
            d.cleared  = $('#cleared').is(':checked') ? 1 : 0;

        }
    }, 
    columns: [  

        { 
            data: null,
            orderable:false,
            searchable:false,
            render:function(data,type,row,meta){

                let i = meta.row + meta.settings._iDisplayStart;
                let checked = row.is_selected == 1 ? 'checked' : '';

                return `
                    <input type="checkbox" 
                        class="rowCheck"
                        id="rowCheck${i}"
                        value="${row.payment_id}"
                        data-selected="${row.is_selected}"
                        ${checked}>
                    <label for="rowCheck${i}"></label>
                `;
            }
        },

        { data: 'name' },
        // { data: 'amount' },
        {
            data: 'amount',
            render: function(data, type, row){
                if(type === 'display' || type === 'filter'){
                    return '₱ ' + parseFloat(data).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
                return data; 
            }
        },
        { data: 'partner' }, 
        { data: 'date' },
        { data: 'state' }, 
    ]
});

let histTable = $('#histTable').DataTable({
  ajax: {
        url: 'ajax/fetch/fetch_payment_hist.php',
        dataSrc: 'data',
    },
    columns: [  
        { data: 'fullname' },
        { data: 'payment_name' },
        // { data: 'payment_amount' },
                {
            data: 'payment_amount',
            render: function(data, type, row){
                if(type === 'display' || type === 'filter'){
                    return '₱ ' + parseFloat(data).toLocaleString('en-PH', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
                return data; 
            }
        },
        { data: 'partner_name' },
        { data: 'added_on' },
        { data: 'pymt_state' },
    ]
});

$('#released, #cleared').on('change', function(){
    table.ajax.reload();
});

table.on('draw', function(){

    table.rows().every(function(){
        let data = this.data();
        let checkbox = $(this.node()).find('.rowCheck');
        if(data.state === 'cleared'){
            checkbox.prop('disabled', true);
        } else {
            checkbox.prop('disabled', false);
        }
    });

    toggleButton();

});

$('#selectAll').on('change', function(){

    let checked = this.checked;

    $('.rowCheck').prop('checked', checked);

    toggleButton();

});

// $('#distTable tbody').on('change','.rowCheck', function(){

//     toggleButton();

//     let total = $('.rowCheck').length;
//     let checked = $('.rowCheck:checked').length;

//     $('#selectAll').prop('checked', total === checked);

// });

$('#distTable tbody').on('change','.rowCheck', function(){

    let checkbox = $(this);
    let payment_id = checkbox.val();
    let wasSelected = checkbox.data('selected'); 

    if(!checkbox.is(':checked') && wasSelected == 1){

        fetch('ajax/transaction/delete_temp_payment.php',{
            method:'POST',
            headers:{
                'Content-Type':'application/x-www-form-urlencoded'
            },
            body:'payment_id='+payment_id
        })
        .then(res=>res.json())
        .then(r=>{
            if(r.status === 'success'){
                checkbox.data('selected', 0);
            }else{
                alert('Failed to remove from temp');
                checkbox.prop('checked', true); 
            }
        });

    }

    toggleButton();
    
    let total = $('.rowCheck').length;
    let checked = $('.rowCheck:checked').length;

    $('#selectAll').prop('checked', total === checked);

});

function toggleButton(){

    let anyChecked = $('.rowCheck:checked').length > 0;

    $('#updatePaymentBtn').prop('disabled', !anyChecked);

}

$('#updatePaymentBtn').on('click', function(){

    let selected = [];

    $('.rowCheck:checked').each(function(){
        selected.push($(this).val());
        // console.log($(this).val());
    });

    if(selected.length === 0){
        alert('No payment selected');
        return; 
    }

    $.ajax({
        url:'ajax/transaction/payment_insert_temp.php',
        type:'POST',
        data:{  payments:selected,
        released: $('#released').is(':checked') ? 1 : 0,
        cleared: $('#cleared').is(':checked') ? 1 : 0 },
        beforeSend:function(){
            $('#updatePaymentBtn').prop('disabled',true).text('Processing...');
        },
        success:function(res){ 

            let r = JSON.parse(res);

            if(r.status === 'success'){

                let status = '';

                if($('#released').is(':checked')){
                    status = 'released';
                }
                else if($('#cleared').is(':checked')){
                    status = 'cleared';
                }
                else{
                    status = 'null';
                }

                window.location.href = 'ajax/fetch/temp_payments.php?status=' + status + '&temp_id=' + r.temp_id;

            }else{
                alert('Error saving payments');
            }

        }
    });

});

</script>
</body>
</html>


