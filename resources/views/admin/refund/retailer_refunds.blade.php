@extends('admin.layout.master')                

@section('main_content')
<style>
  .table.table-striped tr td .btn-success.btn-outline{
    margin-left: 3px;
  }


.label {
  color: white;
  padding: 4px 10px;
  font-family: Arial;
}
/*.success {background-color: #fb3a62;} */
.info {background-color: #2196F3;} /* Blue */
.warning {background-color: #ff9800;} /* Orange */
.danger {background-color: #f44336;} /* Red */ 
.other {background-color: #e7e7e7; color: black;} /* Gray */ 

 th {
    white-space: nowrap;
}
.table > tbody > tr > td:first-child a {
        text-underline-position: under;
        text-decoration: underline;
        margin-bottom: 5px;
        display: inline-block;
    }
    .table > tbody > tr > td:first-child a:hover {
        text-decoration: none;
    }
</style>
<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">
    
<!-- Page Content -->
<div id="page-wrapper">
  <div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title or ''}}</h4> </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
            <ol class="breadcrumb">
                <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
                <li class="active">{{$page_title or ''}}</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
        
    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">
          <div class="pull-right top_small_icon">
            
            <button type="button" onclick="retailerRefundExport()" title="Export Retailer Refund as .csv" class="btn btn-success waves-effect waves-light pull-right" value="export" id="retailerRefundExport">Export</button>
         
            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip mr-2" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            
          </div>
          
            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                              <th>Order No.</th>
                              <th>Date</th>
                              <th>Retailer</th>
                              <th>Paid By</th>
                              <th>Transaction Id</th>
                              <th>Amount</th>                          
                              <th>Status</th>
                            </tr>
                      </thead>
                      <tbody>
                      </tbody>
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
  var order_detailer_url  = "{{ url('admin/leads/view_order/')}}";
  var base_url         = "{{ url('/') }}";


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
      ajax: {
      'url': '{{ $module_url_path}}/all',
     
    'data': function(d)
      {
        d['column_filter[q_retailer_name]']      = $("input[name='q_retailer_name']").val()
        d['column_filter[q_paid_by]']      = $("input[name='q_paid_by']").val()
        d['column_filter[q_transaction_id]'] = $("input[name='q_transaction_id']").val()
        d['column_filter[q_amount]']   = $("input[name='q_amount']").val()
        d['column_filter[q_transaction_status]'] = $("select[name='q_transaction_status']").val()           
        d['column_filter[q_created_at]']= $("input[name='q_created_at']").val()
        d['column_filter[q_order_no]']= $("input[name='q_order_no']").val()    
          
        }
      },

      columns: [
      { 
         render : function(data, type, row, meta) 
         { 
            return `<a href=`+base_url+row.order_link+`>`+row.order_no+`</a>`;
           
         },
         "orderable": false,
         "searchable":false
       },
      {data: 'created_at', "orderable": true, "searchable":false}, 
      {data: 'retailer_name', "orderable": true, "searchable":false},
      {data: 'paid_by', "orderable": true, "searchable":false},
      {data: 'balance_transaction', "orderable": true, "searchable":false},
      // {data:'transfer_id',"orderable":true,"searchable":false},
      
      {data: 'amount', "orderable": true, "searchable":false},      
     {
         data : 'transaction_status',  
         render : function(data, type, row, meta) 
         { 

           if(row.status == '1')
           {
             return `<span class="label warning">Pending</span>`
           }
           else if(row.status == '2')
           {
             return `<span class="label label-success">Paid</span>`
           }
           else(row.status == '3')
           {
             return `<span class="label danger">Failed</span>`
           }
         },
         "orderable": false,
         "searchable":false
       }

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

                     <td><input type="text" name="q_created_at" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/>
                     </td>   

                    <td><input type="text" name="q_retailer_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

                    <td><input type="text" name="q_paid_by" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                    
                    <td><input type="text" name="q_transaction_id" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>

              
                    
                    <td><input type="text" name="q_amount" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
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

  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this record.');
  }
  
  function statusChange(data)
  {
    // var msg = 'Do you really want to perform this action?';

    // swal({
    //       title: msg,
    //       type: "warning",
    //       showCancelButton: true,                
    //       confirmButtonColor: "#444",
    //       confirmButtonText: "Yes, do it!",
    //       closeOnConfirm: true
    // },
    // function(isConfirm,tmp)
    // {
    //     if(isConfirm==true)
    //     {
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
    //     }
    // });
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

function retailerRefundExport(){

    var q_retailer_name         = $("input[name='q_retailer_name']").val();
    var q_paid_by               = $("input[name='q_paid_by']").val();
    var q_transaction_id        = $("input[name='q_transaction_id']").val();
    var q_amount                =  $("input[name='q_amount']").val();
    var q_transaction_status    = $("select[name='q_transaction_status']").val();

    var q_created_at             = $("input[name='q_created_at']").val();
    var q_order_no               = $("input[name='q_order_no']").val();

    $.ajax({
          url: module_url_path+'/get_export_retailer_refund',
          data: {q_order_no:q_order_no,q_retailer_name:q_retailer_name,q_paid_by:q_paid_by,q_transaction_id:q_transaction_id,q_amount:q_amount,q_transaction_status:q_transaction_status,q_created_at:q_created_at},
        
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
              saveData(data, 'retailer_refunds.csv');
            }
          }
        });
  }
</script>

@stop 