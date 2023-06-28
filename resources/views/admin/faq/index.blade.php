@extends('admin.layout.master')                
@section('main_content')
<style type="text/css">
  .switchery+.hover-tool{
   display: none;
    position: absolute;
    bottom: -35px;
    left: 0px;
    width: 100px;
    background-color: #333;
    border-radius: 3px;
    font-size: 13px;
    color: #fff;
    padding: 5px;
    text-align: center;
  }

   th {
    white-space: nowrap;
}
.switchery:hover+.hover-tool{display:block;}
.hover-tool-tool{position: relative;}
.truncate-text {white-space:break-spaces;}
</style>
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
         <div class="pull-right top_small_icon">
            <a href="{{ url('/admin').'/faq/create' }}" class="btn btn-outline btn-info btn-circle show-tooltip" title="Add FAQ"><i class="fa fa-plus"></i> </a>

            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactivate"><i class="ti-lock"></i> </a> 

            <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a> 

            <a href="javascript:void(0)" onclick="javascript:location.reload();" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i> </a>

         </div>

         <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="180">
         <div class="table-responsive">

         

         <input type="hidden" name="multi_action" value="" />
          <table class="table table-striped"  id="table_module" >
            <thead>
              <tr>
                <th>
                  <div class="checkbox checkbox-success">
                    <input class="checkboxInputAll" value="delete" id="checkbox0" type="checkbox">
                    <label for="checkbox0">  </label>
                  </div>
                </th>
                <th>Questions</th>
                <th>Answers</th>
                <th>FAQ for</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
              <tbody>
                @if(sizeof($arr_data)>0)
                  @foreach($arr_data as $faq)
                  <tr>
                    <td>
                      <div class="checkbox checkbox-success"><input type="checkbox" name="checked_record[]" value="{{ base64_encode($faq['id']) }}" id="checkbox'{{$faq['id']}}'" class="case checkItem"/><label for="checkbox'{{$faq['id']}}'"> </label></div>
                    </td>
                    <td> {{-- <div class="truncate">{{ $faq['question'] or ''}} </div> --}} 
                    @if(isset($faq['question']) && strlen($faq['question']) > 90 && $faq['question']!='' )   
                        @php
                        $desc_html = $desc = "";
                        $desc_html = ($faq['question']);
                        $desc =  substr(html_entity_decode($desc_html), 0, 90);
                        @endphp               
                       <p class="prod-desc"> {!!html_entity_decode($desc)!!}
                        <br>
                          <a class="readmorebtn" message="{{$faq['question']}}" style="cursor:pointer">
                              <b>Read more</b>
                          </a>
                      </p>                  
                    @else                      
                       {!!$faq['question']!!}
                    @endif
                    </td>
                    <td class="description-text-td"> {{-- <div class="truncate">{!! $faq['answer'] or ''!!} </div> --}} 
                      @if(isset($faq['answer']) && strlen($faq['answer']) > 150 && $faq['answer']!='' )   
                        @php
                        $desc_html = $desc = "";
                        $desc_html = ($faq['answer']);
                        $desc =  substr(html_entity_decode($desc_html), 0, 150);
                        @endphp               
                       <p class="prod-desc"> {!!html_entity_decode($desc)!!}
                        <br>
                          <a class="readmorebtn" message="{{$faq['answer']}}" style="cursor:pointer">
                              <b>Read more</b>
                          </a>
                      </p>                  
                    @else                      
                       {!!$faq['answer']!!}
                    @endif

                    </td>
                    <td> 
                      @if($faq['faq_for']==1) 
                        Customers

                      @elseif($faq['faq_for']==2) 
                        Vendors

                      @endif
                    </td>
                    <td>
                      <div class="hover-tool-tool">
                        @if($faq['is_active'] == '1')
                          <input type="checkbox" {{--data-toggle="tooltip"  title="Deactive" --}} checked data-size="small"  data-enc_id="'{{base64_encode($faq['id'])}}'"  id="status_'{{$faq['id']}}'" class="js-switch toggleSwitch " data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>
                          {{-- <div class="hover-tool">Activate</div> --}}
                        @else
                          <input type="checkbox" {{--data-toggle="tooltip"  title="Active" --}} data-size="small" data-enc_id="'{{base64_encode($faq['id'])}}'"  class="js-switch toggleSwitch " data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="change_status(this)"/>
                          {{-- <div class="hover-tool">Deactivate</div> --}}
                        @endif
                      </div>
                    </td>
                    <td> 
                      <a href="{{$module_url_path}}/edit/{{base64_encode($faq['id'])}}" data-toggle="tooltip"  data-size="small" title="Edit" class="btn btn-circle btn-success btn-outline show-tooltip" >Edit</a>

                      <a href="{{$module_url_path}}/delete/{{base64_encode($faq['id'])}}" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-info btn-circle show-tooltip" onclick="confirm_delete(this,event);">Delete</a>
                       
                    </td>
                  </tr>
                  @endforeach
                  @endif
               </tbody>
            </table>
         </div>
         <div>   
         </div>
         {!! Form::close() !!}
      </div>
   </div>
 </div>
</div>

<!-- Modal -->
<div class="modal fade vendor-Modal" id="product_description_modal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered product_description_modal" role="document">
    <div class="modal-content">
      <div class="modal-header my-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Answer</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body product_description_modal_body">
            <span id="showmessage"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
       {{--  <button type="button" class="btn btn-primary" onclick="payVendor()" >Pay</button> --}}
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
   $(document).on('click','.readmorebtn',function(){
   
     var reason = $(this).attr('message');
     if(reason)
     {
        $("#product_description_modal").modal('show');
        $("#showmessage").html(reason);      
     }

  });
</script>
<style type="text/css">
   #showmessage p {
    font-weight: normal !important;
   }
   .product_description_modal {
      max-width: 700px !important
    }
    .product_description_modal_body{
      max-height: 500px;
      overflow: auto;
    }
    .my-header {
      display: inherit !important
    }
</style>
<!-- END Main Content -->
<script type="text/javascript">

   $(document).ready(function() {
       var table =  $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
          stateSave: true
       });
     });
   
  function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this FAQ.');
  }


$(function(){

    $("input.checkboxInputAll").click(function(){
             
        if($(this). prop("checked") == true){
          $("input.checkItem").prop('checked',true);
        }
        else{
          $("input.checkItem").prop('checked',false);
        }

    });

});

$('input.checkItem').click(function(){
  toggleSelectAction();
});


function toggleSelectAction()
{
  
  var allBoxes = $("input.checkItem").length;
  var checkedBoxes = $('input:checked[name="checked_record[]"]').length;
 
  if(allBoxes != checkedBoxes){
       
      $("input.checkboxInputAll").prop('checked',false);
  }
  else
  {
     $("input.checkboxInputAll").prop('checked',true);
  }


}

function change_status(reference)
    {

        var module_url_path = "{{$module_url_path}}";
        var enc_id = reference.getAttribute('data-enc_id');
        var status = reference.getAttribute('data-type');

        var msg    = '';
        if(status ==  'activate')
        {
          msg        = "Are you sure? Do you want to activate this FAQ.";
        }
        else if(status == 'deactivate')
        {
           msg       = "Are you sure? Do you want to deactivate this FAQ. "; 
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
                    url:module_url_path+'/change_status',
                    type: 'GET',
                    dataType:'json',
                    data:{faq_id:enc_id,status:status},
                    beforeSend : function()
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
                            swal('Error', data.messsage, data.status);
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


/*-----------------------------------------------------------------------------*/

</script>



@stop