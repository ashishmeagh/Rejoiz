@extends('admin.layout.master')                
@section('main_content')
<style>
  .btn {
    background: none;
    color: #666;
    border: 1px solid #666;
    padding: 6px 11px;
    font-size: 14px;
}
</style>
<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">


<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title">{{$module_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">
            <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
   </div>
   <!-- BEGIN Main Content -->
   <div class="row">
   <div class="col-sm-12">
      <div class="white-box">
         @include('admin.layout._operation_status')
         {!! Form::open([ 'url' => $module_url_path.'/multi_action',
         'method'=>'POST',
         'enctype' =>'multipart/form-data',   
         'class'=>'form-horizontal', 
         'id'=>'frm_manage' 
         ]) !!} 
         {{ csrf_field() }}
         <div class="pull-right top_small_icon">
            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>
         </div>
         <div class="table-responsive">
            <table class="table table-striped"  id="table_module" >
               <thead>
                  <tr>
                     <th>Sr.No</th>
                     <th>Email</th>
                     <th>Date</th>
                   
                  </tr>
               </thead>
               <tbody>

                  @if(sizeof($arr_data)>0)
                      @foreach($arr_data as $subscription)
                        <tr>
                          <td></td>
                           <td> {{ isset($subscription['email'])?$subscription['email']:''}}  </td>

                           <td> {{ isset($subscription['created_at'])?us_date_format($subscription['created_at']):''}}
                           </td>
                           
                        </tr>
                      @endforeach
                  @endif

               </tbody>
            </table>
         </div>
         <div>   
         </div>
         {!! Form::close() !!}
      </div>
   </div>
 </div>
</div>
<!-- END Main Content -->
<script type="text/javascript">
  
   /*$(document).ready(function() {
         $('#table_module').DataTable( {
             "aoColumns": [
             { "bSortable": true },
             { "bSortable": true }
             
             ]
         });

          
     });*/


$(document).ready(function() {
    var t = $('#table_module').DataTable( {
        "columnDefs": [ {
            "searchable": false,
            "orderable": false,
            "targets": 0
        } ]
    } );
 
    t.on( 'order.dt search.dt', function () {
        t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
            cell.innerHTML = i+1;
        } );
    } ).draw();
} );
   
</script>
@stop