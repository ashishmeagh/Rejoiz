@extends('influencer.layout.master')    
@section('main_content')
<style>
   #err_post_code{
   margin: 10px 0 10px;
   padding: 0; 
   font-size: 0.9em;
   line-height: 0.9em;
   color: red;
   }
   .form-group {width:auto;}
</style>


<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$module_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.influencer_panel_slug').'/dashboard') }}">Dashboard</a></li>
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
          @include('influencer.layout._operation_status')
          {{-- <div class="box-title">
             <h3><i class="ti-settings"></i> {{ isset($page_title)?$page_title:"" }}</h3>
             <div class="box-tool">
             </div>
          </div> --}}
          <form id="validation-form">
             {{ csrf_field() }}
             <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                  
                      <input type="hidden" name="old_image" value="{{$arr_user_data['profile_image'] or ''}}">
                      <div class="form-group row">
                         <label for="first_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label">First Name<i class="red">*</i></label>
                         <div class="col-sm-12 col-md-12 col-lg-9">
                            <input type="text" 
                                   class="form-control"  
                                   name="first_name" 
                                   id="first_name"  
                                   data-parsley-required="true" 
                                   data-parsley-required-message="Please enter first name" data-parsley-pattern="^[a-zA-Z ]+$" 
                                   data-parsley-pattern-message="Only alphabets are allowed" 
                                   placeholder="First Name" 
                                   value="{{$arr_user_data['first_name'] or ''}}">
                         </div>
                         <span class='red'>{{ $errors->first('first_name') }}</span>
                      </div>

                      <div class="form-group row">
                         <label for="last_name" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Last Name<i class="red">*</i></label>
                         <div class="col-sm-12 col-md-12 col-lg-9">
                            <input type="text" 
                                   name="last_name" 
                                   id="last_name"  
                                   class="form-control" 
                                   data-parsley-required="true" 
                                   data-parsley-required-message="Please enter last name" data-parsley-pattern="^[a-zA-Z ]+$" 
                                   data-parsley-pattern-message="Only alphabets are allowed"  
                                   placeholder="Last Name" 
                                   value="{{$arr_user_data['last_name'] or ''}}">
                         </div>
                         <span class='red'>{{ $errors->first('last_name') }}</span>
                      </div>
                      <div class="form-group row">
                         <label for="email" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Email Id<i class="red">*</i></label>
                         <div class="col-sm-12 col-md-12 col-lg-9">
                            <input type="text" 
                                   name="email" 
                                   id="email" 
                                   class="form-control" 
                                   data-parsley-required="true" 
                                   readonly="true" 
                                   data-parsley-required-message="Please enter email address" data-parsley-type="email" 
                                   data-parsley-type-message="Please enter valid email address" 
                                   placeholder="Email" 
                                   value="{{$arr_user_data['email'] or ''}}">
                         </div>
                         <span class='red'>{{ $errors->first('email') }}</span>
                      </div>
                      
                      <div class="form-group row">
                         <label for="country_id" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Country<i class="red">*</i></label>
                         <div class="col-sm-12 col-md-12 col-lg-9">
                            <select name="country_id" 
                                     id="country_id" 
                                     data-parsley-required="true" 
                                     data-parsley-required-message="Please select country" 
                                     class="form-control">
                                     <option value="">Select</option>
                               @if(isset($arr_country) && sizeof($arr_country)>0)
                                  @foreach($arr_country as $country)
                                     <option value="{{$country['id']}}" @if($country['id']== $arr_user_data['country_id']) selected="true" @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                                  @endforeach
                               @endif
                            </select>
                         </div>
                         <span class='red'>{{ $errors->first('country_id') }}</span>
                      </div>

                      <div class="form-group row">
                         <label for="contact_no" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Mobile No<i class="red">*</i></label>
                         <div class="col-sm-12 col-md-12 col-lg-9">
                            <input type="text" 
                                   name="contact_no" 
                                   id="contact_no" 
                                   class="form-control" 
                                   data-parsley-required="true" 
                                   data-parsley-required-message="Please enter mobile number" 
                                   placeholder="Mobile No" 
                                   value="{{isset($arr_user_data['country_code'])?trim($arr_user_data['country_code']): ''}}{{isset($arr_user_data['contact_no'])?trim($arr_user_data['contact_no']): ''}}" 
                                   {{-- data-parsley-minlength-message="Mobile No should be of 10 digits"
                                   data-parsley-maxlength="18" --}}

                                  data-parsley-pattern="^[0-9*#+]+$" 
                                  data-parsley-pattern-message="Please enter valid mobile number"
                                   data-parsley-maxlength-message="Mobile No must be less than 18 digits">
                                   <!-- data-parsley-type="number"  -->
                                    <input type="hidden" name="hid_country_code" id="hid_country_code" value="{{isset($arr_user_data['country_code'])?trim($arr_user_data['country_code']): ''}}">
                         </div>
                         <span class='red'>{{ $errors->first('contact_no') }}</span>
                      </div>
                      <div class="form-group row">
                         <label for="post_code" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Zip/Postal Code<i class="red">*</i></label>
                         <div class="col-sm-12 col-md-12 col-lg-9">                            
                               <input type="text" 
                               oninput="this.value = this.value.toUpperCase()"
                               name="post_code" 
                               id="post_code"   
                               class="form-control" 
                               placeholder="Zip/Postal Code"                                 
                               data-parsley-trigger="change" 
                               data-parsley-required="true" 
                               value="{{$arr_user_data['post_code'] or ''}}" 
                               data-parsley-required-message="Please enter zip/postal code"
                              >
                            
                            <span id="err_post_code">{{ $errors->first('post_code') }}</span>
                         </div>
                      </div>

                      @php
                        if(isset($arr_user_data['profile_image']) || 
                           $arr_user_data['profile_image'] != '' || 
                           $arr_user_data['profile_image'] != null)
                        {
                          $profile_image = url('/').'/storage/app/'.$arr_user_data['profile_image'];
                        }
                        else
                        {
                          $profile_image = url('/').'/assets/images/default_images/user-no-img.jpg';
                        }
                      @endphp

                      <div class="form-group row">
                         <label class="col-sm-12 col-md-12 col-lg-3 col-form-label" for="profile_image">Profile Image</label>
                         <div class="col-sm-12 col-md-12 col-lg-9">
                            <span><i class="red">{{-- Note: Image should be 150 * 150 --}}</i></span>
                            <input type="file"  
                                   name="profile_image" 
                                   id="profile_image" 
                                   data-default-file="{{ $profile_image or ''}}" 
                                   data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                   data-max-file-size="2M" 
                                   data-errors-position="outside"  
                                   class="dropify" />
                         </div>
                      </div>
                   
                </div>
             </div>
             <div class="col-md-12">
                <div class="form-group row">
                   <div class="col-sm-12 common_back_save_btn p-0">
                      <a class="btn btn-inverse waves-effect waves-light pull-left backbtn" href="{{ url(config('app.project.influencer_panel_slug').'/dashboard') }}"><i class="fa fa-arrow-left"></i> Back</a>
                      <button type="button" style="float: right" id="btn_update" class="btn btn-success waves-effect waves-light" value="Update">Update</button>
                   </div>
                </div>
             </div>
          </form>
    </div>
   </div>
