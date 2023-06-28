@extends('admin.layout.master')                
@section('main_content')
<link href="{{url('/')}}/assets/css/select2.min.css" rel="stylesheet" />
 
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
   .table > tbody > tr > td:first-child a {
        text-underline-position: under;
        text-decoration: underline;
        margin-bottom: 5px;
        display: inline-block;
    }
    .table > tbody > tr > td:first-child a:hover {
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
                <h4 class="page-title">{{$page_title or ''}}</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                   <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
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
                    <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/report_generator') }}">
                        {{ csrf_field() }}

                        <div class="row commission_filter_row">

                            <div class="col-md-3 mb-4">
                                <label>Select Vendor</label>
                                <select class="form-control dropdown" name="vendor" id="maker">
                                   <option value=""> Select Vendor</option>
                                        @if(isset($arrMakers) && count($arrMakers) > 0)
                                            @foreach($arrMakers as $vendorDetails)
                                                <option value="{{$vendorDetails['user_id']}}">{{isset($vendorDetails['company_name'])?$vendorDetails['company_name']:'-'}}</option>
                                            @endforeach
                                        @endif
                                </select>
                            </div>
                             <div class="col-md-3 mb-4">
                                <label>Select Customer</label>
                                <select class="form-control dropdown" name="retailer" id="retailer">
                                   <option value=""> Select Customer </option>
                                   @if(isset($arrCustomer) && count($arrCustomer) > 0)
                                        @foreach($arrCustomer as $customerDetails)
                                            <option value="{{$customerDetails['id']}}">{{$customerDetails['first_name']}} {{$customerDetails['last_name']}}</option>
                                        @endforeach
                                   @endif
                                </select>
                            </div>

                  
                            <div class="col-md-3 mb-4"> 
                                <label>From Date</label>
                                    <input type="text" name="from_date" class="datepicker form-control" data-date-format="mm/dd/yyyy" id="from_date" placeholder="From Date" readonly>
                                </div>
                                <div class="col-md-3 mb-4"> 
                                    <label>To Date</label>
                                    <input type="text" name="to_date" class="datepicker form-control" data-date-format="mm/dd/yyyy" id="to_date" placeholder="To Date" readonly>
                                </div>
                              
                                <div class="col-md-6 mb-4"> 
                                    <label>Vendor Payment Status (Outbound)</label>
                                    <select class="form-control dropdown" name="vendor_payment_status">
                                        <option value="">Vendor Payment Status (Outbound)</option>
                                        <option value="2">Pending</option>
                                        <option value="1">Paid</option> 
                                    </select>

                                </div>

                                <div class="col-md-6 mb-4"> 
                                    <label>{{$site_setting_arr['site_name'] or ''}} Commission Receipt Status</label>
                                    <select class="form-control dropdown" name="order_payment_status">
                                        <option value="">{{$site_setting_arr['site_name'] or ''}} Commission Receipt Status</option>
                                        <option value="2">Pending</option>
                                        <option value="1">Received</option>
                                    
                                    </select>

                                </div>


                               <div class="col-md-12 btnscenter">
                                    <button type="button" title="Search" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="Search" id="update">Search</button>

                                    <button type="submit" title="Export Commission Report as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="export" id="export">Export</button>
                                  
                                    <button type="reset" title="Clear" name="reset" class="btn btn-defaut waves-effect waves-light filter_btn pull-right" value="Clear" id="reset">Clear</button>
                                </div>
                        <div class="col-md-12 commission_report_view_sec">
                          <div class="col-sm-12">
                          <div class="row">
                            <div class="col-sm-4" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">Customers' Payments (to Vendor):</label>
                              
                            <h5>
                              <span id="commission_text">Total Payments Pending from Customers'</span>:
                              <h3 id="retailer_receivable_amount">$0.00</h3>
                            </h5>

                            <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total Payments Received from Customers'</span>:
                              <h3 id="retailer_received_amount">$0.00</h3>
                            </h5>
                            <h5>
                                <span >Total Customers' Payments</span>:
                                <h3 id="total_retailers_payment">$0.00</h3>
                              </h5>
                            </div>
                          </div>

                           <div class="col-sm-4" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">Vendor Payments(Outbound):</label>                             
                            <h5>
                              <span id="commission_text"> Total Payments Pending to Pay Vendors'</span>:
                              <h3 id="vendor_payable_amount">$0.00</h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total Payments Paid to Vendors'</span>:
                              <h3 id="vendor_paid_amount">$0.00</h3>
                            </h5>

                             <h5>
                                <span>Total Vendors' Payments</span>:
                                <h3 id="vendors_payment">$0.00</h3>
                              </h5>
                            </div>
                          </div>
                          
                          <div class="col-sm-4">
                            <div class="box">
                              <label class="font-larg-ventr">{{$site_setting_arr['site_name'] or ''}} Commissions:</label>
                            
                            <h5>
                              <!-- <span id="commission_text">Total Commissions Payable</span>: -->
                              <span id="rep_commission_text">Total {{$site_setting_arr['site_name'] or ''}} Commissions Pending</span>:
                              <h3 id="admin_commission_payable">$0.00</h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <!-- <span id="order_amount_text">Total Order Amount Dues</span>: -->
                              <span id="order_amount_text">Total {{$site_setting_arr['site_name'] or ''}} Commissions Paid</span>:
                              <h3 id="admin_commission_paid">$0.00</h3>
                            </h5>
                            <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total {{$site_setting_arr['site_name'] or ''}} Commissions Earned</span>:
                              <h3 id="admin_earned_commission">$0.00</h3>
                            </h5>
                            </div>
                          </div>
                         
                        
                        </div>
                        </div>
                        <div class="col-sm-12"> 
                          <div class="row">

                           
                              <div class="col-sm-12 commission-below-report-btns text-right">
                               <div class="dataTables_length" id="table_module_length" style="text-align: end;">
                                 
                                  <button type="button" id="table_module_length" title="Click to Generate Bulk Invoice" class="btn btn-success waves-effect waves-light filter_btn pull-right"  onclick="generate_bulk_invoice();">Bulk Invoice</button>

                                
                                  <span class="err" id="err_btns"></span>
                              </div>
                            </div>
                          </div>
                        </div>
                        </div>
                        
                        
                        <div class="col-sm-12 table-responsive">
                            <input type="hidden" name="bulk_invoice_customer" id="bulk_invoice_customer" value="">
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
                                        <th>Customer Name</th>
                                        <th>Vendor</th>
                                        <th>Customer Payment Status (To Vendor)</th>
                                        <th>{{ $site_setting_arr['site_name']}} Commissions Status</th>
                                        <th>{{ $site_setting_arr['site_name']}} Commissions Amount</th>
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
                    
                </div><!-- END Main Content -->
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
          <input type="hidden" name="maker_id"   id="maker_id">
          <input type="hidden" name="order_id"   id="orderId" >
          <input type="hidden" name="amount"     id="amount">

          <div class="mdl-admin-commi">
            <div class="adms-cmsns">
              <div class="innerbox">
                <div class="admin-commission-lnk">Admin Commission(%) :</div>
              <div class="admin-commission-lnk-right">{{isset($site_setting_arr['commission'])?num_format($site_setting_arr['commission']):0}}%</div>
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
        <button type="button" class="btn btn-secondary" onclick="payVendor()" >Pay</button>
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
        <button type="button" class="btn btn-secondary" onclick="payRepCommission()" >Pay</button>
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
<script src="{{url('/')}}/assets/js/select2.min.js"></script>
<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";
  console.log("module_url_path",module_url_path);

  var table_module = false;

  $(document).ready(function()
  {
   

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


    $('#update').click(function(){

        showProcessingOverlay();
        $("input.checkItemAll").prop('checked',false);

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

        $("#bulk_invoice_customer").val(retailer);
         $("#bulk_invoice_vendor").val(maker);


               
        if($('#frm_manage').parsley().validate()==false) return;
        showProcessingOverlay();
        filterData();
    });

     $('.datepicker').datepicker({
          // format: 'yyyy-mm-dd',
          format: 'mm-dd-yyyy',
          viewMode: "days", 
          minViewMode: "days"
       });
      
      $.fn.dataTable.ext.errMode = 'none';  
      showProcessingOverlay(); 

      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      stateSave: true,
      errMode: true,
     
      bFilter: false ,
      ajax: {
        destroy: true,
        searching: false,
        'url':'{{ $module_url_path.'/search_report'}}',
        'data': function(d)
        {
            hideProcessingOverlay();
            //--------------------------------------filters-----------------------------------------
            d['column_filter[to_date]']        = $("input[name='to_date']").val()
            d['column_filter[from_date]']      = $("input[name='from_date']").val()
            d['column_filter[order_status]']   = $("select[name='order_status']").val()
            d['column_filter[representative]'] = $("select[name='representative']").val()
            d['column_filter[retailer]']       = $("select[name='retailer']").val()
            d['column_filter[vendor]']         = $("select[name='vendor']").val()
            d['column_filter[sales_manager]']  = $("select[name='sales_manager']").val()
            
            d['column_filter[order_payment_status]'] = $("select[name='order_payment_status']").val()
            d['column_filter[vendor_payment_status]']= $("select[name='vendor_payment_status']").val()
            d['column_filter[rep_payment_status]']   = $("select[name='rep_payment_status']").val()

            //---------------------------------------End filters -----------------------------------

            // d['column_filter[q_order_no]']        = $("input[name='q_order_no']").val()
            // d['column_filter[q_order_date]']        = $("input[name='q_order_date']").val()
            // d['column_filter[q_commission_amount]'] = $("input[name='q_commission_amount']").val()
            // d['column_filter[q_rep_name]']        = $("input[name='q_rep_name']").val()
            // d['column_filter[q_vendor_name]']     = $("input[name='q_vendor_name']").val()
            // d['column_filter[q_retaier_name]']    = $("input[name='q_retaier_name']").val()
            // d['column_filter[q_order_amount]']    = $("input[name='q_order_amount']").val()          
            // d['column_filter[q_to_date]']    = $("input[name='to_date']").val()          
            // d['column_filter[q_from_date]']    = $("input[name='from_date']").val()          
            // d['column_filter[q_payment_status]']  = $("select[name='q_payment_status']").val()          
        }
      },

      columns: [

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
            return row.order_no;
           
         },
         "orderable": false,
         "searchable":false
      }, 
      
      {data: 'created_at', "orderable": false, "searchable":false}, 
      {data: 'orderer_name', "orderable": false, "searchable":false},
      {data: 'vendor_name', "orderable": false, "searchable":false},
     
      {
        render : function(data, type, row, meta) 
        {
          return `<span class="label label-warning">`+row.vendor_payment_status+`</span>`;
        
        },          
        "orderable": false, "searchable":false
      },

       {
        render : function(data, type, row, meta) 
        {
          return `<span class="label label-warning">`+row.admin_commission_status+`</span>`;
        
        },          
        "orderable": false, "searchable":false
      },
      

      {data: 'vendor_payment_amount', "orderable": false, "searchable":false},
      
     
      //Vendor Payment Status
  
    
    {
        render : function(data, type, row, meta) 
        {
          //--------------set value to the total commission----------------
          if(type == "display"){

            var rep_commission_pending    = row.rep_commission_pending;
            var rep_commission_paid       = row.rep_commission_paid;
            var order_amount_paid         = row.order_amount_paid;
            var order_amount_pending      = row.order_amount_pending;
            var vendor_commission_pending = row.vendor_commission_pending;
            var vendor_commission_paid    = row.vendor_commission_paid;
            var total_pay_vendors         = row.total_pay_vendors;
            var admin_commission          = row.admin_commission;

            var total_admin_commission              = row.total_admin_commission;
            var total_admin_commission_paid         = row.total_admin_commission_paid;
            var total_admin_commission_pending      = row.total_admin_commission_pending;

            var total_vendor_commission_pending = row.total_vendor_commission_pending;
            var total_vendor_commission_paid    = row.total_vendor_commission_paid;
            var total_pay_vendors               = row.total_pay_vendors;

      

          }
          else{
            var rep_commission_pending      = 0.00;
            var rep_commission_paid         = 0.00;
            var order_amount_paid           = 0.00;
            var order_amount_pending        = 0.00;
            var vendor_commission_pending   = 0.00;
            var vendor_commission_paid      = 0.00;
            var admin_commission            = 0.00;
            var total_pay_vendors           = 0.00;
            
            var total_admin_commission        = 0.00;
            var total_admin_commission_paid   = 0.00;
            var total_admin_commission_pending = 0.00;

            var total_vendor_commission_pending = 0.00;
            var total_vendor_commission_paid    = 0.00;
            var total_pay_vendors         = 0.00;

          }

          var formatter = new Intl.NumberFormat('en-US', {
                                style: 'currency',
                                currency: 'USD',
                              });

          $('#rep_commission_payable').html(formatter.format(rep_commission_pending));
          $('#rep_commission_paid').html(formatter.format(rep_commission_paid));

          $('#admin_earned_commission').html(formatter.format(admin_commission));

          $('#retailer_receivable_amount').html(formatter.format(order_amount_pending));
          $('#retailer_received_amount').html(formatter.format(order_amount_paid));

          let total_retailers_payment = (parseFloat(order_amount_paid)+parseFloat(order_amount_pending)).toFixed(2);

          $('#total_retailers_payment').html(formatter.format(total_retailers_payment));

          //Vendor Payments(Outbound)
          $('#vendor_payable_amount').html(formatter.format(total_vendor_commission_pending));
          $('#vendor_paid_amount').html(formatter.format(total_vendor_commission_paid));
          $('#vendors_payment').html(formatter.format(total_pay_vendors));



          //admin_commissions
          $('#admin_commission_payable').html(formatter.format(total_admin_commission_pending));
          $('#admin_commission_paid').html(formatter.format(total_admin_commission_paid));
          $('#admin_earned_commission').html(formatter.format(total_admin_commission));

          
          
          return formatter.format(row.total_retail_price);
        },
          
        "orderable": false, "searchable":false
      },

 
     
      //shipping_charges
      {
        render : function(data, type, row, meta) 
        {
          var shipping_charges = row.shipping_charge;
          return '$'+shipping_charges;
        },          
        "orderable": false, "searchable":false
      },

      //amount excluding shipping charges
      {
        render : function(data, type, row, meta) 
        {
          console.log(row);
          return '$'+row.amount_excluding_shipping_charge;
        },          
        "orderable": false, "searchable":false
      },
     
     
     // {data: 'action', "orderable": true, "searchable":false},
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
      $("input.checkItemAll").prop('checked',false);
      $("#bulk_invoice_customer").val("");
      $("#bulk_invoice_vendor").val();
      showProcessingOverlay();
      filterData();   

    });


    $('#table_module').on('draw.dt',function(event)
    {
      $.fn.dataTable.ext.errMode = 'none';   
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

       $("input.net_30").change(function(){
          net_30_statusChange($(this));
       });  

       toggleSelect();

    });
    
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
    hideProcessingOverlay();
  }

  function fillData(orderPrice,vendorAmount,adminCommissionAmount,makerId,orderId)
{
  $('.vendor-Modal').modal('show');
  $('#order_amount').html(orderPrice.toFixed(2));    
  $('#actual_amount').html(adminCommissionAmount.toFixed(2));    
  $('.pay_amount').html(vendorAmount.toFixed(2));    
  $('#maker_id').val(makerId);    
  $('#amount').val(vendorAmount.toFixed(2));    
  $('#orderId').val(orderId);    
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
/*-----------------------------------------------------------------------------*/

/* -------------------------- Generate invoice -------------------------------*/
function generate_invoice(order_no = false,orderType = false)
{  

    var csrf_token = "{{csrf_token()}}";

    var order_no = order_no;
    var orderType = orderType;

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


function generate_bulk_invoice(order_no = false, orderType = false)
{  


    $(".err").stop().fadeOut();

    var bulk_invoice_cust = $("#bulk_invoice_customer").val();
    var bulk_invoice_vendor = $("#bulk_invoice_vendor").val();

    if(bulk_invoice_vendor.length <= 0){
    
         swal({
                 title:"Warning",
                 text: "To generate the bulk invoice, Please search records by Vendor .",
                 type: "warning",
                 confirmButtonText: "OK",
                 closeOnConfirm: true
              },
             function(isConfirm,tmp)
             {
                   return false;
             });

      $("#maker").focus();
      return false;
    }
    
    if(bulk_invoice_cust.length <= 0){
     
         swal({
                 title:"Warning",
                 text: "To generate the bulk invoice, Please search records by Customer .",
                 type: "warning",
                 confirmButtonText: "OK",
                 closeOnConfirm: true
              },
             function(isConfirm,tmp)
             {
                   return false;
             });

         $("#retailer").focus();
      return false;
    }

    

    if($('.checkbox_bulkInvoice_vendor:checked').length <= 0)
    {
     
        /*$("#err_btns").fadeIn(1000);
        $("#err_btns").html("<br>&#9888 Oops, Please Select atleast 1 checkbox for generating invoice.");*/
      swal({
                 title:"Warning",
                 text: "Please Select atleast 1 checkbox for generating invoice.",
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
    
    var orderType = "";
    var checkOrderItems = [];
    $('.checkbox_bulkInvoice_vendor:checked').each(function(i, e) {

        var orderObj = {
          orderNumber : $(this).val(),
          orderType : $(this).data("ordertype")
        }
        // alert($(this).data("ordertype"));
        checkOrderItems.push(orderObj);
       // checkOrderItems[orderType][] =$(this).val();
        
    });
        
    
    var csrf_token = "{{csrf_token()}}";

    var generate_invoice_url  = "{{ url(config('app.project.admin_panel_slug')."/admin_commission_reports")."/admin_commission_invoice_generator_bulk" }}";
    
    $.ajax({
               url: generate_invoice_url,
               type:"POST",
               dataType:'json',
               data:{'_token':csrf_token, 'checkOrderItems': checkOrderItems},
               beforeSend: function() 
               {

                 showProcessingOverlay();                
               },
               success:function(response)
               {
                   hideProcessingOverlay();
                  //return false;
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
</script>

@stop 