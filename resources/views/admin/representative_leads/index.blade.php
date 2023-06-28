@extends('admin.layout.master')                

@section('main_content')


<style>
  .dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
  }

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}
.shippingLabel{
  background-color: #dde0e4;
    padding: 10px;
    border-radius: 25px;
}
.table > tbody > tr > td:first-child a{
  text-underline-position: under; text-decoration: underline; margin-bottom: 5px; display: inline-block;
}
.table > tbody > tr > td:first-child a:hover{
   text-decoration: none;
}
</style>
<!-- BEGIN Page Title -->

<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">
    
<!-- Page Content -->
<div id="page-wrapper">
  <div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title or ''}}</h4> </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
            <ol class="breadcrumb">
                <li><a href="{{url('/admin/dashboard')}}">Dashboard</a></li>
                <li class="active">{{$page_title or ''}}</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
        
    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">

         <div>

            <div class="butns-right">   
            <div class="col-sm-12 export-btn-div text-right p-0 mb-3">
               <button type="button" onclick="repSaleOrderExport()" title="Export Reps/Sales orders as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right" value="export" id="repSaleOrderExport">Export</button>
            </div>         
{{--       <form class="form-horizontal" id="report_form">
              
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

                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="To Date" data-parsley-required="true" data-parsley-required-message="Please enter order to date."  data-date-format='yyyy-mm-dd'/>


                      <span id="to_date_error" class="red"></span>
                    </div>
                    <div class="right">
                    <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                    <a href="{{$module_url_path or ''}}" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                   </div> 

              </div>
              
          </form> --}}
          <form class="form-horizontal" id="report_form">
                    <div class="form-group row admin_data_filter_row">
                      <div class="admin_data_filter_input mr-2">
                        <label class="label-float" for="category">Order From Date</label>
                      <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date" readonly/>

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="admin_data_filter_input mr-2">
                      <label class="label-float" for="category">Order To Date</label>

                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date" readonly />

                      <span id="to_date_error" class="red"></span>
                     </div>
                     <div class="admin_data_filter_input">
                      <label class="label-float" for="category">Search By Reps/Sales</label>

                        <input type="text" class="form-control" name="order_rep_sales_name" id="order_rep_sales_name" placeholder="Enter Reps/Sales Name"/>

                     </div>
                     <div class="col-sm-2 admin_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                           <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                     </div>
                </div>
            </form>
         </div> 

          </div>
          <div>

            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                              <th>Order No.</th>
                              <th>Order Date</th>
                              <th>Reps /Sales </th>   
                              <th>Vendor</th>   
                              <th>Products</th>          
                              <th>Total Amount</th>
                              <th>Shipping Status</th>
                              <!-- <th>Vendor Payment Status</th> -->
                              <th>Reps /Sales Payment Status</th>
                              <!-- <th>Payment Term</th> -->
                               <th> Payment Type</th> <!-- Changed label From Payment Term to Payment Type By Harshada on date 29 Aug 2020 -->
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                      <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="4" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                              <!-- <th id="total_quantity"></th>                     -->      
                              <th id="total_amt"></th>                          
                              <th colspan="4"></th>
                             
                            </tr>
                      </tfoot>
                  </table>
              </div>

            </form>
          </div>
        </div>
      </div>         
    </div>
  </div>
</div>


<!--Vendor Payment Modal -->
<div class="modal fade vendor-Modal" id="VendorPaymentModal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
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
          <input type="hidden" name="maker_id"     id="maker_id">
          <input type="hidden" name="order_id"     id="orderId" >
          <input type="hidden" name="amount"       id="amount">
          <input type="hidden" name="order_number" id="order_number">


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
                <div class="admin-commission-lnk">Total Order Amount($) :</div>
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
        {{-- <button type="button" class="btn btn-primary" onclick="payVendor()" >Pay</button> --}}
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
     
      </div>
    </div>
  </div>
</div>

