@extends('front.layout.master')
@section('main_content')
@php
 $influencer_code = '';
 $influencer_code = get_influencer_code();
@endphp
<style>
   #err_post_code {
      margin: 10px 0 10px;
      padding: 0;
      font-size: 0.9em;
      line-height: 0.9em;
      color: red;
   }
</style>
<meta name="google-signin-client_id" content="753906644363-p0gfb3ecld76k0c3dhuen3hqh6hr91jp.apps.googleusercontent.com">
<div class="login-main-bg-img spacesignups">
   <div class="login-main-page-dv-hm cover-img signupsclasssign new-img-influencer">


      <div class="login-right-page none-spacet ">
         

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
         <div class="welcomebackkadoe">Create your Influencer Account</div>

         <div id="status_msg"></div>
         <form id="signup-form" class="custom-signup-form">
            {{ csrf_field() }}
            <div class="loginform">
               <div class="row">
                  <div class="row1">
                     <div class="col-md-6">
                        <div class="user-box">
                           <label class="form-lable">First Name<i class="red">*</i></label>
                           <input class="cont-frm" placeholder="Enter Your First Name" type="text" name="first_name" id="first_name" data-parsley-required="true" data-parsley-required-message="Please enter first name." data-parsley-maxlength="50" data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Only alphabets are allowed" />
                        </div>
                     </div>
                     <div class="col-md-6">
                        <div class="user-box">
                           <label class="form-lable">Last Name<i class="red">*</i></label>
                           <input class="cont-frm" placeholder="Enter Your Last Name" type="text" name="last_name" id="last_name" data-parsley-required="true" data-parsley-required-message="Please enter last name." data-parsley-maxlength="50" data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Only alphabets are allowed" />
                        </div>
                     </div>
                  </div>
                  <div class="row1">
                     <div class="col-md-6">
                        <div class="user-box">
                           <label class="form-lable">Email Address<i class="red">*</i></label>
                           <input class="cont-frm" placeholder="Enter Your Email Address" type="email" name="email" id="email" data-parsley-required="true" data-parsley-required-message="Please enter email address." data-parsley-type-message="please enter valid email." />
                        </div>
                     </div>

                     <div class="col-md-6">
                        <div class="user-box">
                           <label class="form-lable">Rejoiz Influencer Code<i class="red">*</i></label>
                           <input class="cont-frm" placeholder="Enter Your Rejoiz influencer Code" type="text" name="influencer_code" id="influencer_code" data-parsley-required="true" data-parsley-required-message="Please enter rejoiz influencer code" data-parsley-maxlength="50" data-parsley-trigger="change" data-parsley-whitespace="trim" value="{{ $influencer_code }}" readonly style="background-color: #eee;"/>
                       </div>  
                     </div>
                  </div>
                  <div class="row1">

                     <div class="col-md-6">
                        <div class="user-box rw-login-user-box" id="user_pwd">
                           <label class="form-lable">Password<i class="red">*</i></label>
                           <input class="cont-frm" placeholder="Enter Your Password" type="password" name="password" id="password" data-parsley-required="true" data-parsley-required-message="Please enter password." data-parsley-pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$" data-parsley-pattern-message="<ul><li>Password must contain at least one number,one uppercase letter,one special character.</li><li>Password length should be at least 8 or more characters.</li></ul>" data-parsley-minlength="8" autocomplete="new-password" />
                            <span toggle="#password-field" class="fa fa-fw fa-eye field_icon toggle-password"></span>
                        </div>
                     </div>

                     <div class="col-md-6">
                        <div class="user-box rw-login-user-box">
                           <label class="form-lable">Confirm Password<i class="red">*</i></label>
                           <input class="cont-frm" placeholder="Enter Your Confirm Password" data-parsley-equalto="#password" data-parsley-error-message="Confirm password should be same as password" type="password" name="confirm_password" id="confirm_password" data-parsley-required="true" />
                           <span toggle="#password-field" class="fa fa-fw fa-eye field_icon toggle-password-c"></span>
                        </div>
                     </div>
                  </div>
                  <div class="row1">
                     <div class="col-md-6">
                        <div class="user-box">
                           <label class="form-lable">Country <i class="red">*</i></label>
                           <select name="country_id" id="country_id" data-parsley-required="true" data-parsley-required-message="Please select country.">
                              <option value="">Select Country</option>
                              @if(isset($arr_country) && sizeof($arr_country)>0)
                              @foreach($arr_country as $country)
                              <option value="{{$country['id'] or ''}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                              @endforeach
                              @endif
                           </select>
                        </div>
                     </div>

                     <div class="col-md-6">
                        <div class="user-box">
                           <label class="form-lable">Mobile No.<i class="red">*</i></label>
                           <input 
                           class="cont-frm" 
                           placeholder="Enter Your Mobile No." 
                           type="text" 
                           name="contact_no" 
                           id="contact_no" 
                           data-parsley-pattern="^[0-9*#+]+$" 
                           data-parsley-required 
                           data-parsley-pattern-message="Please enter valid mobile number." data-parsley-required="true" 
                           {{-- data-parsley-maxlength="18"  --}}
                           data-parsley-maxlength-message="Mobile No must be less than 14 digits."
                           {{-- data-parsley-minlength-message="Mobile No should be of 10 digits." --}} data-parsley-required-message="Please enter mobile no." 
                           />
                           <!--  data-parsley-type="number"  -->
                           <input type="hidden" name="hid_country_code" id="hid_country_code">
                        </div>
                     </div>
                     
                  </div>

                  <div class="row1">

                     <div class="col-md-6">
                        <div class="user-box">
                           <label class="form-lable">Zip/Postal Code<i class="red">*</i></label>
                           <input oninput="this.value = this.value.toUpperCase()" type="text" class="cont-frm" name="zip_code" id="post_code" placeholder="Enter Zip/Postal Code" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code." data-parsley-trigger="change" />
                           <span id="err_post_code">{{ $errors->first('post_code') }}</span>
                        </div>
                     </div>
                  </div>

               </div>
               <div class="remeber">
                  <div class="checkbx">
                     <input type="checkbox" class="filled-in" id="check1" data-parsley-required="true" data-parsley-errors-container="#term_con" data-parsley-required-message="Please accept terms & conditions." />
                     <label for="check1">I accept the terms & conditions<i class="red">*</i></label>
                  </div>
                  <div class="clearfix"></div>
                  <div id="term_con"></div>
               </div>
               <input type="hidden" name="role" id="role" value="influencer">
               <div class="button-login-pb">
                  <!-- <a href="signup.html" class="gt-button">Register</a> -->
                  <button type="button" class="gt-button" id="btn_signup">Sign Up</button>
                  <div class="clearfix"></div>
                  <div class="account-links-nw">Already a member? <a href="{{ url('/login')}}">Sign In here</a></div>
               </div>
            </div>
         </form>
      </div>
      <div class="clearfix"></div>
   </div>
