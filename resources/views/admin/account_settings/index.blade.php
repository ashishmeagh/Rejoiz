@extends('admin.layout.master')    
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
         <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
         <li class="active">{{$module_title or ''}}</li>
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
        
        
         {!! Form::open([ 'url' => $module_url_path.'/update/'.base64_encode($arr_data['id']),
         'method'=>'POST',   
         'class'=>'form-horizontal', 
         'id'=>'validation-form',
         'enctype' =>'multipart/form-data'
         ]) !!}
         <input type="hidden" name="old_image" value="{{$arr_data['profile_image'] or ''}}">
         <div class="form-group row">
            <label for="first_name" class="col-sm-12 col-md-12 col-lg-2 col-form-label">First Name<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-10">
               <input type="text" class="form-control"  name="first_name" data-parsley-required="true" data-parsley-required-message="Please enter first name" placeholder="First Name" id="first_name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid first name" value="{{$arr_data['first_name'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('first_name') }}</span>
         </div>
         
         <div class="form-group row">
            <label for="last_name" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Last Name<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-10">
              <input type="text" name="last_name" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter last name"  placeholder="Last Name" id="last_name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid last name" value="{{$arr_data['last_name'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('last_name') }}</span>
         </div>
         <div class="form-group row">
            <label for="email" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Email<i class="red">*</i></label>
            <div class="col-sm-12 col-md-12 col-lg-10">
              <input type="text" name="email" class="form-control" data-parsley-required="true" data-parsley-required-message="Please enter email" data-parsley-type="email" data-parsley-type-message="Please enter valid email" placeholder="Email" id="email" value="{{$arr_data['email'] or ''}}">
            </div>
               <span class='red'>{{ $errors->first('email') }}</span>
         </div>
         
     {{--     <div class="form-group row">
            <label for="stripe_publishable_key" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Stripe Publishable key</label>
            <div class="col-sm-12 col-md-12 col-lg-10">
              <input type="text" name="stripe_publishable_key" class="form-control"   placeholder="Stripe Publishable Key" id="stripe_publishable_key" value="{{isset($stripe_publishable_key->data_value)?$stripe_publishable_key->data_value:''}}">
            </div>
               <span class='red'>{{ $errors->first('stripe_publishable_key') }}</span>
         </div> --}}
         {{-- ca_HPCVVnPTQY9NApdm4ksvfHLXPYUvoY6L --}}
         <?php
           $secretKey =  isset($arrStripeKeyData['secret_key'])?$arrStripeKeyData['secret_key']:'';

           $clientId  =  isset($arrStripeKeyData['client_id'])?$arrStripeKeyData['client_id']:'';

           $accountHolder  =  isset($arrStripeKeyData['account_holder'])?$arrStripeKeyData['account_holder']:'';

         ?>


         <!-- <div class="form-group row">
            <label for="stripe_secret_key" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Stripe Secret Key</label> -->

        <!--  <div class="form-group row">
            <label for="stripe_secret_key" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Stripe Secret key</label>
          stripe_payment_flow_changes
            <div class="col-sm-12 col-md-12 col-lg-10">
           
              <span style="color:#fb3b62;" class="mb-2"></span>
              <input type="text" name="stripe_secret_key" class="form-control"  placeholder="Stripe Secret Key" id="stripe_secret_key" value="{{$secretKey}}">
            </div>
               <span class='red'>{{ $errors->first('stripe_secret_key') }}</span>
         </div>

         
         <div class="form-group row">
            <label for="stripe_client_id" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Stripe Client id</label>
            <div class="col-sm-12 col-md-12 col-lg-10">
              <input type="text"  name="stripe_client_id" class="form-control"  placeholder="Stripe client id" id="stripe_client_id" value="{{$clientId}}">
            </div>
               <span class='red'>{{ $errors->first('stripe_client_id') }}</span>
         </div> -->

       
          @php
            if(isset($arr_data['profile_image']) || 
               $arr_data['profile_image'] != '' || 
               $arr_data['profile_image'] != null)
            {
              $profile_image = url('/').'/storage/app/'.$arr_data['profile_image'];
            }
            else
            {
              $profile_image = url('/').'/assets/images/default_images/user-no-img.jpg';
            }
         @endphp


         <div class="form-group row">
          <!-- for="ad_image" -->
             <label class="col-sm-12 col-md-12 col-lg-2 col-form-label">Profile Image</label>
              <div class="col-sm-12 col-md-12 col-lg-10">

                <span><i class="red"><span>Only png,jpg & jpeg extensions files are allowed.</span></i></span>

                <input type="file" name="image" id="ad_image" class="dropify" data-default-file="{{ $profile_image or '' }}"  data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside"/>
              </div>
         </div>
         <div class="form-group row">
            <div class="col-12">
              <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{url('/')}}/admin/dashboard"><i class="fa fa-arrow-left"></i> Back</a>
              <button type="submit" style="float: right" class="btn btn-success waves-effect waves-light" value="Update" id="update">Update</button>
            </div>
         </div>


         {!! Form::close() !!}
      </div>
   </div>
