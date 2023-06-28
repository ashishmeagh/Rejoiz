@extends('maker.layout.master')    
@section('main_content')
  <style>
  .red{ 
  margin: 10px 0 10px;
    padding: 0; 
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;}
   </style> 
 <div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{ isset($page_title)?$page_title:"" }}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="{{url(config('app.project.maker_panel_slug').'/dashboard')}}">Dashboard</a></li>
                  <li><a href="{{url(config('app.project.maker_panel_slug').'/company_settings')}}">Company Settings </a></li>
         <li class="active">{{ isset($page_title)?$page_title:"" }}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

<!-- BEGIN Main Content -->

<div class="row">
<div class="col-md-12">
<div class="white-box">
   @include('admin.layout._operation_status')
   <div class="row">
      <div class="col-sm-12 col-xs-12">
      

          <form class="form-horizontal" id="validation-form">
             {{ csrf_field() }}
            <div class="form-group row">
            <label for="description" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Company Story<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-10">

               <textarea id="shop_story" name="shop_story">{{$shop_data['shop_story']}}</textarea>
            
               <span class='red' id="err_shop_story">{{ $errors->first('shop_story') }}</span>
            </div>
         </div>
          <div class="form-group">
            <div class="col-sm-12 common_back_save_btn">
                <a class="btn btn-inverse waves-effect waves-light" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="button" class="btn btn-success waves-effect waves-light" value="Update" id="btn_update">Update</button>
          </div>
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

  const user_id = "{{$shop_data['maker_id'] or ''}}";

   $(document).ready(function(){

      $('#btn_update').click(function(){

      var textarea_value = tinyMCE.get('shop_story').getContent();

      if(textarea_value=="")
      {
        $("#err_shop_story").html('Please enter company story');
        hideProcessingOverlay();
        return;
      }

     
       else
       { 
        $("#err_shop_story").html('');
        //alert(user_id);
        var formdata = new FormData($("#validation-form")[0]);
        formdata.append('shop_story',textarea_value);
        
        $.ajax({
          url: module_url_path+'/save_shop_story',
          type:"POST",
          data: formdata,
          contentType:false,
          processData:false,
          dataType:'json',
          beforeSend : function()
          {
            showProcessingOverlay();
          },
          success:function(data)
          {
             hideProcessingOverlay(); 
             if('success' == data.status)
             {
                 //swal(data.status,data.description,data.status);
                swal( {
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
                          window.location = data.url;
                       }
                     });
               }
              else
              {
                
                 swal("Error",data.description,data.status);
              }  
          }
          
        });   

       }
      });
   });


</script>

@endsection