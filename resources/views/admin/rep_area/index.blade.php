@extends('admin.layout.master')                
@section('main_content')

<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">

<!-- responsive datatable css -->
{{-- <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/responsive.dataTables.min.css"> --}}


<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title"> {{ $module_title or ''}} </h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">

            <li><a href="{{url('/')}}/{{$curr_panel_slug}}/dashboard">Dashboard</a></li>

            {{-- <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li> --}}

            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
   </div>
         @include('admin.layout._operation_status')
   <!-- BEGIN Main Content -->
   <div class="row">
   <div class="col-sm-12">
      <div class="white-box">
          {!! Form::open([ 'url' => $module_url_path.'/multi_action',
                                 'method'=>'POST',
                                 'enctype' =>'multipart/form-data',   
                                 'class'=>'form-horizontal', 
                                 'id'=>'frm_manage' 
                                ]) !!} 

            {{ csrf_field() }}

          <div class="pull-right top_small_icon">
            <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add Area"><i class="fa fa-plus"></i> </a> 
              
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a> 

            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            
          </div>
          <div class="table-responsive">
            <input type="hidden" name="multi_action" value="" />
            
            <table class="table table-striped"  id="table_module" >
              <thead>
                <tr>
                  <th>
                   <div class="checkbox checkbox-success">
                    <input type="checkbox" name="checked_record_all[]" id="checked_record_all" class="case checkItemAll"/><label for="checkbox"></label></div>
                  </th>  
                  <th>Area Name</th>                   
                  <th>Status</th> 
                  <th width="200px">Action</th>
                </tr>
  
               </thead>
             </table>
          </div>
        
         
          {!! Form::close() !!}
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
      bFilter: false,
      stateSave: true,

      ajax: {
      'url': module_url_path+'/get_area_list',
      'data': function(d)
        {
        
          d['column_filter[q_area_name]']      = $("input[name='q_area_name']").val()
          d['column_filter[q_status]']         = $("select[name='q_status']").val()         
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


      {data: 'area_name', "orderable": true, "searchable":false},

     {data: 'status',
                      orderable: false, 
                      searchable: false,
                      responsivePriority:4,

                    render(data, type, row, meta)
                    {   
                        if(row.status == 1)
                        {
                            return `<input type="checkbox" checked data-size="small"  data-enc_id="`+row.enc_id+`" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="statusChange(this)"/>`
                        }
                        else
                        {
                            return `<input type="checkbox" data-size="small" data-enc_id="`+row.enc_id+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="statusChange(this)"/>`
                        }
                    }
                  },

      {                  
        render(data, type, row, meta)
        {
            
             return `<a href="`+module_url_path+`/edit/`+row.enc_id+`" data-toggle="tooltip"  data-size="small" title="Edit" class="btn btn-circle btn-success btn-outline show-tooltip">Edit</a>

               <a href="`+module_url_path+`/delete/`+row.enc_id+`" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-info btn-circle show-tooltip" onclick="confirm_delete(this,event);">Delete</a>`;
        },
        "orderable": false, "searchable":false
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
                    <td><input type="text" name="q_area_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>  

                    <td>
                      <select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
                          <option value="">All</option>
                          <option value="1">Active</option>
                          <option value="0">Deactive</option>
                      </select>
                    </td>
                   
                    <td></td>                                       
                   
                </tr>`);

       $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
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

  function filterData()
  {
    table_module.draw();
  }

  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this area.');
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
  function statusChange(data)
  { 
          var ref = $(data); 
          var type = $(data).attr('data-type');
          var enc_id = $(data).attr('data-enc_id');
          var id = $(data).attr('data-id');

          $.ajax({
              url:module_url_path+'/'+type,
              type:'GET',
              data:{id:enc_id},
              dataType:'json',
              success: function(response)
              { 
                if(response.status=='SUCCESS')
                {
                  console.log(response.data);
                  if(response.data=='ACTIVE')
                  {
                    $(ref)[0].checked = true;  
                    $(ref).attr('data-type','deactivate');

                     swal('Success','Status has been activated.','success');

                  }else
                  {
                    $(ref)[0].checked = false;  
                    $(ref).attr('data-type','activate');
                    swal('Success','Status has been deactivated.','success');
                  }

                  
                }
                else if(response.status == 'WARNING')
                {
                   swal('Error',"Area can't deactivated first delete from sales manager and representative",'error');
                }  
              }
          }); 
}





</script>



@stop