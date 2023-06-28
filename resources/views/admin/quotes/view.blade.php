@extends('admin.layout.master')                
@section('main_content')
<style>
.dataTables_length {text-align:left !important;}
#table_module_filter {text-align:right !important;}
.row{
     padding-bottom: 10px;
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
    <div class="container-fluid labelnormal">
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
                    <div class="col-sm-12 admin_profile">                     
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>Product Name</b></label>                              
                              
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">

                                <label>{{ $product_details['product_name'] or 'N/A'}}</label>

                              
                            </div>                          
                         </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>Brand Name</b></label>   
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">

                                <label>{{ $product_details['brand_details']['brand_name'] or 'N/A'}}</label>

                            </div>                          
                         </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>User Name</b></label>   
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                               
                                <label>{{ $quote_details['name'] or 'N/A' }}</label>

                            </div>                          
                         </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>Email</b></label>                              
                              
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">

                                <label>{{ $quote_details['email'] or 'N/A' }}</label>

                            </div>                          
                         </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>Contact Number</b></label>                              
                              
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">

                                <label>{{ $quote_details['contact_number'] or 'N/A' }}</label>

                            
                            </div>                          
                         </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>Expected Delivery Days</b></label>                              
                            
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                                <label>{{ $quote_details['no_of_days_to_expected_delivery'] or 'N/A'}} Days</label>
                             
                            </div>                          
                         </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>Expected Delivery Date</b></label>   
                            </div>
                            @php
                            $date = "-";
                            if(strtotime($quote_details['expected_delivery_date'])!=null);
                            {
                              $date = date('m-d-Y', strtotime($quote_details['expected_delivery_date']));
                            }

                            @endphp
                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">
                                <label>{{$date or ''}}</label>
                            </div>                          
                         </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>Product Quantity</b></label>   
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">

                                <label>{{ $quote_details['quantity'] or 'N/A'}}</label>

                              
                            </div>                          
                         </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-sm-12">
                          <div class="row">
                            <div class="col-xs-12 col-sm-12 col-md-3 col-lg-3">
                              <label><b>Additional Notes</b></label>                              
                              
                            </div>

                            <div class="col-xs-12 col-sm-12 col-md-9 col-lg-9">

                                <label>{{ $quote_details['additional_note'] or 'N/A' }}</label>

                              
                            </div>
                         </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>                
            </div>  
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


  $(document).ready(function() {
       
    
     });

</script>
@stop