</div>
<script>
   
var module_url_path = '{{$module_url_path or ''}}'; 
   
$(document).ready(function(){

  assign_country_code_on_load();

// To fixed code no in mobile no textbox
// By Harshada on date 02 Sept 2020
$("#contact_no").keydown(function(event){
    var text_length = $("#contact_no").attr('code_length');
    if(event.keyCode == 8){
        this.selectionStart--;
    }
    if(this.selectionStart < text_length){
        this.selectionStart = text_length;
        console.log(this.selectionStart);
        event.preventDefault();
    }
});
$("#contact_no").keyup(function(event){
    console.log(this.selectionStart);
    if(this.selectionStart < text_length){
        this.selectionStart = text_length;
        console.log(this.selectionStart);
        event.preventDefault();
    }
});


    var phone_code   = $('#country_id option:selected', this).attr('phone_code');      
    $("#contact_no").attr('code_length',phone_code.length+1); 
    $("#country_id").change(function(){
      var phone_code   = $('option:selected', this).attr('phone_code');
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

      $('#contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

      var maxPhone = 14 + codeLength.length;            
      $('#contact_no').attr('data-parsley-maxlength', maxPhone);

      validate_pin_or_zip_code();
    });
   
   function validate_pin_or_zip_code(){
   
      var country_code = $("#country_id").val();
      var post_code    = $("#post_code").val();
   
      if(country_code=="" && post_code!=""){
         $("#err_post_code").html('Invalid zip/postal code');        
   
      }

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
   
      $('#validation-form').parsley();
   }
});
   
   
   
   $('#btn_update').click(function(){
   
      var country_code = $("#country_id").val();
      var post_code    = $("#post_code").val();
   
      if(country_code!="" && post_code==""){ 
         $("#err_post_code").html("");
      } else if(country_code=="" && post_code==""){
         $("#err_post_code").html(""); 
      } else if(country_code!="" && post_code!=""){
         $("#err_post_code").html(""); 
      } else if(country_code=="" && post_code!=""){
         $("#err_post_code").html("Invalid zip/postal code");
      }
    
      if($('#validation-form').parsley().validate()==false) return;


      var formdata = new FormData($("#validation-form")[0]);
        
      $.ajax({
            url: module_url_path+'/update',
            type:"POST",
            data: formdata,
            contentType:false,
            processData:false,
            dataType:'json',
            beforeSend: function() 
            {
              showProcessingOverlay();                 
            },
            success:function(response)
            {
               hideProcessingOverlay();

               if('success' == response.status){
                  swal('Success',response.description,response.status);
               } else {
                  swal('Error',response.description,response.status);
               }     
            }
      });  
   });

  /* function to assign min length on page load */
  function assign_country_code_on_load(){  
      // For personal contact no
     var personal_phone_code   = $('#country_id option:selected').attr('phone_code');
    
      if(personal_phone_code){        
        $("#contact_no").attr('code_length',personal_phone_code.length+1);  
        $("#hid_country_code").val('+'+personal_phone_code);
      } else {       
        $("#contact_no").attr(0); 
        $("#hid_country_code").val('');
     }  
      
      var personal_codeLength = $('#hid_country_code').val();
      var personal_minPhone = 7 + personal_codeLength.length;            
      $('#contact_no').attr('data-parsley-minlength', personal_minPhone);

      $('#contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

      var maxPhone = 14 + codeLength.length;            
      $('#contact_no').attr('data-parsley-maxlength', maxPhone);
  }
   
</script>
<!-- END Main Content --> 
@endsection