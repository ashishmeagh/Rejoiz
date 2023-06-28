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
    <title>{{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME")}}</title>

    <!-- Bootstrap Core CSS -->
    <link href="{{url('/')}}/assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{url('/')}}/assets/admin/plugins/bower_components/bootstrap-extension/css/bootstrap-extension.css" rel="stylesheet">
    <!-- animation CSS -->
    <link href="{{url('/')}}/assets/css/animate.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{url('/')}}/assets/css/style.css" rel="stylesheet">
    <!-- color CSS -->
    <link href="{{url('/')}}/assets/css/colors/default.css" id="theme" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
    <script>
    (function(i, s, o, g, r, a, m) {
        i['GoogleAnalyticsObject'] = r;
        i[r] = i[r] || function() {
            (i[r].q = i[r].q || []).push(arguments)
        }, i[r].l = 1 * new Date();
        a = s.createElement(o),
            m = s.getElementsByTagName(o)[0];
        a.async = 1;
        a.src = g;
        m.parentNode.insertBefore(a, m)
    })(window, document, 'script', 'https://www.google-analytics.com/analytics.js', 'ga');

    ga('create', 'UA-19175540-9', 'auto');
    ga('send', 'pageview');
    </script>
</head>

<body>
    <section id="wrapper" class="error-page">
        <div class="error-box">
            <div class="error-body text-center">
                <h1>403</h1>
                <h3 class="text-uppercase">Access Forbidden.</h3>
                
                {{-- <a href="{{url('/')}}/admin/dashboard" class="btn btn-info btn-rounded waves-effect waves-light m-b-40">Back to home</a> --}} </div>
            <footer class="footer text-center">{{date('Y')}} © {{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME")}}</footer>
        </div>
    </section>
    <!-- jQuery -->
    <script src="{{url('/')}}/assets/plugins/bower_components/jquery/dist/jquery.min.js"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="{{url('/')}}/assets/bootstrap/dist/js/tether.min.js"></script>
    <script src="{{url('/')}}/assets/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-extension/js/bootstrap-extension.min.js"></script>
    <!-- Custom Theme JavaScript -->
    <script src="{{url('/')}}/assets/js/custom.min.js"></script>
</body>

</html>
