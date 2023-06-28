@extends('maker.layout.master')  
@section('main_content')

<style type="text/css">
  .table tbody tr td .table{box-shadow: -2px 6px 14px -5px #b9b9b9;margin-top: 10px;}
  .pro-list-bg {
    position: relative;
  }
  .pro-list-bg span{
       display: inline-block;
    font-weight: 600;
    color: #333;
    width: 18px;
    height: 18px;
    text-align: center;
    background-color: #ececec;
    margin-left: 10px;
    border-radius: 50%;
    line-height: 18px;
    font-size: 10px;
  }

    .btn.btn-circle.btn-danger.btn-outline{
  border: 1px solid #dfdfdf;
    background-color: #fff;
    color: #444;
    font-size: 14px;
    padding: 13px 30px 12px;
    border-radius: 4px;
    display: inline-block;
    height: auto;
}

    th {
    white-space: nowrap;
}
.header-fix .modal-title{float: left;}
.header-fix  .close{float: right;}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-sm-12 top-bg-title">
            <h4 class="page-title">{{$module_title or ''}}</h4>
            <div class="right">
            <ol class="breadcrumb">
                <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
            @include('admin.layout._operation_status')
            <div class="white-box">

              <!-- <div class="pull-right">            
                <a href="{{$module_url_path or ''}}" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>               
              </div>  -->


             <div class="butns-right"> 
              <div class="col-sm-12 text-right export-btn-div p-0 mb-3">
                <button type="button" onclick="customerOrderExport()" title="Export customer orders as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right" value="export" id="customerOrderExport">Export</button>
              </div>
              <form class="form-horizontal" id="report_form">
                    <div class="form-group row maker_data_filter_row">
                      <div class="maker_data_filter_input mr-2">
                        <label class="label-float" for="category">Order From Date</label>
                      <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date." />

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="maker_data_filter_input">
                      <label class="label-float" for="category">Order To Date</label>

                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date."/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                     <div class="col-sm-2 maker_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                     </div>
                </div>
          </form>
         </div> 
              <div class="clearfix"></div>
               <div>
                <div class="col-sm-12">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Order No.</th> 
                           <th> Order Date</th>                          
                           <th> Customer</th>
                           <th> Products</th>
                           <th> Total Amount </th>
                           
                           <th> Payment Status</th>
                           <th> Shipping Status</th>
                           <!-- <th> Payment Term</th> -->
                            <th> Payment Type</th> <!-- Changed label From Payment Term to Payment Type By Harshada on date 29 Aug 2020 -->
                            <th> Admin commission status</th>
                           <th> Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                    
                          <tr style="vertical-align: middle;">
                              <th colspan="3" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                              <th id="total_amt"></th>                          
                              <th colspan="5"></th>                             
                            </tr>
                          
                  </table>
               </div>
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>


<div class="modal fade vendor-Modal" id="VendorPaymentModal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Admin Payment</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="adminPaymentForm"> 
          {{csrf_field()}}

          <!-- Hidden Fields -->
          <input type="hidden" name="order_id"   id="orderId" >
          <input type="hidden" name="amount"     id="amount">
          <input type="hidden" name="maker_id"   id="maker_id">
          <input type="hidden" name="order_from" value="customer"> 


          <div class="mdl-admin-commi">
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission(%) :</div>
              <div class="admin-commission-lnk-right"><span id="admin_commission"></span>%</div>
              </div>
            </div>
             <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission($) :</div>
              <div class="admin-commission-lnk-right">$<span class="pay_amount"></span>
              </div>
              </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Total Order Amount :</div>
              <div class="admin-commission-lnk-right">$<span id="order_amount"></span>
              </div>
              <label class="shippingLabel">Excluded shipping costs</label>
              </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Amount Payable to Admin :</div>
              <div class="admin-commission-lnk-right">$<span class="pay_amount"></span>
              </div>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" onclick="payAdmin()" >Pay</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- Stripe Connection Modal -->
<div class="modal fade " id="sendStripeLinkModel" tabindex="-1" role="dialog" aria-labelledby="RepPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header header-fix">
        <h3 class="modal-title" id="">Stripe Connection Request</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <div class="clearfix"></div>
      </div>
      <div class="modal-body">
      {{--  This user is not connected to {{$site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Admin'}} stripe account , send account creation link . --}}

      Currently this user is not associated with us on stripe, do you want to send email for stripe account association.

      </div>
      <div class="modal-footer">

        <input type="hidden" name="user_id" id="user_id" value="">
        {{-- <input type="hidden" name="client_id" id="client_id" value=""> --}}
        <input type="hidden" name="vendor_id" id="vendor_id" value="">

        <button type="button" class="btn btn-primary" onclick="sendStripeAccountLink()" >Send Email</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- /#page-wrapper -->
<script type="text/javascript"> 
var module_url_path  = "{{ $module_url_path or '' }}";  </script>

<script type="text/javascript">

  var table_module = false;
  var customer_id      = "{{$customer_id or 0}}";

  $(document).ready(function()
  {

      // To date validation
      var dates = $("#order_from_date, #order_to_date").datepicker({
          dateFormat: 'mm-dd-yy',
          numberOfMonths: 1,
          onSelect: function(date) {
              for(var i = 0; i < dates.length; ++i) {
                  if(dates[i].id < this.id)
                      $(dates[i]).datepicker('option', 'maxDate', date);
                  else if(dates[i].id > this.id)
                      $(dates[i]).datepicker('option', 'minDate', date);
              }
          } 
      });

      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      bFilter: false,
      "order":[4,'Asc'],

      ajax: {
      'url': module_url_path+'/get_enquiries',
      'data': function(d)
       {        
          d['column_filter[q_enquiry_id]']        = $("input[name='q_enquiry_id']").val()         
          d['column_filter[q_customer_name]']     = $("input[name='q_customer_name']").val()
          d['column_filter[q_total_retail_cost]'] = $("input[name='q_total_retail_cost']").val()
          d['column_filter[q_ship_status]']       = $("select[name='q_ship_status']").val()
          d['column_filter[q_payment_status]']    = $("select[name='q_payment_status']").val()
          d['column_filter[q_enquiry_date]']      = $("input[name='q_enquiry_date']").val()
          d['column_filter[customer_id]']         = customer_id;
          d['column_filter[q_order_from_date]']   = $("#order_from_date").val();
          d['column_filter[q_order_to_date]']     = $("#order_to_date").val();
          d['column_filter[q_payment_term]']      = $("select[name='q_payment_term']").val()
          d['column_filter[admin_commission_status]'] = $("select[name='admin_commission_status']").val()

       }
      },

      drawCallback:function(settings)
      {       
       $("#total_amt").html("$ "+settings.json.total_amt.toFixed(2));
      },
      columns: [
      {
        render(data, type, row, meta)
        {
          if (row.is_split_order == '1') {
            return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a>`+` <br><span class="label label-success">Split</span>`;
          }  
            return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a>`;
        },
        "orderable": false, "searchable":false
      }, 
      {data: 'created_at', "orderable": false, "searchable":false},                            
      // {data: 'store_name', "orderable": false, "searchable":false},         
      {
        render(data, type, row, meta)
        {          
          return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.user_name+`</a>`;
        },
        "orderable": false, "searchable":false
      }, 
      {data: 'product_html', "orderable": false, "searchable":false},

      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i> '+(+row.total_retail_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },             
      
      // {data: 'payment_status', "orderable": false, "searchable":false},
      {
         data : 'payment_status',  
         render : function(data, type, row, meta) 
         { 
           
           if(row.payment_status == 'Pending')
           {
            
             return `<span class="label label-warning">`+row.payment_status+`</span>`

           }
           else if(row.payment_status == 'Paid')
           {
             return `<span class="label label-success">`+row.payment_status+`</span>`

           }
           else if(row.payment_status == 'Failed')
           {
             return `<span class="label label-danger">`+row.payment_status+`</span>`
           }
           else
           {
              return `<span class="label label-warning">Pending</span>`
           }
         },
         "orderable": false,
         "searchable":false
       },


     {
         data : 'ship_status',  
         render : function(data, type, row, meta) 
         { 
           
           if(row.ship_status == 'Pending')
           {
            
             return `<span class="label label-warning">`+row.ship_status+`</span>`

           }
           else if(row.ship_status == 'Shipped')
           {
             return `<span class="label label-success">`+row.ship_status+`</span>`

           }
           else(row.ship_status == 'Failed')
           {
             return `<span class="label label-danger">`+row.ship_status+`</span>`
           }
         },
         "orderable": false,
         "searchable":false
       },
      {data: 'payment_term', "orderable": false, "searchable":false}, 
      {
          data : 'admin_commission_status',  
          render : function(data, type, row, meta) 
          { 
           
              if (row.is_direct_payment == '0') 
              {
                 return `--`;
              }
              else
              {

                  if(row.admin_commission_status == '0')
                  {
                     return `<span class="label label-warning">--</span>`
                  }
                  else if(row.admin_commission_status == '1')
                  {
                    return `<span class="label label-success">Paid</span>`
                  }
                  else 
                  {
                    return `<span class="label label-warning">Pending</span>`
                  }
                        
                
              }
    
         },
         "orderable": false,
         "searchable":false
       },

      {data: 'build_action_btn', "orderable": false, "searchable":false},     
      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });

  // $('#table_module').on('draw.dt',function(event)
  // {
  //   var oTable = $('#table_module').dataTable();
  //   var recordLength = oTable.fnGetData().length;
  //   $('#record_count').html(recordLength);  
  // });

  /*search box*/
  $("#table_module").find("thead").append(`<tr>          
          <td><input type="text" name="q_enquiry_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>     
          <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>
                  
          <td><input type="text" name="q_customer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>   
          <td></td>      
        
          <td><input type="text" name="q_total_retail_cost" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>           
          

           <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_payment_status" id="q_payment_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Pending</option>
                  <option value="2">Paid</option>
                  <option value="3">Failed</option>
            </select>
          </td> 


           <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_ship_status" id="q_ship_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="0">Pending</option>
                  <option value="1">Shipped</option>
                  <option value="2">Failed</option>
            </select>
          </td>  

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_payment_term" id="q_payment_term" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Direct</option>
                  <option value="0">In-Direct</option>
            </select>
          </td> 

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="admin_commission_status" id="admin_commission_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Paid</option>
                  <option value="0">Pending</option>
            </select>
          </td>
          <td>&nbsp;</td>             

      </tr>`);

  $('input.column_filter').on( 'keyup click', function () 
  {
       filterData();
  });
  });

  function filterData()
  {
    table_module.draw();
  }

  function show_product_list(ref){
    
    let tbl_id = $(ref).attr('data-tbl-id');
    let id = $('#'+tbl_id);
    
    if(id.is(":visible"))
    { 
      id.slideUp();
    }
    else
    {      
      id.slideDown();
    } 
  };


  function fillData(orderPrice,adminCommission,adminCommissionAmount,orderId,vendorId)
  {

    console.log(orderPrice,adminCommissionAmount,orderId,vendorId);
    $('.vendor-Modal').modal('show');
   
    $('#order_amount').html(orderPrice.toFixed(2));     
    $('.pay_amount').html(adminCommissionAmount.toFixed(2));
    $('#admin_commission').html(adminCommission.toFixed(2));         
    $('#orderId').val(orderId);    
    $('#amount').val(adminCommissionAmount.toFixed(2) );     
    $('#maker_id').val(vendorId);     
  }

  function payAdmin()
  {
    var paymentFormData = $('form').serialize();

    $.ajax({
            url: '{{url('/vendor')}}'+'/customer_payment/admin',
            type:"POST",
            data: paymentFormData,  
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
                console.log(data);
                    swal({title: "Success", 
                          text: data.message, 
                          type: data.status},
                          function(){ 
                             location.reload();
                          }
                      );               
               }
               else if('error' == data.status)
               {
                swal('Error',data.message,'error');
               }
               else if('warning' == data.status)
               {

                  // $('#client_id').val(data.client_id);
                  $('#user_id').val(data.user_id);
                  $('#vendor_id').val(data.vendor_id);
                   
                  $('.modal').modal('hide');
                  
                  $('#sendStripeLinkModel').modal('show');

               }
               else if('pay-warning' == data.status)
               {
                  swal({
                           title:"Warning", 
                           text: data.message, 
                           type: "warning",
                           allowEscapeKey : false,
                           allowOutsideClick: false
                         },
                         function(){ 
                             location.reload();
                         }
                      ); 
               }
               else
               {
                 swal("Error",data.message,data.status);
               }  
            }
          }); 
  }

  function sendStripeAccountLink()
  {
    let user_id   = $('#user_id').val();
    // let client_id  = $('#client_id').val();
    let vendor_id = $('#vendor_id').val();
    let token     = "{{csrf_token()}}";

     $.ajax({
            url: '{{url('/vendor')}}'+'/customer_payment/send_stripe_acc_creation_link',
            type:"POST",
            // data: {"_token":token,"user_id":user_id,"client_id":client_id,'vendor_id':vendor_id},
            data: {"_token":token,"user_id":user_id,'vendor_id':vendor_id},
            beforeSend : function()
            {
              showProcessingOverlay();
             
            },
            success:function(data)
            { 
               hideProcessingOverlay();
               if('success' == data.status)
               {
                    swal({title:"Success", 
                          text: data.message, 
                          type: data.status},
                         
                          function(){ 
                            location.reload();
                         }
                      );               
               }
               else
               {
                 swal("Error",data.message,data.status);
               }  
            }
          }); 
  }

  const saveData = (() => {
    const a = document.createElement('a');
    a.style = 'display: none';
    document.body.appendChild(a);

    return (data, fileName, type = 'application/csv') => {
      const blob = new Blob([data], { type });

      if (navigator.msSaveBlob) {
        return navigator.msSaveBlob(blob, fileName);
      }

      const url = URL.createObjectURL(blob);
      a.href = url;
      a.download = fileName;
      a.click();
      URL.revokeObjectURL(url);
      return true;
    };
  })();

  function customerOrderExport(){

    var q_enquiry_id            = $("input[name='q_enquiry_id']").val();
    var q_customer_name         = $("input[name='q_customer_name']").val()
    var q_total_retail_cost  = $("input[name='q_total_retail_cost']").val();
    var q_ship_status           =  $("select[name='q_ship_status']").val()
    var q_payment_status        = $("select[name='q_payment_status']").val()
    var q_enquiry_date          = $("input[name='q_enquiry_date']").val()
    var customer_id             = customer_id;
    var q_order_from_date       = $("#order_from_date").val(); 
    var q_order_to_date         = $("#order_to_date").val(); 
    var q_payment_term          =  $("select[name='q_payment_term']").val()
    var admin_commission_status =  $("select[name='admin_commission_status']").val()

    $.ajax({
          url: module_url_path+'/get_export_customer_orders',
          data: {q_enquiry_id:q_enquiry_id,q_customer_name:q_customer_name,q_total_retail_cost:q_total_retail_cost,q_ship_status:q_ship_status,q_payment_status:q_payment_status,q_enquiry_date:q_enquiry_date,customer_id:customer_id,q_order_from_date:q_order_from_date,q_order_to_date:q_order_to_date,q_payment_term:q_payment_term,admin_commission_status:admin_commission_status},
        
          type:"get",
          beforeSend: function() 
          {
             showProcessingOverlay();                
          },
          success:function(data)
          {
            hideProcessingOverlay();
            if(data.status != null && data.status == 'error')
            {
              swal('Error',data.message,'error');
            }
            else
            {
              saveData(data, 'customer_orders.csv');
            }
          }
        });
 }
</script>
@stop