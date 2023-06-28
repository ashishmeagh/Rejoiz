@php 
$influencer_path = config('app.project.influencer_panel_slug'); 
$loggedInUserName ='';
$influencer_code ='';

$first_name = $loggedInUserDetails['first_name'] or '' ;
$last_name  = $loggedInUserDetails['last_name'] or '';
$influencer_code  = $loggedInUserDetails['influencer_code'] or '';
$loggedInUserName = ucfirst($first_name).' '.$last_name;
$notification_count = get_notification_count('influencer');
@endphp
<!-- Navigation -->

<style type="text/css">
 .industries-header {
    color: #333;
    position: absolute;
    left:0px;
    top: 0px;
    right: 0;
    bottom: 0;
    font-weight: 600;
    font-size: 16px;
    height: 36px;
    /*width: 100%;*/
    text-align: center;
    padding: 0;
    margin: 7px auto;
 }
 .industries-header span{
   display: block;
   color: #666;
 }

.navbar-header{
   position: relative;
 }

.dropdown{
   margin-bottom: 10px;
}

.inflencer_code{
   margin-left: 60px;
   font-size: 16px;
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
  @media all and (max-width:767px){
   .industries-header{width: 100px; font-size: 11px;}
}
}
</style>

@php
$site_logo = get_site_settings(['site_logo']);
$site_logo = url('/storage/app/'.$site_logo['site_logo']);
$site_mob_img = isset($site_setting_arr['login_site_logo']) ? $site_setting_arr['login_site_logo'] : false;
$site_mob_img = imagePath($site_mob_img,'site_logo',0);


$site_base_img = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;
$site_image_base_path = base_path('storage/app/'.$site_base_img);
$site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;
$site_default_image = url('https://via.placeholder.com/160x53.png?text='.$site_name);
$site_img = image_resize($site_image_base_path,153,48,$site_default_image);
@endphp

<!-- <nav class="navbar navbar-default navbar-static-top m-b-0">
   <div class="navbar-header">
      <a class="navbar-toggle hidden-sm hidden-md hidden-lg " href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-collapse"><i class="ti-menu"></i></a>
      <div class="top-left-part">
         <div class="logo-new-blick desk-logo">
            <a target="_blank" href="{{url('/')}}">    
            <img src="{{$site_logo or ''}}" alt=""/> 
            </a>
         </div>
         <div class="logo-new-blick m-logo">
            <a target="_blank" href="{{url('/')}}">    
            <img src="{{url('/')}}/assets/images/m-logo.png" alt=""/> 
            </a>
         </div>
      </div>
      
       
      <div class="industries-header">Vendor/Company Name: <span>{{$company_name or 'N/A'}}</span></div>

      <ul class="nav navbar-top-links navbar-right pull-right" id="myMenu">
    

        <li class="notification-li">
          <div class="not-bell">
           <a href="{{url('influencer/notifications')}}">
           
            <img src="{{url('/assets/front/images/bell.svg')}}">
            <span>{{$notification_count}}</span>
           </a>
          </div>
        </li>


      <li class="dropdown">
         <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> <img id="profile-image" src="{{ getProfileImage($loggedInUserDetails['profile_image']) }}" alt="user-img" width="36" class="img-circle"><b class="hidden-xs color-fnts"><span>Welcome</span> {{$loggedInUserName or 'User'}}</b> </a>
         <ul class="dropdown-menu dropdown-user animated flipInY">
           <li class="menuList">
                  <a href="{{ url('/').'/'.$influencer_path.'/account_settings' }}" class="{{ Request::is($influencer_path.'/account_settings') == $influencer_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Settings
                  </a>
           </li>
            <li><a href="{{ url('/').'/'.$influencer_path.'/change_password' }}"><i class="ti-key"></i> Change Password</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="{{url('/influencer/logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
         </ul>
      </li>
   </div>
