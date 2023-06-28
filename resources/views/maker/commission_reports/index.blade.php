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
                    <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path) }}">
                        {{ csrf_field() }}

                        <div class="row commission_filter_row">
                            <div class="col-md-3 mb-4">
                                <label>Select Representative</label>
                                <select class="form-control dropdown" name="representative" id="rep_name">
                                    <option value=""> All Representatives </option>
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
                                <label>Select Retailer</label>
                                <select class="form-control dropdown" name="retailer" id="retailer">
                                   <option value=""> Select Retailer </option>
                                   @if(isset($arrRetailer) && count($arrRetailer) > 0)
                                        @foreach($arrRetailer as $retailerDetails)
                                            <option value="{{$retailerDetails['id']}}">{{$retailerDetails['first_name']}} {{$retailerDetails['last_name']}}</option>
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
                                        <option value="1">Pending</option>
                                        <option value="2">Paid</option> 
                                    </select>

                                </div>

                                <div class="col-md-6 mb-4">
                                  <div id="commission_status">
                                    <label>Rep Commission Payment Status</label>
                                      <select class="form-control dropdown" name="order_status">
                                          <option value="">Rep Commission Payment Status</option>
                                          <option value="1">Pending</option>
                                          <option value="2">Paid</option>
                                      </select>
                                  </div> 
                                    

                                </div>

                                <div class="col-sm-12 btnscenter mb-4">
                                  <button type="button" title="Search" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="Search" id="update">Search</button>
                                  <button type="submit" title="Export Commission Report as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="export" id="export">Export</button>
                                  
                                   <button type="reset" title="clear" name="reset" class="btn btn-defaut waves-effect waves-light filter_btn pull-right" value="reset" id="reset">Clear</button>
                                   
                                    
                                </div>
                        <div class="col-md-12 commission_report_view_sec">
                          <div class="row">
                            <div class="col-sm-4" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">Retailers' Payments (to Vendor):</label>
                              
                            <h5>
                              <!-- <span id="commission_text">Total Commissions Paid</span>: -->
                              <span id="commission_text">Total Payments Pending from Retailers'</span>:
                              <h3 id="retailer_receivable_amount">$0.00</h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <!-- <span id="order_amount_text">Total Order Amount Received</span>: -->
                              <span id="order_amount_text">Total Payments Received from Retailers'</span>:
                              <h3 id="retailer_received_amount">$0.00</h3>
                            </h5>
                            <h5>
                                <span>Total Retailers' Payments</span>:
                                <h3>$0.00</h3>
                              </h5>
                            </div>
                          </div>
                           <div class="col-sm-4" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">{{$site_setting_arr['site_name'] or ''}} Commissions Receipts:</label>

                            <h5>
                              <span id="commission_text">Total Commissions Pending from Vendors'</span>:
                              <h3 id="vendor_payable_amount">$0.00</h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total Commissions Received from Vendors'</span>:
                              <h3 id="vendor_paid_amount">$0.00</h3>
                            </h5>
                              <h5>
                                <span>Total {{$site_setting_arr['site_name'] or ''}} Commissions Earned</span>:
                                <h3>$0.00</h3>
                              </h5>

                            </div>
                          </div>
                          <div class="col-sm-4">
                            <div class="box">
                              <label class="font-larg-ventr">Rep Commissions Payments:</label>                            
                            <h5>
                              <!-- <span id="commission_text">Total Commissions Payable</span>: -->
                              <span id="rep_commission_text">Total Rep Commissions Pending</span>:
                              <h3 id="rep_commission_payable">$0.00</h3>
                            </h5>

                             <h5 id="order_amount_calculation">
                              <!-- <span id="order_amount_text">Total Order Amount Dues</span>: -->
                              <span id="order_amount_text">Total Rep Commissions Paid</span>:
                              <h3 id="rep_commission_paid">$0.00</h3>
                            </h5>
                            <h5 id="order_amount_calculation">
                              <span id="order_amount_text">Total Rep Commissions Earned</span>:
                              <h3 id="admin_earned_commission">$0.00</h3>
                            </h5>
                            
                            </div>
                          </div>
                         
                          
                          
                        </div>
                        </div>
                        
                        
                        <div class="table-responsive">
                            <input type="hidden" name="multi_action" value="" />
                            <table id="table_module" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Order No.</th>
                                        <th>Order Date</th>
                                        <!-- <th>Commission<br> Amount</th> -->
                                        <th>Rep / Sales<br> Manager</th>
                                        <th>Vendor</th>
                                        <th>Retailer</th>
                                        <th>Retailer Payment<br> Status (to Vendor)</th>                             
                                        <!-- <th>Commission<br> Payment Status<br>(Rep/Sales)</th> -->
                                        <th>{{$site_setting_arr['site_name'] or ''}} Commission<br>Amount</th>                             
                                        <th>{{$site_setting_arr['site_name'] or ''}} Commission<br>Receipt Status</th>                             
                                        <th>Rep Commission<br>Amount</th>                             
                                        <th>Rep Commission<br>Payment Status</th>  
                                        <th>Order Amount</th>                           
                                        <!-- <th>Action</th> -->
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
        var vendorPaymentStatus           = $("select[name='vendor_payment_status']").val();
               
        if($('#frm_manage').parsley().validate()==false) return;

        if (rep_val == "" && retailer == "" && sales_manager == "") {

            swal({title: "Warning", text: "Please select any one stakeholder from Representative, Sales Manager or Retailer", type: 'warning'},

                function(){ 
                }
            );
            return false;
        }

        filterData();
    });

     $('.datepicker').datepicker({
          // format: 'yyyy-mm-dd',
          format: 'mm-dd-yyyy',
          viewMode: "days", 
          minViewMode: "days"
       });
      

      table_module = $('#table_module').DataTable({ 
/*      processing: true,
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
            //--------------------------------------filters-----------------------------------------
            d['column_filter[to_date]']        = $("input[name='to_date']").val()
            d['column_filter[from_date]']      = $("input[name='from_date']").val()
            d['column_filter[order_status]']        = $("select[name='order_status']").val()
            d['column_filter[representative]']        = $("select[name='representative']").val()
            d['column_filter[retailer]']        = $("select[name='retailer']").val()
            d['column_filter[vendor]']        = $("select[name='vendor']").val()
            d['column_filter[sales_manager]']        = $("select[name='sales_manager']").val()
            d['column_filter[order_payment_status]']        = $("select[name='order_payment_status']").val()
            d['column_filter[vendor_payment_status]']        = $("select[name='vendor_payment_status']").val()         
        }
      },

      columns: [
      
      {data: 'order_no', "orderable": true, "searchable":false}, 
      {data: 'created_at', "orderable": true, "searchable":false}, 
      {
        render : function(data, type, row, meta) 
        {
         
          return '$'+row.amount;
        },          
        "orderable": false, "searchable":false
      },
      {data: 'rep_name', "orderable": true, "searchable":false},         
      {data: 'vendor_name', "orderable": true, "searchable":false},
      {data: 'retailer_name', "orderable": true, "searchable":false},
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

          }
          else{
            var rep_commission_pending      = '0.00';
            var rep_commission_paid         = '0.00';
            var order_amount_paid           = '0.00';
            var order_amount_pending        = '0.00';
            var vendor_commission_pending   = '0.00';
            var vendor_commission_paid      = '0.00';
            var admin_commission            = '0.00';

          }
          $('#rep_commission_payable').html(rep_commission_pending);
          $('#rep_commission_paid').html(rep_commission_paid);

          $('#admin_earned_commission').html(admin_commission);

          $('#retailer_receivable_amount').html(order_amount_pending);
          $('#retailer_received_amount').html(order_amount_paid);

          $('#vendor_payable_amount').html(vendor_commission_pending);
          $('#vendor_paid_amount').html(vendor_commission_paid);
          
          return '$'+row.total_wholesale_price;
        },
          
        "orderable": false, "searchable":false
      },
      {
        render : function(data, type, row, meta) 
        { 

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

          if(row.rep_commission_status == 1)
          {
            
            return '<span class="label label-success">Paid</span>';
          }
          if(row.rep_commission_status == 0)
          {
            
            return '<span class="label label-warning">Pending</span>';
          }
          
          if(row.status == '-'){
            
            return '-';

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
          if(row.maker_commission_status == 1)
          {

            return '<span class="label label-success">paid</span>';
          }
          //---------------For retailer orders-----------
          if(row.maker_commission_status == 2)
          {

            return '<span class="label label-success">paid</span>';
          }
          else{

            return '<span class="label label-warning">Pending</span>';
          }
        },
        "orderable": false, "searchable":false
      },

      ],*/

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
    /*search box*/
     /*$("#table_module").find("thead").append(`<tr>
                    <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td><input type="text" name="q_order_date" placeholder="select" class="datepicker search-block-new-table column_filter form-control-small" onchange="filterData();" /></td>
                    <td></td>
                    

                    <td><input type="text" name="q_rep_name" placeholder="Search" class="search-block-new-table column_filter form-control-small search_rep" /></td>

                    <td><input type="text" name="q_vendor_name" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>

                     <td><input type="text" name="q_retaier_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                      <td><input type="text" name="q_order_amount" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_payment_status" id="q_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Pending</option>
                        <option value="2">Paid</option>
                        
                        </select>
                    </td>   
                    
                   
                    <td></td>
                    <td></td>

                </tr>`);*/

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
/*-----------------------------------------------------------------------------*/
</script>

@stop 