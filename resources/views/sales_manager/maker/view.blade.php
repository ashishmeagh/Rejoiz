@extends('sales_manager.layout.master')                
@section('main_content')
<style>
.row{
     padding-bottom: 20px;
  }
</style> 
<!-- Page Content -->
<link rel="stylesheet" type="text/css" href="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.css">

<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">{{$page_title or ''}}</h4>
   </div>
   
   <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.sales_manager_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">My {{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   <div class="col-sm-12">
      <div class="">
         @include('admin.layout._operation_status')
          <div class="row">
            <div class="col-sm-12 col-xs-12">
                 <h3>
                    <span 
                       class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" >
                    </span>
                 </h3>
            </div>
          </div>
                    
           <div class="col-sm-12">
            @php
              $kyc_status = isset($arr_user_data['kyc_status'])?$arr_user_data['kyc_status']:0;
            @endphp              
         
         <div class="row">
          <!-- <div class="col-md-12">
            <div class="main-adm-profile">
           @php
              $profile_image = getProfileImage($arr_user_data['profile_image']);
               $contact_no = isset($arr_user_data['contact_no'])?get_contact_no($arr_user_data['contact_no']):'-';
            @endphp
            <div class="imgview-profile-adm"><img src="{{$profile_image}}"> </div>
            <div class="profile-txtnw-adm">Profile Image</div>
          </div>
          </div> -->
          <div class="row">
           <div class="col-sm-12">
            <div class="white-box">
{{--                 <table class="table table-striped table-bordered view-porifile-table table-responsive">
                        <tr>
                          <th>Name</th>
                          <td>
                            @php
                              $first_name = isset($arr_user_data['first_name']) && $arr_user_data['first_name'] !=""  ?$arr_user_data['first_name']:'N/A';
                              $last_name  = isset($arr_user_data['last_name']) && $arr_user_data['last_name'] !=""  ?$arr_user_data['last_name']:'N/A';
                            @endphp
                            {{ $first_name.' '.$last_name }}
                          </td>
                        </tr>
                        <tr>
                          <th>Email Id</th>
                          <td>{{ isset($arr_user_data['email']) && $arr_user_data['email'] !=""  ?$arr_user_data['email']:'N/A' }}</td>
                        </tr> 
                       <tr>
                          <th>Tax Id</th>
                          <td>{{ isset($arr_user_data['tax_id']) && $arr_user_data['tax_id'] !=""  ?$arr_user_data['tax_id']:'N/A' }}</td>
                        </tr>                           
                        <tr>
                          <th>Contact</th>
                          <td>{{$contact_no}}</td>
                        </tr>
  
                        <tr>
                          <th>Country</th>
                          <td>{{ isset($arr_user_data['country_id']) && $arr_user_data['country_id'] !=""  ?get_country($arr_user_data['country_id']):'N/A' }}</td>
                        </tr>

                        <tr>
                          <th>Zip/Postal Code</th>
                          <td>{{ isset($arr_user_data['post_code']) && $arr_user_data['post_code'] !=""  ?$arr_user_data['post_code']:'N/A' }}</td>
                        </tr>

                      </table> --}}

                     <section>
                         <div class="col-sm-12">
                          <div class="row">
                            <div class="col-md-12">
            <div class="main-adm-profile">
           @php
              $product_img_path = ""; 
              $image_name = (isset($arr_user_data['profile_image']))? $arr_user_data['profile_image']:"";
              $image_type = "user";
              $is_resize = 0; 
              $product_img_path = imagePath($image_name, $image_type, $is_resize);              
            @endphp
            <div class="imgview-profile-adm"><img src="{{$product_img_path}}"> </div>
            <div class="profile-txtnw-adm">Profile Image</div>
          </div>
          </div>
                          </div>
                          <div class="row">
                            <div class="col-sm-12 col-md-6 col-lg-3 profile_box">
                              <label><div class="lbl-font-wight">Name</div></label>
                              <div>
                                    @php
                                    $first_name = isset($arr_user_data['first_name']) && $arr_user_data['first_name'] !=""  ?$arr_user_data['first_name']:'N/A';
                                    $last_name  = isset($arr_user_data['last_name']) && $arr_user_data['last_name'] !=""  ?$arr_user_data['last_name']:'N/A';
                                    @endphp
                                   <span>{{ $first_name.' '.$last_name }}</span>
                                 </div>
                            </div>
                            <div class="col-sm-12 col-md-6 col-lg-3 profile_box">
                                 <label><div class="lbl-font-wight">Email Id</div></label>
                               <div>
                                 <span>{{ isset($arr_user_data['email']) && $arr_user_data['email'] !=""  ?$arr_user_data['email']:'N/A' }}</span>
                               </div>
                             </div>

                             <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">Tax Id</div></label>
                                 <div>
                                   <span>{{ isset($arr_user_data['tax_id']) && $arr_user_data['tax_id'] !=""  ?$arr_user_data['tax_id']:'N/A' }}</span>
                                 </div>
                               </div>
                               <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">Contact</div></label>
                                 <div>
                                   <span>
                                      @php
                                        $countryCode = ltrim($arr_user_data['country_code'],'+');
                                        $contact_no = str_replace($countryCode, "", $arr_user_data['contact_no']);                                        
                                      @endphp
                                    {{ '+'.$countryCode.'-'.get_contact_no($contact_no) }}</span>
                                 </div>
                               </div>
                               <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">Country</div></label>
                                 <div>
                                   <span>{{ isset($arr_user_data['country_id']) && $arr_user_data['country_id'] !=""  ?get_country($arr_user_data['country_id']):'N/A' }}</span>
                                 </div>
                               </div>
                               <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">Zip/Postal Code</div></label>
                                 <div>
                                   <span>{{ isset($arr_user_data['post_code']) && $arr_user_data['post_code'] !=""  ?$arr_user_data['post_code']:'N/A' }}<span>
                                 </div>
                               </div>
                               <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">Company</div></label>
                                 <div>
                                   <span>{{ isset($arr_user_data['maker_details']['company_name']) && $arr_user_data['maker_details']['company_name'] !=""  ?$arr_user_data['maker_details']['company_name']:'N/A' }}</span>
                                 </div>
                               </div>
                               <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">Web Site Url</div></label>
                                 <div>
                                   <span><a href="http://{{$arr_user_data['maker_details']['website_url']}}" target="_blank">{{ isset($arr_user_data['maker_details']['website_url']) && $arr_user_data['maker_details']['website_url'] !=""  ?$arr_user_data['maker_details']['website_url']:'NA' }}</a></span>
                                 </div>
                               </div>
                               <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">Selected Primary Category</div></label>
                                 <div>
                                      @foreach($categories_arr as $category)
                            @if($arr_user_data['maker_details']['primary_category_id']== $category['id'])
                                   <span>{{ $category['category_name'] }}</span>
                            @endif
                            @endforeach       
                                 </div>
                               </div>
                               <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">No of Stores</div></label>
                                 <div>
                                   <span>{{ isset($arr_user_data['maker_details']['no_of_stores']) && $arr_user_data['maker_details']['no_of_stores'] !=""  ?$arr_user_data['maker_details']['no_of_stores']:'N/A' }}</span>
                                 </div>
                               </div>
                               <div class="col-sm-12 col-md-6 col-lg-3 profile_box ">
                                   <label><div class="lbl-font-wight">Instagram Url</div></label>
                                 <div>
                                   <span>{{ isset($arr_user_data['maker_details']['insta_url']) && $arr_user_data['maker_details']['insta_url'] !=""  ?$arr_user_data['maker_details']['insta_url']:'N/A' }}</span>
                                 </div>
                               </div>
                          </div>

                           </div>
                    </section>

                      </div>
                       </div>
                     </div>
         </div>
          <div class="row">
         <div class="white-box">
                 <div class="table-responsive">
                  <input type="hidden" name="multi_action" value="" />
                    <table class="table table-striped"  id="table_module" >
                       <thead>
                          <tr>
                              
                             <th>Representative</th>
                             <th>Email Id</th>
                             <th>Contact No.</th>
                             <th>Sales Manager</th>
                             <th>Area</th>
                             <th>Action</th>
                          </tr>
                       </thead>
                 <tbody>
                

                  @foreach($representative_data_arr as $data)
                        @php
                         
                              $mapping_id = $data['id'];

                              $first_name = isset($data['get_representative_details']['get_user_details']['first_name']) && $data['get_representative_details']['get_user_details']['first_name'] !=""  ?$data['get_representative_details']['get_user_details']['first_name']:'N/A';

                              $last_name  = isset($data['get_representative_details']['get_user_details']['last_name']) && $data['get_representative_details']['get_user_details']['last_name'] !=""  ?$data['get_representative_details']['get_user_details']['last_name']:'N/A';

                               if(isset($data['get_representative_details']['get_user_details']['contact_no']) && $data['get_representative_details']['get_user_details']['contact_no'] !="")  
                               {
                                 $rep_contact_no = get_contact_no($data['get_representative_details']['get_user_details']['contact_no']);
                               }


                               $email = isset($data['get_representative_details']['get_user_details']['email']) && $data['get_representative_details']['get_user_details']['email'] !=""  ?$data['get_representative_details']['get_user_details']['email']:'N/A';

                               $sales_fname = isset($data['get_representative_details']['sales_manager_details']['get_user_data']['first_name']) && $data['get_representative_details']['sales_manager_details']['get_user_data']['first_name'] !=""  ?$data['get_representative_details']['sales_manager_details']['get_user_data']['first_name']:'N/A';

                               $sales_lname = isset($data['get_representative_details']['sales_manager_details']['get_user_data']['last_name']) && $data['get_representative_details']['sales_manager_details']['get_user_data']['last_name'] !=""  ?$data['get_representative_details']['sales_manager_details']['get_user_data']['last_name']:'N/A';

                               $sales_manager_name = $sales_fname.' '.$sales_lname;


                               $area_name = isset($data['get_representative_details']['get_area_details']['area_name']) && $data['get_representative_details']['get_area_details']['area_name'] !=""  ?$data['get_representative_details']['get_area_details']['area_name']:'N/A';


                        @endphp
                  <tr>
                     
                     <td>{{ $first_name.' '.$last_name }}  </td>
                     <td>{{$email or ''}}</td>
                     <td>{{ $rep_contact_no or '' }}  </td>
                     <td>{{$sales_manager_name or ''}}</td>
                     <td>{{ $area_name or ''}}</td>
                     <td> 
                         <a href="{{$module_url_path.'/delete_rep_mapping'}}/{{base64_encode($mapping_id)}}" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-circle btn-danger btn-outline show-tooltip" onclick="confirm_delete(this,event);">Delete</a>
                     
                     </td>
                  </tr>
                  @endforeach
                 
                  </tbody>
                  </table>
                 </div>
                </div>
              </div>
       </div>
               @if($kyc_status == 3)
                        <div class="form-group row">
                          <button class="btn btn-success" onclick="perform_kyc_action({{$arr_user_data['id']}},1)" type="button">Approve</button>
                          &nbsp;&nbsp;&nbsp;&nbsp;
                          <button class="btn btn-danger" onclick="perform_kyc_action({{$arr_user_data['id']}},4)" type="button">Reject</button>
                        </div>
                      @endif

                     <div class="form-group ">
                        <div class="col-md-12">
                           <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path}}" ><i class="fa fa-arrow-left"></i> Back</a>
                        </div>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
