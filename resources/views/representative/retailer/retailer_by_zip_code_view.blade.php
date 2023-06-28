@extends('representative.layout.master')  
@section('main_content')
<style>
.row{
     padding-bottom: 20px;
  }
</style>  
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title or ''}} </h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{ url(config('app.project.representative_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
               <li class="active">{{$page_title or ''}}</li>
            </ol>
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-sm-12">
            <div class="white-box">
               <div class="col-sm-12">
                  <div class="row">
                     <div class="col-md-12">
                        <div class="main-adm-profile float_center">
                           @php
                           $path = isset($arr_user['profile_image'])?$arr_user['profile_image']:'';
                           $profile_image = getProfileImage($path);
                           $contact_no = isset($arr_user['contact_no'])?get_contact_no($arr_user['contact_no']):'';
                           @endphp
                           <div class="imgview-profile-adm"><img src="{{$profile_image}}"> </div>
                           <div class="profile-txtnw-adm">Profile Image</div>
                        </div>
                     </div>
                     <div class="col-sm-12">
                        <section>
                           <div class="container">

                              <div class="row">
                                 <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">User Name</span></label>
                                          <div>
                                             <span> @php
                                             $first_name = isset($arr_user['first_name']) && $arr_user['first_name'] !=""  ?$arr_user['first_name']:'-';
                                             $last_name  = isset($arr_user['last_name']) && $arr_user['last_name'] !=""  ?$arr_user['last_name']:'-';
                                             @endphp
                                             {{ $first_name.' '.$last_name }}
                                             </span>
                                          </div>
                                       </div>
                                       <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">Email Id</span></label>
                                          <div>
                                             <span>{{ isset($arr_user['email']) && $arr_user['email'] !=""  ?$arr_user['email']:'-' }}</span>
                                          </div>
                                       </div>
                                       <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">Contact</span></label>
                                          <div>
                                             <span>{{$contact_no}}</span>
                                          </div>
                                       </div>

                                       <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">Bill User Name</span></label>
                                          <div>
                                             <span>  
                                             @php
                                             $first_name = isset($addr_details['bill_first_name']) && $addr_details['bill_first_name'] !=""  ?$addr_details['bill_first_name']:'-';
                                             $last_name  = isset($addr_details['bill_last_name']) && $addr_details['bill_last_name'] !=""  ?$addr_details['bill_last_name']:'';
                                             @endphp
                                             {{ $first_name.' '.$last_name }}
                                             </span>
                                          </div>
                                       </div>
                                       <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">Bill Mobile No</span></label>
                                          <div>
                                             <span>{{ isset($addr_details['bill_mobile_no']) && $addr_details['bill_mobile_no'] !=""  ?$addr_details['bill_mobile_no']:'-' }}</span>
                                          </div>
                                       </div>
                                       <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">Bill Email</span></label>
                                          <div>
                                             <span>{{ isset($addr_details['bill_email']) && $addr_details['bill_email'] !=""  ?$addr_details['bill_email']:'-' }}</span>
                                          </div>
                                       </div>

                                       <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">Bill Complete Address</span></label>
                                          <div>
                                             <span>
                                             {{ isset($addr_details['bill_complete_address']) && $addr_details['bill_complete_address'] !=""  ?$addr_details['bill_complete_address']:'-' }}
                                             </span>
                                          </div>
                                       </div>
                                       <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">Bill City</span></label>
                                          <div>
                                             <span>{{ isset($addr_details['bill_city']) && $addr_details['bill_city'] !=""  ?$addr_details['bill_city']:'-' }}</span>
                                          </div>
                                       </div>
                                       <div class="col-sm-3 profile_box">
                                          <label><span class="sanssemibold">Bill State</span></label>
                                          <div>
                                             <span>{{ isset($addr_details['bill_state']) && $addr_details['bill_state'] !=""  ?$addr_details['bill_state']:'-' }}</span>
                                          </div>
                                       </div>
                              </div>
                           </div>
                        </section>
                     </div>
                     {{--            
                     <div class="col-md-6">
                        <table class="table table-striped table-bordered view-porifile-table">
                           <tr>
                              <th> User Name</th>
                              <td>
                                 @php
                                 $first_name = isset($arr_user['first_name']) && $arr_user['first_name'] !=""  ?$arr_user['first_name']:'-';
                                 $last_name  = isset($arr_user['last_name']) && $arr_user['last_name'] !=""  ?$arr_user['last_name']:'-';
                                 @endphp
                                 {{ $first_name.' '.$last_name }}
                              </td>
                           </tr>
                           <tr>
                              <th>Email Id</th>
                              <td>{{ isset($arr_user['email']) && $arr_user['email'] !=""  ?$arr_user['email']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Contact</th>
                              <td>{{$contact_no}}</td>
                           </tr>
                           <tr>
                              <th>Bill User Name</th>
                              <td>
                                 @php
                                 $first_name = isset($addr_details['bill_first_name']) && $addr_details['bill_first_name'] !=""  ?$addr_details['bill_first_name']:'-';
                                 $last_name  = isset($addr_details['bill_last_name']) && $addr_details['bill_last_name'] !=""  ?$addr_details['bill_last_name']:'';
                                 @endphp
                                 {{ $first_name.' '.$last_name }}
                              </td>
                           </tr>
                           <tr>
                              <th>Bill Mobile No</th>
                              <td>{{ isset($addr_details['bill_mobile_no']) && $addr_details['bill_mobile_no'] !=""  ?$addr_details['bill_mobile_no']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Bill Email</th>
                              <td>{{ isset($addr_details['bill_email']) && $addr_details['bill_email'] !=""  ?$addr_details['bill_email']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Bill Complete Address</th>
                              <td>{{ isset($addr_details['bill_complete_address']) && $addr_details['bill_complete_address'] !=""  ?$addr_details['bill_complete_address']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Bill City</th>
                              <td>{{ isset($addr_details['bill_city']) && $addr_details['bill_city'] !=""  ?$addr_details['bill_city']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Bill State</th>
                              <td>{{ isset($addr_details['bill_state']) && $addr_details['bill_state'] !=""  ?$addr_details['bill_state']:'-' }}</td>
                           </tr>
                        </table>
                     </div>
                     --}}
                     {{--            
                     <div class="col-md-6">
                        <table class="table table-striped table-bordered view-porifile-table">
                           <tr>
                              <th>Ship User Name</th>
                              <td>
                                 @php
                                 $first_name = isset($addr_details['ship_first_name']) && $addr_details['ship_first_name'] !=""  ?$addr_details['ship_first_name']:'-';
                                 $last_name  = isset($addr_details['ship_last_name']) && $addr_details['ship_last_name'] !=""  ?$addr_details['ship_last_name']:'';
                                 @endphp
                                 {{ $first_name.' '.$last_name }}
                              </td>
                           </tr>
                           <tr>
                              <th>Ship Mobile No</th>
                              <td>{{ isset($addr_details['ship_mobile_no']) && $addr_details['ship_mobile_no'] !=""  ?$addr_details['ship_mobile_no']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Ship Email</th>
                              <td>{{ isset($addr_details['ship_email']) && $addr_details['ship_email'] !=""  ?$addr_details['ship_email']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Ship Complete Address</th>
                              <td>{{ isset($addr_details['ship_complete_address']) && $addr_details['ship_complete_address'] !=""  ?$addr_details['ship_complete_address']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Ship City</th>
                              <td>{{ isset($addr_details['ship_city']) && $addr_details['ship_city'] !=""  ?$addr_details['ship_city']:'-' }}</td>
                           </tr>
                           <tr>
                              <th>Ship State</th>
                              <td>{{ isset($addr_details['ship_state']) && $addr_details['ship_state'] !=""  ?$addr_details['ship_state']:'-' }}</td>
                           </tr>
                        </table>
                     </div>
                     --}}
                  </div>
               </div>
               <div class="form-group row">
                  <div class="col-md-12 text-center">
                     <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path}}"><i class="fa fa-arrow-left"></i> Back</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->
@stop