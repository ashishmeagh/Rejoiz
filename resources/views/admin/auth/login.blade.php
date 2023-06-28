<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    

     @if(isset($site_setting_arr['favicon']))
        <link rel="icon" type="image/ico" sizes="16x16" href="{{url('/')}}/storage/app/{{$site_setting_arr['favicon'] or ''}}">
    @endif
    
    {{-- <title>{{ env('APP_NAME','RWaltz') }} - Login</title> --}}
    <title>Admin Login - {{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz'}}</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{url('/')}}/assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{url('/')}}/assets/plugins/bower_components/bootstrap-extension/css/bootstrap-extension.css" rel="stylesheet">
    <!-- animation CSS -->
    <link href="{{url('/')}}/assets/css/animate.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{url('/')}}/assets/css/style.css" rel="stylesheet">
    <!-- color CSS -->
    <link href="{{url('/')}}/assets/css/colors/blue.css" id="theme" rel="stylesheet">
    <!-- Parsley css-->
    <link href="{{url('/')}}/assets/js/Parsley/dist/parsley.css" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div>
    <section id="wrapper" class="login-register">
        <div class="login-box loginbgman">
            <div class="white-box">
                <div class="admin-logo-lg">
                 
                @php
                    $site_img = false;

                    $site_base_img = isset($site_setting_arr['login_site_logo']) ? $site_setting_arr['login_site_logo'] : false;

                    //$site_image_base_path = base_path('storage/app/'.$site_base_img);

                    $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;
                    
                    //$site_default_image = url('https://via.placeholder.com/160x53.png?text='.$site_name);
                    //$site_img = image_resize($site_image_base_path,160,53,$site_default_image);

                    $site_img = imagePath($site_base_img,'site_logo',0);
                @endphp

                 <img src="{{ $site_img }}" alt="{{ $site_name }}" />
                </div>
             <form class="form-horizontal form-material" id="loginform" action="{{url('/')}}/admin/process_login" method="POST">
               
                    @include('admin.layout._operation_status')  
                  

          @if(Session::has('message'))
          
           {{-- <p class="alert {{ Session::get('alert-class', 'alert-success') }}" id ="alert_container"><a href="javascript:void(0)"></a>{{ Session::get('message') }}</p> --}}


            <div class="alert {{ Session::get('alert-class', 'alert-success') }}" id ="alert_container">
            <a href="javascript:void(0)" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              {{ Session::get('message') }}
            </div>

          @endif

                 {{ csrf_field() }}
                    <h3 class="box-title m-b-20">Sign In</h3>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control" type="text" value="{{isset($_COOKIE["email"])?$_COOKIE["email"]:''}}" name="email" data-parsley-required="true" data-parsley-required-message="Please enter email address." class="form-control" data-parsley-pattern="[^@\s]+@(?:[-a-zA-Z0-9]+\.)+[a-z ]{2,}"  data-parsley-pattern-message="Please enter valid email address." placeholder="Email">
                             <span class="red">{{ $errors->first('email') }} </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12 rw-login-user-box">
                          
                            <input type="password" class="form-control" name="password" id="password"data-parsley-required="true" data-parsley-required-message="Please enter password." placeholder="Password" data-parsley-minlength="6" data-parsley-minlength-message="Password is invalid.it should be atleast 6 characters long." data-parsley-maxlength="16" data-parsley-maxlength-message="Password is invalid.it should be maximum 16 characters long." value="{{isset($_COOKIE['password'])?$_COOKIE['password']:''}}">
                            <span class="red">{{ $errors->first('password') }} </span>
                             <p toggle="#password-field" class="fa fa-fw fa-eye field_icon toggle-password"></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <div class="checkbox checkbox-primary pull-left p-t-0">
                                <input id="checkbox-signup" type="checkbox" @if(isset($_COOKIE["rememberd"])&& $_COOKIE["rememberd"]=='rememberd') checked="checked" @endif name="remember_me">
                                <label for="checkbox-signup"> Remember me </label>
                            </div>
                            <a href="javascript:void(0)" id="to-recover" class="text-dark pull-right"><i class="fa fa-lock m-r-5"></i> Forgot Password?</a> 
                        </div>
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-success btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Log In</button>
                        </div>
                    </div>
                   
                    
                </form>
                <form class="form-horizontal" method="post" id="recoverform" action="{{ url($admin_panel_slug.'/process_forgot_password') }}">
                    {{ csrf_field() }}
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <h3>Recover Password</h3>
                            <p class="text-muted">Enter your email and instructions will be sent to you! </p>
                        </div>
                    </div>
                    <div class="form-group ">
                        <div class="col-xs-12">
                            <input class="form-control" type="text" name="email" data-parsley-required="true" data-parsley-required-message="Please enter email address." data-parsley-pattern="[^@\s]+@(?:[-a-zA-Z0-9]+\.)+[a-z ]{2,}" data-parsley-pattern-message="Please enter valid email address." placeholder="Email">
                            <span class="red">{{$errors->first('email')}}</span>
                        </div>
                        <a href="javascript:void(0)" id="to-admin-login" class="text-dark pull-right m-t-10"><i class="fa fa-arrow-left"></i> Back to login</a> 
                    </div>
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Reset</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
    <!-- jQuery -->
    <script src="{{url('/')}}/assets/plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="{{url('/')}}/assets/bootstrap/dist/js/tether.min.js"></script>
    <script src="{{url('/')}}/assets/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-extension/js/bootstrap-extension.min.js"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="{{url('/')}}/assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js"></script>
    <!--slimscroll JavaScript -->
    <script src="{{url('/')}}/assets/js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="{{url('/')}}/assets/js/waves.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="{{url('/')}}/assets/js/custom.min.js"></script>
    <!--Style Switcher -->
    <script src="{{url('/')}}/assets/plugins/bower_components/styleswitcher/jQuery.style.switcher.js"></script>
    <!--Parsley js-->
    <script type="text/javascript" src="{{url('/')}}/assets/js/Parsley/dist/parsley.min.js"></script>
</body>

</html>
<script type="text/javascript">
    $(document).ready(function()
    {

         $('.emailClass').blur(function(){
            var myStr = $(".emailClass").val();
            var trimStr = $.trim(myStr);
            $(".emailClass").val(trimStr);
          
        });

        $('#loginform').parsley();
        $('#recoverform').parsley();

    });

    $('#to-admin-login').on("click", function () 
    {
        $("#recoverform").slideUp();
        $("#loginform").fadeIn();
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
</script>