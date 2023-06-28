@extends('influencer.layout.master')
@section('main_content')
<style>
   .dash_box .white-box .content .icon-dashbrds img{ width: 60px;}
</style>
<div id="page-wrapper" class="dashboard_page_main_div">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-sm-12 top-bg-title">
            <h4 class="page-title">Dashboard</h4>
            <div class="right">
               <ol class="breadcrumb">
                  <li class="active">Dashboard</li>
               </ol>
            </div>
         </div>
         <!-- /.col-lg-12 -->
      </div>

      @include('influencer.layout._operation_status')
      <div class="row row_dash_box">
          
          <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
               <div class="white-box">
                   <div class="content">
                       <ul class="list-inline two-part">
                       <li class="icon-dashbrds"><img src="{{url('/assets/front/images/sales_target_icon.png')}}"></li>
                       <li class="text-right"><span class="counter">${{ $current_sales_target or '0' }}</span></li>
                   </ul>
                   </div>
                   <div class="dash-footer">
                       <h3 class="box-title">Current Sales Target</h3>
                   </div>
               </div>
           </div>

           <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
               <div class="white-box">
                   <div class="content">
                       <ul class="list-inline two-part">
                       <li class="icon-dashbrds"><img src="{{url('/assets/front/images/reward_amount_icon.png')}}"></li>
                       <li class="text-right"><span class="counter">${{ $current_reward_amount or '0' }}</span></li>
                   </ul>
                   </div>
                   <div class="dash-footer">
                       <h3 class="box-title">Current Reward Amount</h3>
                   </div>
               </div>
           </div>

           <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
              <a href="{{ url('/').'/influencer/promo_code'}}">
               <div class="white-box">
                   <div class="content">
                       <ul class="list-inline two-part">
                       <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order.png')}}"></li>
                       <li class="text-right"><span class="counter">{{ $total_assigned_count or '0' }}</span></li>
                   </ul>
                   </div>
                   <div class="dash-footer">
                       <h3 class="box-title">Total Promo Code Assigned Count</h3>
                   </div>
               </div>
             </a>
           </div>
                    
           <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
              <a href="{{ url('/').'/influencer/promo_code'}}">
               <div class="white-box">
                   <div class="content">
                       <ul class="list-inline two-part">
                       <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-pendding.png')}}"></li>
                       <li class="text-right"><span class="counter">{{ $total_used_count or '0' }}</span></li>
                   </ul>
                   </div>
                   <div class="dash-footer">
                       <h3 class="box-title">Total Promo Code Used Count</h3>
                   </div>
               </div>
             </a>
           </div>

           <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
             <a href="{{ url('/').'/influencer/rewards_history'}}">
               <div class="white-box">
                   <div class="content">
                       <ul class="list-inline two-part">
                       <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-complete.png')}}"></li>
                       <li class="text-right"><span class="counter">${{ $total_received_rewards or '0' }}</span></li>
                   </ul>
                   </div>
                   <div class="dash-footer">
                       <h3 class="box-title">Total Received Rewards</h3>
                   </div>
               </div>
             </a>
           </div>


           <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">  
            <a href="{{ url('/').'/influencer/rewards_history'}}">             
               <div class="white-box">
                   <div class="content">
                       <ul class="list-inline two-part">
                       <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-cancle.png')}}"></li>
                       <li class="text-right"><span class="counter">${{ $total_pending_rewards or '0' }}</span></li>
                   </ul>
                   </div>
                   <div class="dash-footer">
                       <h3 class="box-title">Total Pending Rewards</h3>
                   </div>
               </div>
             </a>
           </div>  
      </div>

      <div class="container mb-5">
          <h2>Orders</h2>
          <table class="table table-bordered">
              <thead>
                <tr>
                    <th>Completed</th>
                    <th>Pending</th>
                    <th>Cancelled</th>
                </tr>
              </thead>
              <tbody>
                <tr style="height: 90px; font-size: 25px">
                    @foreach($influencer_order_count as $order_count)
                    <td>{{$order_count}}</td>
                    @endforeach
                </tr>
              </tbody>
          </table>
      </div>

      <div class="rrow-shop">
         <div class="row">
               <div class="row" style="overflow:hidden;">
                  <div class="col-md-6 text-center">
                     @if($is_stripe_connected == false)
                     @php
                        $site_name = isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'admin';
                     @endphp
                        <div class="white-box">
                           <div style="width: 100%; height: 150px;">
                                 <p>To receive rewards, you need to connect your stripe acccount to {{$site_name}} stripe account. Please click on below button for connecting {{$site_name}} stripe account.</p>
                                 <a href="{{ $connection_request_link or '' }}" target="_blank" class="btn btn-danger striprbtn">
                                    Connect to {{$site_name}} stripe account
                                 </a>
                           </div>
                        </div>
                     @endif
                  </div>
               </div>
         </div>
      </div>
   </div>
   <!-- /#page-wrapper -->
</div>
@stop