@php
$admin_path     = config('app.project.admin_panel_slug');
$obj_data  = Sentinel::getUser();
if($obj_data){
   $arr_data = $obj_data->toArray();    
}            
$first_name = isset($arr_data['first_name'])?$arr_data['first_name']:'';
$notification_count   = get_notification_count('admin');
$b2c_privacy_settings = get_b2c_privacy_settings_detail();

@endphp
<style type="text/css">
   .industries-header.kadoe-admin{
          line-height: 40px;
   }
   .industries-header {
   color: #333;
   position: absolute;
   left: 80px;
   top: 4px;
   right: 0;
   bottom: 0;
   font-weight: 600;
   font-size: 16px;
   height: 36px;
  /* width: 100%;*/
   text-align: center;
   padding: 0;
   margin: 7px auto;
   }
   .logo-new-blick img{
   /*height: 54px;*/
   margin: 0px 0 0 18px;
   text-align: center;
   display: block;
   }
   .industries-header span{
   display: block;
   color: #666;
   }
   .name-span
   {
   color: #666;  
   }
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
   .industries-header{width: 100px; font-size: 10px;}
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
          <img src="{{$site_img or ''}}" alt="" class="new-logo" /> 
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
    
     
      <div class="industries-header">{{$site_setting_arr['site_name'] or ''}} <span>Admin</span></div>
     
     <ul class="nav navbar-top-links navbar-right pull-right" id="myMenu">
  
        <li class="notification-li notification-admin">
          <div class="not-bell">
           <a href="{{url('admin/notifications')}}">
           
            <img src="{{url('/assets/front/images/bell.svg')}}">
            <span>{{$notification_count or ''}}</span>
           </a>
          </div>
        </li>


      <li class="dropdown">
         <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#"> <img src="{{getProfileImage(isset($arr_data['profile_image'])?$arr_data['profile_image']:'')}}" alt="user-img" width="36" class="img-circle" id="profile-image"><b class="hidden-xs">Welcome <span class="name-span">{{$arr_data['first_name'].' '.$arr_data['last_name']}}</b> </span></a>
         <ul class="dropdown-menu dropdown-user animated flipInY">
            <li><a href="{{url('/')}}"  target="_blank"><i class="ti-world"></i> Visit Website</a></li>
            <li>
               <a href="{{ url('/').'/'.$admin_path.'/account_settings' }}" class="{{ Request::is($admin_path.'/account_settings') == $admin_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
               Account Settings
               </a>
            </li>
            <li><a href="{{ url('/').'/'.$admin_path.'/change_password' }}"><i class="ti-key"></i> Change Password</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="{{ url('/').'/'.$admin_path }}/logout"><i class="fa fa-power-off"></i> Logout</a></li>
         </ul>
       
      </li>

    </ul>

      <div class="clearfix"></div>
   </div>

