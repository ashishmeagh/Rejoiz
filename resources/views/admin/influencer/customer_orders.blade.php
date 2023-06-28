@extends('admin.layout.master')  
@section('main_content')
<style type="text/css">
.table tbody tr td .table{box-shadow: -2px 6px 14px -5px #b9b9b9;margin-top: 10px;}
</style>
<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-8 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li><a href="{{ $module_url_path }}">Influencers</a></li>
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
                  
              <div class="table-responsive">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Order No.</th> 
                           <th> Order Date</th>                          
                           <th> Customer</th>
                           <th> Vendor</th>
                           <th> Products</th>
                           <th> Total Amount </th>
                           <th> Promo code </th>
                           <th> Shipping Status</th>
                           <th> Customer Payment Status</th>
                           <th> Vendor Payment Status</th>
                           <th> Action</th>
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

<input type="hidden" name="influencer_id" id="influencer_id" value="{{ isset($influencer_id)?$influencer_id:0 }}">

<!-- /#page-wrapper -->


<script type="text/javascript">

  var module_url_path      = "{{ $module_url_path or '' }}"; 
  var customer_orders_path = "{{ $customer_orders_path or '' }}";

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
      'url': module_url_path+'/get_customer_orders_listing',
      'data': function(d)
       {        
          d['influencer_id'] = $('#influencer_id').val()

          d['column_filter[q_order_no]']      = $("input[name='q_order_no']").val()
           d['column_filter[q_enquiry_date]'] = $("input[name='q_enquiry_date']").val()
          d['column_filter[q_description]']   = $("input[name='q_description']").val()
          d['column_filter[q_customer_name]'] = $("input[name='q_customer_name']").val()
          d['column_filter[q_company_name]']  = $("input[name='q_company_name']").val()
          d['column_filter[q_total_retail_cost]'] = $("input[name='q_total_retail_cost']").val()
          // d['column_filter[q_enquiry_date]']   = $("input[name='q_enquiry_date']").val()
          d['column_filter[q_ship_status]']    = $("select[name='q_ship_status']").val()
          d['column_filter[q_payment_status]'] = $("select[name='q_payment_status']").val()
          

          d['column_filter[q_vendor_payment]'] = $("select[name='q_vendor_payment']").val()



          d['column_filter[q_from_date]']      = $("input[name='order_from_date']").val()
          d['column_filter[q_to_date]']        = $("input[name='order_to_date']").val()
          
          d['column_filter[q_promo_code]']     = $("input[name='q_promo_code']").val()
         
       }
      },

      columns: [
      {
        render(data, type, row, meta)
        {
            if(row.is_split_order == '1')
            {
              return `<a target="_blank" href="`+customer_orders_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a> <br><span class="label label-success">Split</span>`
            }
            return `<a target="_blank" href="`+customer_orders_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a>`

        },
        "orderable": false, "searchable":false
      },

      {data: 'created_at', "orderable": false, "searchable":false},      
      
      // {data: 'store_name', "orderable": false, "searchable":false}, 
      {
        render(data, type, row, meta)
        {            
            return `<a target="_blank" href="`+customer_orders_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.customer_name+`</a>`

        },
        "orderable": false, "searchable":false
      },


      // {data:'company_name',"orderable": false, "searchable":false},
      {
        render(data, type, row, meta)
        {            
            return `<a target="_blank" href="`+customer_orders_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.company_name+`</a>`

        },
        "orderable": false, "searchable":false
      },

      {data: 'product_html', "orderable": false, "searchable":false}, 

      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i> '+(+row.total_retail_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },

      {data: 'promo_code', "orderable": false, "searchable":false},    
      
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
  
      

       {data: 'vendor_payment_status', "orderable": false, "searchable":false}, 
      
      // {data: 'created_at', "orderable": false, "searchable":false},        
      {data: 'build_action_btn', "orderable": false, "searchable":false},        
      
      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });
  
 /* $('#table_module').on('draw.dt',function(event)
  {
    var oTable = $('#table_module').dataTable();
    var recordLength = oTable.fnGetData().length;
    $('#record_count').html(recordLength);  
  });*/

  /*search box*/
  $("#table_module").find("thead").append(`<tr>          
          <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

            <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>        

          <td><input type="text" name="q_customer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>   
          
          <td>
            <input type="text" name="q_company_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" />
          </td>
          <td></td>

          <td><input type="text" name="q_total_retail_cost" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

          <td><input type="text" name="q_promo_code" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_ship_status" id="q_ship_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="0">Pending</option>
                  <option value="1">Shipped</option>
                  <option value="2">Failed</option>
            </select>
          </td> 
          
            <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_payment_status" id="q_payment_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Pending</option>
                  <option value="2">Paid</option>
                  <option value="3">Failed</option>
            </select>
          </td>    


          

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_vendor_payment" id="q_vendor_payment" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Pending</option>
                  <option value="2">Paid</option>
                  <option value="3">Failed</option>
                
            </select>
          </td>     
          <td>&nbsp;</td>
         
         

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

  function show_product_list(ref)
  {
  
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
  }

</script>
@stop