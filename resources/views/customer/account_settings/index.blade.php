@extends('customer.layout.master')    
@section('main_content')
<!-- Page Content -->
<style>
  li.resp-tab-item.d-pointnone {
    pointer-events: none !important;
    opacity: 0.3;
}
.resp-tab-content .form-group {width:auto;}
.err_zip_code{
    margin: 10px 0 10px;
    padding: 0; 
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;
  }

  .modal-dialog {
    max-width: 760px;
    margin:70px auto 0;
}
.col-category {
    width: 25%;
}

.dropify-wrapper input {z-index:0 !important;}

</style>

<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$module_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{url('/')}}/customer/dashboard">Dashboard</a></li>
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
    @include('customer.layout._operation_status')
<div class="col-md-12">
  <div id="error-msg"></div>
</div>

<div class="col-md-12">
  <div class="tabbing_area">
    <div id="horizontalTab">
      <ul class="resp-tabs-list">
        <li id="tab1">Personal Information</li>
      
        {{-- <li  id="tab2"><span>Profile Picture</span></li> --}}
        <li  id="tab3"><span>Shipping/Billing Address</span></li>

      </ul>
      
      <div class="resp-tabs-container">
        <!--tab-1 start-->
        <div id="first_form">
          <form id="tab1_form">
            {{csrf_field()}}

          <input type="hidden" name="tab1" id="tab1" value="1">  

          <div class="row">
            <div class="col-md-6">
              <div class="white-box">
                 {{csrf_field()}}
                  
                  <div class="form-group row">
                     <label for="first_name" class="col-md-12 col-form-label">First Name<i class="red">*</i></label>
                     <div class="col-md-12 col-xs-12">
                        <input type="text" class="form-control"  name="first_name" data-parsley-required="true" data-parsley-required-message="Please enter first name" placeholder="First Name" id="first_name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid first name" value="{{$arr_user_data['first_name'] or ''}}">
                     </div>
                     <span class='red'>{{ $errors->first('first_name') }}</span>
                  </div>
                  <div class="form-group row">
                     <label for="last_name" class="col-md-12 col-form-label">Last Name<i class="red">*</i></label>
                     <div class="col-md-12">
                        <input type="text" name="last_name" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter last name"  placeholder="Last Name" id="last_name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid last name" value="{{$arr_user_data['last_name'] or ''}}">
                     </div>
                     <span class='red'>{{ $errors->first('last_name') }}</span>
                  </div>
                  <div class="form-group row">
                     <label for="email" class="col-md-12 col-form-label">Email Id<i class="red">*</i></label>
                     <div class="col-md-12">
                        <input type="text" name="email" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" placeholder="Email" id="email" value="{{$arr_user_data['email'] or ''}}" readonly="">
                     </div>
                     <span class='red'>{{ $errors->first('email') }}</span>
                  </div>

                   

                  <div class="form-group row">
                     <label for="city" class="col-md-12 col-form-label">City<i class="red">*</i></label>
                     <div class="col-md-12">
    
                       <input type="text"  name="city" id="city" class="form-control" placeholder="City" data-parsley-required="true" data-parsley-required-message="Please enter city" value="{{$arr_user_data['customer_details']['city'] or ''}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">

                     </div>
                     <span class='red'>{{ $errors->first('city') }}</span>
                  </div>
       


                  <div class="form-group row">
                     <label for="state" class="col-md-12 col-form-label">State<i class="red">*</i></label>
                     <div class="col-md-12">

                      <input type="text" name="state" id="state" class="form-control" Placeholder="State" data-parsley-required="true" data-parsley-required-message="Please enter state" value="{{$arr_user_data['customer_details']['state'] or ''}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">
                     </div>
                     <span class='red'>{{ $errors->first('state') }}</span>
                  </div>


                  <div class="form-group row">
                            <label for="country" class="col-md-12 col-form-label">Country<i class="red">*</i></label>
                            <div class="col-md-12">
                              <select name="country_id" id="country_id" data-parsley-required="true" class="form-control" data-parsley-required-message="Please select country">
                                        <option value="">Select Country</option>
                                        @if(isset($country_data) && sizeof($country_data)>0)
                                         @foreach($country_data as $country)
                                            
                                            <option value="{{$country['id']}}" @if($country['id']== $arr_user_data['country_id']) selected="true" @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                                          @endforeach
                                        @endif
                                    </select>
                            </div>
                              <span class='red'>{{ $errors->first('country_id') }}</span>
                  </div>

                  <div class="form-group row">
                     <label for="email" class="col-md-12 col-form-label">Mobile No.<i class="red">*</i></label>
                     <div class="col-md-12">
                        <input 
                        type="text" 
                        name="contact_no" 
                        class="form-control contact_no" 
                        data-parsley-required="true" 
                        data-parsley-required-message="Please enter mobile number" 
                        placeholder="Mobile No." 
                        id="contact_no" 
                        value="{{isset($arr_user_data['country_code'])?trim($arr_user_data['country_code']): ''}}{{$arr_user_data['contact_no'] or ''}}" 
                       
                        data-parsley-maxlength-message="Mobile No must be less than 14 digits" data-parsley-pattern="^[0-9*#+]+$" 
                        data-parsley-required >
                        <input type="hidden" name="hid_country_code" id="hid_country_code" value="{{isset($arr_user_data['country_code'])?trim($arr_user_data['country_code']): ''}}">
                       <!--  data-parsley-type="number"  -->
                     </div>
                     <span class='red'>{{ $errors->first('contact_no') }}</span>
                  </div>

                   <div class="form-group row">
                    <label for="post_code" class="col-md-12 col-form-label">Zip/Postal Code<i class="red">*</i></label>
                    <div class="col-md-12">                    
                        <input oninput="this.value = this.value.toUpperCase()" type="text" name="post_code" class="form-control" placeholder="Zip/Postal Code" data-parsley-trigger="change" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" value="{{$arr_user_data['post_code'] or ''}}" id="pin_or_zip_code"> 
                        <span class="err_zip_code" id="err_zip_code">{{ $errors->first('post_code') }}</span>
                    </div>
                  </div>

                   <div class="form-group row">
                     <label for="address" class="col-md-12 col-form-label">Street Address<i class="red">*</i></label>
                     <div class="col-md-12">

                       <textarea type="text" name="address"  class="form-control" placeholder="Street Address" id="address" data-parsley-required="true" data-parsley-required-message="Please enter street address ">{{$arr_user_data['address'] or ''}}</textarea>
                     </div>
                     <span class='red'>{{ $errors->first('address') }}</span>
                  </div>
                  <div class="form-group row">
                     <label for="address2" class="col-md-12 col-form-label">Suite/Apt</label>
                     <div class="col-md-12">

                       <textarea type="text" name="address2"  class="form-control" placeholder="Suite/Apt" id="address2">{{$arr_user_data['customer_details']['address2'] or ''}}</textarea>

                     </div>
                     <span class='red'>{{ $errors->first('address2') }}</span>
                  </div>

              </div>
            </div>

            <div class="col-md-6">
              <input type="hidden" name="tab2" id="tab2" value="2">
              <input type="hidden" name="old_image" value="{{$arr_user_data['profile_image'] or ''}}">
              <input type="hidden" name="old_store_logo" value="{{ $store_logo or '' }}">

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
              <div class="white-box">
                    
                  <div class="form-group row">
                     <label class="col-md-12 col-form-label" for="ad_image">Profile Image</label>
                     <div class="col-md-12">
                        <input type="file" name="image" id="ad_image" class="dropify"  data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" data-default-file="{{ $profile_image or ''}}"/>
                        <span><i class="red">{{-- Note: Image should be 150 * 150 --}}</i></span>
                     </div>
                  </div>
                  <div class="form-group row">
                    <div class="col-md-12">
                      <button type="button" id="btn_tab1_update" name="btn_tab1_update" class="btn btn-success waves-effect waves-light m-r-10" value="Update">Update</button>
                    </div>
                </div>

              </div>
            </div>
            
            
        </div>
          </form>
        </div>

        <!--tab-2 start-->   
        {{-- <div id="second_form">
          <form id="tab2_form" method="POST" action="{{$module_url_path.'/update/'.base64_encode($arr_user_data['id'])}}"> 
           {{csrf_field()}}
            
          </form>
        </div> --}}

        <!--tab-3 start-->
        <div id="third_form">
          <form id="tab3_form" method="POST" action="{{$module_url_path.'/update/'.base64_encode($arr_user_data['id'])}}">
             {{csrf_field()}}
            <input type="hidden" name="tab3" id="tab3" value="3">
          <div class="row">

            <div class="col-md-6">
            
              <div class="white-box">
                <h4><b>Shipping Address</b></h4>

                  <div class="form-group row">
                    <label for="shipping_addr" class="col-md-12 col-form-label">Street Address<i class="red">*</i></label>
                    <div class="col-md-12">
                    <textarea  name="shipping_addr" class="form-control" id="shipping_addr" data-parsley-required="true" data-parsley-required-message="Please enter street address" placeholder="Street Address">{{$arr_user_data['customer_details']['shipping_addr'] or ''}}</textarea>
                    </div>
                    <span class='red'>{{ $errors->first('shipping_addr') }}</span>

                  </div>

                  <div class="form-group row">
                     <label for="address2" class="col-md-12 col-form-label">Suite/Apt</label>
                     <div class="col-md-12">

                       <textarea type="text" name="shipping_address2"  class="form-control" placeholder="Suite/Apt" id="shipping_address2">{{$arr_user_data['customer_details']['shipping_suit_apt'] or ''}}</textarea>

                     </div>
                     <span class='red'>{{ $errors->first('address2') }}</span>
                  </div>


                  <div class="form-group row">
                    <label for="city" class="col-md-12 col-form-label">City<i class="red">*</i></label>
                      <div class="col-md-12">
    
                      <input type="text"  name="shipping_city" id="shipping_city" class="form-control" placeholder="City" data-parsley-required="true" data-parsley-required-message="Please enter city" value="{{$arr_user_data['customer_details']['shipping_city'] or ''}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">

                      </div>
                      <span class='red'>{{ $errors->first('shipping_city') }}</span>
                  </div>
       

                  <div class="form-group row">
                    <label for="state" class="col-md-12 col-form-label">State<i class="red">*</i></label>
                      <div class="col-md-12">

                      <input type="text" name="shipping_state" id="shipping_state" class="form-control" Placeholder="State" data-parsley-required="true" data-parsley-required-message="Please enter state" value="{{$arr_user_data['customer_details']['shipping_state'] or ''}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">
                      </div>
                      <span class='red'>{{ $errors->first('shipping_state') }}</span>
                  </div>



                  <div class="form-group row">
                     <label for="state" class="col-md-12 col-form-label">Country<i class="red">*</i></label>
                     <div class="col-md-12">

                        <select class="form-control" id="shipping_country" name="shipping_country" data-parsley-required="true" data-parsley-required-message="Please select country">
                        <option value="">Select Country</option>

                        @if(isset($country_data) && count($country_data)>0)
                        @foreach($country_data as $key=>$country)
                          <option value="{{$country['id']}}" @if($country['id'] ==$arr_user_data['customer_details']['shipping_country']) selected @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name']}}</option>
                          
                          @endforeach
                        @php
                          $phone_code = "";
                          $phone_code = isset($arr_user_data['customer_details']['shipping_country']) ? $arr_user_data['customer_details']['shipping_country'] : "";
                          @endphp
                        <input type="hidden" name="hid_ship_contact_no_country_code" id="hid_ship_contact_no_country_code" value="{{ $phone_code }}">
                        @endif
                      </select>

                     </div>

                       <span class='red'>{{ $errors->first('shipping_country') }}</span>
                  </div>

                  <div class="form-group row">
                    <label for="shipping_zip_code" class="col-md-12 col-form-label">Zip/Postal Code<i class="red">*</i></label>

                 <!--    @if(isset($arr_retailer_data['shipping_country']) && $arr_retailer_data['shipping_country'] ==1)

                    <div class="col-md-12">
                      <input type="text" name="shipping_zip_code" class="form-control" placeholder="Zip/Postal Code" id="shipping_zip_code" value="{{$arr_user_data['customer_details']['shipping_zip_postal_code'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code." data-parsley-trigger="change" data-parsley-length="[6,6]" data-parsley-length-message ="Zip/Postal code should be 6 characters long." data-parsley-type="integer" data-parsley-type-message="Zip/Postal code can only be alphanumeric characters.">
                  
                    </div>

                    @else

                    <div class="col-md-12">
                      <input type="text" name="shipping_zip_code" class="form-control" placeholder="Zip/Postal Code" id="shipping_zip_code" value="{{$arr_user_data['customer_details']['shipping_zip_postal_code'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter Zip/Postal code." data-parsley-trigger="change" data-parsley-length="[5,5]" data-parsley-length-message ="Zip/Postal code should be 5 digits long." data-parsley-type="integer"  data-parsley-type-message="Zip/Postal code can only be numbers.">
                  
                    </div>

                    @endif  -->


                    <div class="col-md-12">
                      <input oninput="this.value = this.value.toUpperCase()" type="text" name="shipping_zip_code" class="form-control" placeholder="Zip/Postal Code" id="shipping_zip_code" value="{{$arr_user_data['customer_details']['shipping_zip_postal_code'] or ''}}" data-parsley-required="true" data-parsley-required-message="Please enter Zip/Postal code" data-parsley-trigger="change">
                      <span class='err_zip_code' id="err_shipping_zip_code">{{ $errors->first('shipping_zip_code') }}</span>
                    </div>
                  </div>

                  
                    <div class="form-group row">
                      <label for="shipping_zip_code" class="col-md-12 col-form-label">Mobile Number<i class="red">*</i></label>
                      <?php $shipPhoneCode = substr($arr_user_data['customer_details']['ship_contact_no'],0,3) ?>
                       <div class="col-md-12">
                          <input 
                          type="text" 
                          name="ship_contact_no" 
                          class="form-control" 
                          data-parsley-required="true" 
                          data-parsley-required-message="Please enter mobile number" 
                          placeholder="Mobile No." 
                          id="ship_contact_no" 
                          value="{{$arr_user_data['customer_details']['ship_contact_no'] or ''}}" {{-- data-parsley-maxlength="18"  --}}
                          data-parsley-maxlength-message="Mobile number must be less than 14 digits" {{-- data-parsley-minlength-message="Mobile number should be of 10 digits"  --}}data-parsley-pattern="^[0-9*#+]+$" 
                          data-parsley-required 
                          data-parsley-pattern-message="Please enter valid mobile number">
                          
                        </div>
                       <span class='red'>{{ $errors->first('ship_contact_no') }}</span>
                    </div>

              </div>
            </div>
            <div class="col-md-6">
                <div class="white-box">
                   <h4><b>Billing Address</b></h4>
                  <div class="form-group row">
                    
                    <div class="col-sm-12">
                    <input type="checkbox" name="same_add" id="same_add"> Same as Shipping Address
                    </div>

                     <label for="billing_addr" class="col-md-12 col-form-label">Street Address<i class="red">*</i></label>
                     <div class="col-md-12">
                      <textarea  name="billing_addr" class="form-control" id="billing_addr" data-parsley-required="true" data-parsley-required-message="Please enter street address" placeholder="Street Address" >{{$arr_user_data['customer_details']['billing_address'] or ''}}</textarea>
                     </div>
                     <span class='red'>{{ $errors->first('billing_addr') }}</span>
                  </div>

                  <div class="form-group row">
                     <label for="address2" class="col-md-12 col-form-label">Suite/Apt</label>
                     <div class="col-md-12">

                       <textarea type="text" name="billing_address2"  class="form-control" placeholder="Suite/Apt" id="billing_address2">{{$arr_user_data['customer_details']['billing_suit_apt'] or ''}}</textarea>

                     </div>
                     <span class='red'>{{ $errors->first('address2') }}</span>
                  </div>

                  <div class="form-group row">
                    <label for="city" class="col-md-12 col-form-label">City<i class="red">*</i></label>
                      <div class="col-md-12">
    
                      <input type="text"  name="billing_city" id="billing_city" class="form-control" placeholder="City" data-parsley-required="true" data-parsley-required-message="Please enter city" value="{{$arr_user_data['customer_details']['billing_city'] or ''}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">

                      </div>
                      <span class='red'>{{ $errors->first('billing_city') }}</span>
                  </div>
       

                  <div class="form-group row">
                    <label for="shipping_state" class="col-md-12 col-form-label">State<i class="red">*</i></label>
                      <div class="col-md-12">

                      <input type="text" name="billing_state" id="billing_state" class="form-control" Placeholder="State" data-parsley-required="true" data-parsley-required-message="Please enter state" value="{{$arr_user_data['customer_details']['billing_state'] or ''}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed">
                      </div>
                      <span class='red'>{{ $errors->first('billing_state') }}</span>
                  </div>


                  <div class="form-group row">
                     <label for="state" class="col-md-12 col-form-label">Country<i class="red">*</i></label>
                     <div class="col-md-12">

                        <select class="form-control" id="billing_country" name="billing_country" data-parsley-required="true" data-parsley-required-message="Please select country">
                        <option value="">Select Country</option>

                        @if(isset($country_data) && count($country_data)>0)
                        @foreach($country_data as $key=>$country)
                          <option value="{{$country['id']}}" @if($country['id'] == $arr_user_data['customer_details']['billing_country']) selected @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name']}}</option>
                        @endforeach
                        @php
                          $phone_code = "";
                          $phone_code = isset($arr_user_data['customer_details']['billing_country']) ? $arr_user_data['customer_details']['billing_country'] : "";
                          @endphp
                         <input type="hidden" name="hid_bill_contact_no_country_code" id="hid_bill_contact_no_country_code" value="<?= $phone_code ?>">
                        @endif
                      </select>

                     </div>

                       <span class='red'>{{ $errors->first('billing_country') }}</span>
                  </div>

                  <div class="form-group row">
                     <label for="billing_zip_code" class="col-md-12 col-form-label">Zip/Postal Code<i class="red">*</i></label>

                  <!--   @if(isset($arr_retailer_data['billing_country']) && $arr_retailer_data['billing_country'] ==1)

                      <div class="col-md-12">
                      <input type="text" name="billing_zip_code" class="form-control" placeholder="Zip/Postal Code" id="billing_zip_code" value="{{$arr_user_data['customer_details']['billing_zip_postal_code'] or ''}}" data-parsley-required="true" data-parsley-trigger="change" 
                      data-parsley-required-message="Please enter zip/postal code." data-parsley-type-message="Zip/Postal code can only be alphanumeric characters." data-parsley-length="[6,6]" data-parsley-length-message ="Zip/Postal should be 6 characters long." data-parsley-type="alphanum">
                      </div>
                    @else
                      <div class="col-md-12">
                      <input type="text" name="billing_zip_code" class="form-control" placeholder="Zip/Postal Code" id="billing_zip_code" value="{{$arr_user_data['customer_details']['billing_zip_postal_code'] or ''}}" data-parsley-required="true"  data-parsley-required-message="Please enter zip/postal code." data-parsley-type-message="Zip/Postal code can only be numbers." data-parsley-trigger="change" data-parsley-length="[5,5]" data-parsley-length-message ="Zip/Postal code should be 5 digits long." data-parsley-type="integer">
                      </div>

                    @endif -->

                    <div class="col-md-12">
                      <input oninput="this.value = this.value.toUpperCase()" type="text" name="billing_zip_code" class="form-control" placeholder="Zip/Postal Code" id="billing_zip_code" value="{{$arr_user_data['customer_details']['billing_zip_postal_code'] or ''}}">
                    

                      <span class='err_zip_code' id="err_billing_zip_code">{{ $errors->first('billing_zip_code') }}</span>
                    </div>
                  </div>



                  <div class="form-group row">
                    <label for="shipping_zip_code" class="col-md-12 col-form-label">Mobile Number<i class="red">*</i></label>
                    <?php $billPhoneCode = substr($arr_user_data['customer_details']['bill_contact_no'],0,3) ?>
                      <div class="col-md-12">
                          <input type="text" 
                          name="bill_contact_no" 
                          class="form-control" 
                          data-parsley-required="true" 
                          data-parsley-required-message="Please enter mobile number" 
                          placeholder="Mobile No." 
                          id="bill_contact_no" 
                          value="{{$arr_user_data['customer_details']['bill_contact_no'] or ''}}" {{-- data-parsley-maxlength="18"  --}}
                          data-parsley-maxlength-message="Mobile number must be less than 14 digits" {{-- data-parsley-minlength-message="Mobile number should be of 10 digits"  --}}data-parsley-pattern="^[0-9*#+]+$" 
                          data-parsley-required 
                          data-parsley-pattern-message="Please enter valid mobile number">
                         
                        </div>
                      <span class='red'>{{ $errors->first('bill_contact_no') }}</span>
                  </div>

              </div>
            </div>
              <div class="col-md-12">
                    <div class="form-group row">
                       <div class="col-md-12">
                          <button type="button" id="btn_tab3_update" name="btn_tab3_update" class="btn btn-success waves-effect waves-light m-r-10" value="Update">Update</button>
                    </div>
                </div>
              </div>
            </div>
          </form>
        </div>

      </div>
    </div>
  </div>
 </div>
