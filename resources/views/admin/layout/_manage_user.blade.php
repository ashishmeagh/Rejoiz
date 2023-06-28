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
                <li class="active">{{$module_title or ''}}</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
        
    <div class="row">
      <div class="col-sm-12">
        <div class="white-box">
           @include('admin.layout._operation_status')
            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <br>
              <br>
              {{-- <div class="table-responsive"> --}}
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                              <th>Username</th>
                              <th>Email</th>
                              <th>KYC Status</th>
                              <th>Status</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                  </table>
              {{-- </div> --}}
            </form>
        </div>
      </div>         
    </div>
  </div>
</div>
<!-- END Main Content -->

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
      ajax: {
      'url':'{{ $module_url_path.'/get_users'}}',
      'data': function(d)
        {
          d['column_filter[q_email]']      = $("input[name='q_email']").val()
          d['column_filter[q_username]']   = $("input[name='q_username']").val()
          d['column_filter[q_status]']     = $("select[name='q_status']").val()
          d['column_filter[q_kyc_status]'] = $("select[name='q_kyc_status']").val()
        }
      },

      columns: [
      
      {data: 'user_name', "orderable": true, "searchable":false},
      {data: 'email', "orderable": true, "searchable":false},
      {
        render : function(data, type, row, meta) 
        {
          return row.build_kyc_status;
        },
        "orderable": false, "searchable":false
      },
      {
        render : function(data, type, row, meta) 
        {
          return row.build_status_btn;
        },
        "orderable": false, "searchable":false
      },
      {
        render : function(data, type, row, meta) 
        {
          return row.build_action_btn;
        },
        "orderable": false, "searchable":false
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
                    
                    <td><input type="text" name="q_username" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                    <td><input type="text" name="q_email" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                    <td>
                        <select class="search-block-new-table column_filter form-control" name="q_kyc_status" id="q_kyc_status" onchange="filterData();">
                          <option value="">Select Status</option>
                          <option value="1">Approved</option>
                          <option value="2">Not Complete</option>
                          <option value="3">In-Progress</option>
                          <option value="4">Rejected</option>
                        </select>
                        </td>
                    <td>
                       <select class="search-block-new-table column_filter form-control" name="q_status" id="q_status" onchange="filterData();">
                        <option value="">Select Status</option>
                        <option value="1">Active</option>
                        <option value="0">Block</option>
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

  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this record.');
  }
  
  function statusChange(data)
  {
    var msg = 'Are you sure? Do you want to perform this action.';

    swal({
          title:"Need Confirmation",
          text: msg,
          type: "warning",
          showCancelButton: true,                
          confirmButtonColor: "#444",
          confirmButtonText: "OK",
          closeOnConfirm: true
    },
    function(isConfirm,tmp)
    {
        if(isConfirm==true)
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
    });
  } 

</script>