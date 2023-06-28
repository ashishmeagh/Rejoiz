@extends('admin.layout.master')                
@section('main_content')

<style>
  .nav-tabs {
    border-bottom: none;
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
         <li><a href="{{url('/admin')}}/dashboard">Dashboard</a></li>
         <li><a href="{{$module_url_path or ''}}">{{$module_title or ''}}</a></li>
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

              {{--   @if(isset($arr_lang) && sizeof($arr_lang)>0)

                    @foreach($arr_lang as $lang)

                      <div class="tab-pane fade {{ $lang['locale']=='en'?'in active':'' }}" 
                       id="{{ $lang['locale'] }}">
    
                       <div class="m-b-20 row">
                          <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="state"> Category Name @if($lang['locale'] == 'en') 
                          <i class="red">*</i>
                          @endif
                          </label>
                          <div class="col-sm-12 col-md-12 col-lg-10 controls">
                             @if($lang['locale'] == 'en')        
                             {!! Form::text('category_name_'.$lang['locale'],old('category_name_'.$lang['locale']),['class'=>'form-control','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter category name.' ,'data-parsley-minlength'=>'3', 'placeholder'=>'Category']) !!}

                    
                             @else
                             {!! Form::text('category_name_'.$lang['locale'],old('category_name_'.$lang['locale'])) !!}
                             @endif    
                          </div>
                          <span class='red'>{{ $errors->first('category_name_'.$lang['locale']) }}</span>  
                       </div>

                    </div>
                  @endforeach
                  @endif
 --}}
                  {{--  <div class="m-b-20 row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="state"> {!! translation('category_description') !!}
                      <i class="red">*</i></label>

                        <div class="col-sm-12 col-md-12 col-lg-10 controls">
                         
                         <textarea class="form-control" rows="10" name="category_description" required=""> </textarea>
                          </div>
                          <span class='red'>{{ $errors->first('base_price') }}</span>  
                  </div> --}}
                  
                {{--  <div class="m-b-20 row">
                   <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" for="category_image">{!! translation('category_image') !!}</label>
                    <div class="col-sm-12 col-md-12 col-lg-10 controls">
                      <input type="file" name="category_image" id="category_image" class="dropify" data-default-file="{{url('/')}}/uploads/default_category.png" data-parsley-required ='true' data-parsley-errors-container="#category_image_error" accept="image/*" />
                      <div id="category_image_error"></div>
                     
                    </div>

                 </div> --}}

                  <div class="m-b-20 row">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" >Banner Image <i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                     <input type="file" name="banner_image" id="banner_image" class="dropify" {{-- data-default-file="{{url('/')}}/uploads/default.jpeg" --}} data-parsley-required ="true" data-parsley-required-message ="Please select banner image"
                      data-parsley-errors-container="#err_container" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />
                      <div id ="err_container"></div>
                   </div>
                  </div>

                  
                   <div class="m-b-20 row">
                      <label for="type" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Type<i class="red">*</i></label>

                         <div class="col-sm-12 col-md-12 col-lg-10">
                          <select name="type" id="type" class="form-control" data-parsley-required ='true' data-parsley-required-message ='Please select banner image type'>
                              <option value="">Select Type</option>
                              <option value="1">Size 1</option>
                              <option value="2">Size 2</option>
                              <option value="3">Size 3</option>
                          </select>
                        </div>
                      </div>



                 <div class="m-b-20 row" id="small_banner_image" style="display: none">
                    <label class="col-sm-12 col-md-12 col-lg-2 col-form-label" >Banner Image (For smaller devices)<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                     <input type="file" name="banner_image_resp" id="banner_image_resp" class="dropify" {{-- data-default-file="{{url('/')}}/uploads/default.jpeg" --}} data-parsley-required-message ="Please select banner image"
                      data-parsley-errors-container="#err_container_resp" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-image-width-range="600-680" data-image-height-range="300-380" data-errors-position="outside" onchange="$('#err_container_resp').html('');"/>
                      <div id ="err_container_resp"></div>
                      <span>Note: The banner image width must be between 600px - 680px and height must be between 300px - 380px for smaller devices</span>
                   </div>
                  </div>

               <div class="m-b-20 row">
                  <label for="type" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Url</label>
                    <div class="col-sm-12 col-md-12 col-lg-10">
                        <input type="text" class="form-control" name="url" data-parsley-type="url" data-parsley-type-message="Please enter valid url.">
                    </div>
                </div>

               <div class="row">
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

       $('#btn_add').click(function(){       

        if($('#validation-form').parsley().validate()==false) return;

        
        if($('#banner_image_resp').value!='' && $('#type').val() == '3')
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

      });

       $('#type').change(function(){       
          if($(this).val() == '3'){
            $("#banner_image_resp").attr('data-parsley-required','true');
            $("#small_banner_image").show();
          } else {
            $("#banner_image_resp").removeAttr('data-parsley-required');
             $("#small_banner_image").hide();
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