@extends('admin.layout.master')
@section('main_content')
<link rel="stylesheet" href="https://www.jquery-az.com/boots/css/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="https://www.jquery-az.com/boots/js/bootstrap-multiselect/bootstrap-multiselect.js"></script>
<style>
   .multiselect-cts {
      position: relative;
   }

   .multiselect-cts .parsley-errors-list.filled {
      top: 38px;
      position: absolute;
   }

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
      /*margin-bottom: 13px;*/
      /*margin-top: -20px;*/
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
               <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
               <li class="active">{{$page_title or ''}} </li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-sm-12">
            <div class="white-box">
               <form class="form-horizontal" id="validation-form">
                  {{ csrf_field() }}


                  <input type="hidden" name="user_id" id="user_id" value="{{$arr_user_data['id']}}">

                  <input type="hidden" name="old_profile_image" value="{{$arr_user_data['profile_image']}}">


                  <div class="form-group row sales_manager_create_row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">First Name<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name" value="{{$arr_user_data['first_name']}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid first name" />
                        <span class="red">{{ $errors->first('first_name') }}</span>
                     </div>
                  </div>
                  <div class="form-group row sales_manager_create_row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Last Name<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" data-parsley-required="true" data-parsley-required-message="Please enter last name" value="{{$arr_user_data['last_name']}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid last name" />
                        <span class="red">{{ $errors->first('last_name') }}</span>
                     </div>
                  </div>
                  <div class="form-group row sales_manager_create_row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Email Id<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" name="email" readonly="" placeholder="Enter Email" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" value="{{$arr_user_data['email']}}" />
                        <span class="red">{{ $errors->first('email') }}</span>
                     </div>
                  </div>

                  <div class="form-group row sales_manager_create_row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Country<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">

                        <select name="country_id" id="country_id" data-parsley-required="true" data-parsley-required-message="Please select country" class="form-control">
                           <option value="">Select Country</option>
                           @if(isset($country_arr) && sizeof($country_arr)>0)
                           @foreach($country_arr as $country)

                           <option value="{{$country['id']}}" @if($arr_user_data['country_id']==$country['id']) selected @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>

                           @endforeach
                           @endif
                        </select>

                        <input type="hidden" name="hide_country_id" id="hide_country_id" value="{{$arr_user_data['country_id'] or ''}}">


                        <span class="red">{{ $errors->first('country_code') }}</span>
                     </div>
                  </div>

                  <div class="form-group row sales_manager_create_row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Contact No.<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" name="contact_no" id="contact_no" placeholder="Enter Contact No" data-parsley-required="true" data-parsley-required-message="Please enter contact number" data-parsley-maxlength="18" data-parsley-maxlength-message="Contact number must be less than 18 digits" data-parsley-minlength-message="Contact number should be of 10 digits" data-parsley-pattern="^[0-9*#+]+$" data-parsley-pattern-message="Please enter valid contact number" value="{{$arr_user_data['contact_no']}}" />
                        <input type="hidden" name="hid_country_code" id="hid_country_code">
                        <span class="red">{{ $errors->first('contact_no') }}</span>
                     </div>
                  </div>

                  <div class="form-group row sales_manager_create_row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Zip/Postal Code<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input oninput="this.value = this.value.toUpperCase()" type="text" name="post_code" class="form-control" placeholder="Zip/Postal Code" data-parsley-trigger="change" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" value="{{$arr_user_data['post_code'] or ''}}" id="pin_or_zip_code" >
                     </div>
                  </div>                  

                  <div class="form-group row sales_manager_create_row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Area<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">

                        <div class="multiselect-cts">

                           <select class="form-control" id="rep_area" name="rep_area" data-parsley-required="true" data-parsley-required-message="Please select area">

                              <option value="">Select Area</option>

                              @if(isset($area_names) && count($area_names)>0)

                              @foreach($area_names as $key=>$area)
                              <option value="{{$area['id']}}" @if($area_id==$area['id']) selected @endif>{{$area['area_name']}}</option>
                              @endforeach

                              @endif
                           </select>
                        </div>
                     </div>
                  </div>

                  @php
                  $count = isset($category_div_arr[0])?count($category_div_arr[0]):0;
                  @endphp

                  <input type="hidden" name="div_category_count" id="div_category_count" value="{{$count}}">


                  {{--old code 
                  <div class="form-group row sales_manager_create_row" id="div_cat">
                  <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Category Division</label>
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                   <div class="frms-slt">
                       <select id="boot-multiselect-demo_category" name="category_id[]" multiple="multiple">
                         @if(isset($category_div_arr) && count($category_div_arr)>0)

                            @if(isset($all_category_div_arr) && count($all_category_div_arr)>0)

                              @foreach($all_category_div_arr as $key=>$cat_div)

                                 @if(isset($cat_div) && count($cat_div) > 0)

                                 <option value="{{$cat_div['id']}}" {{(in_array($cat_div['id'], $category_div_arr)) ? 'selected' : '' }}>{{$cat_div['cat_division_name']}}</option>
                  @endif
                  @endforeach
                  @endif

                  @else
                  @foreach($cat_array_from_area as $key=>$category)

                  <option value="{{$category['id']}}">{{$category['cat_division_name']}}</option>

                  @endforeach

                  @endif

                  </select>
                  <div class="clearfix"></div>
                  <span class="red">{{ $errors->first('category_id') }}</span>
            </div>
         </div>
      </div> --}}


      <div class="form-group row sales_manager_create_row" id="div_cat">
         <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Category Division</label>
         <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
            <div class="frms-slt">
               <select id="boot-multiselect-demo_category" name="category_id[]" multiple="multiple">

                  @if(isset($cat_array_from_area) && count($cat_array_from_area)>0)

                  @foreach($cat_array_from_area as $key=>$cat_div)

                  @if(isset($cat_div) && count($cat_div) > 0)

                  <option value="{{$cat_div['id']}}" {{(in_array($cat_div['id'], $category_div_arr)) ? 'selected' : '' }}>{{$cat_div['cat_division_name']}}</option>
                  @endif
                  @endforeach
                  @endif


               </select>
               <div class="clearfix"></div>
               <span class="red">{{ $errors->first('category_id') }}</span>
            </div>
         </div>
      </div>

      <input type="hidden" id="lower_value" value="0">

      <div class="form-group row sales_manager_create_row">
         <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Commission(%)</label>
         <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
            <input type="text" class="form-control" name="commission" placeholder="Enter Commission"  value="{{ num_format($arr_user_data['commission'], 2) }}" data-parsley-type="number" data-parsley-min="1" data-parsley-max="100" data-parsley-pattern-message='Please enter valid commission' data-parsley-type-message="Please enter valid commission" data-parsley-max-message='Commission should be lower than or equal to 100' />
            <span class="red" id="err_msg">{{ $errors->first('commission') }}</span>
         </div>
      </div>

      <div class="form-group row sales_manager_create_row">

        {{--  <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Profile Image<i class="red">*</i></div>
         <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
            <input type="file" name="profile_image" id="profile_img" class="dropify" data-default-file="{{ url('/storage/app/'.$arr_user_data['profile_image'])}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" /> --}}

         <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Profile Image<i class="red">*</i></div>
         <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
            @php
               $product_img_path = ""; 
               $image_name = (isset($arr_user_data['profile_image']))? $arr_user_data['profile_image']:"";
               $image_type = "user";
               $is_resize = 0; 
               $product_img_path = imagePath($image_name, $image_type, $is_resize);
            @endphp         
            <input type="file" name="profile_image" id="profile_img" class="dropify" data-default-file="{{ $product_img_path }}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />

         </div>
      </div>

      <input type="hidden" name="old_sales_manager_desc" id="old_sales_manager_desc" value="{{$arr_user_data['sales_manager_details']['description'] or ''}}">

      <div class="form-group row">
         <div class="col-sm-12 cancel_back_flex_btn">
         <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
         <button type="button" class="btn btn-success waves-effect waves-light" id="btn_update" value="Save">Save</button>
      </div>
      </div>
      </form>
   </div>
</div>
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

      var count = $('#div_category_count').val();

      if (count == 0) {
         $('#div_cat').hide();
      } else {
         $('#div_cat').show();
      }

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
            $("#contact_no").val('+' + phone_code + <?= $arr_user_data['contact_no'] ?>);
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

   const module_url_path = "{{ $module_url_path or ''}}";

   $(document).ready(function() {
      $('#validation-form').parsley().refresh();

      $('#btn_update').click(function() {

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

      var user_id = $('#user_id').val();

      $.ajax({
         url: module_url_path + '/check_category_exist',
         method: 'POST',
         data: {
            category_div_id: category_id,
            user_id: user_id,
            "_token": "{{ csrf_token() }}"
         },
         dataType: 'JSON',
         success: function(response) {
            if (response.count > 0) {
               swal('Warning', "Category division has already assigned.", "warning");
               $('#boot-multiselect-demo_category').val('');
               $('#boot-multiselect-demo_category').multiselect('rebuild');


               setTimeout(function() {
                  location.reload();

               }, 1000);


            }
         }

      });

   });
</script>
<script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>

<script type="text/javascript">
   function saveTinyMceContent() {
      tinyMCE.triggerSave();
   }
</script>
@stop