</div>
</div>
</div>

<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Stripe Settings</h4>
   </div>
</div>

<div class="row">
  <div class="col-sm-12"> 
    <div class="white-box">
      {!! Form::open([ 'url' => 
              $module_url_path.'/update_stripe_settings/'.base64_encode($arr_data['id']),
              'method'=>'POST',   
              'class'=>'form-horizontal', 
              'id'=>'stripe-validation-form',
              'enctype' =>'multipart/form-data'
            ]) !!}
      <div class="row">
        <div class="col-sm-12">
         <div class="form-group row">
            <label for="stripe_secret_key" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Stripe Secret key</label>
            <div class="col-sm-12 col-md-12 col-lg-10">
              <input type="text" name="stripe_secret_key" class="form-control"  placeholder="Stripe Secret Key" id="stripe_secret_key" value="{{$secretKey}}" data-parseley-required = "true" data-parseley-required-message = "Stripe secret key is required">
            </div>
               <span class='red'>{{ $errors->first('stripe_secret_key') }}</span>
         </div>

         
         <div class="form-group row">
            <label for="stripe_client_id" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Stripe client id</label>
            <div class="col-sm-12 col-md-12 col-lg-10">
              <input type="text"  name="stripe_client_id" class="form-control"  placeholder="Stripe client id" id="stripe_client_id" value="{{$clientId}}" data-parseley-required = "true" data-parseley-required-message = "Stripe client id is required">
            </div>
               <span class='red'>{{ $errors->first('stripe_client_id') }}</span>
         </div> 

         <div class="form-group row">
            <label for="account_holder" class="col-sm-12 col-md-12 col-lg-2 col-form-label">Account Holder Name</label>
            <div class="col-sm-12 col-md-12 col-lg-10">
              <input type="text"  name="account_holder" class="form-control"  placeholder="Account Holder Name" id="account_holder" value="{{$accountHolder}}" data-parseley-required = "true" data-parseley-required-message = "Stripe client id is required">
            </div>
               <span class='red'>{{ $errors->first('account_holder') }}</span>
         </div> 
      </div>
    </div>
    {!! Form::close() !!}
    <div class="form-group row">
            <div class="col-12">
              <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{url('/')}}/admin/dashboard"><i class="fa fa-arrow-left"></i> Back</a>
              <!-- <button type="submit" style="float: right" class="btn btn-success waves-effect waves-light" value="Update" id="update_stripe_settings">Update</button> -->

              <button type="button" style="float: right" data-toggle="modal" data-target="#verifyPasswordModal" class="btn btn-success waves-effect waves-light" value="Update">Update</button>
              </div>
    </div>
  </div>
</div>
</div>

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

<script type="text/javascript">
  var module_url_path = "{{$module_url_path}}";
  var csrf_token = $("input[name=_token]").val();
</script>
 <script type="text/javascript">

  $("#update").click(function()
   {
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

  $("#update_stripe_settings").click(function()
   {
      if($('#stripe-validation-form').parsley().validate()==false)
          {  
             hideProcessingOverlay();
             return;
          }
          else
          { 
            showProcessingOverlay();
            $("#stripe-validation-form").submit();
          }    
   });

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