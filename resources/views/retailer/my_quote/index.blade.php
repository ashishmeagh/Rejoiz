@extends('retailer.layout.master')  
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
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
              <li><a href="{{ url(config('app.project.retailer_panel_slug').'/dashboard') }}">Dashboard</a></li>
              <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
        
         <div class="col-lg-12">
            @include('admin.layout._operation_status')
            <div class="white-box">
              <div class="pull-right">            
                <a href="{{$module_url_path}}" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>               
              </div> 
               <div >
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Enquiry ID</th>
                           <th> Maker Name</th>
                           <th>Brand Name/Maker</th>
                           <th> Products</th>
                           <th> Total Costing(Retail)</th>
                           <th> Total Amount (Wholesale)</th>
                           <th> Order Date</th>
                           <th> Details</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>
<!-- /#page-wrapper -->
<script type="text/javascript"> var module_url_path  = "{{ $module_url_path or '' }}";  </script>

<script type="text/javascript">
  var table_module = false;
  var retailer_id      = "{{$retailer_id or 0}}";

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
      'url': module_url_path+'/get_my_quote',
      'data': function(d)
       {        
          d['column_filter[q_enquiry_id]']    = $("input[name='q_enquiry_id']").val()
          // d['column_filter[q_description]']   = $("input[name='q_description']").val()
          d['column_filter[q_retailer_name]'] = $("input[name='q_retailer_name']").val()
          d['column_filter[q_brand_name]']    = $("input[name='q_brand_name']").val()
          d['column_filter[q_total_retail_cost]'] = $("input[name='q_total_retail_cost']").val()
          d['column_filter[q_total_wholesale_cost]'] = $("input[name='q_total_wholesale_cost']").val()
          d['column_filter[q_enquiry_date]']  = $("input[name='q_enquiry_date']").val()
          d['column_filter[retailer_id]']  = retailer_id;
       }
      },

      columns: [
      {
        render(data, type, row, meta)
        {

             return row.id;
        },
        "orderable": false, "searchable":false
      },  
      // {data: 'description', "orderable": false, "searchable":false},                       
      {data: 'user_name', "orderable": false, "searchable":false}, 
      {data: 'brand_name', "orderable": false, "searchable":false}, 
      {data: 'product_html', "orderable": false, "searchable":false}, 

      // {
      //   render(data, type, row, meta)
      //   {
      //        return '<i class="fa fa-dollar"></i>'+row.total_retail_price;
      //   },
      //   "orderable": false, "searchable":false
      // },
       {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.total_retail_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },
      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.total_wholesale_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },             
      {data: 'created_at', "orderable": false, "searchable":false},        
      {data: 'build_action_btn', "orderable": false, "searchable":false},        
         
      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });

  // $('#table_module').on('draw.dt',function(event)
  // {
  //   var oTable = $('#table_module').dataTable();
  //   var recordLength = oTable.fnGetData().length;
  //   $('#record_count').html(recordLength);  
  // });

  /*search box*/
  $("#table_module").find("thead").append(`<tr>          
          <td><input type="text" name="q_enquiry_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>             
          <td><input type="text" name="q_retailer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>        
          <td><input type="text" name="q_brand_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>        
          <td></td> 
          <td><input type="text" name="q_total_retail_cost" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                 
          <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>         

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