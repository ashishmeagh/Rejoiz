@extends('admin.layout.master')                
@section('main_content')

<style>
.row{
     padding-bottom: 20px;
  }
</style> 

<!-- Page Content -->
<div id="page-wrapper">
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">{{$page_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li>
            <a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a>
          </li>
          <li>
            <a href="{{$module_url_path}}">{{$module_title or ''}}</a>
          </li>
          <li class="active">{{$page_title or ''}}</li>
        </ol>
      </div>
      <!-- /.col-lg-12 -->
    </div>
    <!-- .row -->
    <div class="row">
      <div class="col-sm-12">
        <div class="white-box">
         @include('admin.layout._operation_status')

         <div class="row">
          <div class="col-sm-12 col-md-12 col-lg-6">
            <div class="row">
              <div class="col-sm-4">
              <label>Category Name</label>
            </div>
            <div class="col-sm-8">
              <span>{{ isset($subcat_data['category_name']) ? $subcat_data['category_name'] : "" }}</span>
            </div>
            </div>
          </div>

          <div class="col-sm-12 col-md-12 col-lg-6">
            <div class="row">
              <div class="col-sm-4">
              <label>Sub Category</label>
            </div>
            <div class="col-sm-8">
              <span>{{ isset($subcat_data['sub_category_name']) ? $subcat_data['sub_category_name'] : "" }}</span>
               <input type="hidden" name="sub_category_name" id="sub_category_name" value="{{ isset($subcat_data['sub_category_name']) ? $subcat_data['sub_category_name'] : "" }}">
            </div>
            </div>
          </div>

          @if($subcat_data['admin_confirm_status'] == 1)
          <div class="col-sm-12 col-md-12 col-lg-6">
            <div class="row">
              <div class="col-sm-4">
              <label>Reason of Rejection</label>
            </div>
            <div class="col-sm-8">
              <span>{{ isset($subcat_data['reject_reason']) ? strip_tags($subcat_data['reject_reason']) : "" }}</span>
            </div>
            </div>
          </div>
          @endif
         </div>

         {{-- <div class="col-sm-12 col-md-12 col-lg-12 orders_view">

         <div class="row">
          <div class="col-sm-3 col-md-12 col-lg-3">
            <label>Category Name:</label>
          </div>
          <div class="col-sm-12 col-md-12 col-lg-9">
            <span> {{ isset($subcat_data['category_name']) ? $subcat_data['category_name'] : "" }}</span>
          </div>
         </div>
         <div class="row">
          <div class="col-sm-3 col-md-12 col-lg-3">
            <label>Sub Category:</label>
          </div>
          <div class="col-sm-12 col-md-12 col-lg-9">
            <span>{{ isset($subcat_data['sub_category_name']) ? $subcat_data['sub_category_name'] : "" }}</span>
             <input type="hidden" name="sub_category_name" id="sub_category_name" value="{{ isset($subcat_data['sub_category_name']) ? $subcat_data['sub_category_name'] : "" }}">
          </div>
         </div>
          @if($subcat_data['admin_confirm_status'] == 1)   
          <div class="row">
          <div class="col-sm-3 col-md-12 col-lg-3">
            <label>Reason of Rejection:</label>
          </div>
          <div class="col-sm-12 col-md-12 col-lg-9">
            <span>{{ isset($subcat_data['reject_reason']) ? strip_tags($subcat_data['reject_reason']) : "" }}</span>
            
          </div>
         </div>
         @endif

          </div> --}}

            <div class="row pb-0">
                  <div class="col-sm-12 text-right">

                    @if(isset($subcat_data['admin_confirm_status']) && $subcat_data['admin_confirm_status'] == 2)   
                    <input type="hidden" name="cat_id" id="cat_id" value="{{$subcat_data['id']}}">
                  <button class="btn btn-success" onclick="perform_action({{$subcat_data['id']}},0)" type="button">Approve</button>                        
                  <button class="btn btn-danger" onclick="perform_action({{$subcat_data['id']}},1)" type="button">Reject</button>
                  @endif  
                    <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}">
                      <i class="fa fa-arrow-left"></i> Back
                    </a>
                  </div>
                </div>
          <!-- <div class="row">
            <div class="col-sm-12 col-xs-12">
              <h3>
                <span class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" ></span>
              </h3>
            </div>
          </div>
          <div class="col-sm-12 admin_profile common-profile">
          <div class="row">
          <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6 left">
            <div class="main-adm-profile">
                <table border="0" width="100%">
                  <tr>
                    <td width="30%"><strong>Category Name </strong></td>
                    <td width="10%"><strong>: </strong></td>
                    <td width="60%">{{ isset($subcat_data['category_name']) ? $subcat_data['category_name'] : "" }}</td>
                  </tr>
                  <tr>
                    <td><strong>Sub Category Name </strong></td>
                    <td><strong>: </strong></td>
                    <td>{{ isset($subcat_data['sub_category_name']) ? $subcat_data['sub_category_name'] : "" }}
                      <input type="hidden" name="sub_category_name" id="sub_category_name" value="{{ isset($subcat_data['sub_category_name']) ? $subcat_data['sub_category_name'] : "" }}">
                    </td>
                  </tr>
                </table>
              </div>
            </div>            
          </div>
        </div>   -->

             {{--  <!--  @if($subcat_data['admin_confirm_status'] == 2)                        
                <div class="form-group row">
                  <input type="hidden" name="cat_id" id="cat_id" value="{{$subcat_data['id']}}">
                  <button class="btn btn-success" onclick="perform_action({{$subcat_data['id']}},0)" type="button">Approve</button>
                          &nbsp;&nbsp;&nbsp;&nbsp;                          
                  <button class="btn btn-danger" onclick="perform_action({{$subcat_data['id']}},1)" type="button">Reject</button>
                </div>
               @endif       --> --}}    
              </div>
            </div>
          </div>
        </div>
      </div>


        <!-- multiple select dropdown modal -->
