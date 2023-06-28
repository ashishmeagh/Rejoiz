<script type="text/javascript">
  $(document).ready(function() {
    $('#example').DataTable();
   

} );
</script>
<style type="text/css">
  .dataTables_wrapper .col-sm-5, .dataTables_wrapper .col-sm-7 {padding: 10px 15px !important;}
    .modelTbl_th {
    /*background-color: #d3d3d3 !important;*/
    font-size: 14px;
    font-family: 'open_sanssemibold';
    color: #434040;
    letter-spacing: 0px;
    padding-right: 27px;
 }
</style>
<link href="{{url('/')}}/assets/css/select2.min.css" rel="stylesheet" />

{{-- https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css
https://cdn.datatables.net/1.10.21/css/dataTables.bootstrap4.min.css --}}
<?php 
    $colspan = 5;
    switch($user_type)
    {
        case "sales_manager": $col1 = "Representative"; $col1_key = "representative_name";
                              $col2 = "vendor"; $col2_key = "vendor_name";  
                              $commission_to = "Sales Manager"; 
                              $commission_forKey = "sales_manager_name";
                             break;
        case "representative": $col1 = "Sales Manager"; $col1_key = "sales_manager_name";
                               $col2 = "vendor"; $col2_key = "vendor_name";  
                               $commission_to = "Representative";
                               $commission_forKey = "representative_name";
                             break;
        default: $col1 = "Sales Manager"; $col1_key = "sales_manager_name";
                 $col2 = "Representative"; $col2_key = "representative_name";  
                 $commission_to = "Vendor";  $colspan = 6;
                 $commission_forKey = "vendor_name"; 
                             break;
    }
?>
<form id="bulkPay_modelForm" name="bulkPay_modelForm" method="post">
    {{ csrf_field() }}
    <input type="hidden" name="user_id" value="{{$user_id}}">
    <input type="hidden" name="user_type" value="{{$user_type}}">
    <table id="example" class="table table-striped table-bordered" style="width:100% font-size: 12px;">
        <thead>
            <tr>
                <th class="modelTbl_th"> 
                    <div class="checkbox checkbox-success">
                      <input type="checkbox" id="model_check_box_all" class="case check_model_itemAll" checked onclick="all_checkBoxOpration();">
                        <label for="checkbox"></label>
                    </div>
                </th>

                <th class="modelTbl_th">Order no</th>
                <?php   if($user_type == "vendor") { ?>
                         <th class="modelTbl_th">{{$col1}}</th>

                <?php } ?>
                <th class="modelTbl_th">{{$col2}}</th>
                <th class="modelTbl_th">Retailer</th>


                <th class="modelTbl_th">{{$commission_to}} <br/> Commission(%)</th>
                <th class="modelTbl_th">
                    Total Order Amount <br>
                    <em style="color: #0179e0;font-size: 11px;">(Excluded shipping costs)</em>
                </th>
                <th class="modelTbl_th">Admin Commission</th>
                <th class="modelTbl_th">{{$commission_to}} <br/> Commission</th>

                <th class="modelTbl_th">Action</th>
            </tr>
        </thead>
        <tbody>
        @php 
            $total_orderPrice = $total_adminCommission = $total_repCommissionAmount = 0;
            $cnt = 1;
            if(isset($posted_data) && count($posted_data) > 0)
            {
              
            foreach($posted_data as $order_id => $order_rows)
            { 

                $commission_for = isset($user_details[$order_id][$commission_forKey]) ? $user_details[$order_id][$commission_forKey] : "--";

                if(isset($order_rows) && count($order_rows) > 0)
               {

                foreach($order_rows as $keyData => $rowArr)
                {
                  // dd($rowArr);
                    //echo "<pre> user_details==>"; print_r($order_rows); exit();
     
                    $total_orderPrice += $rowArr['orderPrice'];
                    $total_adminCommission += $rowArr['adminCommission'];
                    $total_repCommissionAmount += $rowArr['repCommissionAmount'];

                    $bg_color = "#fff";
                    if($cnt++ % 2 == 0)
                        $bg_color = "#f6f6f6";

           @endphp

            <tr id="model_tr_{{$rowArr['order_id']}}" style="background-color:{{$bg_color}}">

                <input type="hidden" name="order_ids[]" value="{{$rowArr['order_id']}}">
                <input type="hidden" name="order_noArr[{{$order_id}}]" value="{{$user_details[$order_id]['order_no']}}">
                <input type="hidden" name="adminCommission[{{$order_id}}]" value="{{$rowArr['adminCommission']}}">
                <input type="hidden" name="orderPrice[{{$order_id}}]" value="{{$rowArr['orderPrice']}}">
                <input type="hidden" name="repCommissionAmount[{{$order_id}}]" value="{{$rowArr['repCommissionAmount']}}">

                <td align="center">
                    <input type="checkbox" name="model_orderId[]" id="model_orderId_{{$rowArr['order_id']}}" class="case check_modelOrderItems" value="{{$rowArr['order_id']}}" checked onclick="cal_total('{{$rowArr['order_id']}}', 1);">
                        <label for="checkbox"></label>
                </td>
                <td align="center"> 
                    <?php
                        echo $rowArr['orderNo'];
                    ?>  
                </td>

                <?php   if($user_type == "vendor") { ?>
                         <td align="center"><?php
                                if(isset($user_details[$order_id][$col1_key]))
                                    echo $user_details[$order_id][$col1_key];
                                else
                                    echo "--";
                            ?>
                        </td>
                <?php } ?>
                
                <td align="center">
                    <?php
                        if(isset($user_details[$order_id][$col2_key]))
                            echo $user_details[$order_id][$col2_key];
                        else
                            echo "--";
                    ?>  
               </td>
                <td align="center">
                    <?php
                        if(isset($user_details[$order_id]['retailer_name']))
                            echo $user_details[$order_id]['retailer_name'];
                        else
                            echo "--";
                    ?>  
                </td>

                <td align="center">{{num_format($rowArr['repcommission'])}}%</td>
                <td align="center">
                    $<span id="orderPrice_{{$rowArr['order_id']}}">
                       {{num_format($rowArr['orderPrice'])}}
                    </span>
                </td> 
                <td align="center">
                    $<span id="adminCommission_{{$rowArr['order_id']}}">
                       {{num_format($rowArr['adminCommission'])}}
                    </span>
                </td>
                <td align="center">
                    $<span id="repCommissionAmount_{{$rowArr['order_id']}}">
                        {{num_format($rowArr['repCommissionAmount'])}}
                    </span>
                </td>
                <td align="center">
                   <i class="fa fa-trash-o btn_mouseOver" aria-hidden="true" style="color: #cb0101;" onclick="removeRow('{{$rowArr['order_id']}}');" id="deleteRow_{{$rowArr['order_id']}}"></i> 
                   <span class="loader" id="loader_deleteRow_{{$rowArr['order_id']}}" style="color: #07892f; text-align:center;"> <i class="fa fa-spinner fa-pulse fa-3x fa-fw" style="font-size: 17px;"></i> </span>
                </td>
             </tr>


            <?php    
                }
                }
                }
            }

              ?>
        </tbody>
        <tfoot>
            <tr>
                

                <th colspan="{{$colspan}}" style="text-align: right;" class="modelTbl_th"> 
                    Total:&nbsp; 
                </th>
                <th style="text-align: center;" class="modelTbl_th">
                    $<span id="orderPrice_total">
                        {{num_format($total_orderPrice)}}
                    </span>
                </th>
                <th style="text-align: center;" class="modelTbl_th">
                    $<span id="adminCommission_total">
                        {{num_format($total_adminCommission)}}
                    </span>
                </th>
                <th style="text-align: center;" class="modelTbl_th">
                    $<span id="repCommissionAmount_total">
                        {{num_format($total_repCommissionAmount)}}
                    </span>
                </th>
                <th class="modelTbl_th"></th>
            </tr>

             
        </tfoot>
    </table>

    <input type="hidden" name="total_orderPrice" id="total_orderPrice" value="{{$total_orderPrice}}">
    <input type="hidden" name="total_adminCommission" id="total_adminCommission" value="{{$total_adminCommission}}">
    <input type="hidden" name="total_repCommissionAmount" id="total_repCommissionAmount" value="{{$total_repCommissionAmount}}">
</form>

<input type="hidden" id="prev_total_orderPrice" value="{{$total_orderPrice}}">
<input type="hidden" id="prev_total_adminCommission" value="{{$total_adminCommission}}">
<input type="hidden" id="prev_total_repCommissionAmount" value="{{$total_repCommissionAmount}}">

<script type="text/javascript">
    $("#bulkPay_title").html('{{$commission_to}}: <em>{{$commission_for}}</em>');

    function all_checkBoxOpration()
    {

        if($("#model_check_box_all"). prop("checked") == true){

            var prev_total_orderPrice = parseFloat($("#prev_total_orderPrice").val());
            var prev_total_adminCommission = parseFloat($("#prev_total_adminCommission").val());
            var prev_total_repCommissionAmount = parseFloat($("#prev_total_repCommissionAmount").val());

            $("input.check_modelOrderItems").prop('checked',true);
        }
        else
        {
            var prev_total_orderPrice = 0.00;
            var prev_total_adminCommission = 0.00;
            var prev_total_repCommissionAmount = 0.00;
           
            $("input.check_modelOrderItems").prop('checked',false);
        }

        $("#orderPrice_total").html(prev_total_orderPrice.toFixed(2));
        $("#adminCommission_total").html(prev_total_adminCommission.toFixed(2));
        $("#repCommissionAmount_total").html(prev_total_repCommissionAmount.toFixed(2));

        $("#total_orderPrice").val(prev_total_orderPrice.toFixed(2));
        $("#total_adminCommission").val(prev_total_adminCommission.toFixed(2));
        $("#total_repCommissionAmount").val(prev_total_repCommissionAmount.toFixed(2));

    }

    function removeRow(order_id)
    {
       
       
        swal({
                title: "Are you sure?",
                text: "Are you sure to remove this row!",
                type: "warning",
                confirmButtonText: "Yes, I am sure!",
                cancelButtonText: "No, cancel it!",
                showCancelButton: true,
                closeOnConfirm: true
             },
            function(isConfirm,tmp)
            {                       
                if(isConfirm==true)
                {
                    $("#deleteRow_"+order_id).hide();
                    $("#loader_deleteRow_"+order_id).fadeIn(500);
                    
                    if($("#model_orderId_"+order_id). prop("checked") == true){
                        cal_total(order_id, 0);
                    }

                    setTimeout(function() { 
                        $('#example').DataTable().row('#model_tr_'+order_id).remove().draw();
                    }, 1200);

                    
                    //$("#model_tr_"+order_id).remove().draw();
                }
            });

        
    }

    function cal_total(order_id, is_checkBox=0)
    {
        

        var orderPrice = $("#orderPrice_"+order_id).html();
        var adminCommission = $("#adminCommission_"+order_id).html();
        var repCommissionAmount = $("#repCommissionAmount_"+order_id).html();


        var orderPrice_total = $("#orderPrice_total").html();
        var adminCommission_total = $("#adminCommission_total").html();
        var repCommissionAmount_total = $("#repCommissionAmount_total").html();

        if(is_checkBox==1 && $("#model_orderId_"+order_id). prop("checked") == true){
            var cal_orderPrice_total = parseFloat(orderPrice_total) + parseFloat(orderPrice);
            var cal_adminCommission_total = parseFloat(adminCommission_total) + parseFloat(adminCommission);
            var cal_repCommissionAmount_total = parseFloat(repCommissionAmount_total) + parseFloat(repCommissionAmount);
        }
        else
        {
            var cal_orderPrice_total = parseFloat(orderPrice_total) - parseFloat(orderPrice);
            var cal_adminCommission_total = parseFloat(adminCommission_total) - parseFloat(adminCommission);
            var cal_repCommissionAmount_total = parseFloat(repCommissionAmount_total) - parseFloat(repCommissionAmount);

        }

        $("#orderPrice_total").html(cal_orderPrice_total.toFixed(2));
        $("#adminCommission_total").html(cal_adminCommission_total.toFixed(2));
        $("#repCommissionAmount_total").html(cal_repCommissionAmount_total.toFixed(2));

        $("#total_orderPrice").val(cal_orderPrice_total.toFixed(2));
        $("#total_adminCommission").val(cal_adminCommission_total.toFixed(2));
        $("#total_repCommissionAmount").val(cal_repCommissionAmount_total.toFixed(2));

        if(is_checkBox==0)
        {
            $("#prev_total_orderPrice").val(cal_orderPrice_total.toFixed(2));
            $("#prev_total_adminCommission").val(cal_adminCommission_total.toFixed(2));
            $("#prev_total_repCommissionAmount").val(cal_repCommissionAmount_total.toFixed(2));
        }
    }

    function bulk_payCommission()
    {
        
        if($('input.check_modelOrderItems:checked').length <= 0)
        {
          swal({
                     title:" Warning",
                     text: " Please Select atleast 1 checkbox for {{$commission_to}} bulk payment.",
                     type: "warning",
                     confirmButtonText: "OK",
                     closeOnConfirm: true
                  },
                 function(isConfirm,tmp)
                 {
                    return false;
                 });


          return false;

        }

         // var url = '{{url('/admin/payment/bulk_payCommission')}}';
         var modelForm_data = new FormData($("#bulkPay_modelForm")[0]);
         $.ajax({
          // url: url,
          url: '{{url('/admin/leads')}}'+'/bulk_payCommission',
          type:"POST",
          data: modelForm_data,
          contentType:false,
          processData:false,
          dataType:'json',
          beforeSend : function()
          {
            showProcessingOverlay();
           
          },
          success:function(data)
          { 
             hideProcessingOverlay();

             if('success' == data.status)
             {
                  swal({
                         title:"Success", 
                         text: data.message, 
                         type: data.status,
                         allowEscapeKey : false,
                         allowOutsideClick: false
                       },
                       function(){ 
                           location.reload();
                       }
                    );               
             }
             else if('warning' == data.status)
             {
                $('#user_id').val(data.user_id);

                $('.modal').modal('hide');

                $('#sendStripeLinkModel').modal('show');

             }
             else
             {
               swal("Error",data.message,data.status);
             }  
          }
        }); 
        
    }
</script>
<?php exit; ?>