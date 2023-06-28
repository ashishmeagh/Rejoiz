@extends('sales_manager.layout.master')  
@section('main_content')

<style>
.table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #666;
    background-color: transparent;
    color: #666;
}

.dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
}

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
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
                <li><a href="{{ url(config('app.project.sales_manager_panel_slug').'/dashboard') }}">Dashboard</a></li>
                <li class="active">My {{$module_title or ''}}</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>


    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">
        	<div class="pull-right top_small_icon">  
            <a href="{{ url($module_url_path.'/create') }}" class="btn btn-circle btn-success btn-outline show-tooltip" title="Add Vendor"><i class="fa fa-plus"></i> </a> 
             <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-circle btn-success btn-outline show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

             <a href="{{$module_url_path}}" onclick="" class="btn btn-circle btn-success btn-outline show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>
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
                              <th>
                              <div class="checkbox checkbox-success">
                                <input type="checkbox" name="checked_record_all[]" id="checked_record_all" class="case checkItemAll"/><label for="checkbox"></label>
                              </div>
                              </th>
                              <th>Vendor</th>
                              <th>Email Id</th>    
                              <th>Company</th>
                              <th>Contact No</th>  
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
      'url':'{{ $module_url_path.'/vendors_listing'}}',
      'data': function(d)
        {
          d['column_filter[q_name]']            = $("input[name='q_name']").val()
          d['column_filter[q_email]']           = $("input[name='q_email']").val()
          d['column_filter[q_brand_name]']      = $("input[name='q_brand_name']").val()
          d['column_filter[q_contact_no]']      = $("input[name='q_contact_no']").val()
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

      // {data: 'company_name', "orderable": true, "searchable":false},

      {data: 'email', "orderable": true, "searchable":false},

      {
         render : function(data, type, row, meta)
         {
          if (row.company_name) {

            return row.company_name;
          }
          else{

           return 'N/A';
          }

         },
         "orderable": true, "searchable":false
     },
      {
         render : function(data, type, row, meta)
         {
          if (row.contact_no) {

            return row.contact_no;
          }
          else{

           return 'N/A';
          }

         },
         "orderable": true, "searchable":false
      }, 

      {
        render : function(data, type, row, meta) 
        {
          return row.admin_approval;
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
         "orderable": true, "searchable":false
     }    
      
      ]
    });

    $('input.column_filter').on( 'keyup click', function () 
    {
        filterData();
    });

    $("#table_module").on('page.dt', function (){

      var info = table_module.page.info();
     
      $("input.checkItemAll").prop('checked',false);
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

                     <td><input type="text" name="q_brand_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td><input type="text" name="q_contact_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

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

  function confirm_add(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to perform this action.');
  }
  
  function statusChange(data)
  {

      var ref = data; 
      var type = data.attr('data-type');
      var enc_id = data.attr('data-enc_id');
      var id = data.attr('data-id');

      var msg    = '';
      if(type ==  'activate')
      {
        msg        = "Are you sure? Do you want to activate this vendor status.";
      }
      else if(type == 'deactivate')
      {
        msg       = "Are you sure? Do you want to deactivate this vendor status. "; 
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
                        swal('Success','Vendor has been activated.','success');

                      }else
                      {
                        $(ref)[0].checked = false;  
                        $(ref).attr('data-type','activate');
                        swal('Success','Vendor has been deactivated.','success');

                      }

                    }
                    else
                    {
                      sweetAlert('Error','Something went wrong,please try again.','error');
                    }  
                  }
              }); 
          }
          else
          {
             $(ref).trigger('click');
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


 $(function(){
   
    $("input.checkItemAll").click(function(){
             
        if($(this). prop("checked") == true){
          $("input.checkItem").prop('checked',true);
        }
        else{
          $("input.checkItem").prop('checked',false);
        }

    });

});

</script>

@stop 