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
                        
                            
                            {!! Form::open([ 'url' => $module_url_path.'/update_commission_settings',
                                         'method'=>'POST',   
                                         'class'=>'form-horizontal', 
                                         'id'=>'validation-form'
                                        ]) !!}


                            <!-- hidden field -->
                          
                               
                            <div class="col-md-12">


                              <div class="row">
                                <div class="col-sm-12 col-md-12 col-lg-4">
                                  <div class="form-group">
                                  <label class="mb-2">Admin Commission(%)
                                  <i class="red">*</i></label>

                                  <div>
                                    <input type="hidden" id="lower_value" value="0">


                                    {!! Form::text('commission',isset($arr_data['commission'])?num_format($arr_data['commission']):'',['class'=>'form-control','data-parsley-type'=>"number",'data-parsley-type-message'=>'Please enter valid commission','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter admin commission','data-parsley-minlength'=>"1",'data-parsley-max'=>"100",'data-parsley-pattern'=>"^(?=.*[0-9])(?:[0-9]\d*\.?|0?\.)\d*$",'data-parsley-pattern-message'=>'Please enter valid commission','data-parsley-max-message'=>'Commission should be lower than or equal to 100','placeholder'=>'Enter Admin Commission']) !!}
                                      <span class='red' id="err_admin">{{ $errors->first('commission') }}</span>
                                  </div> 
                                </div>
                                </div>

                                <div class="col-sm-12 col-md-12 col-lg-4">
                                  <div class="form-group">
                                  <label class="mb-2">Representative Commission(%)
                                  <i class="red">*</i></label>
                                  <span id="test" style="display:none;">0</span>
                                  <div>
                                    {!! Form::text('representative_commission',isset($arr_data['representative_commission'])?num_format($arr_data['representative_commission']):'',['class'=>'form-control','data-parsley-type'=>"number",'data-parsley-type-message'=>'Please enter valid commission','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter representative commission','data-parsley-minlength'=>"1",'data-parsley-pattern'=>"^(?=.*[0-9])(?:[0-9]\d*\.?|0?\.)\d*$",'data-parsley-pattern-message'=>'Please enter valid commission', 
                                     'data-parsley-max'=>"100",'data-parsley-max-message'=>'Commission should be lower than or equal to 100','placeholder'=>'Enter Representative Commission']) !!}
                                      <span class='red' id="err_rep">{{ $errors->first('representative_commission') }}</span>
                                  </div> 
                                </div>
                                </div>

                                <div class="col-sm-12 col-md-12 col-lg-4">
                                  <div class="form-group">
                                  <label class="mb-2">Sales Manager Commission(%)
                                  <i class="red">*</i></label>
                                  <div>
                                    {!! Form::text('salesmanager_commission',isset($arr_data['salesmanager_commission'])?num_format($arr_data['salesmanager_commission']):'',['class'=>'form-control','data-parsley-type'=>"number",'data-parsley-type-message'=>'Please enter valid commission','data-parsley-required'=>'true','data-parsley-required-message'=>'Please enter sales manager commission','data-parsley-minlength'=>"1",'data-parsley-pattern'=>"^(?=.*[0-9])(?:[0-9]\d*\.?|0?\.)\d*$",'data-parsley-pattern-message'=>'Please enter valid commission','data-parsley-max'=>"100",'data-parsley-max-message'=>'Commission should be lower than or equal to 100','placeholder'=>'Enter Sales Manager Commission']) !!}
                                      <span class='red' id="err_sales">{{ $errors->first('salesmanager_commission') }}</span>
                                  </div>
                                </div>
                                </div>
                              </div>
                              <div class="form-group row">
                                <div class="col-sm-12 p-0">
                
                                    <button type="button" style="float: right" class="btn btn-success waves-effect waves-light" value="Update" id="update">Update</button>
                          
                                  </div>
                              </div>
                            </div>
                    </div>
                    <div class="row">
                          <div class="col-12">
                            {{-- <a class="btn btn-success waves-effect waves-light pull-left" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Back</a> --}}

                            {{-- <button type="button" style="float: right" class="btn btn-success waves-effect waves-light m-r-10 " value="Update" id="update">Update</button> --}}
                          </div>
                    </div>
                            {!! Form::close() !!}
                </div>
           </div>
        </div>
    </div>
    

  <!-- END Main Content --> 
  <script type="text/javascript">


  $("#update").click(function()
   {
      if($('#validation-form').parsley().validate()==false)
      {  
             hideProcessingOverlay();
             return;
      }
      else
      {
             showProcessingOverlay();
             $("#validation-form").submit();
      }
/*      else
      { 
        if($('input[name="salesmanager_commission"]').val() > 0)
        {
            if($('input[name="commission"]').val() > 0)
            {
                if($('input[name="representative_commission"]').val() > 0)
                {
                   $('#err_rep').html(" ");
                    showProcessingOverlay();
                    $("#validation-form").submit();
                    
                }
                else{
                      $('#err_rep').html("Commission value should be greater than 0.");
                      return
                }
                $('#err_admin').html(" "); 
            }
            else{
                  $('#err_admin').html("Commission value should be greater than 0.");
                  return
            }
                $('#err_sales').html(" "); 
            
        }
        else{
              $('#err_sales').html("Commission value should be greater than 0.");
              return
        } 
 
      } */       
   }); 

</script>
@endsection