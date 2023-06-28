   @extends('representative.layout.master')                
@section('main_content')
<style type="text/css">
.rep-back-btn{
   margin-top: 30px;
   }
.row{
  padding-bottom: 20px;
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
               <li><a href="{{ url(config('app.project.representative_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
               <li class="active">{{$page_title or ''}}</li>
            </ol>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-12">
            <div class="white-box">
               <div class="col-sm-12 admin_profile common-profile">
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
               <div class="row">
               <div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 left">
                  <div class="main-adm-profile">
                     @php
                        $product_img_path = ""; 
                        $image_name = (isset($arr_data['profile_image']))? $arr_data['profile_image']:"";
                        $image_type = "user";
                        $is_resize = 0; 
                        $product_img_path = imagePath($image_name, $image_type, $is_resize);
                     @endphp
                     <div class="imgview-profile-adm"><img src="{{$product_img_path}}"></div>
                     <div class="profile-txtnw-adm">Profile Image</div>
                  </div>
               </div>
               <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 right">
                  @php
                  $kyc_status = isset($arr_data['kyc_status'])?$arr_data['kyc_status']:0;
                  @endphp              
                  <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="60">
                  <section>
                     <div>


                        <div class="row">
                           <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                       <label><span class="sanssemibold">Vendor</span></label>
                                       <div>
                                          <span>  @php
                                          $first_name = isset($arr_data['first_name']) && $arr_data['first_name'] !=""  ?$arr_data['first_name']:'NA';
                                          $last_name  = isset($arr_data['last_name']) && $arr_data['last_name'] !=""  ?$arr_data['last_name']:'NA';
                                          @endphp
                                          {{ $first_name.' '.$last_name }}</span>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                       <label><span class="sanssemibold">Vendor Company</span></label>
                                       <div>
                                          <span>{{isset($arr_data['maker_details']['company_name'])?$arr_data['maker_details']['company_name']:'NA'}}</span>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                       <label><span class="sanssemibold">Brand</span></label>
                                       <div>
                                          <span>{{ isset($arr_data['maker_details']['brand_name']) && $arr_data['maker_details']['brand_name'] !=""  ?$arr_data['maker_details']['brand_name']:'NA' }}</span>
                                       </div>
                                    </div>

                                    <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                       <label><span class="sanssemibold">Zip/Postal Code</span></label>
                                       <div>
                                          <span>{{ isset($arr_data['post_code']) && $arr_data['post_code'] !=""  ?$arr_data['post_code']:'NA' }}</span>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                       <label><span class="sanssemibold">Email Id</span></label>
                                       <div>
                                          <span>{{ isset($arr_data['email']) && $arr_data['email'] !=""  ?$arr_data['email']:'NA' }}</span>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                       <label><span class="sanssemibold">Contact No.</span></label>
                                       <div>
                                          <span>
                                          @php
                                             $countryCode = ltrim($arr_data['country_code'],'+');
                                             $contact_no = str_replace($countryCode, "", $arr_data['contact_no']);
                                          @endphp
                                          {{ '+'.$countryCode.'-'.get_contact_no($contact_no) }}</span>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                       <label><span class="sanssemibold">Primary Category</span></label>
                                       <div>
                                          <span>{{ isset($arr_data['maker_details']['primary_category_id']) && $arr_data['maker_details']['primary_category_id'] !="0" ?get_catrgory_name($arr_data['maker_details']['primary_category_id']):'N/A' }}</span>
                                       </div>
                                    </div>
                                    <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                       <label><span class="sanssemibold">Description</span></label>
                                       <div>
                                          <span>{{ isset($arr_data['description']) && $arr_data['description'] !=""  ?$arr_data['description']:'N/A' }}</span>
                                       </div>
                                    </div>
                        </div>

                           </div>
                  </section>
                  {{--                     
                  <div class="table-responsive">
                     <table class="table table-striped table-bordered view-porifile-table">
                        <tr>
                           <th> Vendor</th>
                           <td>
                              @php
                              $first_name = isset($arr_data['first_name']) && $arr_data['first_name'] !=""  ?$arr_data['first_name']:'NA';
                              $last_name  = isset($arr_data['last_name']) && $arr_data['last_name'] !=""  ?$arr_data['last_name']:'NA';
                              @endphp
                              {{ $first_name.' '.$last_name }}
                           </td>
                           <th>Vendor Company</th>
                           <td>{{isset($arr_data['maker_details']['company_name'])?$arr_data['maker_details']['company_name']:'NA'}}</td>
                        </tr>
                        <tr>
                           <th>Brand</th>
                           <td>{{ isset($arr_data['maker_details']['brand_name']) && $arr_data['maker_details']['brand_name'] !=""  ?$arr_data['maker_details']['brand_name']:'NA' }}</td>
                           <th>Zip/Postal code</th>
                           <td>{{ isset($arr_data['post_code']) && $arr_data['post_code'] !=""  ?$arr_data['post_code']:'NA' }}</td>
                        </tr>
                        <tr>
                           <th>Email Id</th>
                           <td>{{ isset($arr_data['email']) && $arr_data['email'] !=""  ?$arr_data['email']:'NA' }}</td>
                           <th>Contact No.</th>
                           <td>{{ isset($arr_data['contact_no'])?$arr_data['contact_no']:'N/A' }}</td>
                        </tr>
                        <tr>
                           <th>Primary Category</th>
                           <td>{{ isset($arr_data['maker_details']['primary_category_id']) && $arr_data['maker_details']['primary_category_id'] !="0" ?get_catrgory_name($arr_data['maker_details']['primary_category_id']):'N/A' }}</td>
                           <th>Description</th>
                           <td>
                              <div class="truncate">{{ isset($arr_data['description']) && $arr_data['description'] !=""  ?$arr_data['description']:'N/A' }}</div>
                           </td>
                        </tr>
                     </table>
                  </div>
                  --}}
               </div>
            </div>
               @if($kyc_status == 3)
               <div class="form-group row">
                  <button class="btn btn-success" onclick="perform_kyc_action({{$arr_data['id']}},1)" type="button">Approve</button>
                  &nbsp;&nbsp;&nbsp;&nbsp;
                  <button class="btn btn-danger" onclick="perform_kyc_action({{$arr_data['id']}},4)" type="button">Reject</button>
               </div>
               @endif
            </div>
            <div class="form-group row">
                  <div class="col-md-12 text-right">
                     <a class="btn btn-inverse waves-effect waves-light rep-back-btn" href="{{$module_url_path}}"><i class="fa fa-arrow-left"></i> Back</a>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
@stop