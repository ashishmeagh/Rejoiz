@extends('sales_manager.layout.master')  
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

  .table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #666;
    background-color: transparent;
    color: #666;
}

th {
    white-space: nowrap;
}

.dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
}

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}
</style>
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">

<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title}}</h4>
         </div>
          <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
            <ol class="breadcrumb">
               <li><a href="{{url('/')}}/sales_manager/dashboard">Dashboard</a></li>
                <li class="active">{{$module_title or ''}}</li>
            </ol>
        </div>

         
         <!-- /.col-lg-12 -->
      </div>
      @include('sales_manager.layout._operation_status')
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">

            <div class="white-box myorders"> 
                <div class="butns-right">            
               <form class="form-horizontal" id="report_form">
                    <div class="form-group row rep_data_filter_row">
                      <div class="rep_data_filter_input mr-2">
                        <label class="label-float" for="category">Order From Date</label>
                      <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date" />

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="rep_data_filter_input mr-2">
                      <label class="label-float" for="category">Order To Date</label>

                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date"/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                    <!-- <div class="admin_data_filter_input">
                      <label class="label-float" for="category">Search By Reps/Sales</label>

                        <input type="text" class="form-control" name="order_rep_sales_name" id="order_rep_sales_name" placeholder="Enter Reps/Sales Name"/>

                     </div> -->
                     <div class="col-sm-2 rep_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}/reps" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                     </div>
                </div>
              </form>
         </div>           
              {{--  <div class="row"></div> --}}
              <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12 text-right add_new_order_div" style="overflow:hidden;">
            <!-- <a href="{{ $module_url_path.'/create'}}"  class="btn btn-success pull-right m-l-20 btn-rounded btn-outline waves-effect waves-light mb-3">Add New Order</a>             -->
         </div>
               <div class="table-responsive">
                  <table class="table table-striped" id="table_module">
                     <thead>
                        <tr>
                           <th>Order No.</th>
                           <th>Order Date</th>                        
                           <th>Customer</th>
                           <th>Representative</th>
                           <th>Vendor</th>
                           <th>Products</th>
                           <th>Total Amount</th>
                           <th>Customer Approval</th>
                           <th>Customer Payment Status</th>
                           <th>Shipping Status</th>
                           
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                      <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="5" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                              <th id="total_amt"></th>                          
                              <th colspan="4"></th>                             
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


