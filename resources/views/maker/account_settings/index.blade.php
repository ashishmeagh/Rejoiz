@extends('maker.layout.master')    
@section('main_content')

<style>
#err_post_code{
    margin: 10px 0 10px;
    padding: 0; 
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;
  }
  .actvspan {
    color: #42a506;
  }
  .actvspan.desc-red{color: #c70303;}
  .close {position:absolute; top:10px; right:10px;}
</style>

<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$module_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li class="active">{{$module_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

<!-- BEGIN Main Content -->
@if(Session::has('message'))
<p class="{{ Session::get('alert-class', 'alert-danger-note') }}">{{ Session::get('message') }}</p>
@endif
<div class="row" style="margin:0px;">
<div class="col-md-12 white-box">


 @include('admin.layout._operation_status')
 <div class="row">
<div class="col-md-12 vendor-account-setting-pay-active">
    <!-- <div class="box-title">
        <h3 class="m-0"><i class="ti-settings"></i> {{ isset($page_title)?$page_title:"" }}</h3>
        <div class="box-tool">
        </div>
     </div> -->
     <div class="col-sm-12 text-right">
        @if(isset($maker_details['is_direct_payment']))
          @if($maker_details['is_direct_payment'] == '1')                                      
           <span class="actvspans"> Direct Payment :</span> <span class="actvspan"><b>Active</b></span>
          @else              
            <span class="actvspans">Direct Payment:</span> <span class="actvspan desc-red"><b>Deactive</b></span>
          @endif
        @endif
     </div>
</div>
</div>
       
<div class="clearfix"></div>
        
        
         {!! Form::open([ 'url' => $module_url_path.'/update/'.base64_encode($arr_user_data['id']),
         'method'=>'POST',   
         'class'=>'form-horizontal', 
         'id'=>'validation-form',
         'enctype' =>'multipart/form-data'
         ]) !!}


<div class="row">
  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
  <div>
  
         <input type="hidden" name="old_image" value="{{$arr_user_data['profile_image'] or ''}}">
         <div class="form-group row">
            <label for="first_name" class="col-12 col-form-label">First Name<i class="red">*</i></label>
            <div class="col-12">
               <input type="text" class="form-control"  name="first_name" data-parsley-required="true" data-parsley-pattern-message="Only alphabets are allowed" data-parsley-required-message="Please enter first name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed" placeholder="First Name" id="first_name"  value="{{$arr_user_data['first_name'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('first_name') }}</span>
         </div>
         
         <div class="form-group row">
            <label for="last_name" class="col-12 col-form-label">Last Name<i class="red">*</i></label>
            <div class="col-12">
              <input type="text" name="last_name" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter last name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed"  placeholder="Last Name" id="last_name"  value="{{$arr_user_data['last_name'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('last_name') }}</span>
         </div>
         <div class="form-group row">
            <label for="email" class="col-12 col-form-label">Email Id<i class="red">*</i></label>
            <div class="col-12">
              <input type="text" name="email" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" placeholder="Email Id" id="email" value="{{$arr_user_data['email'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('email') }}</span>
         </div>
         
<!--          <div class="form-group row">
            <label for="country_code" class="col-12 col-form-label">Country Code<i class="red">*</i></label>
            <div class="col-12">
              <input type="text" name="country_code" class="form-control" data-parsley-required="true" placeholder="Country Code" id="country_code" value="{{$arr_user_data['country_code'] or ''}}" id ="country_code">
            </div>
              <span class='red'>{{ $errors->first('country_code') }}</span>
         </div>  -->

         <div class="form-group row">
            <label for="country_code" class="col-12 col-form-label">Address<i class="red">*</i></label>
            <div class="col-12">
              <input type="text" name="address" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter address" placeholder="Address" id="address" value="{{$arr_user_data['address'] or ''}}">
            </div>

              <span class='red'>{{ $errors->first('address') }}</span>
         </div>

         <div class="form-group row">
            <label for="country" class="col-12 col-form-label">Country<i class="red">*</i></label>
            <div class="col-12">
              <select name="country_id" id="country_id" data-parsley-required="true" data-parsley-required-message="Please select country" class="form-control">
                        <option value="">Select</option>
                        @if(isset($country_arr) && sizeof($country_arr)>0)
                         @foreach($country_arr as $country)
                            
                            <option value="{{$country['id']}}" @if($country['id']== $arr_user_data['country_id']) selected="true" @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                            
                          @endforeach
                        @endif
                    </select>
            </div>
              <span class='red'>{{ $errors->first('country') }}</span>
         </div>

         <div class="form-group row">
            <label for="mobile_no" class="col-12 col-form-label">Mobile No<i class="red">*</i></label>
            <div class="col-12">

              <input 
              type="text" 
              name="mobile_no" 
              class="form-control" 
              data-parsley-required="true" 
              data-parsley-required-message="Please enter mobile number" 
              placeholder="Mobile No" 
              id="mobile_no" 
              value="{{isset($arr_user_data['country_code'])?trim($arr_user_data['country_code']): ''}}{{isset($arr_user_data['contact_no'])?trim($arr_user_data['contact_no']): ''}}" 
              data-parsley-pattern="^[0-9*#+]+$" 
              data-parsley-pattern-message="Please enter valid mobile number"
            {{--   data-parsley-maxlength="18" --}}
               data-parsley-maxlength-message="Mobile No must be less than 14 digits" {{-- data-parsley-minlength-message="Mobile No should be of 10 digits" --}}>
              <input type="hidden" name="hid_country_code" id="hid_country_code" value="{{isset($arr_user_data['country_code'])?trim($arr_user_data['country_code']): ''}}">
            </div>
               <span class='red'>{{ $errors->first('contact_no') }}</span>
         </div>

          <div class="form-group row">
            <label for="post_code" class="col-12 col-form-label">Zip/Postal Code<i class="red">*</i></label>
            <div class="col-12">
              <input oninput="this.value = this.value.toUpperCase()" type="text" name="post_code" class="form-control" placeholder="Zip/Postal Code" id="post_code" data-parsley-trigger="change" data-parsley-required="true" value="{{$arr_user_data['post_code'] or ''}}" data-parsley-required-message="Please enter zip/postal code">              
              <span id="err_post_code">{{ $errors->first('post_code') }}</span>
            </div>
          </div>

          <div class="form-group row">
            <label for="brand_name" class="col-12 col-form-label">Rejoiz Company Name<i class="red">*</i></label>
            <div class="col-12">
              <input class="form-control" placeholder="Enter Your Rejoiz Company Name" type="text" name="company_name" id="company_name" data-parsley-required="true" data-parsley-required-message="Please enter rejoiz company name" data-parsley-maxlength="50" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="{{ url('/does_company_exist/company-name') }}" data-parsley-remote-options='{ "type": "POST", "dataType": "json", "data": { "_token": "{{ csrf_token() }}","user_id":"{{$maker_details['user_id'] or ''}}" }}'  data-parsley-remote-message="Company name already exists" value="{{$maker_details['company_name'] or ''}} " @if (isset($maker_details['company_name']) && strpos($maker_details['company_name'], 'Rejoiz-iStore') !== false) readonly @endif>
            </div>
              <span class='red'>{{ $errors->first('brand_name') }}</span>
         </div>


         <div class="form-group row">
            <label for="brand_name" class="col-12 col-form-label">Company Name<i class="red">*</i></label>
            <div class="col-12">
              <input class="form-control" placeholder="Enter Your Company Name" type="text" name="real_company_name" id="real_company_name" data-parsley-required="true" data-parsley-required-message="Please enter company name" data-parsley-maxlength="50" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="{{ url('/does_real_company_exist/real-company-name') }}" data-parsley-remote-options='{ "type": "POST", "dataType": "json", "data": { "_token": "{{ csrf_token() }}","user_id":"{{$maker_details['user_id'] or ''}}" }}'  data-parsley-remote-message="Company name already exists" value="{{$maker_details['real_company_name'] or ''}} " >
            </div>
              <span class='red'>{{ $errors->first('real_company_name') }}</span>
         </div>

         <div class="form-group row">
            <label for="website_url" class="col-12 col-form-label">Primary Category<i class="red">*</i></label>
            <div class="col-12">
               <select class="form-control" name="primary_category_id" id="primary_category_id" data-parsley-required="true" data-parsley-required-message="Please select primary category" onchange="func_of_other()">
                      
                      @if(isset($categories_arr) && sizeof($categories_arr)>0)
                        @foreach($categories_arr as $category)

                          <option value="{{$category['id'] }}" @if($category['id']==$maker_details['primary_category_id']) selected="true" @endif cat_name="{{ $category['category_name'] }}">{{$category['category_name'] or ''}}</option>
                        
                        @endforeach
                      @endif
                    </select>
            </div>
              <span class='red'>{{ $errors->first('primary_category') }}</span>
         </div>
       

       @if(isset($maker_details['primary_category_name']) && $maker_details['primary_category_name'] != "")
       <div class="form-group row" id="other_category_div">
            <label for="website_url" class="col-12 col-form-label">Category Name<i class="red">*</i></label>
            <div class="col-12">
              <input type="text" name="other_category_name" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter category name" placeholder="Please enter category name" id="other_category_name" data-parsley-type-message="Please enter category name" value="{{$maker_details['primary_category_name'] or ''}}">
            </div>
              <span class='red'>{{ $errors->first('other_category_name') }}</span>
         </div>

        @else 
        <div class="form-group row" id="other_category_div" style="display: none">
            <label for="website_url" class="col-12 col-form-label">Category Name<i class="red">*</i></label>
            <div class="col-12">
              <input type="text" name="other_category_name" class="form-control input_category" 

               data-parsley-required-message="Please enter category name" placeholder="Please enter category name" id="other_category_name" data-parsley-type-message="Please enter category name" value="{{$maker_details['primary_category_name'] or ''}}">
            </div>
              <span class='red'>{{ $errors->first('other_category_name') }}</span>
         </div>
       @endif
       <input type="hidden" name="other_category_name_insert" id="other_category_name_insert">
   
</div>
</div>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
  <div>
    
         <div class="form-group row">
            <label for="tax_id" class="col-12 col-form-label">Tax Id<!-- <i class="red">*</i> --></label>
            <div class="col-12">
              <input class="form-control" placeholder="Enter Your Tax Id" type="text" name="tax_id" id="tax_id" data-parsley-minlength="9" data-parsley-length-message ="Tax id is invalid,it should have 9 digits" data-parsley-trigger="keyup" 
              data-parsley-type="number" value="{{$arr_user_data['tax_id'] or ''}}" maxlength='9'  data-parsley-required-message="Please enter tax id">
            </div>
              <span class='red' id="err_msg">{{ $errors->first('tax_id') }}</span>
         </div>


         <div class="form-group row">
            <label for="website_url" class="col-12 col-form-label">Website Url <!-- <i class="red">*</i> --></label>
            <div class="col-12">
              <input type="text" name="website_url" class="form-control" {{-- data-parsley-required="true" data-parsley-required-message="Please enter website url" --}} placeholder="Website_url" id="website_url" data-parsley-type="url" data-parsley-type-message="Please enter valid website url" value="{{$maker_details['website_url'] or ''}}">
            </div>
              <span class='red'>{{ $errors->first('website_url') }}</span>
         </div>

         <div class="form-group row">
            <label for="insta_url" class="col-12 col-form-label">Your Social URL</label>
            <div class="col-12">
              <input type="text" name="insta_url" class="form-control" placeholder="Instagram URL" id="insta_url" value="{{$maker_details['insta_url'] or ''}}">
            </div>
              <span class='red'>{{ $errors->first('insta_url') }}</span>
         </div>

         <div class="form-group row">
            <label for="description" class="col-12 col-form-label">Description</label>
            <div class="col-12">
               <input type="text" class="form-control"  name="description" placeholder="Description" id="description" value="{{$maker_details['description'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('description') }}</span>
         </div>

      

         <div class="form-group row">
             <label class="col-12 col-form-label" for="image">Profile Image</label>
              <div class="col-12">
                <span><i class="red">{{-- Note: Image should be 150 * 150 --}}</i></span>
                @php
                  $product_img_path = ""; 
                  $image_name = (isset($arr_user_data['profile_image']))? $arr_user_data['profile_image']:"";
                  $image_type = "user";
                  $is_resize = 0; 
                  $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                @endphp
                <input type="file"  name="image" id="profile_image" data-default-file="{{$product_img_path}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside"  class="dropify" />
              </div>
         </div>
  </div>
</div>
</div>
<div class="">
   <div class="form-group row mt-4">
      <div class="col-sm-12 common_back_save_btn">
        <a class="btn btn-inverse waves-effect waves-light pull-left backbtn" href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}"><i class="fa fa-arrow-left"></i> Back</a>
        <button type="button" style="float: right" id="btn_update" class="btn btn-success waves-effect waves-light" value="Update">Update</button>
      </div>
   </div>    
</div>
 {!! Form::close() !!}
</div>
</div>

<!-- Seperate Stripe fields -->
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Stripe Settings</h4>
   </div>
</div>

<div class="row">
  <div class="col-sm-12"> 
    <div class="white-box">
      {!! Form::open([ 'url' => 
              $module_url_path.'/update_stripe_settings/'.base64_encode($arr_user_data['id']),
              'method'=>'POST',   
              'class'=>'form-horizontal', 
              'id'=>'stripe-validation-form',
              'enctype' =>'multipart/form-data'
            ]) !!}
      <div class="row">
        <div class="col-sm-12 p-0 ">
         <?php
         $secretKey =  isset($strip_arr['secret_key'])?$strip_arr['secret_key']:'';

         $clientId  =  isset($strip_arr['client_id'])?$strip_arr['client_id']:'';
         ?>
        <div class="form-group row">
            <label for="stripe_secret_key" class="col-sm-12 col-md-12 col-lg-12 col-form-label">Stripe Secret key</label>
            <div class="col-sm-12 col-md-12 col-lg-12">
              <!-- <span style="color:#fb3b62;" class="mb-2"><i>Please add carefully, Added key can not able to edit again.</i></span> -->
             <!--    <span style="color:#fb3b62;" class="mb-2"><i>Please add the key carefully. Once added, it cannot be changed.</i></span> -->
              <input type="text"  name="stripe_secret_key" class="form-control"  placeholder="Stripe Secret Key" id="stripe_secret_key" value="{{isset($strip_arr['secret_key'])?$strip_arr['secret_key']:''}}">
            </div>
               <span class='red'>{{ $errors->first('stripe_secret_key') }}</span>
        </div>

         <div class="form-group row">
            <label for="stripe_client_id" class="col-12 col-form-label">Stripe client id</label>
            <div class="col-12">
              <input type="text"  name="stripe_client_id" class="form-control"  placeholder="Stripe Client_id" id="stripe_client_id" value="{{isset($strip_arr['client_id'])?$strip_arr['client_id']:''}}">
            </div>
               <span class='red'>{{ $errors->first('stripe_client_id') }}</span>
        </div>

        <div class="form-group row">
            <label for="account_holder" class="col-12 col-form-label">Account Holder Name</label>
            <div class="col-12">
              <input type="text"  name="account_holder" class="form-control"  placeholder="Account Holder Name" id="account_holder" value="{{isset($strip_arr['account_holder'])?$strip_arr['account_holder']:''}}">
            </div>
               <span class='red'>{{ $errors->first('account_holder') }}</span>
        </div>
      </div>
    </div>
    {!! Form::close() !!}
    <!-- <div class="form-group row">
            <div class="col-12">
              <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{url('/')}}/admin/dashboard"><i class="fa fa-arrow-left"></i> Back</a>
              <button type="button" style="float: right" data-toggle="modal" data-target="#verifyPasswordModal" class="btn btn-success waves-effect waves-light" value="Update">Update</button>
              </div>
    </div> -->
    <div class="row">
      <div class="col-sm-12 common_back_save_btn">
        <a class="btn btn-inverse waves-effect waves-light pull-left backbtn" href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}"><i class="fa fa-arrow-left"></i> Back</a>
        <!-- <button type="button" style="float: right" id="btn_stripe_update" class="btn btn-success waves-effect waves-light" value="Update">Update</button> -->
        <button type="button" style="float: right" data-toggle="modal" data-target="#verifyPasswordModal" class="btn btn-success waves-effect waves-light" value="Update">Update</button>
      </div>
   </div>
  </div>
</div>
</div>
<!-- end -->

<!-- verify password -->
@php
 $obj_data   = Sentinel::getUser();
 $valid_password = $obj_data->password;
@endphp

<div class="modal fade" id="verifyPasswordModal" tabindex="-1" role="dialog" aria-labelledby="verifyPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="verifyPasswordModalLabel">Verify Password</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="password" class="form-control" name="password" placeholder="Enter your password..." id = "password" data-parsley-required="true" 
        data-parsley-required-message="Please insert your password"
        data-parsley-errors-container="#erro_container">

        <input type="hidden" class="form-control" name="valid_password" id = "valid_password" id = "valid_password" value="{{$valid_password}}" >
        <div id = "erro_container">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="verify_password">Verify</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
<!-- end -->

<script>
   var flag = true;
    $(document).ready(function() {
      $("#validation-form").parsley().refresh(); 
    });


 function func_of_other(){
        var cat_id = $("#primary_category_id").val();      
        var cat_name = $("#primary_category_id").find(':selected').attr('cat_name');
        // if(cat_id == '0'){
        //   $("#other_category_div").show();
        //   $("#other_category_div.input_category").attr('data-parsley-required','true');
        // } else {
        //    $("#other_category_div").hide();
        // }
        if (cat_name == 'Other' || cat_name == 'Others') {
            $("#other_category_div").show();
            $('#other_category_name').attr('data-parsley-required', true);
            $("#other_category_name_insert").val(cat_name);

        } else {
            $("#other_category_div").hide();
            $('#other_category_name').attr('data-parsley-required', false);
            $("#other_category_name_insert").val('');
        }
  }

// To fixed code no in mobile no textbox
// By Harshada on date 02 Sept 2020
$("#mobile_no").keydown(function(event){
    var text_length = $("#mobile_no").attr('code_length');
    if(event.keyCode == 8){
        this.selectionStart--;
    }
    if(this.selectionStart < text_length){
        this.selectionStart = text_length;
        console.log(this.selectionStart);
        event.preventDefault();
    }
});
$("#mobile_no").keyup(function(event){
    console.log(this.selectionStart);
    if(this.selectionStart < text_length){
        this.selectionStart = text_length;
        console.log(this.selectionStart);
        event.preventDefault();
    }
});

/*$( document ).ready(function(){
  $("#country_id").onchange(function(event) {
   var country_code = $("#country_id").val();

  if(country_code=='2')
  {
      $("#post_code").attr({"data-parsley-type":'integer',
                                  "data-parsley-length":'[5,5]',
                                  "data-parsley-length-message":'Only allowed 5 digits',
                                  "data-parsley-type-message":'Zip/Postal code can only be numbers'
                                });
  }  
  else
    {
      $("#post_code").attr({"data-parsley-type":'alphanum',
                                  "data-parsley-length":'[6,6]',
                                  "data-parsley-length-message":'Only allowed 6 characters',
                                  "data-parsley-type-message":'Zip/Postal code can only be alphanumeric characters'
                                });
    }
    $('#validation-form').parsley();
  
  });
  
});*/


  $(document).ready(function(){

      var phone_code   = $('#country_id option:selected', this).attr('phone_code');      
      $("#mobile_no").attr('code_length',phone_code.length+1); 
      
      $("#country_id").change(function(){

      var codeLength = jQuery('#hid_country_code').val();
      var minPhone = 7 + codeLength.length;            
      $('#contact_no').attr('data-parsley-minlength', minPhone);
      $('#contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

      var maxPhone = 14 + codeLength.length;            
      $('#contact_no').attr('data-parsley-maxlength', maxPhone);  
      
      var phone_code   = $('option:selected', this).attr('phone_code');
      if(phone_code){
        $("#mobile_no").val("+"+phone_code);
        $("#mobile_no").attr('code_length',phone_code.length+1);  
        $("#hid_country_code").val('+'+phone_code);
      } else {
        $("#mobile_no").val('');
        $("#mobile_no").attr(0); 
        $("#hid_country_code").val('');
     }  
      validate_pin_or_zip_code();

    });

  function validate_pin_or_zip_code(){
  
      var country_code = $("#country_id").val();
      var post_code    = $("#post_code").val();

      if(country_code=="" && post_code!="")
      {
         $("#err_post_code").html('Invalid zip/postal code');        
 
      }

      var zipcode_length = $('option:selected', "#country_id").attr('zipcode_length');
      var countryName = $('option:selected', "#country_id").attr('country_name');

      var codeLength = jQuery('#hid_country_code').val();
      var minPhone = 7 + codeLength.length;            
      $('#mobile_no').attr('data-parsley-minlength', minPhone);
      $('#mobile_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

      var maxPhone = 14 + codeLength.length;            
      $('#mobile_no').attr('data-parsley-maxlength', maxPhone);

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
  }
});

$('#tax_id').keyup(function(){
        
        var tax_id = $("#tax_id").val();
       
        var module_url_path = '{{url('vendor/does_exists_tax_id/')}}/'+btoa(tax_id);

        $.ajax({
          url: module_url_path,
          type:"get",
          dataType:'json',
          success:function(data)
          {
            if(data.status == 'error')
            { 
               $('#err_msg').html('Tax id is already available.');
               flag = false;
            }
            else
            {
              $('#err_msg').html('');
              // $('#err_msg').hide();
              flag = true;
            }
          }
        });
        });

$('#btn_update').click(function(){

   var country_code = $("#country_id").val();
   var post_code    = $("#post_code").val();

   if(country_code!="" && post_code=="")
   { 
     $("#err_post_code").html("");
   }

   else if(country_code=="" && post_code=="")
   {
     $("#err_post_code").html(""); 
   }

  else if(country_code!="" && post_code!="")
   {
     $("#err_post_code").html(""); 
   } 

   else if(country_code=="" && post_code!="")
   {
     $("#err_post_code").html("Invalid zip/postal code");
   }

   if($('#validation-form').parsley().validate()==false)
  {
    hideProcessingOverlay();
    return; 
  }

   // validate phone code zip code
   var zipcode_length = $('#country_id option:selected').attr('zipcode_length');
    var countryName = $('#country_id option:selected').attr('country_name');

    var codeLength = jQuery('#hid_country_code').val();
    var minPhone = 10 + codeLength.length;            
    $('#mobile_no').attr('data-parsley-minlength', minPhone);

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

  
 // if(flag == false || $('#validation-form').parsley().validate()==false)
 if($('#validation-form').parsley().validate()==false)
 {
   hideProcessingOverlay();
   return; 
 }
 else
 {
    showProcessingOverlay();   
   $('#validation-form').submit();
 }
});


/*verify password*/
$("#verify_password").click(function(){
    
    $('#password').parsley().validate();
    var valid_password = $("#valid_password").val();
    var password = $("#password").val();
    var email = "{{$obj_data->email}}";
    
    if ($('#password').parsley().isValid()==false) 
    {
        return;
        
    } 
    else
    {
      verify_password(password,email);
    }
  });

$('#btn_stripe_update').click(function(){
  if($('#stripe-validation-form').parsley().validate()==false)
 {
   hideProcessingOverlay();
   return; 
 }
 else
 {
    showProcessingOverlay();   
   $('#stripe-validation-form').submit();
 }
});

  function verify_password(password=false,email=false)
  {
    swal({
         title: "Need Confirmation",
         text: "Are you sure? Once you change stripe settings your current customer,accounts,card will moved to new stripe account ",
         type: "warning",
         showCancelButton: true,
         confirmButtonClass: "btn-danger",
         confirmButtonText: "OK",
         closeOnConfirm: false
       },
       function(){
        var module_url_path = "{{$module_url_path}}";
        var csrf_token = $("input[name=_token]").val();
         
         $.ajax({
             url:module_url_path+'/verify_password',
             type: 'POST',
             headers: {
               'X-CSRF-TOKEN': csrf_token
             },
             data:{'password':password,'email':email},
           
             dataType:'json',
             beforeSend: function() 
            {
              showProcessingOverlay();                 
            },
             success:function(response)
             {
                 // hideProcessingOverlay();  
                 if(response.status =='success')
                 {   
                   if($('#stripe-validation-form').parsley().validate()==false)
                  {  
                    hideProcessingOverlay();
                  }
                  else
                  { 
                    showProcessingOverlay();
                    $("#stripe-validation-form").submit();

                  }    
                 }
                 else
                 {
                  location.reload(); 
                  scroll(0,0);
                 }
             }
         });
       });
  }

</script>


<!-- END Main Content --> 
@endsection