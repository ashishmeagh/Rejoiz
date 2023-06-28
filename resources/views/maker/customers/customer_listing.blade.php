@extends('maker.layout.master')  

@section('main_content')

<style>
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
                <li><a href="">Customer Listing</a></li>
                <li class="active"></li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
        
    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">
          <div class="pull-right top_small_icon">
            
            {{-- <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add More"><i class="fa fa-plus"></i> </a> 
              
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-circle btn-danger btn-outline show-tooltip" title="Multiple Block"><i class="ti-lock"></i> </a> 

           {{--  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-circle btn-danger btn-outline show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>   --}}

            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>  
            
          </div>
          
            <form class="form-horizontal" id="frm_manage" method="POST">
              {{ csrf_field() }}
              <br>
              <br>
              <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                              <th>Retailer Id</th>
                              <th>Retailer Name</th>    
                              <th>Email</th>                   
                              <th>Last Quote</th>
                              <th>Total Quote</th>  
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

  var module_url_path  = "{{ $module_url_path }}";
  var quotes_module_path = "{{ url(config('app.project.maker_panel_slug').'/retailer_quotes') }}";
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
      'url': '{{ $module_url_path.'/get_quote_listing_data'}}',

      
      'data': function(d)
        {
          d['column_filter[q_retailer_id]']     = $("input[name='q_retailer_id']").val()          
          d['column_filter[q_username]']        = $("input[name='q_username']").val()          
          d['column_filter[q_email]']           = $("input[name='q_email']").val()      
          d['column_filter[q_last_quote_date]'] = $("input[name='q_last_quote_date']").val()  
          d['column_filter[q_total_quote]']     = $("input[name='q_total_quote']").val()
        }
      },

      columns: [
      
      {data: 'retailer_id', "orderable": true, "searchable":false},
      {data: 'user_name', "orderable": true, "searchable":false},      
      {data: 'email', "orderable": true, "searchable":false}, 
      {data: 'last_quote_date', "orderable": true, "searchable":false},       
      {
        render(data, type, row, meta)
        {
             return `<span class="badge badge-success">`+row.total_quotes+`</span>`;               
        },
        "orderable": false, "searchable":false
      },
       {
        render(data, type, row, meta)
        {
             return `<a href="`+quotes_module_path+`?retailer_id=`+btoa(row.retailer_id)+`" data-toggle="tooltip"  data-size="small" title="View Leads" class="btn btn-outline btn-info btn-circle show-tooltip">View Quotes</i></a>`;               
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
                    <td><input type="text" name="q_retailer_id" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_username" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>                    
                    <td><input type="text" name="q_email" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_last_quote_date" onchange="filterData();" placeholder="Search" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td>
                    <td><input type="text" name="q_total_quote" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>                    
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