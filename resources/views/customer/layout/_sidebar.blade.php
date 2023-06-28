@php 
$customer_path = config('app.project.customer_panel_slug'); 
$loggedInUserName ='';

$first_name = $loggedInUserDetails['first_name'] or '' ;
$last_name  = $loggedInUserDetails['last_name'] or '';
$loggedInUserName = ucfirst($first_name).' '.$last_name;
$notification_count = get_notification_count('customer');
//$store_name = $loggedInUserDetails['store_name'] or '';

@endphp
{{-- {{dd($arr_view_data)}}
 --}}
 <!-- <style type="text/css">
 .industries-header {
    color: #333;
    position: absolute;
    left:0px;
    top: 4px;
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
   color: #fb3b62;
 }
.navbar-header{position: relative;}

@media all and (max-width:1024px){
  .industries-header{
    white-space: normal;
    overflow: visible;
    font-size: 13px; left: 0px;
    width: 300px;
    word-break: break-all;
    line-height: 14px;
  }
}
@media all and (max-width:767px){
   .industries-header{width: 100px; font-size: 11px;}
}
</style> -->

<!-- Navigation -->
<nav class="navbar navbar-default navbar-static-top m-b-0">
   <div class="navbar-header">
      <a class="navbar-toggle hidden-sm hidden-md hidden-lg " href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-collapse"><i class="ti-menu"></i></a>
      <div class="top-left-part">
         <div class="logo-new-blick desk-logo">



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

      <div class="industries-header" >Customer Panel</div>


      <ul class="nav navbar-top-links navbar-right pull-right">
     

         <!-- /.dropdown -->
          <li class="notification-li notification-admin">
        <div class="not-bell">
          <a href="{{url('customer/notifications')}}">
          <img src="{{url('/assets/front/images/bell.svg')}}">
            <span>{{$notification_count}}</span>
          </a>
        </div>
      </li>

      <li class="dropdown menuList"  id="profile-logo">
         <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> <img src="{{ getProfileImage($loggedInUserDetails['profile_image']) }}" alt="user-img" width="36" id="profile-image" class="img-circle"><b class="hidden-xs color-fnts"> <span>Welcome</span> {{$loggedInUserName or 'User'}}</b> </a>
         <ul class="dropdown-menu dropdown-user animated flipInY">            
            <li>
               <a href="{{ url('/').'/'.$customer_path.'/account_settings' }}" class="{{ Request::is($customer_path.'/account_settings') == $customer_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Settings
                  </a>
            </li>
            <li><a href="{{ url('/').'/'.$customer_path.'/change_password' }}"><i class="ti-key"></i> Change Password</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="{{url('/customer/logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
         </ul>
         <!-- /.dropdown-user -->
      </li>

     

   </div>
   <!-- /.navbar-header -->
   <!-- /.navbar-top-links -->
   <!-- /.navbar-static-side -->
</nav>
<!-- Left navbar-header -->
<div class="navbar-default sidebar newicon-add" role="navigation">
   <div class="sidebar-nav navbar-collapse slimscrollsidebar">
      
       @php
         if(Request::is($customer_path.'/account_settings') == $customer_path.'/account_settings' ||
               Request::is($customer_path.'/logout') == $customer_path.'/logout'
            )
         {
            $collapse_in = "in";
            $user_pro_active = 'active';
         }else
         {
            $collapse_in = '';
            $user_pro_active = '';
         }
      @endphp

      <ul class="nav" id="side-menu">
         
         <li class="menuList"> 

            <a target="_blank" href="{{ url('/').'/search'}}" class="waves-effect">
              <img src="{{ url('/assets/images/icons/marketplace.svg')}}">
              <span class="hide-menu"> Shop Now</span></a> 

         </li>
         <li class="menuList"> 

            <a href="{{ url('/').'/'.$customer_path.'/dashboard'}}" class="waves-effect {{ (Request::segment(2) == 'dashboard')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/dashboard.svg')}}">
              <span class="hide-menu"> Dashboard</span></a> 

            

         </li>

         
         <li class="menuList"> 
            <a href="{{ url('/').'/'.$customer_path.'/my_orders'}}" class="waves-effect {{ (Request::segment(2) == 'my_orders') && (Request::segment(3) != 'order_from_representative') && (Request::segment(3) != 'order_summary') ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> My Orders</span></a> 
         </li>


          <li class="menuList"> 

            <a href="{{ url('/').'/'.$customer_path.'/my_cancel_orders'}}" class="waves-effect {{ (Request::segment(2) == 'my_cancel_orders')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/cancel-order.svg')}}">
              <span class="hide-menu"> My Cancelled Orders</span></a> 

         </li>

        
         <?php
          $is_active = '';

          if((Request::segment(2) == 'card'))
          {
            $is_active = 'active';
          }

         ?>

          <li>
            <a href="{{ url('/').'/'.$customer_path.'/card'}}" class="waves-effect {{$is_active}}">
              <img src="{{ url('/assets/images/icons/manage-card.svg')}}">
              <span class="hide-menu"> Manage Cards</span></a> 

          </li>



         <li class="menuList"> 

            <a href="{{ url('/').'/'.$customer_path.'/transactions/show_transaction_details'}}" class="waves-effect {{ (Request::segment(2) == 'transactions')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/transaction.svg')}}">
              <span class="hide-menu"> Payment Transactions</span></a> 
            
         </li>
            
            
          <li class="menuList"> 
            <a href="{{ url('/').'/'.$customer_path.'/notifications'}}" class="waves-effect {{ (Request::segment(2) == 'notifications')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/notification.svg')}}">
              <span class="hide-menu">Notifications</span></a> 
         </li>


      </ul>
   </div>
</div>
<!-- Left navbar-header end -->