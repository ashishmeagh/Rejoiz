@extends('admin.layout.master')                
@section('main_content')

<style type="text/css">
  @media (max-width:575px) {
    .dataTables_filter {margin-top:10px;}
  }
  .banner-content-img{max-width: 320px;}
  .banner-content-img img{width: 100%;}

  .dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
  }

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}
.white-box{
      padding: 15px 25px 25px;
}
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
<!-- responsive datatable css -->
{{-- <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/responsive.dataTables.min.css"> --}}


<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title">{{$module_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">
            <li><a href="{{url('/admin')}}/dashboard">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
   </div>
         @include('admin.layout._operation_status')
   <!-- BEGIN Main Content -->
   <div class="row">
   <div class="col-sm-12">
      <div class="white-box">
         {{--  {!! Form::open([ 'url' => $module_url_path.'/multi_action',
                                 'method'=>'POST',
                                 'enctype' =>'multipart/form-data',   
                                 'class'=>'form-horizontal', 
                                 'id'=>'frm_manage' 
                                ]) !!}  --}}

            {{ csrf_field() }}

           <div class="pull-right top_small_icon">
            <a href="{{ url('/admin').'/banner_images/create' }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add Banner Image"><i class="fa fa-plus"></i> </a> 
              {{--
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-circle btn-danger btn-outline show-tooltip" title="Multiple Block"><i class="ti-lock"></i> </a> 

            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-circle btn-danger btn-outline show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a> 

            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
             --}}

          </div>
          <div class="table-responsive">
            <input type="hidden" name="multi_action" value="" />
            
            <table class="table table-striped"  id="table1">
              <thead>
                <tr>
<!--                   <th>Sr.No</th>
 -->                  <th>Image</th>                   
                  <th>Type</th> 
                  <th width="200px">Action</th>
                </tr>
                </thead>
                <tbody>
                  @foreach($banner_arr as $key => $banner)
                  <tr>
<!--                     <td>{{$key+1}}</td>
 -->                    <td width="30%">
                      <div class="banner-content-img">
                        @php
                          if(isset($banner['banner_image']) && $banner['banner_image']!='' && file_exists(base_path().'/storage/app/'.$banner['banner_image']))
                          {
                            $banner_img = url('/storage/app/'.$banner['banner_image']);
                            $img_width = 100;
                          }
                          else
                          {                
                            $banner_img = url('/assets/images/no-product-img-found.jpg');
                            $img_width = 35;
                          }
                        
                        @endphp
                        
                      <img style="width: {{$img_width}}%" src="{{$banner_img}}">
                    </div>
                    </td>
                    <td >{{ $banner['type'] or ''}}</td>
                    <td>
                      <a href="{{$module_url_path.'/edit/'.base64_encode($banner['id'])}}" data-toggle="tooltip"  data-size="small" title="Edit" class="btn btn-circle btn-success btn-outline show-tooltip">Edit</a>

                      <a href="{{$module_url_path.'/delete/'.base64_encode($banner['id'])}}" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-circle btn-danger btn-outline show-tooltip" onclick="confirm_delete(this,event);">Delete</a>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
                  
  
               
             </table>
          </div>
        <div> </div>
         
          {!! Form::close() !!}
      </div>
  </div>
</div>
</div>
<script type="text/javascript">
   $(document).ready(function() {
       $("#table1").DataTable();
     });

  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this banner image.');
  }
</script>
@stop                    


