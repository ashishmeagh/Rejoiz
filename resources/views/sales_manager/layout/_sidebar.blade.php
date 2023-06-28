@php 
$maker_path = config('app.project.maker_panel_slug'); 
$loggedInUserName = '';
$company_name = '';

$first_name = $loggedInUserDetails['first_name'] or '' ;
$last_name  = $loggedInUserDetails['last_name'] or '';
$loggedInUserName = ucfirst($first_name).' '.$last_name;

$company_name = isset($maker_data['company_name'])?$maker_data['company_name']:'N/A';
$notification_count = get_notification_count('sales_manager');
@endphp
<!-- Navigation -->

<style type="text/css">
  
  
 .industries-header {
       color: #333;
    position: absolute;
    left: 80px;
    top:0;
    right: 0;
    bottom: 0;
    font-weight: 600;
    font-size: 16px;
    /*width: 100%;*/
    text-align: center;
    padding: 0;
    margin: 0px auto; line-height: 40px;
 }
 .industries-header span{
   display: block;
   color: #666;
 }
.navbar-header{position: relative;}
@media all and (max-width:767px){
   .industries-header{width: 100px !important; font-size: 11px !important;}
}
@media all and (max-width:1024px){
  .industries-header{
    white-space: normal;
    overflow: visible;
        font-size: 13px;left: 0px;
    width: 300px;
        word-break: break-all;
    line-height: 14px;
  }
}
</style>


    @php
       $site_img = false;

       $site_base_img = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;

       $site_img = imagePath($site_base_img,'site_logo',0);


        $site_mob_img = isset($site_setting_arr['login_site_logo']) ? $site_setting_arr['login_site_logo'] : false;

       $site_mob_img = imagePath($site_mob_img,'site_logo',0);

       $site_base_img = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;
      $site_image_base_path = base_path('storage/app/'.$site_base_img);
      $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;
      $site_default_image = url('https://via.placeholder.com/160x53.png?text='.$site_name);
      $site_img = image_resize($site_image_base_path,153,48,$site_default_image);
   @endphp



<nav class="navbar navbar-default navbar-static-top m-b-0">
   <div class="navbar-header">
      <a class="navbar-toggle hidden-sm hidden-md hidden-lg " href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-collapse"><i class="ti-menu"></i></a>
      <div class="top-left-part">
         <div class="logo-new-blick desk-logo">
            <a target="_blank" href="{{url('/')}}">    
            <img src="{{$site_img or ''}}" alt="" /> 
            </a>
         </div>
         <div class="logo-new-blick m-logo">
            <a target="_blank" href="{{url('/')}}"> 
                <img src="{{$site_mob_img or ''}}" alt="" />  
            </a>
         </div>
         <ul class="nav navbar-top-links navbar-left hidden-xs">
                    <li><a href="javascript:void(0)" class="open-close hidden-xs waves-effect waves-light"><i class="icon-arrow-left-circle ti-menu"></i></a></li>
                </ul>
      </div>
    
     
      <div class="industries-header">Sales Manager Panel</div>
     
         
      <!-- <div class="dropdown flot-rgts">
         <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> <img id="profile-image" src="{{ getProfileImage($loggedInUserDetails['profile_image']) }}" alt="user-img" width="36" class="img-circle"><b class="hidden-xs color-fnts"><span>Welcome</span> {{$loggedInUserName or 'User'}}</b> </a>
         <ul class="dropdown-menu dropdown-user animated flipInY">
           <li class="menuList">
                  <a href="{{url('/sales_manager/account_settings')}}" class="{{ Request::is($maker_path.'/account_settings') == $maker_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Settings
                  </a>
           </li>
            <li><a href="{{url('/sales_manager/change_password')}}"><i class="ti-key"></i> Change Password</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="{{url('/sales_manager/logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
         </ul>
        
      </div>

       <div class="dropdown flot-rgts">
        <div class="notification-li">
        <div class="not-bell">
          <a href="{{url('sales_manager/notifications')}}">
          <i class="fa fa-bell"></i>
            <span>{{$notification_count or ''}}</span>
          </a>
        </div>
      </div>
    </div> -->

     <ul class="nav navbar-top-links navbar-right pull-right" id="myMenu">
    

        <li class="notification-li notification-admin">
          <div class="not-bell">
           <a href="{{url('sales_manager/notifications')}}">
            <!-- <i class="fa fa-bell"></i> -->
            <img src="{{url('/assets/front/images/bell.svg')}}">
            <span>{{$notification_count}}</span>
           </a>
          </div>
        </li>


      <li class="dropdown">
         <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> <img id="profile-image" src="{{ getProfileImage($loggedInUserDetails['profile_image']) }}" alt="user-img" width="36" class="img-circle"><b class="hidden-xs color-fnts"><span>Welcome</span> {{$loggedInUserName or 'User'}}</b> </a>
         <ul class="dropdown-menu dropdown-user animated flipInY">
           <li class="menuList">
                  <a href="{{url('/sales_manager/account_settings')}}" class="{{ Request::is($maker_path.'/account_settings') == $maker_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Settings
                  </a>
           </li>
            <li><a href="{{url('/sales_manager/change_password')}}"><i class="ti-key"></i> Change Password</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="{{url('/sales_manager/logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
         </ul>
         <!-- /.dropdown-user -->
      </li>

    </ul>

      <div class="clearfix"></div>
   </div>

