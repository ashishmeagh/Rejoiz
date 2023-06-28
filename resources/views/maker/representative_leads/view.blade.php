@extends('maker.layout.master')                
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
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>

<!-- .row -->
<div class="row">
   @include('admin.layout._operation_status')  
   <div class="col-md-12">
      <div class="white-box">
         <label>
            <h3>{{$module_title or ''}} Details</h3>
         </label>
         <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
               <tr>
                  <th>Order No.</th>
                  <td>{{ $leads_arr['id'] or 'N/A' }}</td>
                  {{-- <th>Leads Description</th>
                  <td>{{ $leads_arr['description'] or 'N/A' }}</td> --}}
              
                  <th>Representative Name</th>
                  <td>{{isset($leads_arr['representative_user_details']['first_name'])?ucfirst($leads_arr['representative_user_details']['first_name']).' '.$leads_arr['representative_user_details']['last_name']:""}}</td>
               </tr>
               
               <tr>
                  <th>Total Costing (Retail)</th>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($leads_arr['total_retail_price'])?num_format($leads_arr['total_retail_price']) : 'N/A' }}</td> 
                  
                   <th>Total Amount (Wholesale)</th>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($leads_arr['total_wholesale_price'])?num_format($leads_arr['total_wholesale_price']) : 'N/A' }}</td>
               </tr>               
               <tr>
                 
                  <th>Order Date</th>
                  {{-- <td> {{ isset($leads_arr['created_at'])?date('d-M-Y',strtotime($leads_arr['created_at'])):'N/A' }}</td> --}}
                  <td class="summmarytdsprice"> {{ isset($leads_arr['created_at'])?us_date_format($leads_arr['created_at']):'N/A' }}</td>

                  <th></th>
                  <td></td>                  
               </tr>
               
            </table>
         </div>
      </div>
   </div>
   <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="60">
   <div class="col-md-12">
      <div class="white-box">
         <label>
            <h3>Summary</h3>
         </label>
         <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
               <tr>
                  <th>Product Name</th>
                  <th>Description</th>
                  <th>Quantity</th>
                  <th>Price (Retail)</th>
                  <th>Price (Wholesale)</th>
                  <th>Retail Sub Total</th>
                  <th>Wholesale Sub Total</th>
               </tr>
               @if(isset($leads_arr['leads_details']) && count($leads_arr['leads_details'])>0)
               @foreach($leads_arr['leads_details'] as $lead)
               
              {{--  {{dd($leads_arr)}} --}}
               <tr>
                  <td>{{isset($lead['product_details']['product_name'])?ucfirst($lead['product_details']['product_name']):""}}</td>
                  <td>
                     <?php $lead['product_details']['description'] = "" ?>
                     @if(isset($lead['product_details']['description']) && $lead['product_details']['description']!=null)
                        <div class="truncate">{!! $lead['product_details']['description'] or 'N/A' !!}</div>
                     @else   
                        N/A      
                     @endif   
                  </td>
                  <td>{{ $lead['qty'] or 'N/A' }}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($lead['retail_price'])?num_format($lead['retail_price']) : 'N/A' }}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($lead['wholesale_price'])?num_format($lead['wholesale_price']) : 'N/A' }}</td>
                  <td class="summmarytdsprice">
                     @php  $sub_retail_price = $lead['qty'] * $lead['retail_price'] @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_retail_price) ? num_format($sub_retail_price) : 'N/A'}}
                  </td>
                  <td class="summmarytdsprice">
                     @php $sub_wholesale_price = $lead['qty'] * $lead['wholesale_price'] @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : "N/A"}}
                  </td>
               </tr>
               @endforeach
               @else
               <td colspan="7">No Record Found</td>
               @endif
            </table>
         </div>
      </div>
   </div>
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{URL::previous()}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
         </div>
      </div>
   </div>
</div>


<!-- END Main Content -->
@stop