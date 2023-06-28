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

  .btn.btn-circle.btn-danger.btn-outline{
  border: 1px solid #dfdfdf;
    background-color: #fff;
    color: #444;
    font-size: 14px;
    padding: 13px 30px 12px;
    border-radius: 4px;
    display: inline-block;
    height: auto;
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
              
              <div class="butns-right">            
                <div class="col-sm-12 text-right export-btn-div p-0 mb-3">
                  <button type="button" onclick="repSalesCancelOrderExport()" title="Export rep/sales cancel orders as .csv" class="btn btn-success waves-effect waves-light filter_btn" value="export" id="repSalesCancelOrderExport">Export</button>
                </div>
              <form class="form-horizontal" id="report_form">
                    <div class="form-group row retailer_data_filter_row">

                      <div class="retailer_data_filter_input mr-2">
                        <label class="label-float" for="category">Order From Date</label>
                      <input type="text" class="form-control datepicker input-float order_from_date" name="order_from_date" id="order_from_date_new" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date"/>

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="retailer_data_filter_input">
                      <label class="label-float" for="category">Order To Date</label>

                        <input type="text" class="form-control datepicker input-float order_to_date" name="order_to_date" id="order_to_date_new" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date"/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                     <div class="col-sm-2 retailer_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-info btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                     </div>
              </div>
            </form>
         </div> 
               <div>
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th>Order No.</th>
                           <th>Order Date</th>
                           <th>Vendor</th>
                           <th>Rep/Sales</th>
                           <th>Products</th>
                           <th>Total Amount</th>
                           <th>Payment Status</th>
                           <th>Refund Status</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                      <tfoot style="background-color: #f1f1f1">
                          <tr style="vertical-align: middle;">
                              <th colspan="4" align="right"> &nbsp;</th>
                              <th style="text-align: right;"> Total: &nbsp;</th>
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

<script type="text/javascript"> var module_url_path  = "{{ $module_url_path or '' }}";  </script>

<script type="text/javascript">
  var table_module     = false;
  var retailer_id      = "{{$retailer_id or 0}}";
  var module_url_path  = "{{$module_url_path or ''}}";
 

  $(document).ready(function()
  {  

       // To date validation
      $( function() {
        $( ".datepicker" ).datepicker();
        
        $('#order_from_date_new').datepicker('setEndDate', '+0d');
        $('#order_to_date_new').datepicker('setEndDate', '+0d');

        $("#order_from_date_new").datepicker({
          
          todayBtn:  1,
          autoclose: true,

        }).on('changeDate', function (selected) {
          var minDate = new Date(selected.date.valueOf());
          $('#order_to_date_new').datepicker('setStartDate', minDate);
        });
      
        $("#order_to_date_new").datepicker()
          .on('changeDate', function (selected) {
              var minDate = new Date(selected.date.valueOf());
              $('#order_from_date_new').datepicker('setEndDate', minDate);
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
      'url': module_url_path+'/get_my_orders',
      'data': function(d)
       {  
        d['column_filter[q_order_no]']        = $("input[name='q_order_no']").val()
        d['column_filter[q_retailer_name]']   = $("input[name='q_retailer_name']").val()
        d['column_filter[q_enquiry_date]']    = $("input[name='q_enquiry_date']").val()
        d['column_filter[q_total_wholesale_cost]']    = $("input[name='q_total_wholesale_cost']").val()
        d['column_filter[q_payment_status]']  = $("select[name='q_payment_status']").val()
        d['column_filter[q_company_name]']    = $("input[name='q_company_name']").val()
        d['column_filter[q_rep_sales_name]']  = $("input[name='q_rep_sales_name']").val()
        d['column_filter[q_refund_status]']  = $("select[name='q_refund_status']").val()
        d['column_filter[q_from_date]']  = $("#order_from_date_new").val()
        d['column_filter[q_to_date]']  = $("#order_to_date_new").val()
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
            return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`">`+row.order_no+`</a>`;
        },
        "orderable": false, "searchable":false
      }, 
      {data: 'created_at', "orderable": false, "searchable":false},         
      // {data: 'company_name', "orderable": false, "searchable":false},                
      {
        render(data, type, row, meta)
        {
            return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.company_name+`</a>`;
        },
        "orderable": false, "searchable":false
      }, 

      {data: 'order_by', "orderable": false, "searchable":false},  
    
      {data: 'product_html', "orderable": false, "searchable":false}, 
      
      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.total_wholesale_price).toFixed(2);
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
              if(row.payment_status == 'Paid')
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
  
      
      {data: 'refund_status', "orderable": false, "searchable":false},        
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


          <td><input type="text" name="q_company_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

          <td>
          <input type="text" name="q_rep_sales_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" />
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
            <select class="search-block-new-table column_filter form-control-small" name="q_refund_status" id="q_refund_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="0">Pending</option>
                  <option value="1">Paid</option>
            </select>
          </td>

          <td></td>        

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


  function cancelOrder(ref)
  {
      var order_id = $(ref).attr('data-order-id');
      order_id = btoa(order_id);
   
      swal({
        title:'Need Confirmation',
        text: "Are you sure? Do you want to cancel this order.",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: true
      },
      function(isConfirm) 
      {
        if (isConfirm) 
        {
              $.ajax({
                url:module_url_path+'/cancel',
                method:'GET',
                data:{order_id:order_id},
                beforeSend : function()
                {
                  showProcessingOverlay();
                 
                },
                success:function(response)
                {
                    hideProcessingOverlay();
                   
                   // swal(response.status,response.description,response.status);

                    swal({
                    title:'Success',
                    text: response.description,
                    type: response.status,
                    showCancelButton: false,
                    confirmButtonClass: "btn-success",
                    confirmButtonText: "OK",
                    cancelButtonText: "Cancel",
                    closeOnConfirm: true,
                    closeOnCancel: true
                  },
                  function(isConfirm) 
                  {
                    location.reload(true);
                  })
                }
             });
          }
       
      });

}

const saveData = (() => {
    const a = document.createElement('a');
    a.style = 'display: none';
    document.body.appendChild(a);

    return (data, fileName, type = 'application/csv') => {
      const blob = new Blob([data], { type });

      if (navigator.msSaveBlob) {
        return navigator.msSaveBlob(blob, fileName);
      }

      const url = URL.createObjectURL(blob);
      a.href = url;
      a.download = fileName;
      a.click();
      URL.revokeObjectURL(url);
      return true;
    };
  })();

function repSalesCancelOrderExport(){

        var q_order_no             = $("input[name='q_order_no']").val();
        var q_retailer_name         = $("input[name='q_retailer_name']").val()
        var q_company_name         = $("input[name='q_company_name']").val()
        var q_rep_sales_name         = $("input[name='q_rep_sales_name']").val()
        var q_total_wholesale_cost = $("input[name='q_total_wholesale_cost']").val();
        var q_payment_status       = $("select[name='q_payment_status']").val()
        var q_refund_status       = $("select[name='q_refund_status']").val()
        var q_enquiry_date         = $("input[name='q_enquiry_date']").val()
        var q_from_date            = $("#order_from_date_new").val()
        var q_to_date              = $("#order_to_date_new").val()

    $.ajax({
          url: module_url_path+'/get_export_rep_sales_cancel_orders',
          data: {q_order_no:q_order_no,q_retailer_name:q_retailer_name,q_company_name:q_company_name,q_rep_sales_name:q_rep_sales_name,q_total_wholesale_cost:q_total_wholesale_cost,q_payment_status:q_payment_status,q_refund_status:q_refund_status,q_enquiry_date:q_enquiry_date,q_from_date:q_from_date,q_to_date:q_to_date},
        
          type:"get",
          beforeSend: function() 
          {
             showProcessingOverlay();                
          },
          success:function(data)
          {
            hideProcessingOverlay();
            if(data.status != null && data.status == 'error')
            {
              swal('Error',data.message,'error');
            }
            else
            {
              saveData(data, 'rep_sale_cancel_orders.csv');
            }
          }
        });
 }

</script>

@stop