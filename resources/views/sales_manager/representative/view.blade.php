@extends('sales_manager.layout.master')  
@section('main_content')
<style>
.row{
     padding-bottom:0px;
  }
  .dataTables_filter{
    text-align: right;
  }
  .dataTables_filter label input{margin-left: 5px;}
</style> 
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{url('/')}}/sales_manager/dashboard">Dashboard</a></li>
               <li><a href="{{$module_url_path or ''}}">My Representatives</a></li>
               <li class="active">{{$page_title or ''}}</li>
            </ol>
         </div>
      </div>
               <div class="row">
                  <div class="col-sm-12 col-xs-12">
                     <h3>
                        <span 
                           class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" >
                        </span>
                     </h3>
                  </div>
               </div>
               <div class="row">
                  <div class="col-sm-12">
                        @php
                        $kyc_status = isset($arr_user['kyc_status'])?$arr_user['kyc_status']:0;
                        @endphp              
                     
                       {{--   <div class="table-responsive">
                           <table class="table table-striped table-bordered view-porifile-table">
                              <tr>
                                 <th>Tax Id</th>
                                 <td>{{ isset($arr_user['get_user_details']['tax_id']) && $arr_user['get_user_details']['tax_id'] !=""  ?$arr_user['get_user_details']['tax_id']:'NA' }}</td>
                              </tr>
                              <tr>
                                 <th> Representative Name</th>
                                 <td>
                                    @php
                                    $first_name = isset($arr_user['get_user_details']['first_name']) && $arr_user['get_user_details']['first_name'] !=""  ?$arr_user['get_user_details']['first_name']:'NA';
                                    $last_name  = isset($arr_user['get_user_details']['last_name']) && $arr_user['get_user_details']['last_name'] !=""  ?$arr_user['get_user_details']['last_name']:'NA';
                                    @endphp
                                    {{ $first_name.' '.$last_name }}
                                 </td>
                              </tr>
                              <tr>
                                 <th>Area</th>
                                 <td>
                                    @php
                                    $area_name  = isset($arr_user['get_area_details']['area_name']) && $arr_user['get_area_details']['area_name'] !=""  ?$arr_user['get_area_details']['area_name']:'NA';
                                    @endphp
                                    {{ $area_name or '' }}
                                 </td>
                              </tr>
                              <tr>
                                 <th>Email Id</th>
                                 <td>{{ isset($arr_user['get_user_details']['email']) && $arr_user['get_user_details']['email'] !=""  ?$arr_user['get_user_details']['email']:'NA' }}</td>
                              </tr>
                              <tr>
                                 <th>Contact</th>
                                 <td>{{$contact_no}}</td>
                              </tr>
                              <tr>
                                 <th>Zip/Postal Code</th>
                                 <td>{{ isset($arr_user['get_user_details']['post_code']) && $arr_user['get_user_details']['post_code'] !=""  ?$arr_user['get_user_details']['post_code']:'NA' }}</td>
                              </tr>
                              <tr>
                                 <th>Country</th>
                                 <td>{{ isset($arr_user['get_user_details']['country_id']) && $arr_user['get_user_details']['country_id'] !=""  ?get_country($arr_user['get_user_details']['country_id']):'NA' }}</td>
                              </tr>
                           </table>
                        </div>
                        --}}
                              <div class="row">
                                <div class="col-sm-12">
                                 <div class="white-box">
                                  <div class="col-sm-12 admin_profile common-profile">
                                    <div class="row">
                                      <div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 left">
                                    <div class="main-adm-profile">
                           @php
                              $product_img_path = ""; 
                              $image_name = (isset($arr_user['get_user_details']['profile_image']))? $arr_user['get_user_details']['profile_image']:"";
                              $image_type = "user";
                              $is_resize = 0; 
                              $product_img_path = imagePath($image_name, $image_type, $is_resize);                           
                           @endphp
                           <div class="imgview-profile-adm"> <img src="{{$product_img_path}}"></div>
                           <div class="profile-txtnw-adm">Profile Image</div>
                        </div>
                      </div>
                                   @include('admin.layout._operation_status')


                                    <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 right">
                                      <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                             <label><span class="col-left-vw">Tax Id</span></label>
                                             <div>
                                                <span>{{ isset($arr_user['get_user_details']['tax_id']) && $arr_user['get_user_details']['tax_id'] !=""  ?$arr_user['get_user_details']['tax_id']:'NA' }}</span>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                             <label><span class="col-left-vw">Tax Id</span></label>
                                             <div>
                                                <span>{{ isset($arr_user['get_user_details']['tax_id']) && $arr_user['get_user_details']['tax_id'] !=""  ?$arr_user['get_user_details']['tax_id']:'NA' }}</span>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                             <label><span class="col-left-vw">Representative Name</span></label>
                                             <div>
                                                <span> @php
                                                $first_name = isset($arr_user['get_user_details']['first_name']) && $arr_user['get_user_details']['first_name'] !=""  ?$arr_user['get_user_details']['first_name']:'NA';
                                                $last_name  = isset($arr_user['get_user_details']['last_name']) && $arr_user['get_user_details']['last_name'] !=""  ?$arr_user['get_user_details']['last_name']:'NA';
                                                @endphp
                                                {{ $first_name.' '.$last_name }}</span>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                             <label><span class="col-left-vw">Area</span></label>
                                             <div>
                                                <span>@php
                                                $area_name  = isset($arr_user['get_area_details']['area_name']) && $arr_user['get_area_details']['area_name'] !=""  ?$arr_user['get_area_details']['area_name']:'NA';
                                                @endphp
                                                {{ $area_name or '' }}</span>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                             <label><span class="col-left-vw">Email Id</span></label>
                                             <div>
                                                <span>{{ isset($arr_user['get_user_details']['email']) && $arr_user['get_user_details']['email'] !=""  ?$arr_user['get_user_details']['email']:'NA' }}</span>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                             <label><span class="col-left-vw">Contact</span></label>
                                             <div>
                                                <span>@php
                                                         $countryCode = $arr_user['get_user_details']['country_code'];
                                                         $contact_no = str_replace($countryCode, "", $arr_user['get_user_details']['contact_no']);                                        
                                                      @endphp
                                                      {{ $countryCode.'-'.get_contact_no($contact_no) }}
                                                </span>
                                             </div>
                                          </div>
                                          <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                             <label><span class="col-left-vw">Zip/Postal Code</span></label>
                                             <div>
                                                <span>{{ isset($arr_user['get_user_details']['post_code']) && $arr_user['get_user_details']['post_code'] !=""  ?$arr_user['get_user_details']['post_code']:'NA' }}</span>
                                             </div>
                                          </div>
                                    </div>
                                  </div>
                                  </div>
                                 </div>
                               </div>
                              </div>
                  </div>
                  <div class="col-sm-12">
                     <div class="white-box">
                        <div class="table-responsive">
                           <input type="hidden" name="multi_action" value="" />
                           <table class="table table-striped"  id="table_module" >
                              <thead>
                                 <tr>
                                    <th>Vendor Name</th>
                                    <th>Vendor Company</th>
                                    <th>Action</th>
                                 </tr>
                              </thead>
                              <tbody>
                                 @foreach($arr_data as $data)
                                 @php
                                 $first_name    = isset($data['first_name']) && $data['first_name'] !=""  ?$data['first_name']:'NA';
                                 $last_name     = isset($data['last_name']) && $data['last_name'] !=""  ?$data['last_name']:'NA';
                                 $company_name = isset($data['company_name']) && $data['company_name'] !=""  ?$data['company_name']:'NA';
                                 @endphp
                                 <tr>
                                    <td> {{ $first_name.' '.$last_name }}  </td>
                                    <td> {{ $company_name or ''}}  </td>
                                    <td> 
                                       <a href="{{url('/').'/sales_manager'.'/delete_vendor'}}/{{base64_encode($data['mapping_id'])}}" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-circle btn-danger btn-outline show-tooltip" onclick="confirm_delete(this,event);">Delete</a>
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
                  <button class="btn btn-success" onclick="perform_kyc_action({{$arr_user['id']}},1)" type="button">Approve</button>
                  &nbsp;&nbsp;&nbsp;&nbsp;
                  <button class="btn btn-danger" onclick="perform_kyc_action({{$arr_user['id']}},4)" type="button">Reject</button>
               </div>
               @endif
               <div class="form-group row">
                  <div class="col-md-12 text-right p-0">
                     <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
                  </div>
               </div>
            </div>
</div>
<!-- END Main Content -->
<script type="text/javascript">
   var module_url_path = '{{$module_url_path or ''}}';
   
   function perform_kyc_action(user_id,kyc_status)
   {
       var msg = 'Are you sure? Do you want to perform this action.';
   
       swal({
             title:"Need Confirmation",
             text : msg,
             type : "warning",
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
                         title: "Success",
                         text : response.description,
                         type : "success",
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
       confirm_action(ref,event,'Are you sure? Do you want to delete this record.');
   }
   
</script>
@stop