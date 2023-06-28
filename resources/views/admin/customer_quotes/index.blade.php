@extends('admin.layout.master')  
@section('main_content')

<style type="text/css">
.table tbody tr td .table{box-shadow: -2px 6px 14px -5px #b9b9b9;margin-top: 10px;}
.pro-list-bg {position: relative;}
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
  .shippingLabel{
    background-color: #dde0e4;
    padding: 10px;
    border-radius: 25px;
  }
  .butns-right .btn-circle{
        width: 36px;
    height: 36px;    padding: 8px 0;
  }
.downloadbtns-btn{padding: 9px 30px !important;     margin-left: 6px;display: inline-block;background-color:none; border: 1px solid #666;color: #666;}
.downloadbtns-btn:hover{background-color: #333; border: 1px solid #666;color: #fff;}
th {white-space: nowrap;}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
           <div class="white-box">
            @include('admin.layout._operation_status')
           
              <div class="butns-right">
                <div class="col-sm-12 export-btn-div text-right p-0 mb-3">
                  <button type="button" onclick="customerOrderExport()" title="Export customer orders as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right" value="export" id="customerOrderExport">Export</button>
                </div>
{{--                  <form class="form-horizontal" id="report_form">
              
                    <div class="form-group row">
                     
                    <div class="col-6">
                    <div class="floatmain-div">
                     <label class="label-float" for="category">Order Date</label>
                      <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="From Date"
                      data-parsley-required="true" data-parsley-required-message="Please enter order from date."  data-date-format='yyyy-mm-dd'/>

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                    </div>
                    <div class="col-3">

                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_from_date" placeholder="To Date" data-parsley-required="true" data-parsley-required-message="Please enter order to date."  data-date-format='yyyy-mm-dd'/>


                      <span id="to_date_error" class="red"></span>
                    </div>
            
                    <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip" title="Search"><i class="fa fa-search" onclick="validateField();filterData();"></i> </a>

                    <a href="{{$module_url_path or ''}}" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>

                    <a href="javascript:void(0)" class="btn btn-succes downloadbtns-btn" title="Export as a xlsx" id="generate_report">Download </a>
              </div>
              
          </form> --}}
            <form class="form-horizontal" id="report_form">
                    <div class="form-group row admin_data_filter_row">
                      <div class="admin_data_filter_input mr-2">
                        <label class="label-float" for="category">Order From Date</label>
                      <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date." />

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="admin_data_filter_input">
                      <label class="label-float" for="category">Order To Date</label>

                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date."/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                     <div class="admin_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip ml-3" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>

                        <!--  <a href="javascript:void(0)" class="btn btn-succes downloadbtns-btn" title="Export as a xlsx" id="generate_report">Download </a> -->
                     </div>
                </div>
            </form>
         </div> 
        <div>

              </div> 
              <div class="table-responsive">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Order No.</th> 
                           <th> Order Date</th>                          
                           <th> Customer</th>
                           <th> Vendor</th>
                           <th> Products</th>
                           <th> Total Amount </th>
                           <th> Shipping Status</th>
                           <th> Customer Payment Status</th>
                           <!-- <th> Payment Term</th> -->
                            <th> Payment Type</th> <!-- Changed label From Payment Term to Payment Type By Harshada on date 29 Aug 2020 -->
                           <th> Vendor Payment Status</th>
                           <th> Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                      <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="4" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                              <!-- <th id="total_quantity"></th>   -->                        
                              <th id="total_amt"></th>                          
                              <th colspan="5"></th>
                             
                            </tr>
                      </tfoot>
                  </table>
                  </div>
            </div>
             </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>


<!-- Modal -->
<div class="modal fade vendor-Modal" id="VendorPaymentModal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Vendor Payment</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="vendorPaymentForm"> 
          {{csrf_field()}}

          <!-- Hidden Fields -->
          <input type="hidden" name="maker_id"   id="maker_id">
          <input type="hidden" name="order_id"   id="orderId" >
          <input type="hidden" name="amount"     id="amount">

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
              <div class="admin-commission-lnk-right">$<span id="actual_amount"></span>
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
                <div class="admin-commission-lnk">Amount Payable to Vendor :</div>
              <div class="admin-commission-lnk-right">$<span id="pay_amount" class="pay_amount"></span>
              </div>
              </div>
            </div>
          </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="payVendor()" >Pay</button>
      </div>
    </div>
  </div>
</div>

<input type="hidden" name="csrfToken" id="csrfToken" value="{{csrf_token()}}">
    
<!-- Stripe Connection Modal -->
<div class="modal fade " id="sendStripeLinkModel" tabindex="-1" role="dialog" aria-labelledby="RepPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="">Stripe Connection Request</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      {{--  This user is not connected to {{$site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Admin'}} stripe account , send account creation link . --}}

      Currently this user is not associated with us on stripe, do you want to send email for stripe account association.

      </div>
      <div class="modal-footer">

        <input type="hidden" name="user_id" id="user_id" value="">

        
        <button type="button" class="btn btn-primary" onclick="sendStripeAccountLink()" >Send Email</button>

        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        
      </div>
    </div>
  </div>
</div>

<!-- /#page-wrapper -->
<script type="text/javascript"> 
  var module_url_path  = "{{ $module_url_path or '' }}"; 
  var module_url  = "{{ $module_url or '' }}"; 
 </script>

<script type="text/javascript">




  var table_module = false;
 

  $(document).ready(function()
  {
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      bFilter: false,
      // "order":[4,'Asc'],

      ajax: {
      'url': module_url_path+'/get_enquiries',
      'data': function(d)
       {        
          d['column_filter[q_order_no]']      = $("input[name='q_order_no']").val()
           d['column_filter[q_enquiry_date]'] = $("input[name='q_enquiry_date']").val()
          d['column_filter[q_description]']   = $("input[name='q_description']").val()
          d['column_filter[q_customer_name]'] = $("input[name='q_customer_name']").val()
          d['column_filter[q_company_name]']  = $("input[name='q_company_name']").val()
          d['column_filter[q_total_retail_cost]'] = $("input[name='q_total_retail_cost']").val()
          // d['column_filter[q_enquiry_date]']   = $("input[name='q_enquiry_date']").val()
          d['column_filter[q_ship_status]']    = $("select[name='q_ship_status']").val()
          d['column_filter[q_payment_status]'] = $("select[name='q_payment_status']").val()
          d['column_filter[q_payment_term]'] = $("select[name='q_payment_term']").val()   
          d['column_filter[q_vendor_payment]'] = $("select[name='q_vendor_payment']").val()
          d['column_filter[q_from_date]']      = $("input[name='order_from_date']").val()
          d['column_filter[q_to_date]']        = $("input[name='order_to_date']").val()    
         
       }
      },

      drawCallback:function(settings)
      {
       $("#total_amt").html("<span title='Total Amount' style='cursor:pointer;'>$ "+settings.json.total_amt.toFixed(2)+"</span>");
       //$("#total_quantity").html("<span title='Total Quantity' style='cursor:pointer;'>"+settings.json.total_quantity+"</span>");
      },
      columns: [
      {
        render(data, type, row, meta)
        {
            if(row.is_split_order == '1')
            {
              return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a> <br><span class="label label-success">Split</span>`
            }
            return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a>`

        },
        "orderable": false, "searchable":false
      },

      {data: 'created_at', "orderable": false, "searchable":false},      
      
      // {data: 'store_name', "orderable": false, "searchable":false}, 
      {
        render(data, type, row, meta)
        {            
            //return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.customer_name+`</a>`

            return `<a href="`+module_url+`/admin/customer/view/`+btoa(row.customer_id)+`" class="link_v">`+row.customer_name+`</a>`

        },
        "orderable": false, "searchable":false
      },


      // {data:'company_name',"orderable": false, "searchable":false},
      {
        render(data, type, row, meta)
        {            
            //return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.company_name+`</a>`

            return `<a href="`+module_url+`/admin/vendor/view/`+btoa(row.maker_id)+`" class="link_v">`+row.company_name+`</a>`

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
      
       {
         data : 'ship_status',  
         render : function(data, type, row, meta) 
         {
           
           if(row.ship_status == '0')
           {
            
             return `<span class="label label-warning">Pending</span>`

           }
           else if(row.ship_status == '1')
           {
             return `<span class="label label-success">Shipped</span>`

           }
           else(row.ship_status == '2')
           {
             return `<span class="label label-danger">Failed</span>`
           }
         },
         "orderable": false,
         "searchable":false
       },

      {
         data : 'payment_status',  
         render : function(data, type, row, meta) 
         { 
           
           if(row.payment_status == 'Pending')
           {
            
             return `<span class="label label-warning">Pending</span>`

           }
           else if(row.payment_status == 'Paid')
           {
             return `<span class="label label-success">Paid</span>`

           }
           else if(row.payment_status == 'Failed')
           {
             return `<span class="label label-danger">Failed</span>`
           }
           else
           {
             return `<span class="label label-warning">Pending</span>`
           }

         },
         "orderable": false,
         "searchable":false
       },
  
      
        {data: 'payment_term', "orderable": false, "searchable":false}, 

        //{data: 'vendor_payment_status', "orderable": false, "searchable":false}, 

        {
           data : 'vendor_payment_status',  
           
           render : function(data, type, row, meta) 
           { 
              if(row.vendor_payment_status == 'Pending')
              {
                 return `<span class="label label-warning">Pending</span>`
              }
             
              if(row.vendor_payment_status == 'Paid')
              {
                return `<span class="label label-success">Paid</span>`
              }
           

           },
           "orderable": false,
           "searchable":false
        },
  
      
        // {data: 'created_at', "orderable": false, "searchable":false},        
        {data: 'build_action_btn', "orderable": false, "searchable":false},        
      
      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });
  
 /* $('#table_module').on('draw.dt',function(event)
  {
    var oTable = $('#table_module').dataTable();
    var recordLength = oTable.fnGetData().length;
    $('#record_count').html(recordLength);  
  });*/

  /*search box*/
  $("#table_module").find("thead").append(`<tr>          
          <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

            <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>        

          <td><input type="text" name="q_customer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>   
          
          <td>
            <input type="text" name="q_company_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" />
          </td>
          <td></td>

          <td><input type="text" name="q_total_retail_cost" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_ship_status" id="q_ship_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="0">Pending</option>
                  <option value="1">Shipped</option>
                  <option value="2">Failed</option>
            </select>
          </td> 
          
            <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_payment_status" id="q_payment_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Pending</option>
                  <option value="2">Paid</option>
                  <option value="3">Failed</option>
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
            <select class="search-block-new-table column_filter form-control-small" name="q_vendor_payment" id="q_vendor_payment" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Pending</option>
                  <option value="2">Paid</option>
                  <option value="3">Failed</option>
                
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

</script>

<script type="text/javascript">
  
  $(document).ready(function()
  {
    $( function() {

        $( ".datepicker" ).datepicker({
          // format: 'yyyy-mm-dd',
          format: 'mm-dd-yyyy',
          viewMode: "days", 
          minViewMode: "days"
       });

        $('#order_from_date').datepicker('setEndDate', '+0d');
        $('#order_to_date').datepicker('setEndDate', '+0d');

        $("#order_from_date").datepicker({
          
          todayBtn:  1,
          autoclose: true,

        }).on('changeDate', function (selected) {
          var minDate = new Date(selected.date.valueOf());
          $('#order_to_date').datepicker('setStartDate', minDate);
        });
      
        $("#order_to_date").datepicker()
          .on('changeDate', function (selected) {
              var minDate = new Date(selected.date.valueOf());
              $('#order_from_date').datepicker('setEndDate', minDate);
          });
    
        });
     $('#generate_report').click(function(){

         if($('#report_form').parsley().validate()==false) return;
           
           var strtDt  = $("#order_from_date").val();
           var endDt   = $("#order_to_date").val();

         if (endDt < strtDt){
          flag = 1; // true
          swal("Warning","To date must be greater than from date.","warning");
          return;
          }
          else
          {
 
            var url =  module_url_path+'/download_report/'+strtDt+'/'+endDt;
            
            window.location = url;
          }


                    
           var flag = 0; // false
           
           if (endDt < strtDt){
            flag = 1; // true
            swal("Warning","To date must be greater than from date.","warning");
            return;
            }
            else
            {
              var url =  module_url_path+'/download_report/'+strtDt+'/'+endDt;
              
              window.location = url;
            }

        
       
          var formdata = new FormData($("#report_form")[0]);

          $.ajax({
                url: module_url_path+'/generate_report',
                type:"POST",
                data: formdata,
                contentType:false,
                processData:false,
                dataType:'json',
                success:function(data)
                { 
                  var url =  module_url_path+'/download_report/'+strtDt+'/'+endDt;
                 
                  /*window.location = module_url_path+'/download_report/'+strtDt+'/'+endDt;*/
                   if('success' == data.status)
                   {
                      window.location = module_url_path+'/generate_report';
                      
                      swal( {
                             title: "Success",
                             text: data.description,
                             type: data.status,
                             confirmButtonText: "OK",
                             closeOnConfirm: false
                            },
                           function(isConfirm,tmp)
                           {
                             if(isConfirm==true)
                             {
                                window.location = data.url;
                             }
                           });
                      
                    }
                    else
                    {
                      swal(data.status,data.description,data.status);
                    }  
                }
            
            });   
      });

  });

  function validateField()
  {
      var from_date = $('#order_from_date').val();
      var to_date   = $('#order_to_date').val();

      if(from_date == '')
      {
         $('#from_date_error').html('Please enter order from date.');
         return false;
      }

      if(to_date == '')
      {
         $('#to_date_error').html('Please enter order to date.');
         return false;
      }
     
      if(from_date == '' && to_date == '')
      {
         $('#from_date_error').html('Please enter order from date.');
         $('#to_date_error').html('Please enter order to date.');
         return false;
      }

      else
      {
        return true;
      }
  }

  function fillData(orderPrice,vendorAmount,adminCommission,adminCommissionAmount,makerId,orderId)
  {

    $('.vendor-Modal').modal('show');
    $('#order_amount').html(orderPrice.toFixed(2));
    $('#admin_commission').html(adminCommission.toFixed(2));         
    $('#actual_amount').html(adminCommissionAmount.toFixed(2));    
    $('.pay_amount').html(vendorAmount.toFixed(2));    
    $('#maker_id').val(makerId);    
    $('#amount').val(vendorAmount.toFixed(2));    
    $('#orderId').val(orderId);    
  }

  function payVendor()
  {
     var paymentFormData = new FormData($("#vendorPaymentForm")[0]);
    
    $.ajax({
            url: '{{url('/admin')}}'+'/customer_payment/vendor',
            type:"POST",
            data: paymentFormData,
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
                    swal({title: "Success", 
                          text: data.message, 
                          type: data.status},
                         
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
    let user_id = $('#user_id').val();
    let token = "{{csrf_token()}}";

     $.ajax({
            url: '{{url('/admin/leads')}}'+'/send_stripe_acc_creation_link',
            type:"POST",
            data: {"_token":token,"user_id":user_id},
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


   function generate_invoice(order_no = false)
  {
    var csrf_token = "{{csrf_token()}}";
    var order_no = order_no;
    var orderType = 'customer';
     var generate_invoice_url  = "{{ url(config('app.project.admin_panel_slug')."/admin_commission_reports")."/admin_commission_invoice_generator" }}";
     generate_invoice_url =  generate_invoice_url+'/'+btoa(order_no)+'/'+orderType;

     $.ajaxSetup({
        headers : { "X-CSRF-TOKEN" :csrf_token}
      });
    
    $.ajax({
               url: generate_invoice_url,
               type:"POST",
               contentType:false,
               processData:false,
               data:{order_no:order_no,orderType:orderType},
               dataType:'json',
               beforeSend: function() 
               {
                 showProcessingOverlay();                
               },
               success:function(response)
               {
                   hideProcessingOverlay();
                  
                  if(response.status == 'success')
                  { 
                    
       
                    swal({
                            title: 'Success',
                            text: response.description,
                            type: 'success',
                            confirmButtonText: "OK",
                            closeOnConfirm: true
                         },
                        function(isConfirm,tmp)
                        {                       
                          if(isConfirm==true)
                          {
                            window.location.reload();
                          }
                        });
                   }
                   else
                   {                
                      swal('Error',response.description,'error');
                   }  
               }           
             });     
    return;
  }


  var saveData = (() => {
    var a = document.createElement('a');
    a.style = 'display: none';
    document.body.appendChild(a);

    return (data, fileName, type = 'application/csv') => {
      var blob = new Blob([data], { type });

      if (navigator.msSaveBlob) {
        return navigator.msSaveBlob(blob, fileName);
      }

      var url = URL.createObjectURL(blob);
      a.href = url;
      a.download = fileName;
      a.click();
      URL.revokeObjectURL(url);
      return true;
    };
  })();

  
  function customerOrderExport(){

    var q_order_no                = $("input[name='q_order_no']").val();
    var q_enquiry_date            = $("input[name='q_enquiry_date']").val();
    var q_customer_name           = $("input[name='q_customer_name']").val();
    var q_company_name            = $("input[name='q_company_name']").val();
    var q_total_retail_cost       = $("input[name='q_total_retail_cost']").val();
    var q_ship_status             = $("select[name='q_ship_status']").val()
    var q_payment_term            = $("select[name='q_payment_term']").val();
    var q_vendor_payment          = $("select[name='q_vendor_payment']").val(); 
    var q_payment_status          = $("select[name='q_payment_status']").val()
    var q_from_date               = $("input[name='order_from_date']").val();
    var q_to_date                 = $("input[name='order_to_date']").val();
    

    $.ajax({
          url: module_url_path+'/get_export_customer_orders',
          data: {q_order_no:q_order_no,q_customer_name:q_customer_name,q_company_name:q_company_name,q_total_retail_cost:q_total_retail_cost,q_ship_status:q_ship_status,q_enquiry_date:q_enquiry_date,q_payment_term:q_payment_term,q_vendor_payment:q_vendor_payment,q_from_date:q_from_date,q_to_date:q_to_date,q_payment_status:q_payment_status},
        
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