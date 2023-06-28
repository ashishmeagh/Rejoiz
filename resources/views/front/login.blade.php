@extends('front.layout.master')
@section('main_content')
<div class="login-main-bg-img">
    
    <div class="login-main-page-dv-hm">
        <!-- <div class="banner-block">
            <div class="img-lgn-in">
            <div class="titlelogin-leftside">Welcome back</div>
            <div class="login-welcm">Please login to your account</div>

            <div class="social-button-main-login">
              
                {{-- <a href="{{ url('/login/facebook') }}"><i class="fa fa-facebook"></i> Login with Facebook</a>
                <a href="#"><i class="fa fa-twitter"></i> Login with Twitter</a> --}}
            </div>

            <div class="account-links-nw">Don't have an account? <a href="{{url('/signup_retailer')}}">Sign up now!</a></div>

            </div>
        </div> -->
        <div class="col-sm-4 login-right-page">

          @if(Session::has('message'))
          
           {{-- <p class="alert {{ Session::get('alert-class', 'alert-success') }}" id ="alert_container"><a href="javascript:void(0)"></a>{{ Session::get('message') }}</p> --}}

            <div class="alert {{ Session::get('alert-class', 'alert-success') }}" id ="alert_container">
            <a href="javascript:void(0)" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              {{ Session::get('message') }}
            </div>

          @endif

          @if(Session::has('warning'))
        
            <div class="alert alert-warning" id ="alert_container">
            <a href="javascript:void(0)" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              {{ Session::get('warning') }}
            </div>

          @endif

          @php
            $site_img = false;

            $site_base_img = isset($site_setting_arr['login_site_logo']) ? $site_setting_arr['login_site_logo'] : false;

            //$site_image_base_path = base_path('storage/app/'.$site_base_img);

            $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;
            
           // $site_default_image = url('https://via.placeholder.com/160x53.png?text='.$site_name);
            
            //$site_img = image_resize($site_image_base_path,160,53,$site_default_image);

            $site_img = imagePath($site_base_img,'site_logo',0);
        @endphp

  <div class="kadoe-logo">
    {{-- <img src="{{url('/')}}/assets/front/images/k-logo.jpg" alt="" /> --}}
     <img src="{{ $site_img }}" alt="{{ $site_name }}" />
  </div>
  <div class="welcomebackkadoe">Welcome Back To {{$site_setting_arr['site_name'] or ''}}!</div>

            {{-- <div class="titlelogin">Login</div> --}}
            <div id="status_msg"></div>
             @include('admin.layout._operation_status')
             <form id="form_login" onsubmit="return false;">
              {{csrf_field()}} 
              <div class="loginform">
                 <div class="user-box">
                    <label class="form-lable">Email Address<i class="red">*</i></label>
                    <input class="cont-frm" placeholder="Enter Your Email Address" type="text" id="user_email" name="user_email" data-parsley-required="true" data-parsley-required-message="Please enter email address." data-parsley-pattern="[^@\s]+@(?:[-a-zA-Z0-9]+\.)+[a-z ]{2,}"  data-parsley-pattern-message="Please enter valid email address." value="{{(isset($_COOKIE["email"]))?$_COOKIE["email"]:''}}"/>
                  </div>
                  <div class="user-box rw-login-user-box">
                    <label class="form-lable">Password<i class="red">*</i></label>
                    <input class="cont-frm" placeholder="Enter Your Password" type="password" id="user_password" name="user_password" data-parsley-required="true" data-parsley-required-message="Please enter password." value="{{(isset($_COOKIE["password"]))?$_COOKIE["password"]:''}}"/>
                    <span toggle="#password-field" class="fa fa-fw fa-eye field_icon toggle-password"></span>
                  </div>

                <div class="remeber">
                     <div class="checkbx">
                        <input type="checkbox" class="filled-in" id="check1" id="check" name="remember_me" value="1" {{ (isset($_COOKIE["email"]))?'checked':'' }}/>
                        <label for="check1">Remember Me</label>
                     </div>
                        
                    {{-- <h5><a href="{{url('/')}}/forgot_password">Forgot Password?</a></h5> --}}
                    <div class="clearfix"></div>
                </div>


            <div class="button-login-pb">
                 <button type="submit" id="btn_login" data-toggle="modal" class="gt-button signin-b-margin">Sign In</button>
                 <div class="clearfix"></div>
                 <div class="account-links-nw">Don't have an account? <a href="{{url('/signup_retailer')}}">Sign Up Now!</a></div>
                 <div class="account-links-nw">Forgot your password? <a href="{{url('/')}}/forgot_password">Reset Password?</a></div>
            </div>
              
              </div>
            </form>  

        </div>
        <div class="clearfix"></div>
    </div>
