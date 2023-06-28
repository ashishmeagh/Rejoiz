@extends('maker.layout.master')  
@section('main_content')
<style type="text/css">
.img-shop-tbl.nw-in-shp	{ width: 140px;}
.img-shop-tbl.nw-in-shp .dropify-wrapper{ height: 100px;}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
           
            <ol class="breadcrumb">
               <li><a href="{{url(config('app.project.maker_panel_slug').'/dashboard')}}">Dashboard</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
         	 @include('admin.layout._operation_status')
            <div class="white-box">
              
               <div class="row">
               <h1 class="text-danger"><i> Coming Soon</i></h1>
               <img src="https://metro.co.uk/wp-content/uploads/2014/02/screen-shot-2014-03-14-at-09-34-56.png" width="70px" height="70px">
               </div>
              
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>
@stop