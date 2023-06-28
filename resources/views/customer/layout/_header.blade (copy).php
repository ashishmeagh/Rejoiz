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

    <title>{{$page_title or ''}}-{{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz'}}</title>
    <!-- Bootstrap Core CSS -->

    {{-- <script src="{{url('/assets/js/cart/cart.js')}}"></script> --}}

   

    <link href="{{url('/')}}/assets/bootstrap/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{url('/')}}/assets/plugins/bower_components/bootstrap-extension/css/bootstrap-extension.css" rel="stylesheet">
    <!-- Menu CSS -->
    <link href="{{url('/')}}/assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.css" rel="stylesheet">
    <!-- toast CSS -->
    <link href="{{url('/')}}/assets/plugins/bower_components/toast-master/css/jquery.toast.css" rel="stylesheet">

    <link href="{{url('/')}}/assets/plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.css" rel="stylesheet">
    <!-- Color picker plugins css -->
    <link href="{{url('/')}}/assets/plugins/bower_components/jquery-asColorPicker-master/css/asColorPicker.css" rel="stylesheet">

     <!-- Datatble spinner css -->
     <link href="{{url('/')}}/assets/css/datatable_spinner.css" rel="stylesheet">

    <!-- morris CSS -->
    <link href="{{url('/')}}/assets/plugins/bower_components/morrisjs/morris.css" rel="stylesheet">
    <!-- animation CSS -->
    <link href="{{url('/')}}/assets/css/animate.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{url('/')}}/assets/css/our-style.css" rel="stylesheet">
    <link href="{{url('/')}}/assets/css/style-retailer.css" rel="stylesheet">
    <!-- color CSS -->
    <link href="{{url('/')}}/assets/css/colors/blue-retailer.css" id="theme" rel="stylesheet">
    <link href="{{url('/')}}/assets/css/just-got-maker.css"rel="stylesheet">

    <link href="{{url('/')}}/assets/sweetalert/sweetalert.css" rel="stylesheet">

    <link href="{{url('/')}}/assets/js/Parsley/dist/parsley.css" rel="stylesheet">

    <link href="{{url('/')}}/assets/css/custom.css" rel="stylesheet">

     <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">

    <!-- switechery css-->
    <link href="{{url('/')}}/assets/plugins/bower_components/switchery/dist/switchery.min.css" rel="stylesheet" />

    <!-- Datepicker css-->
    <link href="{{url('/')}}/assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
   
    <script type="text/javascript" src="{{url('/')}}/assets/js/jquery.min.js"></script>

    @if(Sentinel::check()==true)
    <script type="text/javascript" src="{{url('/')}}/assets/js/module_js/notification.js"></script>
    @endif 

    <!--Datepicker js-->
    <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    
    <script src="{{url('/')}}/assets/plugins/bower_components/morrisjs/morris.js"></script>

    <!-- Dropify -->
    <link rel="stylesheet" href="{{url('/')}}/assets/plugins/bower_components/dropify/dist/css/dropify.min.css">

    <link href="{{url('/')}}/assets/plugins/bower_components/custom-select/custom-select.css" rel="stylesheet" type="text/css" />
    
    <link href="{{url('/')}}/assets/plugins/bower_components/multiselect/css/multi-select.css" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
        const SITE_URL = "{{ url('/')}}";
    </script>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div>
    <div id="wrapper">