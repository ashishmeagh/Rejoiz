@extends('representative.layout.master')
@section('main_content')
<!-- Page Content -->
<div id="page-wrapper">
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Add Customer</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="{{url('/')}}/representative/dashboard">Dashboard</a></li>
          <li><a href="{{$module_url_path}}">My Customers</a></li>
          <li class="active">Add Customer</li>
        </ol>
      </div>
      <!-- /.col-lg-12 -->
    </div>
    <!-- BEGIN Main Content -->
    <div class="row">
      <div class="col-sm-12">
        <div class="white-box">
          @include('admin.layout._operation_status')

          <form class="form-horizontal" id="validation-form">
            {{ csrf_field() }}



            <div class="main">
              <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="form-group">
                    <label class="col-form-label">First Name<i class="red">*</i></label>
                    <div>
                      <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name" data-parsley-pattern="^[a-zA-Z]+$"/>
                      <span class="red">{{ $errors->first('first_name') }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="form-group">
                    <label class="col-form-label">Last Name<i class="red">*</i></label>
                    <div>
                      <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" data-parsley-required="true" data-parsley-required-message="Please enter last name" data-parsley-pattern="^[a-zA-Z]+$"/>
                      <span class="red">{{ $errors->first('Last_name') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="form-group">
                    <label class="col-form-label">Email<i class="red">*</i></label>
                    <div>
                      <input type="text" class="form-control" name="email" placeholder="Enter Email" data-parsley-required="true" data-parsley-required-message="Please enter email" data-parsley-type="email" data-parsley-type-message="Please enter valid email" />
                      <span class="red">{{ $errors->first('email') }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="form-group">
                    <label class="col-form-label">Tax ID<i class="red">*</i></label>
                    <div>
                      <input type="text" class="form-control" name="tax_id" placeholder="Enter Your Tax Id" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="{{ url('/does_exists_tax_id/tax-id') }}" data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}' data-parsley-remote-message="Tax Id already exists" data-parsley-required="true" data-parsley-required-message="Please enter tax id" data-parsley-length="[9, 9]" data-parsley-length-message ="Tax id is invalid. It should have 9 digits" data-parsley-type="integer"/>
                      <span class="red">{{ $errors->first('tax_id') }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="form-group">
                    <label class="col-form-label">Country<i class="red">*</i></label>
                    <div>
                      <select name="country_code" id="country_code" data-parsley-required="true" data-parsley-required-message="Please select country" class="form-control">
                        <option value="">Select Country</option>
                        @if(isset($country_arr) && sizeof($country_arr)>0)
                        @foreach($country_arr as $country)
                        <option value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>

                        @endforeach
                        @endif

                      </select>
                    </div>
                  </div>
                </div>

                <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="form-group">
                    <label class="col-form-label">Mobile Number<i class="red">*</i></label>
                    <div>
                      <input type="text" class="form-control" name="contact_no" id="contact_no" placeholder="Enter Mobile No" data-parsley-required="true" data-parsley-required-message="Please enter mobile number" data-parsley-minlength-message="Mobile number should be 10 digits" data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile No must be less than 18 digits" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number" />
                      <input type="hidden" name="hid_country_code" id="hid_country_code">
                      <span class="red">{{ $errors->first('contact_no') }}</span>
                    </div>
                  </div>
                </div>
                <div class="col-sm-12 col-md-12 col-lg-6">
                  <div class="form-group">
                    <label class="col-form-label">Are you buying for a physical retail store?</label>
                    <div>
                      <select name="buying_status" id="buying_status" class="form-control" onchange="retailerStore($(this));">
                        <option>Select</option>
                        <option value="1">Yes,I am buying for a physical retail store</option>
                        <option value="2">No,I am buying for an online-only store</option>
                        <option value="3">No,I am buying for a pop-up shop</option>
                        <option value="4">None of the above apply</option>
                      </select>
                    </div>
                  </div>
                </div>

                <!-- Extra information -->
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6" id="store_data_container" style="display:none">
                  <div class="form-group" >
                     <label>Years in business<i class="red">*</i></label>
                        <input type="text" class="form-control" name="years_in_business" id="years_in_business" placeholder="Years in business"/>
                      <span class="red">{{ $errors->first('years_in_business') }}</span>
                       
                  </div>

                </div>

              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6" id="store_data_containers" style="display:none">
                <div class="form-group" >
                    <label>Annual Sales</label> 
                       <input class="form-control" placeholder="Annual Sales" type="text" name="Annual Sales" id="Annual_Sales"/>
                  </div> 
              </div>

              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6" id="pop_up_data_container" style="display:none">
                <div class="form-group" >
                  <label class="col-form-label">Enter Zip Code<i class="red">*</i></label>
                   <input oninput="this.value = this.value.toUpperCase()" type="text" class="form-control" name="zip_code" id="zip_code" placeholder="Enter Zip Code"/>
                   <span class="red">{{ $errors->first('state') }}</span>
                 </div>
              </div>

              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6" id=store_website_container style="display:none">
                <div class="form-group" >
                     <label class="col-form-label">Store Website<i class="red">*</i></label>
                        <input class="form-control" placeholder="Enter your store website" type="text" name="store_website" id="store_website" data-parsley-maxlength="50" data-parsley-type="url"/>
                    
                  </div>
              </div>

              <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6" style="display:none" id="store_name_container">
                <div class="form-group">
                  <label class="col-form-label">Store Name<i class="red">*</i></label>
                    <input class="form-control" placeholder="Enter your store name" type="text" name="store_name" id="store_name" data-parsley-maxlength="50"/> 
                </div>
              </div>

              </div>
            </div>
            <div class="form-group">
              <div class="col-md-12 common_back_save_btn p-0">
                <a class="btn btn-success waves-effect waves-light pull-left backbtn" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="button" class="btn btn-success waves-effect waves-light pull-right" id="btn_add" value="Save">Save</button>
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
  const module_url_path = "{{ $module_url_path or '' }}";

  function validateZipAndPhoneCode()
  {
      var country_code = $("#country_code").val();
      var post_code = $("#zip_code").val();
      var buying_status = $("#buying_status").val();

      var phone_code = $('option:selected', "#country_code").attr('phone_code');
      var zipcode_length = $('option:selected', "#country_code").attr('zipcode_length');
      var countryName = $('option:selected', "#country_code").attr('country_name');

      var codeLength = jQuery('#hid_country_code').val();
      var minPhone = 10 + codeLength.length;            
      $('#contact_no').attr('data-parsley-minlength', minPhone);

      if (phone_code) {
        $("#contact_no").val('+' + phone_code);
        $("#contact_no").attr('code_length', phone_code.length + 1);
        $("#hid_country_code").val('+' + phone_code);
      } else {
        $("#contact_no").val('');
        $("#contact_no").attr(0);
        $("#hid_country_code").val('');
      }

      if(buying_status == 3)
      {
        if(zipcode_length == 8)
        {
          $('#zip_code').attr('parsley-maxlength', true);
          $('#zip_code').removeAttr('data-parsley-length');
          $('#zip_code').attr('data-parsley-length-message', "");
          $("#zip_code").attr({
            "data-parsley-maxlength": zipcode_length,
            "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
              zipcode_length +
              '  characters',
          });
          }
          else{
          $('#zip_code').attr('parsley-maxlength', false);
          $('#zip_code').attr('data-parsley-maxlength-message', "");
          $("#zip_code").attr({
          "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
          "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
              zipcode_length +
              '  digits'
          });
        }
      }

      $('#validation-form').parsley();
  }

  $(document).ready(function() {

    $("#country_code").change(function(event) {
      validateZipAndPhoneCode();
    });

    $("#contact_no").keydown(function(event) {
      var text_length = $("#contact_no").attr('code_length');
      if (event.keyCode == 8) {
        this.selectionStart--;
      }
      if (this.selectionStart < text_length) {
        this.selectionStart = text_length;
        event.preventDefault();
      }
    });
    $("#contact_no").keyup(function(event) {
      var text_length = ($("#contact_no").attr('code_length')) ? $("#contact_no").attr(
        'code_length') : "";
      if (this.selectionStart < text_length) {
        this.selectionStart = text_length;
        event.preventDefault();
      }
    });

    $('#btn_add').click(function() {
 
      var buying_status = $("#buying_status").val();

      var country_code = $("#country_code").val();

      var phone_code = $('#country_code option:selected').attr('phone_code');
      var zipcode_length = $('#country_code option:selected').attr('zipcode_length');
      var countryName = $('#country_code option:selected').attr('country_name');

      var codeLength = jQuery('#hid_country_code').val();
      var minPhone = 10 + codeLength.length;            
      $('#contact_no').attr('data-parsley-minlength', minPhone);

      if(buying_status == 3)
      {
        if(zipcode_length == 8)
        {
          $('#zip_code').attr('parsley-maxlength', true);
          $('#zip_code').removeAttr('data-parsley-length');
          $('#zip_code').attr('data-parsley-length-message', "");
          $("#zip_code").attr({
            "data-parsley-maxlength": zipcode_length,
            "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
              zipcode_length +
              '  characters',
          });
          }
          else{
          $('#zip_code').attr('parsley-maxlength', false);
          $('#zip_code').attr('data-parsley-maxlength-message', "");
          $("#zip_code").attr({
          "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
          "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
              zipcode_length +
              '  digits'
          });
        }
      } 
      else{

            $("#store_name").removeAttr("data-parsley-required");
            $("#zip_code").removeAttr("data-parsley-required");
          }
     

      if ($('#validation-form').parsley().validate() == false) return;
      var formdata = new FormData($("#validation-form")[0]);

      $.ajax({
        url: module_url_path + '/save',
        type: "POST",
        data: formdata,
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
          showProcessingOverlay();
          // $('#btn_submit').prop('disabled',true);
          // $('#btn_submit').html('Updating... <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
        },
        success: function(data) {
          hideProcessingOverlay();
          if ('success' == data.status) {

            $('#validation-form')[0].reset();

            swal({
                title: "Success",
                text: data.description,
                type: data.status,
                confirmButtonText: "OK",
                closeOnConfirm: false
              },
              function(isConfirm, tmp) {
                if (isConfirm == true) {
                  window.location = data.url;
                }
              });
          } else {

            swal("Error", data.description, data.status);
          }
        }

      });

    });
  });






  function retailerStore(ref)
  {
    var selected_value = $(ref).children("option:selected").val();

    if(selected_value == 1)
    {
      $("#store_name_container").show();
      $("#store_data_container").show();
      $("#store_data_containers").show();
      $("#store_description_container").hide();
      $("#store_website_container").hide();
      $("#pop_up_data_container").hide();

      $("#store_name").attr("data-parsley-required","true");
      $("#store_name").attr("data-parsley-required-message","Please enter store name");
      $("#years_in_business").attr("data-parsley-required","true");
      $("#years_in_business").attr("data-parsley-required-message","Please enter how many years you were in business");


      $("#store_website").removeAttr("data-parsley-required");
      $("#zip_code").removeAttr("data-parsley-required");
      $("#store_description").removeAttr('data-parsley-required');
      $("#store_website").removeAttr('data-parsley-required');

    }
    else if(selected_value == 2)
    {
      $("#store_name_container").hide();
      $("#store_website_container").show();
      $("#store_name_container").show();
      $("#store_description_container").hide();
      $("#pop_up_data_container").hide();
      $("#store_data_container").hide();
      $("#store_data_containers").hide();

      $("#store_website").attr("data-parsley-required","true");
      $("#store_website").attr("data-parsley-required-message","Please enter store Website");

      $("#store_name").attr("data-parsley-required","true");
      $("#store_name").attr("data-parsley-required-message","Please enter store name");


      $("#years_in_business").removeAttr('data-parsley-required');
      $("#zip_code").removeAttr('data-parsley-required');
      $("#store_description").removeAttr('data-parsley-required');
    }
    else if(selected_value == 3)
    {
      $("#pop_up_data_container").show();
      $("#store_name_container").show();
      $("#store_description_container").hide();
      $("#store_data_container").hide();
      $("#store_data_containers").hide();
      $("#store_website_container").hide();

      $("#zip_code").attr("data-parsley-required","true");
      $("#zip_code").attr("data-parsley-required-message","Please enter zip/postal code");

      $("#store_name").attr("data-parsley-required","true");
      $("#store_name").attr("data-parsley-required-message","Please enter store name");
      $("#years_in_business").removeAttr('data-parsley-required');
      $("#store_description").removeAttr('data-parsley-required');
      $("#store_website").removeAttr('data-parsley-required');

    }
    else
    {
      $("#pop_up_data_container").hide();
      $("#store_name_container").hide();
      $("#store_description_container").hide();
      $("#store_data_container").hide();
      $("#store_data_containers").hide();
      $("#store_website_container").hide();

      $("#years_in_business").removeAttr('data-parsley-required');
      $("#store_description").removeAttr('data-parsley-required');
      $("#store_website").removeAttr('data-parsley-required');

      // $("#store_name").attr("data-parsley-required","true");
      // $("#store_name").attr("data-parsley-required-message","Please enter store name");

      // $("#store_description").attr("data-parsley-required","true");
      // $("#store_description").attr("data-parsley-required-message","Please enter store description");

    }
  }

  function toggle_status() {
    var site_status = $('#site_status').val();
    if (site_status == '1') {
      $('#site_status').val('1');
    } else {
      $('#site_status').val('0');
    }
  }
</script>
@stop