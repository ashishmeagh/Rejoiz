@extends('sales_manager.layout.master')  
@section('main_content')
<!-- Page Content -->

<style>
  .btn-primary, .btn-primary.disabled{
        width: 180px;
            padding: 8px 15px 6px;
    display: inline-block; border-radius: 3px;
  }
th {
    white-space: nowrap;
}
.dropdown-toggle.btn-default{
  width: 100%; position: relative;
  text-align: left;
}
.dropdown-toggle::after{position: absolute;right: 15px; top: 20px; font-weight: 30px}
.input-group-btn{display: none;}
.btn-group, .btn-group-vertical{display: block;}
.btn-group.show {
    display: block!important;
}
.dropdown-menu>li>a .checkbox input[type=checkbox]{opacity: 1;}
.multiselect-container>li>a>label {
    padding: 10px 20px 14px 40px !important;
}
.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus {
    color: #564126;
    text-decoration: none;
    outline: 0;
    background-color: #eff3f8;
    border-bottom: 1px solid #eff3f8;
}
.input-group-addon{
  color: #ffffff;
  background-color: #666;
}

ul.multiselect-container.dropdown-menu {
    max-height: 290px;
    overflow: auto;
    z-index: 999999;
}
.frms-slt {
       display: block;
    position: relative;
    margin-bottom: 13px;
    margin-top: 0px;
}
.frms-slt .parsley-errors-list{
    position: relative;
    bottom: -63px;
    z-index: 9;
    width: 100%;
    display: block;
}

#err_zip_code{
    font-size: 0.9em;
    line-height: 0.9em;
    color: red;
  }  
</style>

<!-- For multiselect with search -->
<link rel="stylesheet" href="https://www.jquery-az.com/boots/css/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="https://www.jquery-az.com/boots/js/bootstrap-multiselect/bootstrap-multiselect.js"></script>


<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{url('/')}}/sales_manager/dashboard">Dashboard</a></li>
         <li><a href="{{url('/').'/sales_manager/representative_listing'}}">My Representatives</a></li>
         <li class="active">Add Representative</li>
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
              
               <form class="form-horizontal" id="validation-form"> 
               {{ csrf_field() }}
               

           
              <div class="form-group">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">First Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name" data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Please enter valid first name"/>
                     <span class="red">{{ $errors->first('first_name') }}</span>
                  </div>
               </div>


                <div class="form-group">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Last Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" data-parsley-required="true" data-parsley-required-message="Please enter last name"  data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Please enter valid last name"/>
                     <span class="red">{{ $errors->first('last_name') }}</span>
                  </div>
               </div>
               
               <div class="form-group">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Email Id<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="email" placeholder="Enter Email Id" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" />
                     <span class="red">{{ $errors->first('email') }}</span>
                  </div>
               </div>

                <div class="form-group">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Tax Id<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                   
                     <input class="form-control" placeholder="Enter Your Tax Id" type="text" name="tax_id" id="tax_id" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="{{ url('/does_exists_tax_id/tax-id') }}"
                      data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}'   data-parsley-remote-message="Tax id already exists" data-parsley-length="[9, 9]" data-parsley-length-message ="Tax id is invalid. it should have 9 digits" data-parsley-type="integer" data-parsley-type-message ="Tax id can only be numbers" data-parsley-required="true" data-parsley-required-message="Please enter tax id"/>
                      
                    
                  </div>
               </div>
           
               <div class="form-group">
                  <label for="country" class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Country<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                    <select name="country_id" id="country_id" data-parsley-required="true" data-parsley-required-message="Please select country" class="form-control">
                        <option value="">Select</option>
                        @if(isset($country_arr) && sizeof($country_arr)>0)
                         @foreach($country_arr as $country)
                            
                            <option value="{{$country['id']}}" phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                           
                          @endforeach
                        @endif
                    </select>
                  </div>
              <span class='red'>{{ $errors->first('country') }}</span>
              </div>

              <div class="form-group">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Contact No<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="contact_no" id="contact_no" placeholder="Enter Contact No" data-parsley-required="true" data-parsley-required-message="Please enter contact number" data-parsley-minlength-message="Contact number should be 10 digits" data-parsley-maxlength="18"  data-parsley-maxlength-message="Contact number must be less than 18 digits" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid contact number" />
                     <input type="hidden" name="hid_country_code" id="hid_country_code">
                     <span class="red">{{ $errors->first('contact_no') }}</span>
                  </div>
               </div>
                
               <div class="form-group">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Zip/Postal Code<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input oninput="this.value = this.value.toUpperCase()" type="text" class="form-control" name="post_code" placeholder="Enter Zip/Postal Code" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" data-parsley-trigger="change" id="pin_or_zip_code" />
                     <span class="red" id="err_zip_code">{{ $errors->first('post_code') }}</span>
                  </div>
               </div>

              <div class="form-group">
                <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold">Vendor Represents<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <!-- <div class="frms-slt"> -->
                       <select id="boot-multiselect-demo"  name="vendor_id[]" multiple="multiple" data-parsley-required="true" data-parsley-required-message="Please select vendor" data-parsley-errors-container="#err_container">
                              
                          @if(isset($all_vendors) && count($all_vendors)>0)
                              @foreach($all_vendors as $vendor)


                                @if(isset($vendor['get_user_details']) && count($vendor['get_user_details']) > 0)

                                    <option value="{{$vendor['get_user_details']['id']}}">{{$vendor['get_user_details']['maker_details']['company_name']}}</option>

                                @endif
                              @endforeach
  
                          @endif
                        </select>
                    <div class="clearfix"></div>
                     <span id="err_container" class="red">{{ $errors->first('vendor_id') }}</span>
                   <!-- </div> -->
                  </div>
               </div> 

              <div class="form-group">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label commonlabel_bold" for="profile_image">Profile Image <i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="file" name="profile_image" id="profile_image" class="dropify" data-default-file="{{url('/')}}/uploads/default.jpeg" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" data-parsley-required="true" data-parsley-required-message="Please select profile image" data-parsley-errors-container="#profile_error"/>
                     <div id="profile_error"></div>
                  </div>
               </div>
               <span>{{ $errors->first('profile_image') }}</span>

               
               
                  <div class="col-md-12 text-center common_back_save_btn">
                        <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ' '}}"><i class="fa fa-arrow-left"></i> Back</a>

                        <button type="button" class="btn btn-success waves-effect waves-light" id = "btn_add" value="Save">Save</button>
                  </div>
              </form>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->

  <script type="text/javascript">

 $(document).ready(function() {
      $('#boot-multiselect-demo').multiselect({
      includeSelectAllOption:false,
      enableFiltering: true,
      nonSelectedText: 'Select Vendors'
  });
});

