@extends('influencer.layout.master')  
@section('main_content')
<style type="text/css">
.table tbody tr td .table{box-shadow: -2px 6px 14px -5px #b9b9b9;margin-top: 10px;}
th {white-space: nowrap;}
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
               <li><a href="{{ url(config('app.project.influencer_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
           <div class="white-box">
            @include('influencer.layout._operation_status')
        
              <div class="table-responsive">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           {{-- <th> Influencer Name</th>  --}}
                           <th> Transaction Id</th>                          
                           <th> Transfer Id</th>                          
                           <th> Reward Amount</th>
                           <th> Transaction Status</th>
                           <th> Transaction Date</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
                      <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="1" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                              <th id="total_amt"></th>                          
                              <th colspan="2"></th>                             
                            </tr>
                          </tfoot>
                  </table>
                  </div>
            </div>
             </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>


<input type="hidden" name="csrfToken" id="csrfToken" value="{{csrf_token()}}">

<!-- /#page-wrapper -->


<script type="text/javascript">

  var module_url_path      = "{{ $module_url_path or '' }}"; 

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
      bFilter: false,
      // "order":[4,'Asc'],

      ajax: {
      'url': module_url_path+'/get_transaction_history_listing',
      'data': function(d)
       {        

          d['column_filter[q_transaction_id]'] = $("input[name='q_transaction_id']").val()
          d['column_filter[q_transfer_id]']    = $("input[name='q_transfer_id']").val()
          d['column_filter[q_reward_amount]']  = $("input[name='q_reward_amount']").val()
          d['column_filter[q_status]']         = $("select[name='q_status']").val()
          d['column_filter[q_created_at]']     = $("input[name='q_created_at']").val()
       }
      },
       drawCallback:function(settings)
      {       
       $("#total_amt").html("$ "+settings.json.total_amt.toFixed(2));
      },

      columns: [

      // {data: 'user_name', "orderable": false, "searchable":false},
      
      {data: 'transaction_id', "orderable": false, "searchable":false},

      {data: 'transfer_id', "orderable": false, "searchable":false},
      
      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.reward_amount).toFixed(2);
        },
        "orderable": false, "searchable":false
      },

      {
         data : 'status',  
         render : function(data, type, row, meta) 
         {
           
           if(row.status == '1')
           {
             return `<span class="label label-warning">Pending</span>`
           }
           else if(row.status == '2')
           {
             return `<span class="label label-success">Success</span>`

           }
           else(row.status == '3')
           {
             return `<span class="label label-danger">Failed</span>`
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
  
 /* $('#table_module').on('draw.dt',function(event)
  {
    var oTable = $('#table_module').dataTable();
    var recordLength = oTable.fnGetData().length;
    $('#record_count').html(recordLength);  
  });*/
  
  /*search box*/
  $("#table_module").find("thead").append(`<tr>          
         
          
          <td><input type="text" name="q_transaction_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

          <td><input type="text" name="q_transfer_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

          <td><input type="text" name="q_reward_amount" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="2">Success</option>
                  <option value="3">Failed</option>
            </select>
          </td> 
          
          <td><input type="text" name="q_created_at" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>

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