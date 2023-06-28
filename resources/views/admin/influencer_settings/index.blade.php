@extends('admin.layout.master')                
@section('main_content')
<!-- Page Content -->
<div id="page-wrapper" >
<div class="container-fluid create_rep_form_white_box">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-12 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
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
               <form class="form-horizontal" id="influencer_setting_form">
                  {{ csrf_field() }}
                  <input type="hidden" name="enc_id" id="enc_id" value="{{ isset($arr_data['id'])?base64_encode($arr_data['id']):'' }}">
                    <div class="form-group row">
                        <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Sales Target ($)<i class="red">*</i></label>
                        <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                            <input type="text" 
                               class="form-control" 
                               name="sales_target" 
                               placeholder="Enter Sales Target" 
                               data-parsley-required="true" 
                               data-parsley-required-message="Please enter sales target" 
                               data-parsley-type="number"
                               data-parsley-type-message="Please enter valid sales target"
                               data-parsley-min="1"
                               data-parsley-min-message="Minimum value should be 1"
                               value={{ isset($arr_data['sales_target'])?num_format($arr_data['sales_target']):'' }}>
                            <span class="red" id="err_msg">{{ $errors->first('sales_target') }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Reward Amount ($)<i class="red">*</i></label>
                        <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                            <input type="text" 
                               class="form-control" 
                               name="reward_amount" 
                               placeholder="Enter Reward Amount" 
                               data-parsley-required="true" 
                               data-parsley-required-message="Please enter reward amount" 
                               data-parsley-type="number"
                               data-parsley-type-message="Please enter valid reward amount"
                               data-parsley-min="1"
                               data-parsley-min-message="Minimum value should be 1"
                               value={{ isset($arr_data['reward_amount'])?num_format($arr_data['reward_amount']):'' }} >
                            <span class="red" id="err_msg">{{ $errors->first('reward_amount') }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Discount On Promo Code (%)<i class="red">*</i></label>
                        <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                            <input type="text" 
                               class="form-control" 
                               name="discount_on_promo_code" 
                               placeholder="Enter Discount On Promo Code (%)" 
                               data-parsley-required="true" 
                               data-parsley-required-message="Please enter discount on promo code(%)" 
                               data-parsley-type="digits"
                               data-parsley-type-message="Only digits allowed (%)"
                               data-parsley-min="1"
                               data-parsley-min-message="Minimum value should be 1"
                               data-parsley-max="100"
                               data-parsley-max-message="Maximum value should not be greater than 100"
                               value={{ isset($arr_data['discount_on_promo_code'])?$arr_data['discount_on_promo_code']:'' }} >
                            <span class="red" id="err_msg">{{ $errors->first('discount_on_promo_code') }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Promo Code Validity (In Days)<i class="red">*</i></label>
                        <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                            <input type="text" 
                               class="form-control" 
                               name="promo_code_validity_in_days" 
                               placeholder="Enter Promo Code Validity (In Days)" 
                               data-parsley-required="true" 
                               data-parsley-required-message="Please enter promo code validity (in days)" 
                               data-parsley-type="number"
                               data-parsley-type-message="Please enter valid promo code validity(In Days)"
                               data-parsley-min="1"
                               data-parsley-min-message="Minimum value should be 1"
                               value={{ $arr_data['promo_code_validity_in_days'] or ''}} >
                            <span class="red" id="err_msg">{{ $errors->first('promo_code_validity_in_days') }}</span>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-success waves-effect waves-light" id="btn_update" value="Update">Update</button>
                        </div>
                    </div>
               </form>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->

<script type="text/javascript">

    const module_url_path = "{{ $module_url_path }}";

    $("#btn_update").click(function()
    {
        
        if($('#influencer_setting_form').parsley().validate()==false) return;

        var formdata = $("#influencer_setting_form").serialize();
       
        $.ajax({
            url: module_url_path+'/update',
            type:"POST",
            data: formdata,
            dataType:'json',
            beforeSend : function()
            {
                showProcessingOverlay();
            },
            success:function(data)
            {
                hideProcessingOverlay();
           
                if('success' == data.status){
                    swal("Success", data.description, data.status);
                }
                else{
                    swal(data.status, data.description, data.status);
                }  
            }
        });
    }); 
</script>

@stop