</nav>
<!-- Left navbar-header -->
<div class="navbar-default sidebar newicon-add" role="navigation">
   <div class="sidebar-nav navbar-collapse slimscrollsidebar">
      <ul class="nav" id="side-menu">

          <li class="menuList"> 

            <a target="_blank" href="{{ url('/').'/search'}}" class="waves-effect">
              <img src="{{ url('/assets/images/icons/marketplace.svg')}}">
              <span class="hide-menu">Marketplace</span></a> 

          </li>

         <li class="menuList"> 
            <a href="{{ url('/').'/sales_manager/dashboard'}}" class="waves-effect {{ (Request::segment(2) == 'dashboard' )  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/dashboard.svg')}}">
              <span class="hide-menu"> Dashboard</span></a> 
          </li>

         <!--  <li class="menuList"> 
            <a href="{{ url('/').'/sales_manager/leads'}}" class="waves-effect {{ (Request::segment(2) == 'leads')  ? 'active' : '' }}"><span class="defualt-icon-sdbr myorder-retailer-icon"></span> <span class="hide-menu">My Orders</span></a> 
          </li> -->

           @php
           if(Request::segment(2) == 'leads')
           {
             $product_collapse    = 'in';
             $product_pro_active  = 'active';
             }else{
             $product_collapse    = '';
             $product_pro_active  = '';
           }
         @endphp 

          <li class="user-pro {{ $product_pro_active or ''}}">
            <a href="#" class="waves-effect">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Orders<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$product_collapse or ''}}">
               <li class="menuList"> 

                  <a href="{{ url('/').'/sales_manager/leads'}}" class="waves-effect {{ (Request::segment(2) == 'leads' && Request::segment(3) != 'reps' || Request::segment(3) == 'confirmed') && (Request::segment(3) != 'view_details')  ? 'active' : '' }}">
                    <span class="hide-menu">  My Orders</span>
                  </a>
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/sales_manager/leads/reps' }}" class="waves-effect {{ (Request::segment(2) == 'leads' && Request::segment(3) == 'reps' || Request::segment(3) == 'view_details')  ? 'active' : '' }}">
                    <span class="hide-menu"> Orders by Reps </span>

                  </a>   
               </li>
              
            </ul>
         </li>


          <li class="menuList"> 

         <li class="menuList"> 
            <a href="{{ url('/').'/sales_manager/vendors'}}" class="waves-effect {{ (Request::segment(2) == 'vendors') ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/user.svg')}}">
              <span class="hide-menu">My Vendors</span></a> 
         </li>

         <li class="menuList"> 

            <a href="{{ url('/').'/sales_manager/representative_listing'}}" class="waves-effect {{ (Request::segment(2) == 'representative_listing') || (Request::segment(2)=='add_representative') || (Request::segment(2)=='view') || (Request::segment(2)=='edit') ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/user.svg')}}">
              <span class="hide-menu">My Representatives</span></a> 
          </li>

          <li class="menuList"> 
            <a href="{{ url('/').'/sales_manager/retailer'}}" class="waves-effect {{ (Request::segment(2) == 'retailer')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/user.svg')}}">
              <span class="hide-menu">My Customers</span></a> 
          </li>

           <!-- <li class="menuList"> 
                <a href="{{ url('/').'/sales_manager/rep_sales_cancel_orders' }}" class="waves-effect {{ Request::segment(2) == 'rep_sales_cancel_orders' ? 'active' : '' }}">
                  <img src="{{ url('/assets/images/icons/cancel-order.svg')}}">
                  <span class="hide-menu">My Cancelled Orders</span></a> 
            </li> -->

          <li class="menuList"> 
            <a href="{{ url('sales_manager/notifications')}}" class="waves-effect {{ Request::segment(2) == 'notifications' ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/notification.svg')}}">
              <span class="hide-menu">Notifications</span></a> 
          </li>

         </ul>
       </div>
</div>
<!-- Left navbar-header end -->