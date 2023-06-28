@extends('sales_manager.layout.master')  
@section('main_content')
<style>
   .row{
       padding-bottom:0px;
   }
</style>
<!-- Page Content -->
<div id="page-wrapper">
<div class="container-fluid">
   <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
         <h4 class="page-title">{{$page_title or ''}} </h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         <ol class="breadcrumb">
            <li><a href="{{ url(config('app.project.representative_panel_slug').'/dashboard') }}">Dashboard</a></li>
            <li><a href="{{$module_url_path}}">My {{$module_title or ''}}</a></li>
            <li class="active">{{$page_title or ''}}</li>
         </ol>
      </div>
   </div>
   <!-- .row -->
   <div class="row">
      <div class="col-sm-12">
         <div class="row">
                  <div class="col-sm-12">
                     <div class="white-box">
                        <div class="col-sm-12 admin_profile common-profile">
                        <div class="row">
                           <div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 left">
                           <div class="main-adm-profile">
               @php
                  $product_img_path = ""; 
                  $image_name = (isset($addr_details['profile_image']))? $addr_details['profile_image']:"";
                  $image_type = "user";
                  $is_resize = 0; 
                  $product_img_path = imagePath($image_name, $image_type, $is_resize);              
               @endphp
               <div class="imgview-profile-adm"><img src="{{$product_img_path}}"> </div>
               <div class="profile-txtnw-adm">Profile Image</div>
            </div>
         </div>

                        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 right">
                           <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">User Name</span></label>
                                 <div>
                                    <span>
                                    @php
                                    $first_name = isset($addr_details['first_name']) && $addr_details['first_name'] !=""  ?$addr_details['first_name']:'NA';
                                    $last_name  = isset($addr_details['last_name']) && $addr_details['last_name'] !=""  ?$addr_details['last_name']:'NA';
                                    @endphp
                                    {{ $first_name.' '.$last_name }}
                                    </span>
                                 </div>
                              </div>
                              <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Email Id</span></label>
                                 <div>
                                    <span>{{ isset($addr_details['email']) && $addr_details['email'] !=""  ?$addr_details['email']:'NA' }}</span>
                                 </div>
                              </div>
                              <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Tax Id</span></label>
                                 <div>
                                    <span>{{ isset($addr_details['tax_id']) && $addr_details['tax_id'] !=""  ?$addr_details['tax_id']:'NA' }}</span>
                                 </div>
                              </div>
                              <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Contact</span></label>
                                 <div>
                                    <span>
                                       @php

                                          $countryCode = $addr_details['country_code'];
                                          $contact_no = str_replace($countryCode, "", $addr_details['contact_no']);                                        
                                       @endphp
                                       {{ $countryCode.'-'.get_contact_no($contact_no) }}
                                    </span>
                                 </div>
                              </div>


                              <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Country</span></label>
                                 <div>
                                    <span>
                                       
                                       {{isset($addr_details['country_id'])?get_country($addr_details['country_id']):'-'}}
                                      
                                    </span>
                                 </div>
                              </div>

                              @if(isset($addr_details['retailer_details']['years_in_business']) && $addr_details['retailer_details']['years_in_business']!="")

                               <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Year In Business</span></label>
                                 <div>
                                    <span>
                                       {{$addr_details['retailer_details']['years_in_business'] or '-'}}
                                      
                                    </span>
                                 </div>
                              </div>

                              @endif

                              @if(isset($addr_details['retailer_details']['annual_sales']) && $addr_details['retailer_details']['annual_sales']!="")
                               
                                <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Annual Sales</span></label>
                                 <div>
                                    <span>
                                       
                                      {{$addr_details['retailer_details']['annual_sales'] or '-'}}
                                       
                                    </span>
                                 </div>
                              </div>

                              @endif


                              @if(isset($addr_details['retailer_details']['store_name']) && $addr_details['retailer_details']['store_name']!="")
                               
                                <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Store Name</span></label>
                                 <div>
                                    <span>
                                       
                                      {{$addr_details['retailer_details']['store_name'] or '-'}}
                                       
                                    </span>
                                 </div>
                              </div>

                              @endif


                             @if(isset($addr_details['retailer_details']['store_website']) && $addr_details['retailer_details']['store_website']!="")
                               
                                <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Store Website</span></label>
                                 <div>
                                    <span>
                                       
                                      {{$addr_details['retailer_details']['store_website'] or '-'}}
                                       
                                    </span>
                                 </div>
                              </div>

                            @endif


                              @if(isset($addr_details['post_code']) && $addr_details['post_code']!="")
                               
                                <div class="col-sm-12 col-md-12 col-lg-3 profile_box">
                                 <label><span class="col-left-vw">Zip Code</span></label>
                                 <div>
                                    <span>
                                       
                                      {{$addr_details['post_code'] or '-'}}
                                       
                                    </span>
                                 </div>
                              </div>

                            @endif

             

                           </div>

                           </div>
                        </div>
                        <div class="form-group row">
         <div class="col-md-12 text-right">
            <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
         </div>
      </div>
                     </div>
                  </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->
@stop