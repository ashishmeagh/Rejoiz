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
            
          {{--   <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add More"><i class="fa fa-plus"></i> </a> 
              
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-circle btn-danger btn-outline show-tooltip" title="Multiple Block"><i class="ti-lock"></i> </a> 
 --}}
           {{--  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-circle btn-danger btn-outline show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>   --}}

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
                              <th>User Name</th>
                              <th>User Type</th>
                              <th>Order Type</th>
                              <th>Sender</th>
                              <th>Reciever</th>
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
                              <th colspan="7" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
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
      'url': '{{ $module_url_path}}/all_transaction',
     
    'data': function(d)
      {
        d['column_filter[q_username]']      = $("input[name='q_username']").val()
        d['column_filter[q_user_role_name]']= $("select[name='q_user_role_name']").val()

        d['column_filter[q_transaction_id]'] = $("input[name='q_transaction_id']").val()
        d['column_filter[q_amount]']         = $("input[name='q_amount']").val()
        d['column_filter[q_transaction_status]'] = $("select[name='q_transaction_status']").val()           
        d['column_filter[q_created_at]']     = $("input[name='q_created_at']").val()
        d['column_filter[q_order_no]']       = $("input[name='q_order_no']").val()  
         d['column_filter[q_order_type]']    = $("select[name='q_order_type']").val()   
          
        }
      },
      drawCallback:function(settings)
      {
       $("#total_amt").html("$ "+settings.json.total_amt);
      },
      columns: [

      {
            render(data, type, row, meta)
            { 
                
                return '';
            },
           
        }, 

      { 
         render : function(data, type, row, meta) 
         { 
            return `<a href=`+base_url+row.order_link+`>`+row.order_no+`</a>`;
           
         },
         "orderable": false,
         "searchable":false
       },

      {data: 'user_name', "orderable": true, "searchable":false},

      /*{data: 'role_name', "orderable": true, "searchable":false},*/
       {
            data : 'role_name',  
            render : function(data, type, row, meta) 
            { 

                if(row.role_name == 'Maker')
                {
                  return `Vendor`;
                }
                else
                {
                  return row.role_name;
                }
           },

           "orderable": false,
           "searchable":false
       },


      {data: 'order_type', "orderable": true, "searchable":false},

      //{data: 'sender', "orderable": true, "searchable":false},
      {
            data : 'sender',  
            render : function(data, type, row, meta) 
            { 

                if(row.sender == 'Maker')
                {
                  return `Vendor`;
                }
                else
                {
                  return row.sender;
                }
           },

           "orderable": false,
           "searchable":false
       },

      //{data: 'reciever', "orderable": true, "searchable":false},

      {
            data : 'reciever',  
            render : function(data, type, row, meta) 
            { 

                if(row.reciever == 'Maker')
                {
                  return `Vendor`;
                }
                else
                {
                  return row.reciever;
                }
           },

           "orderable": false,
           "searchable":false
       },


      {data: 'transaction_id', "orderable": true, "searchable":false},
      // {data:'transfer_id',"orderable":true,"searchable":false},
      
      {data: 'amount', "orderable": true, "searchable":false},      
     
      {
         data : 'transaction_status',  
         render : function(data, type, row, meta) 
         { 

           if(row.transaction_status == '1')
           {
             return `<span class="label label warning">Pending</span>`
           }
           else if(row.transaction_status == '2')
           {
             return `<span class="label label-success">Paid</span>`
           }
           else(row.transaction_status == '3')
           {
             return `<span class="label label danger">Failed</span>`
           }
         },
         "orderable": false,
         "searchable":false
       },

      {data: 'created_at', "orderable": true, "searchable":false}  

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

                    <td><input type="text" name="q_username" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                     <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_user_role_name" id="q_user_role_name" onchange="filterData();">
                        <option value="">All</option>
                        <option value="Maker">Vendor</option>
                        <option value="Representative">Representative</option>
                         <option value="Retailer">Retailer</option>
                         <option value="Sales Manager">Sales Manager</option>
                         <option value="Customer">Customer</option>
                         <option value="Influencer">Influencer</option>
                        </select>
                    </td>

                    <td></td>
                    <td></td>
                    <td></td>

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
</script>

@stop 