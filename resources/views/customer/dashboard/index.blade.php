@extends('customer.layout.master')  
@section('main_content')
<!-- Page Content -->
   
@include('customer.layout._operation_status')  

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
        
        <div class="row rw-sales-manager-dashboard-row"> 
                    {{-- <div class="col-sm-12 col-md-12 col-lg-12 text-right dashboard-note ">
                        <p>Excluded Split Orders</p>
                    </div>        --}}                

            <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
                <a href="{{url('/')}}/customer/my_orders">
                    <div class="white-box">
                        <div class="content">
                            <ul class="list-inline two-part">
                            <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order.png')}}"></li>
                            <li class="text-right"><span class="counter">{{ isset($arr_count['quote_count'])?number_format($arr_count['quote_count']):0 }}</span></li>
                        </ul>
                        </div>
                        <div class="dash-footer">
                            <h3 class="box-title">Total Orders{{-- <label class="shippingLabel">Excluded Split orders</label> --}}</h3>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
                <a href="{{url('/')}}/customer/my_orders/my_pending_orders">
                    <div class="white-box">
                        <div class="content">
                            <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ isset($arr_count['pending_quote_count'])?number_format($arr_count['pending_quote_count']):0 }}</span></li>
                            </ul>
                        </div>
                        <div class="dash-footer">
                            <h3 class="box-title">Total Orders Pending</h3>
                        </div>
                    </div>
                </a>
            </div>


            <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
                    <a href="{{url('/')}}/customer/my_orders/my_completed_orders">
                    <div class="white-box">
                        <div class="content">
                            <ul class="list-inline two-part">
                            <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-complete.png')}}"></li>
                            <li class="text-right"><span class="counter">{{ $arr_count['complete_quote_count'] or '0' }}</span></li>
                        </ul>
                        </div>
                        <div class="dash-footer">
                            <h3 class="box-title">My Completed Orders</h3>
                        </div>
                    </div>
                    </a>
                </div>

            <div class="col-lg-3 col-sm-6 col-xs-12 dash_box">
                <a href="{{url('/')}}/customer/my_cancel_orders">
                    <div class="white-box">
                        <div class="content">
                            <ul class="list-inline two-part">
                                <li class="icon-dashbrds"><img src="{{url('/assets/front/images/icon-retailer-order-cancle.png')}}"></li>
                                <li class="text-right"><span class="counter">{{ isset($arr_count['cancel_quote_count'])?number_format($arr_count['cancel_quote_count']):0 }}</span></li>
                            </ul>
                        </div>
                        <div class="dash-footer">
                            <h3 class="box-title">Total Orders Cancelled</h3>
                        </div>
                    </div>
                </a>
            </div>  


           <div class="col-sm-12">
            <h3 class="mt-3 mb-3 text-left">My orders</h3>
            <div class="row" style="overflow:hidden;">
                <div class="col-md-6 text-center" id="div_piechart2">
                    <div class="white-box">
                      <h5 class="text-left">Orders in last 7 days</h5>
                      <div id="piechart2" style="width: 100%; height: 300px;"></div>
                    </div>
                </div>
    
            <div class="col-md-6 text-center" id="div_piechart3">
                <div class="white-box">
                    <h5 class="text-left">Orders in last 30 days</h5>
                   <div id="piechart3" style="width: 100%; height: 300px;"></div>
               </div>
            </div> 
            </div>
         </div>   

          <div class="col-md-12" id="div_bar_chart">        
            <div id="chart_div1" style="width: 100%; height: 500px;"></div>
          </div>  

         <div class="col-md-12" id="div_bar_chart1">
            <div id="chart_div2" style="width: 100%; height: 500px;"></div>
        </div>     
    

        </div>
    </div>
    <!-- /#page-wrapper -->
</div>

