@extends('maker.layout.master')  
@section('main_content')
<style type="text/css">
  .table tbody tr td .table{box-shadow: -2px 6px 14px -5px #b9b9b9;margin-top: 10px;}
  .pro-list-bg {
    position: relative;
  }
  .pro-list-bg span{
       display: inline-block;
    font-weight: 600;
    color: #333;
    width: 18px;
    height: 18px;
    text-align: center;
    background-color: #ececec;
    margin-left: 10px;
    border-radius: 50%;
    line-height: 18px;
    font-size: 10px;
  }
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-sm-12 top-bg-title">
            <h4 class="page-title">{{$module_title or ''}}</h4>
            <div class="right">
            <ol class="breadcrumb">
                <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
            @include('admin.layout._operation_status')
            <div class="white-box">

              

              <div class="butns-right">            
{{--           <form class="form-horizontal" id="report_form">
                    <div class="form-group row maker_data_filter_row">
                      <div class="maker_data_filter_input mr-2">
                        <label class="label-float" for="category">Order From Date</label>
                      <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date." />

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="maker_data_filter_input">
                      <label class="label-float" for="category">Order To Date</label>

                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date."/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                     <div class="col-sm-2 maker_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                     </div>
                </div>
               </form> --}}
                 <form class="form-horizontal" id="report_form">
                    <div class="form-group row maker_data_filter_row">
                      <div class="maker_data_filter_input mr-2">
                        <label class="label-float" for="category">Order From Date</label>
                      <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date"  readonly/>

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="maker_data_filter_input">
                      <label class="label-float" for="category">Order To Date</label>

                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date" readonly/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                     <div class="col-sm-2 maker_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                     </div>
                </div>
              </form>
         </div>       
               <div >
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Order No.</th>  
                           <th> Order Date</th>                         
                           <th> Customer</th>
                           <th> Products</th>
                           <th> Total Amount</th>
                           <th>Customer Payment Status</th>
                           <th> Shipping Status</th>
                           <th> Payment Type</th>
                           <th> Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                      <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="4" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                              <th id="total_amt"></th>                          
                              <th colspan="3"></th>                             
                            </tr>
                          </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>
<!-- /#page-wrapper -->
<script type="text/javascript"> 
  
var module_url_path  = "{{ $module_url_path or '' }}";  </script>

<script type="text/javascript">

  var table_module     = false;
  var retailer_id      = "{{$retailer_id or 0}}";

  $(document).ready(function()
  {
      // To date validation
      var dates = $("#order_from_date, #order_to_date").datepicker({
          dateFormat: 'mm-dd-yy',
          numberOfMonths: 1,
          maxDate:'+0d',
          onSelect: function(date) {
              for(var i = 0; i < dates.length; ++i) {
                  if(dates[i].id < this.id)
                      $(dates[i]).datepicker('option', 'maxDate', date);
                  else if(dates[i].id > this.id)
                      $(dates[i]).datepicker('option', 'minDate', date);
              }
          } 
      });

      $( function() {
        $( ".datepicker" ).datepicker();
        
        $('#order_from_date').datepicker('setEndDate', '+0d');
        $('#order_to_date').datepicker('setEndDate', '+0d');

        $("#order_from_date").datepicker({
          
          todayBtn:  1,
          autoclose: true,

        }).on('changeDate', function (selected) {
          var minDate = new Date(selected.date.valueOf());
          $('#order_to_date').datepicker('setStartDate', minDate);
        });
      
        $("#order_to_date").datepicker()
          .on('changeDate', function (selected) {
              var minDate = new Date(selected.date.valueOf());
              $('#order_from_date').datepicker('setEndDate', minDate);
          });
    
        });

         $('#search').click(function(){
          if($('#report_form').parsley().validate()==false) return;
          else
            filterData();
       }); 

      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      bFilter: false,
      "order":[4,'Asc'],

      ajax: {
      'url': module_url_path+'/get_enquiries',
      'data': function(d)
       {        
          d['column_filter[q_enquiry_id]']     = $("input[name='q_enquiry_id']").val()         
          d['column_filter[q_retailer_name]']  = $("input[name='q_retailer_name']").val()
          d['column_filter[q_total_wholesale_cost]'] = $("input[name='q_total_wholesale_cost']").val()
          d['column_filter[q_ship_status]']     = $("select[name='q_ship_status']").val()
          d['column_filter[q_payment_status]']  = $("select[name='q_payment_status']").val()
          d['column_filter[q_enquiry_date]']    = $("input[name='q_enquiry_date']").val()
          d['column_filter[retailer_id]']       = retailer_id;

          d['column_filter[q_order_from_date]']     = $("#order_from_date").val();
          d['column_filter[q_order_to_date]']       = $("#order_to_date").val();
          d['column_filter[q_payment_type]']       = $("#payment_type").val();

       }
      },
       drawCallback:function(settings)
      {       
       $("#total_amt").html("$ "+settings.json.total_amt.toFixed(2));
      },

      columns: [
      {
        render(data, type, row, meta)
        {
            //return row.order_no;
            return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a>`;
        },
        "orderable": false, "searchable":false
      },    
      {data: 'created_at', "orderable": false, "searchable":false},                         
      // {data: 'store_name', "orderable": false, "searchable":false},         
      {
        render(data, type, row, meta)
        {
            //return row.order_no;
           // return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.store_name+`</a>`;
            return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.dummy_store_name+`</a>`;
        },
        "orderable": false, "searchable":false
      },    
      {data: 'product_html', "orderable": false, "searchable":false},
    
      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i> '+(+row.total_wholesale_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },             
      

      {
         data : 'payment_status',  
         render : function(data, type, row, meta) 
         { 
           
           if(row.payment_status == 'Pending')
           {
            
             return `<span class="label label-warning">`+row.payment_status+`</span>`

           }
           else if(row.payment_status == 'Paid')
           {
             return `<span class="label label-success">`+row.payment_status+`</span>`

           }
           else if(row.payment_status == 'Failed')
           {
             return `<span class="label label-danger">`+row.payment_status+`</span>`
           }
           else
           {
              return `<span class="label label-warning">Pending</span>`
           }
         },
         "orderable": false,
         "searchable":false
       },


     {
         data : 'ship_status',  
         render : function(data, type, row, meta) 
         { 
           
           if(row.ship_status == 'Pending')
           {
            
             return `<span class="label label-warning">`+row.ship_status+`</span>`

           }
           else if(row.ship_status == 'Shipped')
           {
             return `<span class="label label-success">`+row.ship_status+`</span>`

           }
           else(row.ship_status == 'Failed')
           {
             return `<span class="label label-danger">`+row.ship_status+`</span>`
           }
         },
         "orderable": false,
         "searchable":false
       },
      
      {data: 'payment_type', "orderable": false, "searchable":false},     
      {data: 'build_action_btn', "orderable": false, "searchable":false},     
      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });


  /*search box*/
  $("#table_module").find("thead").append(`<tr>          
          <td><input type="text" name="q_enquiry_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>   

          <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>

          <td><input type="text" name="q_retailer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>   
          <td></td>      
        
          <td><input type="text" name="q_total_wholesale_cost" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>           
         
          

           <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_payment_status" id="q_payment_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Pending</option>
                  <option value="2">Paid</option>
                  <option value="3">Failed</option>
            </select>
          </td> 


           <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_ship_status" id="q_ship_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="0">Pending</option>
                  <option value="1">Shipped</option>
                  <option value="2">Failed</option>
            </select>
          </td>

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_payment_type" id="payment_type" onchange="filterData();">
                  <option value="">All</option>
                  <option value="0">In-Direct</option>
                  <option value="1">Direct</option>
            </select>
          </td>             

      </tr>`);

  $('input.column_filter').on( 'keyup click', function () 
  {
       filterData();
  });

});

function filterData()
{
  table_module.draw();
}

  
function show_product_list(ref){
    
  let tbl_id = $(ref).attr('data-tbl-id');
  let id = $('#'+tbl_id);
    
  if(id.is(":visible"))
  { 
    id.slideUp();
  }
  else
  {      
    id.slideDown();
  } 

};

</script>

@stop