$(document).ready(function(){
   
  $("#country_id").change(function(event) {
   
  var country_code = $("#country_id").val();
  var post_code = $("#pin_or_zip_code").val();

   var phone_code = $('option:selected', this).attr('phone_code');
   var zipcode_length = $('option:selected', this).attr('zipcode_length');
   var countryName = $('option:selected', this).attr('country_name');

   if (phone_code) {
      $("#contact_no").val('');
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

  const module_url_path = "{{url('/').'/sales_manager'}}";

   $(document).ready(function(){

      $('#validation-form').parsley();

        $('#btn_add').click(function(){

            var country_id  =  $("#country_id").val();
            var zip_code    =  $("#pin_or_zip_code").val();

            if(country_id!="" && zip_code=="")
            {
              $("#err_zip_code").html(''); 
            }
            else if(country_id=="" && zip_code=="")
            {
              $("#err_zip_code").html('');
            }
            else if(country_id=="" && zip_code!="")
            {
              $("#err_zip_code").html("Invalid zip/postal code");
            }

            // validate country wise phone code zip code validation
            var phone_code = $('#country_id option:selected').attr('phone_code');
            var zipcode_length = $('#country_id option:selected').attr('zipcode_length');
            var countryName = $('#country_id option:selected').attr('country_name');

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


            if($('#validation-form').parsley().validate()==false) return;

            var formdata = new FormData($("#validation-form")[0]);
           
            $.ajax({
              url: module_url_path+'/save_rep',
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
                   
                  if(data.status == "success")
                  {
                       
                      $('#validation-form')[0].reset();

                      swal({
                              title:"Success",
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
                    swal("Error",data.description,data.status);
                  } 

              }
              
            });   
     
      });

   });

    function toggle_status()
    {
        var site_status = $('#site_status').val();
        if(site_status=='1')
        {
          $('#site_status').val('1');
        }
        else
        {
          $('#site_status').val('0');
        }
    }
  
</script>
@stop