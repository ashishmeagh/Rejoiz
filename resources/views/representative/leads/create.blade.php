@extends('representative.layout.master')
@section('main_content')

<style>
    .btn-primary,
    .btn-primary.disabled {
        width: 180px;
        padding: 8px 15px 6px;
        display: inline-block;
        border-radius: 3px;
    }

    th {
        white-space: nowrap;
    }

    .multiselect-container>li .checkbox input[type=checkbox] {
        opacity: 1;
    }

    .input-group-btn {
        display: none;
    }

    .dropdown-menu>.active>a,
    .dropdown-menu>.active>a:hover,
    .dropdown-menu>.active>a:focus {
        color: #564126;
        text-decoration: none;
        outline: 0;
        background-color: #ffe8ca;
        border-bottom: 1px solid #fff5e8;
    }

    ul.multiselect-container.dropdown-menu {
        max-height: 290px;
        overflow: auto;
    }

    .frms-slt {
        display: block;
        position: relative;
        margin-bottom: 13px;
        margin-top: -20px;
    }

    .frms-slt .parsley-errors-list {
        position: relative;
        bottom: -63px;
        z-index: 9;
        width: 100%;
        display: block;
    }

    .red.space-left-error {
        font-size: 12px;
        margin-left: 8px;
    }


    .select2-container {
        display: block;
        background-color: #fff;
        border: 1px solid #afafaf;
        border-radius: 0;
        box-shadow: none;
        color: #565656;
        height: 38px;
        max-width: 100%;
        padding: 7px 12px;
        position: relative;
    }

    .select2-container .select2-choice .select2-arrow {
        background: transparent;
        background-image: none;
        border-left: none;
    }
</style>


<link href="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/css/select2.css" rel="stylesheet" />

<script src="//cdnjs.cloudflare.com/ajax/libs/select2/4.0.0/js/select2.js"></script>

