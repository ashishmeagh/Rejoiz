@extends('sales_manager.layout.master')  
@section('main_content')

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
            <li><a href="{{url('/').'/sales_manager/representative_listing'}}">Vendors</a></li>
            <li class="active">Edit Vendor</li>
         </ol>
      </div>
      <!-- /.col-lg-12 -->
   </div>
   <!-- .row -->
   <div class="row">
      <div class="col-sm-12">
         <div class="white-box">
            <form class="form-horizontal" id="validation-form" 
               > 
               {{ csrf_field() }}


               <input type="hidden" name="user_id" value="{{$arr_user_data['id']}}">

               <input type="hidden" name="old_profile_image" value="{{$arr_user_data['profile_image']}}">

              
               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">First Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true" data-parsley-required-message="Please enter first name."  data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Please enter valid first name." value="{{$arr_user_data['first_name']}}"  />
                     <span class="red">{{ $errors->first('first_name') }}</span>
                  </div>
               </div>
              <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Last Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="last_name" placeholder="Enter lasr Name" data-parsley-required="true" data-parsley-required-message="Please enter last name." data-parsley-pattern="^[a-zA-Z]+$" data-parsley-pattern-message="Please enter valid last name" value="{{$arr_user_data['last_name']}}"  />
                     <span class="red">{{ $errors->first('last_name') }}</span>
                  </div>
               </div>
                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Email Address<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="email" placeholder="Enter Email" data-parsley-required="true" data-parsley-required-message="Please enter email address." data-parsley-type="email" data-parsley-type-message="Please enter valid email address." value="{{$arr_user_data['email']}}"  />
                     <span class="red">{{ $errors->first('email') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                <label for="country" class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Country<i class="red">*</i></label>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                  <select name="country_id" id="country_id" data-parsley-required="true" data-parsley-required-message="Please select country." class="form-control">
                            <option value="">Select Country</option>
                            @if(isset($country_arr) && sizeof($country_arr)>0)
                             @foreach($country_arr as $country)
                                
                              <!--   <option value="{{$country['id']}}" @if($country['id']== $arr_user_data['country_id']) selected="true" @endif>{{$country['name'] or ''}}</option> -->
                               <option value="{{$country['id']}}" @if($country['id']== $arr_user_data['country_id']) selected="true" @endif phone_code="{{$country['phonecode'] or ''}}" zipcode_length="{{ $country['zipcode_length'] or '' }}" country_name="{{ $country['name'] or '' }}">{{$country['name'] or ''}}</option>
                                }
                              @endforeach
                            @endif
                  </select>
                </div>
                  <span class='red'>{{ $errors->first('country') }}</span>
             </div>


              <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Mobile No<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <!-- <input type="text" class="form-control" name="contact_no" placeholder="Enter Contact No" data-parsley-required="true" data-parsley-required-message="Please enter contact number." data-parsley-type="digits" data-parsley-type-message="please enter valid contact number."  data-parsley-minlength="10" data-parsley-maxlength="10"  data-parsley-maxlength-message="Mobile number should be 10 digits" data-parsley-minlength-message="Mobile number should be 10 digits" value="{{$arr_user_data['contact_no']}}" /> -->
                     <input type="text" class="form-control" name="contact_no" id="contact_no" placeholder="Enter Mobile No" data-parsley-required="true" data-parsley-required-message="Please enter mobile number"  data-parsley-maxlength="18" data-parsley-maxlength-message="Mobile number must be less than 18 digits" data-parsley-minlength-message="Mobile number should be 10 digits" data-parsley-pattern="^[0-9*#+]+$" data-parsley-required data-parsley-pattern-message="Please enter valid mobile number" value="{{$arr_user_data['contact_no']}}"/>
                     <input type="hidden" name="hid_country_code" id="hid_country_code">
                     <span class="red">{{ $errors->first('contact_no') }}</span>
                  </div>
               </div>
          

                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Zip/Postal Code<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="post_code" placeholder="Enter Zip/Postal Code" data-parsley-required="true" data-parsley-required-message="Please enter zip/postal code." data-parsley-trigger="change" value="{{$arr_user_data['post_code'] or ''}}" id= "pin_or_zip_code"/>
                     <span class="red" id="err_zip_code">{{ $errors->first('post_code') }}</span>
                  </div>
               </div>

                <div class="form-group row">

                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Company Name<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <input type="text" class="form-control" name="company_name" placeholder="Enter Company Name" data-parsley-required="true" data-parsley-required-message="Please enter company name." id ="company_name" value="{{$arr_user_data['maker_details']['company_name'] or ''}}" @if (isset($arr_user_data['maker_details']['company_name']) && strpos($arr_user_data['maker_details']['company_name'], 'Rejoiz-iStore') !== false) readonly  style="background-color: #eee;" @endif/>   
                     <span class="red">{{ $errors->first('company_name') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Website URL<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                    <input class="form-control" placeholder="Enter Your Website URL." type="text" name="website_url" id="website_url" data-parsley-required="true"  data-parsley-required-message="Please enter website url." data-parsley-type="url" data-parsley-type-message="Please enter valid website url." data-parsley-maxlength="50"  value="{{$arr_user_data['maker_details']['website_url'] or ''}}"/>     
                  </div>
                 </div>


                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Primary Category</label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                     <select class="form-control" name="primary_category_id" id="primary_category_id" data-parsley-required="true" data-parsley-required-message="Please select primary category.">
                         <option value="">Select primary category</option>
                      @if(isset($categories_arr) && sizeof($categories_arr)>0)
                        @foreach($categories_arr as $category)
                          <option value="{{$category['id'] or ''}}" @if(isset($arr_user_data['maker_details']['primary_category_id']) && $arr_user_data['maker_details']['primary_category_id']==$category['id']) selected="" @endif >{{$category['category_name'] or ''}}</option>
                        @endforeach
                      @endif
                    </select>
                     </div>
                  <span class="red">{{ $errors->first('category_id') }}</span>
                </div>

                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Tax Id<i class="red">*</i></label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                 
                     <input class="form-control" placeholder="Enter Your Tax Id" type="text" name="tax_id" id="tax_id" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="{{ url('/does_exists_tax_id/tax-id') }}"
                      data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "{{ csrf_token() }}" ,"user_id":"{{$arr_user_data['id']}}" }}'   data-parsley-remote-message="Tax id already exists" data-parsley-length="[9, 9]" data-parsley-length-message ="This value is invalid. It should have 9 digits" data-parsley-type="integer" data-parsley-required="true" data-parsley-required-message="Please enter tax id." value="{{$arr_user_data['tax_id']}}"/>
                  </div>
               </div>

                <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Number of stores you are carried in</label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                        <select class="form-control" name="no_of_stores" id="no_of_stores">
                          <option value="">Select no of stores</option>
                          @for($i=1;$i<11;$i++)
                          <option value="{{$i}}"  @if(isset($arr_user_data['maker_details']['no_of_stores']) && $arr_user_data['maker_details']['no_of_stores']==$i) selected="" @endif >{{$i}}</option>
                          @endfor
                    </select>
                  </div>
                 </div>


               <div class="form-group row">
                  <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 col-form-label">Instagram URL</label>
                  <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                   <input class="form-control" placeholder="Enter Your Instagram URL" type="text" name="insta_url" id="insta_url" data-parsley-type="url" data-parsley-type-message="Please enter valid instagram url." data-parsley-maxlength="50" value="{{$arr_user_data['maker_details']['insta_url'] or ''}}"/>    
                  </div>
              </div>  

             <!-- <div class="form-group row">                                   
               <label class="col-xs-12 col-sm-12 col-md-12 col-lg-2 control-label" for="profile_image">Profile Image<i class="red">*</i> </label>
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-10">
                  <input type="file" name="profile_image" id="profile_img" class="dropify" data-default-file="{{ url('/storage/app/'.$arr_user_data['profile_image'])}}" data-allowed-file-extensions="png jpg JPG jpeg JPEG" data-max-file-size="2M" data-errors-position="outside" />
               
              </div>
            </div> -->
               <div class="form-group row">
                  <div class="col-md-12 common_back_save_btn">
                      <a class="btn btn-inverse waves-effect waves-light" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>
                     <button type="button" class="btn btn-success waves-effect waves-light m-r-10" id = "btn_update" value="Save">Save</button>
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

 $(document).ready(function(){
      $('#boot-multiselect-demo').multiselect({
      includeSelectAllOption:false,
      enableFiltering: true,
      nonSelectedText: 'Select Vendors'
  });

});


/*country id validation*/

/*end*/

$(document).ready(function(){

  var country_code = $("#country_id").val();
  var zip_code     = $("#pin_or_zip_code").val();

  var phone_code   = $('option:selected', this).attr('phone_code');
  var zipcode_length = $('option:selected', this).attr('zipcode_length');
  var countryName = $('option:selected', this).attr('country_name');

  var contact_no_val = $("#contact_no").val();

  if(phone_code){
         $("#contact_no").val("+"+phone_code+''+contact_no_val);
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

  
    // validate_pin_or_zip_code();

    // $("#country_id").change(function(){
    //   validate_pin_or_zip_code();
    // });

    // function validate_pin_or_zip_code(){
    //   var country_code = $("#country_id").val();
    //   var zip_code     = $("#pin_or_zip_code").val();

    //   if(country_code=='' && zip_code!="")
    //   {
    //      $("#err_zip_code").html('Invalid zip/postal code.');
    //   }

    //   else if(country_code=='2' && zip_code!="")
    //   {
    //      $("#err_zip_code").html("");
    //      $("#pin_or_zip_code").attr({"data-parsley-type":'integer',
    //                                   "data-parsley-length":'[5,5]',
    //                                   "data-parsley-length-message":'Zip/Postal code should be 5 digits long.',
    //                                   "data-parsley-type-message":'Zip/Postal code can only be   numbers.'
    //                                 });
    //   }  
    //   else if(country_code=='1' && zip_code!="")
    //     {

    //       $("#err_zip_code").html(""); 
    //       $("#pin_or_zip_code").attr({"data-parsley-type":'alphanum',
    //                                   "data-parsley-length":'[6,6]',
    //                                   "data-parsley-length-message":'Zip/Postal code should be 6 characters long.',
    //                                   "data-parsley-type-message":'Zip/Postal code can only be alphanumeric characters.'
    //                                 });
    //     }
    //     $('#validation-form').parsley();
      
    //   }

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
 // const module_url_path = "{{url('/').'/sales_manager'}}";
 var module_url_path  = "{{ $module_url_path or '' }}";

  
   $(document).ready(function(){

       $('#validation-form').parsley().refresh();

       $('#btn_update').click(function(){

            // var country_id  =  $("#country_id").val();
            // var zip_code    =  $("#pin_or_zip_code").val();

            // if(country_id!="" && zip_code=="")
            // {
            //   $("#err_zip_code").html(''); 
            // }
            // else if(country_id=="" && zip_code=="")
            // {
            //   $("#err_zip_code").html('');
            // }
            // else if(country_id=="" && zip_code!="")
            // {
            //   $("#err_zip_code").html("Invalid zip/postal code.");
            // }

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

        // var form_data = $('#validation-form').serialize();  
        var formdata = new FormData($("#validation-form")[0]);
        
        $.ajax({
          url: module_url_path+'/save',
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
        var category_status = $('#category_status').val();

        if(category_status =='1')
        {
          $('#category_status').val('1');
        }
        else
        {
          $('#category_status').val('0');
        }
  } 

 </script>
 <script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
 
 <script type="text/javascript">
    function saveTinyMceContent()
    {
      tinyMCE.triggerSave();
    }
 </script> 
@stop