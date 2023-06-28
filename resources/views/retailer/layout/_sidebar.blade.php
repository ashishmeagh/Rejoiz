@php 
$retailer_path = config('app.project.retailer_panel_slug'); 
$loggedInUserName ='';

$first_name = $loggedInUserDetails['first_name'] or '' ;
$last_name  = $loggedInUserDetails['last_name'] or '';
$loggedInUserName = ucfirst($first_name).' '.$last_name;
$notification_count = get_notification_count('retailer');
//$store_name = $loggedInUserDetails['store_name'] or '';

@endphp
<style type="text/css">
 .industries-header {
    color: #333;
    position: absolute;
    left: 80px;
    /*top: 4px;*/
    right: 0;
    bottom: 0;
    font-family: 'open_sanssemibold';
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
</style>

@php
    $site_logo = get_site_settings(['site_logo']);

    $site_logo = url('/storage/app/'.$site_logo['site_logo']);
    if(file_exists($site_logo)==true && $site_logo!='')
@endphp
<!--Navigation -->

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
    
     
      <div class="industries-header">Customer/Shop Name: <span>{{$store_name or 'N/A'}}</span></div>
     
     <ul class="nav navbar-top-links navbar-right pull-right" id="myMenu">
  
        <li class="notification-li notification-admin">
          <div class="not-bell">
           <a href="{{url('retailer/notifications')}}">
           
            <img src="{{url('/assets/front/images/bell.svg')}}">
            <span>{{$notification_count}}</span>
           </a>
          </div>
        </li>


      <li class="dropdown">
         <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> <img id="profile-image" src="{{ getProfileImage($loggedInUserDetails['profile_image']) }}" alt="user-img" width="36" class="img-circle"><b class="hidden-xs color-fnts">Welcome <span class="name-span">{{$loggedInUserName or 'User'}}</span></b> </a>
         <ul class="dropdown-menu dropdown-user animated flipInY">
           <li class="menuList">
                  <a href="{{ url('/').'/'.$retailer_path.'/account_settings' }}" class="{{ Request::is($retailer_path.'/account_settings') == $retailer_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Settings
                  </a>
           </li>
            <li><a href="{{ url('/').'/'.$retailer_path.'/change_password' }}"><i class="ti-key"></i> Change Password</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="{{url('/retailer/logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
         </ul>
         <!-- /.dropdown-user -->
      </li>

    </ul>

      <div class="clearfix"></div>
   </div>

</nav>
<!--end navbar  -->


<!-- Left navbar-header -->
<div class="navbar-default sidebar newicon-add" role="navigation">
   <div class="sidebar-nav navbar-collapse slimscrollsidebar">
      
       @php
         if(Request::is($retailer_path.'/account_settings') == $retailer_path.'/account_settings' ||
               Request::is($retailer_path.'/logout') == $retailer_path.'/logout'
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
          {{-- <li class="user-pro menuList {{$user_pro_active}}">
            <a href="#" class="waves-effect"><img src="{{ getProfileImage($loggedInUserDetails['profile_image']) }}" class="img-circle"> <span class="hide-menu"> {{$loggedInUserName or 'User'}} <span class="fa arrow"></span></span>
            </a>
            
      
      <ul class="nav nav-second-level collapse {{$collapse_in}}">
               <li>
                  <a href="{{ url('/').'/'.$retailer_path.'/account_settings' }}" class="{{ Request::is($retailer_path.'/account_settings') == $retailer_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Setting
                  </a>
               </li>

               <li>
                  <a href="{{url('/retailer/logout')}}"><i class="fa fa-power-off"> </i> Logout
                  </a>
               </li>
            </ul> --}}
         {{-- </li> --}}
         <li class="menuList"> 

            <a target="_blank" href="{{ url('/').'/search'}}" class="waves-effect">
              <img src="{{ url('/assets/images/icons/marketplace.svg')}}">
              <span class="hide-menu"> Shop Now</span></a> 

         </li>
         <li class="menuList"> 

            <a href="{{ url('/').'/'.$retailer_path.'/dashboard'}}" class="waves-effect {{ (Request::segment(2) == 'dashboard')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/dashboard.svg')}}">
              <span class="hide-menu"> Dashboard</span></a> 

            {{-- <a href="{{ url('/').'/'.$retailer_path.'/dashboard'}}" class="waves-effect {{ (Request::segment(2) == 'dashboard')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/dashboard.svg')}}">
              <span class="hide-menu"> Dashboard</span></a>  --}}

         </li>

         
         <li class="menuList"> 

            <a href="{{ url('/').'/'.$retailer_path.'/my_orders'}}" class="waves-effect {{ (Request::segment(2) == 'my_orders') && (Request::segment(3) != 'order_from_representative') && (Request::segment(3) != 'order_summary') && (Request::segment(3) != 'rep_sales_pending_orders') && (Request::segment(3) != 'rep_sales_completed_orders')? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> My Orders</span></a> 

         </li>

          @if(Request::segment(3) == 'my_orders')
         
           <li class="menuList"> 
            <a href="{{ url('/').'/'.$retailer_path.'/my_orders/order_from_representative'}}" class="waves-effect active">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Orders By Reps / Sales</span></a> 
         </li>

          @elseif(Request::segment(3) == 'order_summary')
           
             <li class="menuList"> 
            <a href="{{ url('/').'/'.$retailer_path.'/my_orders/order_from_representative'}}" class="waves-effect active">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Orders By Reps / Sales</span></a> 
         </li>

         @elseif(Request::segment(3) == 'order_from_representative')
       
          <li class="menuList"> 
            <a href="{{ url('/').'/'.$retailer_path.'/my_orders/order_from_representative'}}" class="waves-effect active">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Orders By Reps / Sales</span></a> 
         </li>

          @elseif(Request::segment(3) == 'rep_sales_pending_orders')

          <li class="menuList"> 
            <a href="{{ url('/').'/'.$retailer_path.'/my_orders/order_from_representative'}}" class="waves-effect active">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Orders By Reps / Sales</span></a> 
          </li>

          @elseif(Request::segment(3) == 'rep_sales_completed_orders')

           <li class="menuList"> 
            <a href="{{ url('/').'/'.$retailer_path.'/my_orders/order_from_representative'}}" class="waves-effect active">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Orders By Reps / Sales</span></a> 
          </li>

          @else
     

            <li class="menuList"> 

            <a href="{{ url('/').'/'.$retailer_path.'/my_orders/order_from_representative'}}" class="waves-effect">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Orders By Reps / Sales</span></a> 

          @endif


        <!--  <li class="menuList"> 

            <a href="{{ url('/').'/'.$retailer_path.'/my_cancel_orders'}}" class="waves-effect {{ (Request::segment(2) == 'my_cancel_orders')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/cancel-order.svg')}}">
              <span class="hide-menu"> My Cancelled Orders</span></a> 

         </li>

         <li class="menuList"> 

            <a href="{{ url('/').'/'.$retailer_path.'/rep_sales_cancel_orders'}}" class="waves-effect {{ (Request::segment(2) == 'rep_sales_cancel_orders')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/cancel-order.svg')}}">
              <span class="hide-menu"> Rep/Sales Cancelled Orders</span></a> 

         </li> -->

         <?php
          $is_active = '';

          if((Request::segment(2) == 'card'))
          {
            $is_active = 'active';
          }

         ?>

          <!-- <li>
            <a href="{{ url('/').'/'.$retailer_path.'/card'}}" class="waves-effect {{$is_active}}">
              <img src="{{ url('/assets/images/icons/manage-card.svg')}}">
            </span> <span class="hide-menu"> Manage Cards</span></a> 

          </li> -->


         

         <li class="menuList"> 

            <a href="{{ url('/').'/'.$retailer_path.'/transactions/show_transaction_details'}}" class="waves-effect {{ (Request::segment(2) == 'transactions')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/transaction.svg')}}">
              <span class="hide-menu"> Payment Transactions</span></a> 
            
         </li>
            
            
          <li class="menuList"> 
            <a href="{{ url('/').'/'.$retailer_path.'/notifications'}}" class="waves-effect {{ (Request::segment(2) == 'notifications')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/notification.svg')}}">
              <span class="hide-menu">Notifications</span></a> 
         </li>


      </ul>
   </div>
</div>
<!-- Left navbar-header end -->