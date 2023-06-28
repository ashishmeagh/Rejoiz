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
               {{--  {{$page_title or ''}} --}}
                <h4 class="page-title">{{$module_title or ''}}</h4>
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
                    <form class="form-horizontal" id="frm_manage" method="POST" action="{{$exprot_csv_url}}">
                        {{ csrf_field() }}

                        <div class="row commission_filter_row">
                            {{-- <div class="col-md-3 mb-4">
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
                            </div> --}}
                            <div class="col-md-3 mb-4"> 
                                    <label>Order No</label>
                                   <input type="text" name="q_order_no" placeholder="Order No" class=" form-control" id = "q_order_no"/>
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
                            
                           {{--  <div class="col-md-3 mb-4">
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
                             --}}

                            <div class="col-md-3 mb-4"> 
                                <label>From Date</label>
                                    <input type="text" name="from_date" class="datepicker form-control" data-date-format="mm/dd/yyyy" id="from_date" placeholder="From Date" readonly>
                                </div>
                                <div class="col-md-3 mb-4"> 
                                    <label>To Date</label>
                                    <input type="text" name="to_date" class="datepicker form-control" data-date-format="mm/dd/yyyy" id="to_date" placeholder="To Date" readonly>
                                </div>

                                <div class="col-md-3 mb-4"> 
                                    <label>Commission Receipt Status</label>
                                    <select class="form-control dropdown" name="vendor_payment_status" id = "vendor_payment_status">
                                        <option value="">Commission Receipt Status</option>
                                        <option value="1">Pending</option>
                                        <option value="2">Paid</option> 
                                        <option value="3">Failed</option> 
                                    </select>
                                </div>
                                <div class="col-md-3 mb-4"> 
                                    <label>Ordered By</label>
                                    <select class="form-control dropdown" name="ordered_by" id = "ordered_by">
                                        <option value="">Ordered By</option>
                                        <option value="4">Retailer</option>
                                        <option value="6">Customer</option> 
                                    </select>
                                </div>
                               
                                </div>
                                

                                {{-- <div class="col-md-6 mb-4">
                                  <div id="commission_status">
                                    <label>Rep Commission Payment Status</label>
                                      <select class="form-control dropdown" name="order_status">
                                          <option value="">Rep Commission Payment Status</option>
                                          <option value="1">Pending</option>
                                          <option value="2">Paid</option>
                                      </select>
                                  </div> 
                                 </div> --}}

                                <div class="col-sm-12 btnscenter">
                                  <button type="button" title="Search" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="Search" id="update">Search</button>
                                  
                                
                                  
                                   <button type="reset" title="clear" name="reset" class="btn btn-defaut waves-effect waves-light filter_btn pull-right" value="reset" id="reset">Clear</button>
                                   

                                  <button type="submit" title="Export Commission Report as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right mr-2" value="export" id="export">Export</button>
                                    
                                </div>
                                  

                        <br><br>
                        <div class="col-md-12 commission_report_view_sec">
                          <div class="row">
                            <div class="col-sm-12 col-md-4 col-lg-4" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">Total commission yet to be paid to Vendor:</label>
                              
                            <h5>
                              <!-- <span id="commission_text">Total Commissions Paid</span>: -->
                              {{-- <span id="commission_text">Total Payments Pending from Retailers'</span>: --}}
                              <h3 id="total_commission_pending">$0.00</h3>
                            </h5>

                             
                            </div>
                          </div>
                           <div class="col-sm-12 col-md-4 col-lg-4" id="first_load">
                            <div class="box">
                              <label class="font-larg-ventr">Total commission paid to vendor:</label>

                            <h5>
                              
                              <h3 id="vendor_payable_amount">$0.00</h3>
                            </h5>

                             

                            </div>
                          </div>
                          <div class="col-sm-12 col-md-4 col-lg-4">
                            <div class="box">
                              <label class="font-larg-ventr">Total Admin Commission Earned:</label>                            
                            <h5>
                              <!-- <span id="commission_text">Total Commissions Payable</span>: -->
                              <h3 id="total_admin_commission">$0.00</h3>
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
                                        <th>Ordered By </th>       
                                        <th>Vendor</th>
                                        <th>Total Order Amount</th>
                                        <th>Total Order Amount<br>(Excluding Shipping Charge)</th>
                                        <th>Amount Paid To Vendor</th>
                                        <th>Admin Commission Amount</th>
                                        <th>Status</th>                             
                                        
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
    show_list();
  });

  $("#update").click(function(){

    show_list();
  })

  function filterData()
  {
    table_module.draw();
  }

  function show_list()
  {
      var admin_commission_amt =[];
      var total_commission_pending =[];
      var vendor_payable_amount =[];
      $.fn.dataTable.ext.errMode = 'none'; 
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      bFilter: false,
      stateSave: true,
      "bDestroy": true,
      "order":[4,'Asc'],

      ajax: {
      'url': module_url_path+'/vendor_commission_reports',
      'data': function(d)
      {
        d['column_filter[q_vendor_name]']   = $("#maker").val()
        d['column_filter[q_status]']        = $("#vendor_payment_status").val()
        d['column_filter[q_from_date]']     = $("#from_date").val()
        d['column_filter[q_to_date]']       = $("#to_date").val()
        d['column_filter[q_order_no]']      = $("#q_order_no").val()
        d['column_filter[q_ordered_by]']    = $("#ordered_by").val()
        
      }
      },

      columns: [
      
      {
        render(data, type, row, meta)
        {   
            return row.lead_id;
        },
        "orderable": false, "searchable":false
      },  

      {data: 'created_at', "orderable": false, "searchable":false},
      {data: 'ordered_by',"orderable": false, "searchable":false},
      {data: 'received_by', "orderable": false, "searchable":false},

      {data: 'order_amount',orderable: false, searchable: false,responsivePriority:4,
       render(data, type, row, meta)
       {
        return `<i class="fa fa-usd" aria-hidden="true"></i>`+row.order_amount;             
        }
      },    

       {data: 'shipping_charge',orderable: false, searchable: false,responsivePriority:4,
       render(data, type, row, meta)
       {
        return `<i class="fa fa-usd" aria-hidden="true"></i>`+row.shipping_charge;             
        }
      },   
 
      {data: 'amount',orderable: false, searchable: false,responsivePriority:4,
       render(data, type, row, meta)
       {
          return `<i class="fa fa-usd" aria-hidden="true"></i>`+row.amount;             
        }
      },      


       

      {data: 'commission_amount',orderable: false, searchable: false,responsivePriority:4,
         render(data, type, row, meta)
         {  
             if(row.status!=1)
             {
                admin_commission_amt.push(row.commission_amount);
                vendor_payable_amount.push(row.amount);
             }

             if(row.status==1)
             {
                total_commission_pending.push(row.amount);
             }
              $("#total_commission_pending").text('$'+sum(total_commission_pending).toFixed(2));
              
              $("#vendor_payable_amount").text('$'+sum(vendor_payable_amount).toFixed(2));

              $("#total_admin_commission").text('$'+sum(admin_commission_amt).toFixed(2));
              
              return `<i class="fa fa-usd" aria-hidden="true"></i>`+row.commission_amount;             
              
          }
      },  

      /*<a class="btn btn-circle btn-success btn-outline show-tooltip" href="`+generate_invoice_url+`" title="Generate Invoice">Generate Invoice</a>`*/
     
      {data: 'status',orderable: false, searchable: false,responsivePriority:4,
         render(data, type, row, meta)
         {    console.log(data)
              if(row.status == 1)
              {   
                  console.log(row);
                  var generate_invoice_url  = "{{ $generate_invoice_url or '' }}";
                  generate_invoice_url =  generate_invoice_url+'/'+btoa(row.order_id);

                return `<b><span class="text-warning">Pending By Admin</span></b>`;
                                     
              }
              else if(row.status==2)
              {
                return `<b><span class="text-success">Paid</span></b>`;
              }
              else
              {
                return `<b><span class="text-danger">Failed</span></b>`;
              }
          }
      },    
      {data: 'sum_total_commission_pending',orderable: false, searchable: false,responsivePriority:4,"visible": false,
       render(data, type, row, meta)
       {

            $("#total_commission_pending").text('$'+row.sum_total_commission_pending.toFixed(2));
              
                        
        }
      },   

      {data: 'sum_vendor_payable_amount',orderable: false, searchable: false,responsivePriority:4,"visible": false,
       render(data, type, row, meta)
       {

              $("#sum_vendor_payable_amount").text('$'+row.sum_vendor_payable_amount.toFixed(2));
              
          
        }
      },  

      {data: 'sum_admin_commission_amt',orderable: false, searchable: false,responsivePriority:4,"visible": false,
       render(data, type, row, meta)
       {

              $("#sum_admin_commission_amt").text('$'+row.sum_admin_commission_amt.toFixed(2));
                        
        }
      },                    
      
      
      ]

    });


  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });

  $("#table_module").on('page.dt', function (){

    var info = table_module.page.info();
   
    $("input.checkItemAll").prop('checked',false);

  });

$("#table_module").on("draw.dt", function (event) {
  var oTable = $("#table_module").dataTable();

  var recordLength = oTable.fnGetData().length;
  $("#record_count").html(recordLength);

  if (recordLength == 0) {
    $("#total_commission_pending").text("$0.00");

    $("#vendor_payable_amount").text("$0.00");

    $("#total_admin_commission").text("$0.00");
  }

  var elems = Array.prototype.slice.call(
    document.querySelectorAll(".js-switch")
  );
  $(".js-switch").each(function () {
    new Switchery($(this)[0], $(this).data());
  });

  toggleSelect();
});
}

function generate_invoice(order_no = false)
{
  console.log(order_no);
  return;
}



function sum(input)
{
  if (toString.call(input) !== "[object Array]")
  return false;
      
  var total =  0;
    for(var i=0;i<input.length;i++)
    {                  
      if(isNaN(input[i])){
      continue;
      }
      total += Number(input[i]);
    }
  
  return total;
}

</script>

@endsection