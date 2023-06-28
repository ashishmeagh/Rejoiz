@extends('admin.layout.master')    

@section('main_content')

<style type="text/css">
  
  .error{
    color: #D94020; font-size: 12px;
  }
  .form-control.error{color: #333; font-size: 13px;}
  .height-forms{height: 60px !important; vertical-align: top;}
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
                      <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
                      <li class="active">{{$module_title or ''}}</li>
                  </ol>
                </div>
              <!-- /.col-lg-12 -->
          </div>

    <!-- BEGIN Main Content -->
    
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
             @include('admin.layout._operation_status')
                <div class="row">
                    <div class="col-sm-12 col-xs-12">
                         <div class="box-title">
                            <h3><i class="ti-settings"></i> {{ isset($page_title)?$page_title:"" }}</h3>
                            <div class="box-tool">
                            </div>
                        </div>
                      {{--       
                        {!! Form::open([ 'url' => $module_url_path.'/update/'.base64_encode($arr_data['id']),
                                         'method'=>'POST',   
                                         'class'=>'form-horizontal', 
                                         'id'=>'validation-form',
                                         'enctype' =>'multipart/form-data'
                                        ]) !!} --}}
                          <form method="POST" class="form-horizontal" id="validation-form"   action="{{ $module_url_path.'/update/'.base64_encode($arr_data['id'])}}">
                            {{csrf_field()}}
                            <div class="row">
                              <div class="col-md-6">                            

                                <div class="form-group">
                                  <label class="col-md-4 control-label"> B2C Visibility: <i class="red"></i></label>

                                  <div class="col-md-8" id="swichery">
                                    <input type="checkbox" name="is_b2c_module_on" id="is_b2c_module_on" value="{{$arr_data['is_b2c_module_on']}}" data-size="small" class="js-switch " @if($arr_data['is_b2c_module_on']=='1') checked="checked" @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return confirm_b2c_box(this);" />

                                  </div>  
                                </div> 

                                <div class="form-group">
                                  <label class="col-md-4 control-label"> Influencer Visibility: <i class="red"></i></label>

                                  <div class="col-md-8" id="swichery">
                                    <input type="checkbox" name="is_influencer_module_on" id="is_influencer_module_on" value="{{$arr_data['is_influencer_module_on']}}" data-size="small" class="js-switch " @if($arr_data['is_influencer_module_on']=='1') checked="checked" @endif data-color="#99d683" data-secondary-color="#f96262" onchange="return confirm_box(this);" />

                                  </div>  
                                </div> 
                              
                              </div>
                              
                            </div>

                            

                              {{-- <div class="form-group row">
                              <div class="col-12">
                                <a class="btn btn-success waves-effect waves-light pull-left" href="{{url('/')}}/admin/dashboard"><i class="fa fa-arrow-left"></i> Back</a>
                                <button type="submit" style="float: right" class="btn btn-success waves-effect waves-light m-r-10" value="Update" id="update">Update</button>
                              </div>
                           </div> --}}

                          </form>
                    </div>
                </div>
           </div>
        </div>
    </div>
    
 

  <!-- END Main Content --> 
  <script type="text/javascript">


  

  function confirm_b2c_box(ref)
  {

    swal({
        title: "Need Confirmation",
        text: "Are you sure? Do you want to change the visibility of B2C Module.",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: true
      },
      function(isConfirm) {
        if (isConfirm) {
                  toggle_b2c_status();
        } else {
            $(ref).trigger('click');
        }
      });
  }
   
  function confirm_box(ref)
  {

    swal({
        title: "Need Confirmation",
        text: "Are you sure? Do you want to change the visibility of Influencer Module.",
        type: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn-danger",
        confirmButtonText: "OK",
        cancelButtonText: "Cancel",
        closeOnConfirm: true,
        closeOnCancel: true
      },
      function(isConfirm) {
        if (isConfirm) {
                  toggle_status();
        } else {
            $(ref).trigger('click');
        }
      });
  }
 
 function toggle_status()
  { 
      let is_influencer_module_on = $('#is_influencer_module_on').val();
      
      if(is_influencer_module_on == '1')
      {
        $('#is_influencer_module_on').val('0');
      }
      else
      {
        $('#is_influencer_module_on').val('1');
      }

      is_influencer_module_on = $('#is_influencer_module_on').val();
      /*alert(is_influencer_module_on);
      return;*/

      let module_url = '{{$module_url_path}}';
      let id         =  '{{base64_encode($arr_data['id'])}}';
      let url = module_url+'/update_is_influencer_module_on';

      $.ajax({
          url: url,
          method:"POST",
          data:{id:id,status:is_influencer_module_on,"_token":"{{csrf_token()}}"},
          dataType:'json',
          beforeSend : function()
          {
            showProcessingOverlay();
          },

          success:function(data)
          {
            hideProcessingOverlay();
             swal({
                    title:"Success", 
                    text: data.description, 
                    type: data.status
                }
               // function(){ 
               //     location.reload();
               // }
            );

          }
          
        });   

  }  
  
  function toggle_b2c_status()
  { 
      let is_b2c_module_on = $('#is_b2c_module_on').val();
      
      if(is_b2c_module_on == '1')
      {
        $('#is_b2c_module_on').val('0');
      }
      else
      {
        $('#is_b2c_module_on').val('1');
      }

      is_b2c_module_on = $('#is_b2c_module_on').val();
      /*alert(is_b2c_module_on);
      return;*/

      let module_url = '{{$module_url_path}}';
      let id         =  '{{base64_encode($arr_data['id'])}}';
      let url = module_url+'/update_is_b2c_module_on';

      $.ajax({
          url: url,
          method:"POST",
          data:{id:id,status:is_b2c_module_on,"_token":"{{csrf_token()}}"},
          dataType:'json',
          beforeSend : function()
          {
            showProcessingOverlay();
          },

          success:function(data)
          {
            hideProcessingOverlay();
             swal({
                    title:"Success", 
                    text: data.description, 
                    type: data.status
                }
               // function(){ 
               //     location.reload();
               // }
            );

          }
          
        });   

  }  
    

</script>
@endsection