<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";

  function reorder(order_no){

      var order_no = order_no;

      swal({
        title: "Need Confirmation",
        text: "Are you sure? Do you want to reorder.",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "Ok",
        closeOnConfirm: false
      },
      function(){
    
         $.ajax({
           url:module_url_path+'/reorder',
           method:'GET',
           data:{'order_no':order_no},
           dataType:'JSON',
           beforeSend : function()
           {
             showProcessingOverlay();
             
           },
           success:function(response)
           {
              hideProcessingOverlay();
             
             if(typeof response =='object')
             {
               if(response.status && response.status=="success")
               {   
                swal({
                     title: "Success",
                     text: response.msg,
                     type: "success",
                     showCancelButton: false,
                     confirmButtonClass: "btn-success",
                     confirmButtonText: "OK",
                     closeOnConfirm: false,
                     closeOnCancel: false
                   },
                   function(isConfirm) {
                     if (isConfirm) {

                      var rediect_url = module_url_path+'/order_summary/'+response.order_no;
                      location.href = rediect_url;
                     } 
                   });
                 
               }
               else if(response.status && response.status=="warning")
               {
                  SliceOrder(order_no,response.msg);
               }
               else if(response.status && response.status=="Apologies")
               {
                 swal(response.status,response.msg,'warning');
               }
               else
               {
                 swal('Error',response.msg,response.status);
               }
               
             }
           },
           error: function(XMLHttpRequest, textStatus, errorThrown) 
           {
              
           }
        });
    
      });
    
  }
  
  var table_module = false;

  $(document).ready(function()
  {
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

        /*$('#search').click(function(){

          var order_rep_sales_name  = $("#order_rep_sales_name").val();
                    
          // if(order_rep_sales_name!="" || order_rep_sales_name!=undefined){
            
          //   $("#order_from_date").removeAttr('data-parsley-required').parsley().destroy();
          //   $("#order_to_date").removeAttr('data-parsley-required').parsley().destroy();
          // }
          // else
          // {
          //   $("#order_from_date").attr('data-parsley-required','true');
          //   $("#order_to_date").attr('data-parsley-required','true');
          // }

          if($('#report_form').parsley().validate()==false) return;
          else
            filterData();
       });*/
   
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },   
      processing: true,
      serverSide: true,
      autoWidth: false,
      bFilter: false ,
      order: [ 1, 'desc' ],
      ajax: {
      'url': '{{ $module_url_path.'/rep_lead_list'}}',


      'data': function(d)
        {
          d['column_filter[q_order_no]']        = $("input[name='q_order_no']").val()
          d['column_filter[q_lead_date]']       = $("input[name='q_lead_date']").val()
          d['column_filter[q_customer_name]']   = $("input[name='q_customer_name']").val()
          d['column_filter[q_representative_name]']        = $("input[name='q_representative_name']").val()

          d['column_filter[q_maker_name]']               = $("input[name='q_maker_name']").val()           

          d['column_filter[q_lead_status]']              = $("select[name='q_lead_status']").val()

          d['column_filter[q_shipping_status]']          = $("select[name='q_shipping_status']").val()

          d['column_filter[q_payment_status]']           = $("select[name='q_payment_status']").val()

          d['column_filter[q_total_costing_retail]']     = $("input[name='q_total_costing_retail']").val()
           
          d['column_filter[q_total_costing_wholesale]']  = $("input[name='q_total_costing_wholesale']").val()

          d['column_filter[q_from_date]']    = $("input[name='order_from_date']").val()
          d['column_filter[q_to_date]']      = $("input[name='order_to_date']").val()
          d['column_filter[q_order_rep_sales_name]'] = $("input[name='order_rep_sales_name']").val()

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
            if (row.is_split_order == 1) {

              return `<a href="`+module_url_path+`/view_details/`+btoa(row.id)+`/`+btoa(row.order_no)+`/1">`+row.order_no+`</a> <span class="label label-success">Split</span>`
            }
            return `<a href="`+module_url_path+`/view_details/`+btoa(row.id)+`/`+btoa(row.order_no)+`/1">`+row.order_no+`</a>`;
        },
        "orderable": false, "searchable":false
      },         
      {data: 'created_at', "orderable": true, "searchable":false},      
      // {data: 'store_name', "orderable": true, "searchable":false}, 
      {
        render(data, type, row, meta)
        {            
            return `<a href="`+module_url_path+`/view_details/`+btoa(row.id)+`/`+btoa(row.order_no)+`/1">`+row.store_name+`</a>`;
        },
        "orderable": false, "searchable":false
      },         
      // {data: 'representative_user_name', "orderable": true, "searchable":false}, 
      {
        render(data, type, row, meta)
        {            
            return `<a href="`+module_url_path+`/view_details/`+btoa(row.id)+`/`+btoa(row.order_no)+`/1">`+row.representative_user_name+`</a>`;
        },
        "orderable": false, "searchable":false
      }, 
      {data: 'company_name', "orderable": true, "searchable":false},     
      {data: 'product_html', "orderable": false, "searchable":false},
      {
        render(data , type , row , meta)
        {
          if(row.total_wholesale_price != '')
          {
            return `<span class="fa fa-dollar"></span>`+row.total_wholesale_price;
          }else{
            return 'N/A';
          }
        }
       },
      {
         render : function(data, type, row, meta) 
         {
            
           if(row.is_confirm == '1')
           {
       
             return `<span class="label label-success">Approved</span>`;               
           }
           else if(row.is_confirm == '2')
           { 
            return `<span class="label label-warning">Pending</span>`;              
           }else  if(row.is_confirm == '3')
           { 
            return `<span class="label label-info">Rejected</span>`;              
           }

           else{
            return `<span class="label label-warning">Pending</span>`;              
          }
         },
         "orderable": true,
         "searchable":false
       },  

       
       {
          data : 'payment_status',  
          render : function(data, type, row, meta) 
          { 
           if (row.is_split_order == '1') 
           {
            return `--`;
           }
           else
           {
              if(row.transaction_status == 1)
              {
                return `<span class="label label-warning">Pending</span>`
              }
              if(row.transaction_status == 2)
              {
                return `<span class="label label-success">Paid</span>`

              }
              else if(row.transaction_status == 3)
              {
                return `<span class="label label-danger">Failed</span>`
              }
              else
              {
                 return `<span class="label label-warning">Pending</span>`
              }
            }
         },
         "orderable": false,
         "searchable":false
      },


        {

         render : function(data, type, row, meta) 
         {
           if (row.is_split_order == '1') 
           {
            return `--`;
           }
           else
           { 
           if(row.ship_status == '1')
           {
       
             return `<span class="label label-success">Shipped</span>`;               
           }
           else if(row.ship_status == '2')
           { 
            return `<span class="label label-danger">Failed</span>`;              
           }else  if(row.ship_status == '0')
           { 
            return `<span class="label label-warning">Pending</span>`;              
           }
          else{
           return `<span class="label label-danger">Pending</span>`;              
          }
        }
         },
         "orderable": true,
         "searchable":false
       },   
       
      {data: 'build_action_btn', "orderable": false, "searchable":false},     
      ]
    });

    $('input.column_filter').on( 'keyup click', function () 
    {
        filterData();
    });

    

    $('#table_module').on('draw.dt',function(event)
    {
      var oTable = $('#table_module').dataTable();
      var recordLength = oTable.fnGetData().length;
      $('#record_count').html(recordLength);

      var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
           new Switchery($(this)[0], $(this).data());
        });

      $("input.toggleSwitch").change(function(){
          statusChange($(this));
       });   
    });

    /*search box*/
     $("#table_module").find("thead").append(`<tr>
                    
                    <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                   
                    <td><input type="text" name="q_lead_date" placeholder="Search" class="search-block-new-table column_filter form-control-small datepicker" onchange="filterData();" readonly/></td>    
                    
                    <td><input type="text" name="q_customer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td><input type="text" name="q_representative_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                    <td><input type="text" name="q_maker_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td>
                       
                    </td>
                    <td><input type="text" name="q_total_costing_wholesale" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_lead_status" id="q_lead_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Approved</option>
                        <option value="2">Pending</option>
                        <option value="3">Rejected</option>
                        
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
                        <select class="search-block-new-table column_filter form-control-small" name="q_shipping_status" id="q_shipping_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="0">Pending</option>
                        <option value="1">Shipped</option>
                        <option value="2">Failed</option>
                        
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

function SliceOrder(order_no,msg)
{
    swal({
    title: "Need Confirmation",
    text: msg,
    type: "warning",
    showCancelButton: true,
    confirmButtonClass: "btn-danger",
    confirmButtonText: "Ok",
    closeOnConfirm: false
    },
    function(){
  
       $.ajax({
         url:module_url_path+'/slice_reorder',
         method:'GET',
         data:{'order_no':order_no},
         dataType:'JSON',
         beforeSend : function()
         {
           showProcessingOverlay();
           
         },
         success:function(response)
         {
            hideProcessingOverlay();
           
           if(typeof response =='object')
           {
             if(response.status && response.status=="success")
             {   
              swal({
                   title: "Success",
                   text: response.msg,
                   type: "success",
                   showCancelButton: false,
                   confirmButtonClass: "btn-success",
                   confirmButtonText: "OK",
                   closeOnConfirm: false,
                   closeOnCancel: false
                 },
                 function(isConfirm) {
                   if (isConfirm) {

                    var rediect_url = module_url_path+'/order_summary/'+response.order_no;
                    location.href = rediect_url;
                   } 
                 });
               
             }
             else
             {
               swal('Error',response.description,response.status);
             }
             
           }
         }
      });
  
    });
}
</script>

@stop                    