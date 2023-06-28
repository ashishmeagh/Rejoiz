@php 
$maker_path = config('app.project.maker_panel_slug'); 
$loggedInUserName = '';
$company_name = '';

$first_name = $loggedInUserDetails['first_name'] or '' ;
$last_name  = $loggedInUserDetails['last_name'] or '';
$loggedInUserName = ucfirst($first_name).' '.$last_name;
$loggedInUserId = isset($loggedInUserDetails['id'])?$loggedInUserDetails['id']:0;

if($loggedInUserId!=0)
{
  $loggedInUserId = base64_encode($loggedInUserId);
}

$company_name = isset($maker_data['company_name'])?$maker_data['company_name']:'N/A';
$notification_count = get_notification_count('maker');
$b2c_privacy_settings = get_b2c_privacy_settings_detail();
@endphp
<!-- Navigation -->



<style type="text/css">
 .industries-header {
       color: #333;
    position: absolute;
    left: 80px;
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
.navbar-header{position: relative;}

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
   .industries-header{width: 100px; font-size: 10px;}
}
}
</style>

@php
$site_logo = get_site_settings(['site_logo']);
$site_logo = url('/storage/app/'.$site_logo['site_logo']);
if(file_exists($site_logo)==true && $site_logo!='')
@endphp

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
      <div class="industries-header">Vendor/Company Name: <span>{{$company_name or 'N/A'}}</span></div>
     
     <ul class="nav navbar-top-links navbar-right pull-right" id="myMenu">
  
        <li class="notification-li notification-admin">
          <div class="not-bell">
           <a href="{{url('vendor/notifications')}}">
           
            <img src="{{url('/assets/front/images/bell.svg')}}">
            <span>{{$notification_count}}</span>
           </a>
          </div>
        </li>


      <li class="dropdown">
         <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> <img id="profile-image" src="{{ getProfileImage($loggedInUserDetails['profile_image']) }}" alt="user-img" width="36" class="img-circle"><b class="hidden-xs color-fnts"><span>Welcome</span> {{$loggedInUserName or 'User'}}</b> </a>
         <ul class="dropdown-menu dropdown-user animated flipInY">
           <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/account_settings' }}" class="{{ Request::is($maker_path.'/account_settings') == $maker_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Settings
                  </a>
           </li>
            <li><a href="{{ url('/').'/'.$maker_path.'/change_password' }}"><i class="ti-key"></i> Change Password</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="{{url('/vendor/logout')}}"><i class="fa fa-power-off"></i> Logout</a></li>
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

            <a target="_blank" href="{{ url('/').'/search'}}" class="">
              <img src="{{ url('/assets/images/icons/marketplace.svg')}}">
              <span class="hide-menu"> Marketplace</span></a> 

         </li>

         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/dashboard'}}" class=" {{ (Request::segment(2) == 'dashboard')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/dashboard.svg')}}">
              <span class="hide-menu"> Dashboard</span></a> 
         </li>
         <li class="menuList"> 
            

            
              <a href="{{ url('/').'/'.$maker_path.'/company_settings'}}" class=" {{ (Request::segment(2) == 'company_settings')  ? 'active' : '' }}">
                <img src="{{ url('/assets/images/icons/settings.svg')}}">
                <span class="hide-menu">Company Settings</span></a>
            
            
            </li>

            @php
              if(Request::segment(2) == 'products'|| 
                 Request::segment(2) == 'product_images')
                {
                  $user_collapse = 'in';
                  $user_pro_active  = 'active';
                }
                else
                {
                  $user_collapse = '';
                  $user_pro_active  = '';
                }
            @endphp 
           
        <li class="user-pro {{ $user_pro_active or ''}}">

            <a href="#" class="">
              <img src="{{ url('/assets/images/icons/manage-pro.svg')}}">
              <span class="hide-menu">Manage Products<span class="fa arrow"></span></span></a>

            <ul class="nav nav-second-level collapse {{$user_collapse or ''}}">
                <li class="menuList">

                <a href="{{ url('/').'/'.$maker_path.'/products'}}" class=" {{ (Request::segment(2) == 'products')  ? 'active' : '' }}"><span class="hide-menu">Products</span></a> 
                </li>

                <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/product_images'}}" class=" {{ (Request::segment(2) == 'product_images')  ? 'active' : '' }}"><span class="hide-menu">Upload Images</span></a> 
                </li>

            </ul>
        </li>

         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/brand'}}" class=" {{ (Request::segment(2) == 'brand')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/manage-brand.svg')}}">
              <span class="hide-menu">Manage Brands</span></a> 
         </li>

         @php
              if(Request::segment(2) == 'request_category'|| 
                 Request::segment(2) == 'request_sub_category'||
                 Request::segment(2) == 'request_third_level_category'||
                 Request::segment(2) == 'request_fourth_level_category')
                {
                  $user_collapse = 'in';
                  $user_req_cat_active  = 'active';
                }
                else
                {
                  $user_collapse = '';
                  $user_req_cat_active  = '';
                }
            @endphp 
         <li class="user-pro {{ $user_req_cat_active or ''}}">

            <a href="#" class=""><img src="{{ url('/assets/images/icons/catalog.svg')}}"><span class="hide-menu">Requested Category<span class="fa arrow"></span></span></a>

            <ul class="nav nav-second-level collapse {{$user_collapse or ''}}">
              <li class="menuList">

              <a href="{{ url('/').'/'.$maker_path.'/request_category'}}" class=" {{ (Request::segment(2) == 'request_category')  ? 'active' : '' }}"><!-- <span class="defualt-icon-sdbr categories-mkr-icon"></span> --> <span class="hide-menu">Categories</span></a> 
              </li>

              <li class="menuList">
                <a href="{{ url('/').'/'.$maker_path.'/request_sub_category'}}" class=" {{ (Request::segment(2) == 'request_sub_category')  ? 'active' : '' }}"><!-- <span class="defualt-icon-sdbr subcategories-mkr-icon"> --></span> <span class="hide-menu">Sub Categories</span></a> 
              </li>

              <li class="menuList">
                <a href="{{ url('/').'/'.$maker_path.'/request_third_level_category'}}" class=" {{ (Request::segment(2) == 'request_third_level_category')  ? 'active' : '' }}"><!-- <span class="defualt-icon-sdbr subcategories-mkr-icon"> --></span> <span class="hide-menu">Third Level Categories</span></a> 
              </li>

              <li class="menuList">
                <a href="{{ url('/').'/'.$maker_path.'/request_fourth_level_category'}}" class=" {{ (Request::segment(2) == 'request_fourth_level_category')  ? 'active' : '' }}"><!-- <span class="defualt-icon-sdbr subcategories-mkr-icon"> --></span> <span class="hide-menu">Fourth Level Categories</span></a> 
              </li>
            </ul>
        </li>

     

            @php

            if(Request::segment(2) == 'catalogs'|| 
               Request::segment(2) == 'catalog_pages' ||
               Request::segment(2) == 'catalog_pdf' ||
               Request::segment(2) == 'catalog_images'
               )
              {
                $user_collapse = 'in';
                $user_pro_active  = 'active';
              }
              else
              {
                $user_collapse = '';
                $user_pro_active  = '';
              }


            
          @endphp 


         <li class="user-pro {{ $user_pro_active or ''}}">

            <a href="#" class="">
              <img src="{{ url('/assets/images/icons/catalog.svg')}}">
              <span class="hide-menu">Catalogs<span class="fa arrow"></span></span></a>

            <ul class="nav nav-second-level collapse {{$user_collapse or ''}}">
                <li class="menuList">

                  <a href="{{ url('/').'/'.$maker_path.'/catalogs'}}" class=" {{ (Request::segment(2) == 'catalogs')  ? 'active' : '' }}">
                    <span class="hide-menu">Create Catalogs</span></a> 
                </li>

                <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/catalog_pages/create'}}" class=" {{ (Request::segment(2) == 'catalog_pages')  ? 'active' : '' }}">
                    <span class="hide-menu">Add Pages</span></a> 
                </li>

                <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/catalog_pdf'}}" class=" {{ (Request::segment(2) == 'catalog_pdf')  ? 'active' : '' }}">
                    <span class="hide-menu">Hotspotted PDF</span></a> 
                </li>

                {{--  <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/catalog_images'}}" class=" {{ (Request::segment(2) == 'catalog_images')  ? 'active' : '' }}">
                    <span class="hide-menu">Catalog Images</span></a> 
                </li> --}}

          

            </ul>
         </li>
         
     
         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/retailer_orders'}}" class=" {{ (Request::segment(2) == 'retailer_orders')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Customers Orders </span></a> 
         </li>
        

         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/representative_orders'}}" class=" {{ (Request::segment(2) == 'representative_orders')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Orders By Reps / Sales </span></a> 
         </li>
         
        @if(isset($b2c_privacy_settings['is_b2c_module_on']) && $b2c_privacy_settings['is_b2c_module_on'] == '1')
         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/customer_orders'}}" class=" {{ (Request::segment(2) == 'customer_orders')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/orders.svg')}}">
              <span class="hide-menu"> Customer Orders </span></a> 
         </li>
        @endif

          @php

            if(Request::segment(2) == 'retailer_cancel_orders' ||
               Request::segment(2) == 'retailer_cancel_orders' ||
               Request::segment(2) == 'cancel_order_requests' ||
               Request::segment(2) == 'cancel_orders' ||
               Request::segment(2) == 'rep_sales_cancel_orders' ||
               Request::segment(2) == 'customer_cancel_orders' ||
               Request::segment(2) == 'customer_cancel_orders_request'
              )

              {
                $user_collapse = 'in';
                $user_pro_active  = 'active';
              }
              else
              {
                $user_collapse = '';
                $user_pro_active  = '';
              }
       @endphp 

       <!-- <li class="user-pro {{ $user_pro_active or ''}}">

        <a href="#" class="">
          <img src="{{ url('/assets/images/icons/cancel-order.svg')}}">
          <span class="hide-menu">Cancelled Orders<span class="fa arrow"></span></span></a>

         <ul class="nav nav-second-level collapse {{$user_collapse or ''}}">
         
          <li class=""menuList> 
            <a href="{{ url('/').'/'.$maker_path.'/retailer_cancel_orders'}}" class=" {{ (Request::segment(2) == 'retailer_cancel_orders')  ? 'active' : '' }}">
              <span class="hide-menu"> Retailer Cancelled Orders Requests</span></a> 
         </li>

          @if(isset($b2c_privacy_settings['is_b2c_module_on']) && $b2c_privacy_settings['is_b2c_module_on'] == '1')
         <li class=""> 
            <a href="{{ url('/').'/'.$maker_path.'/customer_cancel_orders_request'}}" class=" {{ (Request::segment(2) == 'customer_cancel_orders_request')  ? 'active' : '' }}">
              <span class="hide-menu"> Customer Cancelled Order Requests</span></a> 
         </li>
          @endif


         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/cancel_order_requests'}}" class=" {{ (Request::segment(2) == 'cancel_order_requests')  ? 'active' : '' }}">

              <span class="hide-menu"> Rep/Sales Cancelled Orders Requests</span></a> 
         </li>


         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/cancel_orders'}}" class=" {{ (Request::segment(2) == 'cancel_orders')  ? 'active' : '' }}">
              <span class="hide-menu"> Retailer Cancelled Orders</span></a> 
         </li>

          @if(isset($b2c_privacy_settings['is_b2c_module_on']) && $b2c_privacy_settings['is_b2c_module_on'] == '1')
         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/customer_cancel_orders'}}" class=" {{ (Request::segment(2) == 'customer_cancel_orders')  ? 'active' : '' }}">
              <span class="hide-menu"> Customer Cancelled Orders</span></a> 
         </li>
          @endif


          <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/rep_sales_cancel_orders'}}" class=" {{ (Request::segment(2) == 'rep_sales_cancel_orders')  ? 'active' : '' }}">
              <span class="hide-menu"> Rep/Sales Cancelled Orders</span></a> 
         </li>


        </ul> 
      </li> -->
       

          <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/promo_code'}}" class=" {{ (Request::segment(2) == 'promo_code')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/promo-code.svg')}}">
              <span class="hide-menu">Manage Promo Codes</span></a> 
         </li>

         <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/promotions'}}" class=" {{ (Request::segment(2) == 'promotions')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/rewards.svg')}}">
              <span class="hide-menu">Manage Promotions</span></a> 
         </li>

         {{-- <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/commissions'}}" class=" {{ (Request::segment(2) == 'commissions')  ? 'active' : '' }}">
              <span class="hide-menu">Commissions</span></a> 
         </li> --}}

         @php
         if(Request::segment(2) == 'commissions' && (Request::segment(3)=='admin_fees'||Request::segment(3)=='payments'))
         {
         $commission_collapse    = 'in';
         $commission_pro_active  = 'active';
         }else{
         $commission_collapse    = '';
         $commission_pro_active  = '';
         }
         @endphp 
         <!-- <li class="commission-pro {{ $commission_pro_active or ''}}">
            <a href="#" class="waves-effect">
              <img src="{{ url('/assets/images/icons/commission.svg')}}">
              <span class="hide-menu"> Commissions (Retailer)<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$commission_collapse or ''}}">
              <li class="menuList"> 
                  <a href="{{ url('/').'/'.$maker_path.'/commissions/admin_fees/'.$loggedInUserId }}" class="waves-effect {{ (Request::segment(2) == 'commissions' && Request::segment(3)=='admin_fees')  ? 'active' : '' }}">
                    <span class="hide-menu">{{$site_setting_arr['site_name'] ? $site_setting_arr['site_name'] : 'Admin'}} Fees (Vendor to Admin)</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/commissions/payments/'.$loggedInUserId }}" class="waves-effect {{ (Request::segment(2) == 'commissions' && Request::segment(3)=='payments')  ? 'active' : '' }}">
                    <span class="hide-menu">Payments (Admin to Vendor)</span>
                  </a>
               </li>
            </ul>
         </li> -->

        @php

         if(Request::segment(2) == 'customer_commission_reports' && (Request::segment(3)=='admin_fees'||Request::segment(3)=='payments'))
         {
      
         $commission_collapse    = 'in';
         $commission_pro_active  = 'active';
         }else{
         $commission_collapse    = '';
         $commission_pro_active  = '';
         }
         @endphp 
         <!-- <li class="commission-pro {{ $commission_pro_active or ''}}">
            <a href="#" class="waves-effect">
              <img src="{{ url('/assets/images/icons/commission.svg')}}">
              <span class="hide-menu">Commissions (Customer)<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$commission_collapse or ''}}">
               <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/customer_commission_reports/admin_fees/'.$loggedInUserId }}" class="waves-effect {{ (Request::segment(2) == 'customer_commission_reports' && Request::segment(3)=='admin_fees')  ? 'active' : '' }}">
                    <span class="hide-menu">{{$site_setting_arr['site_name'] ? $site_setting_arr['site_name'] : 'Admin'}} Fees (Vendor to Admin)</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/customer_commission_reports/payments/'.$loggedInUserId }}" class="waves-effect {{ (Request::segment(2) == 'customer_commission_reports' && Request::segment(3)=='payments')  ? 'active' : '' }}">
                    <span class="hide-menu">Payments (Admin to Vendor)</span>
                  </a>
               </li>
            </ul>
         </li>-->
     
         
        @php
            if(Request::segment(2) == 'transactions')

            {
                $user_collapse = 'in';
                $user_pro_active  = 'active';
            }
            else
            {
                $user_collapse = '';
                $user_pro_active  = '';
            }
        @endphp
      
         <!-- <li class="user-pro {{ $user_pro_active or ''}}">

            <a href="#" class="">
              <img src="{{ url('/assets/images/icons/transaction.svg')}}">
              <span class="hide-menu">Transactions<span class="fa arrow"></span></span></a>

            <ul class="nav nav-second-level collapse {{$user_collapse or ''}}">
                <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/transactions/admin'}}" class=" {{ (Request::segment(3) == 'admin')  ? 'active' : '' }}">
                    <span class="hide-menu">Admin Payments (Vendor to Admin)</span></a> 
                </li>

                <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/transactions/vendor'}}" class=" {{ (Request::segment(3) == 'vendor')  ? 'active' : '' }}">
                    <span class="hide-menu">Vendor Payments (Admin to Vendor)</span></a> 
                </li>

            </ul>
         </li> -->

            @php
               if(Request::segment(2) == 'refund')
               {
                  $refund_collapse = 'in';
                  $refund_pro_active  = 'active';
               }
               else
               {
                  $refund_collapse = '';
                  $refund_pro_active  = '';
               }
         @endphp 
         <!--  <li class="transaction-pro {{$refund_pro_active or ''}}">
            <a href="#" class="waves-effect">
              <img src="{{ url('/assets/images/icons/refund.svg')}}">
              <span class="hide-menu">Refunds<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$refund_collapse or ''}}">
              
                <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/refund/retailer' }}" class="waves-effect {{ (Request::segment(3) == 'retailer') ? 'active' : '' }}">
                    <span class="hide-menu"> Retailer Refund</span>
                  </a>
                </li>

                <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/refund/rep_sales' }}" class="waves-effect {{ (Request::segment(3) == 'rep_sales') ? 'active' : '' }}">
                    <span class="hide-menu"> Reps / Sales Refund</span>
                  </a>
                </li>

                <li class="menuList">
                  <a href="{{ url('/').'/'.$maker_path.'/refund/customer' }}" class="waves-effect {{ (Request::segment(3) == 'customer') ? 'active' : '' }}"><span class="hide-menu">Customer Refund</span>
                  </a>
                </li>
             

            </ul>
         </li>-->

          {{-- @if(isset($b2c_privacy_settings['is_influencer_module_on']) && 
                    $b2c_privacy_settings['is_influencer_module_on'] == '1')
            <li class="menuList"> 
               <a href="{{ url('/').'/'.$maker_path.'/influencer_promo_code' }}" class="waves-effect {{ Request::segment(2) == 'influencer_promo_code' ? 'active' : '' }}"><span class="defualt-icon-sdbr myretailers-retailer-icon"></span> <span class="hide-menu">Influencer Promo Code</span>
               </a>
            </li>
          @endif --}}

          <li class="menuList"> 
            <a href="{{ url('/').'/'.$maker_path.'/notifications'}}" class=" {{ (Request::segment(2) == 'notifications')  ? 'active' : '' }}">
              <img src="{{ url('/assets/images/icons/notification.svg')}}">
              <span class="hide-menu">Notifications</span></a> 
         </li>     

      </ul>
   </div>
</div>
<!-- Left navbar-header end -->
