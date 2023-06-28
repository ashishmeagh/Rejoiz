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
         <li><a href="{{url('/admin')}}/dashboard">Dashboard</a></li>
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
               <ul  class="nav nav-tabs" style="border:none;">
                  @include('admin.layout._multi_lang_tab')
               </ul>

               <div  class="tab-content m-t-0">
        {{ csrf_field() }}

       
                

                 @if(isset($enc_id))
    
                    <input type="hidden" name="enc_id" value="{{$enc_id}}">
                    
                    @endif


                 @if(isset($arr_data['banner_image']))
    
                    <input type="hidden" name="old_banner_image" value="{{$arr_data['banner_image']}}">
                    
                    @endif

                    @if(isset($arr_data['type']))
    
                    <input type="hidden" name="old_type" value="{{$arr_data['type']}}">
                    
                    @endif

                     @if(isset($arr_data['url']))
    
                    <input type="hidden" name="old_url" value="{{$arr_data['url']}}">
                    
                    @endif
                  
                  <div class="m-b-5 row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" >Banner Image <i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                     <input type="file" name="banner_image" id="banner_image" class="dropify" {{-- data-default-file="{{url('/')}}/uploads/default.jpeg" --}} 
                      data-parsley-errors-container="#err_container" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" 
                      data-default-file="{{ url('/storage/app/'.$arr_data['banner_image'])}}" />
                      <div id ="err_container"></div>
                   </div>
                  </div>
                    <div class="m-b-20 row">
                      <label for="type" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Type<i class="red">*</i></label>
                          <div class="col-sm-12 col-md-12 col-lg-10">
                            <select class="form-control" name="type" id="type">
                              <option @if($arr_data['type'] == 1) selected @endif value="1">Size 1</option>
                              <option @if($arr_data['type'] == 2) selected @endif value="2">Size 2</option>
                              <option @if($arr_data['type'] == 3) selected @endif value="3">Size 3</option>
                              {{-- <option  @if($arr_data['type'] == 2) selected @endif  value="{{$arr_data['type']}}" ></option> --}}
                            </select>
                          </div>
                      </div>

                      <div class="m-b-20 row" id="small_banner_image" @if($arr_data['type'] != '3') style="display: none" @endif>
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" >Banner Image (For smaller devices)<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                     <input type="file" name="banner_image_resp" id="banner_image_resp" class="dropify" {{-- data-default-file="{{url('/')}}/uploads/default.jpeg" --}} data-parsley-required-message ="Please select banner image"
                      data-parsley-errors-container="#err_container_resp" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-image-width-range="600-680" data-image-height-range="300-380" data-errors-position="outside" onchange="$('#err_container_resp').html('');"  data-default-file="{{ url('/storage/app/'.$arr_data['banner_image_small'])}}"  />
                      <div id ="err_container_resp"></div>
                      <span>Note: The banner image width must be between 600px - 680px and height must be between 300px - 380px for smaller devices</span>
                   </div>
                  </div>

                  <input type="hidden" id="old_banner_image_resp" name="old_banner_image_resp" value="{{ $arr_data['banner_image_small']}}">
               <br>

                <div class="m-b-5 row">
                  <label for="type" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Url</label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                        <input type="text" class="form-control" name="url" value="{{isset($arr_data['url'])?$arr_data['url']:''}}" data-parsley-type="url" data-parsley-type-message="Please enter valid url.">
                    </div>
                </div>

               <div class="row m-t-20">
                  <div class="col-md-12 cancel_back_flex_btn">
                  <a class="btn btn-success waves-effect waves-light pull-left" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Back</a>
                  <button class="btn btn-success waves-effect waves-light" type="button" name="Save" id="btn_add" value="true"> Save</button>
                  </div>

              </div>
    
               {!! Form::close() !!}
            </div>
         </div>
      </div>
   </div>
   </div>
</div>
<script type="text/javascript">
  const module_url_path = "{{ $module_url_path }}";
   $(document).ready(function(){

      $('#type').change(function(){       
          if($(this).val() == '3'){
            $("#banner_image_resp").attr('data-parsley-required','true');
            $("#small_banner_image").show();
          } else {
            $("#banner_image_resp").removeAttr('data-parsley-required');
             $("#small_banner_image").hide();
          }
       });

      $('#banner_image_resp').change(function(){       
            $("#old_banner_image_resp").val("");
       });


       $('#btn_add').click(function(){

        if($('#validation-form').parsley().validate()==false) return;

        var formdata = new FormData($("#validation-form")[0]);


        // Image validation

        if($('#type').val() == '3' && $("#old_banner_image_resp").val() == ""){
        if($('#banner_image_resp').value!='')
        {         
          var _URL = window.URL || window.webkitURL;

          let [minWidth, maxWidth] = $('#banner_image_resp').data('image-width-range').split('-');
          let [minHeight, maxHeight] = $('#banner_image_resp').data('image-height-range').split('-');
          
          let bannerImageInstance = $('#banner_image_resp')[0].files[0];            

          var img = new Image();          

          img.src = _URL.createObjectURL(bannerImageInstance);
          
          img.onload = function() {
            if(this.width < minWidth || this.width > maxWidth)
            {
              
              $("#err_container_resp").html('<span style="color:red;font-size:0.9em;">The banner image width must be between ' + minWidth + 'px and ' + maxWidth + 'px</span>');
              $('#banner_image_resp').focus();
              
              return;
            }
            else if(this.height < minHeight || this.height > maxHeight)
            {
              $("#err_container_resp").html('<span style="color:red;font-size:0.9em;">The banner image height must be between ' + minHeight + 'px and ' + maxHeight + 'px</span>');
              $('#banner_image_resp').focus();

              return;
              
            }
            else
            {
              $("#err_container_resp").html('');
            }
            save_banner();
          } 
        }else
        {
         
          save_banner();
        }
      } else {

        
        save_banner();
        
      }
          

      });
   });

   function save_banner()
    {
      var formdata = new FormData($("#validation-form")[0]);
        
      $.ajax({
          url: module_url_path+'/save',
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
                
                 $('#validation-form')[0].reset();

                 swal({
                         title: 'Success',
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
                
                 swal(data.status,data.description,data.status);
              }  
          }
          
        });   
    }

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