@extends('admin.layout.master')  
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
               <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
           <div class="white-box">
            @include('admin.layout._operation_status')
          <div class="col-sm-12 text-right export-btn-div p-0 mb-3">
            <button type="button" onclick="influencerRewardHistoryExport()" title="Export Influencer Reward History as .csv" class="btn btn-success waves-effect waves-light pull-right" value="export" id="influencerRewardHistoryExport">Export</button>
          </div>
              <div class="table-responsive">
                  <table class="table" id="table_module">
                     <thead>
                        <tr>
                           <th> Influencer Name</th> 
                           <th> Date</th>
                           <th> Reward Amount</th>                          
                           <th> Total Order Amount</th>                          
                           <th> Used Order Amount</th>
                           <th> Carry Forward Amount</th>
                           <th> Status</th>
                           <th> Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>
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
  var influencer_view_path = "{{ $influencer_view_path or '' }}"; 

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
      'url': module_url_path+'/get_influencer_rewards_history_listing',
      'data': function(d)
       {        
          d['column_filter[q_user_name]']     = $("input[name='q_user_name']").val()
          d['column_filter[q_reward_amount]'] = $("input[name='q_reward_amount']").val()
          d['column_filter[q_status]']        = $("select[name='q_status']").val()

           d['column_filter[q_reward_date]']  = $("input[name='q_reward_date']").val()
       }
      },

      columns: [

      {
        render(data, type, row, meta)
        {
             return `<a target="_blank" href=`+influencer_view_path+`/view/`+btoa(row.influencer_id)+`>`+row.user_name+`</a>`;
        },
        "orderable": false, "searchable":false
      },

      {data: 'created_at', "orderable": false, "searchable":false},
      
      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.reward_amount).toFixed(2);
        },
        "orderable": false, "searchable":false
      },

      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.total_order_amount).toFixed(2);
        },
        "orderable": false, "searchable":false
      },

      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.used_order_amount).toFixed(2);
        },
        "orderable": false, "searchable":false
      },

      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.carry_forward_amount).toFixed(2);
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
      
      {data: 'build_action_btn', "orderable": false, "searchable":false},
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
          <td><input type="text" name="q_user_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

          <td><input type="text" name="q_reward_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td> 

          <td><input type="text" name="q_reward_amount" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

          <td></td>
          <td></td>
          <td></td>

          <td>
            <select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
                  <option value="">All</option>
                  <option value="1">Pending</option>
                  <option value="2">Success</option>
                  <option value="3">Failed</option>
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


  function influencerRewardHistoryExport(){

    var q_user_name               = $("input[name='q_user_name']").val();
    var q_reward_amount           = $("input[name='q_reward_amount']").val();
    var q_status                  = $("select[name='q_status']").val()
    var q_reward_date             = $("input[name='q_reward_date']").val();    

    $.ajax({
          url: module_url_path+'/get_export_influencer_reward_history',
          data: {q_user_name:q_user_name,q_reward_amount:q_reward_amount,q_status:q_status,q_reward_date:q_reward_date},
        
          type:"get",
          success:function(data)
          {
            if(data.status != null && data.status == 'error')
            {
              swal('Error',data.message,'error');
            }
            else
            {
              saveData(data, 'influencer_reward_history.csv');
            }
          }
        });
  }
  
</script>
@stop