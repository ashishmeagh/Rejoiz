@extends('admin.layout.master')  
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

  th {
    white-space: nowrap;
}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
           <div class="white-box">
            @include('admin.layout._operation_status')
           
              <div class="pull-right top_small_icon">            
                 <form class="form-horizontal" id="report_form">
              
                    <div class="form-group row">
                     
                    <div class="col-6">
                    <div class="floatmain-div">
                     <label class="label-float" for="category">Order Date</label>
                      <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="From Date"
                      data-parsley-required="true"  data-date-format='yyyy-mm-dd' readonly/>

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                    </div>
                    <div class="col-3">
                      <input type="text" class="form-control datepicker" name="order_to_date" id="order_to_date" placeholder="To Date" data-parsley-required="true"  data-date-format='yyyy-mm-dd' readonly/>

                      <span id="to_date_error" class="red"></span>
                    </div>
            
                    <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip space-btn-cirlce" title="Search"><i class="fa fa-search" onclick="validateField();filterData();"></i> </a>

                    <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip space-btn-cirlce" title="Export as a xlsx" id="generate_report"><i class="fa fa-file-excel-o"></i> </a>

                    <a href="{{$module_url_path or ''}}" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
              </div>
          </form>

   
         </div> 
        <div >

                              
              </div> 
              <div class="table-responsive">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Order No.</th> 
                           <th> Order Date</th>                          
                           <th> Store Name</th>
                           <th> Vendor Name</th>
                           <th> Total Amount </th>
                           <th> Payment Status</th>
                           <th> Shipping Status</th>
                           <th> Details</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
                  </div>
            </div>
             </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>

<input type="hidden" name="csrfToken" id="csrfToken" value="{{csrf_token()}}">
    
<!-- /#page-wrapper -->
<script type="text/javascript"> 
  var url_path  = "{{ url('admin/retailer_orders/get_enquiries') }}"; 
  var module_url_path = "{{url('admin/retailer_orders')}}"  ;
 </script>

<script type="text/javascript">

  var table_module = false;

  $(document).ready(function()
  {
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
      'url': url_path,
      'data': function(d)
       {        
          d['column_filter[q_order_no]']      = $("input[name='q_order_no']").val()
           d['column_filter[q_enquiry_date]']  = $("input[name='q_enquiry_date']").val()
          d['column_filter[q_retailer_name]'] = $("input[name='q_retailer_name']").val()
          d['column_filter[q_company_name]']  = $("input[name='q_company_name']").val()
          d['column_filter[q_total_wholesale_cost]'] = $("input[name='q_total_wholesale_cost']").val()
          d['column_filter[q_ship_status]']    = $("select[name='q_ship_status']").val()
          d['column_filter[q_payment_status]'] = $("select[name='q_payment_status']").val()
/*
          d['column_filter[q_from_date]']      = $("input[name='order_from_date']").val()
          d['column_filter[q_to_date]']        = $("input[name='order_to_date']").val()*/
          
         
       }
      },

      columns: [
      {
        render(data, type, row, meta)
        {
            //return row.order_no;
            return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a>`

        },
        "orderable": false, "searchable":false
      },

      {data: 'created_at', "orderable": false, "searchable":false},      
      
      {data: 'store_name', "orderable": false, "searchable":false}, 


      {data:'company_name',"orderable": false, "searchable":false},

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
            
             return `<span class="label label-warning">Pending</span>`

           }
           else if(row.payment_status == 'Paid')
           {
             return `<span class="label label-success">Paid</span>`

           }
           else if(row.payment_status == 'Failed')
           {
             return `<span class="label label-danger">Failed</span>`
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
           
           if(row.ship_status == '0')
           {
            
             return `<span class="label label-warning">Pending</span>`

           }
           else if(row.ship_status == '1')
           {
             return `<span class="label label-success">Shipped</span>`

           }
           else(row.ship_status == '2')
           {
             return `<span class="label label-danger">Failed</span>`
           }
         },
         "orderable": false,
         "searchable":false
       },
      
      // {data: 'created_at', "orderable": false, "searchable":false},        
      {data: 'build_action_btn', "orderable": false, "searchable":false},        
      
      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });

  /*search box*/
  $("#table_module").find("thead").append(`<tr>          
          <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

            <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>        

          <td><input type="text" name="q_retailer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>   
          
          <td>
            <input type="text" name="q_company_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" />
          </td>
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


</script>

@stop