@extends('admin.layout.master')                
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
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-12">
                <h4 class="page-title">{{$page_title or ''}}</h4>
            </div>
            <div class="col-lg-9 col-sm-6 col-md-6 col-xs-12">
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
                            <div class="col-md-3 mb-4">
                                <label>Select Sales Manager</label>
                                <select class="form-control dropdown" name="sales_manager" id="sales_name">
                                    <option value=""> Select Sales Manager </option>
                                    @if(isset($arrSalesManager) && count($arrSalesManager) > 0)
                                        @foreach($arrSalesManager as $salesManager)
                                            <option value="{{$salesManager['id']}}">{{$salesManager['first_name']}} {{$salesManager['last_name']}}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-3 mb-4" id="maker_div">
                                <label>Select Vendor</label>
                                <select class="form-control dropdown" name="vendor" id="maker">
                                   <option value=""> Select Vendor</option>
                                        @if(isset($arrMakers) && count($arrMakers) > 0)
                                            @foreach($arrMakers as $vendorDetails)
                                                <option value="{{$vendorDetails['user_id']}}">{{isset($vendorDetails['company_name'])?$vendorDetails['company_name']:'-'}}</option>
                                            @endforeach
                                        @endif
                                </select>
                                <span class="err" id="err_maker"></span>
                            </div>
                            
                            <div class="col-md-3 mb-4">
                                <label>Select Retailer</label>
                                <select class="form-control dropdown" name="retailer" id="retailer">
                                   <option value=""> Select Retailer </option>
                                   @if(isset($arrRetailer) && count($arrRetailer) > 0)
                                        @foreach($arrRetailer as $retailerDetails)
                                            <option value="{{$retailerDetails['user_id']}}">
                                              {{isset($retailerDetails['store_name'])?$retailerDetails['store_name']:'-'}} 
                                            </option>
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

                                <div class="col-md-6 mb-4">
                                  <div id="commission_status">
                                    <label>Rep Commission Payment Status</label>
                                      <select class="form-control dropdown" name="order_status">
                                          <option value="">Rep Commission Payment Status</option>
                                          <option value="2">Pending</option>
                                          <option value="1">Paid</option>
                                      </select>
                                  </div> 
                                </div>

                                <div class="col-sm-12 btnscenter">
                                  <button type="button" title="Search" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="Search" id="update">Search</button>
                                  <button type="submit" title="Export Commission Report as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="export" id="export">Export</button>
                                  
                                   <button type="reset" title="Clear" name="reset" class="btn btn-defaut waves-effect waves-light filter_btn pull-right" value="reset" id="reset">Clear</button>
                                </div>
                                  

                        <div class="col-md-12 commission_report_view_sec m-b-0">
                          <div class="col-sm-12">
                          <div class="row">
                            <div class="col-sm-4" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">Retailers' Payments (to Vendor):</label>
                              
                            <h5>
                              <!-- <span id="commission_text">Total Commissions Paid</span>: -->
                              <span id="commission_text">Total Payments Pending from Retailers'</span>:
                              <h3 >$<span id="retailer_receivable_amount">0.00</span></h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <!-- <span id="order_amount_text">Total Order Amount Received</span>: -->
                              <span id="order_amount_text">Total Payments Received from Retailers'</span>:
                              <h3 >$<span id="retailer_received_amount">0.00</span></h3>
                            </h5>
                            <h5>
                                <span>Total Retailers' Payments</span>:
                                <h3 >$<span id="total_retailers_payment">0.00</span></h3>
                              </h5>
                            </div>
                          </div>
                           <div class="col-sm-4" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">{{$site_setting_arr['site_name'] or ''}} Commissions Receipts:</label>

                            <h5>
                              <span id="commission_text">Total Commissions Pending from Vendors'</span>:
                              <h3 >$<span id="vendor_payable_amount">0.00</span></h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total Commissions Received from Vendors'</span>:
                              <h3 >$<span id="vendor_paid_amount">0.00</span></h3>
                            </h5>
                              <h5>
                                <span>Total {{$site_setting_arr['site_name'] or ''}} Commissions Earned</span>:
                                <h3 >$<span id="total_vendors_amount">0.00</span></h3>
                              </h5>

                            </div>
                          </div>
                          <div class="col-sm-4">
                            <div class="box">
                              <label class="font-larg-ventr">Rep Commissions Payments:</label>                            
                            <h5>
                              <!-- <span id="commission_text">Total Commissions Payable</span>: -->
                              <span id="rep_commission_text">Total Rep Commissions Pending</span>:
                              <h3 ><span id="rep_commission_payable">0.00</span></h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <!-- <span id="order_amount_text">Total Order Amount Dues</span>: -->
                              <span id="order_amount_text">Total Rep Commissions Paid</span>:
                              <h3 ><span id="rep_commission_paid">0.00</span></h3>
                            </h5>
                            <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total Rep Commissions Earned</span>:
                              <h3 ><span id="admin_earned_commission">0.00</span></h3>
                            </h5>
                            
                            </div>
                          </div>
                         
                          
                          
                        </div>


                        </div>
                        
                        <div class="col-sm-12"> 
                          <div class="row">

                            <!-- <div class="col-sm-4">
                              <div class="dataTables_length" id="table_module_length">
                                  <label></label>
                              </div>
                            </div> -->
                              <div class="col-sm-12 commission-below-report-btns text-right">
                               <div class="dataTables_length" id="table_module_length">
                                  {{-- <button type="button" class="btn btn-primary btn-sm" onclick="generate_bulk_invoice();">
                                      <i class="fa fa-paper-plane-o" aria-hidden="true"></i> &nbsp;Bulk Invoice    
                                  </button> --}}
                                  <button type="button" id="table_module_length" title="Click to Generate Bulk Invoice" class="btn btn-success waves-effect waves-light filter_btn mr-2"  onclick="generate_bulk_invoice();">Bulk Invoice</button>
                                  

                                  <!--  <button type="button" id="table_module_length" title="Click for vendor bulk payment." class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2"  onclick="bulkPayment_validation('vendor');"> 
                                      <i class="fa fa-paper-plane-o" aria-hidden="true"></i> 
                                      Bulk Pay Vendor
                                  </button> -->

                                <button type="button" id="table_module_length" title="Click for Representative Bulk Payment" class="btn btn-success waves-effect waves-light filter_btn mr-2"  onclick="bulkPayment_validation('representative');">
                                      <i class="fa fa-paper-plane" aria-hidden="true"></i>  
                                      Bulk Pay Representative
                                </button>

                                <button type="button" id="table_module_length" title="Click for Sales Manager Bulk Payment" class="btn btn-success waves-effect waves-light filter_btn"  onclick="bulkPayment_validation('sales_manager');">
                                       <i class="fa fa-paper-plane-o" aria-hidden="true"></i>  
                                      Bulk Pay  Sales Manager
                                </button>


                                  

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

                          <input type="hidden" name="vendor_bulk_payment" id="vendor_bulk_payment" value="">
                          <input type="hidden" name="representative_bulk_payment" id="representative_bulk_payment" value="">  
                          <input type="hidden" name="sales_manager_bulk_payment" id="sales_manager_bulk_payment" value="">

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
                                        <th>Rep / Sales<br> Manager</th>
                                        <th>Vendor</th>
                                        <th>Retailer Name</th>
                                        <th>Retailer Payment<br> Status (to Vendor)</th>         <th>{{$site_setting_arr['site_name'] or ''}} Commission<br>Amount</th>                             
                                        <th>{{$site_setting_arr['site_name'] or ''}} Commission<br>Receipt Status</th>                             
                                        <th>Rep/Sales Commission<br>Amount</th>                             
                                        <th>Rep/Sales Commission<br>Payment Status</th>  
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
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Vendor Payment</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form method="post" id="vendorPaymentForm"> 
          {{csrf_field()}}

          <!-- Hidden Fields -->
          <input type="hidden" name="maker_id" id="maker_id">
          <input type="hidden" name="order_id" id="orderId" >
          <input type="hidden" name="amount" id="amount">

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
         <button type="button" class="btn btn-secondary" onclick="payVendor()" >Pay</button>
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
        <!-- <button type="button" class="btn btn-secondary" onclick="payRepCommission()" >Pay</button>-->
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
     
      </div>
    </div>
  </div>
