@extends('maker.layout.master')                
@section('main_content')

<style>
.table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #666;
    background-color: transparent;
    color: #666;
}
</style>
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
         <li><a href="{{$module_url_path or ''}}">Manage {{str_plural($module_title)}}</a></li>
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

               <div  class="tab-content">
                 
                <input type="hidden" name="enc_id" id="enc_id" value="{{isset($promo_code_arr['id'])? base64_encode($promo_code_arr['id']):0}}">

                  <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="brand_image">Promo Code<i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" name="promo_code_name" placeholder="Promotion Code" id="promo_code_name" data-parsley-required ="true" data-parsley-required-message ="Please enter promo code" class="form-control" value="{{$promo_code_arr['promo_code_name'] or ''}}" />
                      <div id ="err_container"></div>
                   </div>
                  </div>
 

                  <div class="form-group row clone-form-minus">
                    <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Status</label>
                      <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10 controls">
                         <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " @if($promo_code_arr['is_active'] == 1) checked @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return toggleStatus();" />
                      </div>    
                  </div> 

               </div>
               <br>

               <div class="form-group">
                  <div class="col-md-12 common_back_save_btn">
                  <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                  <button class="btn btn-success waves-effect waves-light" type="submit" name="Save" id="btn_add" value="true" onclick="saveTinyMceContent();">Save</button>
                  </div>

              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>

<script type="text/javascript">

  const module_url_path = "{{ $module_url_path or ''}}";
  var count = 0;

  function saveTinyMceContent()
  {
    tinyMCE.triggerSave();
  }


  $(document).ready(function(){


    $('#validation-form').parsley();


      $('#validation-form').submit(function(e){
         e.preventDefault();
        if($('#validation-form').parsley().validate()==false) return;

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
                           title: "Success",
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
      
      });

   });

    
  function toggleStatus()
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