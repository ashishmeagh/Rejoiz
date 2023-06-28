@extends('front.layout.master')
@section('main_content')
<style>
#err_zip_code{
    margin: 10px 0 10px;
    padding: 0; 
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;
  }
</style>  

<meta name="google-signin-client_id" content="753906644363-p0gfb3ecld76k0c3dhuen3hqh6hr91jp.apps.googleusercontent.com">

<div class="login-main-bg-img spacesignups">    
    <div class="login-main-page-dv-hm signupsclasssign">
        
{{--       @include('front.layout.flash_messages')--}}       
         <!-- {{ csrf_field() }} -->
        <div class="login-right-page signup_retailer_page signyp-maxwirdss">
           @if(Session::has('message'))
            

            <p class="alert {{ Session::get('alert-class', 'alert-info') }}" id ="alert_container"><a href="javascript:void(0)"><i class="fa fa-times" aria-hidden="true" id ="close_btn"></i></a>{!! Session::get('message') !!}</p>
           @endif

           
            @php
                $site_img = false;

                $site_base_img = isset($site_setting_arr['login_site_logo']) ? $site_setting_arr['login_site_logo'] : false;

                //$site_image_base_path = base_path('storage/app/'.$site_base_img);

                $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;
                
                //$site_default_image = url('https://via.placeholder.com/160x53.png?text='.$site_name);
                //$site_img = image_resize($site_image_base_path,160,53,$site_default_image);

                $site_img = imagePath($site_base_img,'site_logo',0);
                
            @endphp
            <div class="kadoe-logo">
               {{-- <img src="{{url('/')}}/assets/front/images/k-logo.jpg" alt="" /> --}}
                <img src="{{ $site_img }}" alt="{{ $site_name }}" />
            </div>
            <div class="welcomebackkadoe">Create Customer Account</div>
           
              <div id="status_msg"></div>
              <form id="signup-form">
                 {{ csrf_field() }}
              <div class="loginform">
                <div class="row">
                  <div class="col-md-6">
                    <div class="user-box">
                      <label class="form-lable">First Name<i class="red">*</i></label>
                      <input class="cont-frm" placeholder="Enter Your First Name" type="text" name="first_name" id="first_name" data-parsley-required="true" data-parsley-required-message="Please enter first name." data-parsley-maxlength="50" data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Please enter valid first name."/>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="user-box">
                        <label class="form-lable">Last Name<i class="red">*</i></label>
                        <input class="cont-frm" placeholder="Enter Your Last Name" type="text" name="last_name" id="last_name" data-parsley-required="true" data-parsley-required-message="Please enter last name." data-parsley-maxlength="50" data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Please enter valid last name."/>
                      </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="user-box">
                        <label class="form-lable">Email Address<i class="red">*</i></label>
                        <input class="cont-frm" placeholder="Enter Your Email Address" type="email" name="email" id="email" data-parsley-required="true" data-parsley-required-message="Please enter email address." data-parsley-type-message="Please enter valid email address."/>
                      </div>
                  </div>


                  <div class="col-md-6">
                    <div class="user-box">
                        <label class="form-lable">Country<i class="red">*</i></label>
                        <select name="country_id" id="country_id" data-parsley-required="true" data-parsley-required-message="Please select country.">
                          <option value="">Select Country</option>
                            @if(isset($country_arr) && sizeof($country_arr)>0)
                                 @foreach($country_arr as $country)
                                    <option value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}">{{$country['name'] or ''}}</option>
                                    }
                                  @endforeach
                                @endif

                        </select>
                      </div> 
                  </div>
                  
                </div>
                <div class="row">


                  <div class="col-md-6">
                    <div class="user-box">
                      <label class="form-lable">Mobile No<i class="red">*</i></label>
                      <input 
                      class="cont-frm" 
                      placeholder="Enter Your Mobile No." 
                      type="text" 
                      name="contact_no" 
                      id="contact_no" 
                      data-parsley-required="true" 
                      data-parsley-required-message="Please enter mobile no." 
                      data-parsley-type-message="Please enter valid mobile no." 
                      data-parsley-pattern="^[0-9*#+]+$" 
                      data-parsley-required 
                      data-parsley-pattern-message="Please enter valid mobile number." 
                      {{-- data-parsley-minlength-message="Mobile No should be of 10 digits."  --}}
                      {{-- data-parsley-maxlength="14"  --}}
                      data-parsley-maxlength-message="Mobile No must be less than 14 digits." />
                      <!-- data-parsley-type="digits" -->
                      <input type="hidden" name="hid_country_code" id="hid_country_code">
                    </div> 
                  </div>
                  <div class="col-md-6">
                    <div class="user-box rw-login-user-box" id="user_pwd">
                    <label class="form-lable">Password<i class="red">*</i></label>
                    <input class="cont-frm" placeholder="Enter Your Password" type="password" name="password" id="password" data-parsley-required="true" data-parsley-required-message="Please enter password." data-parsley-pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$" data-parsley-pattern-message="<ul><li>Password must contain at least one number,one uppercase letter,one special character.</li><li>Password length should be at least 8 or more characters.</li></ul>" data-parsley-minlength="8" autocomplete="new-password"/>
                     <span toggle="#password-field" class="fa fa-fw fa-eye field_icon toggle-password"></span>
                  </div>
                  </div>
                  <div class="col-md-6">
                    <div class="user-box rw-login-user-box">
                      <label class="form-lable">Confirm Password<i class="red">*</i></label>
                      <input class="cont-frm" placeholder="Enter Your Confirm Password" data-parsley-equalto="#password" data-parsley-error-message="Confirm password should be same as password" type="password" name="confirm_password" id="confirm_password" data-parsley-required="true" data-parsley-required-message="Please enter confirm password."/>
                      <span toggle="#password-field" class="fa fa-fw fa-eye field_icon toggle-password-c"></span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  
                  {{-- <div class="col-md-6">
                    <div class="user-box">
                        <label class="form-lable">Tax Id<i class="red">*</i></label>
                        <input class="cont-frm" placeholder="Enter Your Tax Id" type="text" name="tax_id" id="tax_id" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="{{ url('/does_exists_tax_id/tax-id') }}"
                          data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'   data-parsley-remote-message="Tax Id already exists" data-parsley-length="[9, 9]" data-parsley-length-message ="Tax id is invalid. It should have 9 digits" data-parsley-type="integer" data-parsley-required="true" data-parsley-required-message="Please enter tax id."/>
                      </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="user-box">
                        <label class="form-lable">Are you buying for a physical retail store?</label>
                        <select name="buying_status" id="buying_status" onchange="retailerStore($(this));">
                          <option>Select</option>
                          <option value="1">Yes,I am buying for a physical retail store</option>
                          <option value="2">No,I am buying for an online-only store</option>
                          <option value="3">No,I am buying for a pop-up shop</option>
                          <option value="4">None of the above apply</option>
                        </select>
                      </div> 
                  </div>
                  <div class="col-md-6">
                    <div class="user-box" style="display:none" id="store_name_container">
                        <label class="form-lable">Store Name<i class="red">*</i></label>
                        <input class="cont-frm" placeholder="Enter Your Store Name" type="text" name="store_name" id="store_name"/>
                      </div>
                  </div> --}}
                </div>
                  {{-- <div class="row">
                    <div id="store_data_container" style="display:none">
                       <div class="col-md-6">
                            <div class="user-box">
                          <label class="form-lable">Years in business<i class="red">*</i></label>
                            <input class="cont-frm" placeholder="Years in business" type="text" name="years_in_business" id="years_in_business"/>
                          </div> 
                        </div>
                        <div class="col-md-6">
                          <div class="user-box">
                           <label class="form-lable">Annual Sales</label>
                            <input class="cont-frm" placeholder="Annual Sales" type="text" name="Annual Sales" id="Annual_Sales"/>
                          </div> 
                        </div>
                    </div> 
                  </div> --}}
                  {{-- <div class="row">
                  <div class="col-md-6">
                    <div id=store_website_container style="display:none">
                      <div class="user-box">
                        <label class="form-lable">Store Website<i class="red">*</i></label>
                        <input class="cont-frm" placeholder="Enter Your Store Website" type="text" name="store_website" id="store_website" data-parsley-maxlength="50" data-parsley-type="url"/>
                      </div> 
                      </div>
                  </div>
                  <div class="col-md-6">
                    <div id="pop_up_data_container" style="display:none">
                     <div class="user-box">
                       <label class="form-lable">Zip/Postal Code<i class="red">*</i></label>
                        <input class="cont-frm" placeholder="Zip Code" type="text" name="zip_code" id="pop_up_zip_code" data-parsley-trigger="change"/>
                      <div id="err_zip_code"></div>
                      </div> 
                    </div> 
                  </div>
                  <div class="col-md-6">
                    <div id ="store_description_container" style="display:none">
                      <div class="user-box">
                       <label class="form-lable">Store Description<i class="red">*</i></label>
                        <textarea class="cont-frm" placeholder="Store Description" name="store_description" id="store_description"></textarea>
                        
                      </div> 
                    </div>
                  </div>
                  </div> --}}
                
                
                  