<!-- END Main Content -->

<script type="text/javascript">

var module_url_path = '{{$module_url_path}}';

function perform_kyc_action(user_id,kyc_status)
{
  var msg = 'Are you sure? Do you want to perform this action.';

  swal({
        title :'Need Confirmation',
        text: msg,
        type: "warning",
        showCancelButton: true,                
        confirmButtonColor: "#444",
        confirmButtonText: "OK",
        closeOnConfirm: true
  },
  function(isConfirm,tmp)
  {
    if(isConfirm==true)
    {
      update_kyc_status(user_id,kyc_status);
    }
  });
}


function update_kyc_status(user_id,kyc_status)
{
  $.ajax({
        url:module_url_path+'/update_kyc_status?_token='+'{{csrf_token()}}',
        data:{
              user_id:btoa(user_id),
              kyc_status:kyc_status
        },
        method:'POST',       
        dataType:'json',
        beforeSend : function()
        { 
          // showProcessingOverlay();          
        },
        success:function(response)
        {
          // hideProcessingOverlay();
          
          if(typeof response =='object')
          {
            if(response.status && response.status=="success")
            {
              swal({
                      title: "Need Confirmation",
                      text: response.description,
                      type: "success",
                      showCancelButton: false,                
                      confirmButtonColor: "#444",
                      confirmButtonText: "OK",
                      closeOnConfirm: true
              },
              function(isConfirm,tmp)
              {
                  if(isConfirm==true)
                  {
                    location.reload();
                  }
              });
            }
            else
            {                    
              swal('Error',response.description,'error');  
            }
          }
        }
      });
}

  $(document).ready(function() {
       var table =  $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
          stateSave: true
       });

        $("#table_module").on('page.dt', function (){

          var info = table.page.info();
         
         $("input.checkboxInputAll").prop('checked',false);
      
        });
    
     });

function confirm_delete(ref,event)
{
    confirm_action(ref,event,'Are you sure? Do you want to delete this representative.');
}

</script>
@stop