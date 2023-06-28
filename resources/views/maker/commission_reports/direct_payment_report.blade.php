@extends('maker.layout.master')                
@section('main_content')
<link href="{{url('/')}}/assets/css/select2.min.css" rel="stylesheet" />
<link href="{{url('/')}}/assets/css/common.css" rel="stylesheet" />
  
<style type="text/css">
   th {
   white-space: nowrap;
   }
   .dataTable > thead > tr > th[class*="sort"]:after{
   content: "" !important;
   }
   table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
   padding-right: inherit;
   }
   .filter_btn{
      margin-top: 20px;
      margin-right: 2px;
   }

   .btn-primary {
    color: #fff !important;
    background-color: #0275d8 !important;
    border-color: #0275d8 !important;
  }
  .btn-sm {
    padding: .60rem 1.0rem !important;
    font-size: 1.1rem !important;
    border-radius: 0.3rem !important;
}
</style>
<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">
<!-- Page Content -->
<div id="scroll_on"></div>
<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">{{$page_title or ''}}</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                   <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
                   <li class="active">{{$module_title or ''}}</li>
                </ol>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                @include('admin.layout._operation_status')
                <div class="white-box">
                  <!-- <div class="col-sm-12 text-right commission_report_export_btn_div">
                    <button type="submit" title="Export Commission Report as .csv" class="btn btn-success waves-effect waves-light pull-right" value="export" id="export">Export</button>
                  </div> -->
                    <div class="clearfix"></div>
                    <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($exprot_csv_url) }}">
                        {{ csrf_field() }}

                        <input type="hidden" name="loggedInUserId" value = "{{$loggedInUserId}}">
                        <div class="row commission_filter_row">
                          <!-- reppresentative and sales manager filter -->

                           <!--  <div class="col-md-4 mb-4">
                                <label>Select Representative</label>
                                <select class="form-control dropdown" name="representative" id="rep_name">
                                    <option value=""> Select Representatives </option>
                                    @if(isset($arrRepresentative) && count($arrRepresentative) > 0)
                                        @foreach($arrRepresentative as $representativeDetails)
                                            <option value="{{$representativeDetails['id']}}">{{$representativeDetails['first_name']}} {{$representativeDetails['last_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label>Select Sales Manager</label>
                                <select class="form-control dropdown" name="sales_manager" id="sales_name">
                                    <option value=""> Select Sales Manager </option>
                                    @if(isset($arrSalesManager) && count($arrSalesManager) > 0)
                                        @foreach($arrSalesManager as $salesManager)
                                            <option value="{{$salesManager['id']}}">{{$salesManager['first_name']}} {{$salesManager['last_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div> -->
                            <div class="col-md-12 mb-4">
                                <label>Select Retailer</label>
                                <select class="form-control dropdown" name="retailer" id="retailer">
                                   <option value=""> Select Retailer </option>
                                   @if(isset($arrRetailer) && count($arrRetailer) > 0)
                                        @foreach($arrRetailer as $retailerDetails)
                                            <option value="{{$retailerDetails['user_id']}}">{{$retailerDetails['store_name']}} </option>
                                        @endforeach
                                   @endif
                                </select>
                            </div>
                            

                            <div class="col-md-6 mb-4"> 
                                <label>From Date</label>
                                    <input type="text" name="from_date" class="datepicker form-control" data-date-format="mm-dd-yyyy" id="from_date" placeholder="From Date" readonly>
                                </div>
                                <div class="col-md-6 mb-4"> 
                                    <label>To Date</label>
                                    <input type="text" name="to_date" class="datepicker form-control" data-date-format="mm-dd-yyyy" id="to_date" placeholder="To Date" readonly>
                                </div>
                                <div class="col-md-6 mb-4"> 
                                    <label>Retailer Payment Status (to Vendor)</label>
                                    <select class="form-control dropdown" name="order_payment_status">
                                        <option value="">Retailer Payment Status (to Vendor)</option>
                                        <option value="1">Payment Pending</option>
                                        <option value="2">Payment Received</option>
                                    
                                    </select>

                                </div>

                                
                                <div class="col-md-6 mb-4"> 
                                    <label>{{$site_setting_arr['site_name'] or ''}} Commission Receipt Status</label>
                                    <select class="form-control dropdown" name="vendor_payment_status">
                                        <option value="">{{$site_setting_arr['site_name'] or ''}} Commission Receipt Status</option>
                                        <option value="2">Pending</option>
                                        <option value="1">Paid</option> 
                                    </select>

                                </div>

                                <!-- Rep commission payment status filter -->
                                <!-- <div class="col-md-6 mb-4">
                                  <div id="commission_status">
                                    <label>Rep Commission Payment Status</label>
                                      <select class="form-control dropdown" name="order_status">
                                          <option value="">Rep Commission Payment Status</option>
                                          <option value="2">Pending</option>
                                          <option value="1">Paid</option>
                                      </select>
                                  </div> 
                                </div> -->

                                <div class="col-sm-12 btnscenter mb-4">
                                  <button type="button" title="Search" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="Search" id="update">Search</button>
                                  <button type="submit" title="Export Commission Report as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="export" id="export">Export</button>
                                  
                                   <button type="reset" title="Clear" name="reset" class="btn btn-defaut waves-effect waves-light filter_btn pull-right" value="reset" id="reset">Clear</button>
                                </div>
                                  

                        <div class="col-md-12 commission_report_view_sec m-b-0">
                          <div class="col-sm-12">
                          <div class="row">
                            <div class="col-sm-6 col-lg-6" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">Retailers' Payments (to Vendor):</label>
                              
                            <h5>
                         
                              <span id="commission_text">Total Payments Pending from Retailers'</span>:
                              <h3 >$<span id="retailer_receivable_amount">0.00</span></h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                            
                              <span id="order_amount_text">Total Payments Received from Retailers'</span>:
                              <h3 >$<span id="retailer_received_amount">0.00 </span></h3>
                            </h5>
                            <h5>
                                <span>Total Retailers' Payments</span>:
                                <h3 >$<span id="total_retailers_payment">0.00</span></h3>
                              </h5>
                            </div>
                          </div>
                           <div class="col-sm-6 col-lg-6" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">{{$site_setting_arr['site_name'] or ''}} Commissions Receipts:</label>

                            <h5>
                              <span id="commission_text">Total Commissions Pending from Vendors'</span>:
                              <h3>$<span id="vendor_payable_amount">0.00</span></h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total Commissions Received from Vendors'</span>:
                              <h3 >$<span id="vendor_paid_amount">0.00</span></h3>
                            </h5>
                              <h5>
                                <span>Total {{$site_setting_arr['site_name'] or ''}} Commissions Earned</span>:
                                <h3>$<span id="total_vendors_amount">0.00</span></h3>
                              </h5>

                            </div>
                          </div>
                         
                         <!--  Rep Commissions Payments Box -->
                         <!--  <div class="col-sm-4">
                            <div class="box">
                              <label class="font-larg-ventr">Rep Commissions Payments:</label>                            
                            <h5>
                              
                              <span id="rep_commission_text">Total Rep Commissions Pending</span>:
                              <h3 id="rep_commission_payable">$0.00</h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                             
                              <span id="order_amount_text">Total Rep Commissions Paid</span>:
                              <h3 id="rep_commission_paid">$0.00</h3>
                            </h5>
                            <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total Rep Commissions Earned</span>:
                              <h3 id="admin_earned_commission">$0.00</h3>
                            </h5>
                            
                            </div>
                          </div>
                          -->
                          
                          
                        </div>


                        </div>
                        
                        <div class="col-sm-12"> 
                          <div class="row">

                            <div class="col-sm-6">
                              <div class="dataTables_length" id="table_module_length">
                                  <label></label>
                              </div>
                            </div>
                              <div class="col-sm-6">
                               <div class="dataTables_length" id="table_module_length" style="text-align: end;">
                                  {{-- <button type="button" class="btn btn-primary btn-sm" onclick="generate_bulk_invoice();">
                                      <i class="fa fa-paper-plane-o" aria-hidden="true"></i> &nbsp;Bulk Invoice    
                                  </button> --}}

                                {{--   <button type="button" id="table_module_length" title="Click to Generate Bult Invoice." class="btn btn-success waves-effect waves-light filter_btn pull-right"  onclick="generate_bulk_invoice();">Bulk Invoice</button> --}}

                                  <span class="loader" id="loader_login" style="color: #07892f;"> 
                                      <i class="fa fa-spinner fa-pulse fa-3x fa-fw" style="font-size: 19px;font-weight: bold;"></i>                                 
                                  </span>
                                  <span class="err" id="err_btns"></span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                        
                        
                        <div class="col-sm-12 table-responsive">
                           <input type="hidden" name="bulk_invoice_vendor" id="bulk_invoice_vendor" value=""> 
                            <input type="hidden" name="multi_action" value="" />
                           
                            <table id="table_module" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="checkbox checkbox-success">
                                              <input type="checkbox" name="checked_record_all[]" id="checked_record_all" class="case checkItemAll">
                                                <label for="checkbox"></label>
                                            </div>
                                        </th>
                                        <th>Order No.</th>
                                        <th>Order Date</th>
                                        <th>Order By<br>Rep / Sales Manager</th>
                                        <th>Vendor</th>
                                        <th>Retailer Name</th>
                                        <th>Retailer Payment<br> Status (to Vendor)</th>         <th>{{$site_setting_arr['site_name'] or ''}} Commission<br>Amount</th>                             
                                        <th>{{$site_setting_arr['site_name'] or ''}} Commission<br>Receipt Status</th>                             
                                        <!-- <th>Rep/Sales Commission<br>Amount</th>                             
                                        <th>Rep/Sales Commission<br>Payment Status</th>   -->
                                        <th>Order Amount</th>
                                        <th>Shipping Charges</th>
                                        <th>Order Amount<br>(Excluding Shipping Charge)</th>
                                        <th>Action</th>                                      
                                    </tr>
                                </thead>                            
                                
                                <tbody>
                                </tbody>
                              
                            </table>
                           
                        </div>
                    </form>                        
                    
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade vendor-Modal" id="VendorPaymentModal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
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
          <input type="hidden" name="order_from" id="order_from">


          <div class="mdl-admin-commi">
            <div class="adms-cmsns">
              <div class="innerbox">
              <div class="admin-commission-lnk">Admin Commission(%) :</div>
              <div class="admin-commission-lnk-right"><span id="admin_commission_per"></span>%</div>
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
         <button type="button" class="btn btn-secondary" onclick="payAdmin();" >Pay</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
       
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


<script src="{{url('/')}}/assets/js/select2.min.js"></script>

<script type="text/javascript">


var module_url_path  = "{{ $module_url_path or '' }}";
var table_module = false;

$(document).ready(function()
{
   
  // $('.datepicker').datepicker({
  //         // format: 'yyyy-mm-dd',
  //         format:'mm-dd-yyyy',
  //         viewMode: "days", 
  //         minViewMode: "days",
  //         separator: '-'
  //      });
      
    $( function() {
        $( ".datepicker" ).datepicker();
        
        $('#from_date').datepicker('setEndDate', '+0d');
        $('#to_date').datepicker('setEndDate', '+0d');

        $("#from_date").datepicker({
          
          todayBtn:  1,
          autoclose: true,

        }).on('changeDate', function (selected) {
          var minDate = new Date(selected.date.valueOf());
          $('#to_date').datepicker('setStartDate', minDate);
        });
      
        $("#to_date").datepicker()
          .on('changeDate', function (selected) {
              var minDate = new Date(selected.date.valueOf());
              $('#from_date').datepicker('setEndDate', minDate);
          });


        $("input.checkItemAll").click(function(){
                                             
                                        if($(this). prop("checked") == true){
                                          $("input.checkOrderItems").prop('checked',true);
                                        }
                                        else{
                                          $("input.checkOrderItems").prop('checked',false);
                                        }

                                    });

    
    });
    //-----------------select with search dropdown--------------------------------
    $("#rep_name").select2();
    $("#retailer").select2();
    $("#maker").select2();
    $("#sales_name").select2();

    //-----------------if rep select then reset sales manager dropdown-------------
    $('#rep_name').change(function(){
      $('#sales_name').prop('selectedIndex',0);
      $('#sales_name').select2();
    });

    //-----------------if sales manager select then reset rep dropdown-------------
    $('#sales_name').change(function(){
        $('#rep_name').prop('selectedIndex',0);
        $("#rep_name").select2();
    });

    $('#retailer').change(function () {
        var reps = $('#rep_name').val();
        var sales = $('#sales_name').val();

        if (reps == "" && sales == "") {
          $('#commission_status').hide();
        }
    });

    $('#rep_name').change(function () {
        
      $('#commission_status').show();
     
    });

    $('#sales_name').change(function () {
       
      $('#commission_status').show();
      
    });

        var rep_val               = $("select[name='representative']").val();
        var retailer              = $("select[name='retailer']").val();
        var maker                 = $("select[name='vendor']").val();
        var sales_manager         = $("select[name='sales_manager']").val();

        var to_date               = $("#to_date").val();
        var from_date             = $("#from_date").val();

        var rep_name              = $("select[name='representative'] option:selected").text();       
        var maker_name            = $("select[name='vendor'] option:selected").text();
        var sales_manager_name    = $("select[name='sales_manager'] option:selected").text();

        var commissionStatus      = $("select[name='order_status']").val();
        var orderStatus           = $("select[name='order_payment_status']").val();
        var vendorPaymentStatus   = $("select[name='vendor_payment_status']").val();
               
    if($('#frm_manage').parsley().validate()==false) return;

    $('#update').click(function(){

        showProcessingOverlay();

        $("input.checkItemAll").prop('checked',false);

        var rep_val               = $("select[name='representative']").val();
        var retailer              = $("select[name='retailer']").val();
        //var maker                 = $("select[name='vendor']").val();

        // if(maker.length <= 0){
        //   maker = ""; 
        // }

        //$("#bulk_invoice_vendor").val(maker);

        var sales_manager         = $("select[name='sales_manager']").val();

        var to_date               = $("#to_date").val();
        var from_date             = $("#from_date").val();

        var rep_name              = $("select[name='representative'] option:selected").text();       
        var maker_name            = $("select[name='vendor'] option:selected").text();
        var sales_manager_name    = $("select[name='sales_manager'] option:selected").text();

        var commissionStatus      = $("select[name='order_status']").val();
        var orderStatus           = $("select[name='order_payment_status']").val();
        var vendorPaymentStatus           = $("select[name='vendor_payment_status']").val();
               
        if($('#frm_manage').parsley().validate()==false) return;
            filterData();
    });

     $('.datepicker').datepicker({
          // format: 'yyyy-mm-dd',
          format: 'mm-dd-yyyy',
          viewMode: "days", 
          minViewMode: "days"
       });
      


     showProcessingOverlay();
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },    
      processing: true,
      serverSide: true,
      autoWidth: false,
      stateSave: true,
     
      bFilter: false ,
      ajax: {
        destroy: true,
        searching: false,
        'url':'{{$module_url_path}}',
        'data': function(d)
        {
            hideProcessingOverlay();
            //--------------------------------------filters-----------------------------------------
            d['column_filter[to_date]']         = $("input[name='to_date']").val()
            d['column_filter[from_date]']       = $("input[name='from_date']").val()
            d['column_filter[order_status]']    = $("select[name='order_status']").val()
            d['column_filter[representative]']  = $("select[name='representative']").val()
            d['column_filter[retailer]']        = $("select[name='retailer']").val()
            d['column_filter[vendor]']          = $("select[name='vendor']").val()
            d['column_filter[sales_manager]']   = $("select[name='sales_manager']").val()
            
            d['column_filter[order_payment_status]'] = $("select[name='order_payment_status']").val()
            d['column_filter[vendor_payment_status]'] = $("select[name='vendor_payment_status']").val()         
        }
      },

      columns: [
      
     /* {data: 'order_no', "orderable": true, "searchable":false}, */
      { 
         render : function(data, type, row, meta) 
         { 
            return row.showCheckboxes;
           
         },
         "orderable": false,
         "searchable":false
      }, 
      { 
         render : function(data, type, row, meta) 
         { 
            return row.order_link;
           
         },
         "orderable": false,
         "searchable":false
      }, 
      {data: 'created_at', "orderable": true, "searchable":false}, 
      {data: 'rep_name', "orderable": true, "searchable":false}, 
      {data: 'vendor_name', "orderable": true, "searchable":false},
      {data: 'orderer_name', "orderable": true, "searchable":false},

      
       
      //Retailer PaymentStatus (to Vendor)
      {
        render : function(data, type, row, meta) 
        { 
          //console.log(row);
          if(row.transaction_status == 2)
          {
            
            return '<span class="label label-success">Paid</span>';
          }
          if(row.transaction_status == 3)
          {
            
            return '<span class="label label-danger">Failed</span>';
          }
          if(row.transaction_status == 1)
          {

            return '<span class="label label-warning">Pending</span>';
          }
          else{

            return '<span class="label label-warning">Pending</span>';
          }
        },
        "orderable": false, "searchable":false
      },

      {
        render : function(data, type, row, meta) 
        {
          // return '$'+row.amount;
          return '$'+row.admin_commission_amount;
        },          
        "orderable": false, "searchable":false
      },
      {
        render : function(data, type, row, meta) 
        { 
          if(row.admin_commission_status == 0 || row.admin_commission_status==null)
          {

            return '<span class="label label-warning">Pending</span>';
          }
          //---------------For retailer orders-----------
          if(row.admin_commission_status == 1)
          {

            return '<span class="label label-success">Paid</span>';
          }
          else{

            return '<span class="label label-warning">Pending</span>';
          }
        },
        "orderable": false, "searchable":false
      },
      //rep/sales commission amount 
      // {
      //   render : function(data, type, row, meta) 
      //   {
      //    return row.rep_commission_amount;
      //   },          
      //   "orderable": false, "searchable":false
      // },
      
      //rep/sales commission amount
      // {
      //   render : function(data, type, row, meta) 
      //   {         
      //     if(row.rep_commission_status == 0 || row.sales_manager_commission_status!=1)
      //     {
            
      //       var rep_sales_commission_status = '<span class="label label-warning">Pending</span>';
      //     }

      //     if(row.rep_commission_status == 1 || row.sales_manager_commission_status==1)
      //     {
            
      //       var rep_sales_commission_status = '<span class="label label-success">Paid</span>';
      //     }

      //     if(row.representative_id!=null||row.sales_manager_id!=null)
      //     {
      //       return rep_sales_commission_status;
      //     }
          
      //     if(row.status == '-'){
            
      //       return '-';

      //     }
      //     else{

      //       return '<span class="label label-warning">Pending</span>';
      //     }
      //   },
      //   "orderable": false, "searchable":false
      // },

      //order amount
      {
        render : function(data, type, row, meta) 
        {
          return '$'+row.total_wholesale_price;
        },          
        "orderable": false, "searchable":false
      },

      //shipping charges
      {
        render : function(data, type, row, meta) 
        {
          var shipping_charges = row.total_wholesale_price - row.amount_excluding_shipping_charge;
          shipping_charges = parseFloat(shipping_charges).toFixed(2);
          return '$'+shipping_charges;
        },          
        "orderable": false, "searchable":false
      },


      //Order Amount (Excluding Shipping Charge)    
      {
        render : function(data, type, row, meta) 
        {
          if(type == "display"){

            var rep_commission_pending    = row.rep_commission_pending;
            var rep_commission_paid       = row.rep_commission_paid;
            var order_amount_paid         = row.order_amount_paid;
            var order_amount_pending      = row.order_amount_pending;
            
            var vendor_commission_pending = row.vendor_commission_pending;
            var vendor_commission_paid    = row.vendor_commission_paid;
          
            var admin_commission          = row.admin_commission;
            
            var total_vendors_amount      =  row.total_vendors_amount;
            var total_retailers_payment   = row.total_retailers_payment;

            var total_rep_commission              = row.total_rep_commission;
            var total_rep_commission_paid         = row.total_rep_commission_paid;
            var total_rep_commission_pending      = row.total_rep_commission_pending;
       

          }
          else{
            var rep_commission_pending      = '0.00';
            var rep_commission_paid         = '0.00';
            var order_amount_paid           = '0.00';
            var order_amount_pending        = '0.00';
            var vendor_commission_pending   = '0.00';
            var vendor_commission_paid      = '0.00';
            var admin_commission            = '0.00';
            var total_rep_commission        = '0.00';
            var total_rep_commission_paid   = '0.00';
            var total_rep_commission_pending = '0.00';

          }
           var formatter = new Intl.NumberFormat('en-US', {
                                style: 'currency',
                                currency: 'USD',
                              });
          $('#rep_commission_payable').html(rep_commission_pending);
          $('#rep_commission_paid').html(rep_commission_paid);

          $('#admin_earned_commission').html(admin_commission);

          $('#retailer_receivable_amount').html(order_amount_pending);
          $('#retailer_received_amount').html(order_amount_paid);
          $("#total_retailers_payment").html(total_retailers_payment);

          $('#vendor_payable_amount').html(vendor_commission_pending);
          $('#vendor_paid_amount').html(vendor_commission_paid);
          $("#total_vendors_amount").html(total_vendors_amount);

          //rep_commissions
          $('#rep_commission_payable').html(formatter.format(total_rep_commission_pending));
          $('#rep_commission_paid').html(formatter.format(total_rep_commission_paid));
          $('#admin_earned_commission').html(formatter.format(total_rep_commission));
          
          return '$'+row.amount_excluding_shipping_charge;
        },
          
        "orderable": false, "searchable":false
      },

      {
        render : function(data, type, row, meta) 
        {
          //alert(JSON.stringify(row)); 
          return row.action;
        },          
        "orderable": false, "searchable":false
      },
      
      ],
    });

    $('input.column_filter').on( 'keyup click', function () 
    {
        filterData();
    });

     $('#reset').click(function(){

      $('.dropdown').prop('selectedIndex',0);
      $("#rep_name").select2();
      $("#retailer").select2();
      $("#maker").select2();
      $("#sales_name").select2();
      $('.datepicker').val('');

      filterData();   
    });

    $('#table_module').on('draw.dt',function(event)
    {      
      $.fn.dataTable.ext.errMode = 'none';   
      var oTable = $('#table_module').dataTable();
      var recordLength = oTable.fnGetData().length;

      if(recordLength==0)
      {

          $('#rep_commission_payable').html("0.00");
          $('#rep_commission_paid').html("0.00");

          $('#admin_earned_commission').html("0.00");

          $('#retailer_receivable_amount').html("0.00");
          $('#retailer_received_amount').html("0.00");
          $("#total_retailers_payment").html("0.00");

          $('#vendor_payable_amount').html("0.00");
          $('#vendor_paid_amount').html("0.00");
          $("#total_vendors_amount").html("0.00");

      }

      $('#record_count').html(recordLength);

      var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
           new Switchery($(this)[0], $(this).data());
        });

      $("input.toggleSwitch").change(function(){
          statusChange($(this));
       });  

       $("input.net_30").change(function(){
          net_30_statusChange($(this));
       });  

       toggleSelect();

    });
       filterData();
    
        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });


        $("#table_module").on('page.dt', function (){

          var info = table_module.page.info();
         
          $("input.checkItemAll").prop('checked',false);
      
        });


});


function filterData()
{
  table_module.draw();
}



function fillData(orderPrice,adminCommissionPer,adminCommissionAmount,orderId,vendorId,OrderFrom)
{
   
    console.log(orderPrice,adminCommissionAmount,orderId,vendorId);
    $('.vendor-Modal').modal('show');
   
    $('#order_amount').html(orderPrice.toFixed(2));     
    $('.pay_amount').html(adminCommissionAmount.toFixed(2)); 
    $('#admin_commission_per').html(adminCommissionPer.toFixed(2));    
    $('#orderId').val(orderId);    
    $('#amount').val(adminCommissionAmount);     
    $('#maker_id').val(vendorId);     

    if(OrderFrom == 1)
    {
        $("#order_from").val('retailer');
        
    }
    else
    {
        $("#order_from").val('rep_sales');
    }
   
}

function payAdmin()
{
    var paymentFormData = $('form').serialize();
    
    $.ajax({
            url: '{{url('/vendor')}}'+'/payment/admin',
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
                    swal({title: "Success", 
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
            url: '{{url('/vendor')}}'+'/payment/send_stripe_acc_creation_link',
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



/*-----------------------------------------------------------------------------*/
</script>

@stop 