@extends('admin.layout.master')                   
@section('main_content')

<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">

<style>
.table.table-striped tr td .btn-success.btn-outline {
    border: 1px solid #666;
    background-color: transparent;
    color: #666;
}
</style>

<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title">{{$module_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">          
            <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
            <li class="active">{{$module_title or ''}}</li>
         </ol>
      </div>
   </div>
   <!-- BEGIN Main Content -->
   <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
         @include('admin.layout._operation_status')
          {!! Form::open([ 'url' => $module_url_path.'/multi_action',
                                 'method'=>'POST',
                                 'enctype' =>'multipart/form-data',   
                                 'class'=>'form-horizontal', 
                                 'id'=>'frm_manage' 
                                ]) !!} 

            {{ csrf_field() }}
   
          <div class="col-sm-12">
          
            <div class="pull-right top_small_icon">
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

               {{--  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a> --}}
                
                <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a> 
            </div>
            
          </div>
          <br/>
          <br>
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
                   
                   
                  <th>Order No</th>
                  <th>Order Date</th>
                  <th>Order Status</th>
                  <th>Rep/Sales Manager</th>
                  <th>Vendor</th>
                  <th>Retailer</th>
                  <th>Products</th>
                  <th>Retailer Payment Status</th>
                  <th>Shipping Status</th>
                  <th>Total Amount</th>

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

  var module_url_path = "{{$module_url_path or ''}}";
  var table_module;

  // console.log(module_url_path);

  // $(document).ready(function() 
  // { 
     
  //     table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
  //     processing: true,
  //     serverSide: true,
  //     responsive:true,
  //     bFilter: false ,
  //     stateSave: true,
  //           order : [[ 1, "desc" ]],
            
  //           ajax: {
  //                   url:module_url_path+'/get_influencer_list',

  //                  'data': function(d)
  //                       {
  //                         d['column_filter[q_user_name]']    = $("input[name='q_user_name']").val()
  //                         d['column_filter[q_email]']        = $("input[name='q_email']").val()
  //                         d['column_filter[q_contact_no]']   = $("input[name='q_contact_no']").val()
  //                         d['column_filter[q_status]']       = $("select[name='q_status']").val()
  //                       }
  //                 },
            
  //           columns: [

  //                 {
  //                    render : function(data, type, row, meta) 
  //                    {
  //                         return '<div class="checkbox checkbox-success"><input type="checkbox" '+
  //                            ' name="checked_record[]" '+  
  //                            ' value="'+row.id+'" id="checkbox'+row.id+'" class="case checkboxInput"/><label for="checkbox'+row.id+'">  </label></div>';
                        
  //                    },
  //                    "orderable": false,
  //                    "searchable":false
  //                 },
      
                  
  //                 {data: 'user_name', "orderable": false, "searchable":false},
  //                 {data: 'email', "orderable": false, "searchable":false},
  //                 {data: 'contact_no', "orderable": false, "searchable":false},
               

                 
  //                 {data: 'status',
  //                     orderable: false, 
  //                     searchable: false,
  //                     // responsivePriority:4,

  //                   render(data, type, row, meta)
  //                   {   
                        
  //                         if(row.status == 1)
  //                         {
  //                             return `<input type="checkbox" checked data-size="small"  data-enc_id="`+row.id+`" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>`
  //                         }
  //                         else
  //                         {
  //                             return `<input type="checkbox" data-size="small" data-enc_id="`+row.id+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>`
  //                         }
                        
                        
  //                   }
  //                 },

  //                 {                  
  //                   render(data, type, row, meta)
  //                   {
                       
  //                       return  `

  //                         <a href="`+module_url_path+`/view/`+row.id+`" data-toggle="tooltip"  data-size="small" title="View" class="btn btn-cirle btn-success btn-outline show-tooltip">View</a>
  //                       `;

  //                        // <a href="`+module_url_path+`/delete/`+row.id+`" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-primary btn-circle show-tooltip btn-retailer-view" onclick="confirm_delete(this,event);">Delete</a>                        
  //                   },

  //                   "orderable": false, "searchable":false
  //                 }],
            
  //      });



  //       /*$('#table_module').on('draw.dt',function(event)
  //       {
  //           toggle_switch();
  //           toggleSelect();
  //       });*/

  

  //       $('input.column_filter').on( 'keyup click', function () 
  //       {
  //            filterData();
  //       });

  //       /*$("#table_module").on('page.dt', function (){

  //       var info = table_module.page.info();
       
  //       $("input.checkItemAll").prop('checked',false);
    
  //       });*/

  //       $('input.column_filter').on( 'keyup click', function () 
  //       {
  //            filterData();
  //       });

  // });

  function filterData()
  {
    table_module.draw();
  }
  
</script>
@stop                    


