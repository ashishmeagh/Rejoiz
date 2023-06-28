@extends('admin.layout.master')                
@section('main_content')
<style>
.row{
     padding-bottom: 20px;
  }
.clearfix-btn {
  clear:both;
  margin:0px;
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

         <div class="col-sm-12 col-md-12 col-lg-12 orders_view">
         <div class="row">
          <div class="col-sm-3 col-md-12 col-lg-3">
            <label>Category Name:</label>
          </div>
          <div class="col-sm-12 col-md-12 col-lg-9">
            <span> {{ isset($arr_data['translations']['en']['category_name']) ? $arr_data['translations']['en']['category_name'] : "" }}</span>
            <input type="hidden" name="category_name" id="category_name" value="{{ isset($arr_data['translations']['en']['category_name']) ? $arr_data['translations']['en']['category_name'] : "" }}">
          </div>
         </div>
         <div class="row">
          <div class="col-sm-3 col-md-12 col-lg-3">
            <label>Category Image </label>
          </div>

          <div class="col-sm-8">
            @if(isset($arr_data['category_image']))
            <span><img class="img-responsive" src="{{url('/')}}/storage/app/{{ $arr_data['category_image'] }}" ></span>
            @endif
          </div>
         </div>

         @if($arr_data['admin_confirm_status'] == 1)   
          <div class="row">
          <div class="col-sm-3 col-md-12 col-lg-3">
            <label>Reason of Rejection:</label>
          </div>
          <div class="col-sm-12 col-md-12 col-lg-9">
            <span>{{ isset($arr_data['reject_reason']) ? strip_tags($arr_data['reject_reason']) : "" }}</span>
            
          </div>
         </div>
         @endif

         
          </div>
            <div class="row clearfix-btn">
                  <div class="col-sm-12 text-right">

                    @if(isset($arr_data['admin_confirm_status']) && $arr_data['admin_confirm_status'] == 2)   
                    <input type="hidden" name="cat_id" id="cat_id" value="{{$arr_data['id']}}">
                  <button class="btn btn-success" onclick="perform_action({{$arr_data['id']}},0)" type="button">Approve</button>
                                             
                  <button class="btn btn-danger" onclick="perform_action({{$arr_data['id']}},1)" type="button">Reject</button>
                  @endif  
                    <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}">
                      <i class="fa fa-arrow-left"></i> Back
                    </a>
                  </div>
                </div>
         
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
                                <textarea class="form-control" id="reject_reason" name="reject_reason"></textarea>
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

  var category_name =  $("#category_name").val();

  if(status == '1'){
     $('#ModalRejectReason').modal('show');
     return false;
  } else {
      fun_update_status(cat_id,status,'',category_name);
  }
}

$('#btn_submit').click(function(){
    var cat_id        = $("#cat_id").val();
    var category_name =  $("#category_name").val();
    //var tinyMCE_value =  $('#reject_reason').contents().find('body').text().trim().length;

    var tinyMCE_value = tinyMCE.get('reject_reason').getContent();
    
    if(tinyMCE_value !=""){
      var reject_reason = tinyMCE_value;
      fun_update_status(cat_id,1,reject_reason,category_name);
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

function fun_update_status(cat_id,status,reject_reason = false,category_name){
  
  $.ajax({
        url:module_url_path+'/change_status?_token='+'{{csrf_token()}}',
        data:{
              cat_id:btoa(cat_id),
              status:status,
              reject_reason:reject_reason,
              category_name : category_name
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
                    //location.reload();
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
<style type="text/css">
#mceu_15{
    display: none !important;
 }
 #reject_reason {
  display: block;
 }
</style>
@stop