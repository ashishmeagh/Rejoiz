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
         <li><a href="{{$module_url_path or ''}}">Create Catalogs</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   <div class="col-sm-12 white-box-spacing-equal">
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
                    <label class="col-xs-2 col-sm-12 col-md-12 col-lg-2 col-form-label" for="sequence">Name<i class="red">*</i></label>
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" name="catalog_name" id="catalog_name" class="form-control" placeholder="Name"  data-parsley-maxlength="50"  data-parsley-whitespace="trim" data-parsley-required="true" data-parsley-required-message="Please enter catalog name" data-parsley-maxlength-message="Catalog name should be maximum 50 characters long"/>
                   </div>
                  </div>


                  <div class="form-group row">
                    <label class="col-xs-2 col-sm-2 col-md-12 col-lg-2 col-form-label">Status</label>
                      <div class="col-xs-12 col-sm-10 col-md-12 col-lg-10 controls">
                         <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>    
                  </div> 

               </div>
               <br>

               <div class="form-group">
                 <div class="common_back_save_btn">
                   <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-left common_back_save_btn">
                  <a class="btn btn-inverse waves-effect waves-light backbtn" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                  <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true"> Save</button>
                  </div>
                   <!-- <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 text-right">
                    <button class="btn btn-success waves-effect waves-light " type="button" name="Save" id="btn_add" value="true"> Save</button>
                  </div> -->
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
      $('#validation-form').parsley();
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
                  var prev_status= data.status;
                      status     = prev_status.charAt(0).toUpperCase() + prev_status.slice(1);
                      
                   swal(status,data.description,prev_status);
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