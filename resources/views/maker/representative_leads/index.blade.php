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

  .table.table-striped tr td .btn-success.btn-outline{
    margin-left: 3px;
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
                <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
                <li><a href="#" class="active">Representative Leads</a></li>
            </ol>
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
                              <th>Order No.</th>
                              <th>Retailer Name</th>   
                              <th>Products</th>        
                              <th>Total Costing(Retail)</th>
                              <th>Total Amount (Wholesale)</th>
                              <th>Order Date</th>
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
<!-- END Main Content -->
<script type="text/javascript"> var module_url_path  = "{{ $module_url_path or '' }}";  </script>
<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";
  var retailer_id      = "{{$retailer_id or '0'}}";
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
      'url': '{{ $module_url_path.'/get_leads'}}',
      
      'data': function(d)
        {
          d['column_filter[retailer_id]']     = retailer_id            
          d['column_filter[q_lead_id]']       = $("input[name='q_lead_id']").val()
          d['column_filter[q_username]']      = $("input[name='q_username']").val()
          // d['column_filter[q_description]']   = $("input[name='q_description']").val()
          d['column_filter[q_tot_retail]']    = $("input[name='q_tot_retail']").val()
          d['column_filter[q_tot_wholesale]'] = $("input[name='q_tot_wholesale']").val()     
          d['column_filter[q_enquiry_date]']  = $("input[name='q_enquiry_date']").val()    
        }
      },

      columns: [
      {data: 'id', "orderable": true, "searchable":false},
      {data: 'user_name', "orderable": true, "searchable":false},
      {data: 'product_html', "orderable": false, "searchable":false},
      /*{data: 'total_retail_price', "orderable": true, "searchable":false},*/
      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.total_retail_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },
      {
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.total_wholesale_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },

      /*{data: 'total_wholesale_price', "orderable": true, "searchable":false},      */
      {data: 'created_at', "orderable": true, "searchable":false},      
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
                    <td><input type="text" name="q_lead_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_username" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td></td>
                    <td><input type="text" name="q_tot_retail" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td>
                        <input type="text" name="q_tot_wholesale" placeholder="Search" class="search-block-new-table column_filter form-control-small" />
                    </td>  
                    <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>   
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
  
  

/* check all */
$('#checked_record_all').change(function () 
{ 
  var is_checked = $(this).is(":checked"); 
  $('.checkItem').each(function(index,elem)
  {
     $(elem).prop('checked', is_checked);     
  });     
});

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
</script>

@stop 