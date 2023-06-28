@extends('maker.layout.master')                
@section('main_content')


<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Page Details</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         
         <li><a href="{{$back_url or ''}}">All Pages</a></li>
         <li class="active">Page Details</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

<!-- .row -->
<div class="row">
   @include('admin.layout._operation_status')
   <div class="col-sm-12">
      <div class="white-box">
         <label>
            <h4><b>Catalog Name:</b> {{$arrCatlogImages['get_catalog_data']['catalog_name'] or ''}}</h4>
            <h5><b>Page No.:</b> {{$arrCatlogImages['sequence'] or ''}}</h5>
         </label>
          <div class="row">

         @if(isset($arrCatlogImages['get_catalog_image_data']) && sizeof($arrCatlogImages['get_catalog_image_data'])>0) 
         @foreach($arrCatlogImages['get_catalog_image_data'] as $images)

            @if($arrCatlogImages['page_type']== 'product_images')

             <div class="col-sm-3 text-center view_box">
                 <div class="inner_view_box">
                     <div class="imgbx">
                        @if(isset($images['image']) && $images['image']!="" && file_exists(base_path().'/storage/app/'.$images['image'])) 
                           <img class="zoom-img" height="100px" width="100px" src="{{ url('/storage/app/'.$images['image'])}}"> 
                        @else
                           <img height="100px" width="100px" src="{{ $default_image or '' }}">
                        @endif    
                     </div>
                     <div class="contentbx">
                         <h5>{{isset($images['sku'])?$images['sku']:"N/A"}}</h5>
                     </div>
                 </div>
             </div>

            @else 

              <div class="col-sm-3 text-center view_box img_center">
                 <div class="inner_view_box">
                     <div class="imgbx center">
                        @if(isset($images['image']) && $images['image']!="" && file_exists(base_path().'/storage/app/'.$images['image'])) 
                           <img class="zoom-img" height="100px" width="100px" src="{{ url('/storage/app/'.$images['image'])}}"> 
                        @else
                           <img height="100px" width="100px" src="{{ $default_image or '' }}">
                        @endif    
                     </div>
                     <div class="contentbx">
                         <h5>{{isset($images['sku'])?$images['sku']:""}}</h5>
                     </div>
                 </div>
             </div>

            @endif 

         @endforeach
         @endif
      </div>
      </div>
   </div>
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{$back_url or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
         </div>
      </div>
   </div>
</div>

<!-- END Main Content -->
@stop