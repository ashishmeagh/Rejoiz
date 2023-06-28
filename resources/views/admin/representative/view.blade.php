@extends('admin.layout.master')                
@section('main_content')
<style>
.dataTables_length {text-align:left !important;}
#table_module_filter {text-align:right !important;}
.row{
     padding-bottom: 20px;
  }
.input-break{
  word-break: break-all;
}
@media (max-width:575px) {
  .dataTables_length {text-align:center !important; margin-bottom:10px;}
#table_module_filter {text-align:center !important;}
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
          <div class="">
            @include('admin.layout._operation_status')
          
            <div class="row">
              <div class="col-sm-12 col-xs-12">
                <h3>
                  <span 
                       class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" ></span>
                </h3>
              </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                  <div class=" white-box">
                    @php
                      $kyc_status = isset($arr_user['kyc_status'])?$arr_user['kyc_status']:0;
                      $contact_no = $arr_user['get_user_details']['contact_no'];
                      if($arr_user['get_user_details']['country_code'] != "")
                      {
                        $contact_no = str_replace($arr_user['get_user_details']['country_code'],"",$contact_no);
                        $contact_no = $arr_user['get_user_details']['country_code'].'-'.get_contact_no($contact_no);
                      }
                      else
                      {
                        $contact_no = isset($arr_user['get_user_details']['contact_no'])?get_contact_no($arr_user['get_user_details']['contact_no']):'';
                      }                      
                    @endphp
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
                      <div class="imgview-profile-adm">
                        <img src="{{ $product_img_path }}">
                        </div>
                        <div class="profile-txtnw-adm">Profile Image</div>
                      </div>
                          </div>
                      <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 right">
                        <div class="col-sm-12 p-0">
                          <div class="row">
                          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
                            <div class="input-break">
                            <label><b>Name</b></label>
                              @php
                                $first_name = isset($arr_user['get_user_details']['first_name']) && $arr_user['get_user_details']['first_name'] !=""  ?$arr_user['get_user_details']['first_name']:'NA';
                                $last_name  = isset($arr_user['get_user_details']['last_name']) && $arr_user['get_user_details']['last_name'] !=""  ?$arr_user['get_user_details']['last_name']:'NA';
                              @endphp
                            <div class="form-group">
                              <span>
                              {{ $first_name.' '.$last_name }}</span>
                            </div>
                          </div>
                          </div>

                          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
                            <label><b>Area</b></label>
                              @php
                                
                                $area_name  = isset($arr_user['get_area_details']['area_name']) && $arr_user['get_area_details']['area_name'] !=""  ?$arr_user['get_area_details']['area_name']:'NA';

                              @endphp
                            <div class="form-group">
                              <span>{{ $area_name or '' }}</span>
                            </div>
                          </div>

                          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
                              <label><b>Contact</b></label>
                            <div class="form-group">
                              <span>{{$contact_no}}</span>
                            </div>
                          </div>




                          
                          
                          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
                              <label><b>Zip/Postal Code</b></label>
                            <div class="form-group">
                              <span>{{ isset($arr_user['get_user_details']['post_code']) && $arr_user['get_user_details']['post_code'] !=""  ?$arr_user['get_user_details']['post_code']:'NA' }}</span>
                            </div>
                          </div>

                          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
                            <label><b>Sales Manager Name</b></label>
                             @php
                                $first_name = isset($arr_user['sales_manager_details']['get_user_data']['first_name']) && $arr_user['sales_manager_details']['get_user_data']['first_name'] !=""  ?$arr_user['sales_manager_details']['get_user_data']['first_name']:'NA';

                                $last_name  = isset($arr_user['sales_manager_details']['get_user_data']['last_name']) && $arr_user['sales_manager_details']['get_user_data']['last_name'] !=""  ?$arr_user['sales_manager_details']['get_user_data']['last_name']:'NA';
                              @endphp
                            <div class="form-group">
                              <span> {{ $first_name.' '.$last_name }}</span>
                            </div>
                          </div>

                          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
                            <label><b>Email Id</b></label>
                            <div class="form-group">
                              <span>{{ isset($arr_user['get_user_details']['email']) && $arr_user['get_user_details']['email'] !=""  ?$arr_user['get_user_details']['email']:'NA' }}</span>
                            </div>
                          </div>


                          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
                              <label><b>Country Name</b></label>
                            <div class="form-group">
                              <span>{{ isset($arr_user['get_user_details']['country_details']['name']) && $arr_user['get_user_details']['country_details']['name'] !=""  ?$arr_user['get_user_details']['country_details']['name']:'NA' }}</span>
                            </div>
                          </div>

                          <div class="col-xs-12 col-sm-12 col-md-6 col-lg-3">
                              <label><b>Registration Date</b></label>
                            <div class="form-group">
                              <span>{{ isset($arr_user['created_at']) && $arr_user['created_at'] !="" ?us_date_format($arr_user['created_at']):'NA' }}</span>
                            </div>
                          </div>



                          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                              <label><b>Description</b></label>
                            <div class="form-group">
                              <span>{{ isset($arr_user['description']) && $arr_user['description'] !=""  ?$arr_user['description']:'NA' }}</span>
                            </div>
                          </div>

                          

                        </div>
                        </div>
                      </div>
                    </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-12">
                  <div class="white-box">
                    <div class="table-responsive admin-vendor-data-table">
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
                              $first_name = isset($data['first_name']) && $data['first_name'] !=""  ?$data['first_name']:'NA';
                              $last_name  = isset($data['last_name']) && $data['last_name'] !=""  ?$data['last_name']:'NA';
                               $company_name = isset($data['company_name']) && $data['company_name'] !=""  ?$data['company_name']:'NA';
                        @endphp
                  
                          <tr>
                            <td> {{ $first_name.' '.$last_name }}  </td>
                            <td> {{ $company_name }}  </td>
                            <td>
                              <a href="{{$module_url_path.'/delete_vendor'}}/{{base64_encode($data['mapping_id'])}}" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-circle btn-danger btn-outline show-tooltip" onclick="confirm_delete(this,event);">Delete</a>
                     
                     
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
              <div class="col-md-12 text-left">
                <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}">
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
        title: "Need Confirmation",
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
                      title: "Need Confirmation",
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