<!--              <div class="col-md-6"><div class="user-box">
                    <label class="form-lable">Country Code<i class="red">*</i></label>
                    <input class="cont-frm" placeholder="Enter Your Country Code" type="text" name="country_code" id="country_code" data-parsley-required="true" data-parsley-maxlength="20" data-parsley-pattern="/^[0-9\+/\-]+$/"/>
                  </div> </div> -->
                 
                  
                   
                  
                  

                {{--   <div class="user-box">
                    <label class="form-lable">Address</label>
                    <input class="cont-frm" placeholder="Enter Your Address" type="text" name="address" id="address" data-parsley-maxlength="50"/>
                  </div>  --}} 

                 {{--  <div class="user-box">
                    <label class="form-lable">PIN or ZIP Code</label>
                    <input class="cont-frm" placeholder="Enter PIN or ZIP Code" type="text" name="post_code" id="post_code" data-parsley-maxlength="50" data-parsley-type="integer" data-parsley-length="[5,5]" data-parsley-trigger="change"/>
                  </div>   --}}

                <div class="remeber">
                     <div class="checkbx">
                        <input type="checkbox" class="filled-in" id="check1" data-parsley-required="true" data-parsley-errors-container="#term_con" data-parsley-required-message="Please accept terms & conditions."/>
                        <label for="check1">I accept the terms & conditions<i class="red">*</i></label>
                     </div>
                        
                    <div class="clearfix"></div>
                    <div id="term_con"></div>
                </div>

                <input type="hidden" name="role" id="role" value="customer">
              <div class="button-login-pb">
                   <!-- <a href="signup.html" class="gt-button">Register</a> -->
                   <button type="button" class="gt-button" id="btn_signup">Register</button>
                   <div class="clearfix"></div>
                   <div class="account-links-nw">Already a member? <a href="{{url('/login')}}">Login Here</a></div>
              </div>
              
              </div>
            </form>  

        </div>
    
        <div class="clearfix"></div>
    </div>
