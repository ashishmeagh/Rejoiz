<!DOCTYPE html>
<html>
    <head>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <!-- <link rel="icon" href="{{url('/')}}/images/logo.png" type="image/x-icon" /> -->
        <style>
            html, body {
                height: 100%;
            }

.face {
    background-image: url(assets/front/images/blick-logo.png);
    display: block; position: absolute;
    left: 42px;
    top: 10px;
    background-position: center;
    background-repeat: no-repeat;
    justify-content: space-between;
    width: 66px;
    height: 45px;
    /*background: tomato;*/
    transition: .3s ease-in-out;
}
.logo-new-blick a{position: relative; display: inline-block;}
.logo-new-blick{position: relative;height: 55px;margin-bottom: 4px;margin-top: 4px;}
.eye.eye-ftr{left: 43px;}
.eye {background: #333;width: 10px;display: inline-block;height: 10px;border-radius: 50%; position: absolute; 
-webkit-transform-origin: 50%;-webkit-animation: blink 4s infinite;left: 12px;top: 22px;}
@-webkit-keyframes blink {
0%, 100% {transform: scale(1, .05);}
5%, 95% {transform: scale(1, 1);}
}
 .hovereffect p.icon-links a.active{color: #0098da;}

.eye:before{background-image: url(assets/front/images/eyebros-1.png); background-repeat: no-repeat; left: 28px;transition: .3s ease-in-out;}
.eye:after{background-image: url(assets/front/images/eyebros-2.png); background-repeat: no-repeat; right: -8px;transition: .3s ease-in-out;}
.eye:before, .eye:after { animation-duration: 3s;content: ''; display: block; height:7px; width: 24px;position: absolute;top: -23px;}
.eye.eye-ftr:after, .eye.eye-ftr:before{display: none;}


            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
                background-image:url('images/about-middle-banner-resp.jpg');
                background-size:cover;
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
            p
            {
                font-size: 30px;
                font-weight: bold;
            }
        </style>
        <title>{{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME")}}</title>
    </head>
    <body >
        <div class="container">
            <div class="content">
               
                <!-- <a href="{{url('/')}}"> <img src="{{url('/')}}/assets/front/images/logo.png" alt="" /> -->
               <div class="logo-new-blick">
         <a href="{{url('/')}}"> <img src="{{url('/')}}/assets/front/images/logo.png" alt="" />
         <div class="face">
            <div class="main-eye">
            <div class="eye"></div>
            <div class="eye eye-ftr"></div>
            </div>
         </div>
        </a>
        </div>
                <div class="title">Site Offline</div>
                <p>
                    We will be back shortly
                </p>
                <p>
                   This site is down for maintenance. We will back soon
                </p>
            </div>
        </div>
    </body>
</html>
