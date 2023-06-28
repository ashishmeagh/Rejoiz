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
         <li><a href="{{$curr_panel_slug}}/dashboard">Dashboard</a></li>
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
              <form method="POST", class="form-horizontal", id="validation-form", enctype="multipart/form-data">
               <ul  class="nav nav-tabs">
                  @include('admin.layout._multi_lang_tab')
               </ul>
               <div  class="tab-content">
                       <div class="form-group row">
                          <label class="col-2 col-form-label" for="state"> Retailer First Name <i class="red">*</i></label>

                          <div class="col-sm-6 col-lg-8 controls">
                            <input type="text" name="fname", id="fname" class="form-control" placeholder="First Name">
                          </div>
                          <span class='red'>{{ $errors->first('fname') }}</span>  
                       </div>
                    </div>
               


                  <div class="form-group row">
                    <label class="col-md-2 col-form-label">Status<i class="red">*</i></label>
                      <div class="col-sm-6 col-lg-8 controls">
                         <input type="checkbox" name="status" id="status" value="1" data-size="small" class="js-switch " data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>    
                  </div> 
               </div>
               <br>
               
               <div class="form-group ">
                  <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                    <button class="btn btn-success waves-effect waves-light m-r-10" type="button" name="Save" id="btn_add" value="true"> Save</button>
                    <a class="btn btn-inverse waves-effect waves-light" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Back</a>
                  </div>
              </div>    
            </form>
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
          success:function(data)
          {

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
                          window.location = data.link;
                       }
                     });
              }
              else
              {
                
                 swal("Error",data.description,data.status);
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