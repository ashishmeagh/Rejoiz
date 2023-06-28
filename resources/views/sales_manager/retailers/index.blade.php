@extends('sales_manager.layout.master')  
@section('main_content')
<style>
  
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
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">My Customers</h4> </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12"> 
            <ol class="breadcrumb">
                <li><a href="{{ url(config('app.project.representative_panel_slug').'/dashboard') }}">Dashboard</a></li>
                <li class="active">My Customers</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>

    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">       
        	<div class="pull-right top_small_icon">


            <a href="{{ url($module_url_path.'/create') }}" class="btn btn-circle btn-success btn-outline show-tooltip" title="Add More"><i class="fa fa-plus"></i> </a> 
            
             <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

        {{--     <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>  
 --}}
            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-circle btn-success btn-outline show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>
            
          </div>

              
           
          <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">

            {{ csrf_field() }}
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
                              <th>Name</th>
                              <th>Email Id</th>    
                              <th>Contact No</th>   
                              <th>Store Name</th>
                              <th>Admin Approval</th>
                              <th>Status</th>                       
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

  var lead_module_url_path = "{{$lead_module_url_path or ''}}";

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
      order: [ 0, 'desc' ],
      bFilter: false ,
      ajax: {
      'url':'{{ $module_url_path.'/retailer_list'}}',
      'data': function(d)
        {
          d['column_filter[q_name]']            = $("input[name='q_name']").val()
          d['column_filter[q_email]']           = $("input[name='q_email']").val()
          d['column_filter[q_contact_no]']      = $("input[name='q_contact_no']").val()
          d['column_filter[q_store_name]']      = $("input[name='q_store_name']").val()
          d['column_filter[q_status]']          = $("select[name='q_status']").val()
          d['column_filter[q_is_approved]']     = $("select[name='q_is_approved']").val()
                   
        }
      },

      columns: [
      {
      render: function(data, type, row, meta)
      {
        return '<div class="checkbox checkbox-success"><input type="checkbox" id="checked_record" name="checked_record[]" value="'+row.enc_id+'" class="checkItem case"><label style="text-decoration: none"></label></div>';
      },
       "orderable": false,
       "searchable":false
      },
      
      {data: 'user_name', "orderable": true, "searchable":false},
      {data: 'email', "orderable": true, "searchable":false},      
      {data: 'contact_no', "orderable": true, "searchable":false}, 
      {data: 'store_name', "orderable": true, "searchable":false}, 

      {
        render : function(data, type, row, meta) 
        {
          return row.admin_approval;
        },
        "orderable": false, "searchable":false
      },

      {data : 'status',

          render : function(data, type, row, meta) 
          { 
              if(row.status == 1)
              {
                  return `<input type="checkbox" checked data-size="small" class="js-switch" onchange="changeStatus($(this))" data-color="#99d683" data-secondary-color="#f96262" data-enc_id=`+btoa(row.id)+` data-type="deactivate"/>`

              }
              else
              {
                  return `<input type="checkbox" data-size="small" class="js-switch" onchange="changeStatus($(this))" data-color="#99d683" data-secondary-color="#f96262" data-enc_id=`+btoa(row.id)+` data-type="activate" />`
               
              }

          },
          "orderable": false,
          "searchable":false
      },        
      
      {
        render(data, type, row, meta)
        {

             return `<a href="`+module_url_path+`/view_customer/`+btoa(row.id)+`"  data-size="small" title="View Product Details" class="btn btn-circle btn-success btn-outline show-tooltip">View</i></a>
             <a href="`+module_url_path+`/edit_customer/`+btoa(row.id)+`"  data-size="small" title="Edit Customer Details" class="btn btn-circle btn-success btn-outline show-tooltip">Edit</i></a>
             `;               
        },
        "orderable": true, "searchable":false

      }]


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
          
       toggleSelect();
    });



    /*search box*/
     $("#table_module").find("thead").append(`<tr>
                    <td></td>
                    <td><input type="text" name="q_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                    <td><input type="text" name="q_email" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td><input type="text" name="q_contact_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                     <td><input type="text" name="q_store_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_is_approved" id="q_is_approved" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Approved</option>
                        <option value="0">Disapproved</option>
                        </select>
                    </td>
                    
                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Deactivate</option>
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
  

  function changeStatus(reference)
  {
      var retailer_id = $(reference).attr('data-enc_id');

      var status = $(reference).attr('data-type');
      var type = $(reference).attr('data-type');
      var msg    = '';
      if(type ==  'activate')
      {
        msg        = "Are you sure? Do you want to activate this retailer status.";
      }
      else if(type == 'deactivate')
      {
        msg       = "Are you sure? Do you want to deactivate this retailer status. "; 
      }

      swal({
        title: "Need Confirmation",
        text: msg,
        type: "warning",
        showCancelButton: true,                
        confirmButtonColor: "#8CD4F5",
        confirmButtonText: "OK",
        closeOnConfirm: true
      },
      function(isConfirm,tmp)
      {
        if(isConfirm==true)
        { 
            
              $.ajax({
                        url:'{{url($module_url_path.'/change_status')}}',
                        type: 'GET',
                        dataType:'json',
                        data:{retailer_id:retailer_id,status:status},

                        success:function(data)
                        {
                          
                            if(data.status =='success')
                            {    
                                swal({
                                      title: "Success",
                                      text: data.message,
                                      type: data.status,
                                      confirmButtonText: "Ok",
                                      closeOnConfirm: false
                                    },
                                    function(isConfirm) {
                                      if (isConfirm) 
                                      {
                                         location.reload();
                                      } 
                                    });
                            }
                            else
                            {   
                                swal("Error", data.message, data.status);
                            }
                        }
                });
              }
          else
          {
             $(reference).trigger('click');
          }
      });    
  }
 


function changeAprovalStatus(retailer_id,ref)
{   
     var retailerAprovalStatus = '';

     if($(ref).is(":checked"))
     {
       retailerAprovalStatus = '1';
     }
     else
     {
      retailerAprovalStatus = '0';
     }
     
     $.ajax({
         method   : 'GET',
         dataType : 'JSON',
         data     : {retailer_id:retailer_id,retailerAprovalStatus:retailerAprovalStatus},
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