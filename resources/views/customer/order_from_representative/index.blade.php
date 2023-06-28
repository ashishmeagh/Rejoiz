@extends('retailer.layout.master')    
@section('main_content')
{{dd('hghgf')}}
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


  .dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
  }

  table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}


th {
    white-space: nowrap;
}
</style>

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
      @include('representative.layout._operation_status')
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
            <div class="white-box">           
               <div class="row"></div>
               <div class="">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th>Order No.</th>
                           <th>Order Date</th>                        
                           <th>Vendor</th>
                           <th>Products</th>
                           <th>Total Amount</th>
                           <th>Order Status</th>
                           <th>Payment Status</th>
                           <th>Shipping Status</th>
                           <th>Action</th>
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


<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";


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
      bFilter: false ,
      order: [ 1, 'desc' ],
      ajax: {
      'url': '{{ $module_url_path.'/get_listing'}}',


      'data': function(d)
        {
          d['column_filter[q_order_no]']           = $("input[name='q_order_no']").val()
          d['column_filter[q_lead_date]']          = $("input[name='q_lead_date']").val()



          d['column_filter[q_maker_company]']       = $("input[name='q_maker_company']").val()           
        
          d['column_filter[q_lead_status]']        = $("select[name='q_lead_status']").val()

          d['column_filter[q_payment_status]']     = $("select[name='q_payment_status']").val()
          d['column_filter[q_ship_status]']     = $("select[name='q_ship_status']").val()

          d['column_filter[q_total_costing_retail]']  = $("input[name='q_total_costing_retail']").val()
           
          d['column_filter[q_total_costing_wholesale]']  = $("input[name='q_total_costing_wholesale']").val()


        

        }
      },
   

   columns: [
      
                   {
        render(data, type, row, meta)
        {
            if (row.is_split_order == 1) {

              return `<a href="`+module_url_path+`/order_summary/`+btoa(row.order_no)+`/`+btoa(row.maker_id)+`">`+row.order_no+`</a><br> <span class="label label-success">Split</span>`
            }
            return `<a href="`+module_url_path+`/order_summary/`+btoa(row.order_no)+`/`+btoa(row.maker_id)+`">`+row.order_no+`</a>`

        },
        "orderable": false, "searchable":false
      }, 

      {data: 'created_at', "orderable": false, "searchable":false},      
      {data: 'company_name', "orderable": false, "searchable":false},     
    
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
        },
        "orderable": false,
         "searchable":false
       },

      {
         render : function(data, type, row, meta) 
         {
           if(row.is_confirm == '1')
           {
             return `<span class="label label-success">Approved</span>`;               
           }else if(row.is_confirm == '2')
           {
       
             return `<span class="label label-warning">Pending</span>`;               
           }else if(row.is_confirm == '3')
           {
              return `<span class="label label-danger">Rejected</span>`;               
           }else
           { 
            return `<span class="label label-danger">Pending</span>`;              
           }
         },
         "orderable": false,
         "searchable":false
       },
       
        {
         render : function(data, type, row, meta) 
         {
           
           if(row.payment_status == '1')
           {
             return `<span class="label label-success">Pending</span>`;

           }else if(row.payment_status == '2')
           {
              return `<span class="label label-info">Paid</span>`;       

           }else if(row.payment_status == '3')
           {
              return `<span class="label label-danger">Failed</span>`;               
           }
           else
           { 
              return `<span class="label label-warning">Pending</span>`;              
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
                    

                    <td><input type="text" name="q_maker_company" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
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

function confirm_reorder(ref)
{
    swal({
          title: "Need Confirmation",
          type: "warning",
          text: "Are you sure? Do you want to reorder.",
          showCancelButton: true,
          confirmButtonColor: "#8CD4F5",
          confirmButtonText: "OK",
          closeOnConfirm: true
        },
        function(isConfirm,tmp)
        {
          if(isConfirm==true)
          {
            order_no        = $(ref).attr('data-order_no');
            maker_id        = $(ref).attr('data-maker_id'); 
            link            = $(ref).attr('data-link');
            chk_products_availability(order_no,maker_id,link);
          }
        });
}

  function chk_products_availability(order_no,maker_id,link)
  {
    $.ajax({
              url:module_url_path+'/chk_products_availability',
              method:'GET',
              data:{order_no:order_no,maker_id:maker_id,order_from:'rep'},
              beforeSend : function()
              {
                showProcessingOverlay();
               
              },
              success:function(response)
              {
                  hideProcessingOverlay();

                  if(response.title!="Success")
                 {

                  swal({
                  title:response.title,
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
                  if(response.title=='Apologies')
                  return false;
                  else 
                  window.location = link;  
                })
               }
               else
               { 
                 swal(response.title,response.description,response.status);
                 window.location   = link;   
               } 
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
                url:module_url_path+'/order_cancel',
                method:'GET',
                data:{order_id:order_id},
                beforeSend : function()
                {
                  showProcessingOverlay();
                 
                },
                success:function(response)
                {
                    hideProcessingOverlay();
                   
                    swal(response.status,response.description,response.status);

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


</script>



@stop                    