</div>
<script src="https://apis.google.com/js/client:platform.js?onload=renderButton" async defer></script>

<script type="text/javascript">
  $( document ).ready(function() {
      // $('#signup-form').parsley();
// To fixed code no in mobile no textbox
// By Harshada on date 02 Sept 2020
$("#contact_no").keydown(function(event){
    var text_length = ($("#contact_no").attr('code_length') ) ? $("#contact_no").attr('code_length') : "";
    if(event.keyCode == 8){
        this.selectionStart--;
    }
    if(this.selectionStart < text_length){
        this.selectionStart = text_length;        
        event.preventDefault();
    }
});
$("#contact_no").keyup(function(event){
    var text_length = ($("#contact_no").attr('code_length') ) ? $("#contact_no").attr('code_length') : "";   
    if(this.selectionStart < text_length){
        this.selectionStart = text_length;
        console.log(this.selectionStart);
        event.preventDefault();
    }
});
     
  }); 
 
 $('#close_btn').click(function()
  {
    $("#alert_container").hide();
  });
 $('#btn_signup').click(function()
  {
      var country_code = $("#country_id").val();
      var zip_code     = $("#pop_up_zip_code").val();

      if(country_code!="" && zip_code!="")
      {
        $("#err_zip_code").html('');
      }
      else if(country_code=="" && zip_code=="")
      {
         $("#err_zip_code").html('');
      }
      else if(country_code=="" && zip_code!="")
      {
        $("#err_zip_code").html('Invalid zip/postal code.');
      }
      else if(country_code!="" && zip_code=="")
      {
        $("#err_zip_code").html('');
      } 


      /*------------password validation---------------------*/

        var password = $('#password').val();

        if(password != '')
        { 
          $('#user_pwd').addClass('user-box_password');
        }
        else if(password == '')
        {
          $('#user_pwd').removeClass('user-box_password');
        }

      /*----------------------------------------------------*/

      if($('#signup-form').parsley().validate()==false) return; 

      var form_data = $('#signup-form').serialize();      
      var url       = "{{url('/')}}/process_signup"; 
      // alert(form_data);
      // return false; 
      if($('#signup-form').parsley().isValid() == true )
      {
          
         $.ajax({
            url:url,
            data:form_data,
            method:'POST',
            
            beforeSend : function()
            {
              showProcessingOverlay();
              $('#btn_signup').prop('disabled',true);
              $('#btn_signup').html('Please Wait <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
            },
            success:function(response)
            {
              scrollToTop();
               hideProcessingOverlay();
               $('#btn_signup').prop('disabled',false);
               $('#btn_signup').html('Sign Up');

              if(typeof response =='object')
              {
                if(response.status && response.status=="SUCCESS")
                {
                  $("#signup-form")[0].reset();

                  var success_HTML = '';
                  success_HTML +='<div class="alert alert-success alert-dismissible">\
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">\
                            <span aria-hidden="true">&times;</span>\
                        </button>'+response.msg+'</div>';

                        $('#status_msg').html(success_HTML);
                  
                }
                else
                {                    
                    var error_HTML = '';   
                    error_HTML+='<div class="alert alert-danger alert-dismissible">\
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">\
                            <span aria-hidden="true">&times;</span>\
                        </button>'+response.msg+'</div>';
                    
                    $('#status_msg').html(error_HTML);
                }
              }
            }
         });
      }
  });

 /*password validation on password field onchange*/
  $('#password').on("change keyup blur",function(){
    
      var password = $('#password').val();

      if(password != '')
      { 
         $('#user_pwd').addClass('user-box_password');
      }
          
      else if(password == '')
      {
          $('#user_pwd').removeClass('user-box_password');
      }
     
      if($('#password').parsley().validate()==false) return;

   });
 /*Login with facebook */
window.fbAsyncInit = function() {
    // FB JavaScript SDK configuration and setup
    FB.init({
      appId      : '376161336631554', // FB App ID
      cookie     : true,  // enable cookies to allow the server to access the session
      xfbml      : true,  // parse social plugins on this page
      version    : 'v2.8' // use graph api version 2.8
    });
    
    // Check whether the user already logged in
    FB.getLoginStatus(function(response) {
        if (response.status === 'connected') {
            //display user data
            getFbUserData();
        }
    });
};

 // Load the JavaScript SDK asynchronously
(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));

 // Facebook login with JavaScript SDK
