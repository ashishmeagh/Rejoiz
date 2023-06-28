@extends('sales_manager.layout.master')
@section('main_content')

<style>
.dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
}

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit !important;
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
            <li><a href="{{url('/')}}/sales_manager/dashboard">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
   </div>

   <!-- BEGIN Main Content -->
   <div class="row">
   <div class="col-sm-12">
      <div class="white-box">
         @include('sales_manager.layout._operation_status')
         {!! Form::open([ 'url' => $module_url_path.'/multi_action',
         'method'=>'POST',
         'enctype' =>'multipart/form-data',
         'class'=>'form-horizontal',
         'id'=>'frm_manage'
         ]) !!}
         {{ csrf_field() }}
         <div class="col-sm-12 pull-right text-right top_small_icon">
            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-circle btn-success btn-outline show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>
            <a href="javascript:void(0)" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','mark_as_read');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Mark as read"><i class="fa fa-bell"></i> </a>
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>
            <a  href="{{$module_url_path}}/export"  class="btn btn-outline btn-success btn-circle show-tooltip" title="Export as a xlsx"><i class="fa fa-file-excel-o"></i></a>
         </div>
         <br>
         <br>
         <div class="col-sm-12 table-responsive">
         <input type="hidden" name="multi_action" value="" />
            <table class="table table-striped"  id="table_module" >
               <thead>
                  <tr>
                      <th>
                           <div class="checkbox checkbox-success">
                              <input class="checkItemAll" value="delete" id="checkbox0" type="checkbox">
                              <label for="checkbox0">  </label>
                           </div>
                        </th>

                     <th width="20%">Date</th>
                     <th width="30%">Title</th>
                     <th width="60%">Description</th>
                     <th width="10%">Action</th>

                  </tr>
               </thead>
               <tbody>
                  @if(sizeof($arr_data)>0)
                  @foreach($arr_data as $notification)
                  <tr>
                     <td>
                          <div class="checkbox checkbox-success"><input type="checkbox" name="checked_record[]" value="{{ base64_encode($notification['id']) }}" id="checkbox'{{$notification['id']}}'" class="case checkItem"/><label for="checkbox'{{$notification['id']}}'"> </label></div>
                        </td>
                        <td>{{isset($notification['created_at'])?notification_format_date($notification['created_at']):''}}</td>
                     <td> {{ $notification['title']  or ''}}  </td>

                      @if(isset($notification['notification_url']) && $notification['notification_url']!='')

                       <td> <!-- <a class="linkankrtg" target="_blank" href="{{$notification['notification_url']}}">{!! $notification['description'] or ''!!} </a> -->

                        @if(isset($notification['is_read']) && $notification['is_read'] == 0)

                          <a class="linkankrtg" href="javascript:void(0);" data-id="{{$notification['id']}}" onclick="readNotification(this);">{!! $notification['description'] or ''!!} </a>

                        @else

                          <a class="" href="{{$notification['notification_url']}}" >{!! $notification['description'] or ''!!} </a>

                        @endif
                        </td>

                      @else
                       <td>{!! $notification['description'] or ''!!}</td>

                      @endif
                      <td>
                         <a href="{{$module_url_path}}/delete/{{base64_encode($notification['id'])}}" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-info btn-circle show-tooltip deletestyle" onclick="confirm_delete(this,event);">Delete</a>

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

   var module_url_path  = "{{ $module_url_path or '' }}";

   $(document).ready(function() {
         $('#table_module').DataTable( );
     });

  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this notification.');
  }

  $('input.checkItem').click(function(){
  toggleSelect();
  });

  $(function(){

   $("input.checkItemAll").click(function(){

    if($("input.checkItemAll").prop('checked')==true)
    {
      $("input.checkItem").prop('checked',true);
    }
    else
    {
      $("input.checkItem").prop('checked',false);
    }

     });

   });

  function readNotification(ref)
  {
      var notification_id = $(ref).attr('data-id');
      var notification_id = btoa(notification_id);

      $.ajax({
          url:module_url_path+'/read_notification/'+notification_id,
          type:"GET",
          dataType:'json',
          success:function(response){
             window.location.href= response.url;

          },
          error:function(response){
              swal('error',response.description,'Error');
          }


     });
  }

</script>
@stop