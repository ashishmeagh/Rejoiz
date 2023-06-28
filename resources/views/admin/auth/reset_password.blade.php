
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
    <title>{{ $site_setting_arr['site_name'] or '' }} - Reset Password</title>
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
    <!-- Custom CSS -->
    <link href="{{url('/')}}/assets/css/style.css" rel="stylesheet">
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

                 <img src="{{url('/')}}/storage/app/{{$site_setting_arr['login_site_logo']}}" alt="{{$site_setting_arr['site_name']}}" />
                </div>
                
                {!! Form::open([ 'url' => $admin_panel_slug.'/reset_password',
                                 'method'=>'POST',
                                 'id'=>'form-reset_password'
                                ]) !!}
                                    
                    @include('admin.layout._operation_status')  
                 
                 {{ csrf_field() }}
                    <h3 class="box-title m-b-20">Reset Password</h3>
                    <div class="form-group ">
                        <div class="col-xs-12">
                           <input class="form-control" 
                               type="password" 
                               placeholder="New Password"  
                               name="password" 
                               id="new_password"
                               data-parsley-required="true"
                               data-parsley-required-message="Please enter password."
                               data-parsley-pattern="^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$"
                               data-parsley-pattern-message="<ul><li>Password must contain at least one number,one uppercase letter,one special character.</li><li>Password length should be at least 8 or more characters.</li></ul>"
                               data-parsley-minlength="8"/>
                            <span class="red">{{ $errors->first('password') }} </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-xs-12">
                           <input class="form-control" 
                               type="password" 
                               placeholder="Confirm Password" 
                               data-parsley-equalto="#new_password" 
                               data-parsley-error-message="Confirm password should be same as new password." data-parsley-required="true" 
                               data-parsley-required-message="Please enter confirm password." 
                               name="confirm_password" 
                               id="cnfm_new_password" />

                        <span class="red">{{ $errors->first('confirm_password') }} </span>
                        </div>
                    </div>
                    <input type="hidden" name="enc_id" value="{{ $enc_id or '' }}" />
                    <input type="hidden" name="enc_reminder_code"  value="{{ $enc_reminder_code or '' }}"/>
                    
                    <div class="form-group text-center m-t-20">
                        <div class="col-xs-12">
                            <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Change Password</button>
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
  $(document).ready(function(){
    $('#form-reset_password').parsley();
});



</script>