<!-- Representative Payment Modal -->
<div class="modal fade rep-Modal" id="repPaymentModal" tabindex="-1" role="dialog" aria-labelledby="RepPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Pay Commission</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="repPaymentForm"> 
          {{csrf_field()}}

          <!-- Hidden Fields -->
          <input type="hidden" name="rep_id"     id="rep_id">
          <input type="hidden" name="sales_id"   id="sales_id">
          <input type="hidden" name="order_id"   id="rep_orderId" >
          <input type="hidden" name="amount"     id="rep_amount">

          <div class="mdl-admin-commi">
            <div class="adms-cmsns">
               <div class="innerbox">
                <div class="admin-commission-lnk"><span id="commission_lable"></span></div>
              <div class="admin-commission-lnk-right"><span id="rep_commission"></span>%
              </div>
               </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Total Order Amount($) :</div>
              <div class="admin-commission-lnk-right">$<span id="rep_order_amount"></span>
              </div>
              <label class="shippingLabel">Excluded shipping costs</label>
              </div>
            </div>
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission($) :</div>
              <div class="admin-commission-lnk-right">$<span id="admin_commission_amount"></span>
              </div>
              </div>
            </div>            
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk"><span id="commission_amount_lable"></span></div>
              <div class="admin-commission-lnk-right">$<span id="rep_actual_amount"></span>
              </div>
              </div>
            </div>

          </div>

        </form>
      </div>
      <div class="modal-footer">
        {{-- <button type="button" class="btn btn-primary" onclick="payRepCommission()" >Pay</button> --}}
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
     
      </div>
    </div>
  </div>
</div>

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
<!-- END Main Content -->
{{-- <script type="text/javascript"> var module_url_path  = "{{ $module_url_path or '' }}";  </script> --}}
<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";

  var module_url  = "{{ $module_url or '' }}";
  
  var table_module = false;

  $(document).ready(function()
  {
    
         // To date validation
        var dates = $("#order_from_date, #order_to_date").datepicker({
            // dateFormat: 'mm-dd-yy',
            // numberOfMonths: 1,
            maxDate:'+0d',
            onSelect: function(date) {
                for(var i = 0; i < dates.length; ++i) {
                    if(dates[i].id < this.id)
                        $(dates[i]).datepicker('option', 'maxDate', date);
                    else if(dates[i].id > this.id)
                        $(dates[i]).datepicker('option', 'minDate', date);
                }
            } 
        });
        $('#search').click(function()
        {
          var order_rep_sales_name  = $("#order_rep_sales_name").val();
          
          if(order_rep_sales_name!=""){
            
            $("#order_from_date").removeAttr('data-parsley-required').parsley().destroy();
            $("#order_to_date").removeAttr('data-parsley-required').parsley().destroy();
          }
          else
          {
            $("#order_from_date").attr('data-parsley-required','true');
            $("#order_to_date").attr('data-parsley-required','true');
          }

         if($('#report_form').parsley().validate()==false) return;
         else
          filterData();
           
        });

      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      aaSorting: [[ 0, "desc" ]],
      order:[],
      columnDefs:[{
                "targets": 7,
                "orderable": false
            }],
      bFilter: false ,
  
      ajax: {
      'url': '{{ $module_url_path.'/get_leads'}}',
      
      'data': function(d)
        {
         
          d['column_filter[q_lead_id]']       = $("input[name='q_lead_id']").val()
          d['column_filter[q_username]']      = $("input[name='q_username']").val()
          d['column_filter[q_company_name]']      = $("input[name='q_company_name']").val()
          d['column_filter[q_tot_retail]']    = $("input[name='q_tot_retail']").val()   
          d['column_filter[q_tot_commi_less_wholesale]'] = $("input[name='q_tot_commi_less_wholesale']").val()     
          d['column_filter[q_enquiry_date]']    = $("input[name='q_enquiry_date']").val()  
          
          d['column_filter[q_shipping_status]'] = $("select[name='q_shipping_status']").val()  
          d['column_filter[q_vendor_payment]']  = $("select[name='q_vendor_payment']").val()  
          d['column_filter[q_rep_payment]']     = $("select[name='q_rep_payment']").val()  
          d['column_filter[q_payment_term]']     = $("select[name='q_payment_term']").val()  

          d['column_filter[q_from_date]']      = $("input[name='order_from_date']").val()
          d['column_filter[q_to_date]']        = $("input[name='order_to_date']").val()
          d['column_filter[q_order_rep_sales_name]'] = $("input[name='order_rep_sales_name']").val()
        }
      },
      drawCallback:function(settings)
      {
       $("#total_amt").html("<span title='Total Amount' style='cursor:pointer;'>$ "+settings.json.total_amt.toFixed(2)+"</span>");
       //$("#total_quantity").html("<span title='Total Quantity' style='cursor:pointer;'>"+settings.json.total_quantity+"</span>");
      },
      columns: [{
        render(data, type, row, meta)
      {
        if (row.is_split_order == '1') {
          return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a> <span class="label label-success">Split</span>`
        }
          return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a>`

      },
      "orderable": false, "searchable":false
      },
      
      {data: 'created_at', "orderable": true, "searchable":false},      
      // {data: 'user_name', "orderable": true, "searchable":false},
       {
        render(data, type, row, meta)
        {        


            //return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.user_name+`</a>`

            if(row.representative_id == 0){
                return `<a href="`+module_url+`/admin/sales_manager/view/`+btoa(row.sales_manager_id)+`" class="link_v">`+row.user_name+`</a>`
            } else if(row.sales_manager_id == 0){
               return `<a href="`+module_url+`/admin/representative/view/`+btoa(row.representative_id)+`" class="link_v">`+row.user_name+`</a>`
            }


        },
        "orderable": false, "searchable":false
      },
      // {data: 'company_name', "orderable": true, "searchable":false},
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
             return '<i class="fa fa-dollar"></i> '+(+row.total_wholesale_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },
      
      {data: 'ship_status', "orderable": true, "searchable":false},      
      /*{data: 'vendor_payment_status', "orderable": true, "searchable":false},      */
      {data: 'rep_payment_status', "orderable": false, "searchable":false},      
      {data: 'payment_term', "orderable": true, "searchable":false},      

      {data: 'build_action_btn', "orderable": false, "searchable":false},      

     
      ]
    });

    $('input.column_filter').on( 'keyup click', function () 
    {
        filterData();
    });

    $('#table_module').on('draw.dt',function(event)
    {
      var oTable = $('#table_module').dataTable();
      var recordLength = oTable.fnGetData().length;
      $('#record_count').html(recordLength);

      var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
           new Switchery($(this)[0], $(this).data());
        });

      $("input.toggleSwitch").change(function(){
          statusChange($(this));
       });   
    });

    /*search box*/
     $("#table_module").find("thead").append(`<tr>
                    <td><input type="text" name="q_lead_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                     <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>  

                    <td><input type="text" name="q_username" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>                    
               
                   
                    <td><input type="text" name="q_company_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td></td>
                     <td>
                        <input type="text" name="q_tot_commi_less_wholesale" placeholder="Search" class="search-block-new-table column_filter form-control-small" />
                    </td>
                   

                    <td>
                        <select class="search-block-new-table column_filter form-control-small" name="q_shipping_status" id="q_shipping_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="0">Pending</option>
                        <option value="1">Shipped</option>
                        <option value="2">Failed</option>
                        </select>
                    </td> 
                     
                    


                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_rep_payment" id="q_rep_payment" onchange="filterData();">
                        <option value="">All</option>
                        <option value="0">Pending</option>
                        <option value="1">Paid</option>
                        <option value="2">Failed</option>
                        </select>
                    </td> 

                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_payment_term" id="q_payment_term" onchange="filterData();">
                        <option value="">All</option>
                        <option value="0">In-Direct</option>
                        <option value="1">Direct</option>
                        </select>
                    </td>


                </tr>`);

     // <td>
     //                   <select class="search-block-new-table column_filter form-control-small" name="q_vendor_payment" id="q_vendor_payment" onchange="filterData();">
     //                    <option value="">All</option>
     //                    <option value="0">Pending</option>
     //                    <option value="1">Paid</option>
     //                    <option value="2">Failed</option>
     //                    </select>
     //                </td>

       $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });


  });

  function filterData()
  {
    table_module.draw();
  }


  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this record.');
  }
  
  