</div>

 <input type="hidden" name="csrfToken" id="csrfToken" value="{{csrf_token()}}">



<!-- Modal Sign IN  Start -->
<div id="CongratulationsModal" class="modal fade login-popup" data-replace="true" style="display: none;">
    <div class="modal-dialog fancy-modal-popup">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="close" data-toggle="modal" href="#ModalCategory"><img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="" id="close_img"/> </button>
               <div class="login-form-block">
                        <div class="login-content-block">
                          <div class="congratulationsimage"></div>
                            <div class="successfully-title">Congratulations </div>  
                            <div class="login-content-block">
                           {{--    <div class="welcm-cong-title">Welcome</div> --}}
                             <div class="cong-text">You are registered successfully in Just Got Have It.</div>
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
    <div class="modal-dialog">
        <!-- Modal content-->
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
                            <span class="red">Note: Please choose at least one category</span>
                          </div>
                          <div class="pull-right">
                            <a class="logi-link-block btn-primary"  data-toggle="modal" id="btn_skip">Skip</a> 
                            <a class="logi-link-block btn-primary" data-toggle="modal" id="btn_submit">Submit</a>
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

 <script type="text/javascript">
   
   $(document).ready(function(){

  $("#close_img").click(function () {
      $("#CongratulationsModal").modal("hide");
  });
    $('#user_email').blur(function(){
        var myStr = $("#user_email").val();
        var trimStr = $.trim(myStr);
        $("#user_email").val(trimStr);
      
    });

      $('#btn_login').click(function()
      {
          if($('#form_login').parsley().validate()==false) return;
          var formdata = new FormData($("#form_login")[0]);
          var url = '{{url("/")}}/process_login';
              $.ajax({
                          
                  url: '{{url("/")}}/process_login',
                  type:"POST",
                  data: formdata,
                  dataType:'json',
                  contentType:false,
                  processData:false,
                  beforeSend : function()
                  {
                    showProcessingOverlay();
                    $('#btn_login').prop('disabled',true);
                    $('#btn_login').html('Updating... <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
                  },
                  success:function(data)
                  {
                      hideProcessingOverlay();

                      //return false;
                      $('#btn_login').prop('disabled',false);
                      $('#btn_login').html('SIGN IN');
                      
                      if('success' == data.status)
                      {
                          $('#form_login')[0].reset();
                           
                       
                         /* if(data.login_count == 0)
                          { 

                            update_user_active_time();
                            $('#CongratulationsModal').modal('show');
                          }
                          else
                          { */
                            window.location = data.redirect_link;

                          //}

                                 
                          
                      }
                      else if(data.status=='ERROR')
                      {
                          // swal('Opps...',data.message,'error');
                          var error_msg = `<div class="alert alert-danger alert-dismissible fade in">
                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                              `+data.message+`
                            </div>`;

                            $('#status_msg').html(error_msg);

                             setTimeout(function () {
                                //alert('Reloading Page');
                                location.reload(true);
                              }, 5000);
                      }
                      else
                      {
                          var error_msg = `<div class="alert alert-danger alert-dismissible fade in">
                              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                              `+data.description+`
                            </div>`;

                            $('#status_msg').html(error_msg);

                            setTimeout(function () {
                                //alert('Reloading Page');
                                location.reload(true);
                              }, 5000);
                         //swal('warning',data.description,data.status);
                      }  
                  }
                });   
        });






   });


  $('#btn_skip').click(function(){
     //$.session('message','Please fill all the required  profile fields');
     window.location = '{{url('/retailer/account_settings')}}';
     
   
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
                           

 </script>

  <script type="text/javascript" src="{{url('/')}}/assets/js/after_login_common.js"></script>


</script>

<!-- Hide show password functionality by Harshada on date 20 Oct 2020 -->
<script>
$("body").on('click','.toggle-password',function(){
    $(this).toggleClass("fa-eye fa-eye-slash");

    var input = $("#user_password");

    if (input.attr("type") === "password") {
        input.attr("type", "text");
    } else {
        input.attr("type", "password");
    }
});
</script>
 
@endsection