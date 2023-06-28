@extends('maker.layout.master')  
@section('main_content')
<style type="text/css">
    .mainshop-box-left{
    float: left;
    }
    .mainshop-box-right{margin-left: 100px;}
    .mainshop-box{
    margin-bottom: 20px;
    color: #555;
    display: block;
    }
    .mainshop-box:hover{
    color: #444;
    }
    .titleshop-t {
    font-size: 17px;
    font-weight: 600;
    margin-bottom: 5px;
    }
    .mainshop-box-left i{
    font-size: 70px;
    }
    /*.checkbox label::before{    background-color: #a7a7a7 !important;}*/
    .checkbox.checkbox-success.hover-effects{position: relative; display: inline-block;}
    .checkbox.checkbox-success.hover-effects .hovercheckout-content {
    font-size: 12px;
    position: absolute;
    width: 280px;
    padding: 10px;
    top: -10px;
    left: 30px;font-weight: 600;
    opacity: 0;
    background-color: #000;
    border-radius: 5px;
    color: #fff;
    }
    .checkbox.checkbox-success.hover-effects:hover .hovercheckout-content{ opacity: 1 !important;}
    .checkbox.checkbox-success.hover-effects .hovercheckout-content:after {
    margin-left: 0;
    right: 99%;
    top: 50%;
    border: solid transparent;
    content: " ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
    border-color: rgba(136, 183, 213, 0);
    border-right-color: #000000;
    border-width: 11px;
    margin-top: -19px;
    }
    .form-group {width:auto;}
</style>
<div id="page-wrapper" >
<div class="container-fluid">
<div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Sales Terms</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
            <li><a href="{{url(config('app.project.maker_panel_slug').'/dashboard')}}">Dashboard</a></li>
            <li><a href="{{url(config('app.project.maker_panel_slug').'/company_settings')}}">Company Settings </a></li>
            <li class="active">Sales Terms</li>
        </ol>
    </div>
    <!-- /.col-lg-12 -->
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="white-box">
            <form class="form-horizontal" id="validation-form">
                {{ csrf_field() }}
                {{--   
                <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-3 control-label" for="cover_image">First Order minimum<i class="red">*</i> </label>
                    <div class="col-sm-12 col-md-12 col-lg-9">
                        <input type="tex" name="first_order_min" id="first_order_min" data-errors-position="outside" />
                    </div>
                </div>
                --}}
                <div class="form-group row">
                    <label class="col-sm-12 col-md-12 col-lg-3 col-form-label">Minimum Order Amount ($)<i class="red">*</i></label>
                    <div class="col-sm-12 col-md-12 col-lg-9">
                        <input type="text" class="form-control" name="first_order_min" id = "first_order_min"  placeholder="Enter First Order Minimum" data-parsley-required="true" data-parsley-required-message="Please enter first order minimum"
                            value = "{{$shop_data['first_order_minimum']}}" data-parsley-type="number"/>
                        <span class="red">{{-- {{ $errors->first('tax_id') }} --}}</span>
                    </div>
                </div>
                <div class="form-group row">
        <label for="shop_lead_time" class="col-sm-12 col-md-12 col-lg-3 col-form-label">Company Lead Time<i class="red">*</i></label>
        <div class="col-sm-12 col-md-12 col-lg-9">
        <select name="shop_lead_time" id="shop_lead_time" class="form-control" data-parsley-required ="true" data-parsley-required-message="Please enter company lead time" value ="" >
        <option value="2-4" @if(isset($shop_data['shop_lead_time']) && $shop_data['shop_lead_time']=='2-4') selected="" @endif>2-4 Days</option>
        <option value="4-5" @if(isset($shop_data['shop_lead_time']) && $shop_data['shop_lead_time']=='4-5') selected="" @endif>4-5 Days</option>
        <option value="8-10" @if(isset($shop_data['shop_lead_time']) && $shop_data['shop_lead_time']=='8-10') selected="" @endif>8-10 Days</option>
        <option value="13-15" @if(isset($shop_data['shop_lead_time']) && $shop_data['shop_lead_time']=='13-15') selected="" @endif>13-15 Days</option>
        <option value="15-30" @if(isset($shop_data['shop_lead_time']) && $shop_data['shop_lead_time']=='15-30') selected="" @endif>15-30 Days</option>
        </select>
        </div>
        </div>
        <div class="form-group row">
        <label for="category" class="col-sm-12 col-md-4 col-lg-3 col-form-label">Split order free shipping</label>
        <div class="col-sm-12 col-md-8 col-lg-9"> 
        <div class="checkbox checkbox-success hover-effects">
        <input type="checkbox" name="split_order_free_shipping" id="split_order_free_shipping" @php 
        if(isset($shop_data['split_order_free_shipping']) && $shop_data['split_order_free_shipping']==1)
        echo "checked=checked";
        @endphp>
        <label for="split_order_free_shipping">  </label>
        <div class="hovercheckout-content">When checked then free shipping for 2nd order of split iteration.</div>
        </div>
        </div>  
        </div>
        <!-- <div class="form-group row">
        <label for="category" class="col-3 col-form-label">Vacation Mode</label>
        <div class="col-3">
        <input type="text" class="form-control datepicker" name="vacation_mode_start" id="vacation_mode_start" placeholder="Start Date"
        @isset($shop_data['vacation_mode_start']) value = " {{ isset($shop_data['vacation_mode_start'])?date('d-m-Y',strtotime($shop_data['vacation_mode_start'])):''}}"
        @endisset/>
        </div>
        <div class="col-3">
        <input type="text" class="form-control datepicker" name="vacation_mode_end" id="vacation_mode_end" placeholder="End Date" @isset($shop_data['vacation_mode_end'])
        value="{{isset($shop_data['vacation_mode_end'])?date('d-m-Y',strtotime($shop_data['vacation_mode_end'])):''}}" @endisset/>
        </div>
        </div> -->
        <div class="form-group">
        <div class="col-md-12 common_back_save_btn">
        <a class="btn btn-inverse waves-effect waves-light" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>
        <button type="button" class="btn btn-success waves-effect waves-light" id="btn_update" value="Save">Update</button>
        </div>
        </div>
        </div>
        </form>
    </div>
</div>
<script type="text/javascript">
    const module_url_path = "{{ $module_url_path }}";
     $(document).ready(function(){
      $( function() {
        $( ".datepicker" ).datepicker();
        });
        $('#btn_update').click(function(){
          
      /*    var vacation_mode_start = $('#vacation_mode_start').val();
    ​
          if(vacation_mode_start != '' && $('#vacation_mode_end').val() == '')
          { 
            swal('Warning', 'Please select vacation end date.');
            return false ;
          }*/
          
         
         if($('#validation-form').parsley().validate()==false)return;
           
          var formdata = new FormData($("#validation-form")[0]);
          
          $.ajax({
            url: module_url_path+'/save_settings',
            type:"POST",
            data: formdata,
            contentType:false,
            processData:false,
            dataType:'json',
            beforeSend : function()
            {
              showProcessingOverlay();
            },
            success:function(data)
            {
               hideProcessingOverlay(); 
               if('success' == data.status)
               {
                   //swal(data.status,data.description,data.status);
                  swal( {
                         title: "Success",
                         text: data.description,
                         type: data.status,
                         confirmButtonText: "OK",
                         closeOnConfirm: false
                        },
                       function(isConfirm,tmp)
                       {
                         if(isConfirm==true)
                         {
                            window.location = data.url;
                         }
                       });
                }
                else
                {
                   swal('Error',data.description,'error');
                }  
            }
          });   
        });
     });
</script>
@stop

