
@extends('maker.layout.master')  
@section('main_content')
<style type="text/css">
.img-shop-tbl.nw-in-shp	{ width: 140px;}
.img-shop-tbl.nw-in-shp .dropify-wrapper{ height: 100px;}
th {
    white-space: nowrap;
}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <a href="#" data-toggle="modal" data-target=".bs-example-modal-lg" class="btn btn-success pull-right m-l-20 btn-rounded btn-outline waves-effect waves-light">Import {{str_singular($module_title)}} </a>
            <ol class="breadcrumb">
               <!-- <li><a href="{{url(config('app.project.maker_panel_slug').'/my_shop')}}">My Shop </a></li> -->
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
              
               <div>

                 <form action="{{url('/')}}/{{$maker_panel_slug}}/products/save_products" method="post" enctype ="multipart/form-data">
                  {{csrf_field()}}
                  <input type="file" name="product_csv" id="product_csv" class="form-control dropify" placeholder="Enter Product Name" data-default-file="" data-allowed-file-extensions="csv" data-errors-position="outside" data-parsley-required="true" data-parsley-errors-container="#err_primary_product_img">
                  <button type="submit" data-dismiss="modal" class="fcbtn btn btn-danger btn-outline btn-1c">Import </button>
                 </form>
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>
<!-- /#page-wrapper -->

@include('maker.layout.modals')

<script type="text/javascript"> var module_url_path  = "{{ $module_url_path or '' }}";  </script>
<script type="text/javascript" src="{{url('/assets/js/module_js/edit_product.js')}}"></script>
<script type="text/javascript" src="{{url('/assets/js/module_js/add_product.js')}}"></script>
@stop