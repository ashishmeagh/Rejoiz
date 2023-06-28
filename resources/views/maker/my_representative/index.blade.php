@extends('maker.layout.master')   
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

.table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #666;
    background-color: transparent;
    color: #666;
}
</style>
<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">
    
<!-- Page Content -->
<div id="page-wrapper">
  <div class="container-fluid">
    <div class="row bg-title">
        <div class="col-sm-12 top-bg-title">
            <h4 class="page-title">{{$page_title or ''}}</h4> 
            <div class="right"> 
            <ol class="breadcrumb">
                <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
                <li><a href="#" class="active">My Representatives</a></li>
            </ol>
        </div>
          </div>
        <!-- /.col-lg-12 -->
    </div>
        
    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">
         <div class="pull-right top_small_icon">
            
            <a href="{{$module_url_path}}" onclick="" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            
          </div>
          
            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <br>
              <br>
              <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                            <!-- <th>
                              <div class="checkbox checkbox-success"><input type="checkbox" name="checked_record_all[]" id="checked_record_all" class="case checkItemAll"/><label for="checkbox"></label>
                              </div>
                          </th> -->
                              <th>Representative Name</th>   
                              <th>Email Id</th>        
                              <th>Area</th>
                              <th>Action</th>
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
      'url': '{{ $module_url_path.'/get_listing_data'}}',


      'data': function(d)
        {
          
          d['column_filter[q_representative_name]'] = $("input[name='q_representative_name']").val()
          d['column_filter[q_representative_email]']     = $("input[name='q_representative_email']").val()
          d['column_filter[q_representative_area]']  = $("input[name='q_representative_area']").val()
    
        }
      },

     columns: [
      {data: 'user_name', "orderable": true, "searchable":false}, 
      {data: 'email', "orderable": true, "searchable":false},
      {data: 'area_name', "orderable": true, "searchable":false},
     
      {
         render : function(data, type, row, meta)
         {
          // console.log(row.build_action_btn);
           return row.build_action_btn;

         },
         "orderable": true, "searchable":false
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
                    
                    <td><input type="text" name="q_representative_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                   
                    <td><input type="text" name="q_representative_email" placeholder="Search" class="search-block-new-table column_filter form-control-small " onchange="filterData();" /></td>    
                    
                    <td><input type="text" name="q_representative_area" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                     
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
      
</script>




@stop

