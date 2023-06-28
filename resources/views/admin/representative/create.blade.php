@extends('admin.layout.master')
@section('main_content')
<!-- Page Content -->

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
    margin-bottom: 0px;
    margin-top: 0px;
  }

  .frms-slt .parsley-errors-list {
    position: relative;
    bottom: -63px;
    z-index: 9;
    width: 100%;
    display: block;
  }
</style>

<!-- For multiselect with search -->
<link rel="stylesheet" href="https://www.jquery-az.com/boots/css/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="https://www.jquery-az.com/boots/js/bootstrap-multiselect/bootstrap-multiselect.js"></script>

<div id="page-wrapper">
  <div class="container-fluid create_rep_form_white_box">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
        <h4 class="page-title">{{$page_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-12 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
          <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
          <li class="active">Create Representative</li>
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

              <form class="form-horizontal" id="validation-form">
                {{ csrf_field() }}


                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">First Name<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid first name" />
                    <span class="red">{{ $errors->first('first_name') }}</span>
                  </div>
                </div>


                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Last Name<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" data-parsley-required="true" data-parsley-required-message="Please enter last name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid last name" />
                    <span class="red">{{ $errors->first('last_name') }}</span>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Email Id<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <input type="text" class="form-control" name="email" placeholder="Enter Email Id" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" />
                    <span class="red">{{ $errors->first('email') }}</span>
                  </div>
                </div>



                <div class="form-group row">
                  <label for="country" class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Country<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <select name="country_id" id="country_id" data-parsley-required="true" class="form-control" data-parsley-required-message="Please select country">
                      <option value="">Select Country</option>
                      @if(isset($country_arr) && sizeof($country_arr)>0)
                      @foreach($country_arr as $country)

                      <option value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>

                      @endforeach
                      @endif
                    </select>
                  </div>
                  <span class='red'>{{ $errors->first('country_id') }}</span>
                </div>

                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Mobile Number<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <input type="text" class="form-control" name="contact_no" id="contact_no" placeholder="Enter Mobile Number" data-parsley-required="true" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number" data-parsley-required-message="Please enter mobile number" data-parsley-minlength-message="Mobile number should be 10 digits" data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile number must be less than 18 digits" />
                    <input type="hidden" name="hid_country_code" id="hid_country_code">
                    <span class="red">{{ $errors->first('contact_no') }}</span>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Zip/Postal Code<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <input oninput="this.value = this.value.toUpperCase()" type="text" class="form-control" name="post_code" placeholder="Enter Zip/Postal Code" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" data-parsley-trigger="change" id="pin_or_zip_code" />
                    <span class="red">{{ $errors->first('post_code') }}</span>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Sales Manager<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <select class="form-control" data-parsley-required="true" data-parsley-required-message="Please select sales manager" name="sales_manager_id" id="sales_manager_id">
                      <option value="">Select Sales Manager</option>
                      @if(isset($sales_manager_list) && count($sales_manager_list)>0)
                      @foreach($sales_manager_list as $sales_manager)
                      @if(isset($sales_manager['get_user_data']) && count($sales_manager['get_user_data'] > 0))
                      <option value="{{$sales_manager['user_id']}}">{{$sales_manager['get_user_data']['first_name'].' '.$sales_manager['get_user_data']['last_name']}}</option>
                      @endif
                      @endforeach
                      @endif
                    </select>
                    <span class="red">{{ $errors->first('sales_manager_id') }}</span>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Area<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <select class="form-control" data-parsley-required="true" data-parsley-required-message="Please select area" name="area_id" id="area_id">
                      <option value="">Select Area</option>

                    </select>
                    <span class="red">{{ $errors->first('area_id') }}</span>
                  </div>
                </div>


                <div class="form-group row" id="div_cat">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Category Division</label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <div class="frms-slt">
                      <select id="boot-multiselect-demo_category" name="category_id[]" multiple="multiple">


                      </select>
                      <div class="clearfix"></div>
                      <span class="red">{{ $errors->first('category_id') }}</span>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Vendor Represents<i class="red">*</i></label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <!-- <div class="frms-slt"> -->
                      <select id="boot-multiselect-demo" class = "select2 form-control" name="vendor_id[]" multiple="multiple" data-parsley-required="true" data-parsley-required-message="Please select vendor" data-parsley-errors-container="#err_container">

                        @if(isset($all_vendors) && count($all_vendors)>0)
                        @foreach($all_vendors as $vendor)
                        @if(isset($vendor['user_details']) && count($vendor['user_details'] > 0))

                        <option value="{{$vendor['user_id']}}">{{$vendor['company_name']}}</option>
                        @endif
                        @endforeach

                        @endif
                      </select>
                      <div class="clearfix"></div>
                      <span id="err_container" class="red">{{ $errors->first('vendor_id') }}</span>
                    <!-- </div> -->
                  </div>
                </div>

                <div class="form-group row">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Commission(%)</label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <input type="text" class="form-control" name="commission" placeholder="Enter Commission" {{-- data-parsley-required="true" data-parsley-required-message="please enter commission." --}} data-parsley-type="number" data-parsley-min="1" data-parsley-max="100" data-parsley-type-message="Please enter valid commission" data-parsley-pattern-message='Please enter valid commission' data-parsley-max-message='Commission should be lower than or equal to 100' />
                    <span class="red" id="err_msg">{{ $errors->first('commission') }}</span>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label" for="profile_image">Profile Image <i class="red">*</i></div>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                    <input type="file" name="profile_image" id="profile_image" class="dropify" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" data-parsley-errors-container="#profile_error" data-parsley-required="true" data-parsley-required-message="Please select profile image" />
                    <div id="profile_error"></div>
                  </div>
                </div>
                <span>{{ $errors->first('profile_image') }}</span>


                <div class="form-group row">
                  <div class="col-sm-12 cancel_back_flex_btn">
                    <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>

                    <button type="button" class="btn btn-success waves-effect waves-light " id="btn_add" value="Save">Save</button>
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
      $(document).ready(function() {
        // $('#boot-multiselect-demo').multiselect({
        //   includeSelectAllOption: false,
        //   enableFiltering: true,
        //   nonSelectedText: 'Select Vendors'
        // });

        $('#boot-multiselect-demo').select2({
          placeholder: 'Select vendor'
        });

        $('#boot-multiselect-demo').html('');

        $('#boot-multiselect-demo_category').multiselect({
          includeSelectAllOption: false,
          enableFiltering: true,
          nonSelectedText: 'Select Category Division'
        });


      });




      $(document).ready(function() {
      



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

        $("#country_id").change(function(event) {
          var country_code = $("#country_id").val();
          var post_code = $("#pin_or_zip_code").val();

          var phone_code = $('option:selected', this).attr('phone_code');
          var zipcode_length = $('option:selected', this).attr('zipcode_length');
          var countryName = $('option:selected', this).attr('country_name');

          if (phone_code) {
            $("#contact_no").val('+' + phone_code);
            $("#contact_no").attr('code_length', phone_code.length + 1);
            $("#hid_country_code").val('+' + phone_code);
          } else {
            $("#contact_no").val('');
            $("#contact_no").attr(0);
            $("#hid_country_code").val('');
          }

          if (country_code == "" && post_code != "") {
            $("#err_post_code").html('Invalid zip/postal code.');
          }

          var codeLength = jQuery('#hid_country_code').val();
            var minPhone = 10 + codeLength.length;            
            $('#contact_no').attr('data-parsley-minlength', minPhone);

          if(zipcode_length == 8)
            {
                $('#pin_or_zip_code').attr('parsley-maxlength', true);
                $('#pin_or_zip_code').removeAttr('data-parsley-length');
                $('#pin_or_zip_code').attr('data-parsley-length-message', "");
                $("#pin_or_zip_code").attr({
                  "data-parsley-maxlength": zipcode_length,
                  "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
                    zipcode_length +
                    '  characters',
                });
            }
            else{
                $('#pin_or_zip_code').attr('parsley-maxlength', false);
                $('#pin_or_zip_code').attr('data-parsley-maxlength-message', "");
                $("#pin_or_zip_code").attr({
                "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
                "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                    zipcode_length +
                    '  digits'
                });
            }
          
          $('#signup-form').parsley();
        });
      });


      const module_url_path = "{{ $module_url_path }}";
      $(document).ready(function() {
        $('#validation-form').parsley();
        $('#btn_add').click(function() {

          if ($('#validation-form').parsley().validate() == false) return;
          else {
            var formdata = new FormData($("#validation-form")[0]);



            $('#err_msg').html("");
            $.ajax({
              url: module_url_path + '/save',
              type: "POST",
              data: formdata,
              contentType: false,
              processData: false,
              dataType: 'json',
              beforeSend: function() {
                showProcessingOverlay();
              },
              success: function(data) {
                hideProcessingOverlay();
                if (data.status == "success") {

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
          }
        });
      });

      function toggle_status() {
        var site_status = $('#site_status').val();
        if (site_status == '1') {
          $('#site_status').val('1');
        } else {
          $('#site_status').val('0');
        }
      }


      $('#sales_manager_id').change(function() {
        var sales_manager_id = $('#sales_manager_id').val();
        var area_id = $('#area_id').val();

        if (sales_manager_id != '') {
          if (area_id == "" || area_id == "Select Area") {
            $("#area_id").attr("data-parsley-required", "true");
          }

          $.ajax({
            url: module_url_path + '/fetch_area',
            method: 'POST',
            data: {
              sales_manager_id: sales_manager_id,
              "_token": "{{ csrf_token() }}"
            },
            dataType: 'JSON',
            success: function(response) {

              var html = '<option>Select Area</option>';

              for (var i = 0; i < response[0].length; i++) {
                var obj_area = response[0][i];

                
                  html += "<option value='" + obj_area.area_details.id + "'>" + obj_area.area_details.area_name + "</option>";
              }

              $('#area_id').html(html);

              var vendor_html = '<option>Select Vendors</option>';

              for (var i = 0; i < response[1].length; i++) {
                var obj_vendor = response[1][i];


                // <option value="{{$vendor['user_id']}}">{{$vendor['company_name']}}</option>
                vendor_html += "<option value='" + obj_vendor.vendor_id + "'>" + obj_vendor.get_maker_details.company_name + "</option>";
                

              }
              //console.log(vendor_html);
              $("#boot-multiselect-demo").html(vendor_html);

            }

          });
        } else {
          $('#area_id').html('<option value="">Select Area</option>');
        }

      });


      $('#area_id').change(function() {

        var area_id = $('#area_id').val();

        if (area_id != '') {
          $.ajax({
            url: module_url_path + '/fetch_category',
            method: 'POST',
            data: {
              area_id: area_id,
              "_token": "{{ csrf_token() }}"
            },
            dataType: 'JSON',
            success: function(response) {
              html = '';

              if (response.length > 0) {
                for (var i = 0; i < response.length; i++) {
                  var obj_category = response[i];

                  html += "<option value='" + obj_category.id + "'>" + obj_category.cat_division_name + "</option>";

                }

                $('#boot-multiselect-demo_category').empty();

                $("#boot-multiselect-demo_category").append(html);

                $('#boot-multiselect-demo_category').multiselect('rebuild');
                $("#div_cat").show();

              } else {
                $('#boot-multiselect-demo_category').empty();
                $('#boot-multiselect-demo_category').multiselect('rebuild');
                $("#div_cat").hide();

              }

            }


          });
        } else {
          $('#boot-multiselect-demo_category').append('<option value="">Select Division Category</option>');
        }

      });
    </script>
    @stop