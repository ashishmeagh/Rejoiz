@extends('representative.layout.master')  
@section('main_content')
<!-- Page Content -->
 <div id="page-wrapper">

<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$module_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{url('/')}}/representative/dashboard">Dashboard</a></li>
         <li class="active">{{$module_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

<!-- BEGIN Main Content -->
@if(Session::has('message'))
<p class="{{ Session::get('alert-class', 'alert-danger-note') }}">{{ Session::get('message') }}</p>
@endif

<div class="row">
<div class="col-md-12">
<div class="white-box">
   @include('admin.layout._operation_status')
   <div class="row">
      <div class="col-sm-12 col-xs-12">
         <div class="box-title">
            <h3><i class="ti-settings"></i> {{ isset($page_title)?$page_title:"" }}</h3>
            <div class="box-tool">
            </div>
         </div>
        
         {!! Form::open([ 'url' => $module_url_path.'/update/'.base64_encode($arr_data['id']),
         'method'=>'POST',   
         'class'=>'form-horizontal', 
         'id'=>'validation-form',
         'enctype' =>'multipart/form-data'
         ]) !!}
         <input type="hidden" name="old_image" value="{{$arr_data['get_user_details']['profile_image'] or ''}}">
         
          <div class="form-group row area-row">
            <label for="first_name" class="col-sm-12 col-md-4 col-lg-3 col-form-label">Sales Manager Name:</label>
            <div class="col-sm-12 col-md-8 col-lg-9">
              @php 
                 $first_name = isset($arr_data['sales_manager_details']['get_user_data']['first_name'])?$arr_data['sales_manager_details']['get_user_data']['first_name']:'';
                 $last_name = isset($arr_data['sales_manager_details']['get_user_data']['last_name'])?$arr_data['sales_manager_details']['get_user_data']['last_name']:'';

              @endphp
               <span>{{$first_name.' '.$last_name}}</span>
            </div> 
               
         </div>


          <div class="form-group row area-row">
            <label for="first_name" class="col-sm-12 col-md-4 col-lg-3 col-form-label">Area:</label>
            <div class="col-sm-12 col-md-8 col-lg-9">
               <span>{{$arr_data['get_area_details']['area_name'] or ''}}</span>
            </div>
              
         </div>

         <div class="form-group row">
            <label for="first_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label">First Name<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
               <input type="text" class="form-control"  name="first_name" data-parsley-required="true" data-parsley-required-message="Please enter first name" placeholder="First Name" id="first_name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed" value="{{$arr_data['get_user_details']['first_name'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('first_name') }}</span>
         </div>
         
         <div class="form-group row">
            <label for="last_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Last Name<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <input type="text" name="last_name" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter last name"  placeholder="Last Name" id="last_name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed" value="{{$arr_data['get_user_details']['last_name'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('last_name') }}</span>
         </div>
         <div class="form-group row">
            <label for="email" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Email Id<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <input type="text" name="email" class="form-control" data-parsley-required="true"data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" placeholder="Email Id" id="email" value="{{$arr_data['get_user_details']['email'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('email') }}</span>
         </div>
         <div class="form-group row">
             <div class="col-sm-12 col-md-12 col-lg-3">
              <label class="col-form-label" for="ad_image">Profile Image</label>
            </div>
              <div class="col-sm-12 col-md-12 col-lg-9">
                <span><i class="red">{{-- Note: Image should be 150 * 150 --}}</i></span>
                @php
                  $product_img_path = ""; 
                  $image_name = (isset($arr_data['get_user_details']['profile_image']))? $arr_data['get_user_details']['profile_image']:"";
                  $image_type = "user";
                  $is_resize = 0; 
                  $product_img_path = imagePath($image_name, $image_type, $is_resize);
               @endphp
                <input type="file" name="image" id="ad_image" class="dropify" data-default-file="{{$product_img_path}}"
                data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside"/>
              </div>
         </div>



        <div class="form-group row">
            <label for="country" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Country<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <select name="country_id" id="country_id" data-parsley-required="true" data-parsley-required-message="Please select country" class="form-control">
                        <option value="">Select</option>
                        @if(isset($country_arr) && sizeof($country_arr)>0)
                         @foreach($country_arr as $country)
                            
                            <option value="{{$country['id']}}" @if(isset($arr_data['get_user_details']['country_id']) && $country['id']== $arr_data['get_user_details']['country_id']) selected="true" @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                            
                          @endforeach
                        @endif
                    </select>
            </div>
              <span class='red'>{{ $errors->first('country_id') }}</span>
         </div>

         <div class="form-group row">
            <label for="post_code" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Zip/Postal Code<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <input oninput="this.value = this.value.toUpperCase()" type="text" name="post_code" class="form-control" placeholder="Zip/Postal Code" id="post_code" data-parsley-trigger="change" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" value="{{$arr_data['get_user_details']['post_code'] or ''}}">
            
              <span class='red' id="err_post_code">{{ $errors->first('post_code') }}</span>
            </div>
          </div>



         <div class="form-group row">
            <label for="last_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Description<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <input type="text" row="2" name="description" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter description" data-parsley-maxlength="1000" placeholder="Description" id="description" value="{{$arr_data['description'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('description') }}</span>
         </div>
         {{-- <div class="form-group row">
            <div class="col-sm-12 col-md-12 col-lg-9">
              <button type="submit" class="btn btn-success waves-effect waves-light m-r-10" value="Update" id="btn_submit">Update</button>
            </div>
         </div> --}}
          
         {!! Form::close() !!}
         <div class="form-group row">
            <div class="col-12">
              <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{url('/')}}/representative/dashboard"><i class="fa fa-arrow-left"></i> Back</a>
              <button type="button" style="float: right" class="btn btn-success waves-effect waves-light" value="Update" id="update">Update</button>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
</div>

<script type="text/javascript">

$(document).ready(function(){
   jQuery("#update").on("click", function(e){
      e.preventDefault();
      
      var zipcode_length = $('option:selected', "#country_id").attr('zipcode_length');
      var countryName = $('option:selected', "#country_id").attr('country_name');

      if(zipcode_length == 8)
      {
         $('#post_code').attr('parsley-maxlength', true);
         $('#post_code').removeAttr('data-parsley-length');
         $('#post_code').attr('data-parsley-length-message', "");
         $("#post_code").attr({
            "data-parsley-maxlength": zipcode_length,
            "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
               zipcode_length +
               '  characters',
         });
      }
      else{
         $('#post_code').attr('parsley-maxlength', false);
         $('#post_code').attr('data-parsley-maxlength-message', "");
         $("#post_code").attr({
         "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
         "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
               zipcode_length +
               '  digits'
         });
      }

      if($('#validation-form').parsley().validate()==false)
      {  
         hideProcessingOverlay();
         return;
      }
      else
      { 
         showProcessingOverlay();
         $("#validation-form").submit();
      }
   })
   
  $("#country_id").change(function(event) {
   
  var country_code = $("#country_id").val();
  var zip_code  = $("#post_code").val();

  if(country_code=='' && post_code!="")
  {
     $("#err_post_code").css('font-size','0.9em');
     $("#err_post_code").html('Invalid zip/postal code');   
  }

   var phone_code   = $('option:selected', this).attr('phone_code');
   var zipcode_length = $('option:selected', this).attr('zipcode_length');
   var countryName = $('option:selected', this).attr('country_name');

   // if(phone_code){
   //    $("#contact_no").val("+"+phone_code);
   //    $("#contact_no").attr('code_length',phone_code.length+1);  
   //    $("#hid_country_code").val('+'+phone_code);
   //    } else {
   //      $("#contact_no").val('');
   //      $("#contact_no").attr(0); 
   //      $("#hid_country_code").val('');
   //   }  


    if(country_code=='' && zip_code!="")
    {
        $("#err_zip_code").html('Invalid zip/postal code');
    }

   //  var codeLength = jQuery('#hid_country_code').val();
   //  var minPhone = 10 + codeLength.length;            
   //  $('#contact_no').attr('data-parsley-minlength', minPhone);

    if(zipcode_length == 8)
    {
        $('#post_code').attr('parsley-maxlength', true);
        $('#post_code').removeAttr('data-parsley-length');
        $('#post_code').attr('data-parsley-length-message', "");
        $("#post_code").attr({
          "data-parsley-maxlength": zipcode_length,
          "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
            zipcode_length +
            '  characters',
        });
    }
    else{
        $('#post_code').attr('parsley-maxlength', false);
        $('#post_code').attr('data-parsley-maxlength-message', "");
        $("#post_code").attr({
        "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
        "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
            zipcode_length +
            '  digits'
        });
    }

    $('#validation-form').parsley();  
  });

  $('#btn_submit').click(function(){
      
      if($('#validation-form').parsley().validate()==false)
      {  
         hideProcessingOverlay();
         return;
      }
      else
      { 
         showProcessingOverlay();
         $("#validation-form").submit();
      } 

   }); 
});

</script>

<!-- END Main Content --> 
@endsection