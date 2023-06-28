<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}">

  
    @if(isset($site_setting_arr['favicon']))
        <link rel="icon" type="image/ico" sizes="16x16" href="{{url('/')}}/storage/app/{{$site_setting_arr['favicon'] or ''}}">
    @endif

    <title>{{$page_title or ''}}-{{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz'}}</title>
    <!-- Bootstrap Core CSS -->

    <script type="text/javascript">
        var SITE_URL = '{{ url('/')}}'; 
    </script>
   
    <script type="text/javascript" src="{{url('/')}}/assets/js/Parsley/dist/parsley.min.js"></script>
    <script type="text/javascript" src="{{url('/')}}/assets/js/Parsley/src/extra/validator/notequalto.js"></script>
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

    {{-- <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">  --}}


    
    <!-- morris CSS -->
    <link href="{{url('/')}}/assets/plugins/bower_components/morrisjs/morris.css" rel="stylesheet">
    <!-- animation CSS -->
    <link href="{{url('/')}}/assets/css/animate.css" rel="stylesheet">
    <!-- Custom CSS -->
     
    <link href="{{url('/')}}/assets/css/our-style.css" rel="stylesheet">

    <link href="{{url('/')}}/assets/css/style.css" rel="stylesheet">
    <!-- color CSS -->
    <link href="{{url('/')}}/assets/css/colors/blue.css" id="theme" rel="stylesheet">

    <link href="{{url('/')}}/assets/sweetalert/sweetalert.css" rel="stylesheet">

    <link href="{{url('/')}}/assets/js/Parsley/dist/parsley.css" rel="stylesheet">

    <link href="{{url('/')}}/assets/css/custom.css" rel="stylesheet">

    <link rel="stylesheet" href="{{url('/')}}/assets/css/jquery-ui.css">


    <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">


   
    <!-- switechery css-->
    <link href="{{url('/')}}/assets/plugins/bower_components/switchery/dist/switchery.min.css" rel="stylesheet" />

    <!-- Datepicker css-->
    <link href="{{url('/')}}/assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
   

    <script type="text/javascript" src="{{url('/')}}/assets/js/jquery.min.js"></script>   
    <!--Datepicker js-->
    <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
    
    <script src="{{url('/')}}/assets/plugins/bower_components/morrisjs/morris.js"></script>

    <!-- Dropify -->
    <link rel="stylesheet" href="{{url('/')}}/assets/plugins/bower_components/dropify/dist/css/dropify.min.css">

    <script type="text/javascript" src="https://code.jquery.com/jquery-migrate-3.0.0.min.js"></script>

    <!-- Select2 -->
    <link href="{{url('/')}}/assets/css/select2.min.css" rel="stylesheet" />
    <link href="{{url('/')}}/assets/css/common.css" rel="stylesheet" />
    <script src="{{url('/')}}/assets/js/select2.min.js"></script>


     <script type="text/javascript" src="{{url('/')}}/assets/js/jquery-ui.js"></script>

    @if(Sentinel::check()==true)
    <script type="text/javascript" src="{{url('/')}}/assets/js/module_js/notification.js"></script>
    @endif

 <style>
    .dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
}

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}
 </style>   
    
</head>

@php
$login_user = Sentinel::check();
@endphp


 <script type="text/javascript">
     var common_controller_url  = '{{url('/')}}/common';

    // This is an example script - don't forget to change it!
// LogRocket.identify('ccf799d6-d83d-4e2c-bb90-0a5e5d0f8151', {
//   name: '{{$login_user->first_name}}',
//   email: '{{$login_user->email}}',
//   // Add your own custom user variables here, ie:
//   subscriptionType: 'pro'
// });

 </script>



<script type="text/javascript">
    var common_controller_url  = '{{url('/')}}/common';
</script>
<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div>
    <div id="wrapper">