function fbLogin() {
    FB.login(function (response) {
        if (response.authResponse) {
            // Get and display the user profile data
            getFbUserData();
        } else {
            // document.getElementById('status').innerHTML = 'User cancelled login or did not fully authorize.';
        }

    }, {scope: 'email'});
}

function retailerStore(ref)
{
   var selected_value = $(ref).children("option:selected").val();
   
   if(selected_value == 1)
   {
       $("#store_name_container").show();
       $("#store_data_container").show();
       $("#store_description_container").hide();
       $("#store_website_container").hide();
       $("#pop_up_data_container").hide();

       $("#store_name").attr("data-parsley-required","true");
       $("#store_name").attr("data-parsley-required-message","Please enter store name.");
       $("#years_in_business").attr("data-parsley-required","true");
       $("#years_in_business").attr("data-parsley-required-message","Please enter how many years you were in business.");


       $("#store_website").removeAttr("data-parsley-required");
       $("#pop_up_zip_code").removeAttr("data-parsley-required");
       $("#store_description").removeAttr('data-parsley-required');
       $("#store_website").removeAttr('data-parsley-required');

   }
   else if(selected_value == 2)
   {
     $("#store_name_container").hide();
     $("#store_website_container").show();
     $("#store_name_container").show();
     $("#store_description_container").hide();
     $("#pop_up_data_container").hide();
     $("#store_data_container").hide();

     $("#store_website").attr("data-parsley-required","true");
     $("#store_website").attr("data-parsley-required-message","Please enter store Website.");

     $("#store_name").attr("data-parsley-required","true");
     $("#store_name").attr("data-parsley-required-message","Please enter store name.");


     $("#years_in_business").removeAttr('data-parsley-required');
     $("#pop_up_zip_code").removeAttr('data-parsley-required');
     $("#store_description").removeAttr('data-parsley-required');



  }
  else if(selected_value == 3)
  {
      $("#pop_up_data_container").show();
      $("#store_name_container").show();
      $("#store_description_container").hide();
      $("#store_data_container").hide();
      $("#store_website_container").hide();

      $("#pop_up_zip_code").attr("data-parsley-required","true");
      $("#pop_up_zip_code").attr("data-parsley-required-message","Please enter zip/postal code.");

      $("#store_name").attr("data-parsley-required","true");
      $("#store_name").attr("data-parsley-required-message","Please enter store name.");


      $("#years_in_business").removeAttr('data-parsley-required');
      $("#store_description").removeAttr('data-parsley-required');
      $("#store_website").removeAttr('data-parsley-required');

  }
  else
  {
      $("#pop_up_data_container").hide();
      $("#store_name_container").hide();
      $("#store_description_container").hide();
      $("#store_data_container").hide();
      $("#store_website_container").hide();

      $("#store_name").attr("data-parsley-required","true");
      $("#store_name").attr("data-parsley-required-message","Please enter store name.");

      $("#store_description").attr("data-parsley-required","true");
      $("#store_description").attr("data-parsley-required-message","Please enter store description.");


    /*  $("#years_in_business").removeAttr('data-parsley-required');
      $("#pop_up_zip_code").attr("data-parsley-required","true");
      $("#store_website").removeAttr('data-parsley-required');*/

  }
}

