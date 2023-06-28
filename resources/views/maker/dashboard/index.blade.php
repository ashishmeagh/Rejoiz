@extends('maker.layout.master')  
@section('main_content')
<!-- Page Content -->
       
 @include('maker.layout._operation_status')  

 <div id="page-wrapper" class="dashboard_page_main_div ">
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
            
                <div class="row row_dash_box">
                    <div class="col-sm-12 col-md-12 col-lg-12 text-right dashboard-note ">
                        <p>Excluded Split Orders</p>
                    </div>
                    <div class="col-lg-4 col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/vendor/products">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-product.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ isset($arr_count['product_count'])?number_format($arr_count['product_count']):0  }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Total Products</h3>
                            </div>
                        </div>
                        </a>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/vendor/retailer_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ isset($arr_count['sales_order_count'])?number_format($arr_count['sales_order_count']):0 }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <!-- <h3 class="box-title">Total Sales Orders</h3> -->
                                <h3 class="box-title">Total Customer Orders
                                   {{--  <label class="shippingLabel">Excluded Split orders</label> --}}
                                </h3>
                            </div>
                        </div>
                        </a>
                    </div>

                    <div class="col-lg-4 col-sm-6 col-xs-12 dash_box">
                        <a href="{{url('/')}}/vendor/representative_orders">
                        <div class="white-box">
                            <div class="content">
                                <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ isset($arr_count['reps_order_count'])?number_format($arr_count['reps_order_count']):0 }}</span></li>
                            </ul>
                            </div>
                            <div class="dash-footer">
                                <h3 class="box-title">Total Orders by Representative / Sales Manager
                               {{--  <label class="shippingLabel">Excluded Split orders</label> --}} </h3>
                            </div>
                        </div>
                        </a>
                    </div>

                    {{--  <div class="col-md-12">
                        <h3 class="mt-3 mb-3 text-left">Payment Transaction details</h3>
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
                    </div> --}}

                   {{--   <div class="col-md-12" id="div_bar_chart">        
                        <div id="chart_div1" style="width: 100%; height: 500px;"></div>
                    </div>
                           
                    <div class="col-md-12" id="div_bar_chart1">
                        <div id="chart_div2" style="width: 100%; height: 500px;"></div>
                    </div> --}}

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

    var orderAmountData = <?php echo json_encode($orderAmountData); ?>;


       
    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawVisualization);

    function drawVisualization() 
    {
       // last week order report
        var data = google.visualization.arrayToDataTable([
          ['','Completed', 'Pending', 'Cancelled'],

          ['Monday',120,140,160],

          // ['Tuesday',order_data.Tuesday.completed_order,order_data.Tuesday.pending_order,         order_data.Tuesday.canceled_order],
          ['Tuesday',120,140,160],

          ['Wednesday',120,140,160],

          ['Thursday',120,140,160],

          ['Friday',120,140,160],

          ['Saturday',120,140,160],

          ['Sunday',120,140,160]

        ]);

        var options = {
            title : 'Vendor To Admin Transactions',
           
            hAxis: {title: 'Week'},
            seriesType: 'bars',
            series: {3: {type: 'line'}},
            vAxis: {
                        viewWindow: {
                            min: 100,
                            max: 1000

                        },
                        title: 'Amount($)',
                        ticks: [100,200,300,400,500,600,700,800,900,1000] 
                    },
            colors: ['#ffb6c1','#b19cd9','#ec9787']        
                  
        };

        var chart = new google.visualization.ComboChart(document.getElementById('chart_div1'));

        chart.draw(data, options);

       
       // last month order report

        if(month_name == 'January')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',order_month_data.January.completed_order,order_month_data.January.pending_order,         order_month_data.January.canceled_order],
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

        var visualization = new google.visualization.ComboChart(container);
        google.charts.load("current", {packages: ["corechart"]});
               
    }

        /*--------------rep/sales order------------------------------*/

     //pie chart for last 7 days
    google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart()
    {
       //  last 7 days 
        if(orderAmountData.pending_amount!=0 || orderAmountData.collected_amount!=0 )
        {
          
               var data = google.visualization.arrayToDataTable([
                ['Status', 'Order Count'],
                ['Pending', orderAmountData.pending_amount],
                ['Completed',  orderAmountData.collected_amount],
                // ['Cancelled', 300],
                  
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
        

       

    
            
      
    }

      
   </script>
@stop                    