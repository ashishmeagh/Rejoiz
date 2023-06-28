@extends('admin.layout.master')                

@section('main_content')

<style type="text/css">

th {
    white-space: nowrap;
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
                <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
                <li class="active">{{$module_title or ''}}</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
        
    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">
        	<div class="pull-right top_small_icon">
             {{-- <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add More"><i class="fa fa-plus"></i> </a>  --}}
              

             <a  href="{{url('/')}}/admin/retailer/report_generator/csv" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a CSV"><i class="fa fa-file-text"></i></a> 

             <a  href="{{url('/')}}/admin/retailer/report_generator/xlsx" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a xlsx"><i class="fa fa-file-excel-o"></i></a> 


              <a  href="{{url('/')}}/admin/retailer/report_generator/pdf" class="btn btn-outline btn-info btn-circle show-tooltip" title="Export as a PDF"><i class="fa fa-file-pdf-o"></i></a> 
 

            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

           {{--  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>   --}}

            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            
          </div>
          
            <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
              <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                          	 <th>
                          	 	<div class="checkbox checkbox-success"><input type="checkbox" name="checked_record_all[]" id="checked_record_all" class="case checkItemAll"/><label for="checkbox"></label>
                          	 	</div>
				              </th>
                              <th>Name</th>
                              <th>Email Id</th>    
                              <th>Contact No.</th>  
                              <th>Registration Date</th>                        
                              <th>User Email Status</th>
                              <th>Admin Approval Status</th>
                              <th>Net30 Status</th>
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
      stateSave: true,
     
      bFilter: false ,
      ajax: {
      'url':'{{ $module_url_path.'/get_retailer'}}',
      'data': function(d)
        {
          d['column_filter[q_email]']      = $("input[name='q_email']").val()
          d['column_filter[q_contact_no]'] = $("input[name='q_contact_no']").val()
          d['column_filter[q_username]']   = $("input[name='q_username']").val()
          d['column_filter[q_status]']     = $("select[name='q_status']").val()
          d['column_filter[q_is_approved]']= $("select[name='q_is_approved']").val()
          d['column_filter[q_status_net_30]']= $("select[name='q_status_net_30']").val()
          d['column_filter[q_date]']       = $("input[name='q_date']").val()          
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
      // {data: 'user_name', "orderable": true, "searchable":false},
      {
        render: function(data, type, row, meta)
        {
           return `<a href="`+module_url_path+`/view/`+row.enc_id+`" class="link_v">`+row.user_name+`</a>`;
        },
         "orderable": false,
         "searchable":false
      },
      {data: 'email', "orderable": true, "searchable":false},      

      {data: 'contact_no', "orderable": true, "searchable":false},   

      {data: 'registration_date', "orderable": true, "searchable":false},

      {
        render : function(data, type, row, meta) 
        {
          return row.build_status_btn;
        },
        "orderable": false, "searchable":false
      },

     
      
      {
         data : 'is_approved',  
         render : function(data, type, row, meta) 
         { 
           if(row.is_approved == '1')
           {
             return `<input type="checkbox" checked data-size="small" class="js-switch" onchange='changeAprovalStatus(`+row.id+`,$(this))' data-color="#99d683" data-secondary-color="#f96262" action="deactivate" data-type="deactivate"/>`
           }
           else
           {
             return `<input type="checkbox" data-size="small" class="js-switch" onchange='changeAprovalStatus(`+row.id+`,$(this))' data-color="#99d683" data-secondary-color="#f96262" action="activate" data-type="activate"/>`
           }
         },
         "orderable": false,
         "searchable":false
       }, 

      {
        render : function(data, type, row, meta) 
        {
          return row.build_status_net_30;
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

       $("input.net_30").change(function(){
          net_30_statusChange($(this));
       });  

       toggleSelect();

    });



    /*search box*/
     $("#table_module").find("thead").append(`<tr>
                    <td></td>

                    <td><input type="text" name="q_username" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td><input type="text" name="q_email" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>

                    <td><input type="text" name="q_contact_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>

                    <td><input type="text" name="q_date" placeholder="Search" onchange="filterData();" class="search-block-new-table column_filter form-control-small datepicker" readonly/></td> 

                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Active</option>
                        <option value="0">Deactivate</option>
                        </select>
                    </td>   

                     <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_is_approved" id="q_is_approved" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Approved</option>
                        <option value="0">Disapproved</option>
                        </select>
                    </td> 

                    <td>
                       <select class="search-block-new-table column_filter form-control-small" name="q_status_net_30" id="q_status_net_30" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Approved</option>
                        <option value="0">Disapproved</option>
                        </select>
                    </td> 
                    
                    <td></td>

                </tr>`);

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });


        $("#table_module").on('page.dt', function (){

          var info = table_module.page.info();
         
          $("input.checkItemAll").prop('checked',false);
      
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
      var ref    = data; 
      var type   = data.attr('data-type');
      var enc_id = data.attr('data-enc_id');
      var id     = data.attr('data-id');
      var msg    = '';

    if(type ==  'activate')
    {
      msg        = "Are you sure? Do you want to activate this Customer email status.";
    }

    else if(type == 'deactivate')
    {
       msg       = "Are you sure? Do you want to deactivate this Customer email status."; 
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
                  beforeSend : function()
                  {
                    showProcessingOverlay();
                  },
                  success: function(response)
                  {   
                      hideProcessingOverlay();
                      if(response.status=='SUCCESS')
                      {

                        if(response.data=='ACTIVE')
                        {
                          $(ref)[0].checked = true;  
                          $(ref).attr('data-type','deactivate');
                          // swal('Success','Retailer has been activated.','success');
                          swal({
                                  title:"Success",
                                  text: "Customer email status has been activated.",
                                  type: "success",
                                  showCancelButton: false,                
                                  confirmButtonColor: "#8CD4F5",
                                  confirmButtonText: "OK",
                                  closeOnConfirm: false
                            },
                            function(isConfirm,tmp)
                            {
                              if(isConfirm==true)
                              {
                                location.reload();
                              }
                            });

                        }else if(response.data=="DEACTIVE")
                        {
                          $(ref)[0].checked = false;  
                          $(ref).attr('data-type','activate');
                          swal('Success','Customer email status has been deactivated.','success');
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
            $(data).trigger('click');
          }
      });      
}


function net_30_statusChange(data)
{

    var ref    = data; 
    var type   = data.attr('data-type');
    var enc_id = data.attr('data-enc_id');
    var id     = data.attr('data-id');
    var msg    = '';


    if(type ==  'net_30_activate')
    {
      msg      = "Are you sure? Do you want to activate net30 status.";
    }

    else if(type == 'net_30_deactivate')
    {
       msg     = "Are you sure? Do you want to deactivate net30 status. "; 
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
                beforeSend : function()
                {
                    showProcessingOverlay();
                },
                success: function(response)
                {
                   hideProcessingOverlay();
                  if(response.status=='SUCCESS')
                  {
                    if(response.data=='ACTIVE')
                    {
                      $(ref)[0].checked = true;  
                      $(ref).attr('data-type','net_30_deactivate');

                      swal('Success','Net30 status has been activated.','success');


                    }else if(response.data=='DEACTIVE')
                    {

                      $(ref)[0].checked = false;  
                      $(ref).attr('data-type','net_30_activate');

                      swal('Success','Net30 status has been deactivated.','success');
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
    action = $(ref).attr('action');
    msg    = '';
    var type    = $(ref).attr('data-type');
   
    if(type == 'activate')
    {
       var msg = 'Are you sure? Do you want to approve this user.';
    }
    else 
    {
       var msg = 'Are you sure? Do you want to disapprove this user.'; 
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
                data     : {retailer_id:retailer_id,retailerAprovalStatus:retailerAprovalStatus,type : type},
                url      : module_url_path+'/changeAprovalStatus',
                beforeSend : function()
                {
                    showProcessingOverlay();
                },
                success  : function(response)
                {   
                   hideProcessingOverlay();
                  if(typeof response == 'object' && response.status == 'SUCCESS')
                  { 
                    //swal('Success',response.message,'success');
                      if(response.data=='ACTIVE')
                      {
                        $(ref)[0].checked = true;  
                        $(ref).attr('data-type','deactivate');
                        swal('Success',response.message, 'success');

                      }
                      else
                      {
                        $(ref)[0].checked = false;  
                        $(ref).attr('data-type','activate');
                        swal('Success',response.message, 'success');
                      }
                  }
                  else
                  {
                    swal('Error',response.message,'error');
                  }               
                },
                error:function(response)
                {
                  swal('Error','Something went wrong,please try again.','error');
                }

            });
        }
        else
        {
          $(ref).trigger('click');
        }

    });    

      
  }



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



/*-----------------------------------------------------------------------------*/
</script>

@stop 