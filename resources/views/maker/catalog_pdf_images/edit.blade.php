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
         <li><a href="{{$module_url_path or ''}}">Catalog Images</a></li>
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

                    <div class="form-group row">
                      <label class="col-2 col-form-label" for="sequence">Sequence<i class="red">*</i></label>
                      <div class="col-10">
                       <input type="text" name="sequence" id="sequence" class="form-control" placeholder="Sequence"  data-parsley-maxlength="50"  data-parsley-whitespace="trim" data-parsley-required="true" data-parsley-required-message="Please enter sequence" value="{{isset($catalogPageArr['page_sequence'])?$catalogPageArr['page_sequence']:''}}" />
                       </div>
                    </div>

                    <div class="form-group row">
                      <label class="col-2 col-form-label" for="catalog_image">Image
                      </label>
                      <div class="col-md-10">
                        <input type="file" name="catalog_image" id="catalog_image" class="dropify" data-default-file="{{ url('/storage/app/'.$catalogPageArr['image'])}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-errors-position="outside" 
                        />
                      </div>
                    </div>


                    <input type="hidden" name="old_catlog_image" value="{{isset($catalogPageArr['image'])?$catalogPageArr['image']:''}}">
        
                     <div class="form-group row">
                        <label class="col-2 col-form-label">Status</label>
                          <div class="col-10">
                              @php

                                if(isset($catalogPageArr['is_active'])&& $catalogPageArr['is_active']!='')
                                {
                                  $status = $catalogPageArr['is_active'];
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

               <input type="hidden" name="enc_id" id="enc_id" value="{{isset($catalogPageArr['id'])?base64_encode($catalogPageArr['id']) :0}}">
               <input type="hidden" name="enc_catlog_id" id="enc_catlog_id" value="{{isset($catalogPageArr['id'])?base64_encode($catalogPageArr['catalog_id']) :0}}">

               <div class="form-group">
                  <div class="col-md-6 text-left">
                  <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                  </div>

                  <div class="col-md-6 text-right">
                    <button class="btn btn-success waves-effect waves-light m-r-10" type="button" name="Save" id="btn_update" value="true"> Update</button>
                  </div>

              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
</div>
<script type="text/javascript">
  const module_url_path = "{{ $module_url_path or '' }}";
   $(document).ready(function(){
      $('#validation-form').parsley();


       $('#btn_update').click(function(){

        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);
      
          $.ajax({
            url: module_url_path+'/update',
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