</div>


 <input type="hidden" name="login_count" id="login_count" value="{{$is_login or ''}}">


<!-- Modal Sign IN  Start -->
<div id="CongratulationsModal" class="modal fade login-popup" data-replace="true" style="display: none;">
    <div class="modal-dialog fancy-modal-popup">
       
        <div class="modal-content">
            <div class="modal-body">

                <button type="button" class="close" data-toggle="modal" href="#ModalCategory"><img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="" /> </button>
               <div class="login-form-block">
                        <div class="login-content-block">
                          <div class="congratulationsimage"></div>
                            <div class="successfully-title">Congratulations {{$arr_user_data['first_name'] or ''}}</div>  


                            <div class="login-content-block">
                           
                             <div class="cong-text">You are registered successfully in {{$site_setting_arr['site_name'] or ''}}.</div>
                           </div>
                        </div>
                    </div>
                <div class="clr"></div>
            </div>
        </div>
    </div>
</div>



<!-- Modal Sign IN  End -->

<div id="ModalCategory" class="modal fade login-popup" data-replace="true" style="display: none;">
    <div class="modal-dialog modal-lg intrest_modal maxwidth-category-mdl">
       
        <div class="modal-content sign-up-popup">
            <div class="modal-body">
                    <div class="login-form-block">
                        <div class="login-content-block">

                          <div class="categorymodalslst">
                           
                            @if(isset($arr_category) && count($arr_category) >0)
                              @foreach($arr_category as $key=>$category)

                                 <div class="col-category">
                                    
                                      <div class="round">
                                          <input type="checkbox" id="checkbox_{{$category['id']}}" name="category_checkbox" value="{{$category['id']}}" min="1" max="3" />
                                          <label for="checkbox_{{$category['id']}}"></label>
                                        </div>


                                     <img src="{{url('/')}}/storage/app/{{$category['category_image']}}" alt="" />
                                     <div class="categorty-title">
                                      {{$category['category_name'] or ''}}
                                     </div>
                                 </div>

                              @endforeach
                            @endif

                          </div>

                           <div class="clearfix"></div>
                           <div class="categorynote-main">
                              <div class="note-category">
                            <span>Note:</span> Please choose at least one category
                          </div>
                          <div class="pull-right mb-3">
                            <a class="btn logi-link-block btn-primary"  data-toggle="modal" id="btn_skip">Skip</a> 
                            <a class="btn logi-link-block btn-primary" data-toggle="modal" id="btn_submit">Submit</a>
                          </div>
                           <div class="clearfix"></div>
                           </div>
                          <div class="clearfix"></div>
                        </div>

                    </div>
                <div class="clr"></div>
            </div>
        </div>
    </div>
