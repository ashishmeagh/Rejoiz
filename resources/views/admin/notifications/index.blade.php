@extends('admin.layout.master')                

@section('main_content')
<style>
  .table.table-striped tr td .btn-success.btn-outline{
    margin-left: 3px;
  }


.label {
  color: white;
  padding: 8px;
  font-family: Arial;
}
.success {background-color: #fb3a62;} /* Green */
.info {background-color: #2196F3;} /* Blue */
.warning {background-color: #ff9800;} /* Orange */
.danger {background-color: #f44336;} /* Red */ 
.other {background-color: #e7e7e7; color: black;} /* Gray */ 

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
          <form class="form-horizontal" id="report_form">
                    <div class="form-group row admin_data_filter_row">
                      <div class="admin_data_filter_input mr-2">
                        <label class="label-float" for="category">From Date</label>
                      <input type="text" autocomplete="off" class="form-control datepicker input-float" name="order_from_date" readonly='true'  id="order_from_date" placeholder="Select From Date"
                      data-parsley-required="true" data-parsley-required-message="Please select order from date" />

                      <span id="from_date_error" class="red"></span>
                      <div class="clearfix"></div>
                      </div>
                     <div class="admin_data_filter_input">
                      <label class="label-float" for="category">To Date</label>

                        <input type="text" autocomplete="off" class="form-control datepicker input-float" name="order_to_date" readonly='true' id="order_to_date" placeholder="Select To Date" data-parsley-required="true" data-parsley-required-message="Please select order to date"/>

                      <span id="to_date_error" class="red"></span>
                     </div>
                     <div class="admin_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip ml-3" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>

                     </div>
                </div>
            </form>
             @if(Session::has('error'))

              <div class="alert {{ Session::get('alert-class', 'alert-danger') }}" id ="alert_container">
              <a href="javascript:void(0)" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ Session::get('error') }}
              </div>

            @endif
            
            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <div class="pull-right top_small_icon">
                <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>
                <a href="javascript:void(0)" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','mark_as_read');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Mark as read"><i class="fa fa-bell"></i> </a>
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a> 
                <a  href="{{$module_url_path}}/export"  class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a xlsx"><i class="fa fa-file-excel-o"></i></a> 
             </div>
              <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                              <th>
                              <div class="checkbox checkbox-success">
                                <input type="checkbox" name="checked_record_all[]" id="checked_record_all" class="case checkItemAll"/><label for="checkbox"></label>
                              </div>
                             </th>
                              <th>Date</th>    
                              <th>Title</th>
                              <th>Description</th>
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

<script type="text/javascript">

  var module_url_path  = "{{ $module_url_path or '' }}";
  var order_detailer_url  = "{{ url('admin/leads/view_order/')}}";
  var base_url         = "{{ url('/') }}";
  var table_module = false;

  // To date validation
        var dates = $("#order_from_date, #order_to_date").datepicker({
            // dateFormat: 'mm-dd-yy',
            // numberOfMonths: 1,
            maxDate:'+0d',
            onSelect: function(date) {
                for(var i = 0; i < dates.length; ++i) {
                    if(dates[i].id < this.id)
                        $(dates[i]).datepicker('option', 'maxDate', date);
                    else if(dates[i].id > this.id)
                        $(dates[i]).datepicker('option', 'minDate', date);
                }
            } 
        });

  $('#search').click(function()
  {
    if($('#report_form').parsley().validate()==false) return;
    else

    showProcessingOverlay();  
    filterData();
   
  });

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
      'url': '{{ $module_url_path}}/get_notifications',
      
    'data': function(d)
      {
      
        d['column_filter[q_from_date]']      = $("input[name='order_from_date']").val()
        d['column_filter[q_to_date]']        = $("input[name='order_to_date']").val()

        }
      },

      columns: [
      
      // {data:'order_no',"orderable":true,"searchable":false},

      {
      render: function(data, type, row, meta)
      {
        return '<div class="checkbox checkbox-success"><input type="checkbox" id="checked_record" name="checked_record[]" value="'+row.enc_id+'" class="checkItem case"><label style="text-decoration: none"></label></div>';
      },
       "orderable": false,
       "searchable":false
      },

      {data: 'created_at', "orderable": true, "searchable":false},
      {data: 'title', "orderable": true, "searchable":false},
      {data: 'description', "orderable": true, "searchable":false},      
      {data: 'action', "orderable": true, "searchable":false}  

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
     $("#table_module").find("thead").append();

      /*`<tr>
                    <td></td>
                     <td><input type="text" name="q_enquiry_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" /></td>  

                      <td><input type="text" name="q_lead_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                </tr>`
*/
       $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });

  });

  function filterData()
  {
    table_module.draw();
    hideProcessingOverlay();
  }

  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this notification.');
  }

  $(document).ready(function() {

    $("input.checkItemAll").click(function(){
             
        if($(this). prop("checked") == true){
          $("input.checkItem").prop('checked',true);
        }
        else{
          $("input.checkItem").prop('checked',false);
        }

    });

});


  function toggleSelect()
  {
      $("input.checkItem").click(function()
      {  
          var checked_checkbox_length = $('input:checked[name="checked_record[]"]').map(function (){ return $(this).val(); } ).get();

          if(checked_checkbox_length.length < 10){
            $("input.checkItemAll").prop('checked',false);
          }
          else
          {
            $("input.checkItemAll").prop('checked',true);
          }

      });

  }

  $(document).ready(function()
  {
    $( function() {

        $( ".datepicker" ).datepicker({
          // format: 'yyyy-mm-dd',
          format: 'mm-dd-yyyy',
          viewMode: "days", 
          minViewMode: "days"
       });

        $('#order_from_date').datepicker('setEndDate', '+0d');
        $('#order_to_date').datepicker('setEndDate', '+0d');

        $("#order_from_date").datepicker({
          
          todayBtn:  1,
          autoclose: true,

        }).on('changeDate', function (selected) {
          var minDate = new Date(selected.date.valueOf());
          $('#order_to_date').datepicker('setStartDate', minDate);
        });
      
        $("#order_to_date").datepicker()
          .on('changeDate', function (selected) {
              var minDate = new Date(selected.date.valueOf());
              $('#order_from_date').datepicker('setEndDate', minDate);
          });
    
        });


      $("#order_from_date").on('change', function() {
          $("input[name=order_from_date]").parsley().reset();
      });

      $("#order_to_date").on('change', function() {
          $("input[name=order_to_date]").parsley().reset();
      });

  });

  function validateField()
  {
      var from_date = $('#order_from_date').val();
      var to_date   = $('#order_to_date').val();

      if(from_date == '')
      {
         $('#from_date_error').html('Please enter order from date.');
         return false;
      }

      if(to_date == '')
      {
         $('#to_date_error').html('Please enter order to date.');
         return false;
      }
     
      if(from_date == '' && to_date == '')
      {
         $('#from_date_error').html('Please enter order from date.');
         $('#to_date_error').html('Please enter order to date.');
         return false;
      }

      else
      {
        return true;
      }
  }


  function readNotification(ref)
  {
      var notification_id = $(ref).attr('data-id');
      var notification_id = btoa(notification_id);

      $.ajax({
          url:module_url_path+'/read_notification/'+notification_id,
          type:"GET",
          dataType:'json',
          success:function(response){
  
             window.location.href= response.url;
          },
          error:function(response){
              swal('error',response.description,'Error');     
          }


     });
  }


</script>

@stop 