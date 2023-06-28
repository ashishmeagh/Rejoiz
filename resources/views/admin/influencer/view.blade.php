@extends('admin.layout.master')                
@section('main_content')
<style>
   .row{
   padding-bottom: 20px;
   }
</style>

<!-- Page Content -->
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$page_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
               <li><a href="{{ url(config('app.project.admin_panel_slug').'/dashboard') }}">Dashboard</a></li>
               <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
               <li class="active">{{$page_title or ''}}</li>
            </ol>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-12">
            <div class="white-box">
               @include('admin.layout._operation_status')
                  @php                     
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
                  @endphp       
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
                     <div class="imgview-profile-adm"> <img src="{{$product_img_path}}"></div>
                     <div class="profile-txtnw-adm">Profile Image</div>
                  </div>
                </div>
                  <div class="col-xs-12 col-sm-9 col-md-9 col-lg-10 right">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 profile_box">
                           <label>Influencer Name</label>
                           @php
                              $first_name = isset($arr_user['first_name']) && $arr_user['first_name'] !=""  ?$arr_user['first_name']:'NA';
                              $last_name  = isset($arr_user['last_name']) && $arr_user['last_name'] !=""  ?$arr_user['last_name']:'NA';
                           @endphp
                           <div>
                              <span>
                                 {{ $first_name.' '.$last_name }}
                              </span>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 profile_box">
                           <label>Influencer Code</label>
                           <div>
                              <span>
                              {{ isset($arr_user['influencer_code']) && $arr_user['influencer_code'] !=""  ?$arr_user['influencer_code']:'NA' }}
                              </span>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 profile_box">
                           <label>Email Id</label>
                           <div>
                              <span>
                              {{ isset($arr_user['email']) && $arr_user['email'] !=""  ?$arr_user['email']:'NA' }}
                              </span>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 profile_box">
                           <label>Contact</label>
                           <div>
                              <span>
                                 {{ $contact_no }}
                              </span>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 profile_box">
                           <label>Country Name</label>
                           <div>
                              <span>
                                 {{ isset($arr_user['country_details']['name']) && $arr_user['country_details']['name'] !=""  ?$arr_user['country_details']['name']:'NA' }}
                              </span>
                           </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 profile_box">
                           <label>Zip/Postal Code</label>
                           <div>
                              <span>
                                 {{ isset($arr_user['post_code']) && $arr_user['post_code'] !=""  ?$arr_user['post_code']:'NA' }}
                              </span>
                           </div>
                        </div>

                         <div class="col-xs-12 col-sm-12 col-md-12 col-lg-3 profile_box">
                           <label>Registration Date</label>
                           <div>
                              <span>
                                 {{ isset($arr_user['created_at']) && $arr_user['created_at'] !="" ?us_date_format($arr_user['created_at']):'NA' }}
                              </span>
                           </div>
                        </div>
                        
                      </div>
                  </div> 
                  </div> 
                </div>
               
            </div>

            <div class="white-box">
               <!-- Show Assigned Promo Code Listing (START)-->
               <h4 class="text-bold mb-3">Assigned Promo Code Listing</h4>
               <div class="row">
               <div class="col-sm-12 table-responsive">
                  <input type="hidden" name="multi_action" value="" />
                  
                  <table class="table table-striped"  id="table_module" >
                    <thead>
                      <tr>
                        <th>Promo Code</th>
                        {{-- <th>Vendor Name</th> --}}
                        <th>Start Date</th>
                        <th>Expiry Date</th>
                        <th>Used Count</th>
                      </tr>
                     </thead>
                   </table>
                </div>
              </div>
              <!-- Show Assigned Promo Code Listing (END)-->

            </div>

            <div class="form-group row">
              <div class="col-md-12">
                 <a class="btn btn-success waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>
              </div>
            </div>

          </div>
      </div>

   </div>
</div>

<input type="hidden" name="influencer_id" id="influencer_id" value="{{ isset($arr_user['id'])?base64_encode($arr_user['id']):0 }}">

<!-- END Main Content -->

<script type="text/javascript">

  var module_url_path = "{{$module_url_path or ''}}";
  var table_module;

  $(document).ready(function() 
  {

      table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
         processing: true,
         serverSide: true,
         responsive:true,
         bFilter: false ,
         stateSave: true,
         //order : [[ 1, "desc" ]],
            
            ajax: {
                    url:module_url_path+'/get_assigned_promo_code_listing',

                   'data': function(d)
                        {
                          
                          d['column_filter[q_promo_code]']  = $("input[name='q_promo_code']").val()
                        /*  d['column_filter[q_vendor_name]'] = $("input[name='q_vendor_name']").val()*/
                          d['influencer_id'] = $('#influencer_id').val()
                        }
            
                  },
            
            columns: [
                
                  {data: 'promo_code_name', "orderable": false, "searchable":false},
                  /*{
                     render : function(data, type, row, meta) 
                     {
                         return row.company_name + ' <span>('+ row.vendor_name +')</span>';
                     },
                     "orderable": false,
                     "searchable":false
                  }*/

                   {data: 'assigned_date', "orderable": false, "searchable":false},

                   {data: 'expiry_date', "orderable": false, "searchable":false},
                   {data: 'promo_code_used_cnt', "orderable": false, "searchable":false},
                  ],
            
       });

        $('input.column_filter').on( 'keyup click', function () 
        {
             filterData();
        });

        /*search box*/
        // <td><input type="text" name="q_vendor_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
        $("#table_module").find("thead").append(`<tr>   
              
                <td><input type="text" name="q_promo_code" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

                <td></td>
                <td></td>
                <td></td>

            </tr>`);

        $('input.column_filter').on( 'keyup click', function () 
        {
            filterData();
        });
  });

  function filterData()
  {
    table_module.draw();
  }

</script>

@stop