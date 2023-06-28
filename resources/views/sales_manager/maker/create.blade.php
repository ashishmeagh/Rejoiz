@extends('sales_manager.layout.master')                
@section('main_content')
@php
 $company_name = '';
 $company_name = get_vendor_company_name();
@endphp
<!-- Page Content -->
<link rel="stylesheet" href="https://www.jquery-az.com/boots/css/bootstrap-multiselect/bootstrap-multiselect.css" type="text/css">
<script type="text/javascript" src="https://www.jquery-az.com/boots/js/bootstrap-multiselect/bootstrap-multiselect.js"></script>
<style>
  .btn-primary, .btn-primary.disabled{
        width: 180px;
            padding: 8px 15px 6px;
    display: inline-block; border-radius: 3px;
  }
th {
    white-space: nowrap;
}
.multiselect-cts{position: relative;}
.multiselect-cts .parsley-errors-list.filled {
    top: 35px;
    position: absolute;
}
.multiselect-container>li .checkbox input[type=checkbox]{
  opacity: 1;
}
.input-group-btn{display: none;}
.dropdown-menu > .active > a, .dropdown-menu > .active > a:hover, .dropdown-menu > .active > a:focus {
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
.frms-slt .parsley-errors-list{
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
         <li><a href="{{$module_url_path}}">My {{$module_title or ''}}</a></li>
         <li class="active">Add Vendor</li>
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
            <div class="col-sm-12 col-xs-12 pad0">
              
               <form class="form-horizontal" id="validation-form"> 
               {{ csrf_field() }}
               
               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">First Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">

                     <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name"   data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid first name"/>
                     <span class="red">{{ $errors->first('first_name') }}</span>
                  </div>
                </div>


                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Last Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="last_name" placeholder="Enter Last Name" data-parsley-required="true" data-parsley-required-message="Please enter last name"  data-parsley-pattern="^[a-zA-Z ]+$" data-parsley-pattern-message="Please enter valid last name"/>
                     <span class="red">{{ $errors->first('last_name') }}</span>
                  </div>
               </div>
               
               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Email Address<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="email" placeholder="Enter Email Address" data-parsley-required="true" data-parsley-required-message="Please enter email address" data-parsley-type="email" data-parsley-type-message="Please enter valid email address" />
                     <span class="red">{{ $errors->first('email') }}</span>
                  </div>
               </div>

             <div class="form-group row">
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

              <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Mobile No.<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="contact_no" id="contact_no" placeholder="Enter Mobile No" data-parsley-required="true" data-parsley-required-message="Please enter mobile number"  data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile number must be less than 18 digits" data-parsley-minlength-message="Mobile number should be 10 digits" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number" />
                     <input type="hidden" name="hid_country_code" id="hid_country_code">
                     <span class="red">{{ $errors->first('contact_no') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Zip/Postal Code<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input oninput="this.value = this.value.toUpperCase()" type="text" class="form-control" name="post_code" placeholder="Enter Zip/Postal Code" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code" data-parsley-trigger="change" id ="pin_or_zip_code"/>
                     <span class="red">{{ $errors->first('post_code') }}</span>
                  </div>
               </div>


               <div class="form-group row">

                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Company Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="company_name" placeholder="Enter Company Name" data-parsley-required="true" data-parsley-required-message="Please enter company name" id ="company_name" value = "{{$company_name}}" readonly="readonly" />
                     <span class="red">{{ $errors->first('company_name') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Website URL<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                    <input class="form-control" placeholder="Enter Your Website URL" type="text" name="website_url" id="website_url" data-parsley-required="true"  data-parsley-required-message="Please enter website url" data-parsley-type="url" data-parsley-type-message="Please enter valid website url" data-parsley-maxlength="50"/>     
                  </div>
                 </div>

{{--                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Tax Id<i class="red">*</i></label>
                   <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                    <input class="form-control" placeholder="Enter Your Tax Id" type="text" name="tax_id" id="tax_id" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="{{ url('/does_exists_tax_id/tax-id') }}"
                      data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}' data-parsley-remote-message="Tax Id already exists" data-parsley-length="[9, 9]" data-parsley-length-message ="Tax id is invalid. It should have 9 digits" data-parsley-type="integer" data-parsley-required="true" data-parsley-required-message="Please enter tax id"/>
                    </div>  
              </div>   --}}      


                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Primary Category<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <select class="form-control" name="primary_category_id" id="primary_category_id" data-parsley-required="true" data-parsley-required-message="Please select primary category">
                         <option value="">Select Primary Category</option>
                      @if(isset($categories_arr) && sizeof($categories_arr)>0)
                        @foreach($categories_arr as $category)
                          <option value="{{$category['id'] or ''}}">{{$category['category_name'] or ''}}</option>
                        @endforeach
                      @endif
                    </select>
                     </div>
                  <span class="red">{{ $errors->first('category_id') }}</span>
                </div>

                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Tax Id<i class="red">*</i></label>
                   <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                    <input class="form-control" placeholder="Enter Your Tax Id" type="text" name="tax_id" id="tax_id" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="{{ url('/does_exists_tax_id/tax-id') }}"
                      data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}' data-parsley-remote-message="Tax Id already exists" data-parsley-length="[9, 9]" data-parsley-length-message ="Tax id is invalid. It should have 9 digits" data-parsley-type="integer" data-parsley-required="true" data-parsley-required-message="Please enter tax id"/>
                    </div>  
              </div>

                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Number of stores you are carried in</label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        <select class="form-control" name="no_of_stores" id="no_of_stores">
                          <option value="">Select no of stores</option>
                          @for($i=1;$i<11;$i++)
                          <option value="{{$i}}">{{$i}}</option>
                          @endfor
                    </select>
                  </div>
                 </div> 

              <!-- <div class="col-xs-12 col-sm-12 col-md-12 col-lg-6">
                <div class="form-group">
                  <label>Tax Id<i class="red">*</i></label>
                     <input type="text" class="form-control" name="tax_id" placeholder="Enter Your Tax Id" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="{{ url('/does_exists_tax_id/tax-id') }}" data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}' data-parsley-remote-message="Tax Id already exists" data-parsley-required="true" data-parsley-required-message="Please enter tax id" data-parsley-length="[9, 9]" data-parsley-length-message ="Tax id is invalid. It should have 9 digits" data-parsley-type="integer"/>
                     <span class="red">{{ $errors->first('tax_id') }}</span>
               </div>
              </div> -->
              <!-- <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Tax Id</label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                   <input class="form-control" placeholder="Enter Your Tax Id" type="text" name="tax_id" id="tax_id" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="{{ url('/does_exists_tax_id/tax-id') }}" data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" }}' data-parsley-remote-message="Tax Id already exists" data-parsley-length="[9, 9]" data-parsley-length-message ="Tax id is invalid. It should have 9 digits" data-parsley-type="integer"/>    
                   <span class="red">{{ $errors->first('tax_id') }}</span>
                  </div>
              </div> -->

               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Instagram URL</label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                   <input class="form-control" placeholder="Enter Your Instagram URL" type="text" name="insta_url" id="insta_url" data-parsley-type="url" data-parsley-type-message="Please enter valid instagram url" data-parsley-maxlength="50"/>    
                  </div>
              </div>

      <div class="col-md-12 common_back_save_btn">
                    <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
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

 var module_url_path  = "{{ $module_url_path or '' }}";
$(document).ready(function(){
   
  $("#country_id").change(function(event) {   
      var country_code = $("#country_id").val();
      var zip_code     = $("#pin_or_zip_code").val();

      if(country_code  == '' && zip_code!="")
      {
         $("#err_zip_code").html('Invalid zip/postal code'); 
      }

      var phone_code   = $('option:selected', this).attr('phone_code');
      var zipcode_length = $('option:selected', this).attr('zipcode_length');
      var countryName = $('option:selected', this).attr('country_name');

      if(phone_code){
         $("#contact_no").val("+"+phone_code);
         $("#contact_no").attr('code_length',phone_code.length+1);  
         $("#hid_country_code").val('+'+phone_code);
         } else {
         $("#contact_no").val('');
         $("#contact_no").attr(0); 
         $("#hid_country_code").val('');
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
  });
 
  $('#btn_add').click(function()
  {
      var country_code = $("#country_id").val();
      var post_code    = $("#post_code").val();

      if(country_code!="" && post_code!="")
      {
        $("#err_post_code").html('');
      }
      else if(country_code=="" && post_code=="")
      {
         $("#err_post_code").html('');
      }
      else if(country_code=="" && post_code!="")
      {
        $("#err_post_code").html('Invalid zip/postal code');
      }
      else if(country_code!="" && post_code=="")
      {
        $("#err_post_code").html('');
      } 

      // country wise phone code and zip code validation
      var phone_code   = $('#country_id option:selected').attr('phone_code');
      var zipcode_length = $('#country_id option:selected').attr('zipcode_length');
      var countryName = $('#country_id option:selected').attr('country_name');

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

      if($('#validation-form').parsley().validate()==false) return;     
      var form_data = $('#validation-form').serialize();      
      var url       = module_url_path+"/save"; 

       if($('#validation-form').parsley().validate()==false) return;

      // if($('#validation-form').parsley().isValid() == true )
      // {
          
         $.ajax({
                  url:url,
                  data:form_data,
                  method:'POST',
                  
                  beforeSend : function()
                  {
                    showProcessingOverlay();
                    $('#btn_signup').prop('disabled',true);
                    $('#btn_signup').html('Please Wait <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
                  },
                  success:function(data)
                  {
                     hideProcessingOverlay();
                     if(data.status == "success")
                     {
                        
                         $('#validation-form')[0].reset();

                         swal({
                                 title: "Success",
                                 text:  data.description,
                                 type:  data.status,
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
        // }
    });
});  

</script>
@stop