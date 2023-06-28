@extends('representative.layout.master')  
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

  .view-produt-modal-dialog {
    max-width: 1150px !important;
    margin-top: 100px;
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


<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <!--   <h4 class="page-title">{{isset($confirmed_flag) && $confirmed_flag==1?'My Confirmed Orders':'My Orders'}}</h4> -->

          <h4 class="page-title">{{$page_title or ''}}</h4>
            
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
            <ol class="breadcrumb">
               <li><a href="{{url('/')}}/representative/dashboard">Dashboard</a></li>
                <li class="active">{{$module_title or ''}}</li>
            </ol>
        </div>
        
         <!-- /.col-lg-12 -->
      </div>

      @include('representative.layout._operation_status')
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
            <div class="white-box">    

               <div class="butns-right">            
                <form class="form-horizontal" id="report_form">
                    <div class="form-group row rep_data_filter_row">
                      <div class="rep_data_filter_input mr-2">
                        <label class="label-float" for="category">Order From Date</label>
                      <input type="text" autocomplete="off" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date" />

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="rep_data_filter_input">
                      <label class="label-float" for="category">Order To Date</label>

                        <input type="text" autocomplete="off" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date"/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                     <div class="col-sm-3 rep_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                        <a href="{{ $module_url_path.'/create'}}"  class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce add-new-order">Add New Order</a>
                     </div>
                </div>
              </form>

                      
         </div>  
             <div style="overflow:hidden; margin-bottom:10px;">
                           
              </div>       
               <div class="row"></div>
               <div class="table-responsive">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th>Order No.</th>
                           <th>Order Date</th>
                           <th>Customer</th>
                           <th>Vendor</th>
                           <th>Products</th>
                           <th>Total Amount</th>
                           <th>Customer Approval</th>
                           <th>Customer Payment Status</th>
                           <th>Shipping Status</th>
                           <th>Representative Commission</th>
                           <th>Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                     <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="4" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                              <th id="total_amt"></th>                          
                              <th colspan="5"></th>                             
                            </tr>
                          </tfoot>
                  </table>
               </div>
            </div>
         </div>
      </div>
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
        confirmButtonText: "OK",
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

       var confirmed_flag = '{{$confirmed_flag or 0}}'; 

       var pending_flag = '{{$pending_flag or 0 }}'; 

       var completed_flag = '{{$completed_flag or 0}}';
       
       var approved_flag = '{{$approved_flag or 0}}';

       var net_30_completed_orders = '{{$net_30_completed_orders or 0}}';

       var net_30_pending_orders = '{{$net_30_pending_orders or 0}}';


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
      'url': '{{ $module_url_path.'/lead_listing'}}',


      'data': function(d)
        {
          d['column_filter[q_order_no]']                = $("input[name='q_order_no']").val()
          d['column_filter[q_lead_date]']               = $("input[name='q_lead_date']").val()
          d['column_filter[q_customer_name]']           = $("input[name='q_customer_name']").val()
          d['column_filter[q_maker_name]']              = $("input[name='q_maker_name']").val()           
          d['column_filter[q_lead_status]']             = $("select[name='q_lead_status']").val()
          d['column_filter[q_ship_status]']             = $("select[name='q_ship_status']").val()
          d['column_filter[q_payment_status]']          = $("select[name='q_payment_status']").val()
          d['column_filter[q_total_costing_wholesale]'] = $("input[name='q_total_costing_wholesale']").val()
          d['column_filter[q_comission_status]']        = $("select[name='q_comission_status']").val()


          d['confirmed_flag'] = confirmed_flag;
          d['pending_flag']   = pending_flag;
          d['completed_flag'] = completed_flag;
          d['approved_flag']  = approved_flag;
          d['net_30_completed_orders']  = net_30_completed_orders;
          d['net_30_pending_orders']  = net_30_pending_orders;

          d['column_filter[q_from_date]'] = $("input[name='order_from_date']").val()
          d['column_filter[q_to_date]']   = $("input[name='order_to_date']").val()

        /*  d['column_filter[q_total_costing_wholesale]'] = $("input[name='q_total_costing_wholesale']").val()*/

        }
      },
    drawCallback:function(settings)
      {       
       //$("#total_amt").html("$ "+settings.json.total_wholesale_amt.toFixed(2));
       $("#total_amt").html("$ "+settings.json.total_amt.toFixed(2));
      },
    columns: [

      {
        render(data, type, row, meta)
        {
          if (row.is_split_order == 1) {

            return `<a href="`+module_url_path+`/view_lead_listing/`+btoa(row.id)+`/`+btoa(row.order_no)+`">`+row.order_no+`</a><br> <span class="label label-success">Split</span>`
          }
          else{
            return `<a href="`+module_url_path+`/view_lead_listing/`+btoa(row.id)+`/`+btoa(row.order_no)+`">`+row.order_no+`</a>`
          }
 
        },
        "orderable": false, "searchable":false
      },

      {data: 'created_at', "orderable":   false, "searchable":false},      
      // {data: 'store_name', "orderable":   false, "searchable":false}, 
      {
        render(data, type, row, meta)
        {          
            return `<a href="`+module_url_path+`/view_lead_listing/`+btoa(row.id)+`/`+btoa(row.order_no)+`" class="link_v">`+row.store_name+`</a>`
        },
        "orderable": false, "searchable":false
      },
      // {data: 'company_name', "orderable": false, "searchable":false},     
      {
        render(data, type, row, meta)
        {   
            if(row.is_confirm == 4)
            {
               return '<label>-</label>';
            } 
            else
            {
              return `<a href="`+module_url_path+`/view_lead_listing/`+btoa(row.id)+`/`+btoa(row.order_no)+`" class="link_v">`+row.company_name+`</a>`  
            }      
            
        },
        "orderable": false, "searchable":false
      },
      {data: 'product_html', "orderable": false, "searchable":false},
/*
      {
        render(data , type , row , meta)
        {
          if(row.total_wholesale_price != '')
          {
            return `<span class="fa fa-dollar"></span>`+row.total_wholesale_price;
          }else{
            return 'N/A';
          }
        },
       "orderable": false, "searchable":false

       },*/

       {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.total_wholesale_price).toFixed(2);
        },
        "orderable": false, "searchable":false
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
            return `<span class="label label-warning">Rejected</span>`;              
           }
          else{
             return `-`;              
          }
         },
         "orderable": false,
         "searchable":false
       }, 

      //  {
      //     data : 'payment_status',  
      //     render : function(data, type, row, meta) 
      //     { 
      //      if (row.is_split_order == '1') 
      //      {
      //       return `--`;
      //      }
      //      else
      //      {
      //         if(row.payment_status == 'Pending')
      //         {
              
      //           return `<span class="label label-warning">`+row.payment_status+`</span>`

      //         }
      //         if(row.payment_status == 'Paid')
      //         {
      //           return `<span class="label label-success">`+row.payment_status+`</span>`

      //         }
      //         else if(row.payment_status == 'Failed')
      //         {
      //           return `<span class="label label-danger">`+row.payment_status+`</span>`
      //         }
      //         else
      //         {

      //           if(row.is_confirm == 4)
      //           { 
      //              return `-`
      //           }
      //           else
      //           {
      //              return `<span class="label label-warning">Pending</span>`
      //           }

      //         }
      //       }
      //    },
      //    "orderable": false,
      //    "searchable":false
      // },

       {
          data : 'payment_status',  
          render : function(data, type, row, meta) 
          { 
           
              if(row.is_payment_status == '0')
              {              
                return `<span class="label label-warning">Pending</span>`;

              } 
              else if(row.is_payment_status == '1')
              {
                return `<span class="label label-warning">Paid</span>`;
              }
              else
              {
                 return `<span class="label label-warning">Failed</span>`;
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
                if(row.is_confirm == 4)
                {
                   return `-`;   
                }
                else
                {
                  return `<span class="label label-warning">Pending</span>`;   
                }

                            
             }
            else{
             return `-`;              
            }
          }
         },
         "orderable": false,
         "searchable":false
       },   
      
      {data: 'comission_status', "orderable": false, "searchable":false},        
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
    if(confirmed_flag!="" && confirmed_flag==1)
    {  
     $("#table_module").find("thead").append(`<tr>
                    
                    <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                   
                    <td><input type="text" name="q_lead_date" placeholder="Search" class="search-block-new-table column_filter form-control-small datepicker" onchange="filterData();" readonly/></td>    
                    
                    <td><input type="text" name="q_customer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                    <td><input type="text" name="q_maker_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td></td>
                     <td><input type="text" name="q_total_costing_wholesale" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_lead_status" id="q_lead_status" onchange="filterData();">
                        <option value="1">Approved</option>
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
                        <select class="search-block-new-table column_filter form-control-small" name="q_ship_status" id="q_ship_status" onchange="filterData();">
                              <option value="">All</option>
                              <option value="0">Pending</option>
                              <option value="1">Shipped</option>
                              <option value="2">Failed</option>
                        </select>
                      </td>     

                      <td>
                        <select class="search-block-new-table column_filter form-control-small" name="q_comission_status" id="q_comission_status" onchange="filterData();">
                              <option value="">All</option>
                              <option value="1">Pending</option>
                              <option value="2">Paid</option>
                              <option value="3">Failed</option>
                        </select>
                      </td>                    

                   
                    <td></td>
                </tr>`);
   }
   else
   {
      $("#table_module").find("thead").append(`<tr>
                    
                    <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                   
                    <td><input type="text" name="q_lead_date" placeholder="Search" class="search-block-new-table column_filter form-control-small datepicker" onchange="filterData();" readonly/></td>    
                    
                    <td><input type="text" name="q_customer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                    <td><input type="text" name="q_maker_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td></td>
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
                        <select class="search-block-new-table column_filter form-control-small" name="q_ship_status" id="q_ship_status" onchange="filterData();">
                              <option value="">All</option>
                              <option value="0">Pending</option>
                              <option value="1">Shipped</option>
                              <option value="2">Failed</option>
                        </select>
                      </td>     

                      <td>
                        <select class="search-block-new-table column_filter form-control-small" name="q_comission_status" id="q_comission_status" onchange="filterData();">
                              <option value="">All</option>
                              <option value="1">Pending</option>
                              <option value="2">Paid</option>
                              <option value="3">Failed</option>
                        </select>
                      </td>     

                    <td></td>
                </tr>`);
   }

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