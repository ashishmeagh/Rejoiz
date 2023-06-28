@extends('maker.layout.master')                
@section('main_content')
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-sm-12 top-bg-title">
      <h4 class="page-title">{{$page_title or ''}}</h4>
      <div class="right">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path or ''}}">Manage {{str_plural($module_title)}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   <div class="col-sm-12">
      <div class="white-box">
           @include('maker.layout._operation_status')
          <div class="row">
            <div class="col-sm-12 col-xs-12">
                {!! Form::open([ 
                                'method'=>'POST',   
                                'class'=>'form-horizontal', 
                                'id'=>'validation-form'
                              ]) 
                !!} 

               {{csrf_field()}}
               
              
                  <div class="form-group row">
                    <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label" for="promo_code_name">Promo Code<i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10">
                     <input type="text" 
                            name="promo_code_name" 
                            id="promo_code_name"  
                            placeholder="Promotion Code" 
                            class="form-control" 
                            data-parsley-required='true' 
                            data-parsley-required-message='Please enter promo code.'/>
                      <div id ="err_container"></div>
                   </div>
                  </div>
 

                  <div class="form-group row clone-form-minus">
                    <label class="col-md-2 col-form-label">Status</label>
                      <div class="col-sm-6 col-lg-8 controls">
                         <input type="checkbox" 
                                name="is_active" 
                                id="is_active" 
                                value="1" 
                                data-size="small" 
                                class="js-switch" 
                                data-color="#99d683" 
                                data-secondary-color="#f96262" 
                                onchange="return toggleStatus();" />
                      </div>    
                  </div> 

               <div class="form-group">
                  <div class="col-md-12 common_back_save_btn">
                  <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                  <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true">Save</button>
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
   
  $(document).ready(function(){
  
    $('#btn_add').click(function(){
      
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
      
      });

   });

    
  function toggleStatus()
  {
      var is_active = $('#is_active').val();
      if(is_active=='1')
      {
        $('#is_active').val('1');
      }
      else
      {
        $('#is_active').val('0');
      }
  }

  
</script>
@stop