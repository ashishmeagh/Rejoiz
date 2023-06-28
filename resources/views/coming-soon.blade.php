<!DOCTYPE HTML>
<html lang="en">
<head>

    <title>{{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME")}}- coming-Soon</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    
    
    <!-- Font -->
    
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700%7CPoppins:400,500" rel="stylesheet">
    
    
    <link href="{{url('/')}}/assets/js/coming-soon/css/ionicons.css" rel="stylesheet">
    
    
    <link rel="stylesheet" href="{{url('/')}}/assets/js/coming-soon/css/jquery.classycountdown.css" />
        
    <link href="{{url('/')}}/assets/js/coming-soon/css/styles.css" rel="stylesheet">
    
    <link href="{{url('/')}}/assets/js/coming-soon/css/responsive.css" rel="stylesheet">
    
</head>
<style>
table, th, td {
  border: 1px solid black;
  border-collapse: collapse;
}
</style>
<body>
    
    <div class="main-area center-text" style="background-image:url({{url('/')}}/assets/js/coming-soon/countdown-3-1600x900.jpg);">
        
        <div class="display-table">
            <div class="display-table-cell">
                
                <h1 class="title font-white"><b> Coming Soon</b></h1>
                <p class="desc font-white">{{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz'}} is currently under development mode. Thank you for your patience. Stay Tuned!</p>
                
                {{-- <a class="notify-btn" href="#"><b>NOTIFY US</b></a> --}}
                
               {{--  <ul class="social-btn font-white">
                    <li><a href="#">facebook</a></li>
                    <li><a href="#">twitter</a></li>
                    <li><a href="#">google</a></li>
                    <li><a href="#">instagram</a></li>
                </ul> --}}<!-- social-btn -->

                 <table>
        <tr>
            <th>Brand Name</th>
            
        </tr>
        @foreach ($data as $data1)
        <tr>
            <td>{{$data1}}</td>
         
        </tr>
        @endforeach

    </table>
                
            </div><!-- display-table -->
        </div><!-- display-table-cell -->
    </div><!-- main-area -->
    
</body>
</html>