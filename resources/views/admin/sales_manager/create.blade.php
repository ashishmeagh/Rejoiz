@extends('admin.layout.master')
@section('main_content')
<!-- Page Content -->
<link rel="stylesheet" href="https://www.jquery-az.com/boots/css/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="https://www.jquery-az.com/boots/js/bootstrap-multiselect/bootstrap-multiselect.js"></script>
<style>
   .dropify-errors-container {display:none;}
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

   .multiselect-cts {
      position: relative;
   }

   .multiselect-cts .parsley-errors-list.filled {
      top: 35px;
      position: absolute;
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
   }

   .frms-slt .parsley-errors-list {
      position: relative;
      bottom: -63px;
      z-index: 9;
      width: 100%;
      display: block;
   }
</style>
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{url('/')}}/admin/dashboard">Dashboard</a></li>
               <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
               <li class="active">Create Sales Manager</li>
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



                        <div class="row sales_manager_create_row m-b-20">
                           <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">First Name<i class="red">*</i></label>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                              <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid first name" />
                              <span class="red">{{ $errors->first('first_name') }}</span>
                           </div>
                        </div>


                        <div class="row sales_manager_create_row m-b-20">
                           <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Last Name<i class="red">*</i></label>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                              <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" data-parsley-required="true" data-parsley-required-message="Please enter last name" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid last name" />
                              <span class="red">{{ $errors->first('last_name') }}</span>
                           </div>
                        </div>

                        <div class="row sales_manager_create_row m-b-20">
                           <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Email Id<i class="red">*</i></label>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                              <input type="text" class="form-control" name="email" placeholder="Enter Email Id" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" />
                              <span class="red">{{ $errors->first('email') }}</span>
                           </div>
                        </div>

                        <div class="row sales_manager_create_row m-b-20">
                           <label for="country" class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Country<i class="red">*</i></label>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                              <select name="country_id" id="country_id" data-parsley-required="true" class="form-control" data-parsley-required-message="Please select country">
                                 <option value="">Select Country</option>
                                 @if(isset($country_arr) && sizeof($country_arr)>0)
                                 @foreach($country_arr as $country)

                                 <option value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                                 }
                                 @endforeach
                                 @endif
                              </select>
                           </div>
                           <span class='red'>{{ $errors->first('country_id') }}</span>
                        </div>

                        <div class="row sales_manager_create_row m-b-20">
                           <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Contact No.<i class="red">*</i></label>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                              <input type="text" class="form-control" name="contact_no" id="contact_no" placeholder="Enter Contact No" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid contact number" data-parsley-required="true" data-parsley-required-message="Please enter contact number" data-parsley-minlength-message="Contact number should be 10 digits" data-parsley-maxlength="18" data-parsley-maxlength-message="Contact number must be less than 18 digits" />
                              <input type="hidden" name="hid_country_code" id="hid_country_code">
                              <span class="red">{{ $errors->first('contact_no') }}</span>
                           </div>
                        </div>

                        <div class="row sales_manager_create_row m-b-20">
                           <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Zip/Postal Code<i class="red">*</i></label>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                              <input oninput="this.value = this.value.toUpperCase()" type="text" class="form-control" name="post_code" placeholder="Enter Zip/Postal Code" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" data-parsley-trigger="change" id="pin_or_zip_code" />
                              <span class="red">{{ $errors->first('post_code') }}</span>
                           </div>
                        </div>

                        <div class="row sales_manager_create_row m-b-0">
                           <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Area<i class="red">*</i></label>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">

                              <select class="form-control" id="rep_area" name="rep_area" data-parsley-required="true" data-parsley-required-message="Please select area">

                                 <option value="">Select Area</option>

                                 @if(isset($area_names) && count($area_names)>0)

                                 @foreach($area_names as $key=>$area)
                                 <option value="{{$area['id']}}">{{$area['area_name']}}</option>
                                 @endforeach

                                 @endif
                              </select>

                              <span class="red">{{ $errors->first('rep_area') }}</span>
                           </div>

                        </div>

                        <br>



                        <div class="row sales_manager_create_row m-b-10" id="div_cat">
                           <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Category Division</label>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                              <div class="frms-slt">
                                 <select id="boot-multiselect-demo_category" name="category_id[]" multiple="multiple">

                                 </select>
                                 <div class="clearfix"></div>
                                 <span class="red">{{ $errors->first('category_id') }}</span>
                              </div>
                           </div>
                        </div>

                        <div class="row sales_manager_create_row m-b-20">
                           <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Commission(%)</label>
                           <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                              <input type="text" class="form-control" name="commission" placeholder="Enter Commission"  data-parsley-type="number" data-parsley-min="1" data-parsley-max="100"  data-parsley-type-message="Please enter valid commission" data-parsley-pattern-message='Please enter valid commission' data-parsley-max-message='Commission should be lower than or equal to 100' />
                              <span class="red" id="err_msg">{{ $errors->first('commission') }}</span>
                           </div>
                        </div>

                        <div class="row sales_manager_create_row">
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label" for="profile_image">Profile Image <i class="red">*</i></div>
                           <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                              <input type="file" name="profile_image" id="profile_image" class="dropify" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" data-parsley-required="true" data-parsley-required-message="Please select profile image" data-parsley-errors-container="#profile_error" />
                              <div id="profile_error"></div>
                           </div>
                        </div>

                        <span>{{ $errors->first('profile_image') }}</span>


                        <div class="clearfix"></div>
                        <div class="cancel_back_flex_btn mt-5">
                           <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                           <button type="button" class="btn btn-success waves-effect waves-light" id="btn_add" value="Save">Save</button>
                        </div>
                     </form>
                     <div class="clearfix"></div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
   <!-- END Main Content -->

   <script type="text/javascript">
      $(document).ready(function() {
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
               $("#err_post_code").html('Invalid zip/postal code');
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


      const module_url_path = "{{ $module_url_path or ''}}";
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


      $('#rep_area').change(function() {

         var area_id = $('#rep_area').val();

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
                     $.ajax({
                        url: module_url_path + '/check_area_exist',
                        method: 'POST',
                        data: {
                           area_id: area_id,
                           "_token": "{{ csrf_token() }}"
                        },
                        dataType: 'JSON',
                        success: function(response) {
                           if (response.count > 0) {
                              swal('Warning', "Area has already assigned.", "warning");
                              $('#rep_area').val('');
                              $("#div_cat").hide();

                           } else {
                              $('#boot-multiselect-demo_category').empty();
                              $('#boot-multiselect-demo_category').multiselect('rebuild');
                              $("#div_cat").hide();
                           }
                        }

                     });


                  }



               }

            });
         } else {
            $('#boot-multiselect-demo_category').append('<option value="">Select Division Category</option>');
         }

      });


      $('#boot-multiselect-demo_category').change(function() {
         var category_id = $('#boot-multiselect-demo_category').val();


         $.ajax({
            url: module_url_path + '/check_category_exist',
            method: 'POST',
            data: {
               category_div_id: category_id,
               "_token": "{{ csrf_token() }}"
            },
            dataType: 'JSON',
            success: function(response) {
               if (response.count > 0) {
                  swal('Warning', "Category division has already assigned.", "warning");

                  $('#boot-multiselect-demo_category').val('');
                  $('#boot-multiselect-demo_category').multiselect('rebuild');


               }
            }

         });

      });
   </script>
   @stop