function filterData()
{
  table_module.draw();
}

/* check all */
$('#checked_record_all').change(function () 
{ 
  var is_checked = $(this).is(":checked"); 
  $('.checkItem').each(function(index,elem)
  {
     $(elem).prop('checked', is_checked);     
  });     
});

function show_product_list(ref){    
var tbl_id = $(ref).attr('data-tbl-id');
var id = $('#'+tbl_id);

if(id.is(":visible"))
{ 
  id.slideUp();
}
else
{      
  id.slideDown();
} 
};

function fillData(orderPrice,vendorAmount,adminCommission,adminCommissionAmount,makerId,orderNo,orderId)
{ 
  $('.vendor-Modal').modal('show');
  $('#order_amount').html(orderPrice.toFixed(2));  
  $('#admin_commission').html(adminCommission.toFixed(2));       
  $('#actual_amount').html(adminCommissionAmount.toFixed(2));    
  $('.pay_amount').html(vendorAmount.toFixed(2));    
  $('#maker_id').val(makerId);    
  $('#amount').val(vendorAmount.toFixed(2));    
  $('#orderId').val(orderId);
  $("#order_number").val(orderNo);    
}

function payCommission(orderPrice,adminCommission,repcommission,repCommissionAmount,repId,orderId)
{
  $('.rep-Modal').modal('show');
  $('#rep_commission').html(repcommission);    
  $('#rep_pay_amount').html(repCommissionAmount.toFixed(2));    
  $('#rep_actual_amount').html(repCommissionAmount.toFixed(2));    
  $('#rep_order_amount').html(orderPrice.toFixed(2));    
  $('#admin_commission_amount').html(adminCommission.toFixed(2));    
  $('#rep_id').val(repId); 
  $('#sales_id').val('');  
  
  $('#commission_lable').html("Representative Commission(%) :");
  $('#commission_amount_lable').html("Representative Commission($) :");
  $('#rep_amount').val(repCommissionAmount.toFixed(2));    
  $('#rep_orderId').val(orderId);    
}