<div id="ModalRejectReason" data-controls-modal="ModalRepresentative" data-backdrop="static" data-keyboard="false" class="modal fade login-popup" data-replace="true" style="display: none;">
    <div class="modal-dialog md-size-mdl">
        <!-- Modal content-->
        <div class="modal-content sign-up-popup">
            <div class="modal-body">
                    <div class="login-form-block">
                        <div class="login-content-block">
                          <div class="categorymodalslst">       
                            <div class="row">
                              <div class="col-md-12">
                                <label>Reason for rejection</label>
                                <textarea class="form-control" id="reject_reason"  ></textarea>
                                <span id="error_container" class="red"></span>
                              </div>
                            </div>
                          </div>
                           <div class="modal-footer">
                          <a class="logi-link-block btn-primary" data-toggle="modal" id="btn_submit" is_button="submit">Submit</a>

                          <button id="close_submit" type="button" class="btn logi-link-block btn-primary" data-dismiss="modal" is_button="cancel">Close</button>
                      </div>
                          <div class="clearfix"></div>
                        </div>

                    </div>
                <div class="clr"></div>
            </div>
        </div>
    </div>
</div>
      <!-- END Main Content -->
<script type="text/javascript">
var module_url_path = '{{$module_url_path}}';

function perform_action(cat_id,status)
{
  var msg = 'Are you sure? Do you want to perform this action.';

  swal({
        title:"Need Confirmation",
        text: msg,
        type: "warning",
        showCancelButton: true,                
        confirmButtonColor: "#444",
        confirmButtonText: "OK",
        closeOnConfirm: true
  },
  function(isConfirm,tmp)
  {
    if(isConfirm==true)
    {
      update_status(cat_id,status);
    }
  });
}


function update_status(cat_id,status)
{
  var sub_cat_name = $("#sub_category_name").val();

  if(status == '1'){
     $('#ModalRejectReason').modal('show');
     saveTinyMceContent();
     return false;
  } else {
      fun_update_status(cat_id,status,'',sub_cat_name);
  }
}

$('#btn_submit').click(function(){
    var cat_id        = $("#cat_id").val();
    var sub_cat_name = $("#sub_category_name").val();
    var tinyMCE_value = tinyMCE.get('reject_reason').getContent();
    
    if(tinyMCE_value !=""){
      var reject_reason = tinyMCE_value;
      fun_update_status(cat_id,1,reject_reason,sub_cat_name);
      $('#ModalRejectReason').modal('hide');
    } else {
      $("#error_container").html("Please enter reason of rejection");
    }
    
});

$('#close_submit').click(function(){
    $('#ModalRejectReason').modal('hide');
});

$('#reject_reason').keyup(function(){
      $("#error_container").html("");
 });

function fun_update_status(cat_id,status,reject_reason = false,sub_cat_name){
  
  $.ajax({
        url:module_url_path+'/change_status?_token='+'{{csrf_token()}}',
        data:{
              cat_id:btoa(cat_id),
              status:status,
              reject_reason:reject_reason,
              sub_cat_name : sub_cat_name
        },
        method:'POST',       
        dataType:'json',
        beforeSend : function()
        { 
           showProcessingOverlay();          
        },
        success:function(response)
        {
           hideProcessingOverlay();
          
          if(typeof response =='object')
          {
            if(response.status && response.status=="success")
            {
              swal({
                      title:"Success",
                      text: response.description,
                      type: "success",
                      showCancelButton: false,                
                      confirmButtonColor: "#444",
                      confirmButtonText: "OK",
                      closeOnConfirm: true
              },
              function(isConfirm,tmp)
              {
                  if(isConfirm==true)
                  {
                   // location.reload();
                   window.location = response.link;
                  }
              });
            }
            else
            {                    
              swal('Error',response.description,'error');  
            }
          }
        }
      });
}



</script>

 <script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
 
 <script type="text/javascript">
    function saveTinyMceContent()
    {
      tinyMCE.triggerSave();
    }
 </script> 
<style type="text/css">
#mceu_15{
    display: none !important;
 }
 #reject_reason {
  display: block;
 }
</style>
@stop