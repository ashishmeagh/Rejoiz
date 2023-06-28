@extends('admin.layout.master')                
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
         <li><a href="{{url('/')}}/{{$curr_panel_slug}}/dashboard">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
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

               <div  class="tab-content rw-create-category-admin-tab-content">

                  <div class="row">
                    <label class="col-xs-4 col-sm-4 col-md-2 col-lg-2 col-form-label">Name<i class="red">*</i></label>
                      <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 controls">
                         <input type="text" name="menu_name" id="menu_name"  class="form-control "  />
                      </div>    
                  </div>

                  <div class="row">
                    <label class="col-xs-4 col-sm-4 col-md-2 col-lg-2 col-form-label">Slug<i class="red">*</i></label>
                      <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 controls">
                         <input type="text" name="menu_slug" id="menu_slug"  class="form-control "  />
                      </div>    
                  </div>
                  
                  <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label">Status</label>
                      <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10 controls">
                         <input type="checkbox" name="menu_status" id="menu_status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>    
                  </div> 

                 

               </div>

               <div class="cancel_back_flex_btn">
                <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true"> Save</button>
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

       $('#btn_add').click(function(){

        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);
        
        $.ajax({
          url: module_url_path+'/save',
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
                          window.location = data.link;
                       }
                     });
              }
              else
              {
                
                 swal('Error',data.description,data.status);
              }  
          }
          
        });   

      });
   });

    function toggle_status()
    {
        var site_status = $('#site_status').val();
        if(site_status=='1')
        {
          $('#site_status').val('1');
        }
        else
        {
          $('#site_status').val('0');
        }
    }
  
</script>
@stop