<div id="page-wrapper">
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">Customer Details</h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li><a href="{{url('/')}}/representative/dashboard">Dashboard</a></li>
                    <li><a href="{{url('/')}}/representative/leads">{{$module_title or ''}}</a></li>
                    <li class="active">Customer Details</li>
                </ol>
            </div>
            <!-- /.col-lg-12 -->

        </div>
        
        <!-- .row -->
        <div class="row">
            <div class="col-lg-12">
                @include('admin.layout._operation_status')
                <div class="white-box">
                    <div class="row">
                        <div class="col-md-12">
                            <ul class="tb-links tab-find-link">
                                <li class="active"> <a href="{{ $module_url_path.'/create'}}">Customer Details</a> </li>

                                @php
                                $order_no = isset($arr_address['order_no'])?$arr_address['order_no']:false;

                                $url = 'javascript:void(0)';
                                $ord_summary_url = 'javascript:void(0)';

                                if($order_no)
                                {
                                $url = url('/representative/leads/find_products/').'/'.base64_encode($order_no);

                                $ord_summary_url = url('/sales_manager/leads/order_summary/').'/'.base64_encode($order_no);
                                }
                                @endphp


                                <li> <a href="{{$url}}">Add Products</a> </li>
                                <li> <a href="{{$ord_summary_url}}">Order Summary</a> </li>
                                {{-- <li> <a href="javascript:void(0)">Order Summary</a> </li> --}}
                                {{-- <li> <a href="#">Confirm Lead</a> </li> --}}
                            </ul>
                        </div>

                        <div class="col-md-12">
                            <form id="address-frm">
                                {{ csrf_field() }}
                                <div class="title-billing-address">Shipping Address</div>

                                <div class="form-group">
                                    <label>Select Customer<span class="text-danger">*</span></label>
                                    <select class="select2" style="width: 100%" id="search-customer">

                                        <option class="li" value="">Select Customer</option>
                                        @if(isset($retailer_arr) && count($retailer_arr)>0)

                                        @foreach($retailer_arr as $key=>$retailer)
                                        @if(isset($arr_address) && count($arr_address)>0)

                                        <option class="l1" {{$arr_address['user_id'] == $retailer['retailer_id']? 'selected':''}} value="{{$retailer['retailer_id']}}">{{$retailer['retailer_details']['store_name'] or '-'}}

                                        </option>

                                        @else
                                        <option class="l1" value="{{$retailer['retailer_id']}}">                                            
                                        {{$retailer['retailer_details']['store_name'] or '-'}}

                                        </option>
                                        @endif

                                        @endforeach

                                        @endif

                                    </select>
                                </div>

                                <div class="main">

                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>First Name <span class="text-danger">*</span></label>
                                                <div>
                                                    <input type="text" class="form-control" placeholder="Enter First Name" id="ship_first_name" name="ship_first_name" data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Only alphabets are allowed" readonly="" data-parsley-required="true" data-parsley-required-message="Please enter first name" value="{{isset($arr_address['ship_first_name'])?$arr_address['ship_first_name']:''}}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>Last Name <span class="text-danger">*</span></label>
                                                <div>
                                                    <input type="text" class="form-control" placeholder="Enter Last Name" id="ship_last_name" data-parsley-pattern="^[a-zA-Z]+$" readonly="" data-parsley-pattern-message="Only alphabets are allowed" name="ship_last_name" data-parsley-required="true" data-parsley-required-message="Please enter last name" value="{{isset($arr_address['ship_last_name'])?$arr_address['ship_last_name']:''}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">                                        
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>Email Address <span class="text-danger">*</span></label>
                                                <div>
                                                    <input type="text" class="form-control" placeholder="Enter Email Address" id="ship_email" name="ship_email" data-parsley-required="true" data-parsley-required-message="Please enter email address" readonly="" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" value="{{isset($arr_address['ship_email'])?$arr_address['ship_email']:''}}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label>Street Address<span class="text-danger">*</span></label>
                                                <input type="text" name="shipping_street_address" class="form-control" placeholder="Street Address" id="shipping_street_address" data-parsley-required="true" row="5" data-parsley-required-message="Please enter street address" value="{{isset($arr_address['ship_street_address'])?$arr_address['ship_street_address']:''}}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">

                                        {{-- <div class="col-sm-6">
                        <div class="form-group">
                            <label>Complete Address <span class="text-danger">*</span></label>
                            <div>
                                <input type="text" class="form-control" placeholder="Enter Complete Address" id="ship_complete_addr" name="ship_complete_addr" data-parsley-required="true" data-parsley-required-message="Please enter complete address" value="{{isset($arr_address['ship_complete_address'])?$arr_address['ship_complete_address']:''}}">
                                    </div>
                                </div>
                        </div>
                        --}}
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Suite/Apt</label>
                                <input type="text" name="shipping_suite_apt" class="form-control" placeholder="Suite/Apt" id="shipping_suite_apt" row="5" value="{{isset($arr_address['ship_suit_apt'])?$arr_address['ship_suit_apt']:''}}">
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>State <span class="text-danger">*</span></label>
                                <div>
                                    <input type="text" class="form-control" placeholder="Enter State" id="ship_state" name="ship_state" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed" data-parsley-required="true" data-parsley-required-message="Please enter state" value="{{isset($arr_address['ship_state'])?$arr_address['ship_state']:''}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">                        
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>City <span class="text-danger">*</span></label>
                                <div>
                                    <input type="text" class="form-control" placeholder="Enter City" id="ship_city" name="ship_city" data-parsley-required="true" data-parsley-required-message="Please enter city" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed" value="{{isset($arr_address['ship_city'])?$arr_address['ship_city']:''}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Country<span class="text-danger">*</span></label>
                                <div>
                                    <select class="form-control" id="ship_country" name="ship_country" data-parsley-required="true" data-parsley-required-message="Please select country" placeholder="Select Country">
                                        <option value="">Select Country</option>
                                        @if(isset($country_arr) && count($country_arr)>0)
                                        @foreach($country_arr as $key=>$country)
                                        <option @if(isset($arr_address['ship_country']) && $arr_address['ship_country']==$country['id']) selected="selected" @endif value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                                        @endforeach
                                        @endif
                                    </select>
                                    <input type="hidden" name="hide_ship_country" id="hide_ship_country" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Mobile Number <span class="text-danger">*</span></label>
                                <div>
                                    <input type="text" class="form-control" 
                                    placeholder="Enter Mobile Number" 
                                    id="ship_mobile_no" name="ship_mobile_no" 
                                    data-parsley-required="true" 
                                    data-parsley-required-message="Please enter mobile number"
                                    minlength="10" 
                                    data-parsley-minlength-message="Mobile number should be 10 digits, including country code" data-parsley-maxlength="18" 
                                    data-parsley-maxlength-message="Mobile number must be less than 18 digits" data-parsley-pattern="^[0-9*#+]+$" 
                                    data-parsley-required data-parsley-pattern-message="Please enter valid mobile number"
                                    value="{{isset($arr_address['ship_mobile_no'])?$arr_address['ship_mobile_no']:''}}">
                                    
                                    <input type="hidden" name="hid_ship_mobile_no_country_code" id="hid_ship_mobile_no_country_code">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Zip/Postal Code <span class="text-danger">*</span></label>
                                <div>
                                    <input oninput="this.value = this.value.toUpperCase()" type="text" class="form-control" placeholder="Enter Zip/Postal Code" id="ship_zip_code" name="ship_zip_code" value="{{isset($arr_address['ship_zip_code'])?$arr_address['ship_zip_code']:''}}" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code">
                                </div>
                                <div class="red space-left-error" id="err_shipping_zip_code"></div>
                            </div>
                        </div>
                    </div>
                </div>





                <div class="title-billing-address">Billing Address </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <div class="form-check bd-example-indeterminate">
                                <label class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="same-as-billing" name="same_as_billing" value="1" @if(isset($arr_address['is_as_below']) && $arr_address['is_as_below']=='1' ) checked="" @endif>
                                    <span class="custom-control-indicator"></span>
                                    <span class="custom-control-description">Same as Shipping Address</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="main">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>First Name <span class="text-danger">*</span></label>
                                <div>
                                    <input type="text" class="form-control" name="bill_first_name" id="bill_first_name" readonly="" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name" data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Only alphabets are allowed" value="{{isset($arr_address['bill_first_name'])?$arr_address['bill_first_name']:""}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Last Name <span class="text-danger">*</span></label>
                                <div>
                                    <input type="text" class="form-control" placeholder="Enter Last Name" name="bill_last_name" id="bill_last_name" data-parsley-required="true" data-parsley-required-message="Please enter last name" readonly="" data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Only alphabets are allowed" value="{{isset($arr_address['bill_last_name'])?$arr_address['bill_last_name']:""}}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">                        
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label>Email Address <span class="text-danger">*</span></label>
                                <div>
                                    <input type="text" class="form-control" placeholder="Enter Email Address" id="bill_email" name="bill_email" data-parsley-required="true" data-parsley-required-message="Please enter email address" readonly="" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" value="{{isset($arr_address['bill_email'])?$arr_address['bill_email']:''}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {{-- <label>Complete Address <span class="text-danger">*</span></label>
                            <div>
                                <input type="text" class="form-control" placeholder="Enter Complete Address" name="bill_complete_addr" id="bill_complete_addr" data-parsley-required="true" data-parsley-required-message="Please enter complete address." value="{{isset($arr_address['bill_complete_address'])?$arr_address['bill_complete_address']:""}}">
                            </div> --}}

                            <label>Street Address<span class="text-danger">*</span></label>

                            <input type="text" name="billing_street_address" class="form-control" placeholder="Street Address" id="billing_street_address" data-parsley-required="true" row="5" data-parsley-required-message="Please enter street address" value="{{isset($arr_address['bill_street_address'])?$arr_address['bill_street_address']:""}}">
                        </div>
                    </div>
                    </div>
                    <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">

                            <label>Suite/Apt</label>

                            <input type="text" name="billing_suite_apt" class="form-control" placeholder="Suite/Apt" id="billing_suite_apt" row="5" value="{{isset($arr_address['bill_suit_apt'])?$arr_address['bill_suit_apt']:""}}">

                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>State <span class="text-danger">*</span></label>
                            <div>
                                <input type="text" class="form-control" placeholder="Enter State" name="bill_state" id="bill_state" data-parsley-required="true" data-parsley-required-message="Please enter state" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed" value="{{isset($arr_address['bill_state'])?$arr_address['bill_state']:""}}">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">                    
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>City <span class="text-danger">*</span></label>
                            <div>
                                <input type="text" class="form-control" placeholder="Enter City" name="bill_city" id="bill_city" data-parsley-required="true" data-parsley-required-message="Please enter city" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Only alphabets are allowed" value="{{isset($arr_address['bill_city'])?$arr_address['bill_city']:""}}">
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Country<span class="text-danger">*</span></label>
                            <div>
                                <select class="form-control" id="bill_country" name="bill_country" data-parsley-required="true" data-parsley-required-message="Please select country" placeholder="Select Country">
                                    <option value="">Select Country</option>
                                    @if(isset($country_arr) && count($country_arr)>0)
                                    @foreach($country_arr as $key=>$country)
                                    <option @if(isset($arr_address['bill_country']) && $arr_address['bill_country']==$country['id']) selected="selected" ; @endif value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name']}}</option>
                                    @endforeach
                                    @endif
                                </select>
                                <input type="hidden" name="hide_bill_country" id="hide_bill_country" value="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6">
                            <div class="form-group">
                                <label>Mobile Number <span class="text-danger">*</span></label>
                                <div>
                                    <input type="text" class="form-control" name="bill_mobile_no" id="bill_mobile_no" placeholder="Enter Mobile Number" 
                                    data-parsley-required="true" 
                                    data-parsley-required-message="Please enter mobile number" data-parsley-minlength-message="Mobile number should be 10 digits, including country code" data-parsley-maxlength="18" 
                                    data-parsley-maxlength-message="Mobile number must be less than 18 digits" data-parsley-pattern="^[0-9*#+]+$" 
                                    minlength="10"
                                    data-parsley-required data-parsley-pattern-message="Please enter valid mobile number"

                                    value="{{isset($arr_address['bill_mobile_no'])?$arr_address['bill_mobile_no']:""}}">
                                    <input type="hidden" name="hid_bill_mobile_no_country_code" id="hid_bill_mobile_no_country_code">       
                                </div>
                            </div>
                        </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label>Zip/Postal Code <span class="text-danger">*</span></label>
                            <div>
                                <input oninput="this.value = this.value.toUpperCase()" type="text" class="form-control" placeholder="Enter Zip/Postal Code" name="bill_zip" id="bill_zip" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" value="{{isset($arr_address['bill_zip_code'])?$arr_address['bill_zip_code']:""}}">
                            </div>
                            <div class="red space-left-error" id="err_billing_zip_code"></div>
                        </div>
                    </div>
                </div>
            </div>


            <input type="hidden" name="order_no" value="{{isset($arr_address['order_no'])?$arr_address['order_no']:false}}">
            <div class="create-button-leads mt-4">
                <button type="button" id="btn-save-addr" class="btn btn-block btn-outline btn-rounded btn-success">Next</button>
            </div>
            </form>
        </div>
    </div>
</div>
</div>
</div>
<!-- /.row -->
</div>
</div>
<script type="text/javascript">
    let module_url_path = "{{$module_url_path or ''}}";


    $(document).ready(function() {
        $('.select2').select2({
            templateResult: function(data) {
                // We only really care if there is an element to pull classes from
                if (!data.element) {
                    return data.text;
                }

                var $element = $(data.element);

                var $wrapper = $('<span></span>');
                $wrapper.addClass($element[0].className);

                $wrapper.text(data.text);

                return $wrapper;
            }
        });

        $("#ship_mobile_no").keydown(function(event) {
            var text_length = $("#ship_mobile_no").attr('code_length');
            if (event.keyCode == 8) {
                this.selectionStart--;
            }
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });
        $("#ship_mobile_no").keyup(function(event) {
            var text_length = ($("#ship_mobile_no").attr('code_length')) ? $("#ship_mobile_no").attr(
                'code_length') : "";
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });

        $("#ship_country").change(function() {

            var ship_country = $("#ship_country").val();
            var post_code = $("#ship_zip_code").val();

            var phone_code = $('option:selected', this).attr('phone_code');
            var zipcode_length = $('option:selected', this).attr('zipcode_length');
            var countryName = $('option:selected', this).attr('country_name');

            if (phone_code) {
                $("#ship_mobile_no").val('');
                $("#ship_mobile_no").val('+' + phone_code);
                $("#ship_mobile_no").attr('code_length', phone_code.length + 1);
                $("#hid_ship_mobile_no_country_code").val('+' + phone_code);
            } else {
                $("#ship_mobile_no").val('');
                $("#ship_mobile_no").attr(0);
                $("#hid_ship_mobile_no_country_code").val('');
            }

            if (ship_country == "" && post_code != "") {
                $("#err_post_code").html('Invalid zip/postal code');
            }

            var codeLength = jQuery('#hid_ship_mobile_no_country_code').val();
            var minPhone = 10 + codeLength.length;            
            $('#ship_mobile_no').attr('data-parsley-minlength', minPhone);

            if(zipcode_length == 8)
            {
                $('#ship_zip_code').attr('parsley-maxlength', true);
                $('#ship_zip_code').removeAttr('data-parsley-length');
                $('#ship_zip_code').attr('data-parsley-length-message', "");
                $("#ship_zip_code").attr({
                  "data-parsley-maxlength": zipcode_length,
                  "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
                    zipcode_length +
                    '  characters',
                });
            }
            else{
                $('#ship_zip_code').attr('parsley-maxlength', false);
                $('#ship_zip_code').attr('data-parsley-maxlength-message', "");
                $("#ship_zip_code").attr({
                "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
                "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                    zipcode_length +
                    '  digits'
                });
            }
           
            $('#address-frm').parsley();
        });

        $("#bill_mobile_no").keydown(function(event) {
            var text_length = $("#bill_mobile_no").attr('code_length');
            if (event.keyCode == 8) {
                this.selectionStart--;
            }
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });
        $("#bill_mobile_no").keyup(function(event) {
            var text_length = ($("#bill_mobile_no").attr('code_length')) ? $("#bill_mobile_no").attr(
                'code_length') : "";
            if (this.selectionStart < text_length) {
                this.selectionStart = text_length;
                event.preventDefault();
            }
        });

        $("#bill_country").change(function() {

            var bill_country = $("#bill_country").val();
            var bill_zip = $("#bill_zip").val();

            var phone_code = $('option:selected', this).attr('phone_code');
            var zipcode_length = $('option:selected', this).attr('zipcode_length');
            var countryName = $('option:selected', this).attr('country_name');

            if (phone_code) {
                $("#bill_mobile_no").val('');
                $("#bill_mobile_no").val('+' + phone_code);
                $("#bill_mobile_no").attr('code_length', phone_code.length + 1);
                $("#hid_bill_mobile_no_country_code").val('+' + phone_code);
            } else {
                $("#bill_mobile_no").val('');
                $("#bill_mobile_no").attr(0);
                $("#hid_bill_mobile_no_country_code").val('');
            }


            if (bill_country == "" && post_code != "") {
                $("#err_post_code").html('Invalid zip/postal code');
            }

            var codeLength = jQuery('#hid_bill_mobile_no_country_code').val();
            var minPhone = 10 + codeLength.length;            
            $('#bill_mobile_no').attr('data-parsley-minlength', minPhone);

            if(zipcode_length == 8)
            {
                $('#bill_zip').attr('parsley-maxlength', true);
                $('#bill_zip').removeAttr('data-parsley-length');
                $('#bill_zip').attr('data-parsley-length-message', "");
                $("#bill_zip").attr({
                  "data-parsley-maxlength": zipcode_length,
                  "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
                    zipcode_length +
                    '  characters',
                });
            }
            else{
                $('#bill_zip').attr('parsley-maxlength', false);
                $('#bill_zip').attr('data-parsley-maxlength-message', "");
                $("#bill_zip").attr({
                "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
                "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                    zipcode_length +
                    '  digits'
                });
            }
            
            $('#address-frm').parsley();
        });
    });
</script>
<script type="text/javascript" src="{{url('assets/js/module_js/representative/leads.js')}}"></script>
@stop