</nav>
<!-- Left navbar-header -->
@php
if(Request::is($admin_path.'/account_settings') == $admin_path.'/account_settings' ||
Request::is($admin_path.'/logout') == $admin_path.'/logout'
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
<div class="navbar-default sidebar newicon-add" role="navigation">
   <div class="sidebar-nav navbar-collapse slimscrollsidebar">
      <ul class="nav" id="side-menu">
         {{-- 
         <li class="sidebar-search hidden-sm hidden-md hidden-lg">
            <!-- input-group -->
            <div class="input-group custom-search-form">
               <input type="text" class="form-control" placeholder="Search..."> <span class="input-group-btn">
               <button class="btn btn-default" type="button"> <i class="fa fa-search"></i> </button>
               </span> 

            </div>
            <!-- /input-group -->
         </li>
         --}}
         <li class="user-pro {{$user_pro_active}}">
            {{--  <a href="#" class="waves-effect"><img src="{{getProfileImage(isset($arr_data['profile_image'])?$arr_data['profile_image']:'')}}" class="img-circle"> <span class="hide-menu"> {{$first_name}} <span class="fa arrow"></span></span>
            </a> --}}
            <ul class="nav nav-second-level collapse {{$collapse_in}}">
               {{-- 
               <li>
                  <a href="{{ url('/').'/'.$admin_path.'/account_settings' }}" class="{{ Request::is($admin_path.'/account_settings') == $admin_path.'/account_settings' ? 'active' : ''}}"><i class="ti-settings"></i> 
                  Account Setting
                  </a>
               </li>
               <li>
                  <a href="{{ url('/').'/'.$admin_path }}/logout"><i class="fa fa-power-off"> </i> Logout
                  </a>
               </li>
               --}}
            </ul>
         </li>
         <li class="menuList"> 
            <a href="{{ url('/').'/'.$admin_path.'/dashboard'}}" class="waves-effect {{ (Request::segment(2) == 'dashboard')  ? 'active' : '' }}">
               <img src="{{ url('/assets/images/icons/dashboard.svg')}}">
               <span class="hide-menu"> Dashboard</span></a> 
         </li>
         @php
         if(Request::segment(2) == 'products' || 
         Request::segment(2) == 'categories' || 
         Request::segment(2) == 'sub_category' ||
          Request::segment(2) == 'third_sub_category' ||
           Request::segment(2) == 'fourth_sub_category'
         )
         {
         $product_collapse    = 'in';
         $product_pro_active  = 'active';
         }else{
         $product_collapse    = '';
         $product_pro_active  = '';
         }
         @endphp 
         <li class="user-pro {{ $product_pro_active or ''}}">
            <a href="#" class="waves-effect ">
               <img src="{{ url('/assets/images/icons/products.svg')}}">
               <span class="hide-menu"> Products<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$product_collapse or ''}}">
               <li class="menuList"> 
                  <a href="{{ url($admin_path.'/products')}}" class="waves-effect {{ (Request::segment(2) == 'products')  ? 'active' : '' }}"> 
                     <span class="hide-menu">  Products</span>
                  </a>
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/categories' }}" class="waves-effect {{ (Request::segment(2) == 'categories')  ? 'active' : '' }}">

                     <span class="hide-menu"> Categories </span>
                  </a>   
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/sub_category' }}" class="waves-effect {{ (Request::segment(2) == 'sub_category')  ? 'active' : '' }}">
                     <span class="hide-menu"> Sub Categories </span>
                  </a>
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/third_sub_category' }}" class="waves-effect {{ (Request::segment(2) == 'third_sub_category')  ? 'active' : '' }}">
                     <span class="hide-menu"> Third level Categories </span>
                  </a>
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/fourth_sub_category' }}" class="waves-effect {{ (Request::segment(2) == 'fourth_sub_category')  ? 'active' : '' }}">
                     <span class="hide-menu"> Fourth level Categories </span>
                  </a>
               </li>
            </ul>
         </li>
          @php
         if(Request::segment(2) == 'request_categories' || Request::segment(2) == 'request_sub_categories' || Request::segment(2) == 'request_third_sub_categories' || Request::segment(2) == 'request_fourth_sub_categories')
         {
         $product_collapse    = 'in';
         $product_pro_active  = 'active';
         }else{
         $product_collapse    = '';
         $product_pro_active  = '';
         }
         @endphp 
          <li class="user-pro {{ $product_pro_active or ''}}">
            <a href="#" class="waves-effect "><!-- <span class="defualt-icon-sdbr categories-mkr-icon"></span> -->
               <img src="{{ url('/assets/images/icons/catalog.svg')}}"><span class="hide-menu"> Request Category<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$product_collapse or ''}}">
               
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/request_categories' }}" class="waves-effect {{ (Request::segment(2) == 'request_categories')  ? 'active' : '' }}"><!-- <span class="defualt-icon-sdbr categories-mkr-icon"></span> --><span class="hide-menu"> Categories </span>
                  </a>   
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/request_sub_categories' }}" class="waves-effect {{ (Request::segment(2) == 'request_sub_categories')  ? 'active' : '' }}"><!-- <span class="defualt-icon-sdbr subcategories-mkr-icon"></span> --> <span class="hide-menu"> Sub Categories </span>
                  </a>
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/request_third_sub_categories' }}" class="waves-effect {{ (Request::segment(2) == 'request_third_sub_categories')  ? 'active' : '' }}"><!-- <span class="defualt-icon-sdbr subcategories-mkr-icon"></span> --> <span class="hide-menu"> Third Level Categories </span>
                  </a>
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/request_fourth_sub_categories' }}" class="waves-effect {{ (Request::segment(2) == 'request_fourth_sub_categories')  ? 'active' : '' }}"><!-- <span class="defualt-icon-sdbr subcategories-mkr-icon"></span> --> <span class="hide-menu"> Fourth Level Categories </span>
                  </a>
               </li>
            </ul>
         </li>
         <li class="menuList"> 
            <a href="{{ url('/').'/'.$admin_path.'/rep_area' }}" class="waves-effect {{ (Request::segment(2) == 'rep_area')  ? 'active' : '' }}">
               <img src="{{ url('/assets/images/icons/area.svg')}}">
               <span class="hide-menu"> Areas/Regions</span>
            </a>
         </li>
         {{-- <li class="menuList"> 
            <a href="{{ url('/').'/'.$admin_path.'/card' }}" class="waves-effect {{ (Request::segment(2) == 'card')  ? 'active' : '' }}"><span class="defualt-icon-sdbr card-adm-icon"></span><span class="hide-menu"> Cards </span></a>
         </li> --}}
         @php
         if(Request::segment(2) == 'vendor' || 
            Request::segment(2) == 'retailer' || 
            Request::segment(2) == 'representative' ||
            Request::segment(2) == 'sales_manager' ||
            Request::segment(2) == 'influencer' ||
            Request::segment(2) == 'customer'  
         )
         {
            $user_collapse = 'in';
            $user_pro_active  = 'active';
         }else{
            $user_collapse = '';
            $user_pro_active  = '';
         }


         @endphp 
         <li class="user-pro {{ $user_pro_active or ''}}">
            <a href="#" class="waves-effect">
               <img src="{{ url('/assets/images/icons/user.svg')}}">
               <span class="hide-menu"> Users<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$user_collapse or ''}}">
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/representative' }}" class="waves-effect {{ Request::segment(2) == 'representative' ? 'active' : '' }}">
                     <span class="hide-menu"> Representatives</span>
                  </a> 
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/retailer' }}" class="waves-effect {{ Request::segment(2) == 'retailer' ? 'active' : '' }}">
                     <span class="hide-menu">  Customers</span>
                  </a>
               </li>
               @if(isset($b2c_privacy_settings['is_b2c_module_on']) && $b2c_privacy_settings['is_b2c_module_on'] == '1')
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/customer' }}" class="waves-effect {{ Request::segment(2) == 'customer' ? 'active' : '' }}">
                     <span class="hide-menu">  Customers</span>
                  </a>
               </li>
               @endif
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/sales_manager' }}" class="waves-effect {{ Request::segment(2) == 'sales_manager' ? 'active' : '' }}">
                     <span class="hide-menu"> Sales Managers</span>
                  </a> 
               </li>
               <li class="menuList"> 
                  <a href="{{ url('/').'/'.$admin_path.'/vendor' }}" class="waves-effect {{ Request::segment(2) == 'vendor' ? 'active' : '' }}">
                     <span class="hide-menu"> Vendors</span>
                  </a> 
               </li>

               @if(isset($b2c_privacy_settings['is_influencer_module_on']) && 
                         $b2c_privacy_settings['is_influencer_module_on'] == '1')
                  <li class="menuList"> 
                     <a href="{{ url('/').'/'.$admin_path.'/influencer' }}" class="waves-effect {{ Request::segment(2) == 'influencer' ? 'active' : '' }}">
                        <span class="hide-menu">Influencers</span>
                     </a>
                  </li>
               @endif

            </ul>
         </li>
         @php
         if(Request::segment(2) == 'retailer_orders' || 
         Request::segment(2) == 'leads' || 
         Request::segment(2) == 'cancel_orders' ||
         Request::segment(2) == 'rep_sales_cancel_orders' ||
         Request::segment(2) == 'customer_orders' ||
         Request::segment(2) == 'customer_cancel_orders'


         )
         {
         $order_collapse = 'in';
         $order_pro_active  = 'active';
         }else{
         $order_collapse = '';
         $order_pro_active  = '';
         }
         if(Request::segment(2) == 'transactions')
         {
         $transaction_collapse = 'in';
         $transaction_pro_active  = 'active';
         }else{
         $transaction_collapse = '';
         $transaction_pro_active  = '';
         }
         @endphp 
         <li class="order-pro {{ $order_pro_active or ''}}">
            <a href="#" class="waves-effect">
                <img src="{{ url('/assets/images/icons/orders.svg')}}">
               <span class="hide-menu"> Orders<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$order_collapse or ''}}">
               <li class="menuList">
                  <a href="{{ url($admin_path.'/retailer_orders')}}" class="waves-effect {{ (Request::segment(2) == 'retailer_orders')  ? 'active' : '' }}">
                     <span class="hide-menu">Orders by Customer</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url($admin_path.'/leads')}}" class="waves-effect {{ (Request::segment(2) == 'leads')  ? 'active' : '' }}">
                     <span class="hide-menu">Orders by Reps / Sales manager </span>
                  </a>
               </li>    
               @if(isset($b2c_privacy_settings['is_b2c_module_on']) && $b2c_privacy_settings['is_b2c_module_on'] == '1')           
               <li class="menuList">
                  <a href="{{ url($admin_path.'/customer_orders')}}" class="waves-effect {{ (Request::segment(2) == 'customer_orders')  ? 'active' : '' }}">
                     <span class="hide-menu">Orders by Customer</span>
                  </a>
               </li>
               @endif

             <!--  <li class="menuList">
                  <a href="{{ url($admin_path.'/cancel_orders')}}" class="waves-effect {{ (Request::segment(2) == 'cancel_orders')  ? 'active' : '' }}">
                     <span class="hide-menu"> Retailer Cancelled Orders</span>
                  </a>
               </li>
               @if(isset($b2c_privacy_settings['is_b2c_module_on']) && $b2c_privacy_settings['is_b2c_module_on'] == '1')
               <li class="menuList">
                  <a href="{{ url($admin_path.'/customer_cancel_orders')}}" class="waves-effect {{ (Request::segment(2) == 'customer_cancel_orders')  ? 'active' : '' }}">
                     <span class="hide-menu"> Customer Cancelled Orders</span>
                  </a>
               </li>
               @endif


               <li class="menuList">
                    <a href="{{ url($admin_path.'/rep_sales_cancel_orders')}}" class="waves-effect {{ (Request::segment(2) == 'rep_sales_cancel_orders')  ? 'active' : '' }}">
                     <span class="hide-menu"> Rep/Sales Cancelled Orders</span>
                    </a>
                </li> -->


            </ul>
         </li>
         @php
         if(Request::segment(2) == 'commission_reports' || Request::segment(2) == 'admin_commission_reports' || Request::segment(2) == 'direct_payment_to_vendor'||Request::segment(2)=='direct_payment_to_vendor'||Request::segment(3)=='commission_settings')
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
                  <a href="{{ url('/').'/'.$admin_path.'/direct_payment_to_vendor' }}" class="waves-effect {{ (Request::segment(2) == 'direct_payment_to_vendor')  ? 'active' : '' }}">
                     <span class="hide-menu">Direct Payment (To Vendor)</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/commission_reports' }}" class="waves-effect {{ (Request::segment(2) == 'commission_reports')  ? 'active' : '' }}">
                     <span class="hide-menu">Payment Intermediation (Through {{$site_setting_arr['site_name'] or ''}})</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/site_settings/commission_settings' }}" class="waves-effect {{ (Request::segment(3) == 'commission_settings')  ? 'active' : '' }}">
                     <span class="hide-menu">Settings</span>
                  </a>
               </li>

            </ul>
         </li> -->
         @php
         if(Request::segment(2) == 'customer_commission_reports')
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
                  <a href="{{ url('/').'/'.$admin_path.'/customer_commission_reports/direct_payment' }}" class="waves-effect {{ (Request::segment(3) == 'direct_payment')  ? 'active' : '' }}">
                     <span class="hide-menu">Direct Payment (To Vendor)</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/customer_commission_reports/payment_intermediation' }}" class="waves-effect {{ (Request::segment(3) == 'payment_intermediation')  ? 'active' : '' }}">
                     <span class="hide-menu">Payment Intermediation (Through {{$site_setting_arr['site_name']}})</span>
                  </a>
               </li>
            </ul>
         </li> -->
         @php
  /*       if((Request::segment(3)  == 'show_transaction_details' || 
         Request::segment(3) == 'vendor' ||
         Request::segment(3) == 'admin' ||
         Request::segment(3) == 'customer' ||
         Request::segment(3) == 'all' ||
         Request::segment(3) == 'representative' ||
         Request::segment(3) == 'sales_manager' ||
         Request::segment(3) == 'influencer'
         ) && (Request::segment(2) == 'transactions'))
         {
            $transaction_collapse    = 'in';
            $transaction_pro_active  = 'active';
         }
         else
         {
            $transaction_collapse    = '';
            $transaction_pro_active  = '';
         }*/



         if((Request::segment(3) == 'show_transaction_details' ||
             Request::segment(3)  == 'all_orders' ||
             Request::segment(3)  == 'customer'
           ) && (Request::segment(2) == 'transactions'))
         {
            $transaction_collapse    = 'in';
            $transaction_pro_active  = 'active';
         }
         else
         {
            $transaction_collapse    = '';
            $transaction_pro_active  = '';
         }


         @endphp 

        {{--  <li class="transaction-pro {{$transaction_pro_active or ''}}">
            <a href="#" class="waves-effect"><span class="defualt-icon-sdbr payment-t-mkr-icon"></span><span class="hide-menu">Transactions<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$transaction_collapse or ''}}">
               <li class="menuList active">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/all' }}" class="waves-effect {{ (Request::segment(3) == 'all') ? 'active' : '' }}">
                  <span class="defualt-icon-sdbr payment-t-mkr-icon"></span>  <span class="hide-menu"> All Payments</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/show_transaction_details' }}" class="waves-effect {{ (Request::segment(3) == 'show_transaction_details') ? 'active' : '' }}">
                  <span class="defualt-icon-sdbr payment-t-mkr-icon"></span>  <span class="hide-menu"> Retailer Payments</span>
                  </a>
               </li>

                <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/customer' }}" class="waves-effect {{ (Request::segment(3) == 'customer') ? 'active' : '' }}">
                  <span class="defualt-icon-sdbr payment-t-mkr-icon"></span>  <span class="hide-menu"> Customer Payments</span>
                  </a>
               </li>
               
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/vendor' }}" class="waves-effect {{ (Request::segment(3) == 'vendor') ? 'active' : '' }}">
                  <span class="defualt-icon-sdbr payment-t-mkr-icon"></span>  <span class="hide-menu"> Vendor Received Payments</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/admin' }}" class="waves-effect {{ (Request::segment(3) == 'admin') ? 'active' : '' }}">
                  <span class="defualt-icon-sdbr payment-t-mkr-icon"></span>  <span class="hide-menu"> Admin Received Payments</span>
                  </a>
               </li>
              
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/representative' }}" class="waves-effect {{ (Request::segment(3) == 'representative') ? 'active' : '' }}">
                  <span class="defualt-icon-sdbr payment-t-mkr-icon"></span>  <span class="hide-menu"> Representative Received Payments</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/sales_manager' }}" class="waves-effect {{ (Request::segment(3) == 'sales_manager') ? 'active' : '' }}">
                  <span class="defualt-icon-sdbr payment-t-mkr-icon"></span>  <span class="hide-menu"> Sales Manager Received Payments</span>
                  </a>
               </li>

               @if(isset($b2c_privacy_settings['is_influencer_module_on']) && 
                         $b2c_privacy_settings['is_influencer_module_on'] == '1')
                  <li class="menuList">
                     <a href="{{ url('/').'/'.$admin_path.'/transactions/influencer' }}" class="waves-effect {{ (Request::segment(3) == 'influencer') ? 'active' : '' }}">
                     <span class="defualt-icon-sdbr payment-t-mkr-icon"></span>  <span class="hide-menu"> Influencer Received Payments</span>
                     </a>
                  </li>
               @endif

            </ul>
         </li> --}}

       
         <li class="transaction-pro {{$transaction_pro_active or ''}}">
            <a href="#" class="waves-effect">
                <img src="{{ url('/assets/images/icons/order-pay.svg')}}">
               <span class="hide-menu">Order Payments<span class="fa arrow"></span></span></a>

               <ul class="nav nav-second-level collapse {{$transaction_collapse or ''}}">

               <li class="menuList active">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/all_orders' }}" class="waves-effect {{ (Request::segment(3) == 'all_orders') ? 'active' : '' }}">
                     <span class="hide-menu"> All Payments</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/show_transaction_details' }}" class="waves-effect {{ (Request::segment(3) == 'show_transaction_details') ? 'active' : '' }}">
                     <span class="hide-menu"> Customer Payments</span>
                  </a>
               </li>

                {{-- <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/customer' }}" class="waves-effect {{ (Request::segment(3) == 'customer') ? 'active' : '' }}">
                     <span class="hide-menu"> Customer Payments</span>
                  </a>
               </li> --}}

              </ul> 
         </li>     


         
         @php
         
         if((Request::segment(3)     == 'vendor' ||
            Request::segment(3)      == 'admin' ||
            Request::segment(3)      == 'representative' ||
            Request::segment(3)      == 'sales_manager' ||
            Request::segment(3)      == 'influencer' ||
            Request::segment(3)      == 'all_transaction'
           ) && (Request::segment(2) == 'transactions'))
         {
            $transaction_collapse    = 'in';
            $transaction_pro_active  = 'active';
         }
         else
         {
            $transaction_collapse    = '';
            $transaction_pro_active  = '';
         }
 

         @endphp


        <!-- <li class="transaction-pro {{$transaction_pro_active or ''}}">
            <a href="#" class="waves-effect">
                <img src="{{ url('/assets/images/icons/commission.svg')}}">
               <span class="hide-menu">Commission Payments<span class="fa arrow"></span></span></a>
              <ul class="nav nav-second-level collapse {{$transaction_collapse or ''}}">

                  <li class="menuList active">
                     <a href="{{ url('/').'/'.$admin_path.'/transactions/all_transaction' }}" class="waves-effect {{ (Request::segment(3) == 'all_transaction') ? 'active' : '' }}">
                        <span class="hide-menu"> All Payments</span>
                    </a>

                  </li>

                  <li class="menuList">
                     <a href="{{ url('/').'/'.$admin_path.'/transactions/vendor' }}" class="waves-effect {{ (Request::segment(3) == 'vendor') ? 'active' : '' }}">
                        <span class="hide-menu"> Vendor Received Payments</span>
                     </a>
                  </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/admin' }}" class="waves-effect {{ (Request::segment(3) == 'admin') ? 'active' : '' }}">
                     <span class="hide-menu"> Admin Received Payments</span>
                  </a>
               </li>
              
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/representative' }}" class="waves-effect {{ (Request::segment(3) == 'representative') ? 'active' : '' }}">
                     <span class="hide-menu"> Representative Received Payments</span>
                  </a>
               </li>

               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/transactions/sales_manager' }}" class="waves-effect {{ (Request::segment(3) == 'sales_manager') ? 'active' : '' }}">
                     <span class="hide-menu"> Sales Manager Received Payments</span>
                  </a>
               </li>

               @if(isset($b2c_privacy_settings['is_influencer_module_on']) && 
                         $b2c_privacy_settings['is_influencer_module_on'] == '1')
                  <li class="menuList">
                     <a href="{{ url('/').'/'.$admin_path.'/transactions/influencer' }}" class="waves-effect {{ (Request::segment(3) == 'influencer') ? 'active' : '' }}">
                        <span class="hide-menu"> Influencer Received Payments</span>
                     </a>
                  </li>
               @endif

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
         <!-- <li class="transaction-pro {{$refund_pro_active or ''}}">
            <a href="#" class="waves-effect">
                <img src="{{ url('/assets/images/icons/refund.svg')}}">
               <span class="hide-menu">Refunds<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$refund_collapse or ''}}">
              
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/refund/retailer' }}" class="waves-effect {{ (Request::segment(3) == 'retailer') ? 'active' : '' }}">
                     <span class="hide-menu"> Retailer Refund</span>
                  </a>
               </li>

               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/refund/rep_sales' }}" class="waves-effect {{ (Request::segment(3) == 'rep_sales') ? 'active' : '' }}">
                     <span class="hide-menu"> Reps / Sales Refund</span>
                  </a>
                </li>

               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/refund/customer' }}" class="waves-effect {{ (Request::segment(3) == 'customer') ? 'active' : '' }}">
                     <span class="hide-menu"> Customer Refund</span>
                  </a>
               </li>
             

            </ul>
         </li> -->

         @php
         if((Request::segment(2)== 'site_settings') ||
               Request::segment(2) == 'banner_images' ||
               Request::segment(2) == 'email_template'||
               Request::segment(2) == 'static_pages' ||
               Request::segment(2) == 'influencer_settings'||
               Request::segment(3) == 'commission_settings'||
               Request::segment(2) == 'menu_settings'

            )
         {
            $setting_collapse       = 'in';
            $setting_pro_active     = 'active';
         }else{
            $setting_collapse       = '';
            $setting_pro_active     = '';
         }
         @endphp 
         <li class="setting-pro {{ $setting_pro_active or ''}}">
            <a href="#" class="waves-effect">
                <img src="{{ url('/assets/images/icons/settings.svg')}}">
               <span class="hide-menu"> Settings<span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$setting_collapse or ''}}">

               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/site_settings/commission_settings' }}" class="waves-effect {{ (Request::segment(3) == 'commission_settings')  ? 'active' : '' }}">
                     <span class="hide-menu">Commission Settings</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/site_settings' }}" class="waves-effect {{ (Request::segment(2) == 'site_settings') && (Request::segment(3) != 'commission_settings') ? 'active' : '' }}">
                     <span class="hide-menu"> Site Settings</span>
                  </a>
               </li>

               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/menu_settings' }}" class="waves-effect {{ (Request::segment(2) == 'menu_settings') && (Request::segment(3) != 'commission_settings') ? 'active' : '' }}"><span class="hide-menu"> Menu Settings</span>
                  </a>
               </li>

               @if(isset($b2c_privacy_settings['is_influencer_module_on']) && 
                         $b2c_privacy_settings['is_influencer_module_on'] == '1')
                  <li class="menuList">
                     <a href="{{ url($admin_path.'/influencer_settings') }}" class="waves-effect {{ (Request::segment(2) == 'influencer_settings') ? 'active' : '' }}">
                        <span class="hide-menu">
                        Influencer Settings</span>
                     </a>
                  </li>
               @endif

               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path."/banner_images"}}" class="waves-effect {{ (Request::segment(2) == 'banner_images')  ? 'active' : '' }}">
                     <span class="hide-menu">Banner Images</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url($admin_path.'/email_template')}}" class="waves-effect {{ (Request::segment(2) == 'email_template')  ? 'active' : '' }}">
                     <span class="hide-menu">Email Templates</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/static_pages' }}" class="waves-effect {{ (Request::segment(2) == 'static_pages')  ? 'active' : '' }}">
                     <span class="hide-menu">CMS</span>
                  </a>
               </li>
            </ul>
         </li>

         @if(isset($b2c_privacy_settings['is_influencer_module_on']) && 
                         $b2c_privacy_settings['is_influencer_module_on'] == '1')
            <li class="menuList"> 
               <a href="{{ url('/').'/'.$admin_path.'/influencer_promo_code' }}" class="waves-effect {{ Request::segment(2) == 'influencer_promo_code' ? 'active' : '' }}">
                   <img src="{{ url('/assets/images/icons/promo-code.svg')}}">
                  <span class="hide-menu">Influencer Promo Code</span>
               </a>
            </li>

            <li class="menuList"> 
               <a href="{{ url('/').'/'.$admin_path.'/influencer_rewards_history' }}" class="waves-effect {{ Request::segment(2) == 'influencer_rewards_history' ? 'active' : '' }}">
                   <img src="{{ url('/assets/images/icons/rewards.svg')}}">
                  <span class="hide-menu">Influencer Rewards History</span>
               </a>
            </li>
         @endif
        
         <li class="menuList"> 
            <a href="{{ url('/').'/'.$admin_path.'/quote_requests' }}" class="waves-effect {{ Request::segment(2) == 'quote_requests' ? 'active' : '' }}">
               <img src="{{ url('/assets/images/icons/quote.svg')}}">
               <span class="hide-menu">Quote Requests</span>
            </a>
         </li>

         <li class="menuList">
            <a href="{{ url($admin_path.'/faq')}}" class="waves-effect {{ (Request::segment(2) == 'faq')  ? 'active' : '' }}">
               <img src="{{ url('/assets/images/icons/faqs.svg')}}">
               <span class="hide-menu">FAQs</span>
            </a>
         </li>
         @php
         if((
         Request::segment(3) == 'vendor' ||
         Request::segment(3) == 'representative' ||
         Request::segment(3) == 'sales'
         ) && (Request::segment(2) == 'commission_reports'))
         {
         $transaction_collapse    = 'in';
         $transaction_pro_active  = 'active';
         }
         else
         {
         $transaction_collapse    = '';
         $transaction_pro_active  = '';
         }
         @endphp 
         {{-- 
         <li class="commission-pro {{$transaction_pro_active or ''}}">
            <a href="#" class="waves-effect "><span class="defualt-icon-sdbr payment-t-mkr-icon"></span><span class="hide-menu"> Comission Reports <span class="fa arrow"></span></span></a>
            <ul class="nav nav-second-level collapse {{$transaction_collapse or ''}}">
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/commission_reports/representative' }}" class="waves-effect {{ (Request::segment(2) == 'commission_reports') && (Request::segment(3) == 'representative')  ? 'active' : '' }}"><span class="defualt-icon-sdbr payment-t-mkr-icon"></span> <span class="hide-menu">Reps Commission Reports</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/commission_reports/sales' }}" class="waves-effect {{ (Request::segment(2) == 'commission_reports') && (Request::segment(3) == 'sales')   ? 'active' : '' }}"><span class="defualt-icon-sdbr payment-t-mkr-icon"></span> <span class="hide-menu">Sales Manager Commission Reports</span>
                  </a>
               </li>
               <li class="menuList">
                  <a href="{{ url('/').'/'.$admin_path.'/commission_reports/vendor' }}" class="waves-effect {{ (Request::segment(2) == 'commission_reports') && ((Request::segment(3) == 'vendor') )  ? 'active' : '' }}"><span class="defualt-icon-sdbr payment-t-mkr-icon"></span> <span class="hide-menu">Vendor Commission Reports</span>
                  </a>
               </li>
            </ul>
         </li>


         --}}

          <!--   <li class="menuList">

               <a href="{{ url('/').'/'.$admin_path.'/all_orders_report' }}" class="waves-effect {{ (Request::segment(2) == 'all_orders_report')  ? 'active' : '' }}"><span class="defualt-icon-sdbr payment-t-mkr-icon"></span> <span class="hide-menu">Vendors Accounts Payable</span>
               </a>
      
            </li>  -->



         {{--  
         <li class="menuList">
            <a href="{{url($admin_path.'/payment/vendor')}}" class="waves-effect {{ (Request::segment(2) == 'payment') && (Request::segment(3) == 'vendor') ? 'active' : '' }}"><span class="defualt-icon-sdbr payment-transactions-retailer-icon"></span>  <span class="hide-menu">Vendor Payment</span>
            </a>
         </li>
         --}}

         <li class="menuList">
            <a href="{{url($admin_path.'/subscription')}}" class="waves-effect {{ (Request::segment(2) == 'subscription')  ? 'active' : '' }}">
               <img src="{{ url('/assets/images/icons/subscribe.svg')}}">
               <span class="hide-menu">Subscription</span>
            </a>
         </li>

         <li class="menuList">
            <a href="{{url($admin_path.'/notifications')}}" class="waves-effect {{ (Request::segment(2) == 'notifications')  ? 'active' : '' }}">
               <img src="{{ url('/assets/images/icons/notification.svg')}}">
               <span class="hide-menu">Notifications</span>
            </a>
         </li>
         {{--   
         <li class="">
            <a href="{{ url('/').'/'.$admin_path.'/contact_enquiry' }}" class="waves-effect {{ (Request::segment(2) == 'contact_enquiry')  ? 'active' : '' }}"><i data-icon="P" class="fa fa-envelope-o"></i> <span class="hide-menu">Contact Enquiry</span>
            </a>
         </li>
         --}}
         <li class="menuList">
            <a href="{{url($admin_path.'/visitors_enquiry')}}" class="waves-effect {{ (Request::segment(2) == 'visitors_enquiry')  ? 'active' : '' }}">
               <img src="{{ url('/assets/images/icons/visitors-icon.svg')}}">
               <span class="hide-menu">Visitors Enquiry</span>
            </a>
         </li>
      </ul>
   </div>
</div>

<!-- Left navbar-header end -->