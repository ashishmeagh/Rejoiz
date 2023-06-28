<!DOCTYPE html>
<html lang="en">
@php
    $b2c_privacy_settings = get_b2c_privacy_settings_detail();
    $menu_settings = array();
    $menu_settings = get_menu_detail();
    foreach($menu_settings as $menus){
        array_push($menu_settings,trim($menus['menu_slug']));
    }


    // Get count of vendors is active
    $chk_is_single_vendor = check_is_single_vendor();

    $vendor_listing_href = url('/search_vendor');
    $vendor_menu_name = "Vendors";

    if(isset($chk_is_single_vendor) && count($chk_is_single_vendor) == 1){
       
        $vendor_menu_name = "Vendor";
        $vendor_listing_href = url('/')."/vendor-details?vendor_id=".base64_encode($chk_is_single_vendor[0]['id'])."";
    } 

    //dd($login_user);
@endphp
<head>
    <!-- Page Title -->
    @if(isset($meta_details['meta_title']) && $meta_details['meta_title']!="")
        <meta title="title" content="{{$meta_details['meta_title'] or $site_setting_arr['site_name']}}" />
        <meta property="og:title" content='{{$meta_details['meta_title'].'-'.$site_setting_arr['site_name']}}'>
        <meta property="twitter:title" content='{{$meta_details['meta_title'].'-'.$site_setting_arr['site_name']}}'>
    @else
        <meta title="title" content="{{$site_setting_arr['site_name']}}" />
        <meta property="og:title" content='{{$site_setting_arr['site_name']}}'>
        <meta property="twitter:title" content='{{$site_setting_arr['site_name']}}'>
    @endif

    <!-- Page Description -->
    <meta name="description" content="{{$site_setting_arr['meta_desc'] or ''}}" />
    <meta property="og:description" content="{{$site_setting_arr['meta_desc'] or ''}}">


    <!-- Page Keywords need to change...... -->
    <meta name="keywords" content="{{$site_setting_arr['meta_keyword'] or ''}}" />

    <meta property="og:type" content="website">

    <meta charset="utf-8" />
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1" /> -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- Page URL -->
    <meta property="og:url" content="{{\Request::fullUrl()}}">
    <meta property="twitter:url" content="{{\Request::fullUrl()}}">


    <!-- Page Large Image -->
    @if(isset($meta_details['meta_image']) && $meta_details['meta_image']!="")
        <meta class="meta_image" property="twitter:card" content="{{$meta_details['meta_image'] or $site_logo}}">
        <meta class="meta_image" property="og:image" content="{{$meta_details['meta_image'] or $site_logo}}">
        <meta class="meta_image" property="twitter:image"  content="{{$meta_details['meta_image'] or $site_logo}}">
    @else
        <meta property="twitter:card" content="logo_large_image">
        <meta property="og:image" content="{{$site_logo or ''}}">
        <meta property="twitter:image"  content="{{$site_logo or ''}}">
    @endif

    <title>{{isset($page_title)?$page_title:""}} : {{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME")}}</title>


    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600&display=swap" rel="stylesheet">


 <link href="https://fonts.googleapis.com/css?family=Poppins:100,300,400,500,600,700,800&display=swap" rel="stylesheet">

   
    <!-- ======================================================================== -->
    @if(isset($site_setting_arr['favicon']))
        <link rel="icon" type="image/ico" sizes="16x16" href="{{url('/')}}/storage/app/{{$site_setting_arr['favicon'] or ''}}">
    @endif
   <!--  -->
    <!-- Bootstrap CSS -->
    <link href="{{url('/')}}/assets/front/css/bootstrap.css" rel="stylesheet" type="text/css" />
    <!--font-awesome-css-start-here-->
    <link href="{{url('/')}}/assets/front/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
    <!--Custom Css-->


    <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">


    <link href="{{url('/')}}/assets/front/css/just-got-to-have-it.css" rel="stylesheet" type="text/css" />
     <!-- Datepicker css-->
    <link href="{{url('/')}}/assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
    <!--Main JS-->

    
    @php 
      $get_url =  URL::current();
      if($get_url != 'https://rejoiz.ug'){
     @endphp 
     
     @php } @endphp 

     <script type="text/javascript" src="{{url('/')}}/assets/front/js/jquery-1.11.3.min.js"></script>
     <script type="text/javascript" src="{{url('/')}}/assets/js/jquery-ui.js" async></script>
    <!--  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script> -->

    <link href="{{url('/')}}/assets/js/Parsley/dist/parsley.css" rel="stylesheet">

    <link href="{{url('/')}}/assets/sweetalert/sweetalert.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/front/css/slick.css">
    <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/front/css/slick-theme.css">

    <!-- <script type="text/javascript" src="{{url('/')}}/assets/js/ajax_loader.js"></script> -->
    <link href="{{url('/')}}/assets/front/css/bootstrap-modal.css" rel="stylesheet" type="text/css" />

 <!-- hidden field for getting sequence of product from product details popup which is comes from catalog -->
    <input type="hidden" name="img_sku_sequence" id="img_sku_sequence" value="">


    @if(Sentinel::check()==true)
    <script defer async type="text/javascript" src="{{url('/')}}/assets/js/module_js/notification.js"></script>
    @endif


    <link rel="stylesheet" href="{{url('/')}}/assets/css/jquery-ui.css">

     <!-- <script src="https://cdn.lr-ingest.io/LogRocket.min.js" crossorigin="anonymous"></script>
<script>window.LogRocket && window.LogRocket.init('wukznv/kadoe');</script> -->

    <script type="text/javascript">
        var SITE_URL = "{{url('/')}}";

        var ProjectName = " {{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:env("APP_NAME")}}";

    </script>
    <script defer async type="text/javascript" src="{{url('/')}}/assets/js/pinterest_grid.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/JavaScript-autoComplete/1.0.4/auto-complete.js"></script>



<style type="text/css">


.min-menu li .su-menu li.active a{
    color: #666;
    text-decoration: underline;
}


.ui-menu .ui-menu-item a
{    text-transform: capitalize;
   margin-top: 10px; display: block;
   font-size:15px;
}
.min-menu li.mega-menu .su-menu li a.newaeeivals-link {
    text-align: center;
    display: inline-block; margin-left: 50px;
    color: #ee375d;
    text-decoration: underline;
}
.min-menu li.mega-menu .su-menu li a.newaeeivals-link:hover{padding-left: 0px; color: #333;}

.last_li
{
order:0;
}

    .large-num-menu .group{
        padding: 20px 0;
    }
    .large-num-menu .flex{
        display:flex; white-space: nowrap; margin:0 10px;
        align-items: center;
        color: #505050;
        font-weight: 600;
        justify-content: space-between;
        justify-content: center;
    }
    .large-num-menu .flex svg{
        margin-left: 3px;
    }
    .content-wrapper.large-num-menu {
        margin: 0 30px;
    }
    .relative{
        position: relative;
    }
    .product-img-icon {
        overflow: hidden;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        margin: 0 auto 4px;
    }
    .product-img-icon img{
        object-fit: cover;
        width: 100%;
        height: 100%;
    }

    .mainbody{
        overflow-x:hidden;
    }

    .relative .slick-list{
        /* position: static !important;
        overflow: visible !important; */
            position: relative;
        overflow: visible !important;
        z-index: 999;
    }
    .relative .slick-track{
        position: static !important;
    }
    .relative .slick-initialized .slick-slide{
        position: relative !important;
        height: 120px;
    }
    .large-num-menu .group {
        position: static;
        height: 140px;
    }


    .relative .slider {
        width: 100%;
        margin: 0px auto;
    }

    .relative .slick-slide {
      margin: 0px 20px;
    }

    .relative .slick-slide img {
      width: 100%;
    }

    .relative .slick-prev:before,
    .relative .slick-next:before {
      color: black;
    }

    .viewallbutton {
        display: block;
        width: 120px;
        text-align: center;
        color: #333;
        font-weight: 600;
    }
    .relative .slick-slide {
      transition: all ease-in-out .3s;
      /* opacity: .2; */
    }
    
    .relative .slick-active {
      /* opacity: .5; */
    }

    .relative .slick-current {
      opacity: 1;
    }
    .relative .slick-prev, .relative .slick-next{
        width: 60px;
        height: 100%;
        background-color: #f3f4f6 !important;
        z-index: 9999;
    }
    .relative .slick-next {
        right: -45px;
    }
    .relative .slick-prev {
        left: -45px;
    }
    .group:hover .dropcontent{
        display: block;
    }
    .group {
        /* position: relative; */
    }
    .dropcontent ul li:hover .hideme1{
        display: block;
    } 
    ul.hideme1 li:hover .hideme2{
        display: block;
    } 
    .hideme1, .hideme2{
        display: none;
    }
    .category-name-n {
        white-space: normal;
        text-align: center;
    }
    .dropcontent {
        display: none;
        /* width: 450px; */
        background: white;
        font-size: 14px;
        box-shadow: 0 0 15px #ccc;
        position: absolute;
        top: 120px;
        z-index: 999;
    }
    .size-16 {
        height: 16px;
        width: 16px;
    }
    .viewallbutton:hover .icon-viewall{
        background-color: #f5417e;    transition: all 0.5s ease 0s;
    }
    .viewallbutton:hover .icon-viewall img {
        filter: brightness(0) invert(1);
    }
    .icon-viewall {
        width: 50px;
        height: 50px;
        background-color: #f3f3f3;
        margin: 0 auto;
        border-radius: 50%;
        display: flex;
        padding: 10px; margin-bottom: 5px; 
    }
    .content-wrapper{
        align-items:center;
        display:flex;
        justify-content: space-between;
    }
    .dropcontent ul {
        padding: 0;
        width: 200px;
        /* border-right: 1px solid #eaeaea; */
        margin: 0;
        /* border: 1px solid #dadada; */
        /* overflow: auto;
        max-height:400px; */
        height: auto;
        box-shadow: -4px 1px 4px 0px rgb(51 51 51 / 14%);
    }


/* 
.dropcontent ul::-webkit-scrollbar {
	background-color: #fff;
	width: 16px
}

.dropcontent ul::-webkit-scrollbar-track {
	background-color: #fff
}

.dropcontent ul::-webkit-scrollbar-track:hover {
	background-color: #f4f4f4
}

.dropcontent ul::-webkit-scrollbar-thumb {
	background-color: #babac0;
	border-radius: 16px;
	border: 7px solid #fff
}

.dropcontent ul::-webkit-scrollbar-thumb:hover {
	background-color: #a0a0a5;
	border: 6px solid #f4f4f4
}

.dropcontent ul::-webkit-scrollbar-button {
	display: none
} */


    
    .dropcontent ul li{
        display: block;
        padding: 0 5px;
        position: relative;
    }
    
    .dropcontent ul li a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 5px;
        color: black;
    }
    .dropcontent ul li a:hover {
        text-decoration: none;
        background: #fff2f2;
        color: #cb0c4c;
        border-radius: 0px;
    }
    .dropcontent ul li a:hover svg{
        display: block;
    }
    .dropcontent ul li a svg{
        width: 16px;
        height: 16px;
        display: none;
    }
    .dropcontent ul ul {
        position: absolute;
        left: 200px;
        top: 0;
        /* border: 1px solid #dadada; */
        background: white;
    }
    /* .dropcontent ul ul ul{
        left: 266.66px;
        top: 0;
    } */
    
    .menuitems{
        position: relative;
    }
    .more-in{
        font-size: 12px;
        text-transform: uppercase;
        opacity: 0.5;
        display: block;
        width: 100%;
        color: #333;
        padding: 8px;
        border-bottom: 1px solid #ccc;
    }
    .more-in .morein{
        color:#e00000;
    }
    @media all and (max-width:767px) {
        .content-wrapper{
            overflow: auto;
        }
        .category-name-n {
            white-space: nowrap;
        }
    }
    @media all and (max-width:767px) {
        .relative .slick-initialized .slick-slide{
            width: 120px !important; float:left;
        }
        .relative .slick-track{
            display: flex !important;
        }
        .relative .slick-track{
            white-space: nowrap;
        }
    }

</style>

<!-- Temp CSS Start -->

</head>

<body class="mainbody">

    <div id="main" onclick="closeNav()"></div>
    <!--Header section start here-->
    <header>

        <div id="header-home">

            <!--<div class="main-banner-block">-->
            <div class="header header-home">
                <div class="logo-block wow fadeInDown" data-wow-delay="0.2s">
                    <a href="{{url('/')}}">



                        @php
                            $site_img = false;

                            $site_base_img = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;

                            $site_image_base_path = base_path('storage/app/'.$site_base_img);

                            $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;

                            $site_default_image = url('https://via.placeholder.com/160x53.png?text='.$site_name);
                            $site_img = image_resize($site_image_base_path,211,75,$site_default_image);
                            
                        @endphp

                        <img src="{{ $site_img }}" alt="{{ $site_name }}" />
                    </a>
                </div>

                 <form name="frm-search" id="frm-search" action="{{url('/')}}/search" style="display: block;" onsubmit="return false;" >
                        <div class="search-header-mn search-bxsearch">
                            <div class="user-box">

                            @php
                                $placeholder = '';
                                if(\Request::has('search_type') && \Request::get('search_type')!=null)
                                {
                                    $placeholder = \Request::get('search_type');


                                    if($placeholder=="category")
                                    {
                                        $placeholder = "Search for ".$placeholder.'...';

                                    }
                                    else if ($placeholder=="subcategory")
                                    {
                                       $placeholder = "Search for category...";
                                    }

                                    else if($placeholder=="maker")
                                    {
                                       $placeholder = "Search for vendors...";
                                    }
                                    else
                                    {
                                        $placeholder = "Search for ".$placeholder.'s...';
                                    }
                                }
                                else{
                                    $placeholder = "Search for category...";
                                }
                                @endphp

                                {{-- <input data-parsley-required="true" type="text" class="form-control" id="search_term"


                                placeholder="{{$placeholder or ''}}"
                                data-parsley-errors-container="#error_search_msg" name="search_term" value="{{isset($search_value['search_term'])?$search_value['search_term']:""}}">
                                <button type="submit" id="btn-search" class="search-btn-head">Search</button> --}}
                                @php
/*                                $route_name = Request::route()->getName();
                                if(isset($route_name) && $route_name=="search")
                                {
                                    $search_val =  Request::segment(2);
                                }*/
                                @endphp
                                 <input title="Search for items or brands" type="text" class="form-control" id="elastic_search_term" placeholder="Search for items or brands" data-parsley-required="true" data-parsley-required-message="please enter search term" data-parsley-errors-container="#search_error" autocomplete="off" maxlength="80" name ="elastic_search_term" value="{{ Request::input('search_term') }}">



                                <button type="button" id="elastic_btn_search" class="search-btn-head"><i class="fa fa-search" aria-hidden="true"></i></button>

                                <div class="clearfix"></div>

                                <button type="button" id="elastic_btn_close" class="closebtn" display=none><i class="fa fa-times" aria-hidden="true"></i></button>
                                <div class="clearfix"></div>


                                <div class="clearfix"></div>

                                <div id ="elresult"></div>

                              {{--   <label for="search">Search: </label>
<input id="search"> --}}
                               {{--  <div class="clearfix"></div> --}}
                                <br>
                                {{--  <input  type="text" class="form-control" id="elastic_search_term">
                                <button type="button" id="elastic_btn-search" class="search-btn-head">Search</button>
                                <div class="clearfix"></div>
                                <div id ="elresult"></div> --}}

                                <div class="myresult" style=" background-color:white" >
                                    <div class="maker_list"></div>
                                    <div class="product_list"></div>
                                </div>

                            </div>
                            <div id="search_error"></div>
                        </div>
                 </form>
                @php
                    $login_user = Sentinel::check();

                @endphp
                <span class="menu-icon" onclick="openNav()"><img src="{{url('/')}}/assets/front/images/menu.svg" alt="" /></span>
                <div class="list-of-notification-user">
                    <div class="links-right-login">
                        @if($login_user==true &&  ($login_user->inRole('retailer') || $login_user->inRole('maker') || $login_user->inRole('representative')|| $login_user->inRole('sales_manager') || $login_user->inRole('customer') || $login_user->inRole('influencer')))


                          @if(isset($login_user['first_name']) && $login_user['last_name'])

                             <a href="javascript:void(0)" class="icon_link" title="{{ucfirst($login_user['first_name'])}} {{ucfirst($login_user['last_name'])}}">{{ucfirst($login_user['first_name'])}} {{ucfirst($login_user['last_name'])}}</a>

                          @endif

                         @else

                        {{-- <a  @if(Request::segment(1) == "signup_retailer") class="active" @endif href="{{url('/')}}/signup_retailer">Register as Retailer</a> --}}


                            <a @if(Request::segment(1) == "login") class="active"                                     @endif href="{{url('/')}}/login" @if(!in_array('sign_in',$menu_settings)) style="display: none" @endif>Sign In</a>
                        @endif
                    </div>
                    <div class="links-right-just">
                  @if($login_user==true &&  ($login_user->inRole('retailer') || $login_user->inRole('maker') || $login_user->inRole('representative')|| $login_user->inRole('sales_manager') || $login_user->inRole('customer') || $login_user->inRole('influencer')))



                        <div class="list-a-top dropdown-submenu">
                            <a href="#" class="test" tabindex="-1"> <img src="{{url('/')}}/assets/front/images/user-icn.svg" alt="" /> </a>
                            <ul class="account-setti-sub-menu dropdown-menu" id="user_info_dropdown">
                                <li class="retailername-hea">
                                    <div class="avtr-headr">
                                    <img src="{{ getProfileImage($login_user->profile_image)}}">
                                    </div>
                                    <div class="avtr-headr-right">
                                         {{-- {{$arr_active_user['first_name'] or ''}} {{$arr_active_user['last_name'] or ''}} --}}

                                    {{$login_user['first_name'] or ''}} {{$login_user['last_name'] or ''}}

                                     </div>
                                     <div class="clearfix"></div>
                                </li>

                                <li>
                                    <?php
$panel_dashboard_url = "";
if ($login_user->inRole('retailer')) {
	$panel_dashboard_url = url('/' . config('app.project.retailer_panel_slug') . '/dashboard');

	$my_favorite = url('/' . config('app.project.retailer_panel_slug') . '/my_favorite');
} elseif ($login_user->inRole('maker')) {
	$panel_dashboard_url = url('/' . config('app.project.maker_panel_slug') . '/dashboard');
} elseif ($login_user->inRole('representative')) {
	$panel_dashboard_url = url('/' . config('app.project.representative_panel_slug') . '/dashboard');
} elseif ($login_user->inRole('sales_manager')) {
	$panel_dashboard_url = url('/') . '/sales_manager/dashboard';
} elseif ($login_user->inRole('influencer')) {
	$panel_dashboard_url = url('/') . '/influencer/dashboard';
} elseif ($login_user->inRole('customer')) {
	$panel_dashboard_url = url('/') . '/customer/dashboard';
	$my_favorite = url('/' . config('app.project.customer_panel_slug') . '/my_favorite');
}

?>
                                    <a href="{{$panel_dashboard_url or ''}}"><i class="fa fa-angle-right"></i> My Dashboard </a>
                                </li>

                               @if(isset($login_user) && ($login_user->inRole('retailer') || $login_user->inRole('customer')))
                                <li>

                                     <a href="{{$my_favorite or ''}}"><i class="fa fa-angle-right"></i> My Favorites</a>
                                </li>
                               @endif

                                <li><a href="{{url('/logout')}}"><i class="fa fa-angle-right"></i> Logout </a></li>
                            </ul>
                        </div>
                    @endif

                        {{-- @if($login_user==true) --}}
                            <div class="list-a-top">
                                @if($login_user==true && ($login_user->inRole('customer')))
                                    <a href="{{url('/customer_my_bag')}}"> <img src="{{url('/')}}/assets/front/images/shopping-cart.svg" alt="bag" />
                                        <span id="bag_count">
                                            {{ get_bag_count() }}
                                        </span>
                                    </a>
                                @else
                                    <a href="{{url('/my_bag')}}"> <img src="{{url('/')}}/assets/front/images/shopping-cart.svg" alt="bag" />
                                        <span id="bag_count">
                                            {{ get_bag_count() }}
                                        </span>
                                    </a>
                                @endif
                            </div>
                        {{-- @else --}}
                           {{--  <div class="list-a-top">
                                <a href="javascript:void(0)"> <img src="{{url('/')}}/assets/front/images/shopping-cart.svg" alt="bag" /> </a>
                                 <span id = "bag_count">
                                   0
                                </span>
                            </div>
                        @endif --}}


                        <!---Responsive Search-->
                        <div class="list-a-top search-bx-none" style="display: none;">
                            <i class="fa fa-search oniconclick "  aria-hidden="true"></i>

                                <div class="search-header-mn" style="display: none;">
                                    <div class="user-box">

                                        <input data-parsley-required="true" type="text" class="form-control resp_search" id="search_term" placeholder ="Search for products..."data-parsley-errors-container="#error_search_msg" name="search_term">
                                        <button type="button" id="btn-search" class="search-btn-head"><i class="fa fa-search" aria-hidden="true"></i></button>
                                        <div class="clearfix"></div>

                                    </div>
                                </div>
                        </div>
                    </div>
                    <div class="clr"></div>



                </div>

                <!--Menu Start-->
                <div id="mySidenav" class="sidenav">
                    <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
                    <div class="banner-img-block">
                          <img src="{{ url('/')}}/storage/app/{{isset($site_setting_arr['site_logo'])?$site_setting_arr['site_logo']:""}}" alt="Logo" />
                        <div class="img-responsive-logo"></div>
                    </div>


                    <ul class="min-menu">

                        <!-- Vendors menu li -->
                        <li class="ctsk" @if(!in_array('vendors',$menu_settings)) style="display: none" @endif><a href="{{ $vendor_listing_href }}" @if(Request::segment(1) =='search_vendor' || Request::segment(1) == 'vendor-details') class="active" @endif>{{  $vendor_menu_name }}</a></li>


                        <li class="ctsk" @if(!in_array('promotions',$menu_settings)) style="display: none" @endif><a href="{{url('/promotions')}}" @if(Request::segment(1) =='promotions') class="active" @endif  >Promotions</a></li> 

               {{--          <li class="ctsk sub-menu"><a href="#" @if(Request::segment(1) =='promotions') class="active" @endif>Promotions</a>
                          <ul class="su-menu res-su-menu">
                            @if(isset($arr_area) && count($arr_area)>0)
                                 <li @if(Request::segment(2) == 'all-promotions') class="active" @endif><a href="{{url('/promotions')}}/all-promotions">All Promotions</a></li>

                                @foreach($arr_area as $key=>$area)
                                   <li @if($area['id'] == base64_decode(Request::segment(2))) class="active" @endif><a href="{{url('/promotions')}}/{{isset($area['id'])?base64_encode($area['id']):0}}">{{$area['area_name'] or ''}}</a></li>
                                @endforeach

                            @endif
                          </ul>

                        </li> --}}


                    <li><a @if(Request::segment(1) =='about') class="active" @endif href="{{url('/')}}/about" >About us</a></li> 
                    @if($login_user == false || $login_user==true &&  ($login_user->inRole('retailer') || $login_user->inRole('maker') || $login_user->inRole('representative')|| $login_user->inRole('sales_manager')) && (isset($arr_area) && count($arr_area)>0))


                    <li class="ctsk sub-menu " @if(!in_array('special_offers',$menu_settings)) style="display: none" @endif><a class="ponitnone" href="javascript:void(0);" @if(Request::segment(1) =='promotions') class="active" @endif>Special Offers

                        <div class="arrwhedr">
                        <img src="{{url('/')}}/assets/front/images/select-arrow.png" alt="bag" />
                    </div></a>

                          <ul class="su-menu res-su-menu style-4" >
                             @if(isset($arr_area) && count($arr_area)>0)



                                <li @if(Request::segment(1) == 'promotions' && Request::segment(2) =='') class="active" @endif><a href="{{url('/promotions')}}">All Offers</a></li>


                                @foreach($area_category_arr as $key=>$area)

                                    @if(isset($area['category_arr']) && count($area['category_arr'])>0)

                                        @foreach($area['category_arr'] as $key=>$category)




                                            <li  @if(Request::segment(3) == base64_encode($category['id']) && Request::segment(2) == base64_encode($area['area_id']) && Request::segment(1)=='promotions') class="active" @endif><a href="{{url('/promotions')}}/{{isset($area['area_id'])?base64_encode($area['area_id']):0}}/{{isset($category['id'])?base64_encode($category['id']):0}}">{{$area['area_name'].' '.$category['cat_division_name']}}</a></li>

                                        @endforeach

                                    @else
                                   <li @if(Request::segment(1) == 'promotions' && Request::segment(2)==base64_encode($area['area_id'])) class="active" @endif><a href="{{url('/promotions')}}/{{isset($area['area_id'])?base64_encode($area['area_id']):0}}">{{$area['area_name']}}</a></li>
                                   @endif


                                @endforeach
                            @endif
                          </ul>

                        </li>
                        @endif

                    @if($login_user == false || $login_user==true &&  ($login_user->inRole('retailer') || $login_user->inRole('maker') || $login_user->inRole('representative')|| $login_user->inRole('sales_manager')) && (isset($arr_area) && count($arr_area)>0))

                        <li class="ctsk sub-menu" @if(!in_array('rep_center',$menu_settings)) style="display: none" @endif><a class="ponitnone" href="javascript:void(0);" @if(Request::segment(1) =='find_rep') class="active" @endif >Rep Center

                         <div class="arrwhedr">
                            <img src="{{url('/')}}/assets/front/images/select-arrow.png" alt="bag" />
                        </div> </a>
                          <ul class="su-menu res-su-menu style-4">
                            @if(isset($arr_area) && count($arr_area)>0)
                                 <li @if(Request::segment(1) == 'find_rep' && Request::segment(2)=="") class="active" @endif><a href="{{url('/find_rep')}}">All Divisions</a></li>

                                @foreach($area_category_arr as $key=>$area)

                                    @if(isset($area['category_arr']) && count($area['category_arr'])>0)

                                      @foreach($area['category_arr'] as $key=>$category)

                                        <li @if(Request::segment(3) == base64_encode($category['id']) && Request::segment(2) == base64_encode($area['area_id']) && Request::segment(1)== 'find_rep') class="active" @endif ><a href="{{url('/find_rep')}}/{{isset($area['area_id'])?base64_encode($area['area_id']):0}}/{{isset($category['id'])?base64_encode($category['id']):0}}">{{$area['area_name'].' '.$category['cat_division_name']}}</a></li>

                                       @endforeach

                                    @else

                                    <li @if(Request::segment(1) == 'find_rep' && Request::segment(2)==base64_encode($area['area_id'])) class="active" @endif><a href="{{url('/find_rep')}}/{{isset($area['area_id'])?base64_encode($area['area_id']):0}}">{{$area['area_name']}}</a></li>

                                    @endif

                                @endforeach

                            @endif
                          </ul>

                        </li>
                        @endif


                        <!-- Category -->
                        <!-- <li class="mega-menu sub-menu" @if(!in_array('categories',$menu_settings)) style="display: none" @endif>
                            <a class="ponitnone catglogo" href="javascript:void(0);" >Category 
                                <div class="arrwhedr">
                                    <img src="{{url('/')}}/assets/front/images/select-arrow.png" alt="bag" />
                                </div>
                            </a>
                            <div class="cat_sub_menu style-4">
                                <ul class="su-menu res-su-menu">
                                    {{--   <div class="all-sub feature-ul">
                                        <ul>
                                            <li class="title-of-feature"><b>FEATURED</b></li>
                                            <li><a href="#">New Arrivals</a></li>
                                            <li><a href="#">Best Sellers</a></li>
                                            <li><a href="#">International Best Sellers</a></li>
                                            <li><a href="{{url('/')}}/search?search_type=maker&search_term=">Maker A-Z</a></li>

                                        </ul>
                                    </div> --}}
                                    <li>
                                        <div class="featured-menus" >
                                            <div class="featured-title">Featured</div>
                                            <ul>
                                                <li><a href="{{url('/').'/search?category=new_arrivals'}}">New Arrivals</a></li>
                                                <li><a href="{{url('/').'/search?category=best_seller'}}">Best Sellers</a></li>
                                                <li><a href="#">International Best Sellers</a></li>
                                                <li><a href="{{url('/promotions')}}">Vendor Specials</a></li>
                                                {{--   <li><a href="{{url('/').'/search_vendor'}}">American Best Sellers</a></li> --}}
                                                <li><a href="{{url('/').'/search?country_id='.base64_encode(2)}}">American Best Sellers</a></li>
                                            </ul>
                                        </div>   
                                        <div class="scrolldiv">
                                            <div id="blog-landing" class="ul-main-left-hedr">
                                                @if(isset($arr_category) && count($arr_category)>0)
                                                    @foreach($arr_category as $category)
                                                        <div class="all-sub white-panel">
                                                            <div class="su-menu-title">
                                                                <a href="{{url('/')}}/search?category_id={{isset($category['id'])?base64_encode($category['id']):""}}">{{ ucfirst($category['category_name'])}}</a>
                                                            </div>
                                                            <ul>
                                                                @php
                                                                    $total_subcategory =  isset($category['subcategory_details'])?count($category['subcategory_details']):0;
                                                                @endphp

                                                                @if(isset($category['subcategory_details']) && count($category['subcategory_details'])>0)
                                                                    @foreach($category['subcategory_details'] as $key => $subcategory)
                                                                        @if(isset($subcategory['subcategory_name']) && $subcategory['subcategory_name']=="General" && ($key == intVal($total_subcategory)+1))
                                                                            <li class="last_li">
                                                                        @else
                                                                            <li>
                                                                        @endif
                                                                        <a href="{{url('/')}}/search?category_id={{isset($category['id'])?base64_encode($category['id']):""}}&subcategory={{isset($subcategory['id'])?base64_encode($subcategory['id']):""}}">{{isset($subcategory['subcategory_name'])?ucfirst($subcategory['subcategory_name']):''}}</a></li>
                                                                    @endforeach
                                                                @endif
                                                            </ul>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>

                                        <div class="ul-main-right-hedr">
                                            <img src="{{url('/')}}/assets/front/images/left-large-img.jpg" alt="" />
                                            {{-- <div class="subtitls">Shop Now</div>
                                            <div class="titlt-megamenus">New Arrival</div> --}}
                                        </div>

                                        <div class="cleardix"></div>
                                        <div class="cleardix"></div>
                                        <div class="cleardix"></div>
                                        <div class="cleardix"></div>
                                        <div class="ul-main-right-hedr">
                                            <a href="{{url('/').'/search?category=new_arrivals'}}" class="newaeeivals-link" style="padding-top: 25px">New Arrivals</a>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </li> -->
                        <!-- END Category -->

                        <!-- <li><a @if(Request::segment(1) =='about') class="active" @endif href="{{url('/')}}/about" >About</a></li> -->



                        <li @if(!in_array('shop_now',$menu_settings)) style="display: none" @endif><a @if(Request::segment(1) =='search') class="active" @endif href="{{url('/').'/search?category=shop_now'}}">Shop Now</a></li>


                        {{-- <li class="responsive-show"><a @if(Request::segment(1) == "signup_retailer") @endif href="{{url('/')}}/signup">Register as Retailer</a></li> --}}
                        @if($login_user == false || $login_user->inRole('admin') == true)
                        <li class="ctsk sub-menu register-menu" @if(!in_array('sign_up',$menu_settings)) style="display: none" @endif><a class="ponitnone" href="#">Sign Up

                        <div class="arrwhedr">
                        <img src="{{url('/')}}/assets/front/images/select-arrow.png" alt="bag" />
                    </div>
                        </a>
                          <ul class="su-menu res-su-menu">
                            <li><a @if(Request::segment(1) == "signup") @endif href="{{url('/')}}/signup">Apply as Vendor</a></li>

                            <li><a @if(Request::segment(1) == "signup_retailer") @endif href="{{url('/')}}/signup_retailer">Register as Customer</a></li>

                            @if(isset($b2c_privacy_settings['is_b2c_module_on']) &&
                                      $b2c_privacy_settings['is_b2c_module_on'] == '1')
                                <li><a @if(Request::segment(1) == "signup_customer") @endif href="{{url('/')}}/signup_customer">Sign up as Customer</a></li>
                            @endif
                            @if(isset($b2c_privacy_settings['is_influencer_module_on']) &&
                                      $b2c_privacy_settings['is_influencer_module_on'] == '1')
                                <li><a @if(Request::segment(1) == "signup_influencer") @endif href="{{url('/')}}/signup_influencer">Apply as Influencer</a></li>
                            @endif
                         </ul>
                        </li>
                        @endif
                        {{-- @if(Sentinel::check()==true)

                        @else  --}}
                        {{--  <a @if(Request::segment(1) == "login") class="active"                                     @endif href="{{url('/')}}/login">Login</a> --}}

                        @if($login_user == false || $login_user->inRole('admin') == true)

                        <li class="responsive-show" @if(!in_array('sign_in',$menu_settings)) style="display: none" @endif><a @if(Request::segment(1) == "login")  @endif href="{{url('/')}}/login">Sign In</a></li>

                        @endif

                      {{--   <li class="responsive-show"><a href="{{url('/')}}/signup_retailer">Sign Up</a></li> --}}
                        {{-- @endif --}}
                    </ul>

                    <div class="clearfix"></div>

                   </div>



                <div class="clr"></div>
            </div>
            <div class="clr"></div>
            <!--</div>-->

        </div>
        <div class="blank-div"></div>

        @php

            $is_logged_in = Sentinel::check();
            if($is_logged_in==false)
            {
                $is_logged_in = "false";
            }
            else
            {
                $is_logged_in = "true";
            }
        @endphp

    <input type="hidden" name="is_logged_in" id = "is_logged_in" value="{{$is_logged_in}}">
    </header>

    <!--Header section end here-->




<div class="container-fluid relative">
    <div class="content-wrapper large-num-menu">
        @php
            $cnt = 1;
            
           
        @endphp
        
        @if($arr_category !="" && $arr_category != null)
            @foreach($arr_category as $category)
                @if($cnt <= 5 && $category['priority']!=0)
                    <div class="group-inline">
                        <div class="group">
                            <div class="product-img-icon">
                                
                                @php
                                    $category_img = false;
                                    $category_base_img = isset($category['category_image']) ? $category['category_image'] : false;
                                    $category_image_base_path  = base_path('storage/app/'.$category_base_img);
                                    $category_default_image =  url('/assets/images/no-product-img-found.jpg');
                                    $category_img = image_resize($category_image_base_path,442,320,$category_default_image);
                                @endphp
                                <a href="{{url('/')}}/search?category_id={{isset($category['id'])?base64_encode($category['id']):''}}">
                                    <img src="{{$category_img or '' }}" alt="">
                                </a>
                            </div>
                            <a class="flex item-center semibold-title" href="{{url('/')}}/search?category_id={{isset($category['id'])?base64_encode($category['id']):''}}"> <span class="category-name-n">{{$category['category_name'] or '' }}</span>
                                @if(isset($category['subcategory_details']) && count($category['subcategory_details'])>0)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="size-16 ml-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                @endif
                            </a>
                            <div class="dropcontent">
                                <div class="menuitems">
                                    <ul class="">
                                        @if(isset($category['subcategory_details']) && count($category['subcategory_details'])>0)
                                            @foreach($category['subcategory_details'] as $key => $subcategory)
                                                <li class=""> 
                                                    <a href="{{url('/')}}/search?category_id={{isset($category['id'])?base64_encode($category['id']):''}}&subcategory={{isset($subcategory['id'])?base64_encode($subcategory['id']):''}}" class="cat-1-menu">{{$subcategory['subcategory_name'] or '' }}
                                                        @if(isset($subcategory['second_subcategory_details']) && count($subcategory['second_subcategory_details'])>0)
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-16 ml-3"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                            </svg>
                                                        @endif
                                                    </a>
                                                    
                                                    @if(isset($subcategory['second_subcategory_details']) && count($subcategory['second_subcategory_details'])>0)
                                                        <ul class="hideme1" @if($cnt ==5) style="right:202px; left:auto" @endif>
                                                            <span class="more-in"><span class="morein">More In</span> <b>{{$subcategory['subcategory_name'] or '' }}</b></span>
                                                            @foreach($subcategory['second_subcategory_details'] as $key => $sec_subcategory)
                                                                <li class="" >
                                                                    <a href="{{url('/')}}/search?category_id={{isset($category['id'])?base64_encode($category['id']):''}}&subcategory={{isset($subcategory['id'])?base64_encode($subcategory['id']):''}}&thirdsubcategory={{isset($sec_subcategory['id'])?base64_encode($sec_subcategory['id']):''}}" class="cat-2-menu">{{$sec_subcategory['third_sub_category_name'] or '' }}
                                                                        @if($sec_subcategory['third_subcategory_details']!= "" && count($sec_subcategory['third_subcategory_details'])>0)
                                                                            <svg xmlns="http://www.w3.org/2000/svg" class="size-16 ml-3"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                                            </svg>
                                                                        @endif
                                                                    </a>
                                                                    @if(isset($sec_subcategory['third_subcategory_details']) && count($sec_subcategory['third_subcategory_details'])>0)
                                                                        <ul class="hideme2" @if($cnt ==5) style="right:202px; left:auto" @endif>
                                                                            <span class="more-in"><span class="morein">More In</span> <b>{{$sec_subcategory['third_sub_category_name'] or '' }}</b></span>
                                                                            @foreach($sec_subcategory['third_subcategory_details'] as $key => $third_subcategory)
                                                                                <li class="">
                                                                                    <a href="{{url('/')}}/search?category_id={{isset($category['id'])?base64_encode($category['id']):''}}&subcategory={{isset($subcategory['id'])?base64_encode($subcategory['id']):''}}&thirdsubcategory={{isset($sec_subcategory['id'])?base64_encode($sec_subcategory['id']):''}}&fourthsubcategory={{isset($third_subcategory['id'])?base64_encode($third_subcategory['id']):''}}" class="cat-3-menu">{{$third_subcategory['fourth_sub_category_name'] or '' }}</a>
                                                                                </li>
                                                                            @endforeach
                                                                        </ul>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @php
                        $cnt++;
                    @endphp
                @endif
            @endforeach
        @endif  
        <div class="group-inline">
            <div class="group">
                 <a href="{{url('/')}}/search?category=shop_now" class="viewallbutton">
                     <div class="icon-viewall"><img src="{{url('/')}}/assets/front/images/arrow-right-view-all.svg" alt="" /></div>
                     View All</a>
            </div>        
        </div>
    </div>
</div>
   

<!-- <script src="{{url('/')}}/assets/front/js/slick.js" type="text/javascript" charset="utf-8"></script> -->
<!-- <script type="text/javascript">
   $('.center').slick({
        dots: false,
        infinite: false,
        speed: 300,
        slidesToShow: 6,
        slidesToScroll: 1,
    });
</script> -->

<script>
    var all_suggetions = [];
    var suggetions= [];
    var maker_suggetions = [];
    var category_suggetions = [];
    var company_suggetions = [];
    var product_unique_arr =[];
    var recent_searches =[];
    var is_logged_in = $("#is_logged_in").val();
    var mobile_search_term='';
    var window_width = 0;
    var search_term_id='';

$(document).ready(function() {

    window_width = $(window).width();

    if(window_width < 1200){
       /*Mobile View*/
       search_term_id = "search_term";
    }
    else{
        /*Desktop View*/
        search_term_id = "elastic_search_term";
    }

   
    $("#elastic_btn_close").css("display", "none");

    $(".oniconclick").click(function() {
       $(".search-header-mn").toggle();
     });

    $("#btn-search").click(function(){
        mobile_search_term =  $("#search_term").val();
    });


    $('#btn-search').click(function()
    {   var search_term  = $("#search_term").val();
        var search_type  = $("#search_type").val();
        var url          = "{{url('/')}}search";
    });

    $('.icon_link,.dropdown-submenu a.test').on("click", function(e) {
        $('.dropdown-submenu a.test').next('ul').toggle();
        e.stopPropagation();
        e.preventDefault();
    });


     //start of getting autocomplete data
    var search = $("#elastic_search_term .resp_search").val();
    var resp_search = $('.resp_search').val();

    if(resp_search!='' && resp_search!=undefined)
    {
        search = $(".resp_search").val();
    }
   
    var by_ajax = 1;
    var name = "initial-search";

    if(mobile_search_term=='' || mobile_search_term!=undefined || mobile_search_term!='')
    {
        search = mobile_search_term;
        resp_search = mobile_search_term;

    }

    if (search == undefined) {
        var search_url = SITE_URL + '/search?category=new_arrivals';
    } else {
        var search_url = SITE_URL + '/search' + search;
        
    }

    var origin_url = "{{url('/')}}";
        
    $.ajax({
        url: search_url,
        method: 'GET',
        data: {
            by_ajax: by_ajax
        },
        dataType: 'JSON',
        success: function(response) {
            if (response.status == "success") {

                $.each(response.data, function(key, val) {

                    if (val.product_name != null && val.category_name != '' && val.brand_name != null)
                    {

                        suggetions.push({
                            title: val.product_name,
                            value: val.product_name,
                            product_id: val.id,
                            maker_id: val.user_id,
                            category: "Suggested Products"
                        });


                        /* maker_suggetions.push({
                             title:val.maker_name,
                             value:val.maker_name,
                             maker_id:val.user_id,
                             category:"Suggested Vendors"
                         })*/

                        /*company_suggetions.push({
                            title: val.brand_name,
                            value: val.brand_name,
                            maker_id: val.user_id,
                            category: "Suggested Vendors"
                        })*/

                    }
                });

               $.each(response.category_data, function(key, val) {
                    if (val != false) {
                         category_suggetions.push({
                            title: val.category_name,
                            value: val.category_name,
                            category_id: val.category_id,
                            category: "Suggested Categories"
                        })
                    }
                });

                $.each(response.vendor_data, function(key, val) {
                    if (val != false) {
                         company_suggetions.push({
                            title: val.brand_name,
                            value: val.brand_name,
                            maker_id: val.user_id,
                            category: "Suggested Vendors"
                        })
                    }
                });

                $.each(response.recently_searched, function(key, val) {
                    if (val != false && val != "initial-search") {
                        recent_searches.push({
                            title: val.replace('/', ''),
                            value: val.replace('/', ''),
                            category: "Recently searched"
                        });
                    }
                });


                var product_unique_arr = $.unique(suggetions);
                var maker_unique_arr   = $.unique(maker_suggetions);

                var result = [];
                var map = new Map();
                for (var item of maker_unique_arr) {
                    if (!map.has(item.title)) {
                        map.set(item.title, true); // set any value to Map
                        result.push({
                            title: item.title,
                            value: item.value,
                            maker_id: item.maker_id,
                            category: "Suggested Makers"
                        });
                    }
                }

                var category_result = [];
                var category_map = new Map();
                for (var item of category_suggetions) {
                    if (!category_map.has(item.title)) {
                        category_map.set(item.title, true); // set any value to Map
                        category_result.push({
                            title: item.title,
                            value: item.value,
                            category_id: item.category_id,
                            category: "Suggested Categories"
                        });
                    }
                }

                var company_result = [];
                var company_map = new Map();
                for (var item of company_suggetions) {
                    if (!company_map.has(item.title)) {
                        company_map.set(item.title, true); // set any value to Map
                        company_result.push({
                            title: item.title,
                            value: item.value,
                            maker_id: item.maker_id,
                            //category: "Suggested Companies"
                            category: "Suggested Vendors"
                        });
                    }
                }

                var product_unique_arr = [product_unique_arr, result, category_result, company_result, recent_searches];

                var flatArray = [].concat.apply([], product_unique_arr);
                var product_unique_arr = Array.prototype.concat(...product_unique_arr);
                suggetions = product_unique_arr;
            }
            else {
                alert("something went wrong...");
            }

        }
    });
       //end of getting autocomplete data

        $('#elastic_search_term,.resp_search').bind("enterKey",function(e){

             initiateSearch();
        });

        $('#elastic_search_term,.resp_search').keyup(function(e){

            if(e.keyCode == 13)
            {
              $(this).trigger("enterKey");
            }
        });

        $("#elastic_search_term .resp_search").on("keyup",function(e){
            e.preventDefault();
            if(e.keyCode == 13) /* For Enter*/
            {
                initiateSearch();
            }

        })

        $(".search-btn-head").click(function(){

            initiateSearch();

        });

        function initiateSearch(){
            var search = $("#elastic_search_term").val();
            var mobile_search_term = $("#search_term").val();
            
            if(mobile_search_term!="undefined" && mobile_search_term!='' &&mobile_search_term!=null)
            {     
                search = mobile_search_term;
                resp_search = mobile_search_term;

            }

            if(search =="")
            {
                return;
            }
            else{
                var url = SITE_URL+'/search?search_term='+search;
                window.location.href= url;    
            }
            // var url = SITE_URL+'/search?search_term='+search;
            // window.location.href= url;
        }


        $.widget( "custom.catcomplete", $.ui.autocomplete, {

             _create: function() {
                this._super();
                this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
            },


             _renderMenu: function( ul, items ) {


                 var that = this,
                currentCategory = "";

                 $.each( items, function( index, item ) {
                    var li;
                    if ( item.category != currentCategory ) {
                        ul.addClass('heder-search-drop');
                        ul.append( "<li class='ui-autocomplete-category " + item.category + "'>" + item.category + "</li>" );
                        currentCategory = item.category;
                    }
                    li = that._renderItemData( ul,item );

                    if ( item.category ) {
                        li.attr( "aria-label", item.category + " : " + item.label );
                    }
                });
            },

             _renderItem: function( ul, item ) {
                console.log(item);
                return $( "<li>" )
                .addClass(item.category)
                .attr( "data-value", item.value)
                .append( $( "<a>" ).text( item.label ) )
                .appendTo( ul );
            }
        });


        $("#"+search_term_id).catcomplete({
            delay: 0,
            source: function(request, response) {

                if(request.term==" ")
                {
                    var results = suggetions;                }
                else
                {
                    var results = $.ui.autocomplete.filter(suggetions, request.term);
                }

                
                if(window_width < 1200)
                {   
                    response(results.slice(0, 2));
                }
                else
                {
                    response(results.slice(0, 5));
                }
                

            },

            select: function(event, ui) {

                var search_val = ui.item.value;
                if (search_val != undefined && search_val != null) {
                    var recent_search_val = search_val;
                }

                var maker_id = ui.item.maker_id;
                var product_id = ui.item.product_id;
                var category_id = ui.item.category_id;


                if (product_id != undefined && maker_id != undefined && product_id != '' && product_id != null) {
                    if(is_logged_in=="true")
                    {
                    location.href = SITE_URL + '/vendor-details/product_detail?product_id='+btoa(product_id)+'&vendor_id=' + btoa(maker_id);
                    }
                    else
                    {
                        //location.href = SITE_URL + '/vendor-details?vendor_id=' + btoa(maker_id);
                        location.href = SITE_URL + "/search?search_term=" + search_val;
                    }
                }

                else if(maker_id != undefined && maker_id != '' && maker_id != null)
                {
                  location.href = SITE_URL + '/vendor-details?vendor_id=' + btoa(maker_id);
                }
                else if (category_id != undefined && category_id != '' && category_id != null) {
                    location.href = SITE_URL + '/search?category_id=' + btoa(category_id);


                } else
                    location.href = SITE_URL + "/search?search_term=" + search_val;
                return false;
            },


        }).focus(function() {

            var data = $("#elastic_search_term").data('customCatcomplete');

            $("#elastic_search_term").catcomplete("search", " ");

            if (recent_searches.length != 0) {
                $("#ui-id-1").prepend(`<div id = "recently_searched"><li class="ui-autocomplete-category Suggested Products">Recently Searched</li><ul class ="recent"></ul></div>`);
            }

            var i;

            if(recent_searches.length!=0){

                if(recent_searches.length<5)
                {
                   var length =  recent_searches.length;
                }
                else
                {
                    var length = 5;
                }
                for (i = 0; i < length; i++) {
                    try
                    {
                    $(".recent").append("<li class = recent_items ui-menu-item><a href=" + SITE_URL + "/search?search_term=" + encodeURI(recent_searches[i].value) + "> " + recent_searches[i].value + "</a></li>");
                    }
                    catch(e)
                    {
                    //console.error(e.message);
                    console.log(e);
                    }
                }
            }
        });

      $(".last_li").css("color","red");

      $("#elastic_search_term").keyup(function(){
        if($("#elastic_search_term").val()!="")
        {
          var search_keyword = $("#elastic_search_term").val();
          get_search_suggetions(search_keyword);

          $("#elastic_btn_close").css("display", "block");
        }
        else{
            $("#elastic_btn_close").css("display", "none");
        }
      });

      $("#elastic_btn_close").click(function() {
        $("#elastic_search_term").val("");
        $("#elastic_btn_close").css("display", "none");
      });

      $("#elastic_btn_search").click(function() {
        //code by abbas for validation to search box
        if($('#frm-search').parsley().validate()==false) return;
         showProcessingOverlay();
      });


    });

function get_search_suggetions(search)
{

     all_suggetions = [];
     suggetions= [];
     product_unique_arr =[];

        var search_url = SITE_URL + '/search?search_term=/' + search;


        var origin_url = "{{url('/')}}";
        $.ajax({
            url: search_url,
            method: 'GET',
            data: {
                by_ajax: 1,
                name: search,
                is_suggetion:1
            },
            dataType: 'JSON',
            success: function(response) {
                if (response.status == "success") {



                    $.each(response.data, function(key, val) {

                        if (val.product_name != null && val.category_name != '' && val.brand_name != null) {



                            suggetions.push({
                                title: val.product_name,
                                value: val.product_name,
                                product_id: val.id,
                                maker_id: val.user_id,
                                category: "Suggested Products"
                            });




                            /* maker_suggetions.push({
                                 title:val.maker_name,
                                 value:val.maker_name,
                                 maker_id:val.user_id,
                                 category:"Suggested Vendors"
                             })*/

                            /*company_suggetions.push({
                                title: val.brand_name,
                                value: val.brand_name,
                                maker_id: val.user_id,
                                category: "Suggested Vendors"
                            })*/

                        }
                    });


                    $.each(response.category_data, function(key, val) {
                        if (val != false) {
                             category_suggetions.push({
                                title: val.category_name,
                                value: val.category_name,
                                category_id: val.category_id,
                                category: "Suggested Categories"
                            })
                        }
                    });

                     $.each(response.vendor_data, function(key, val) {
                        if (val != false) {
                             company_suggetions.push({
                                title: val.brand_name,
                                value: val.brand_name,
                                maker_id: val.user_id,
                                category: "Suggested Vendors"
                            })
                        }
                    });




                    var product_unique_arr = $.unique(suggetions);
                    var maker_unique_arr = $.unique(maker_suggetions);


                    var result = [];
                    var map = new Map();
                    for (var item of maker_unique_arr) {
                        if (!map.has(item.title)) {
                            map.set(item.title, true); // set any value to Map
                            result.push({
                                title: item.title,
                                value: item.value,
                                maker_id: item.maker_id,
                                category: "Suggested Makers"
                            });
                        }
                    }


                    var product_result = [];
                    var product_map = new Map();
                    for (var item of product_unique_arr) {
                        if (!product_map.has(item.title)) {
                            product_map.set(item.title, true); // set any value to Map
                            if(item.category=="Suggested Products")
                            {
                                product_result.push({
                                title: item.title,
                                value: item.value,
                                product_id: item.product_id,
                                category: "Suggested Products"
                                });
                            }
                        }
                       /* else
                        {
                            console.log("duplicate item",item.title)
                        }*/
                    }


                    var category_result = [];
                    var category_map = new Map();
                    for (var item of category_suggetions) {
                        if (!category_map.has(item.title)) {
                            category_map.set(item.title, true); // set any value to Map
                            category_result.push({
                                title: item.title,
                                value: item.value,
                                category_id: item.category_id,
                                category: "Suggested Categories"
                            });
                        }
                    }

                    var company_result = [];
                    var company_map = new Map();
                    for (var item of company_suggetions) {
                        if (!company_map.has(item.title)) {
                            company_map.set(item.title, true); // set any value to Map
                            company_result.push({
                                title: item.title,
                                value: item.value,
                                maker_id: item.maker_id,
                                //category: "Suggested Companies"
                                category: "Suggested Vendors"
                            });
                        }
                    }

                    var product_unique_arr = [product_result, result, category_result, company_result];

                    //console.log(recent_searches);
                    var flatArray = [].concat.apply([], product_unique_arr);
                    var product_unique_arr = Array.prototype.concat(...product_unique_arr);
                    suggetions = product_unique_arr;




                }
                else {
                    alert("something went wrong...");
                }

            }
        });

}
</script>

<!-- 
   <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script> -->