</div>


<link href="{{url('/')}}/assets/front/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />    


<script>
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
    /*console.log(this.selectionStart);*/
    var text_length = $("#contact_no").attr('code_length');
    if(this.selectionStart < text_length){
        this.selectionStart = text_length;
        console.log(this.selectionStart);
        event.preventDefault();
    }
});
  
 var module_url_path = '{{$module_url_path or ''}}'; 
  
$(document).ready(function()
{
  

  assign_country_code_on_load();


  $('#tab1_form').parsley();
  $('#tab2_form').parsley();
  $('#tab3_form').parsley();

  
  var login_count = $('#login_count').val();

  if(login_count!='' && login_count == 0)
  {
     update_user_active_time();
     $('#CongratulationsModal').modal('show');

      /*after 5 sec is_login status will update*/
      setTimeout(function(){
        updateIsloginStatus();
          
      }, 5000);

  }


  checkProfileComplete();



 
  
/*$("#country_code").keyup(function(){
  validate_pin_or_zip_code();
});*/

$("#same_add").change(function() {
    var ischecked= $(this).is(':checked');
    var shipping_addr= $("#shipping_addr").val();
    var shipping_address2= $("#shipping_address2").val();
    var shipping_city= $("#shipping_city").val();
    var shipping_state= $("#shipping_state").val();
    var shipping_zip_code= $("#shipping_zip_code").val();
    var shipping_country= $("#shipping_country").val();
    var shipping_contact_no= $("#ship_contact_no").val();
    if(ischecked){
      $("#billing_addr").val(shipping_addr);
      $("#billing_address2").val(shipping_address2);
      $("#billing_city").val(shipping_city);
      $("#billing_state").val(shipping_state);
      $("#billing_zip_code").val(shipping_zip_code);
      $("#billing_country").val(shipping_country);
       $("#bill_contact_no").val(shipping_contact_no);
    }
    else
    {
      // old values of billing address
      var billing_addr      = '{{$arr_user_data['customer_details']['billing_address'] or ''}}';
      var billing_address2  = '{{$arr_user_data['customer_details']['billing_suit_apt'] or ''}}';
      var billing_city      = '{{$arr_user_data['customer_details']['billing_city'] or ''}}';
      var billing_state     = '{{$arr_user_data['customer_details']['billing_state'] or ''}}';
      var billing_zip_code  = '{{$arr_user_data['customer_details']['billing_zip_postal_code'] or ''}}';
      var billing_country   = '{{$arr_user_data['customer_details']['billing_country'] or ''}}';
      var billing_contact_no = '{{$arr_user_data['customer_details']['bill_contact_no'] or ''}}';
      $("#billing_addr").val(billing_addr);
      $("#billing_address2").val(billing_address2);
      $("#billing_city").val(billing_city);
      $("#billing_state").val(billing_state);
      $("#billing_zip_code").val(billing_zip_code);
      $("#billing_country").val(billing_country);
      $("#bill_contact_no").val(billing_contact_no);
    }
    
}); 

/*-----------parsley validation----------------*/



 $('#btn_tab1_update').click(function(){

       var formdata   = new FormData($("#tab1_form")[0]);
       var country_id = $("#country_id").val(); 
       var zip_code   = $("#pin_or_zip_code").val();
       buying_status  = $("#buying_status").val();

       if(country_id!="" && zip_code=="")
       { 
         $("#err_zip_code").html("");
       }

       else if(country_id=="" && zip_code=="")
       {
         $("#err_zip_code").html(""); 
       } 

       else if(country_id=="" && zip_code!="")
       {
         $("#err_zip_code").html("Invalid zip/postal code");
       }

       if($('#tab1_form').parsley().validate()==false)
       { 
          return;
       }

       // validate country wise phone code zip code
      var phone_code   = $('#country_id option:selected').attr('phone_code');
      var zipcode_length = $('#country_id option:selected').attr('zipcode_length');
      var countryName = $('#country_id option:selected').attr('country_name');


      var codeLength = jQuery('#hid_country_code').val();
      var minPhone = 7 + codeLength.length;            
      $('#contact_no').attr('data-parsley-minlength', minPhone);

      $('#contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');

      var maxPhone = 14 + codeLength.length;            
      $('#contact_no').attr('data-parsley-maxlength', maxPhone);

      if(zipcode_length == 8)
      {
          $('#pin_or_zip_code').attr('parsley-maxlength', true);
          $('#pin_or_zip_code').removeAttr('data-parsley-length');
          $('#pin_or_zip_code').attr('data-parsley-length-message', "");
          $("#pin_or_zip_code").attr({
            "data-parsley-maxlength": zipcode_length,
            "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
              zipcode_length +
              '  characters',
          });
      }
      else{
          $('#pin_or_zip_code').attr('parsley-maxlength', false);
          $('#pin_or_zip_code').attr('data-parsley-maxlength-message', "");
          $("#pin_or_zip_code").attr({
          "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
          "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
              zipcode_length +
              '  digits'
          });
      }

      if($('#tab1_form').parsley().validate()==false)
       { 
          return;
       }

        var formdata = new FormData($("#tab1_form")[0]);
        // alert(formdata.serialize());
        // return;
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

             if('success' == response.status)
             {
                  $("#profile-logo").load(window.location.href + " #profile-logo");
                  swal('Success',response.description,response.status);
                  
                  $('#tab3').removeClass('d-pointnone');

                  $('#tab1').removeClass('resp-tab-active');
                  $("#tab3").addClass('resp-tab-active');

                  $("#first_form").css("display", "none");

                  $("#first_form").removeClass('resp-tab-content resp-tab-content-active');
                  $("#third_form").addClass('resp-tab-content resp-tab-content-active');

                  $("#third_form").css("display", "block");

              }
              else
              {
                swal('Error',response.description,response.status);
              }  
          }
          
        });   

  });



  $('#btn_tab2_update').click(function(){

      
        if($('#tab2_form').parsley().validate()==false) return;

        var formdata = new FormData($("#tab2_form")[0]);
        
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
             if('success' == response.status)
             {
                  swal('Success',response.description,response.status);
                  $('#tab3').removeClass('d-pointnone');

                  $('#tab1').removeClass('resp-tab-active');
                  $("#tab2").removeClass('resp-tab-active');
                  $("#tab3").addClass('resp-tab-active');

                  $("#first_form").css("display", "none");
                  $("#second_form").css("display", "none");

                  $("#first_form").removeClass('resp-tab-content resp-tab-content-active');
                  $("#second_form").removeClass('resp-tab-content resp-tab-content-active');
                  $("#third_form").addClass('resp-tab-content resp-tab-content-active');

                  $("#third_form").css("display", "block");

              }
              else
              {
                swal('Error',response.description,response.status);
              }  
          }
          
        });   

  });



  $('#btn_tab3_update').click(function(){

       var billing_country    = $("#billing_country").val(); 
       var shipping_country   = $("#shipping_country").val();
       var billing_zip_code   = $("#billing_zip_code").val();
       var shipping_zip_code  = $("#shipping_zip_code").val();

       if(shipping_country!="" && shipping_zip_code=="")
       { 
         $("#err_shipping_zip_code").html("");
       }

       else if(shipping_country=="" && shipping_zip_code=="")
       {
         $("#err_shipping_zip_code").html(""); 
       } 

       else if(shipping_country=="" && shipping_zip_code!="")
       {
         $("#err_shipping_zip_code").html("Invalid zip/postal code");
       }

       if(billing_country!="" && billing_zip_code=="")
       { 
         $("#err_billing_zip_code").html("");
       }

       else if(billing_country=="" && billing_zip_code=="")
       {
         $("#err_billing_zip_code").html(""); 
       } 

       else if(billing_country=="" && billing_zip_code!="")
       {
         $("#err_billing_zip_code").html("Invalid zip/postal code");
       }

       if($('#tab3_form').parsley().validate()==false) return;

       // shipping country wise phone code and zip code
      var phone_code   = $('#shipping_country option:selected').attr('phone_code');
      var zipcode_length = $('#shipping_country option:selected').attr('zipcode_length');
      var countryName = $('#shipping_country option:selected').attr('country_name');

      var codeLength = jQuery('#hid_ship_contact_no_country_code').val();

      var minPhone = 7 + codeLength.length; 
      $('#ship_contact_no').attr('data-parsley-minlength', minPhone);

      $('#ship_contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

       var maxPhone = 14 + codeLength.length;            
       $('#ship_contact_no').attr('data-parsley-maxlength', maxPhone);

      if(zipcode_length == 8)
      {
          $('#shipping_zip_code').attr('parsley-maxlength', true);
          $('#shipping_zip_code').removeAttr('data-parsley-length');
          $('#shipping_zip_code').attr('data-parsley-length-message', "");
          $("#shipping_zip_code").attr({
            "data-parsley-maxlength": zipcode_length,
            "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
              zipcode_length +
              '  characters',
          });
      }
      else{
          $('#shipping_zip_code').attr('parsley-maxlength', false);
          $('#shipping_zip_code').attr('data-parsley-maxlength-message', "");
          $("#shipping_zip_code").attr({
          "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
          "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
              zipcode_length +
              '  digits'
          });
      }

      // billing country wise zip code phone code validation
      var phone_code   = $('#billing_country option:selected').attr('phone_code');
      var zipcode_length = $('#billing_country option:selected').attr('zipcode_length');
      var countryName = $('#billing_country option:selected').attr('country_name');

      var codeLength = jQuery('#hid_bill_contact_no_country_code').val(); 
      var minPhone = 7 + codeLength.length;  
      $('#bill_contact_no').attr('data-parsley-minlength', minPhone);

      $('#bill_contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

      var maxPhone = 14 + codeLength.length;            
      $('#bill_contact_no').attr('data-parsley-maxlength', maxPhone);

      if(zipcode_length == 8)
      {
          $('#billing_zip_code').attr('parsley-maxlength', true);
          $('#billing_zip_code').removeAttr('data-parsley-length');
          $('#billing_zip_code').attr('data-parsley-length-message', "");
          $("#billing_zip_code").attr({
            "data-parsley-maxlength": zipcode_length,
            "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
              zipcode_length +
              '  characters',
          });
      }
      else{
          $('#billing_zip_code').attr('parsley-maxlength', false);
          $('#billing_zip_code').attr('data-parsley-maxlength-message', "");
          $("#billing_zip_code").attr({
          "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
          "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
              zipcode_length +
              '  digits'
          });
      }
        
        if($('#tab3_form').parsley().validate()==false) return;

        var formdata = new FormData($("#tab3_form")[0]);

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
             if('success' == response.status)
             {
                  swal('Success',response.description,response.status);
                  $('#tab3').removeClass('d-pointnone');

                  $('#tab1').removeClass('resp-tab-active');
                  $("#tab2").removeClass('resp-tab-active');
                  $("#tab3").addClass('resp-tab-active');

                  $("#first_form").css("display", "none");
                  $("#second_form").css("display", "none");

                  $("#first_form").removeClass('resp-tab-content resp-tab-content-active');
                  $("#second_form").removeClass('resp-tab-content resp-tab-content-active');
                  $("#third_form").addClass('resp-tab-content resp-tab-content-active');


                  window.location.reload();

              }
              else
              {
                swal('Error',response.description,response.status);
              }  
          }
          
        });   

  });



