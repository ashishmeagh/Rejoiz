@extends('retailer.layout.master')                
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
         <li><a href="{{ url(config('app.project.retailer_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   <!-- /.col-lg-12 -->
</div>
<!-- .row -->
<div class="row">
   <div class="col-md-12">
         @include('retailer.layout._operation_status')  
      <div class="white-box">
         <label>

            <h3>{{$module_title or ''}} Details</h3>
         </label>
         <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
               <tr>
                  <th>Enquiry ID</th>
                  <td>{{ $enquiry_arr['id'] or 'N/A' }}</td>
                  <th>Brand Name/Maker</th>
                  <td>{{isset($enquiry_arr['maker_data']['brand_name'])?ucfirst($enquiry_arr['maker_data']['brand_name']):""}}</td>
               </tr>
               <tr>
               </tr>
               <tr>
                  <th>Order Date</th>
                  <td> {{ isset($enquiry_arr['created_at'])?date('d-M-Y',strtotime($enquiry_arr['created_at'])):'N/A' }}</td>
                  <th>Total Costing (Retail)</th>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($enquiry_arr['total_retail_price'])?num_format($enquiry_arr['total_retail_price']) : 'N/A' }}</td>
               </tr>               
             <tr>
               <th>Total Amount (Wholesale)</th>
               <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($enquiry_arr['total_wholesale_price'])?num_format($enquiry_arr['total_wholesale_price']) : 'N/A' }}</td>
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
                  <th>SKU No</th>
                  <th>Quantity</th>
                  <th>Retail Price</th>
                  <th>Wholesale Price</th>
                  <th>Retails Sub Total</th>
                  <th>Wholesale Sub Total</th>
                  <th>Shipping Charges</th>
               </tr>
               @if(isset($enquiry_arr['quotes_details']) && count($enquiry_arr['quotes_details'])>0)

               @foreach($enquiry_arr['quotes_details'] as $quote)
               <tr>
                  <td>{{isset($quote['product_details']['product_name'])?ucfirst($quote['product_details']['product_name']):""}}</td>
                  
                  <td><div class="truncate">{!! $quote['product_details']['description'] or 'N/A' !!}</div></td>

                   <td><div class="truncate">{!! $quote['sku_no']  or 'N/A' !!}</div></td>
                  <td>{{ $quote['qty'] or 'N/A' }}</td>
                 
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($quote['retail_price'])?num_format($quote['retail_price']) : 'N/A' }}</td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>{{ isset($quote['wholesale_price'])?num_format($quote['wholesale_price']) : 'N/A' }}</td>
                   <td class="summmarytdsprice">
                     @php $sub_retail_price = $quote['qty'] * $quote['retail_price'] @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_retail_price)?num_format($sub_retail_price) : "N/A"}}
                  </td>
                  <td class="summmarytdsprice">
                     @php $sub_wholesale_price = $quote['qty'] * $quote['wholesale_price'] @endphp
                     <span class="fa fa-dollar"></span>{{isset($sub_wholesale_price)?num_format($sub_wholesale_price) : "N/A"}}
                  </td>
                  <td class="summmarytdsprice"><span class="fa fa-dollar"></span>0.00</td>
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

               <a target="_blank" class="btn btn-inverse waves-effect waves-light pull-right" href="{{ url('/')}}"><i class="fa fa-arrow-left"></i> Back To Home</a>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->
@stop