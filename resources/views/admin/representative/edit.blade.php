@extends('admin.layout.master')
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
   .select2{
      width: 100% !important;
   }
</style>
<!-- For multiselect with search -->
<link rel="stylesheet" href="https://www.jquery-az.com/boots/css/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="https://www.jquery-az.com/boots/js/bootstrap-multiselect/bootstrap-multiselect.js"></script>
<div id="page-wrapper">
   <div class="container-fluid create_rep_form_white_box">
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
                  <input type="hidden" name="user_id" value="{{$arr_user_data['id']}}">
                  <input type="hidden" name="old_profile_image" value="{{$arr_user_data['profile_image']}}">

                  <div class="form-group row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">First Name<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name" value="{{$arr_user_data['first_name']}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid first name" />
                        <span class="red">{{ $errors->first('first_name') }}</span>
                     </div>
                  </div>
                  <div class="form-group row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Last Name<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" data-parsley-required="true" data-parsley-required-message="Please enter last name" value="{{$arr_user_data['last_name']}}" data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid last name" />
                        <span class="red">{{ $errors->first('last_name') }}</span>
                     </div>
                  </div>
                  <div class="form-group row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Email Id<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" name="email" readonly="" placeholder="Enter Email Id" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" value="{{$arr_user_data['email']}}" />
                        <span class="red">{{ $errors->first('email') }}</span>
                     </div>
                  </div>

                  <div class="form-group row">
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


                        <span class="red">{{ $errors->first('country_id') }}</span>
                     </div>
                  </div>

                  <div class="form-group row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Contact No.<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <input type="text" class="form-control" name="contact_no" id="contact_no" placeholder="Enter Contact No" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid contact number" data-parsley-required="true" data-parsley-required-message="Please enter contact number" data-parsley-minlength-message="Contact number should be 10 digits" data-parsley-maxlength="18" data-parsley-maxlength-message="Contact number must be less than 18 digits" value="{{$arr_user_data['contact_no']}}" />
                        <input type="hidden" name="hid_country_code" id="hid_country_code">
                        <span class="red">{{ $errors->first('contact_no') }}</span>
                     </div>
                  </div>

                  <div class="form-group row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Zip/Postal Code<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                     <input oninput="this.value = this.value.toUpperCase()" id="pin_or_zip_code" type="text" name="post_code" class="form-control" placeholder="Zip/Postal Code" data-parsley-trigger="change" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" value="{{$arr_user_data['post_code'] or ''}}" id="pin_or_zip_code">
                     </div>
                  </div>

                  <div class="form-group row">
                     <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Sales Manager<i class="red">*</i></label>
                     <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                        <select class="form-control" data-parsley-required="true" data-parsley-required-message="Please select sales manager" name="sales_manager_id" id="sales_manager_id" onchange="appendAreaAndVendor()">
                           <option value="">Select Sales Manager</option>
                           @if(isset($sales_manager_list) && count($sales_manager_list)>0)
                           @foreach($sales_manager_list as $sales_manager)
                           @if(isset($sales_manager['get_user_data']) && count($sales_manager['get_user_data'] > 0))
                           <option value="{{$sales_manager['user_id']}}" {{$sales_manager['user_id'] == $arr_user_data['representative_details']['sales_manager_id'] ? 'selected' : ''}}>{{$sales_manager['get_user_data']['first_name'].' '.$sales_manager['get_user_data']['last_name']}}</option>
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
                        <?php
                        $user_area_id = isset($arr_user_data['representative_details']['area_id']) ? $arr_user_data['representative_details']['area_id'] : '';
                        ?>
                        <select class="form-control" data-parsley-required="true" data-parsley-required-message="Please select area" name="area_id" id="area_id">
                           <option value="">Select Area</option>
                           @if(isset($area_names) && count($area_names)>0)
                           @foreach($area_names as $key=>$area)
                           <option value="{{$area['id']}}" @if($area['id']==$user_area_id) selected @endif>{{ $area['area_name']}}</option>
                           @endforeach
                           @endif

                        </select>
                        <span class="red">{{ $errors->first('area_id') }}</span>
                     </div>
                  </div>

                  {{-- <div class="form-group row" id="div_cat">
                <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Category Division</label>
                
                  <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
                     <div class="frms-slt">
                      <select id="boot-multiselect-demo_category" name="category_id[]" id="category_id" multiple="multiple">

                        @if(isset($category_id_arr) && $category_id_arr != '')
                              
                           @if(isset($cat_translation) && count($cat_translation)>0)
                              @foreach($cat_translation as $key=>$cat_trans)
                                 @if(isset($cat_trans) && count($cat_trans > 0))

                                 <option value="{{$cat_trans['id']}}" {{(in_array($cat_trans['id'], $category_id_arr)) ? 'selected' : '' }}>{{$cat_trans['cat_division_name']}}</option>
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

      @php

      $count = isset($category_id_arr)?count($category_id_arr):0;
      @endphp

      <input type="hidden" name="div_category_count" id="div_category_count" value="{{$count}}">

      <div class="form-group row" id="div_cat">
         <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Category Division</label>

         <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
            <div class="frms-slt">
               <select id="boot-multiselect-demo_category" name="category_id[]" id="category_id" multiple="multiple">


                  @if(isset($cat_array_from_area) && count($cat_array_from_area)>0)

                  @foreach($cat_array_from_area as $key=>$category_div)
                  @if(isset($category_id_arr))
                  <option value="{{$category_div['id']}}" {{(in_array($category_div['id'], $category_id_arr)) ? 'selected' : '' }}>{{$category_div['cat_division_name']}}</option>
                  @else
                  <option>Select category division</option>
                  @endif

                  @endforeach

                  @endif

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
               <select id="boot-multiselect-demo" name="vendor_id[]" multiple="multiple" data-parsley-required="true" data-parsley-required-message="Please select vendor" data-parsley-errors-container="#err_container">
                  @if(isset($all_vendors) && count($all_vendors)>0)
                  @foreach($all_vendors as $key=>$vendor)
                  @if(isset($vendor['user_details']) && count($vendor['user_details'] > 0))
                  <option value="{{$vendor['user_id']}}" {{(in_array($vendor['user_id'], $vendor_id_arr)) ? 'selected' : '' }}>{{$vendor['company_name']}}</option>
                  @endif
                  @endforeach
                  @endif
               </select>
            <!-- </div> -->
            <div class="clearfix"></div>
            <span id="err_container" class="red">{{ $errors->first('vendor_id') }}</span>
         </div>
      </div>

      <input type="hidden" id="lower_value" value="0">

      <div class="form-group row">
         <label class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label">Commission(%)</label>
         <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
            <input type="text" class="form-control" name="commission" placeholder="Enter Commission"  value="{{ num_format($arr_user_data['commission'], 2) }}" data-parsley-type="number" data-parsley-min="1" data-parsley-max="100"  data-parsley-pattern-message='Please enter valid commission' data-parsley-type-message="Please enter valid commission" data-parsley-max-message='Commission should be lower than or equal to 100' />
            <span class="red" id="err_msg">{{ $errors->first('commission') }}</span>
         </div>
      </div>

      <div class="form-group row">
{{-- 
         <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label" for="profile_image">Profile Image <i class="red">*</i></div>
         <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12">
            <input type="file" name="profile_image" id="profile_img" class="dropify" data-default-file="{{ url('/storage/app/'.$arr_user_data['profile_image'])}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" /> --}}

         <div class="col-lg-2 col-md-12 col-sm-12 col-xs-12 col-form-label" for="profile_image">Profile Image <i class="red">*</i></div>
         <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12">
            @php 
               $product_img_path = "";
               $image_name = $arr_user_data['profile_image'];
               $image_type = "user";
               $is_resize = 0; 
               $product_img_path = imagePath($image_name, $image_type, $is_resize);                        
            @endphp        
            <input type="file" name="profile_image" id="profile_img" class="dropify" data-default-file="{{ $product_img_path }}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />

         </div>
      </div>


      <div class="form-group row">
         <div class="col-sm-12 cancel_back_flex_btn">
         <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
         <button type="button" class="btn btn-success waves-effect waves-light " id="btn_update" value="Save">Save</button>
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
@php
/*
  $obj_vendor_id_arr =  json_encode($vendor_id_arr, false);*/
@endphp
<!-- END Main Content -->
<script type="text/javascript">
   $(document).ready(function() {
     /* $('#boot-multiselect-demo').multiselect({
         includeSelectAllOption: false,
         enableFiltering: true,
         enableCaseInsensitiveFiltering: true,
         nonSelectedText: 'Select Vendors'
      });*/

      $('#boot-multiselect-demo').select2({
          placeholder: 'Select vendor'
        });

      var count = $('#div_category_count').val();

      if (count == 0) {
         $('#div_cat').hide();
      } else {
         $('#div_cat').show();
      }


      appendAreaAndVendor(1);

   });

   $(document).ready(function() {
      $('#boot-multiselect-demo_category').multiselect({
         includeSelectAllOption: false,
         enableFiltering: true,
         nonSelectedText: 'Select Category'
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

      $("#country_id").change(function() {
         validate_pin_or_zip_code();
      });

      function validate_pin_or_zip_code() {
         var country_id = $("#country_id").val();
         var post_code = $("#pin_or_zip_code").val();

         var phone_code = $('option:selected', "#country_id").attr('phone_code');
         var zipcode_length = $('option:selected', "#country_id").attr('zipcode_length');
         var countryName = $('option:selected', "#country_id").attr('country_name');

         if (phone_code) {
            $("#contact_no").val('+' + phone_code + <?= $arr_user_data['contact_no'] ?>);
            $("#contact_no").attr('code_length', phone_code.length + 1);
            $("#hid_country_code").val('+' + phone_code);
         } else {
            $("#contact_no").val('');
            $("#contact_no").attr(0);
            $("#hid_country_code").val('');
         }

         if (country_id == "" && post_code != "") {
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
        
         $('#validation-form').parsley();
      }
   });

   const module_url_path = "{{ $module_url_path }}";
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
                  if ('success' == data.status) {
                     //swal(data.status,data.description,data.status);
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
      var category_status = $('#category_status').val();

      if (category_status == '1') {
         $('#category_status').val('1');
      } else {
         $('#category_status').val('0');
      }
   }





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


/* Function to perform operation on onchange of sales manager dropdown change*/
   function appendAreaAndVendor(is_on_load = false){
      
      var vendor_id_arr = <?php echo json_encode($vendor_id_arr); ?>;
     

      var sales_manager_id = $('#sales_manager_id').val();

      if(is_on_load != '1'){
         $("#boot-multiselect-demo").select2("val", "");
      }

      if (sales_manager_id != '') {
         $.ajax({
            url: module_url_path + '/fetch_area',
            method: 'POST',
            data: {
               sales_manager_id: sales_manager_id,
               "_token": "{{ csrf_token() }}"
            },
            dataType: 'JSON',
            success: function(response) {
               console.log(response);
              
               var html = '<option>Select Area</option>';

                for (var i = 0; i < response[0].length; i++) {
                  var obj_area = response[0][i];
                  // console.log(obj_area);
                  html += "<option value='" + obj_area.area_details.id + "'>" + obj_area.area_details.area_name + "</option>";
               }

               //$('#area_id').html(html);

               /* For append html of vendor */
               var vendor_html = '<option>Select Vendors</option>';

              for (var i = 0; i < response[1].length; i++) {
                var obj_vendor = response[1][i];

                var selected = "";

                var user_id = obj_vendor.vendor_id;

                if ($.inArray(user_id, vendor_id_arr) > -1) {               
                     selected = "selected";
                }
                  
                vendor_html += "<option "+selected+" value='" + obj_vendor.vendor_id + "'>" + obj_vendor.get_maker_details.company_name + "</option>";
                

              }
              //console.log(vendor_html);
              $("#boot-multiselect-demo").html(vendor_html);
            }

         });
      } else {
         $('#area_id').html('<option value="">Select Area</option>');
      }
     // $('#boot-multiselect-demo').select2();
  }
</script>
<script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
<script type="text/javascript">
   function saveTinyMceContent() {
      tinyMCE.triggerSave();
   }
</script>
@stop