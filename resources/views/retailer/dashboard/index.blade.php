@extends('retailer.layout.master')  
@section('main_content')

<!-- Page Content -->
<style>
    .dash_box .white-box .content .icon-dashbrds img{    width: 60px;}



    .main-dash-box {background-color:#fff;}
    .main-dash-box .dash_box {min-width:20%;}
    .total-ttl {margin-bottom:15px;}
    .total-ttl h2 {font-weight:normal !important; font-family:inherit !important; font-size:22px;}
    .total-ttl h2 span {font-weight:600 !important;}
    .total-ttl a {color:#141414;}
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
            
                @include('admin.layout._operation_status')
                
                <div class="col-sm-12 col-md-12 col-lg-12 text-right dashboard-note ">
                        <p>Excluded Split Orders</p>
                    </div>
                <div class="col-sm-12 main-dash-box white-box">
                <div class="row row_dash_box">

                    <div class="col-sm-12 total-ttl">
                        <h2>Rep/Sales Total Orders: <span><a href="{{url('/')}}/retailer/my_orders/order_from_representative">{{ $order_count_arr['total_count'] or '0' }}</a></span></h2>
                    </div>
          <!--           <div class="col-lg-4 col-sm-6 col-xs-12">
                        <a href="{{url('/')}}/retailer/my_orders">
                        <div class="white-box">
                            <h3 class="box-title">Total My Orders</h3>
                            <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="fa fa-quote-left text-purple"></i></li>
                                <li class="text-right"><span class="counter">{{ $arr_count['quote_count'] or '0' }}</span></li>
                            </ul>
                        </div>
                        </a>
                    </div>

                     <div class="col-lg-4 col-sm-6 col-xs-12">
                        <a href="{{url('/')}}/retailer/my_cancel_orders">
                        <div class="white-box">
                            <h3 class="box-title">Total Canceled Orders</h3>
                            <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="fa fa-quote-left text-purple"></i></li>
                                <li class="text-right"><span class="counter">{{ $arr_count['cancel_quote_count'] or '0' }}</span></li>
                            </ul>
                        </div>
                        </a>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-xs-12">
                        <a href="{{url('/')}}/retailer/my_orders/order_from_representative">
                        <div class="white-box">
                            <h3 class="box-title">Total Orders by Sales / Reps</h3>
                            <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="fa fa-quote-left text-purple"></i></li>
                                <li class="text-right"><span class="counter">{{ $arr_count['reps_order'] or '0' }}</span></li>
                            </ul>
                        </div>
                        </a>
                    </div>


                     <div class="col-lg-4 col-sm-6 col-xs-12">
                        <a href="{{url('/')}}/retailer/rep_sales_cancel_orders">
                        <div class="white-box">
                            <h3 class="box-title">Total Canceled Orders of Sales / Reps</h3>
                            <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><i class="fa fa-quote-left text-purple"></i></li>
                                <li class="text-right"><span class="counter">{{ $arr_count['rep_cancle_order_count'] or '0' }}</span></li>
                            </ul>
                        </div>
                        </a>
                    </div> -->

                    {{-- <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/retailer/my_orders/order_from_representative">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $order_count_arr['total_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Rep/Sales Total Orders
                                </h3>
                            </div>
                        </div>
                        </a>
                    </div> --}}
                    
                    <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/retailer/my_orders/rep_sales_pending_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-pendding.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $order_count_arr['pending_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Rep/Sales Pending Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div>


                    <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        
                        <a href="{{url('/')}}/retailer/my_orders/rep_sales_completed_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-complete.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $order_count_arr['completed_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Rep/Sales Completed Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div>


                    <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        
                        <a href="{{url('/')}}/retailer/rep_sales_cancel_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-cancle.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $order_count_arr['canceled_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Rep/Sales Cancelled Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div>  

                    <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        
                        <a href="{{url('/')}}/retailer/my_orders/net_30_rep_sales_completed_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-complete.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $order_count_arr['net_30_completed_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title"> Rep/Sales - Net30 Completed Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div>

                     <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        
                        <a href="{{url('/')}}/retailer/my_orders/net_30_rep_sales_pending_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-pendding.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $order_count_arr['net_30_pending_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title"> Rep/Sales - Net30 Pending Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div>
                </div>
            </div>


            <div class="col-sm-12 main-dash-box white-box">
                <div class="row row_dash_box">
                    <div class="col-sm-12 total-ttl">
                        <h2>My Total Orders: <span><a href="{{url('/')}}/retailer/my_orders">{{ $quotes_arr['total_count'] or '0' }}</a></span></h2>
                    </div>
                    {{-- <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/retailer/my_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $quotes_arr['total_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">My Total Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div> --}}
                    
                    <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/retailer/my_orders/my_pending_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-pendding.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $quotes_arr['pending_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">My Pending Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div>


                    <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/retailer/my_orders/my_completed_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-complete.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $quotes_arr['completed_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">My Completed Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div>


                    <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        
                        <a href="{{url('/')}}/retailer/my_cancel_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-cancle.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $quotes_arr['canceled_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">My Cancelled Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div> 

                    <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        
                        <a href="{{url('/')}}/retailer/my_orders/my_net_30_completed_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-complete.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $quotes_arr['my_net_30_completed_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title"> My Net30 Completed Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div>

                     <div class="col-lg-2 col-sm-6 col-xs-12 dash_box">
                        
                        <a href="{{url('/')}}/retailer/my_orders/my_net_30_pending_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-pendding.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ $quotes_arr['my_net_30_pending_count'] or '0' }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title"> My Net30 Pending Orders</h3>
                            </div>
                        </div>
                        </a>
                    </div> 
                </div>
            </div>


            <div class="row">
                 
                    
                    <!-- 
                    1) orders_data_arr.completed_order!=0 || orders_data_arr.pending_order!=0 || orders_data_arr.canceled_order!=0
                    if($order_count_arr['total_count'] != 0)
                    lead_count_arr
                     -->
                    @if(isset($sales_leads_count_arr['completed_order']) && !empty($sales_leads_count_arr['completed_order']) || isset($sales_leads_count_arr['pending_order']) && !empty($sales_leads_count_arr['pending_order']) || isset($sales_leads_count_arr['canceled_order']) && !empty($sales_leads_count_arr['canceled_order']) ||
                    isset($lead_count_arr['completed_order']) && !empty($lead_count_arr['completed_order']) || isset($lead_count_arr['pending_order']) && !empty($lead_count_arr['pending_order']) || isset($lead_count_arr['canceled_order']) && !empty($lead_count_arr['canceled_order'])
                    )
                    <div class="col-md-12">
                        <h3 class="mt-3 mb-3 text-left">Rep/Sales orders</h3>
                        <div class="row" style="overflow:hidden;">
                           
                            <div class="col-md-6 text-center" id="div_piechart">
                              <div class="white-box">
                                  <h5>Orders in last 7 days</h5>
                                  <div class="innerchart" id="piechart" style="width: 100%; height: 300px;"></div>
                              </div>
                            </div>
                            <div class="col-md-6 text-center" id="div_piechart1">
                                <div class="white-box">
                                    <h5>Orders in last 30 days</h5>
                                    <div class="innerchart" id="piechart1" style="width: 100%; height: 300px;"></div>
                                </div>
                            </div>  
                        </div>
                    </div>
                    @endif

                    <!-- 
                        if($quotes_arr['total_count'] != 0) 
                    -->
                    @if(isset($retailer_quotes_arr['completed_order']) && !empty($retailer_quotes_arr['completed_order']) || isset($retailer_quotes_arr['pending_order']) && !empty($retailer_quotes_arr['pending_order']) || isset($retailer_quotes_arr['canceled_order']) && !empty($retailer_quotes_arr['canceled_order']) ||
                    isset($sales_quotes_count_arr['completed_order']) && !empty($sales_quotes_count_arr['completed_order']) || isset($sales_quotes_count_arr['pending_order']) && !empty($sales_quotes_count_arr['pending_order']) || isset($sales_quotes_count_arr['canceled_order']) && !empty($sales_quotes_count_arr['canceled_order'])
                    )
                    <div class="col-sm-12">
                        <h3 class="mt-3 mb-3 text-left">My Orders</h3>
                        <div class="row" style="overflow:hidden;">
                            <div class="col-md-6 text-center" id="div_piechart2">
                                <div class="white-box">
                                  <h3>Orders in last 7 days</h3>
                                  <div id="piechart2" style="width: 100%; height: 300px;"></div>
                                </div>
                            </div>
                
                        <div class="col-md-6 text-center" id="div_piechart3">
                            <div class="white-box">
                                <h3>Orders in last 30 days</h3>
                               <div id="piechart3" style="width: 100%; height: 300px;"></div>
                           </div>
                        </div> 
                        </div>
                    </div>
                    @endif

                   <!-- bar chart for rep/sales order  -->
                    <div class="col-md-12" id="div_bar_chart">  
                         <h3 class="mt-3 mb-3">Rep/Sales Orders</h3>      
                         <div class="white-box" style="overflow:hidden;">
                            <div id="chart_div1" style="width: 100%; height: 500px;"></div>
                        </div>
                    </div>
                            
                    <div class="col-md-12" id="div_bar_chart1">
                        <div class="white-box" style="overflow:hidden;">
                        <div id="chart_div2" style="width: 100%; height: 500px;"></div>
                    </div>
                    </div>

                   
                   <!-- bar chart for retailer orders -->
                    <div class="col-md-12" id="div_bar_char2"> 
                        <h3 class="mt-3 mb-3">My Orders</h3>        
                        <div class="white-box" style="overflow:hidden;">
                        <div id="chart_div3" style="width: 100%; height: 500px;"></div>
                    </div>
                    </div>
                            
                           
                    <div class="col-md-12" id="div_bar_chart3">
                        <div class="white-box" style="overflow:hidden;">
                        <div id="chart_div4" style="width: 100%; height: 500px;"></div>
                    </div>
                    </div>


            </div>



            </div>


<div>



            

               
</div>

        </div>
        <!-- /#page-wrapper -->
    </div>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    
   <script type="text/javascript">
       
    //bar chart
    var order_data =  <?php echo json_encode($orders_arr); ?>;

    var order_month_data = <?php echo json_encode($orders_data);?>;

    var month_name = '{{$previous_month_name or ''}}';


    var retailer_orders_arr =  <?php echo json_encode($retailer_orders_arr); ?>;

    var retailer_order_data = <?php echo json_encode($retailer_order_data);?>;




   
   // pie chart

   //  retailer quotes & days
    var quotes_data = <?php echo json_encode($sales_quotes_count_arr); ?>;

    // 30 Days
    var quotes_arr = <?php echo json_encode($retailer_quotes_arr); ?>;


    //rep/sales leads

    var orders_data_arr = <?php echo json_encode($sales_leads_count_arr); ?>;

    var leads_data = <?php echo json_encode($lead_count_arr); ?>;






    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawVisualization);

    function drawVisualization() 
    {
        //last week order report rep/sales
        var data = google.visualization.arrayToDataTable([
          ['','Completed', 'Pending', 'Cancelled'],

          ['Monday',order_data.Monday.completed_order,order_data.Monday.pending_order,         order_data.Monday.canceled_order],

          ['Tuesday',order_data.Tuesday.completed_order,order_data.Tuesday.pending_order,         order_data.Tuesday.canceled_order],

          ['Wednesday',order_data.Wednesday.completed_order,order_data.Wednesday.pending_order,         order_data.Wednesday.canceled_order],

          ['Thursday',order_data.Thursday.completed_order,order_data.Thursday.pending_order,         order_data.Thursday.canceled_order],

          ['Friday',order_data.Friday.completed_order,order_data.Friday.pending_order,         order_data.Friday.canceled_order],

          ['Saturday',order_data.Saturday.completed_order,order_data.Saturday.pending_order,         order_data.Saturday.canceled_order],

          ['Sunday',order_data.Sunday.completed_order,order_data.Sunday.pending_order,         order_data.Sunday.canceled_order]

        ]);

        var options = {
            title : 'Last week orders',
           
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {3: {type: 'line'}},
            vAxis: {
                        viewWindow: {
                            min: 100,
                            max: 1000

                        },
                        title: 'Order Amount($)',
                        ticks: [100,200,300,400,500,600,700,800,900,1000] 
                    },
            colors: ['#ffb6c1','#b19cd9','#ec9787']        
                  
        };

        var chart = new google.visualization.ComboChart(document.getElementById('chart_div1'));

        chart.draw(data, options);

       
        //last month order report for rep/sales

        if(month_name == 'January')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',order_month_data.January.completed_order,order_month_data.January.pending_order,         order_month_data.January.canceled_order],

            ['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],

            ['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);
        }
        else if(month_name == 'February'){
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',order_month_data.February.completed_order,order_month_data.February.pending_order,order_month_data.February.canceled_order],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);
        }
        else if(month_name == 'March')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',order_month_data.March.completed_order,order_month_data.March.pending_order,order_month_data.March.canceled_order],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]); 
        }
        else if(month_name == 'April')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',order_month_data.April.completed_order,order_month_data.April.pending_order,order_month_data.April.canceled_order],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'May')
        {
           
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',order_month_data.May.completed_order,order_month_data.May.pending_order,order_month_data.May.canceled_order],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'June')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',order_month_data.Jun.completed_order,order_month_data.Jun.pending_order,order_month_data.Jun.canceled_order],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'July'){

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',order_month_data.July.completed_order,order_month_data.July.pending_order,order_month_data.July.canceled_order],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);  
        }

        else if(month_name == 'August'){

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',order_month_data.August.completed_order,order_month_data.August.pending_order,order_month_data.August.canceled_order],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'September')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',order_month_data.September.completed_order,order_month_data.September.pending_order,order_month_data.September.canceled_order],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);    
        }
        else if(month_name == 'October')
        {

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',order_month_data.October.completed_order,order_month_data.October.pending_order,order_month_data.October.canceled_order],['Nov',0,0,0],['Dec',0,0,0]

            ]);    
        }

        else if(month_name == 'November')
        {
           
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',order_month_data.November.completed_order,order_month_data.November.pending_order,order_month_data.November.canceled_order],['Dec',0,0,0]

            ]); 
        }
        else if(month_name == 'December')
        {
          
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',order_month_data.December.completed_order,order_month_data.December.pending_order,order_month_data.December.canceled_order]

            ]); 

        }


        var options = {
            title : 'Last month orders',
            
            hAxis: {title: 'Months'},
            seriesType: 'bars',
            series: {3: {type: 'line'}},
            vAxis: {
                    viewWindow: {
                        min: 100,
                        max: 1000
                    },
                    title: 'Order Amount($)',
                    ticks: [100,200,300,400,500,600,700,800,900,1000] 
                },
            colors: ['#ffb6c1','#b19cd9','#ec9787']    
                       
        };

        var chart = new google.visualization.ComboChart(document.getElementById('chart_div2'));

        chart.draw(data, options);




        //---------last week and last month order report for retailer order--------


         var data = google.visualization.arrayToDataTable([
          ['','Completed', 'Pending', 'Cancelled'],

          ['Monday',retailer_orders_arr.Monday.completed_order,retailer_orders_arr.Monday.pending_order,         retailer_orders_arr.Monday.canceled_order],

          ['Tuesday',retailer_orders_arr.Tuesday.completed_order,retailer_orders_arr.Tuesday.pending_order,         retailer_orders_arr.Tuesday.canceled_order],

          ['Wednesday',retailer_orders_arr.Wednesday.completed_order,retailer_orders_arr.Wednesday.pending_order,         retailer_orders_arr.Wednesday.canceled_order],

          ['Thursday',retailer_orders_arr.Thursday.completed_order,retailer_orders_arr.Thursday.pending_order,         retailer_orders_arr.Thursday.canceled_order],

          ['Friday',retailer_orders_arr.Friday.completed_order,retailer_orders_arr.Friday.pending_order,         retailer_orders_arr.Friday.canceled_order],

          ['Saturday',retailer_orders_arr.Saturday.completed_order,retailer_orders_arr.Saturday.pending_order,         retailer_orders_arr.Saturday.canceled_order],

          ['Sunday',retailer_orders_arr.Sunday.completed_order,retailer_orders_arr.Sunday.pending_order,         retailer_orders_arr.Sunday.canceled_order]

        ]);

        var options = {
            title : 'Last week orders',
           
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {3: {type: 'line'}},
            vAxis: {
                        viewWindow: {
                            min: 100,
                            max: 1000

                        },
                        title: 'Order Amount($)',
                        ticks: [100,200,300,400,500,600,700,800,900,1000] 
                    },
            colors: ['#ffb6c1','#b19cd9','#ec9787']        
                  
        };

        var chart = new google.visualization.ComboChart(document.getElementById('chart_div3'));

        chart.draw(data, options);


       // last month order report retailer order

         if(month_name == 'January')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',retailer_order_data.January.completed_order,retailer_order_data.January.pending_order,         retailer_order_data.January.canceled_order],

            ['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],

            ['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);
        }
        else if(month_name == 'February'){
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',retailer_order_data.February.completed_order,retailer_order_data.February.pending_order,retailer_order_data.February.canceled_order],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);
        }
        else if(month_name == 'March')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',retailer_order_data.March.completed_order,retailer_order_data.March.pending_order,retailer_order_data.March.canceled_order],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]); 
        }
        else if(month_name == 'April')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',retailer_order_data.April.completed_order,retailer_order_data.April.pending_order,retailer_order_data.April.canceled_order],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'May')
        {
           
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',retailer_order_data.May.completed_order,retailer_order_data.May.pending_order,retailer_order_data.May.canceled_order],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'June')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',retailer_order_data.Jun.completed_order,retailer_order_data.Jun.pending_order,retailer_order_data.Jun.canceled_order],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'July'){

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',retailer_order_data.July.completed_order,retailer_order_data.July.pending_order,retailer_order_data.July.canceled_order],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);  
        }

        else if(month_name == 'August'){

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',retailer_order_data.August.completed_order,retailer_order_data.August.pending_order,retailer_order_data.August.canceled_order],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'September')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',retailer_order_data.September.completed_order,retailer_order_data.September.pending_order,retailer_order_data.September.canceled_order],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);    
        }
        else if(month_name == 'October')
        {

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',retailer_order_data.October.completed_order,retailer_order_data.October.pending_order,retailer_order_data.October.canceled_order],['Nov',0,0,0],['Dec',0,0,0]

            ]);    
        }

        else if(month_name == 'November')
        {
           
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',retailer_order_data.November.completed_order,retailer_order_data.November.pending_order,retailer_order_data.November.canceled_order],['Dec',0,0,0]

            ]); 
        }
        else if(month_name == 'December')
        {
          
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',retailer_order_data.December.completed_order,retailer_order_data.December.pending_order,retailer_order_data.December.canceled_order]

            ]); 

        }


        var options = {
            title : 'Last month orders',
           
            hAxis: {title: 'Months'},
            seriesType: 'bars',
            series: {3: {type: 'line'}},
            vAxis: {
                    viewWindow: {
                        min: 100,
                        max: 1000
                    },
                    title: 'Order Amount($)',
                    ticks: [100,200,300,400,500,600,700,800,900,1000] 
                },
            colors: ['#ffb6c1','#b19cd9','#ec9787']    
                       
        };

        var chart = new google.visualization.ComboChart(document.getElementById('chart_div4'));

        chart.draw(data, options);

        var visualization = new google.visualization.ComboChart(container);
        google.charts.load("current", {packages: ["corechart"]});
               
    }

   /*--------------------------------------------------------------------------*/




    /*--------------rep/sales order------------------------------*/

     //pie chart for last 7 days
    google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart()
    {
       
       //  last 7 days 
        if(orders_data_arr.completed_order!=0 || orders_data_arr.pending_order!=0 || orders_data_arr.canceled_order!=0)
        {
          
               var data = google.visualization.arrayToDataTable([
                ['Status', 'Order Count'],
                ['Completed', orders_data_arr.completed_order],
                ['Pending',  orders_data_arr.pending_order],
                ['Cancelled', orders_data_arr.canceled_order],
                  
            ]);

            var options = {
                legend: 'none',
                pieSliceText: 'label',
               // title: 'Orders report last 7 days',
                pieStartAngle:100,
                slices: [{color: 'ffb6c1'}, {color: '#b19cd9'}, {color: '#ec9787'}]
            };

            var chart = new google.visualization.PieChart(document.getElementById('piechart'));
            chart.draw(data, options);
        }
        else
        {
            $('#div_piechart').hide();
        }
        

         //last 30 days
       
        if(leads_data.completed_order!=0 || leads_data.pending_order!=0 || leads_data.canceled_order!=0)
        {

                var data = google.visualization.arrayToDataTable([
                ['Status', 'Order Count'],
                ['Completed', leads_data.completed_order],
                ['Pending', leads_data.pending_order],
                ['Cancelled', leads_data.canceled_order],
                  
            ]);

            var options = {
                legend: 'none',
                pieSliceText: 'label',
              //  title: 'Orders report last 30 days',
                pieStartAngle:100,
                slices: [{color: 'ffb6c1'}, {color: '#b19cd9'}, {color: '#ec9787'}]
            };

          
            var chart = new google.visualization.PieChart(document.getElementById('piechart1'));
            chart.draw(data, options);      

        }
        else
        {
            $('#div_piechart1').hide();
        }

        
        //------------------my order report------------------------------

       //  last 7 days report
        if(quotes_data.completed_order!=0 || quotes_data.pending_order!=0 || quotes_data.canceled_order!=0)
        {
             
                var data = google.visualization.arrayToDataTable([
                    ['Status', 'Order Count'],
                    ['Completed', quotes_data.completed_order],
                    ['Pending',  quotes_data.pending_order],
                    ['Cancelled', quotes_data.canceled_order],
                      
                ]);

                var options = {
                    legend: 'none',
                    pieSliceText: 'label',
                    //title: 'Orders report last 7 days',
                    pieStartAngle:100,
                    slices: [{color: 'ffb6c1'}, {color: '#b19cd9'}, {color: '#ec9787'}]
                };

                var chart = new google.visualization.PieChart(document.getElementById('piechart2'));
                chart.draw(data, options);
        }
        else
        {
            $('#div_piechart2').hide();
        }
            

        // last 30 days report
           
        if(quotes_arr.completed_order!=0 || quotes_arr.pending_order!=0 || quotes_arr.canceled_order!=0)
        {

            var data = google.visualization.arrayToDataTable([
                    ['Status', 'Order Count'],
                    ['Completed', quotes_arr.completed_order],
                    ['Pending', quotes_arr.pending_order],
                    ['Cancelled', quotes_arr.canceled_order],
                  
            ]);

            var options = {
                    legend: 'none',
                    pieSliceText: 'label',
                   // title: 'Orders report last 30 days',
                    pieStartAngle:100,
                    slices: [{color: 'ffb6c1'}, {color: '#b19cd9'}, {color: '#ec9787'}]
            };

              
            var chart = new google.visualization.PieChart(document.getElementById('piechart3'));
            chart.draw(data, options);      

        }
        else
        {
            $('#div_piechart3').hide();
        }
            
      
    }
   </script>
   
@stop                    