</div>


<!-- Bulk Payment Modal -->
<div class="modal fade " id="bulkPaymentModel" tabindex="-1" role="dialog" aria-labelledby="RepPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered titlecommission  modal-lg" role="document" style="max-width: 1210px;">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title" id="model_header_title">
            Bulk Pay for <em id="bulkPay_title" style="font-weight: bold;"></em>
        </h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body table-responsive" id="model_body">
        
      </div>
      <div class="modal-footer">

        <input type="hidden" name="user_id" id="user_id" value="">

        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-secondary" onclick="bulk_payCommission();" > Pay </button>
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
        var maker                 = $("select[name='vendor']").val();

        var sales_manager         = $("select[name='sales_manager']").val();

        if(maker.length <= 0){
          maker = ""; 
        }

        $("#bulk_invoice_vendor").val(maker);

        $("#vendor_bulk_payment").val(maker);
        $("#representative_bulk_payment").val(rep_val);
        $("#sales_manager_bulk_payment").val(sales_manager);


        

        var to_date               = $("#to_date").val();
        var from_date             = $("#from_date").val();

        var rep_name              = $("select[name='representative'] option:selected").text();       
        var maker_name            = $("select[name='vendor'] option:selected").text();
        var sales_manager_name    = $("select[name='sales_manager'] option:selected").text();

        var commissionStatus      = $("select[name='order_status']").val();
        var orderStatus           = $("select[name='order_payment_status']").val();
        var vendorPaymentStatus           = $("select[name='vendor_payment_status']").val();
               
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
        'url':'{{ $module_url_path.'/search_report'}}',
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
      {data: 'created_at', "orderable": false, "searchable":false}, 
      {data: 'rep_name', "orderable": false, "searchable":false}, 
      {data: 'vendor_name', "orderable": false, "searchable":false},
      {data: 'orderer_name', "orderable": false, "searchable":false},

      
       
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
       {
        render : function(data, type, row, meta) 
        {
         return row.rep_commission_amount;
        },          
        "orderable": false, "searchable":false
      },
      {
        render : function(data, type, row, meta) 
        {         
          //filter for rep/sales commission is pending
          if(row.rep_commission_status == 0 || row.sales_manager_commission_status!=1)
          {
            var rep_sales_commission_status = '<span class="label label-warning">Pending</span>';
          }

          //filter for rep/sales commission is paid
          if(row.rep_commission_status == 1 || row.sales_manager_commission_status==1)
          {
            var rep_sales_commission_status = '<span class="label label-success">Paid</span>';
          }

          //return status when order from rep or sales
          if(row.representative_id!=null||row.sales_manager_id!=null)
          {
            return rep_sales_commission_status;
          }
          
          //return status when order is not from sales and rep
          if(row.status == '-')
          {
            return '-';
          }
          else
          {
            return '<span class="label label-warning">Pending</span>';
          }
        },
        "orderable": false, "searchable":false
      },

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
          // $('#rep_commission_payable').html(formatter.format(total_rep_commission_pending));
          // $('#rep_commission_paid').html(formatter.format(total_rep_commission_paid));
          // $('#admin_earned_commission').html(formatter.format(total_rep_commission));

          $('#rep_commission_payable').html(total_rep_commission_pending);
          $('#rep_commission_paid').html(total_rep_commission_paid);
          $('#admin_earned_commission').html(total_rep_commission);


          
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

      $("#bulk_invoice_vendor").val("");
      $("#vendor_bulk_payment").val("");
      $("#representative_bulk_payment").val("");
      $("#sales_manager_bulk_payment").val("");

      $("input.checkItemAll").prop('checked',false);
      showProcessingOverlay();
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
    hideProcessingOverlay();
  }



  function fillData(orderPrice,vendorAmount,adminCommissionPer,adminCommissionAmount,makerId,orderId)
  {

    $('.vendor-Modal').modal('show');
    $('#order_amount').html(orderPrice.toFixed(2));    
    $('#actual_amount').html(adminCommissionAmount.toFixed(2));    
    $('#admin_commission_per').html(adminCommissionPer.toFixed(2));    
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
    
    $.ajax({
            url: '{{url('/admin')}}'+'/payment/vendor',
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

    var bulk_invoice_vendor = $("#bulk_invoice_vendor").val();
    if(bulk_invoice_vendor.length <= 0){     
        
         swal({
                 title:"Warning",
                 text: "To generate the bulk invoice, Please search records by vendor .",
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


/*-----------------------------------------------------------------------------*/

/*------------------ Start Bulk Payment Scripts :Mr.Yo ------------------------*/

function bulkPayment_validation(bulkPayFor)
{
  //'checkbox_bulk_vendor' checkbox_bulk_representative checkbox_bulk_sales_manager
  switch(bulkPayFor)
  {
    case "sales_manager": var user_type = "Sales Manager";
                                  break;
    default: var user_type = bulkPayFor;
                                  break;
  }

  var bulkPay_class = "checkbox_bulk_"+bulkPayFor;
  $(".err").stop().fadeOut();

    var selected_user_id = $("#"+bulkPayFor+"_bulk_payment").val();

    if(selected_user_id.length <= 0){
         
        
        swal({
                 title:" Warning",
                 text: "For "+user_type+" bulk payment, Please search records by selecting "+user_type+".",
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

    if($('.'+bulkPay_class+':checked').length <= 0)
    {
      swal({
                 title:" Warning",
                 text: " Please Select atleast 1 checkbox for "+user_type+" bulk payment.",
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
    $('.'+bulkPay_class+':checked').each(function(i, e) {

      
        if(bulkPayFor == "vendor")
        {
          console.log(this);
            var orderObj = {
              order_id : $(this).val(),
              orderNo : $(this).data("order_no"),
              orderPrice : $(this).data("totalprice"),
              adminCommission : $(this).data("admincommissionamount"),
              repcommission : {{$site_setting_arr['commission']}},
              repCommissionAmount : $(this).data("vendorpaybleamount"),
              user_id : $(this).data("maker_id"),
            }
        }
        else
        {
            var orderObj = {
              order_id : $(this).val(),
              orderNo : $(this).data("order_no"),
              orderPrice : $(this).data("amount_excluding_shipping_charge"),
              adminCommission : $(this).data("admincommissionamount"),
              repcommission : $(this).data("representative_commission"),
              repCommissionAmount : $(this).data("representative_pay_amount"),
              user_id : $(this).data(bulkPayFor),
            }

        }
       
        checkOrderItems.push(orderObj);
        
    });
            
  load_BulkPayment_model(checkOrderItems, selected_user_id, bulkPayFor)    

}

function load_BulkPayment_model(checkOrderItems, id, user_type)
{

    var csrf_token = "{{csrf_token()}}";
    var generated_url  = '{{url('/admin/commission_reports')}}'+'/load_bulkPaymentModelData';
    
    $.ajax({
               url: generated_url,
               type:"POST",
               dataType:'html',
               data:{'_token':csrf_token, 'checkOrderItems': checkOrderItems, 'user_id': id, 'user_type': user_type},
               beforeSend: function() 
               {

                $("#model_body").html('<div id="loader_model_body" style="color: #07892f; text-align:center;"> <i class="fa fa-refresh fa-spin fa-3x fa-fw" style="font-size: 25px;"></i> </div>');
                 $('#bulkPaymentModel').modal('show');
                               
               },
               success:function(response)
               {

                  setTimeout(function(){
                     $("#model_body").html(response); 
                   }, 1500);
                  
               }           
             });     
    return;
}
/*------------------ End Bulk Payment Scripts :Mr.Yo -------------------------*/

</script>

@stop 