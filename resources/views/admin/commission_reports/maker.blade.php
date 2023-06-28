
@extends('admin.layout.master')                

@section('main_content')

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
         <!-- /.col-lg-12 -->
      </div>
      <div class="row">
         <div class="col-sm-12">
            @include('admin.layout._operation_status')
            <div class="white-box">
               <div class="pull-right top_small_icon">
             
                <!-- <a  href="{{$module_url_path}}/report_generator/xlsx" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a xlsx "><i class="fa fa-file-excel-o"></i></a>   -->

                  <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
               </div>
               <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/all_reports/representative') }}">
                  {{ csrf_field() }}
                 <!--  <div class="col-md-12">
                     <div class="col-md-4">
                        <select class="form-control" name="representative">
                           <option> Select Representative </option>
                           @if(isset($arrRepresentative) && count($arrRepresentative) > 0)
                           @foreach($arrRepresentative as $representativeDetails)
                           <option value="{{$representativeDetails['id']}}">{{$representativeDetails['first_name']}} {{$representativeDetails['last_name']}}</option>
                           @endforeach
                           @endif
                        </select>
                     </div>
                     <div class="col-md-4">
                        <select class="form-control" name="retailer">
                           <option> Select Retailer </option>
                           @if(isset($arrRetailer) && count($arrRetailer) > 0)
                           @foreach($arrRetailer as $retailerDetails)
                           <option value="{{$retailerDetails['id']}}">{{$retailerDetails['first_name']}} {{$retailerDetails['last_name']}}</option>
                           @endforeach
                           @endif
                        </select>
                     </div>
                     <div class="col-md-4">
                        <select class="form-control" name="vendor">
                           <option> Select Vendor</option>
                           @if(isset($arrMakers) && count($arrMakers) > 0)
                           @foreach($arrMakers as $vendorDetails)
                           <option value="{{$vendorDetails['id']}}">{{$vendorDetails['first_name']}} {{$vendorDetails['last_name']}}</option>
                           @endforeach
                           @endif
                        </select>
                     </div>
                  </div>
                  <br>
                  <br>
                  <br> -->
                  <div class="col-md-12">
                     <div class="col-md-4"> 
                        <input type="text" name="from_date" data-parsley-required="true" class="datepicker form-control" data-date-format="mm/dd/yyyy" id="from_date" placeholder="From Date" readonly>
                     </div>
                     <div class="col-md-4"> 
                        <input type="text" name="to_date" data-parsley-required="true" class="datepicker form-control" data-date-format="mm/dd/yyyy" id="to_date" placeholder="To Date" readonly>
                     </div>
                     <div class="col-md-2">
                        <button type="button" style="float: right" class="btn btn-success waves-effect waves-light m-r-10" value="Search" title="Search" id="update">Search</button>
                     </div>
                    <!--  <div class="col-md-4">
                        <select class="form-control" name="order_status">
                           <option> Select Order status </option>
                           <option>Pending</option>
                           <option>Cancelled</option>
                        </select>
                     </div> -->
                      
                  </div>
                  <br>
                  <br>
                  <br>
                

                  <div class="table-responsive">
                     <table id="table_module" class="table table-striped">
                        <thead>
                           <tr>
                              <th> Order No.</th>
                              <th>Commission Amount</th>
                              <th>Order Amount</th>
                              <th>Vendor</th>
                              <th>Representative</th>
                              <th>Sales Manager </th>
                              <th>Retailer</th>
                              <th>Payment Status</th>                             
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

<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";

  var table_module = false;

  $(document).ready(function()
  {
     $('#update').click(function(){
        if($('#frm_manage').parsley().validate()==false) return;

       filterData();
    });


     $('.datepicker').datepicker({
          format: 'yyyy-mm-dd',
          startDate: '-1d',
          viewMode: "days", 
          minViewMode: "days"
       });

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
      'url':'{{ $module_url_path.'/all_reports/maker'}}',
      'data': function(d)
        {
          d['column_filter[q_order_no]']        = $("input[name='q_order_no']").val()
          d['column_filter[q_commission_amount]'] = $("input[name='q_commission_amount']").val()
          d['column_filter[q_rep_name]']        = $("input[name='q_rep_name']").val()
          d['column_filter[q_vendor_name]']     = $("input[name='q_vendor_name']").val()
          d['column_filter[q_sales_name]']      = $("input[name='q_sales_name']").val()
          d['column_filter[q_retaier_name]']    = $("input[name='q_retaier_name']").val()
          d['column_filter[q_order_amount]']    = $("input[name='q_order_amount']").val()          
          d['column_filter[q_payment_status]']  = $("select[name='q_payment_status']").val()  
          d['column_filter[q_to_date]']         = $("input[name='to_date']").val()          
          d['column_filter[q_from_date]']       = $("input[name='from_date']").val()        
        }
      },

      columns: [
      
      {data: 'order_no', "orderable": true, "searchable":false},      
      {data: 'amount', "orderable": true, "searchable":false},
      {data: 'total_wholesale_price', "orderable": true, "searchable":false}, 
      {data: 'vendor_name', "orderable": true, "searchable":false},
      {data: 'rep_name', "orderable": true, "searchable":false},
      // {data: 'sales_manager_name', "orderable": true, "searchable":false},
       {
        render : function(data, type, row, meta) 
        {
          return row.sales_manager_name;
        },
        "orderable": false, "searchable":false
      },
      {data: 'retailer_name', "orderable": true, "searchable":false},      
      {
        render : function(data, type, row, meta) 
        {
          // return '<label>'+row.status+'</label>';
           if(row.status == 2)
          {
            return '<span class="label label-success">Paid</span>';
          }
          if(row.status == 3)
          {
            return '<span class="label label-warning">Failed</span>';
          }
          if(row.status == 1)
          {
            return '<span class="label label-default">Pending</span>';
          }
        },
        "orderable": false, "searchable":false
      },

    /*  {data: 'status', "orderable": true, "searchable":false},      
      {data: 'rep_commission_status', "orderable": true, "searchable":false},      
      {data: 'maker_commission_status', "orderable": true, "searchable":false},   */   
      {
        render : function(data, type, row, meta) 
        {
         return '<a href="{{$module_url_path}}/report_details/'+btoa(row.id)+'" class="btn btn-circle btn-success btn-outline show-tooltip" title="View"> View </a>';
        },
        "orderable": false, "searchable":false
      },


  
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

       $("input.net_30").change(function(){
          net_30_statusChange($(this));
       });  

       toggleSelect();

    });



    /*search box*/
     $("#table_module").find("thead").append(`<tr>
                    <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_commission_amount" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_order_amount" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_vendor_name" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                    <td><input type="text" name="q_rep_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_sales_name" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                     <td><input type="text" name="q_retaier_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_payment_status" id="q_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="2">Paid</option>
                        <option value="3">Failed</option>
                        </select>
                    </td>   
                    
                   
                    <td></td>
                    <td></td>

                </tr>`);

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