@extends('admin.layout.master')                
@section('main_content')
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
         <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
         <li class="active">{{$module_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<div class="row">
   <div class="col-sm-12">
      <div class="white-box">
         @include('admin.layout._operation_status')
         <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
            {{ csrf_field() }}
            <div class="pull-right top_small_icon">
               <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            </div>
            <br/>
            <br/>
            <div class="table-responsive" style="border:0">
               <input type="hidden" name="multi_action" value="" />
               <table class="table table-striped" id="table_module">
                  <thead>
                     <tr>
                        <th>
                           <a class="sort-desc sort-asc"> Date </a>
                           <input id="date" name="q_date" onchange="filterData();"  Placeholder="Select Date" value="" class="search-block-new-table column_filter form-control datepicker" size="10" type="text" >
                        </th>
                        <th>
                           <a class="sort-desc sort-asc"> Module Name </a>
                           <input type="text" name="q_module_name" id="q_module_name" placeholder="Search" class="search-block-new-table column_filter form-control" size="10" />
                        </th>
                        <th>
                           <a class="sort-desc sort-asc"> User Name </a>
                           <input type="text" name="q_user_name" placeholder="Search" class="search-block-new-table column_filter form-control" size="10" />
                        </th>
                        <th>
                           <a class="sort-desc sort-asc"> Action Performed </a>
                           <select name="q_action" id="q_action" onchange="filterData();" class="form-control column_filter form-control" >
                              <option value="">Select Action Performed
                              </option>
                              <option value="ADD">Add
                              </option>
                              <option value="EDIT">Edit
                              </option>
                              <option value="REMOVED">Removed
                              </option>
                           </select>
                        </th>
                     </tr>
                  </thead>
               </table>
            </div>
         </form>
      </div>
   </div>
</div>
</div>
</div>
<!-- END Main Content -->


<script type="text/javascript">
   function show_details(url)
   {
     window.location.href = url;
   }
   
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
       bFilter: false ,
       ajax: {
       'url':'{{ $module_url_path.'/get_records'}}',
       'data': function(d)
         {
           d['column_filter[q_date]']        = $("input[name='q_date']").val()
           d['column_filter[q_module_name]'] = $("input[name='q_module_name']").val()
           d['column_filter[q_action]']      = $("select[name='q_action']").val()
           d['column_filter[q_user_name]']   = $("input[name='q_user_name']").val()
           
         }
       },
       columns: [
       {data: 'date', "orderable": true, "searchable":false},
       {data: 'module_name', "orderable": true, "searchable":false},
       {data: 'user_name', "orderable": true, "searchable":false},
       {data: 'action', "orderable": false, "searchable":false},
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
     });
   });
   
   function filterData()
   {
     table_module.draw();
   }

   $('#date').datepicker({
    dateFormat: "yy-mm-dd",
   });

   $('.datepicker').datepicker({
        autoclose: true,
        todayHighlight: true,
        format: 'd-M-YYYY'
  });
   
</script>

@stop