<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

 <script type="text/javascript">

    var sevenDaysOrderCount = <?php echo json_encode($sevenDaysOrderCount); ?>;
    var thirtyDaysOrderCount = <?php echo json_encode($thirtyDaysOrderCount); ?>;
    var lastWeekOrders = <?php echo json_encode($lastWeekOrders); ?>;
    var lastMonthOrders = <?php echo json_encode($lastMonthOrders); ?>;
    var month_name = '{{$previous_month_name or ''}}';

    google.charts.load('current', {'packages':['corechart']});
    google.charts.setOnLoadCallback(drawVisualization);

    function drawVisualization() 
    {
       // last week order report
        var data = google.visualization.arrayToDataTable([
          ['','Completed', 'Pending', 'Cancelled'],

          ['Monday',lastWeekOrders.Monday.completed_order,lastWeekOrders.Monday.pending_order,         lastWeekOrders.Monday.canceled_order],

          // ['Tuesday',order_data.Tuesday.completed_order,order_data.Tuesday.pending_order,         order_data.Tuesday.canceled_order],
          ['Tuesday',lastWeekOrders.Tuesday.completed_order,lastWeekOrders.Tuesday.pending_order,         lastWeekOrders.Tuesday.canceled_order],

          ['Wednesday',lastWeekOrders.Wednesday.completed_order,lastWeekOrders.Wednesday.pending_order,         lastWeekOrders.Wednesday.canceled_order],

          ['Thursday',lastWeekOrders.Thursday.completed_order,lastWeekOrders.Thursday.pending_order,         lastWeekOrders.Thursday.canceled_order],

          ['Friday',lastWeekOrders.Friday.completed_order,lastWeekOrders.Friday.pending_order,         lastWeekOrders.Friday.canceled_order],

          ['Saturday',lastWeekOrders.Saturday.completed_order,lastWeekOrders.Saturday.pending_order,         lastWeekOrders.Saturday.canceled_order],

          ['Sunday',lastWeekOrders.Sunday.completed_order,lastWeekOrders.Sunday.pending_order,         lastWeekOrders.Sunday.canceled_order]

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
              
            // ['Jan',order_month_data.January.completed_order,order_month_data.January.pending_order,         order_month_data.January.canceled_order],
            ['Jan',lastMonthOrders.January.completed_order,lastMonthOrders.January.pending_order,         lastMonthOrders.January.canceled_order],

            ['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],

            ['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);
        }
        else if(month_name == 'February'){
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',lastMonthOrders.February.completed_order,lastMonthOrders.February.pending_order,         lastMonthOrders.February.canceled_order],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);
        }
        else if(month_name == 'March')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',lastMonthOrders.March.completed_order,lastMonthOrders.March.pending_order,         lastMonthOrders.March.canceled_order],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]); 
        }
        else if(month_name == 'April')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',lastMonthOrders.April.completed_order,lastMonthOrders.April.pending_order,         lastMonthOrders.April.canceled_order],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'May')
        {
           
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',lastMonthOrders.May.completed_order,lastMonthOrders.May.pending_order,         lastMonthOrders.May.canceled_order],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'June')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',lastMonthOrders.June.completed_order,lastMonthOrders.June.pending_order,         lastMonthOrders.June.canceled_order],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'July'){

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',lastMonthOrders.July.completed_order,lastMonthOrders.July.pending_order,         lastMonthOrders.July.canceled_order],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);  
        }

        else if(month_name == 'August'){

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',lastMonthOrders.August.completed_order,lastMonthOrders.August.pending_order,         lastMonthOrders.August.canceled_order],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);   
        }
        else if(month_name == 'September')
        {
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',lastMonthOrders.September.completed_order,lastMonthOrders.September.pending_order,         lastMonthOrders.September.canceled_order],['Oct',0,0,0],['Nov',0,0,0],['Dec',0,0,0]

            ]);    
        }
        else if(month_name == 'October')
        {

            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',lastMonthOrders.October.completed_order,lastMonthOrders.October.pending_order,         lastMonthOrders.October.canceled_order],['Nov',0,0,0],['Dec',0,0,0]

            ]);    
        }

        else if(month_name == 'November')
        {
           
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',lastMonthOrders.November.completed_order,lastMonthOrders.November.pending_order,         lastMonthOrders.November.canceled_order],['Dec',0,0,0]

            ]); 
        }
        else if(month_name == 'December')
        {
          
            var data = google.visualization.arrayToDataTable([
            ['','Completed', 'Pending', 'Cancelled'],
              
            ['Jan',0,0,0],['Feb',0,0,0],['March',0,0,0],['April',0,0,0],['May',0,0,0],['Jun',0,0,0],['July',0,0,0],['Aug',0,0,0],['Sept',0,0,0],['Oct',0,0,0],['Nov',0,0,0],['Dec',lastMonthOrders.December.completed_order,lastMonthOrders.December.pending_order,         lastMonthOrders.December.canceled_order]

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
   /*--------------------------------------------------------------------------*/
     //pie chart for last 7 days
    google.charts.load("current", {packages:["corechart"]});
    google.charts.setOnLoadCallback(drawChart);

    function drawChart()
    {
    
       //  last 7 days report
        if(sevenDaysOrderCount.completed_order!=0 || sevenDaysOrderCount.pending_order!=0 || sevenDaysOrderCount.canceled_order!=0)
        {

            // console.log(sevenDaysOrderCount.completed_order,sevenDaysOrderCount.pending_order,sevenDaysOrderCount.canceled_order);
             
                var data = google.visualization.arrayToDataTable([
                    ['Status', 'Order Count'],
                    ['Completed', sevenDaysOrderCount.completed_order],
                    ['Pending',  sevenDaysOrderCount.pending_order],
                    ['Cancelled', sevenDaysOrderCount.canceled_order]
                      
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
           
        if(thirtyDaysOrderCount.completed_order!=0 || thirtyDaysOrderCount.pending_order!=0 || thirtyDaysOrderCount.canceled_order!=0)
        {

            var data = google.visualization.arrayToDataTable([
                    ['Status', 'Order Count'],
                    ['Completed', thirtyDaysOrderCount.completed_order],
                    ['Pending',  thirtyDaysOrderCount.pending_order],
                    ['Cancelled', thirtyDaysOrderCount.canceled_order]
                  
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