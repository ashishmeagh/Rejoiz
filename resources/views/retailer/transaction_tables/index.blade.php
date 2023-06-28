@extends('retailer.layout.master')                
@section('main_content')

<style>
  .table.table-striped tr td .btn-success.btn-outline{
    margin-left: 3px;
  }

  .dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
  }

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}
.table > tbody > tr > td a{
  text-decoration: underline;text-underline-position: under;
}
.table > tbody > tr > td a:hover{
  text-decoration: none;
}
.label {
  color: white;
  padding: 4px 11px;
}
.success {background-color: #4CAF50;} /* Green */
.info {background-color: #2196F3;} /* Blue */
.warning {background-color: #ff9800;} /* Orange */
.danger {background-color: #f44336;} /* Red */ 
.other {background-color: #e7e7e7; color: black;} /* Gray */ 
</style>
<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">
    
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-sm-12 top-bg-title">
         <h4 class="page-title">{{$module_title or ''}}</h4>
         <div class="right">
         <ol class="breadcrumb">
            <li><a href="{{url('/')}}/retailer/dashboard">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
      </div>
   </div>
        
    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">
        	<div class="pull-right top_small_icon">
            
          {{--   <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add More"><i class="fa fa-plus"></i> </a> 
              
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-circle btn-danger btn-outline show-tooltip" title="Multiple Block"><i class="ti-lock"></i> </a> 
 --}}
           {{--  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-circle btn-danger btn-outline show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>   --}}

           <button type="button" onclick="retailTransactionsExport()" title="Export Customer transactions as .csv" class="btn btn-success waves-effect waves-light" value="export" id="retailTransactionsExport">Export</button>

            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            
          </div>
          
            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                              <th>Sr.No</th>
                              <th>Order No.</th>
                              <th>Company Name</th>
                              <th>Order Type</th> 
                              <th>Transaction Type</th>
                              <th>Transaction Id</th> 
                              <th>Amount</th>                          
                              <th>Transaction Status</th>
                              <th>Transaction Date</th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                       <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="5" align="right"> &nbsp;</th>
                              <th style="text-align: right;"> Total: &nbsp;</th>
                              <th id="total_amt"></th>                          
                              <th colspan="2"></th>                             
                            </tr>
                          </tfoot>
                  </table>
              </div>
            </form>
        </div>
      </div>         
    </div>
  </div>
</div>
<!-- END Main Content -->

<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";
  var base_url         = "{{ url('/') }}";

  var table_module = false;
  var sr_no = 0;
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

          ajax: {
          'url': '{{ $module_url_path}}',
          
        'data': function(d)
          {
            d['column_filter[q_transaction_id]'] = $("input[name='q_transaction_id']").val()
            d['column_filter[q_order_no]']       = $("input[name='q_order_no']").val()
            d['column_filter[q_amount]']         = $("input[name='q_amount']").val()
            d['column_filter[q_transaction_status]'] = $("select[name='q_transaction_status']").val()
            d['column_filter[q_payment_type]']   = $("select[name='q_payment_type']").val()
               
            d['column_filter[q_created_at]']     = $("input[name='q_created_at']").val()         
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
                
                return '';
            },
           
          }, 

          {
            render(data, type, row, meta)
            {
                return `<a href=`+base_url+row.order_link+`>`+row.order_no+`</a>`;
            },
            "orderable": false, "searchable":false
          }, 

          {
            render(data, type, row, meta)
            {
                return row.company_name;
            },
            "orderable": false, "searchable":false
          },

          {data: 'order_type', "orderable": true, "searchable":false},

          {data: 'transaction_type', "orderable": true, "searchable":false},

          {data: 'transaction_id', "orderable": true, "searchable":false},

          {data: 'amount', "orderable": true, "searchable":false}, 

          {
             data : 'transaction_status',  
             render : function(data, type, row, meta) 
             { 
               if(row.transaction_status == '1')
               {
                 return `<span class="label label-success">Pending</span>`
               }
               else if(row.transaction_status == '2')
               {
                 return `<span class="label label-success">Paid</span>`
               }
               else(row.transaction_status == '3')
               {
                 return `<span class="label label-success">Failed</span>`
               }
             },
             "orderable": false,
             "searchable":false
           },
          {data: 'created_at', "orderable": true, "searchable":false}  

          ],
          "order": [[ 1, 'asc' ]]
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

 
          /*this code for serial number with pagination*/
          table_module.on( 'draw.dt', function (){
            var PageInfo = $('#table_module').DataTable().page.info();

            table_module.column(0, { page: 'current' }).nodes().each( function (cell, i) {
              cell.innerHTML = i + 1 + PageInfo.start;
          });
    });

    

        /*search box*/
         $("#table_module").find("thead").append(`<tr>

                       <td></td>

                       <td><input type="text" name="q_order_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                       <td></td>
                        <td></td>

                       <td>
                           <select class="search-block-new-table column_filter form-control-small" name="q_payment_type" id="q_payment_type" onchange="filterData();">
                            <option value="">All</option>
                            <option value="order_payment">Order Payment</option>
                            
                            </select>
                       </td>

                        <td><input type="text" name="q_transaction_id" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                        
                        <td><input type="text" name="q_amount" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                        <td>
                           <select class="search-block-new-table column_filter form-control-small" name="q_transaction_status" id="q_status" onchange="filterData();">
                            <option value="">All</option>
                            <option value="1">Pending</option>
                            <option value="2">Paid</option>
                             <option value="3">Failed</option>
                            </select>
                        </td>
                         <td><input type="text" name="q_created_at" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/>
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

      function confirm_delete(ref,event)
      {
        confirm_action(ref,event,'Are you sure? Do you want to delete this record.');
      }
  
      function statusChange(data)
      {
       
              var ref = data; 
              var type = data.attr('data-type');
              var enc_id = data.attr('data-enc_id');
              var id = data.attr('data-id');

              $.ajax({
                  url:module_url_path+'/'+type,
                  type:'GET',
                  data:{id:enc_id},
                  dataType:'json',
                  success: function(response)
                  {
                    if(response.status=='SUCCESS')
                    {
                      if(response.data=='ACTIVE')
                      {
                        $(ref)[0].checked = true;  
                        $(ref).attr('data-type','deactivate');

                      }else
                      {
                        $(ref)[0].checked = false;  
                        $(ref).attr('data-type','activate');
                      }

                      swal('Success','Status has been changed.','success');
                    }
                    else
                    {
                      sweetAlert('Error','Something went wrong,please try again.','error');
                    }  
                  }
              }); 
       
      } 

      function changeAprovalStatus(representative_id,ref)
      {   
           var representativeAprovalStatus = '';
     
           if($(ref).is(":checked"))
           {
             representativeAprovalStatus = '1';
           }
           else
           {
            representativeAprovalStatus = '0';
           }
           
           $.ajax({
               method   : 'GET',
               dataType : 'JSON',
               data     : {representative_id:representative_id,representativeAprovalStatus:representativeAprovalStatus},
               url      : module_url_path+'/changeAprovalStatus/',
               success  : function(response)
               {                         
                if(typeof response == 'object' && response.status == 'SUCCESS')
                {
                  swal('Done', response.message, 'success');
                }
                else
                {
                  swal('Oops...', response.message, 'error');
                }               
               }
           });
      }



    /* check all */
    $('#checked_record_all').change(function () 
    { 
      var is_checked = $(this).is(":checked"); 
      $('.checkItem').each(function(index,elem)
      {
         $(elem).prop('checked', is_checked);     
      });     
    });


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

function retailTransactionsExport(){
    var module_url_base_path  = "{{ $module_url_base_path or '' }}";

    var q_transaction_id            = $("input[name='q_transaction_id']").val();
    var q_order_no                  = $("input[name='q_order_no']").val()
    var q_amount                    = $("input[name='q_amount']").val()
    var q_transaction_status        = $("select[name='q_transaction_status']").val()
    var q_payment_type              = $("select[name='q_payment_type']").val()
    var q_created_at                = $("input[name='q_created_at']").val()
  
    $.ajax({
          url: module_url_base_path+'/get_export_transasction_orders',
          data: {q_transaction_id:q_transaction_id,q_order_no:q_order_no,q_amount:q_amount,q_transaction_status:q_transaction_status,q_payment_type:q_payment_type,q_created_at:q_created_at},
        
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
              saveData(data, 'customer_payment_transaction.csv');
            }
          }
        });

}

</script>

@stop 