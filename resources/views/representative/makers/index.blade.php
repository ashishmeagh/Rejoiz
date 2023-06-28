@extends('representative.layout.master')  
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
                <li><a href="{{ url(config('app.project.representative_panel_slug').'/dashboard') }}">Dashboard</a></li>
                <li class="active">{{$module_title or ''}}</li>
            </ol>
        </div>
        <!-- /.col-lg-12 -->
    </div>
      {{-- {{dd($module_url_path)}}   --}}
    <div class="row">
      <div class="col-sm-12">
           @include('admin.layout._operation_status')
        <div class="white-box">
        	<div class="pull-right">           
          </div>
          
              <br>
              <br>
              <div class="table-responsive">
              <input type="hidden" name="multi_action" value="" />
                  <table id="table_module" class="table table-striped">
                      <thead>
                          <tr>
                          	  
                              <th>Vendor</th>
                              <th>Vendor Company</th>
                              <th>Email</th>    
                              <th>Contact No</th>                          
                              <th>Action</th>
                          </tr>
                      </thead>
                      <tbody>
                      </tbody>
                  </table>
              </div>
          {{--   </form> --}}
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
      'url':'{{ $module_url_path.'/makers_listing'}}',
      'data': function(d)
        {
          d['column_filter[q_name]']       = $("input[name='q_name']").val()
          d['column_filter[q_brand_name]'] = $("input[name='q_brand_name']").val()
          d['column_filter[q_email]']      = $("input[name='q_email']").val()
          d['column_filter[q_contact_no]'] = $("input[name='q_contact_no']").val()
                   
        }
      },

      columns: [
      
  
     
      {data: 'user_name', "orderable": true, "searchable":false},
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
      // {data: 'company_name', "orderable": true, "searchable":false},

      {data: 'email', "orderable": true, "searchable":false},
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
                   
                    <td><input type="text" name="q_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_brand_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
                    <td><input type="text" name="q_email" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_contact_no" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    
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