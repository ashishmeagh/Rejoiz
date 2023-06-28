@extends('admin.layout.master')                
@section('main_content')
<!-- Page Content -->
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
            <div class="col-sm-12 col-xs-12">
              
               <form class="form-horizontal" id="validation-form" 
               > 
               {{ csrf_field() }}
               
               <div class="form-group row">
                  <label class="col-2 col-form-label">Tax id<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="text" class="form-control" name="tax_id" placeholder="Enter Tax Id" data-parsley-required="true"/>
                     <span class="red">{{ $errors->first('tax_id') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-2 col-form-label">Brand name<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="text" class="form-control" name="brand_name" placeholder="Enter brand name" data-parsley-required="true"  />
                     <span class="red">{{ $errors->first('brand_name') }}</span>
                  </div>
               </div>

                <div class="form-group row">
                  <label class="col-2 col-form-label">Web site url<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="url" class="form-control" name="website_url" placeholder="Enter website url" data-parsley-required="true" />
                     <span class="red">{{ $errors->first('website_url') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-2 col-form-label">First Name<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="text" class="form-control" name="first_name" placeholder="Enter First Name" data-parsley-required="true"  data-parsley-pattern="^[a-zA-Z ]+$"/>
                     <span class="red">{{ $errors->first('first_name') }}</span>
                  </div>
               </div>


                <div class="form-group row">
                  <label class="col-2 col-form-label">Last Name<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="text" class="form-control" name="last_name" placeholder="Enter lasr Name" data-parsley-required="true"  data-parsley-pattern="^[a-zA-Z ]+$"/>
                     <span class="red">{{ $errors->first('last_name') }}</span>
                  </div>
               </div>
               
               <div class="form-group row">
                  <label class="col-2 col-form-label">Email<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="text" class="form-control" name="email" placeholder="Enter Email" data-parsley-required="true" data-parsley-type="email" />
                     <span class="red">{{ $errors->first('email') }}</span>
                  </div>
               </div>
               
               <div class="form-group row">
                  <label class="col-2 col-form-label">Password<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="password" class="form-control" name="password" id = "password"placeholder="Enter password" data-parsley-required="true" data-parsley-length="[6, 16]"  />
                     <span class="red">{{ $errors->first('password') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-2 col-form-label">Confirm Password<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="password" class="form-control" name="confirm_password" placeholder="Confirm password" data-parsley-required="true" data-parsley-length="[6, 16]" data-parsley-equalto="#password"  />
                     <span class="red">{{ $errors->first('password') }}</span>
                  </div>
               </div>



               <div class="form-group row">
                  <label class="col-2 col-form-label">Contact No.<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="text" class="form-control" name="contact_no" placeholder="Enter Contact No" data-parsley-required="true" data-parsley-length="[6, 16]"  />
                     <span class="red">{{ $errors->first('contact_no') }}</span>
                  </div>
               </div>
               
               <div class="form-group row">
                  <label class="col-2 col-form-label">Primary Category<i class="red">*</i></label>
                  <div class="col-10">
                     
                         <select name="primary_category_id" id="primary_category_id" class="form-control" data-parsley-required ='true'>
                              <option value="">Select Category</option>
                                @foreach($categories_arr as $category)
                                  <option value="{{ $category['id'] }}">{{ $category['category_name'] }}</option>
                                @endforeach
                              </select>
                     </select>
                     <span class="red">{{ $errors->first('category') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-2 col-form-label">No of Stores<i class="red">*</i></label>
                  <div class="col-10">
                     
                      <select name="no_of_store" id="no_of_store" class="form-control" data-parsley-required ='true'>
                              <option value="">Select no of stores</option>
                                
                                  <option value="1">1</option>
                                  <option value="2">2</option>
                                  <option value="3">3</option>
                                  <option value="4">4</option>
                                  <option value="5">5</option>
                                  <option value="6">6</option>
                                  <option value="7">7</option>
                                  <option value="8">8</option>
                                  <option value="9">9</option>
                                  <option value="10">10</option>
                              
                              </select>
                     <span class="red">{{ $errors->first('no_of_store') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-2 col-form-label">Nationality<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="text" class="form-control" name="nationality" placeholder="Enter nationality" data-parsley-required="true"  />
                     <span class="red">{{ $errors->first('nationality') }}</span>
                  </div>
               </div>

                <div class="form-group row">
                  <label class="col-2 col-form-label">Instagram Handle<i class="red">*</i></label>
                  <div class="col-10">
                     <input type="text" class="form-control" name="instagram_handle" placeholder="Enter Instagram Handle" data-parsley-required="true" />
                     <span class="red">{{ $errors->first('instagram_handle') }}</span>
                  </div>
               </div>

               <div class="form-group row">
                  <label class="col-2 col-form-label" for="profile_image">Profile Image <i class="red">*</i></label>
                  <div class="col-10">
                     <input type="file" name="profile_image" id="profile_image" class="dropify" data-default-file="{{url('/')}}/uploads/default.jpeg" />
                  </div>
               </div>
               <span>{{ $errors->first('profile_image') }}</span>
               <div class="form-group row">
                  <div class="col-10">
                     <button type="button" class="btn btn-success waves-effect waves-light m-r-10" id = "btn_add" value="Save">Save</button>
                        <a class="btn btn-inverse waves-effect waves-light" href="{{ URL::previous() }}"><i class="fa fa-arrow-left"></i> Back</a>
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
  const module_url_path = "{{ $module_url_path }}";
   $(document).ready(function(){

       $('#btn_add').click(function(){

          
        if($('#validation-form').parsley().validate()==false) return;
        var formdata = new FormData($("#validation-form")[0]);
        // var formdata = new FormData($("#validation-form")[0]);
        
        
        $.ajax({
          url: module_url_path+'/save',
          type:"POST",
          data: formdata,
          contentType:false,
          processData:false,
          dataType:'json',
          success:function(data)
          {

             if('success' == data.status)
             {
                 
                 $('#validation-form')[0].reset();

                 swal({
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
                          // window.location = data.link;
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