function paySalesCommission(orderPrice,adminCommission,repcommission,repCommissionAmount,salesId,orderId)
{
  $('.rep-Modal').modal('show');
  $('#rep_commission').html(repcommission);    
  $('#rep_pay_amount').html(repCommissionAmount.toFixed(2));    
  $('#rep_actual_amount').html(repCommissionAmount.toFixed(2));    
  $('#rep_order_amount').html(orderPrice.toFixed(2));    
  $('#admin_commission_amount').html(adminCommission.toFixed(2));    
  $('#sales_id').val(salesId);  
  $('#rep_id').val('');  
  $('#commission_lable').html("Sales Manager Commission(%) :");
  $('#commission_amount_lable').html("Sales Manager Commission($) :");  
  $('#rep_amount').val(repCommissionAmount.toFixed(2));    
  $('#rep_orderId').val(orderId);    
}

function payVendor()
{
  var paymentFormData = new FormData($("#vendorPaymentForm")[0]);
  commssionTransaction(paymentFormData);
}

function commssionTransaction(data)
{
  $.ajax({
          url: '{{url('/admin/leads')}}'+'/pay_commission',
          type:"POST",
          data: data,
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

function payRepCommission()
{
  var repPaymentData = new FormData($("#repPaymentForm")[0]);
  commssionTransaction(repPaymentData);
}

function sendStripeAccountLink()
{
  var user_id = $('#user_id').val();
  var token = "{{csrf_token()}}";

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
                  swal({title: "Success", 
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

    $(document).ready(function()
  {
    $( function() {
        $( ".datepicker" ).datepicker();
        
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
           var endDt  = $("#order_to_date").val();

         if (endDt <= strtDt){
          flag = 1; // true
          swal("Warning","from date must be grater than to date","warning");
          return;
          }
          else
          {
 
            var url =  module_url_path+'/download_report/'+strtDt+'/'+endDt;
            
            window.location = url;
          }


                    
           var flag = 0; // false
           
           if (endDt <= strtDt){
            flag = 1; // true
            swal("Warning","from date must be grater than to date","warning");
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
               {window.location = module_url_path+'/generate_report';
                   //swal(data.status,data.description,data.status);
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
                  
                   swal('Error',data.description,data.status);
                }  
            }
            
          });   
     });
  });   

function generate_invoice(order_no = false)
{
  var csrf_token = "{{csrf_token()}}";
  var order_no = order_no;
  var orderType = 'rep-sales';

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

function repSaleOrderExport(){

    var q_lead_id               = $("input[name='q_lead_id']").val();
    var q_username              = $("input[name='q_username']").val();
    var q_company_name          = $("input[name='q_company_name']").val();
    var q_tot_retail            =  $("input[name='q_tot_retail']").val();
    var q_tot_commi_less_wholesale  =  $("input[name='q_tot_commi_less_wholesale']").val()
    var q_enquiry_date          =  $("input[name='q_enquiry_date']").val();
    var q_shipping_status           = $("select[name='q_shipping_status']").val();
    var q_payment_term          = $("select[name='q_payment_term']").val();
    var q_vendor_payment        = $("select[name='q_vendor_payment']").val(); 
    var q_rep_payment           = $("select[name='q_rep_payment']").val(); 

    var q_from_date             = $("input[name='order_from_date']").val();
    var q_to_date               = $("input[name='order_to_date']").val();
    var q_order_rep_sales_name  = $("input[name='order_rep_sales_name']").val();

    $.ajax({
          url: module_url_path+'/get_export_reps_orders',
          data: {q_lead_id:q_lead_id,q_username:q_username,q_company_name:q_company_name,q_tot_retail:q_tot_retail,q_tot_commi_less_wholesale:q_tot_commi_less_wholesale,q_enquiry_date:q_enquiry_date,q_shipping_status:q_shipping_status,q_payment_term:q_payment_term,q_vendor_payment:q_vendor_payment,q_rep_payment:q_rep_payment,q_from_date:q_from_date,q_to_date:q_to_date,q_order_rep_sales_name:q_order_rep_sales_name},
        
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
              saveData(data, 'representative_orders.csv');
            }
          }
        });
  }

</script>

@stop 