// Fetch the user profile data from facebook
function getFbUserData(){
    FB.api('/me', {locale: 'en_US', fields: 'id,first_name,last_name,email,link,gender,locale,picture'},
    function (response) {      
      $('#first_name').val(response.first_name);   
      $('#last_name').val(response.last_name);   
      $('#email').val(response.email);   
    });
}
</script>


<!-- Google sign In -->
<script>
// Render Google Sign-in button
function renderButton() {
    gapi.signin2.render('gSignIn', {
        'scope': 'profile email',
        'width': 240,
        'height': 50,
        'longtitle': true,
        'theme': 'dark',
        'onsuccess': onSuccess,
        'onfailure': onFailure
    });
}

// Sign-in success callback
function onSuccess(googleUser) {
    // Get the Google profile data (basic)
    //var profile = googleUser.getBasicProfile();
    
    // Retrieve the Google account data
    gapi.client.load('oauth2', 'v2', function () {
        var request = gapi.client.oauth2.userinfo.get({
            'userId': 'me'
        });
        request.execute(function (resp) {          
          $('#first_name').val(resp.given_name);   
          $('#last_name').val(resp.family_name);   
          $('#email').val(resp.email);            
        });
    });
}

// Sign-in failure callback
function onFailure(error) {
    console.log(error);
}

// Sign out the user
// function signOut() {
//     var auth2 = gapi.auth2.getAuthInstance();
//     auth2.signOut().then(function () {
//         document.getElementsByClassName("userContent")[0].innerHTML = '';
//         document.getElementsByClassName("userContent")[0].style.display = "none";
//         document.getElementById("gSignIn").style.display = "block";
//     });
    
//     auth2.disconnect();
// }



function scrollToTop() 
{
  $('html, body').animate({
      scrollTop:0
  }, $(window).scrollTop() / 3);
    return false;
};


$( document ).ready(function(){
  $("#country_id").change(function(event) {
   var country_code = $("#country_id").val();
   var zip_code     = $("#pop_up_zip_code").val();

   var phone_code   = $('option:selected', this).attr('phone_code');

   if(phone_code){
     $("#contact_no").val('+'+phone_code);
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

  if(country_code=='' && zip_code!="")
  {
    $("#err_zip_code").html('Invalid zip/postal code.');
  } 
    
  else if(country_code=='2' && zip_code!="")
    {
     $("#err_zip_code").html('');  
     $("#pop_up_zip_code").attr({"data-parsley-type":'integer',
                           "data-parsley-length":'[5,5]',
                           "data-parsley-length-message":'Zip/Postal code should be 5 digits long.',
                           "data-parsley-type-message":'Zip/Postal code can only be numbers.'
                         });
   }  
  else if(country_code=='1' && zip_code!="")
    {
      $("#err_zip_code").html('');
      $("#pop_up_zip_code").attr({"data-parsley-type":'alphanum',
                                  "data-parsley-length":'[6,6]',
                                  "data-parsley-length-message":'Zip/Postal code should be 6 characters long.',
                                  "data-parsley-type-message":'Zip/Postal code can only be alphanumeric characters.'
                          });
    }
    $('#signup-form').parsley();
  
  });
});


</script>
<!-- Hide show password functionality by Harshada on date 20 Oct 2020 -->
<script>
$("body").on('click','.toggle-password',function(){
    $(this).toggleClass("fa-eye fa-eye-slash");

    var input = $("#password");

    if (input.attr("type") === "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});

$("body").on('click','.toggle-password-c',function(){
    $(this).toggleClass("fa-eye fa-eye-slash");

    var input = $("#confirm_password");

    if (input.attr("type") === "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});
</script>
@endsection