@extends('maker.layout.master')                
@section('main_content')
<style>
.table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #666;
    background-color: transparent;
    /*color: #666;*/
}
</style>

<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">


<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-sm-12 top-bg-title">
         <h4 class="page-title">{{$module_title or ''}}</h4>
         <div class="right">
         <ol class="breadcrumb">
            {{-- <li><a href="{{$module_url_path or ''}}/dashboard">Dashboard</a></li> --}}
            <li><a href="{{url('/')}}/{{$maker_panel_slug or ''}}/dashboard">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
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

        {{--   <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info show-tooltip" title="Add Catalog Name"> Add Catalog Name</a>  --}}

          <div class="row">
            <div class="col-sm-12 pull-right manage_catalog_top_small_box">
       

            <div class="pull-right top_small_icon">

                <a  href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add new catalog"><i class="ti-plus"></i></a> 
                
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','block');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Block"><i class="ti-lock"></i> </a> 

                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a>
                
                <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            </div>
            
          </div>
          </div>
          <div class="row">
            <div class="col-sm-12 table-responsive">
            <input type="hidden" name="multi_action" value="" />
            
            <table class="table table-striped"  id="table_module" >
              <thead>
                <tr>
                  <th>
                    <div class="checkbox checkbox-success">

                      <input class="checkItemAll" id="checkbox0" type="checkbox">
                      <label for="checkbox0">  </label>
                    </div>
                  </th>
                  <th>Name</th>
                  <th>Status</th> 
                  <th>Action</th>
                </tr>
  
               </thead>
             </table>
          </div>
          </div>
        <div> </div>
         
          {!! Form::close() !!}
      </div>
  </div>
</div>
</div>

<!-- END Main Content -->
<script type="text/javascript">

  var module_url_path = "{{$module_url_path or ''}}";
  var module_url_path_images = "{{$module_url_path_images}}";
  var table_module;
  
  $(document).ready(function() 
  {
      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
      processing: true,
      serverSide: true,
      responsive:true,
      bFilter: false ,
      stateSave: true,
            order : [[ 1, "desc" ]],
            
            ajax: {
                    url:module_url_path+'/get_catlogs',

                   'data': function(d)
                        {
                          d['column_filter[q_catalog_name]'] = $("input[name='q_catalog_name']").val()
                          d['column_filter[q_status]']       = $("select[name='q_status']").val()

                        }
            
                  },
            
            columns: [

                  {
                     render : function(data, type, row, meta) 
                     {
                        return '<div class="checkbox checkbox-success"><input type="checkbox" '+
                             ' name="checked_record[]" '+  
                             ' value="'+row.enc_id+'" id="checkbox'+row.id+'" class="case checkboxInput"/><label for="checkbox'+row.id+'">  </label></div>';
                     },
                     "orderable": false,
                     "searchable":false
                  },
      
                  {data: 'catalog_name',"orderable": true, "searchable":false},
             
                  {data: 'is_active',
                      orderable: false, 
                      searchable: false,
                      responsivePriority:4,

                    render(data, type, row, meta)
                    {   
                        if(row.is_active == 1)
                        {
                            return `<input type="checkbox" checked data-size="small"  data-enc_id="`+btoa(row.id)+`" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>`
                        }
                        else
                        {
                            return `<input type="checkbox" data-size="small" data-enc_id="`+btoa(row.id)+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>`
                        }
                    }
                  },

                  {                  
                    render(data, type, row, meta)
                    {
                         return `
                          <a href="`+module_url_path+`/edit/`+btoa(row.id)+`" data-toggle="tooltip"  data-size="small" title="Edit Catalog" class="btn btn-circle btn-success btn-outline show-tooltip">Edit</a>

                            <a href="`+module_url_path_images+`/view/`+btoa(row.id)+`" data-toggle="tooltip"  data-size="small" title="View Catalog" class="btn btn-circle btn-success btn-outline show-tooltip">View</a>
                            
                           <a href="`+module_url_path+`/delete/`+btoa(row.id)+`" data-toggle="tooltip"  data-size="small" title="Delete Catalog" class="btn btn-outline btn-primary btn-circle show-tooltip btn-retailer-view" onclick="confirm_delete(this,event);">Delete</a>

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

  

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });

        $("#table_module").on('page.dt', function (){

        var info = table_module.page.info();
       
        $("input.checkItemAll").prop('checked',false);
    
        });


        /*search box*/
        $("#table_module").find("thead").append(`<tr>   

                <td></td>       
                <td><input type="text" name="q_catalog_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

                <td>
                  <select name="q_status" class="search-block-new-table column_filter form-control td-select-dropdown" id="q_status" onchange="filterData();">
                   <option value="">Select Status</option>
                   <option value="0">Block</option>
                   <option value="1">Active</option>
                  </select>
                </td>

                <td></td>        

            </tr>`);

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
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

  function change_status(reference)
  {
      var enc_id = reference.getAttribute('data-enc_id');
       var status = reference.getAttribute('data-type');

       $.ajax({
           url:module_url_path+'/change_status',
           type: 'GET',
           dataType:'json',
           data:{catalog_id:enc_id,status:status},
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
                      text: data.description,
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
                   swal("Error", data.description, data.status);
               }
           }
       });
        
    }
  
  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this catalog.');
  }



 $(function(){

    $("input.checkItemAll").click(function(){
         
        if($(this). prop("checked") == true){
          $("input.checkboxInput").prop('checked',true);
        }
        else{
          $("input.checkboxInput").prop('checked',false);
        }

    });

});

</script>
@stop                    