</div>
<script src="https://apis.google.com/js/client:platform.js?onload=renderButton" async defer></script>
<script type="text/javascript">
   $(document).ready(function() {

      $("#country_id").change(function() {

         $("#err_post_code").html('');
         var country_code = $("#country_id").val();
         var post_code = $("#post_code").val();

         var phone_code = $('option:selected', this).attr('phone_code');
         var zipcode_length = $('option:selected', this).attr('zipcode_length');
         var countryName = $('option:selected', this).attr('country_name');

         if (phone_code) {
            $("#contact_no").val('+' + phone_code);
            $("#contact_no").attr('code_length', phone_code.length + 1);
            $("#hid_country_code").val('+' + phone_code);
         } else {
            $("#contact_no").val('');
            $("#contact_no").attr(0);
            $("#hid_country_code").val('');
         }


         if (country_code == "" && post_code != "") {
            $("#err_post_code").html('Invalid zip/postal code.');
         }

            var codeLength = jQuery('#hid_country_code').val();
            var minPhone = 7 + codeLength.length;            
            $('#contact_no').attr('data-parsley-minlength', minPhone);
            $('#contact_no').attr('data-parsley-minlength-message', 'Mobile No. must be greater than or equal to 7 digits');  


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
                    '  characters.',
                });
            }
            else{
                $('#post_code').attr('parsley-maxlength', false);
                $('#post_code').attr('data-parsley-maxlength-message', "");
                $("#post_code").attr({
                "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
                "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                    zipcode_length +
                    '  digits.'
                });
            }

         $('#signup-form').parsley();

      });


      // To fixed code no in mobile no textbox
      // By Harshada on date 02 Sept 2020
      $("#contact_no").keydown(function(event) {
         var text_length = ($("#contact_no").attr('code_length')) ? $("#contact_no").attr('code_length') : "";
         if (event.keyCode == 8) {
            this.selectionStart--;
         }
         if (this.selectionStart < text_length) {
            this.selectionStart = text_length;
            event.preventDefault();
         }
      });
      $("#contact_no").keyup(function(event) {
         var text_length = ($("#contact_no").attr('code_length')) ? $("#contact_no").attr('code_length') : "";
         if (this.selectionStart < text_length) {
            this.selectionStart = text_length;
            console.log(this.selectionStart);
            event.preventDefault();
         }
      });
   });

   $('#btn_signup').click(function() {
      var country_code = $("#country_id").val();
      var post_code = $("#post_code").val();

      if (country_code != "" && post_code != "") {
         $("#err_post_code").html('');
      } else if (country_code == "" && post_code == "") {
         $("#err_post_code").html('');
      } else if (country_code == "" && post_code != "") {
         $("#err_post_code").html('Invalid zip/postal code.');
      } else if (country_code != "" && post_code == "") {
         $("#err_post_code").html('');
      }


      /*------------password validation---------------------*/

      var password = $('#password').val();

      if (password != '') {
         $('#user_pwd').addClass('user-box_password');
      } else if (password == '') {
         $('#user_pwd').removeClass('user-box_password');
      }

      /*----------------------------------------------------*/

      if ($('#signup-form').parsley().validate() == false) return;

      var form_data = $('#signup-form').serialize();
      
      var url = "{{url('/')}}/process_signup";

      if ($('#signup-form').parsley().isValid() == true) {

         $.ajax({
            url: url,
            data: form_data,
            method: 'POST',

            beforeSend: function() {
               showProcessingOverlay();
               $('#btn_signup').prop('disabled', true);
               $('#btn_signup').html('Please Wait <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
            },
            success: function(response) {
               
               hideProcessingOverlay();
               $('#btn_signup').prop('disabled', false);
               $('#btn_signup').html('Sign Up');

               if (typeof response == 'object') {
                  scrollToTop();
                  
                  if (response.status == "SUCCESS") {
                     $("#signup-form")[0].reset();

                     swal({
                              title: "Influencer Account Created",
                              text: response.msg,
                              type: 'success',
                              icon: "success",
                              confirmButtonText: "Ok",
                              closeOnConfirm: false
                              },
                              function(isConfirm) {
                                 location.reload();
                              });
                  } else {
                     var error_HTML = '';
                     error_HTML += '<div class="alert alert-danger alert-dismissible">\
                         <button type="button" class="close" data-dismiss="alert" aria-label="Close">\
                             <span aria-hidden="true">&times;</span>\
                         </button>' + response.msg + '</div>';

                     $('#status_msg').html(error_HTML);
                  }
               }
            }
         });
      }
   });


   /*password validation on password field onchange*/
   $('#password').on("change keyup blur", function() {

      var password = $('#password').val();

      if (password != '') {
         $('#user_pwd').addClass('user-box_password');
      } else if (password == '') {
         $('#user_pwd').removeClass('user-box_password');
      }

      if ($('#password').parsley().validate() == false) return;

   });


   /*Login with facebook */
   window.fbAsyncInit = function() {
      // FB JavaScript SDK configuration and setup
      FB.init({
         appId: '376161336631554', // FB App ID
         cookie: true, // enable cookies to allow the server to access the session
         xfbml: true, // parse social plugins on this page
         version: 'v2.8' // use graph api version 2.8
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
      js = d.createElement(s);
      js.id = id;
      js.src = "//connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));

   // Facebook login with JavaScript SDK
   function fbLogin() {
      FB.login(function(response) {
         if (response.authResponse) {
            // Get and display the user profile data
            getFbUserData();
         } else {
            // document.getElementById('status').innerHTML = 'User cancelled login or did not fully authorize.';
         }

      }, {
         scope: 'email'
      });
   }

   // Fetch the user profile data from facebook
   function getFbUserData() {
      FB.api('/me', {
            locale: 'en_US',
            fields: 'id,first_name,last_name,email,link,gender,locale,picture'
         },
         function(response) {
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
      gapi.client.load('oauth2', 'v2', function() {
         var request = gapi.client.oauth2.userinfo.get({
            'userId': 'me'
         });
         request.execute(function(resp) {
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



   function scrollToTop() {
      $('html, body').animate({
         scrollTop: 0
      }, $(window).scrollTop() / 3);
      return false;
   };
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