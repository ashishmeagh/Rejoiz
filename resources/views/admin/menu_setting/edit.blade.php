@extends('admin.layout.master')                
@section('main_content')
{{-- {{dd($arr_data)}}
 --}}<!-- Page Content -->

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
            <li class="active">{{$page_title or ''}} </li>
         </ol>
      </div>
      <!-- /.col-lg-12 -->
   </div>
   <!-- .row -->
    <div>
            <div class="col-sm-12 col-xs-12 white-box">
               {!! Form::open([ 
                              'method'=>'POST',   
                              'class'=>'form-horizontal', 
                              'id'=>'validation-form',
                              'enctype' =>'multipart/form-data'
               ]) !!} 
             

               <div  class="tab-content rw-create-category-admin-tab-content mt-0">

                  <div class="row">
                    <label class="col-xs-4 col-sm-4 col-md-2 col-lg-2 col-form-label">Name<i class="red">*</i></label>
                      <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10 controls">
                         <input type="text" name="menu_name" id="menu_name"  class="form-control "  value="{{ $arr_data['menu_name'] or '' }}" />
                      </div>    
                  </div>

                   <div class="row">
                    <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label">Slug<i class="red">*</i></label>
                      <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10 controls">
                         <input type="text" name="menu_slug" id="menu_slug"  class="form-control " value="{{ $arr_data['menu_slug'] or '' }}"  />
                      </div>    
                  </div>
                  
                  <div class="row">
                    @php
                                if(isset($arr_data['menu_status'])&& $arr_data['menu_status']!='')
                                {
                                  $status = $arr_data['menu_status'];
                                } 
                                else
                                {
                                  $status = '';
                                }
    
                              @endphp
                    <label class="col-xs-12 col-sm-12 col-md-2 col-lg-2 col-form-label">Status</label>
                      <div class="col-xs-12 col-sm-12 col-md-10 col-lg-10 controls">
                         <input type="checkbox" name="menu_status" id="menu_status" value="1" data-size="small" class="js-switch " @if($status =='1') checked="checked" @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return toggle_status();" />
                      </div>    
                  </div> 

                 

               </div>

               <div class="cancel_back_flex_btn">
                <input type="hidden" name="enc_id" value="{{$enc_id or ''}}">
                <input type="hidden" name="status" value="{{$arr_data['menu_status'] or ''}}">

                <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="button" onclick="saveTinyMceContent();" class="btn btn-success waves-effect waves-light m-r-10" value="Update" id="btn_update" >Save</button>
              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
</div>

<!-- END Main Content -->
<script type="text/javascript">
  const module_url_path = "{{ $module_url_path }}";
   $(document).ready(function(){

       $('#btn_update').click(function(){

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
        var menu_status = $('#menu_status').val();

        if(menu_status =='1')
        {
          $('#menu_status').val('1');
        }
        else
        {
          $('#menu_status').val('0');
        }
  } 

 </script>
 <script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
 
 <script type="text/javascript">
    function saveTinyMceContent()
    {
      tinyMCE.triggerSave();
    }
 </script> 
@stop