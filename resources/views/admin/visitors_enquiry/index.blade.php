@extends('admin.layout.master')                

@section('main_content')


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
           <div class="pull-right top_small_icon">
            
             <button type="button" onclick="visitorsEnquiryExport()" title="Export Admin Product as .csv" class="btn btn-outline btn-info btn-circle show-tooltip" value="export" id="visitorsEnquiryExport">Export</button>     

             
            
          </div>
            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <br>
              <br>
              
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                              <th>Name</th>
                              <th>Mobile No</th>
                              <th>Type</th>
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                  </table>
              
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
      'url':'{{ $module_url_path.'/get_data'}}',
      'data': function(d)
        {
          d['column_filter[q_name]']    = $("input[name='q_name']").val()
          d['column_filter[q_mobile_no]']   = $("input[name='q_mobile_no']").val()
          d['column_filter[q_type]'] = $("input[name='q_type']").val()
        }
      },

      columns: [
      
      {data: 'name', "orderable": true, "searchable":false},
      {data: 'mobile_no', "orderable": true, "searchable":false},
      {data: 'type', "orderable": true, "searchable":false},
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
    });



    /*search box*/
     $("#table_module").find("thead").append(`<tr>
                    
                    <td><input type="text" name="q_name" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                    <td><input type="text" name="q_mobile_no" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                    <td><input type="text" name="q_type" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                        
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

function visitorsEnquiryExport(){

var q_name            = $("input[name='q_name']").val();
var q_mobile_no            = $("input[name='q_mobile_no']").val()
var q_type              = $("input[name='q_type']").val();


$.ajax({
      url: module_url_path+'/get_export_visitors_enquiry',
      data: {q_name:q_name,q_mobile_no:q_mobile_no,q_type:q_type},
    
      type:"get",
      
      success:function(data)
      {
        hideProcessingOverlay();
        if(data.status != null && data.status == 'error')
        {
          swal('Error',data.message,'error');
        }
        else
        {
          saveData(data, 'visitors_enquiry.csv');
        }
      }
    });
}


</script>



@stop                    