</nav> -->

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
      <div class="industries-header">Influencer Panel</div>
     
     <ul class="nav navbar-top-links navbar-right pull-right" id="myMenu">
  
        <li class="notification-li notification-admin">
          <div class="not-bell">
           <a href="{{url('influencer/notifications')}}">
           
            <img src="{{url('/assets/front/images/bell.svg')}}">
            <span>{{$notification_count}}</span>
           </a>
          </div>
        </li>


      <li class="dropdown">
         <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> <img id="profile-image" src="{{ getProfileImage($loggedInUserDetails['profile_image']) }}" alt="user-img" width="36" class="img-circle"><b class="hidden-xs color-fnts"><span>Welcome</span> {{$loggedInUserName or 'User'}}</b> </a>
         
         <b class="inflencer_code pb-4"><span>{{$influencer_code}}</span></b>
         
         <ul class="dropdown-menu dropdown-user animated flipInY">
           <li class="menuList">
                  <a href="{{ url('/').'/'.$influencer_path.'/account_settings' }}" class="{{ Request::is($influencer_path.'/account_settings') == $influencer_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Settings
                  </a>
           </li>
            <li><a href="{{ url('/').'/'.$influencer_path.'/change_password' }}"><i class="ti-key"></i> Change Password</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="{{url('/influencer/logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
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
         
          <li class="menuList mt-5"> 
            <a href="{{ url('/').'/'.$influencer_path.'/dashboard'}}" class=" {{ (Request::segment(2) == 'dashboard')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/dashboard.svg')}}">
              <span class="hide-menu"> Dashboard</span></a> 
          </li>

          <li class="menuList"> 
            <a href="{{ url('/').'/'.$influencer_path.'/promo_code'}}" class="waves-effect {{ (Request::segment(2) == 'promo_code')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/promo-code.svg')}}">
              <span class="hide-menu">Promo Code</span></a>
          </li>   

          <li class="menuList"> 
            <a href="{{ url('/').'/'.$influencer_path.'/customer_orders'}}" class="waves-effect {{ (Request::segment(2) == 'customer_orders')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu">Customer Orders</span></a>
          </li>
          
          <li class="menuList"> 
            <a href="{{ url('/').'/'.$influencer_path.'/quote_requests'}}" class="waves-effect {{ (Request::segment(2) == 'quote_requests')  ? 'active' : '' }}">
               <img src="{{ url('/assets/images/icons/quote.svg')}}">
              <span class="hide-menu">Quote Requests</span></a>
          </li>

          <li class="menuList"> 
            <a href="{{ url('/').'/'.$influencer_path.'/rewards_history'}}" class="waves-effect {{ (Request::segment(2) == 'rewards_history')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/rewards.svg')}}">
              <span class="hide-menu">Rewards History</span></a>
          </li>

           <li class="menuList">
              <a href="{{ url('/').'/'.$influencer_path.'/transaction_history' }}" class="waves-effect {{ (Request::segment(2) == 'transaction_history') ? 'active' : '' }}">
                <img src="{{ url('/assets/images/icons/transaction.svg')}}">
                <span class="hide-menu"> Transaction History</span>
              </a>
          </li>
         

          {{-- @if(isset($b2c_privacy_settings['is_influencer_module_on']) && 
                    $b2c_privacy_settings['is_influencer_module_on'] == '1')
            <li class="menuList"> 
               <a href="{{ url('/').'/'.$influencer_path.'/influencer_promo_code' }}" class="waves-effect {{ Request::segment(2) == 'influencer_promo_code' ? 'active' : '' }}">
                <img src="{{ url('/assets/images/icons/promo-code.svg')}}">
                <span class="hide-menu">Influencer Promo Code</span>
               </a>
            </li>
          @endif --}}

          <li class="menuList"> 
            <a href="{{ url('/').'/'.$influencer_path.'/notifications'}}" class=" {{ (Request::segment(2) == 'notifications')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/notification.svg')}}">
              <span class="hide-menu">Notifications</span></a> 
         </li>     

      </ul>
   </div>
</div>
<!-- Left navbar-header end -->