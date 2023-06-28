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
   <div class="col-sm-12">
      <div class="white-box">
           @include('admin.layout._operation_status')
          <div class="row">
            <div class="col-sm-12 col-xs-12">
               {!! Form::open([ 
                              'method'=>'POST',   
                              'class'=>'form-horizontal', 
                              'id'=>'validation-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
               <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul>

               <div class="row">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-22 col-md-12 col-lg-2 col-form-label" for="category_image">Brand Name <i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" name="brand_name" id="brand_name" class="form-control" placeholder="Brand Name"  data-parsley-maxlength="50" data-parsley-maxlength-message="Brand name should be 50 or less characters long" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-whitespace-message="whitespaces are not allowed." data-parsley-remote="{{$module_url_path}}/does_brand_exists" data-parsley-remote-options='{ "type": "POST", "dataType": "json", "data": { "_token": "{{ csrf_token() }}" }}'  data-parsley-remote-message="Brand already exists" data-parsley-required="true" data-parsley-required-message="Please enter brand name"/>
                   </div>
                  </div>
               </div>

              <div class="row">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-22 col-md-12 col-lg-2 col-form-label" for="brand_image">Brand Image <i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="file" name="brand_image" id="brand_image" class="dropify" {{-- data-default-file="{{url('/')}}/uploads/default.jpeg" --}} data-parsley-required ="true"
                     data-parsley-required-message ="Please upload brand image"
                      data-parsley-errors-container="#err_container" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />
                      <div id ="err_container"></div>
                   </div>
                  </div>
              </div>

              <div class="row">
                <div class="form-group">
                    <label class="col-md-2 col-form-label">Status</label>
                      <div class="col-sm-6 col-lg-8 controls">
                         <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>    
                  </div> 
              </div>
               <br>

               <div class="form-group">
                  <div class="text-left common_back_save_btn">
                    <a class="btn btn-inverse waves-effect waves-light backbtn" href="{{$module_url_path}}"><i class="fa fa-arrow-left"></i> Back</a>
                    <button class="btn btn-success waves-effect waves-light " type="button" name="Save" id="btn_add" value="true"> Save</button>
                  </div>

              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">
  const module_url_path = "{{ $module_url_path }}";
   $(document).ready(function(){
    $('#validation-form').parsley();
       $('#btn_add').click(function(){

        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);
        
        // if($('#validation-form').parsley().isValid() == '' )
        // {
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
                            window.location = '{{$module_url_path or ''}}';
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