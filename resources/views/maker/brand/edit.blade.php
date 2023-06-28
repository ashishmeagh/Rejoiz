@extends('maker.layout.master')                
@section('main_content')
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">Manage {{str_plural($module_title)}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   <div class="col-sm-12 manage_brand_edit_brand">
      <div class="white-box">
           @include('admin.layout._operation_status')
               {!! Form::open([ 
                              'method'=>'POST',   
                              'class'=>'form-horizontal', 
                              'id'=>'validation-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
               <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul>
               
               <div  class="tab-content">

               <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="category_image">Brand Name <i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" name="brand_name" id="brand_name" class="form-control" placeholder="Brand Name" data-parsley-required="true" data-parsley-maxlength="50" data-parsley-maxlength-message="Brand name should be 50 or less characters long" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="{{$module_url_path}}/does_brand_exists/{{isset($brand_details['id'])?base64_encode($brand_details['id']):''}}" data-parsley-remote-options='{ "type": "POST", "dataType": "json", "data": { "_token": "{{ csrf_token() }}","id":"{{$brand_details['id'] or ''}} }}' value="{{$brand_details->brand_name or ''}}"  data-parsley-remote-message="Brand already exists" data-parsley-required-message="Please enter brand name" />
                   </div>
                  </div>

                   <div class="form-group row">
                      <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="category_image"> Brand Image <i class="red">*</i>
                      </label>
                      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        @php
                           $product_img_path = ""; 
                           $image_name = (isset($brand_details['brand_image']))? $brand_details['brand_image']:"";
                           $image_type = "category";
                           $is_resize = 0; 
                           $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                        @endphp 
                        <input type="file" name="brand_image"  class="dropify" data-default-file="{{ $product_img_path }}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside"
                        />
              
                      </div>
                    </div>

                    <input type="hidden" name="old_brand_image" value="{{$brand_details['brand_image'] or ''}}">
        
                     <div class="form-group row">
                        <label class="col-2 col-form-label">Status</label>
                          <div class="col-sm-10">
                              @php
                                if(isset($brand_details->is_active)&& $brand_details->is_active!='')
                                {
                                  $status = $brand_details->is_active;
                                } 
                                else
                                {
                                  $status = '';
                                }
    
                              @endphp
                              <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " @if($status =='1') checked="checked" @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                          </div>

                      </div>

               </div>
               <br>

               <input type="hidden" name="enc_id" value="{{isset($brand_details['id'])?base64_encode($brand_details['id']) : ''}}">

               <div class="form-group row">
               <div class="col-sm-12 common_back_save_btn">
                  <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path}}"><i class="fa fa-arrow-left"></i> Back</a>
                  <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true"> Update</button>
              </div>
            </div>
    
               {!! Form::close() !!}
      </div>
   </div>
</div>
<script type="text/javascript">
  const module_url_path = "{{ $module_url_path }}";
   $(document).ready(function(){
      $('#validation-form').parsley();


       $('#btn_add').click(function(){

        if($('#validation-form').parsley().validate()==false) return;


       else
       { 
        var formdata = new FormData($("#validation-form")[0]);
        
          $.ajax({
            url: module_url_path+'/store',
            type:"POST",
            data: formdata,
            contentType:false,
            processData:false,
            dataType:'json',
            beforeSend: function() 
            {
              showProcessingOverlay();                 
            },
            success:function(data)
            {
               hideProcessingOverlay(); 
               if('success' == data.status)
               {
                   $('#validation-form')[0].reset();

                   swal({
                           title:"Success",
                           text: data.description,
                           type: data.status,
                           confirmButtonText: "OK",
                           closeOnConfirm: false
                        },
                       function(isConfirm,tmp)
                       {
                         if(isConfirm==true)
                         {
                            window.location = '{{$module_url_path}}';
                         }
                       });
                }
                else
                {
                  var status = data.status;
                      status = status.charAt(0).toUpperCase() + status.slice(1);
                      
                   swal(status,data.description,data.status);
                }  
            }
         });
       }    
       // }
      });
   });

    function toggle_status()
    {
        var status = $('#status').val();
        if(status=='1')
        {
          $('#status').val('1');
        }
        else
        {
          $('#status').val('0');
        }
    }
  
</script>
@stop