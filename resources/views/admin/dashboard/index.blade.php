@extends('admin.layout.master')  
@section('main_content')


@php
$b2c_privacy_settings = get_b2c_privacy_settings_detail();

$col_cnt = 3;
if(isset($b2c_privacy_settings['is_b2c_module_on']) && $b2c_privacy_settings['is_b2c_module_on'] == '1'){
    $col_cnt = '2';
}


@endphp
<!-- Page Content -->
        <div id="page-wrapper" class="dashboard_page_main_div">
            <div class="container-fluid">
                <div class="row bg-title">
                    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                        <h4 class="page-title">Dashboard</h4> </div>
                    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                        <ol class="breadcrumb">
                            <li class="active">Dashboard</li>
                        </ol>
                    </div>
                    <!-- /.col-lg-12 -->
                </div>

                <div class="row rw-sales-manager-dashboard-row">
                    

                    <div class="col-lg-4 col-sm-6 col-xs-12 dash_box">
                      <!--   <a href="{{url('/')}}/admin/products"> -->
                        <div class="white-box adm-dash-bx">
                            <div class="content">
                                <div class="icon-adm-dash"><img src="{{url('/assets/front/images/money.png')}}"></div>  
                                
                            <ul class="list-inline two-part">
                                <li><span class="counter"><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($total_order_amt)?number_format($total_order_amt,2):0.00}}</span></li>
                            </ul>
                            <p class="lastday-div">Last 365 days</p>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Total Order Amounts($)</h3>
                            </div>
                        </div>
                    <!--     </a> -->
                    </div>

                     <div class="col-lg-4 col-sm-6 col-xs-12 dash_box">
                     <!--    <a href="{{url('/')}}/admin/products"> -->
                        <div class="white-box adm-dash-bx">
                            <div class="content">
                                <div class="icon-adm-dash"><img src="{{url('/assets/front/images/money.png')}}"></div>
                            <ul class="list-inline two-part">
                                <li><span class="counter"><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($total_orders_amount)?number_format($total_orders_amount,2):0.00}}</span></li>
                            </ul>
                            <p class="lastday-div">Last 30 days</p>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Total Order Amounts($)</h3>
                            </div>
                        </div>
                    <!--     </a> -->
                    </div>

                    <div class="col-lg-4 col-sm-6 col-xs-12 dash_box">
                     <!--    <a href="{{url('/')}}/admin/products"> -->
                        <div class="white-box adm-dash-bx">
                            <div class="content">
                                <div class="icon-adm-dash"><img src="{{url('/assets/front/images/money.png')}}"></div>
                               
                            <ul class="list-inline two-part">
                                <li><span class="counter"><i class="fa fa-usd" aria-hidden="true"></i>{{ isset($total_order_amount)?number_format($total_order_amount,2):0.00}}</span></li>
                            </ul>
                             <p class="lastday-div">Last 7 days</p>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Total Order Amounts($)</h3>
                            </div>
                        </div>
                    <!--     </a> -->
                    </div>
            </div>
            <div class="row rw-sales-manager-dashboard-row row_dash_box2">
                <div class="col-lg-{{$col_cnt}} col-sm-6 col-xs-12 dash_box">
                     <a href="{{url('/')}}/admin/vendor">
                        <div class="white-box">
                            <div class="content">
                                <p class="text-right">&nbsp;</p>
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="icon-people text-info"></i></li>
                                <li class=""><span class="counter">{{ $arr_user_count['maker'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Vendors</h3>
                            </div>
                        </div>
                    </a>
                    </div>
                    <div class="col-lg-{{$col_cnt}} col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/admin/retailer">
                        <div class="white-box">
                            <div class="content">
                                <p class="text-right">&nbsp;</p>
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="icon-people text-info"></i></li>
                                <li class=""><span class="counter">{{ $arr_user_count['retailer'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Customers</h3>
                            </div>
                        </div>
                        </a>
                    </div>
                    <div class="col-lg-{{$col_cnt}} col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/admin/representative">
                        <div class="white-box">
                            <div class="content">
                                <p class="text-right">&nbsp;</p>
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="icon-people text-info"></i></li>
                                <li class=""><span class="counter">{{ $arr_user_count['representative'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Representatives</h3>
                            </div>
                        </div>
                        </a>
                    </div>
                    <div class="col-lg-{{$col_cnt}} col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/admin/sales_manager">
                        <div class="white-box">
                            <div class="content">
                                <p class="text-right">&nbsp;</p>
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="icon-people text-info"></i></li>
                                <li class=""><span class="counter">{{ $arr_user_count['sales_manager'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Sales Managers</h3>
                            </div>
                        </div>
                        </a>
                    </div>
                    @if(isset($b2c_privacy_settings['is_b2c_module_on']) && $b2c_privacy_settings['is_b2c_module_on'] == '1')
                    <div class="col-lg-{{$col_cnt}} col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/admin/customer">
                        <div class="white-box">
                            <div class="content">
                                <p class="text-right">&nbsp;</p>
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="icon-people text-info"></i></li>
                                <li class=""><span class="counter">{{ $arr_user_count['customer'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Customer</h3>
                            </div>
                        </div>
                        </a>
                    </div>
                    @endif

                    <div class="col-lg-{{$col_cnt}} col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/admin/products">
                        <div class="white-box">
                            <div class="content">
                                <p class="text-right">&nbsp;</p>
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="fa fa-cart-plus fa-lg text-info"></i></li>
                                <li class=""><span class="counter">{{ $arr_user_count['products'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Total Products</h3>
                            </div>
                        </div>
                        </a>
                    </div>
            </div>
        </div>
        <!-- /#page-wrapper -->
    </div>
   <script type="text/javascript">
       
   </script>
@stop                    