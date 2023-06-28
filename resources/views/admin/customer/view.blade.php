@extends('admin.layout.master')                
@section('main_content')
<style>
.row{
     padding-bottom: 20px;
  }
</style> 
{{-- {{dd($arr_user)}} --}}
<!-- Page Content -->
<div id="page-wrapper">
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">{{$page_title or ''}}</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li>
            <a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a>
          </li>
          <li>
            <a href="{{$module_url_path}}">{{$module_title or ''}}</a>
          </li>
          <li class="active">{{$page_title or ''}}</li>
        </ol>
      </div>
      <!-- /.col-lg-12 -->
    </div>
    <!-- .row -->
    <div class="row">
      <div class="col-sm-12">
        <div class="white-box">
         @include('admin.layout._operation_status')
          
          <div class="row">
            <div class="col-sm-12 col-xs-12">
              <h3>
                <span class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" ></span>
              </h3>
            </div>
          </div>
          <div class="col-sm-12 admin_profile common-profile">
          <div class="row">
          <div class="col-xs-12 col-sm-3 col-md-3 col-lg-2 left">
            <div class="main-adm-profile">
              @php
                $product_img_path = ""; 
                $image_name = (isset($arr_user['profile_image']))? $arr_user['profile_image']:"";
                $image_type = "user";
                $is_resize = 0; 
                $product_img_path = imagePath($image_name, $image_type, $is_resize);
              @endphp                        
              <div class="imgview-profile-adm">
                <img src="{{$product_img_path}}">
                </div>
                <div class="profile-txtnw-adm">Profile Image</div>
              </div>
            </div>
            <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 right">
            @php
              $kyc_status = isset($arr_user['kyc_status'])?$arr_user['kyc_status']:0;
              $contact_no = $arr_user['contact_no'];
              if($arr_user['country_code'] != "")
              {
                $contact_no = str_replace($arr_user['country_code'],"",$contact_no);
                $contact_no = $arr_user['country_code'].'-'.get_contact_no($contact_no);
              }
              else
              {
                $contact_no = isset($arr_user['contact_no'])?get_contact_no($arr_user['contact_no']):'';
              }
              
                if(isset($arr_user['buying_status']))
                {
                      if($arr_user['buying_status']==1)
                      {
                        $buying_status =  "Buys for a physical retail store";
                      } 
                      elseif($arr_user['buying_status']==2)
                      {
                        $buying_status =  "Buys for an online-only store";
                      }
                      elseif($arr_user['buying_status']==3)
                      {
                        $buying_status =  "Buys for a pop-up shop";
                      } 
                      else
                      {
                        $buying_status =  "NA";
                      }
                }
            @endphp     

            <div class="row">
                <div class="col-sm-6">
                    {{-- <div class="row">
                        <label class="col-sm-12 col-md-4 col-lg-4 col-lg-12"><b>Tax Id</b></label>
                        <div class="col-sm-12 col-md-12 col-lg-12">
                          <span>{{ isset($arr_user['tax_id']) && $arr_user['tax_id'] !=""  ?$arr_user['tax_id']:'NA' }}</span>
                        </div>
                    </div> --}}
                    <div class="row">
                        <label class="col-sm-12 col-md-12 col-lg-12"><b>Email Id</b></label>
                        <div class="col-sm-12 col-md-12 col-lg-12">
                          <span>{{ isset($arr_user['email']) && $arr_user['email'] !=""  ?$arr_user['email']:'NA' }}</span>
                        </div>
                    </div>
                    <div class="row">
                        <label class="col-sm-12 col-md-4 col-lg-12"><b>Contact</b></label>
                        <div class="col-sm-12 col-md-12 col-lg-12">
                          <span>{{isset($contact_no)?$contact_no:'NA'}}</span>
                        </div>
                    </div>


                      <div class="row">
                        <label class="col-sm-12 col-md-4 col-lg-12"><b>Registration Date</b></label>
                        <div class="col-sm-12 col-md-12 col-lg-12">
                          <span>{{ isset($arr_user['created_at']) && $arr_user['created_at'] !="" ?us_date_format($arr_user['created_at']):'NA' }}</span>
                        </div>
                    </div>

                    {{-- @if(isset($arr_user['buying_status']) && $arr_user['buying_status']!="")
                        
                        <div class="row">
                            <label class="col-sm-12 col-md-12 col-lg-12"><b>Store Name</b></label>
                            <div class="col-sm-12 col-md-12 col-lg-12">
                              <span>{{ isset($arr_user['store_name']) && $arr_user['store_name'] !=""  ?ucfirst($arr_user['store_name']):'NA' }}</span>
                            </div>
                            @if($arr_user['buying_status']=='1')    
                                <label class="col-sm-12 col-md-12 col-lg-12"><b>Annual Sales</b></label>
                                <div class="col-sm-12 col-md-12 col-lg-12">
                                  <span>{{ isset($arr_user['annual_sales']) && $arr_user['annual_sales'] !=""  ?$arr_user['years_in_business']:'NA' }}</span>
                                </div>
                            @endif
                        </div>

                    @endif    --}} 
                </div>
                <div class="col-sm-6">
                    <div class="row">
                        <label class="col-sm-12 col-md-12 col-lg-12"><b>Name</b></label>
                        <div class="col-sm-12 col-md-12 col-lg-12">
                            @php
                                $first_name = isset($arr_user['first_name']) && $arr_user['first_name'] !=""  ?$arr_user['first_name']:'NA';
                                $last_name  = isset($arr_user['last_name']) && $arr_user['last_name'] !=""  ?$arr_user['last_name']:'NA';
                            @endphp
                          <span>{{ $first_name.' '.$last_name }}</span>
                        </div>
                    </div>


                    
                      @php  

                 
                        $user_address = "";

                        $address = isset($arr_user['address']) && $arr_user['address']!=''?$arr_user['address'].',':'';

                        $address2 = isset($arr_user['customer_details']['address2']) && $arr_user['customer_details']['address2']!=''?$arr_user['customer_details']['address2'].', ':'';

                        $city = isset($arr_user['customer_details']['city'])&&$arr_user['customer_details']['city']!=''?$arr_user['customer_details']['city'].', ':'';

                        $state = isset($arr_user['customer_details']['state'])&& $arr_user['customer_details']['state']!=''?$arr_user['customer_details']['state'].', ':'';

                        $country = isset($arr_user['country_id']) && $arr_user['country_id']!='' ? get_country($arr_user['country_id']).', ':'';

                        $post_code = isset($arr_user['post_code']) && $arr_user['post_code']!=''?$arr_user['post_code']:'';

                        $user_address = $address.' '.$address2.' '.$city.' '.$state.' '.$country.' '.$post_code;

                        if(isset($user_address)){
                          $user_address = trim($user_address,', ');
                        }


                      @endphp

                     <div class="row">
                          <label class="col-sm-12 col-md-12 col-lg-12"><b>Country</b></label>
                          <div class="col-sm-12 col-md-12 col-lg-12">
                               <span>{{ isset($arr_user['country_id']) && $arr_user['country_id'] !=""  ?get_country($arr_user['country_id']):'NA' }}</span>
                          </div>
                    </div>

                    @if(isset($user_address) && $user_address!="")
                        <div class="row">
                           <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                            <label><b>Address</b></label>
                            <div class="form-group">                               
                              <span>{{$user_address or ''}}</span>
                            </div>
                        </div>
                        </div>
                     @endif
                     
                   {{--  <div class="row">
                        <label class="col-sm-12 col-md-12 col-lg-12"><b>Buying status</b></label>
                        <div class="col-sm-12 col-md-12 col-lg-12">
                           
                          <span>{{ isset($buying_status)?$buying_status:'N/A'}}</span>
                        </div>
                    </div> --}}
                    @if(isset($arr_user['buying_status']) && $arr_user['buying_status']!="")
                                            
                        <div class="row">
                            @if($arr_user['buying_status']=='1')    
                            
                                <label class="col-sm-12 col-md-12 col-lg-12"><b>Years in Business</b></label>
                                <div class="col-sm-12 col-md-12 col-lg-12">
                                  <span>{{ isset($arr_user['years_in_business']) && $arr_user['years_in_business'] !=""  ?$arr_user['years_in_business']:'NA' }}</span>
                                </div>

                            @elseif($arr_user['buying_status']=='2')
                                <label class="col-sm-12 col-md-12 col-lg-12"><b>Store Website</b></label>
                                <div class="col-sm-12 col-md-12 col-lg-12">
                                  <span>{{ isset($arr_user['store_website']) && $arr_user['store_website'] !=""  ?$arr_user['store_website']:'NA' }}</span>
                                </div>

                            @elseif($arr_user['buying_status']=='3')

                                <label class="col-sm-12 col-md-12 col-lg-12"><b>Zip/Postal Code</b></label>
                                <div class="col-sm-12 col-md-12 col-lg-12">
                                  <span>{{ isset($arr_user['post_code']) && $arr_user['post_code'] !=""  ?$arr_user['post_code']:'NA' }}</span>
                                </div>

                            @elseif($arr_user['buying_status']=='4')

                                <label class="col-sm-12 col-md-12 col-lg-12"><b>Store Description</b></label>
                                <div class="col-sm-12 col-md-12 col-lg-12">
                                  <span>{{ isset($arr_user['store_description']) && $arr_user['store_description'] !=""  ?$arr_user['store_description']:'NA' }}</span>
                                </div>

                            @endif

                        </div>

                    @endif 


                
                </div>
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
                  <div class="col-sm-12 text-right">
                    <a class="btn btn-inverse waves-effect waves-light" href="{{$module_url_path or ''}}">
                      <i class="fa fa-arrow-left"></i> Back
                    </a>
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
        title:"Need Confirmation",
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
                      title:"Success",
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

</script>
@stop