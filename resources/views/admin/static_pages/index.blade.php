@extends('admin.layout.master')                
@section('main_content')
<!-- BEGIN Page Title -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">


<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
               <li class="active">{{$module_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <div class="row">
         <div class="col-sm-12">
            <div class="white-box">
               @include('admin.layout._operation_status')
               {!! Form::open([ 'url' => $module_url_path.'/multi_action',
               'method'=>'POST',
               'class'=>'form-horizontal', 
               'id'=>'frm_manage' 
               ]) !!}     
               <div class="pull-right top_small_icon">
                  <a href="{{ url($module_url_path.'/create') }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add CMS"><i class="fa fa-plus"></i> </a>
                  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a>
                   
                  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 
                  <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a> 
                  <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>
               </div>
               <input type="hidden" name="multi_action" value="" />
               <div class="col-sm-12 table-responsive p-0">
                <table class="table table-advance table-striped "  id="table_module" >
                  <thead>
                     <tr>
                        <th>
                           <div class="checkbox checkbox-success">
                              <input class="checkboxInputAll" value="delete" id="checkbox0" type="checkbox">
                              <label for="checkbox0">  </label>
                           </div>
                        </th>
                        <th>Page Name</th>
                        <th>Status</th>
                        <th width="200px">Action</th>
                     </tr>
                  </thead>
                  <tbody>
                     @foreach($arr_static_page as $page)
                     <tr>
                        <td>
                           <div class="checkbox checkbox-success"><input type="checkbox" name="checked_record[]" value="{{ base64_encode($page['id']) }}" id="checkbox'{{$page['id']}}'" class="case checkboxInput"/><label for="checkbox'{{$page['id']}}'"> </label></div>
                        </td>
                        <td> {{ $page['page_title'] }} </td>
                        <td>
                           @if($page['is_active']=='1')
                           <input type="checkbox" checked data-size="small"  data-enc_id="'{{base64_encode($page['id'])}}'"  id="status_'{{$page['id']}}'" class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" />
                           @else
                           <input type="checkbox" data-size="small" data-enc_id="'{{base64_encode($page['id'])}}'"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" />
                           @endif
                        </td>
                        <td> 
                           <a href="{{ $module_url_path.'/edit/'.base64_encode($page['id']) }}"  data-toggle="tooltip"  data-size="small"   class="btn btn-circle btn-success btn-outline show-tooltip" title="Edit">Edit
                           </a>  
                           <a href="{{ $module_url_path.'/delete/'.base64_encode($page['id'])}}"  data-toggle="tooltip"  data-size="small" class="btn btn-outline btn-info btn-circle show-tooltip" 
                              onclick="confirm_action(this,event,'Are you sure? Do you want to delete this record.');"  title="Delete">Delete
                           </a>   
                        </td>
                     </tr>
                     @endforeach
                  </tbody>
               </table>
               </div>
            </div>
            {!! Form::close() !!}
         </div>
      </div>
   </div>
</div>
</div>

<!-- END Main Content -->
<script type="text/javascript">
   var module_url_path         = "{{ $module_url_path or '' }}";
    $(document).ready(function() {

      var table =  $('#table_module').DataTable( {
             "aoColumns": [
             { "bSortable": false },
             { "bSortable": true },
             { "bSortable": true },
             { "bSortable": false },
             { "stateSave": true }

            ]
        });

        $("#table_module").on('page.dt', function (){

          var info = table.page.info();
         
          $("input.checkboxInputAll").prop('checked',false);
      
        });
    

    });
   
    function statusChange(data)
    {
        var ref    = data; 
        var type   = data.attr('data-type');
        var enc_id = data.attr('data-enc_id');
        var id     = data.attr('data-id');


        var msg    = '';
        if(type ==  'activate')
        {
        msg        = "Are you sure? Do you want to activate this CMS status.";
        }
        else if(type == 'deactivate')
        {
        msg       = "Are you sure? Do you want to deactivate this CMS status. "; 
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
                 if(response.status=='SUCCESS'){

                   if(response.data=='ACTIVE')
                   { 
                     $(ref)[0].checked = true;  
                     $(ref).attr('data-type','deactivate');
                     swal('Success',"Status has been activated.","success");


                   }else if(response.data=='DEACTIVE')
                   {
                     $(ref)[0].checked = false;  
                     $(ref).attr('data-type','activate');
                     swal('Success',"Status has been deactivated.","success");
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
   
/*   $(function(){
   
   $("input.checkboxInputAll").click(function(){
   
     if($("input.checkboxInput:checkbox:checked").length <= 0){
         $("input.checkboxInput").prop('checked',true);
     }else{
         $("input.checkboxInput").prop('checked',false);
     }
   
   });  
   });*/



$(function(){

  $("input.checkboxInputAll").click(function(){
             
      if($(this). prop("checked") == true){
        $("input.checkboxInput").prop('checked',true);
      }
      else{
        $("input.checkboxInput").prop('checked',false);
      }

  });

});




$('input.checkboxInput').click(function(){
  toggleSelectAction();
});


function toggleSelectAction()
{
  
  var allBoxes = $("input.checkboxInput").length;
  var checkedBoxes = $('input:checked[name="checked_record[]"]').length;
 
  if(allBoxes != checkedBoxes){
       
      $("input.checkboxInputAll").prop('checked',false);
  }
  else
  {
     $("input.checkboxInputAll").prop('checked',true);
  }


}
</script>
@stop