@extends('admin.layout.master')                
@section('main_content')

<style>
  input[type="radio"], input[type="checkbox"] {vertical-align:super;}
  .dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
}

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}
/*
@keyframes spinner {
  to {transform: rotate(360deg);}
}
 
.spinner:before {
  content: '';
  box-sizing: border-box;
  position: absolute;
  top: 50%;
  left: 50%;
  width: 20px;
  height: 20px;
  margin-top: -10px;
  margin-left: -10px;
  border-radius: 50%;
  border: 2px solid #ccc;
  border-top-color: #333;
  animation: spinner .6s linear infinite;
} */ 

}

</style>

<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">
<!-- responsive datatable css -->
{{-- <link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/responsive.dataTables.min.css"> --}}


<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title">{{$module_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">
            <li><a href="{{url('/')}}/{{$curr_panel_slug}}/dashboard">Dashboard</a></li>
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
            <!-- <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add Category"><i class="fa fa-plus"></i> </a> 
               -->
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','approve');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Approve"><i class="ti-unlock"></i></a> 
            
           <!--  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','Reject');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Reject"><i class="ti-lock"></i> </a>  -->

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
                  <th>Image</th>          
                  <th>Name</th>                   
                  <th>Status</th> 
                  <th>Action</th>
                </tr>
  
               </thead>
             </table>
          </div>
        <div> </div>
         
          {!! Form::close() !!}
      </div>
  </div>
   </div>
</div>

<!-- END Main Content -->
<script type="text/javascript">
  var module_url_path = "{{$module_url_path}}";
  var image_url = "{{ url('/storage/app/')}}";
  var table_module;

  $(document).ready(function() 
  {
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
      processing: true,
      language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },    
      serverSide: true,
      responsive:true,
      bFilter: false ,
      stateSave: true,
            order : [[ 1, "desc" ]],
          
            ajax: {
                    url:module_url_path+'/get_all_request_category',

                   'data': function(d)
                        {
                          d['column_filter[q_category_name]'] = $("input[name='q_category_name']").val()

                          d['column_filter[q_status]']   = $("select[name='q_status']").val()
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
                 
                {
                    render : function(data, type, row, meta) 
                    {
                      return row.build_category_image;
                    },
                    "orderable": false, "searchable":false
                  },
                 {data: 'category_name',"orderable": false, "searchable":false},
                 
                 {data: 'admin_confirm_status',
                      orderable: false, 
                      searchable: false,
                      responsivePriority:4,

                    render(data, type, row, meta)
                    {   
                        if(row.admin_confirm_status == 1)
                        {
                           var content = row.reject_reason;

                           var reject_reason = $(content).text();

                            return `<span class="label label-warning" title="`+reject_reason+`">Rejected</span`;
                        }
                        else if(row.admin_confirm_status == 2)
                        {
                            return `<span class="label label-warning">Pending</span`
                        }
                    }
                  },

                  {                  
                    render(data, type, row, meta)
                    {
                         return `

                          <a href="`+module_url_path+`/view/`+row.enc_id+`" data-toggle="tooltip"  data-size="small" class="btn btn-circle btn-success btn-outline show-tooltip editstyle" title="View">View</a>                       

                             `;
                    },

                    "orderable": false, "searchable":false
                  }],
            
       });


        $('#table_module').on('draw.dt',function(event)
        {
            toggle_switch();
            toggleSelect();
            
        });

        $("#table_module").find("thead").append(`<tr>
                    <td></td>
                     <td></td>
                    <td><input type="text" name="q_category_name" placeholder="Search" class="search-block-new-table column_filter form-control" /></td>
                    
                    <td>
                       <select class="search-block-new-table td-select-dropdown column_filter form-control" name="q_status" id="q_status" onchange="filterData();">
                        <option value="">All</option>
                        <option value="1">Rejected</option>
                        <option value="2">Pending</option>
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

    function toggle_switch()
    {
        // Switchery
        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
            new Switchery($(this)[0], $(this).data());
        });  
    }

    function filterData()
    {
       table_module.draw();
    }


    function show_details(url)
    { 
        window.location.href = url;
    } 


  
  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this category.');
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


/*function toggleSelect()
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

}*/

  function change_status(reference)
  { 
      msg = '';
    
      action = $(reference).attr('action');
       
      if(action == 'activate')
      {
         var msg = 'Are you sure? Do you want to activate this category.';
      }
      else if(action == 'deactivate')
      {
        var msg = 'Are you sure? Do you want to deactivate this category.'; 
      }


      swal({  
                title:'Need Confirmation',
                text : msg,
                type : "warning",
                showCancelButton: true,                
                confirmButtonColor: "#8CD4F5",
                confirmButtonText: "OK",
                closeOnConfirm: true
            },
            function(isConfirm,tmp)
            {
                if(isConfirm==true)
                {
                    var enc_id = reference.getAttribute('data-enc_id');

                    var status = reference.getAttribute('data-type');
                      
                     
                    $.ajax({
                          url:'{{url($module_url_path.'/change_status')}}',
                          type: 'GET',
                          dataType:'json',
                          data:{category_id:enc_id,status:status},
                          beforeSend: function() 
                          {
                           showProcessingOverlay();                 
                          },

                          success:function(data)
                          {
                              hideProcessingOverlay();   
                              if(data.status =='success')
                              {    
                                  swal({
                                        title: "Success",
                                        text: data.message,
                                        type: data.status,
                                        confirmButtonText: "OK",
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
                                  swal('Error', data.message, data.status);
                              }
                          }
                    });

                }
                else{
                    $(reference).trigger('click');
                }


            }); 



  }





</script>
@stop                    