$('#tab1').click(function(){
  
   $("#first_form").css("display", "block");
   $("#first_form").addClass('resp-tab-content resp-tab-content-active');
   $("#third_form").css("display", "none");

});


$('#tab3').click(function(){
  
   $("#third_form").css("display", "block");
   $("#third_form").addClass('resp-tab-content resp-tab-content-active');
   $("#first_form").css("display", "none");

});

var phone_code   = $('#country_id option:selected', this).attr('phone_code');      
 $("#contact_no").attr('code_length',phone_code.length+1); 
/*onchange of country dropdown validate zip/postal code field*/
$("#country_id").change(function(){

    var country   = $("#country_id").val();
    var zip_code  = $("#pin_or_zip_code").val();

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


    if(country=='' && zip_code!="")
    {
        $("#err_zip_code").html('Invalid zip/postal code');
    }

    var codeLength = jQuery('#hid_country_code').val();
    var minPhone = 7 + codeLength.length;            
    $('#contact_no').attr('data-parsley-minlength', minPhone);

    $('#contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

    var maxPhone = 14 + codeLength.length;            
    $('#contact_no').attr('data-parsley-maxlength', maxPhone);

    if(zipcode_length == 8)
    {
        $('#pin_or_zip_code').attr('parsley-maxlength', true);
        $('#pin_or_zip_code').removeAttr('data-parsley-length');
        $('#pin_or_zip_code').attr('data-parsley-length-message', "");
        $("#pin_or_zip_code").attr({
          "data-parsley-maxlength": zipcode_length,
          "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
            zipcode_length +
            '  characters',
        });
    }
    else{
        $('#pin_or_zip_code').attr('parsley-maxlength', false);
        $('#pin_or_zip_code').attr('data-parsley-maxlength-message', "");
        $("#pin_or_zip_code").attr({
        "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
        "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
            zipcode_length +
            '  digits'
        });
    }
    $('#tab1_form').parsley();
});


/*onchange of country dropdown validate shop post code field*/


$("#ship_contact_no").keydown(function(event) {
            var text_length = $("#ship_contact_no").attr('code_length');
            if (event.keyCode == 8) {
                this.selectionStart--;
            }
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });
        $("#ship_contact_no").keyup(function(event) {
            var text_length = ($("#ship_contact_no").attr('code_length')) ? $("#ship_contact_no").attr(
                'code_length') : "";
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });

/*onclick of shipping country dropdown validate shipping zip/postal code field*/
$("#shipping_country").change(function(){
    
    var shipping_country  = $("#shipping_country").val();
    var shipping_zip_code = $("#shipping_zip_code").val();

    if(shipping_country == '' && shipping_zip_code !='')
    {
      $('#err_shipping_zip_code').html('Invalid zip/postal code');
    }

    var phone_code   = $('option:selected', this).attr('phone_code');
    var zipcode_length = $('option:selected', this).attr('zipcode_length');
    var countryName = $('option:selected', this).attr('country_name');

    if(phone_code){
        $("#ship_contact_no").val("+"+phone_code);
        $("#ship_contact_no").attr('code_length',phone_code.length+1);  
        $("#hid_ship_contact_no_country_code").val('+'+phone_code);
       } else {
        $("#ship_contact_no").val('');
        $("#ship_contact_no").attr(0); 
        $("#hid_ship_contact_no_country_code").val('');
     } 
    
    var codeLength = jQuery('#hid_ship_contact_no_country_code').val();
    var minPhone = 7 + codeLength.length;  
    $('#ship_contact_no').attr('data-parsley-minlength', minPhone);

    $('#ship_contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

    var maxPhone = 14 + codeLength.length;            
    $('#ship_contact_no').attr('data-parsley-maxlength', maxPhone);

    if(zipcode_length == 8)
    {
        $('#shipping_zip_code').attr('parsley-maxlength', true);
        $('#shipping_zip_code').removeAttr('data-parsley-length');
        $('#shipping_zip_code').attr('data-parsley-length-message', "");
        $("#shipping_zip_code").attr({
          "data-parsley-maxlength": zipcode_length,
          "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
            zipcode_length +
            '  characters',
        });
    }
    else{
        $('#shipping_zip_code').attr('parsley-maxlength', false);
        $('#shipping_zip_code').attr('data-parsley-maxlength-message', "");
        $("#shipping_zip_code").attr({
        "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
        "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
            zipcode_length +
            '  digits'
        });
    }
    
    $('#tab3_form').parsley();

});



/*onclick of billing country validate billing zip/postal code field*/
$("#bill_contact_no").keydown(function(event) {
            var text_length = $("#bill_contact_no").attr('code_length');
            if (event.keyCode == 8) {
                this.selectionStart--;
            }
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });
        $("#bill_contact_no").keyup(function(event) {
            var text_length = ($("#bill_contact_no").attr('code_length')) ? $("#bill_contact_no").attr(
                'code_length') : "";
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });

$("#billing_country").change(function(){

    var billing_country   = $("#billing_country").val();
    var billing_zip_code  = $("#billing_zip_code").val();

    if(shipping_country == '' && billing_zip_code !='')
    {
      $('#err_billing_zip_code').html('Invalid zip/postal code');
    }

    var phone_code   = $('option:selected', this).attr('phone_code');
    var zipcode_length = $('option:selected', this).attr('zipcode_length');
    var countryName = $('option:selected', this).attr('country_name');

    if(phone_code){
        $("#bill_contact_no").val("+"+phone_code);
        $("#bill_contact_no").attr('code_length',phone_code.length+1);  
        $("#hid_bill_contact_no_country_code").val('+'+phone_code);
       } else {
        $("#bill_contact_no").val('');
        $("#bill_contact_no").attr(0); 
        $("#hid_bill_contact_no_country_code").val('');
     } 
    
    var codeLength = jQuery('#hid_bill_contact_no_country_code').val();
    var minPhone = 7 + codeLength.length;  
    $('#bill_contact_no').attr('data-parsley-minlength', minPhone);

    $('#bill_contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

    var maxPhone = 14 + codeLength.length;            
    $('#bill_contact_no').attr('data-parsley-maxlength', maxPhone);

    if(zipcode_length == 8)
    {
        $('#billing_zip_code').attr('parsley-maxlength', true);
        $('#billing_zip_code').removeAttr('data-parsley-length');
        $('#billing_zip_code').attr('data-parsley-length-message', "");
        $("#billing_zip_code").attr({
          "data-parsley-maxlength": zipcode_length,
          "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
            zipcode_length +
            '  characters',
        });
    }
    else{
        $('#billing_zip_code').attr('parsley-maxlength', false);
        $('#billing_zip_code').attr('data-parsley-maxlength-message', "");
        $("#billing_zip_code").attr({
        "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
        "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
            zipcode_length +
            '  digits'
        });
    }
      
    $('#tab3_form').parsley();
});




  $('#btn_skip').click(function(){
     
     $('#ModalCategory').modal('hide');
     $('#CongratulationsModal').modal('hide');
   
        $.ajax({
          url: module_url_path+'/is_login_update',
          type:"GET",
          dataType:'json',
          success:function(response)
          {
             if(response.status == 'success')
             {
                
                var html = '<div class="alert alert-danger" >'+response.error_message+'</div>';

                $('#error-msg').html(html);
             }
             else
             {
               
             }
          }
          
        });
   
  });


  $('#btn_submit').click(function(){
     
      var csrf_token      = $("input[name=_token]").val();
      var category_id_arr = category_arr= [];
      
      $.each($("input[name='category_checkbox']:checked"), function(){
        category_id_arr.push($(this).val());
          

      });

      if(category_id_arr.length == 0)
      {
        swal('Warning','Please select atleast one category','warning');
        return false;
      }      
      else if(category_id_arr.length >3)
      {
        swal('Warning','You can select maximum three categories','warning');
        return false;
      }
      else
      {
        window.location = '{{url('/')}}/search?category_id_arr='+encodeURIComponent(JSON.stringify(category_id_arr));
      }

      
   

  });


  /*after 5 sec update login count*/

  function updateIsloginStatus()
  {
       $.ajax({
          url: module_url_path+'/is_login_update',
          type:"GET",
          dataType:'json',
          success:function(response)
          {
             if(response.status == 'success')
             {
                
                var html = '<div class="alert alert-danger" >'+response.error_message+'</div>';

                $('#error-msg').html(html);
             }
             else
             {
               
             }
          }
          
        }); 
  }


  function checkProfileComplete()
  {
       $.ajax({
          url: module_url_path+'/check_profile_complete',
          type:"GET",
          dataType:'json',
          success:function(response)
          {
             if(response.status == 'success')
             {
                /*var html = '<div class="alert alert-danger" >'+response.description+'</div>';

                $('#error-msg').html(html);*/
             }
             else if(response.status == 'error')
             {
                var html = '<div class="alert alert-danger" >'+response.description+'</div>';

                $('#error-msg').html(html);
             }

          }
          
        }); 
  }

});



</script>

<!--tabing-css-js-start-here-->
    <link href="{{url('/')}}/assets/css/easy-responsive-tabs.css" rel="stylesheet" type="text/css" />
       <script src="{{url('/')}}/assets/js/easyResponsiveTabs.js" type="text/javascript"></script>
<script>
   //<!--tab js script-->  
         $('#horizontalTab').easyResponsiveTabs({
            
           type: 'default', //Types: default, vertical, accordion           
           width: 'auto', //auto or any width like 600px
           fit: true, // 100% fit in a container
           closed: 'accordion', // Start closed if in accordion view
           activate: function(event) { // Callback function if tab is switched
               var $tab = $(this);
               var $info = $('#tabInfo');
               var $name = $('span', $info);
         
               $name.text($tab.text());
         
               $info.show();
           }
         });

   /* function to assign min length on page load */
  function assign_country_code_on_load(){

     // For shipping country
     var phone_code   = $('#shipping_country option:selected').attr('phone_code');
    
      if(phone_code){        
        $("#ship_contact_no").attr('code_length',phone_code.length+1);  
        $("#hid_ship_contact_no_country_code").val('+'+phone_code);
      } else {       
        $("#ship_contact_no").attr(0); 
        $("#hid_ship_contact_no_country_code").val('');
     } 
 
      var zipcode_length = $('#shipping_country option:selected').attr('zipcode_length');
      var countryName = $('#shipping_country option:selected').attr('country_name');
      var codeLength = $('#hid_ship_contact_no_country_code').val();
      var minPhone = 7 + codeLength.length;            
      $('#ship_contact_no').attr('data-parsley-minlength', minPhone);

      $('#ship_contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

      var maxPhone = 14 + codeLength.length;            
      $('#ship_contact_no').attr('data-parsley-maxlength', maxPhone);

      // For billing country
     var bill_phone_code   = $('#billing_country option:selected').attr('phone_code');
    
      if(bill_phone_code){        
        $("#bill_contact_no").attr('code_length',bill_phone_code.length+1);  
        $("#hid_bill_contact_no_country_code").val('+'+bill_phone_code);
      } else {       
        $("#bill_contact_no").attr(0); 
        $("#hid_bill_contact_no_country_code").val('');
     }  
      
      var bill_codeLength = $('#hid_bill_contact_no_country_code').val();
      var bill_minPhone = 7 + bill_codeLength.length;            
      $('#bill_contact_no').attr('data-parsley-minlength', bill_minPhone);

      $('#bill_contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  

      var maxPhone = 14 + codeLength.length;            
      $('#bill_contact_no').attr('data-parsley-maxlength', maxPhone);

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

{{-- <script type="text/javascript" src="{{url('/')}}/assets/js/after_login_common.js"></script> --}}

@endsection
