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

.success {background-color: #686868;} /* Green */
.info {background-color: #2196F3;} /* Blue */
.warning {background-color: #ff9800;} /* Orange */
.danger {background-color: #f44336;} /* Red */ 
.other {background-color: #e7e7e7; color: black;} /* Gray */ 
.table > tbody > tr > td:first-child a {
        text-underline-position: under;
        text-decoration: underline;
        margin-bottom: 5px;
        display: inline-block;
    }
    .table > tbody > tr > td:first-child a:hover {
        text-decoration: none;
    }
 th {
    white-space: nowrap;
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
                              <th>Company Name</th>    
                              <th>Customer Name</th>
                              <th>Transaction Id</th>
                              <th>Amount</th>                          
                              <th>Transaction Status</th>
                              <th>Transaction Date</th>
                              {{-- <th>&nbsp;</th> --}}
                            </tr>
                      </thead>
                      <tbody>
                      </tbody>
                      <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="4" align="right"> &nbsp;</th>
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
      'url': '{{ $module_url_path}}',
      
    'data': function(d)
      {
        d['column_filter[q_username]']      = $("input[name='q_username']").val()
        d['column_filter[q_transaction_id]'] = $("input[name='q_transaction_id']").val()
        d['column_filter[q_amount]']   = $("input[name='q_amount']").val()
        d['column_filter[q_transaction_status]'] = $("select[name='q_transaction_status']").val()
           
        d['column_filter[q_created_at]']= $("input[name='q_created_at']").val()
        d['column_filter[q_order_no]']= $("input[name='q_order_no']").val()            
        }
      },
      drawCallback:function(settings)
      {
          var total_amt = 0;
          if(settings.json.total_amt)
            var total_amt = settings.json.total_amt;

         $("#total_amt").html("$ "+total_amt);
      },
      columns: [

      {
            render(data, type, row, meta)
            { 
                
                return '';
            },
           
      }, 
      
      // {data:'order_no',"orderable":true,"searchable":false},

      {
        render(data, type, row, meta)
        {
             return `<a href=`+base_url+row.order_link+`>`+row.order_no+`</a>`;

        },
        "orderable": false, "searchable":false
      },

      { 
         render : function(data, type, row, meta) 
         { 
            
            return row.company_name;

           
         },
         "orderable": false,
         "searchable":false
       },

      {data: 'user_name', "orderable": true, "searchable":false},
      {data: 'transaction_id', "orderable": true, "searchable":false},
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
             return `<span class="label label label-success">Paid</span>`
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
                  <td></td> 

                    <td><input type="text" name="q_username" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

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

</script>

@stop 