@extends('sales_manager.layout.master')  
@section('main_content')
<style>
#err_zip_code{
    margin: 2px 0 3px;
    padding: 0; 
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;
  }

</style>    


 <div id="page-wrapper">
 <div class="container-fluid">
 <div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$module_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{url('/')}}/sales_manager/dashboard">Dashboard</a></li>
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
         <!-- <div class="box-title">
            <h3><i class="ti-settings"></i> {{ isset($page_title)?$page_title:"" }}</h3>
            <div class="box-tool">
            </div>
         </div> -->
        
         {!! Form::open([ 'url' => url('/').'/sales_manager/update_sales_manager/'.base64_encode($arr_data['id']),
         'method'=>'POST',   
         'class'=>'form-horizontal', 
         'id'=>'validation-form',
         'enctype' =>'multipart/form-data'
         ]) !!}
         <input type="hidden" name="old_image" value="{{$arr_data['profile_image'] or ''}}">
         
       {{--   <div class="form-group row">
            <label for="first_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Commission:</label>
            <div class="col-sm-12 col-md-12 col-lg-9">
               <span>{{number_format((float)$arr_data['commission'], 2, '.','')}}%</span>
            </div>
               <span class='red'>{{ $errors->first('first_name') }}</span>
         </div> --}}

          <div class="form-group row area-row" >
            <label for="first_name" class="col-sm-12 col-md-1 col-lg-3 col-form-label commonlabel_bold">Area:</label>
            <div class="col-sm-12 col-md-11 col-lg-9">
  
    
               @if(isset($sales_manager_area) && count($sales_manager_area)>0)
                 
                @foreach($sales_manager_area as $key=>$sales_manager)

                  @php 
                    $area_name[] = $sales_manager['area_details']['area_name'];
                  @endphp
                @endforeach
             
                <span>{{implode(',',$area_name)}}</span>

              @else 

                <span>Area not assigned yet</span>

              @endif

            </div>
               <span class='red'>{{ $errors->first('first_name') }}</span>
         </div>

         <div class="form-group row">
            <label for="first_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label commonlabel_bold">First Name<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
               <input type="text" class="form-control"  name="first_name" data-parsley-required="true" data-parsley-required-message="Please enter first name" placeholder="First Name" id="first_name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid first name" value="{{$arr_data['first_name'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('first_name') }}</span>
         </div>
         
         <div class="form-group row">
            <label for="last_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label commonlabel_bold">Last Name<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <input type="text" name="last_name" class="form-control" data-parsley-required="true"  data-parsley-required-message="Please enter last name" placeholder="Last Name" id="last_name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid last name" value="{{$arr_data['last_name'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('last_name') }}</span>
         </div>
         <div class="form-group row">
            <label for="email" class="col-sm-12 col-md-12 col-lg-3 col-form-label commonlabel_bold">Email Id<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <input type="text" name="email" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" placeholder="Email Id" id="email" value="{{$arr_data['email'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('email') }}</span>
         </div>

         <div class="form-group row">
             <label class="col-sm-12 col-md-12 col-lg-3 col-form-label commonlabel_bold">Profile Image</label>
              <div class="col-sm-12 col-md-12 col-lg-9">
                <span><i class="red">{{-- Note: Image should be 150 * 150 --}}</i></span>
                @php
                  $product_img_path = ""; 
                  $image_name = (isset($arr_data['profile_image']))? $arr_data['profile_image']:"";
                  $image_type = "user";
                  $is_resize = 0; 
                  $product_img_path = imagePath($image_name, $image_type, $is_resize);
               @endphp
                <input type="file" name="image" id="ad_image" class="dropify" data-default-file="{{$product_img_path}}"
                data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside"/>
              </div>
         </div>


        <div class="form-group row">
            <label for="country" class="col-sm-12 col-md-12 col-lg-3 col-form-label commonlabel_bold">Country<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <select name="country_id" id="country_id" data-parsley-required="true" data-parsley-required-message="Please select country" class="form-control">
                        <option value="">Select</option>
                        @if(isset($country_arr) && sizeof($country_arr)>0)
                         @foreach($country_arr as $country)
                            
                            <option value="{{$country['id']}}" @if($country['id']== $arr_data['country_id']) selected="true" @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                            
                          @endforeach
                        @endif
                    </select>
            </div>
              <span class='red'>{{ $errors->first('country_id') }}</span>
         </div>
         
         <div class="form-group row">
            <label for="email" class="col-sm-12 col-md-12 col-lg-3 col-form-label commonlabel_bold">Contact No<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <input 
              type="text" 
              name="contact_name" 
              class="form-control" 
              data-parsley-required="true" 
              data-parsley-required-message="Please enter contact number" 
              placeholder="Contact No" 
              id="contact_no" 
              data-parsley-minlength-message="Contact No should be of 10 digits" 
              {{-- data-parsley-maxlength="18"  --}}
              data-parsley-maxlength-message="Contact No must be less than 14 digits" 
              data-parsley-pattern="^[0-9*#+]+$" 
              data-parsley-required 
              data-parsley-pattern-message="Please enter valid contact number" 
              value="{{ $arr_data['contact_no']}}">
              <input type="hidden" name="hid_country_code" id="hid_country_code">
            </div>
                 <span class='red'>{{ $errors->first('contact_no') }}</span>
         </div>

         <div class="form-group row">
            <label for="post_code" class="col-sm-12 col-md-12 col-lg-3 col-form-label commonlabel_bold">Zip/Postal Code<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
              <input oninput="this.value = this.value.toUpperCase()" type="text" name="post_code" class="form-control" data-parsley-required="true" placeholder="Zip/Postal Code" id="post_code" data-parsley-trigger="change" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" value="{{$arr_data['post_code'] or ''}}">
            
            <span class='red' id="err_zip_code">{{ $errors->first('post_code') }}</span>
            </div>
          </div>

          <div class="form-group row">
            <label for="last_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label commonlabel_bold">Description<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-9">
             {{--  <input type="text" row="2" name="description" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter description" data-parsley-maxlength="1000" placeholder="Description" id="description" value="{{$arr_data['sales_manager_details']['description'] or ''}}"> --}}

             <textarea type="text" class="form-control" data-parsley-required-message="Please enter description" data-parsley-errors-container="#errors_container" data-parsley-required="true" data-parsley-maxlength="1000" id="description" name="description">{{$arr_data['sales_manager_details']['description'] or ''}}</textarea>
             <span class='red' id="errors_textarea">{{ $errors->first('description') }}</span>
             <div id="errors_container"></div>
            </div>
         </div>         
         {!! Form::close() !!}
         <div class="form-group row">
            <div class="col-sm-12 col-md-9 col-lg-9">
              <button type="button" class="btn btn-success waves-effect waves-light m-r-10" value="Update" id="btn_update">Update</button>
            </div>
         </div>
      </div>
   </div>
</div>
</div>
</div>
<script type="text/javascript">

$(document).ready(function(){

  $("#btn_update").click(function(){
      var phone_code   = $('#country_id option:selected').attr('phone_code');
      var zipcode_length = $('#country_id option:selected').attr('zipcode_length');
      var countryName = $('#country_id option:selected').attr('country_name');

      var codeLength = jQuery('#hid_country_code').val();
      var minPhone = 7 + codeLength.length;            
      $('#contact_no').attr('data-parsley-minlength', minPhone);
      $('#contact_no').attr('data-parsley-minlength-message', 'Contact No must be greater than or equal to 7 digits');  

      var maxPhone = 14 + codeLength.length;            
      $('#contact_no').attr('data-parsley-maxlength', maxPhone);

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

      var description = tinyMCE.get('description').getContent();

      if(description == "" || description == null)
      {
         $('#errors_textarea').css('display','block');
         $('#errors_textarea').html('Please enter description');
         // $('#description').attr('data-parsley-required');
         return false;
      }else{
         $('#description').removeAttr('data-parsley-required',"true");
         $('#errors_textarea').css('display','none');
      }

      $('#validation-form').parsley();  
     
       if($('#validation-form').parsley().validate()==false)
      {  
         hideProcessingOverlay();
         return;
         
      }
      else
      { 
         showProcessingOverlay();
         tinyMCE.triggerSave();
         jQuery('#validation-form').submit();
      } 
  });

  $("#contact_no").keydown(function(event) {
            var text_length = $("#contact_no").attr('code_length');
            if (event.keyCode == 8) {
                this.selectionStart--;
            }
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });
   $("#contact_no").keyup(function(event) {
      var text_length = ($("#contact_no").attr('code_length')) ? $("#contact_no").attr(
            'code_length') : "";
      if (this.selectionStart < text_length) {
            this.selectionStart = text_length;
            event.preventDefault();
      }
   });
   
  $("#country_id").change(function(event) {
   
  var country_code       = $("#country_id").val();
  var zip_code          = $("#post_code").val();

  if(country_code  == '' && zip_code!="")
  {
    $("#err_zip_code").html('Invalid zip/postal code'); 
  }

   var phone_code   = $('option:selected', this).attr('phone_code');
   var zipcode_length = $('option:selected', this).attr('zipcode_length');
   var countryName = $('option:selected', this).attr('country_name');

   if(phone_code){
      $("#contact_no").val("+"+phone_code);
      $("#contact_no").attr('code_length',phone_code.length+1);  
      $("#hid_country_code").val('+'+phone_code);
      } else {
        $("#contact_no").val('');
        $("#contact_no").attr(0); 
        $("#hid_country_code").val('');
     }  

  var codeLength = jQuery('#hid_country_code').val();
  var minPhone = 7 + codeLength.length;            
  $('#contact_no').attr('data-parsley-minlength', minPhone);
  $('#contact_no').attr('data-parsley-minlength-message', 'Contact No must be greater than or equal to 7 digits');  

  var maxPhone = 14 + codeLength.length;            
  $('#contact_no').attr('data-parsley-maxlength', maxPhone);

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
});
</script>
<!-- END Main Content --> 
@endsection