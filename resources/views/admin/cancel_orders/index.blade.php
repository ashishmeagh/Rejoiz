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
  .btn-danger.btn-outline {
    border: 1px solid #d0d0d0;
    background-color: #fff;
    color: #444;
}
   .filter_btn{
      margin-top: 20px;
      margin-right: 2px;
   }

.table > tbody > tr > td:first-child a{
  text-decoration: underline;
  text-underline-position: under;
}
.table > tbody > tr > td:first-child a:hover{
  text-decoration: none;
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
              <li><a href="{{url('/')}}/{{$curr_panel_slug or ''}}/dashboard">Dashboard</a></li>
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
              <div class="col-sm-12 export-btn-div p-0 mb-3">
                <button type="button" onclick="retailerCancelOrderExport()" title="Export Retailer Cancel orders as .csv" class="btn btn-success waves-effect waves-light filter_btn pull-right" value="export" id="retailerCancelOrderExport">Export</button>
              </div>      
                 <form class="form-horizontal" id="report_form">
              
                    <div class="row admin_data_filter_row">
                      <div class="admin_data_filter_input mr-2"> 
                        <label>Order From Date</label>
                        <input type="text" class="form-control datepicker input-float" name="order_from_date" id="order_from_date" placeholder="Select From Date" data-parsley-required="true" data-parsley-required-message="Please select order from date" readonly />

                      <span id="from_date_error" class="red"></span>
                     </div>
                     <div class="admin_data_filter_input mr-2"> 
                        <label>Order To Date</label>
                        <input type="text" class="form-control datepicker input-float" name="order_to_date" id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date" readonly/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                    <div class="admin_data_filter_input mr-2">
                       <label>Select Refund Status</label>
                        <select class="form-control dropdown" name="refund_status" id="refund_status"  {{-- data-parsley-required="true" data-parsley-required-message="Please select refund status." --}}>
                            <option value=""> Select Refund Status </option>
                            <option value="0">Refund Pending</option>
                            <option value="1">Refund Paid</option>
                        </select> 
                    </div>
                    <div class="admin_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip ml-3" title="Search" id="get_result"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>

                        {{--  <a href="javascript:void(0)" class="btn btn-succes downloadbtns-btn" title="Export as a xlsx" id="generate_report">Download </a> --}}
                     </div>
                    {{-- <div class="admin_data_filter_input"> 

                    <button type="button" title="Search" class="btn btn-success waves-effect waves-light filter_btn pull-right" value="Search" id="get_result">Search</button> 
                     <button type="reset" title="Clear" name="reset" class="btn btn-defaut waves-effect waves-light filter_btn pull-right" value="reset" id="reset">Clear</button>
                                
                   </div> --}}
            </div>  
              
          </form>
         </div> 
               <div class="table-responsive">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Order No.</th>                           
                           <th> Order Date</th>
                           <th> Retailer</th>
                           <th> Vendor</th>
                           <th> Products</th>
                           <th> Total Amount</th>
                           <th> Retailer Payment Status</th>
                           <th> Payment Type</th>
                           <th> Refund Status</th>
                           <th> Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                      <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="4" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                             <!--  <th id="total_quantity"></th>          -->                 
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
var module_url_path  = "{{ $module_url_path or '' }}";  

var module_url       = "{{ $module_url or '' }}";  

</script>

<script type="text/javascript">
  var table_module = false;
 

  $(document).ready(function()
  {
         // To date validation
        var dates = $("#order_from_date, #order_to_date").datepicker({
            // dateFormat: 'mm-dd-yy',
            // numberOfMonths: 1,
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

     $('#reset').click(function(){
      $("#order_date").val('');
      $("#refund_status").val('');
      filterData();
    });
     

    $('#get_result').click(function(){
      if($('#report_form').parsley().validate()==false) return;
        var refund_status               = $("select[name='refund_status']").val();
        var order_to_date               = $("#order_to_date").val();
        var order_from_date             = $("#order_from_date").val();
        if (refund_status == "" && order_from_date == "" && order_to_date == "") {

            swal({title: "Warning", text: "Please select refund status.", type: 'warning'},

                function(){ 
                }
            );
            return false;
        }
        else if (refund_status == "" && order_from_date != "" && order_to_date == "") {

           
            swal({title: "Warning", text: "Please select refund status.", type: 'warning'},

                function(){ 
                }
            );
            return false;
        }
        else if (refund_status == "" && order_from_date == "" && order_to_date != "") {

  
            swal({title: "Warning", text: "Please select refund status.", type: 'warning'},

                function(){ 
                }
            );
            return false;
        }
        else if (refund_status == "" && order_from_date != "" && order_to_date != "") {


            swal({title: "Warning", text: "Please select refund status.", type: 'warning'},

                function(){ 
                }
            );
            return false;
        }
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
          d['column_filter[q_order_no]']       = $("input[name='q_order_no']").val()
          d['column_filter[q_description]']    = $("input[name='q_description']").val()
          d['column_filter[q_retailer_name]']  = $("input[name='q_retailer_name']").val()
          d['column_filter[q_company_name]']   = $("input[name='q_company_name']").val()
          d['column_filter[q_total_wholesale_cost]'] = $("input[name='q_total_wholesale_cost']").val()
          d['column_filter[q_enquiry_date]']   = $("input[name='q_enquiry_date']").val()
          d['column_filter[q_ship_status]']    = $("select[name='q_ship_status']").val()
          d['column_filter[q_payment_status]'] = $("select[name='q_payment_status']").val()


          d['column_filter[q_order_from_date]']= $("input[name='order_from_date']").val()
          d['column_filter[q_order_to_date]']  = $("input[name='order_to_date']").val()
          d['column_filter[q_refund_status]']  = $("select[name='refund_status']").val()
          d['column_filter[q_refund_field]']   = $("select[name='q_refund_field']").val()
          d['column_filter[q_payment_type]']       = $("#payment_type").val();

       }
      },
      drawCallback:function(settings)
      {
       $("#total_amt").html("<span title='Total Amount' style='cursor:pointer;'>$ "+settings.json.total_amt.toFixed(2)+"</span>");
       // $("#total_quantity").html("<span title='Total Quantity' style='cursor:pointer;'>"+settings.json.total_quantity+"</span>");
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
      
      // {data: 'store_name', "orderable": false, "searchable":false}, 
      {
        render(data, type, row, meta)
        {
            //return row.order_no;
            //return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.store_name+`</a>`

            return `<a href="`+module_url+`/admin/retailer/view/`+btoa(row.retailer_id)+`" class="link_v">`+row.store_name+`</a>`

        },
        "orderable": false, "searchable":false
      }, 

      // {data:'company_name',"orderable": false, "searchable":false},
      {
        render(data, type, row, meta)
        {
            //return row.order_no;
            //return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.company_name+`</a>`

            return `<a href="`+module_url+`/admin/vendor/view/`+btoa(row.maker_id)+`" class="link_v">`+row.company_name+`</a>`

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

      {data: 'payment_status', "orderable": false, "searchable":false}, 
      {data: 'payment_type', "orderable": false, "searchable":false}, 

    

   /*  {
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
       },*/
  
      /* {
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
       },*/
       {data: 'build_refund_btn', "orderable": false, "searchable":false},  
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
            <select class="search-block-new-table column_filter form-control-small" name="q_payment_type" id="payment_type" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Direct</option>
                  <option value="0">In-Direct</option>
            </select>
          </td>

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_refund_field" id="q_refund_field" onchange="filterData();">
                  <option value="">All</option>
                  <option value="0">Pending</option>
                  <option value="1">Refund Paid</option>
                  
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

    function refundProcess(order_id)
    { 
          swal({  
                    title:'Need Confirmation',
                    text : "Are you sure? Do you want to pay refund.",
                    type : "warning",
                    showCancelButton: true,                
                    confirmButtonColor: "#8CD4F5",
                    confirmButtonText: "OK",
                    closeOnConfirm: true
                },
                function(isConfirm,tmp)
                {
                    if(isConfirm==true)
                    {
                       
                          
                         
                        $.ajax({
                              url:'{{url($module_url_path.'/refund_process')}}',
                              type: 'GET',
                              dataType:'json',
                              data: {'order_id':order_id},
                              beforeSend: function() 
                              {
                               showProcessingOverlay();                 
                              },

                              success:function(data)
                              {
                                  hideProcessingOverlay();   
                                  if(data.status =='success')
                                  {    
                                      swal({
                                            title: "Success",
                                            text: data.msg,
                                            type: data.status,
                                            confirmButtonText: "OK",
                                            closeOnConfirm: false
                                          },
                                          function(isConfirm) {
                                            if (isConfirm) 
                                            {
                                               location.reload();
                                            } 
                                          });
                                  }
                                  else
                                  {   
                                    swal(data.status,data.msg,'error');
                                  }
                              }
                        });

                    }


                }); 



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

function retailerCancelOrderExport(){

    var q_order_no                = $("input[name='q_order_no']").val();
    var q_retailer_name            = $("input[name='q_retailer_name']").val();
    var q_company_name            = $("input[name='q_company_name']").val();
    var q_total_wholesale_cost       = $("input[name='q_total_wholesale_cost']").val();
    var q_enquiry_date       = $("input[name='q_enquiry_date']").val();
    var q_ship_status             = $("select[name='q_ship_status']").val()
    var q_payment_status          = $("select[name='q_payment_status']").val()
    var q_from_date               = $("input[name='order_from_date']").val();
    var q_to_date                 = $("input[name='order_to_date']").val();
    var q_refund_status                 = $("select[name='refund_status']").val()
    var q_refund_field                 = $("select[name='q_refund_field']").val()
    

    $.ajax({
          url: module_url_path+'/get_export_cancel_orders',
          data: {q_order_no:q_order_no,q_retailer_name:q_retailer_name,q_company_name:q_company_name,q_total_wholesale_cost:q_total_wholesale_cost,q_enquiry_date:q_enquiry_date,q_ship_status:q_ship_status,q_from_date:q_from_date,q_to_date:q_to_date,q_payment_status:q_payment_status,q_refund_status:q_refund_status,q_refund_field:q_refund_field},
        
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
              saveData(data, 'retailer_cancelled_orders.csv');
            }
          }
        });
  }
</script>
@stop