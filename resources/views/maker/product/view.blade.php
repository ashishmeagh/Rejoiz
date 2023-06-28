@extends('maker.layout.master')                
@section('main_content')
<style>
   .row{
   padding-bottom: 20px;
   }
   .tr-multiple-images {
        border-bottom: 1px solid #cccc;
   }
   .color-header{
      margin: 10px 30px;
   }
   
</style>
<div id="page-wrapper">
<div class="container-fluid">
<div class="row bg-title">
   <div class="col-sm-12 top-bg-title">
      <h4 class="page-title">{{$page_title or ''}}</h4>
      <div class="right">
      <ol class="breadcrumb">
         <li><a href="{{ url(config('app.project.maker_panel_slug').'/dashboard') }}">Dashboard</a></li>
         <li><a href="{{$module_url_path}}">{{$module_title or ''}}</a></li>
         <li class="active">{{$page_title or ''}}</li>
      </ol>
   </div>
   </div>
   
</div>


<div class="row">
   @include('maker.layout._operation_status')
   <div class="col-sm-12 col-xs-12">
      @if(isset($product_arr['remark']) && $product_arr['remark'] !='')
      <div class="form-mkr">
         <div class="labl-i">Remark :</div>
         <div class="red-color">{!!$product_arr['remark']!!}</div>
         <div class="clearfix"></div>
      </div>
      @endif
      @if(isset($product_arr['is_active']) && $product_arr['is_active'] =='1')
          <div class="form-mkr">
            <div class="labl-i">Approval Status : </div>
            <div class="red-color"><p><span style="color:#3CB371;font-weight:bold;padding: 8px;">Approved</span></p></div>
            <div class="clearfix"></div>
          </div>
        @endif

        @if(isset($product_arr['is_active']) && $product_arr['is_active'] =='2')
          <div class="form-mkr">
            <div class="labl-i">Approval Status : </div>
            <div class="red-color"><p><span style="color:#fec107;font-weight:bold;padding: 8px;">Pending</span></p></div>
            <div class="clearfix"></div>
          </div>
        @endif

   </div>
   <div class="col-sm-12 col-xs-12">
      <h3>
         <span 
            class="text-" ondblclick="scrollToButtom()" style="cursor: default;" title="Double click to Take Action" >
         </span>
      </h3>
   </div>
   <div class="col-md-6">
      <div class="white-box">
         <label>
            <h3>Product Details</h3>
         </label>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Image</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               @php
                  $product_img_path = ""; 
                  $image_name = (isset($product_arr['product_image']))? $product_arr['product_image']:"";
                  $image_type = "product";
                  $is_resize = 0; 
                  $product_img_path = imagePath($image_name, $image_type, $is_resize);              
               @endphp            
               <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}">
            </div>
         </div>

         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Name</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>
               {{ $product_arr['product_name'] or '-'}}
               </span>
            </div>
         </div>

         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Category</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>
               @php
               if(isset($product_arr['product_sub_categories']) && sizeof($product_arr['product_sub_categories'])>0)
               {
                  $category_name = isset($product_arr['product_sub_categories'][0]['category_details']['category_name'])?$product_arr['product_sub_categories'][0]['category_details']['category_name']:'-';

                  $category_name = isset($category_name)?$category_name:'-';
               } 
               else
               {
                  $category_name = '-';
               }
               @endphp

               {{ $category_name or '-'}}

               </span>
            </div>
         </div>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Sub Category</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span> 
               @php
               $data = [];

                  $show_sub_categories = "";
                     if(isset($sub_categories_arr) && !empty($sub_categories_arr))
                     {
                        // dd($sub_categories_arr);
                        foreach($sub_categories_arr as $sub_category_arr)
                        {
                          if(!in_array($sub_category_arr[0]['subcategory_name'],$data))
                          {
                            array_push($data,$sub_category_arr[0]['subcategory_name']);
                              
                              if(!empty($sub_category_arr))
                              {
                                 $show_sub_categories .= $sub_category_arr[0]['subcategory_name'].',';
                              }
                          }
                        }
                     }
                     else
                     {
                        $show_sub_categories = '-';
                     }
                     echo rtrim($show_sub_categories,',');
                  @endphp
               </span>
            </div>
         </div>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Third Level Category</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span> 
                  @php
                  $data = [];
                  $show_sub_categories = "";
                     if(isset($third_sub_category_arr) && !empty($third_sub_category_arr))
                     {
                        foreach($third_sub_category_arr as $third_sub_category_arr)
                        {
                             if(!in_array($third_sub_category_arr[0]['third_sub_category_name'],$data))
                             {
                               array_push($data,$third_sub_category_arr[0]['third_sub_category_name']);

                               $show_sub_categories .= $third_sub_category_arr[0]['third_sub_category_name'].',';
                             }
                        }
                     }
                     else
                     {
                        $show_sub_categories = '-';
                     }
                     echo rtrim($show_sub_categories,',');
                  @endphp
               </span>
            </div>
         </div>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Fourth Level Category</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span> 
                  @php
                     $data = [];
                     $show_sub_categories = "";
                     if(isset($fourth_sub_categories_arr) && !empty($fourth_sub_categories_arr))
                     {
                        foreach($fourth_sub_categories_arr as $four_sub_category_arr)
                        {
                           if(!in_array($four_sub_category_arr[0]['fourth_sub_category_name'],$data))
                           {
                               array_push($data,$four_sub_category_arr[0]['fourth_sub_category_name']);

                              if(sizeof($four_sub_category_arr)>0)
                              {
                                 $show_sub_categories .= $four_sub_category_arr[0]['fourth_sub_category_name'].',';
                              }
                           }
                        }
                     }
                     else
                     {
                        $show_sub_categories = '-';
                     }
                     echo rtrim($show_sub_categories,',');
                  @endphp
               </span>
            </div>
         </div>
         
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Brand Name</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>{{$product_arr['brand_details']['brand_name'] or '-'}}</span>
            </div>
         </div>

         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Case Quantity</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               {{-- <span>{{ $product_arr['available_qty'] or '-'}}</span> --}}
               <span>{{ isset($product_arr['case_quantity']) && $product_arr['case_quantity']!="" ?$product_arr['case_quantity']:'-'}}</span>
            </div>
         </div>
          <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Restock Days</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>{{ isset($product_arr['restock_days']) && $product_arr['restock_days']!="" ?$product_arr['restock_days']:'-'}}</span>
            </div>
         </div>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Price</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>${{ isset($product_arr['unit_wholsale_price'])?num_format($product_arr['unit_wholsale_price']) : '-'}}</span>
            </div>
         </div>
         {{-- <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Price (Retail)</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>${{ isset($product_arr['retail_price'])?num_format($product_arr['retail_price']) : '-'}}</span>
            </div>
         </div> --}}
       {{--   <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Discount</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>{{ isset($product_arr['product_discount'])?'$'.num_format($product_arr['product_discount']) : '-'}}</span>
            </div>
         </div> --}}

        {{--  <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Discount Min Amount</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>{{ isset($product_arr['product_dis_min_amt'])?'$'.num_format($product_arr['product_dis_min_amt']) : '-'}}</span>
            </div>
         </div> --}}

         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Ingredients</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>                  
                  @if($product_arr['ingrediants']!='')              
                    <div class="truncate"> {{$product_arr['ingrediants'] or '-' }}</div>
                  @else
                    <div class="truncate">-</div>
                  @endif
               </span>
            </div>
         </div>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">About This Product</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               {{-- @if(isset($product_arr['description']) && strlen($product_arr['description']) > 300  && $product_arr['description']!='')
               <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="300">
               <span>
                  <div class="truncate"> {!! $product_arr['description'] or '-' !!}</div>
               </span>
               @else
               <span> -</span>
               @endif --}}
               @if(isset($product_arr['description']) && strlen($product_arr['description']) > 300  && $product_arr['description']!='')
                        @php
                        $desc_html = html_entity_decode($product_arr['description']);
                        $desc = str_limit($desc_html,300);
                        @endphp               
                       <p class="prod-desc"> {!! $desc !!}
                          <a class="readmorebtn" message="{{$product_arr['description']}}" style="cursor:pointer">
                              <b>Read more</b>
                          </a>
                      </p>                  
                    @else
                       
                        {!!$product_arr['description']!!}
                    @endif
            </div>
         </div>

        <!--  <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Is Tester Available</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>@if($product_arr['is_tester_available']==1) Yes @else No @endif</span>
            </div>
         </div> -->

         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Is Best Seller</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span>@if($product_arr['is_best_seller']==1) Yes @else No @endif</span>
            </div>
         </div>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Status</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               @if(isset($product_arr['is_active']) && $product_arr['is_active']==0)
               <span>Block</span>
               @else
               <span>Active</span>
               @endif                  
            </div>
         </div>

          {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Product Discount Type</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                @if(isset($product_arr['prodduct_dis_type']))
                  @if($product_arr['prodduct_dis_type']==1)
                    <span>% off</span>
                  @elseif($product_arr['prodduct_dis_type']==2)
                    <span>$ off</span>
                  @else
                  <span> - </span>
                  @endif
                @else
                  <span> - </span>
                @endif     
              </div>
            </div> --}}

            {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Product Discount</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                @if($product_arr['prodduct_dis_type']==1)

                  @if($product_arr['product_discount']!='')
                    <span>{{ isset($product_arr['product_discount'])?$product_arr['product_discount'] : '0'}}%</span>
                  @else
                    <span>-</span>
                   @endif
                
                @elseif($product_arr['prodduct_dis_type']==2) 

                  @if($product_arr['product_discount']!='')
                    <span>${{ isset($product_arr['product_discount'])?num_format($product_arr['product_discount']) : '0'}}</span>
                  @else
                    <span>-</span>
                  @endif
                  @else
                <span>-</span>
                @endif
              </div>
            </div> --}}

            {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Min Order Amount to get product discount </b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                @if(isset($product_arr['product_dis_min_amt']) && $product_arr['product_dis_min_amt'] != "") 

              

                  @if($product_arr['prodduct_dis_type']==1)

                    <span>${{ isset($product_arr['product_dis_min_amt'])?num_format($product_arr['product_dis_min_amt']) : '0'}} - {{ isset($product_arr['product_discount'])?$product_arr['product_discount'].'%' : '-'}}</span>

                  @elseif($product_arr['prodduct_dis_type']==2)

                    <span>${{ isset($product_arr['product_dis_min_amt'])?num_format($product_arr['product_dis_min_amt']) : '0'}} - ${{ isset($product_arr['product_discount'])?$product_arr['product_discount'] : '-'}}</span>

                  @else
                    <span>-</span>

                  @endif
                @else

                <span> - </span>
              @endif                
              </div>
            </div> --}}

   

         {{-- <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Shipping Charges</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               @if(isset($product_arr['shipping_charges']) && $product_arr['shipping_charges'] != '')
               <span>${{ isset($product_arr['shipping_charges']) ?num_format($product_arr['shipping_charges']) : '-'}}</span>
               @else
               <span> - </span>
               @endif
            </div>
         </div> --}}
         {{-- <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Shipping Type</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               @if(isset($product_arr['shipping_type']) && $product_arr['shipping_type'] != "0")
               @if($product_arr['shipping_type']==1)
               <span>Free Shipping</span>
               @elseif($product_arr['shipping_type']==2)
               <span>% off</span>
               @elseif($product_arr['shipping_type']==3)
               <span>$ off</span>
               @endif
               @else
               <span> - </span>
               @endif                 
            </div>
         </div> --}}
         {{-- <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Min Order Amount to get shipping discount </span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               @if(isset($product_arr['minimum_amount_off']) && $product_arr['minimum_amount_off'] != "")
               @if($product_arr['shipping_type']==1)
               <span>${{ isset($product_arr['minimum_amount_off'])?num_format($product_arr['minimum_amount_off']) : '-'}}</span>
               @elseif($product_arr['shipping_type']==2)
               <span>${{ isset($product_arr['minimum_amount_off'])?num_format($product_arr['minimum_amount_off']) : 'N/A'}} - {{ isset($product_arr['off_type_amount'])?$product_arr['off_type_amount'].'%' : '-'}}</span>
               @elseif($product_arr['shipping_type']==3)
               <span>${{ isset($product_arr['minimum_amount_off'])?num_format($product_arr['minimum_amount_off']) : 'N/A'}} - ${{ isset($product_arr['off_type_amount'])?$product_arr['off_type_amount'] : '-'}}</span>
               @else
               <span>-</span>
               @endif
               @else
               <span> - </span>
               @endif                
            </div>
         </div> --}}
      </div>
   </div>
   <div class="col-sm-6">
      <div class="white-box">
         <label>
            <h3>Additional Images</h3>
         </label>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Image</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               @php
                  $product_img_path = ""; 
                  $image_name = (isset($product_arr['product_images']['product_image']))? $product_arr['product_images']['product_image']:"";
                  $image_type = "product";
                  $is_resize = 0; 
                  $product_img_path = imagePath($image_name, $image_type, $is_resize);              
               @endphp               
               <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}">
            </div>
         </div>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Packaging Image</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               @php
                  $product_img_path = ""; 
                  $image_name = (isset($product_arr['product_images']['packaging_image']))? $product_arr['product_images']['packaging_image']:"";
                  $image_type = "product";
                  $is_resize = 0; 
                  $product_img_path = imagePath($image_name, $image_type, $is_resize);              
               @endphp                
               <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}">
               
            </div>
         </div>
         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Lifestyle Image</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               @php
                  $product_img_path = ""; 
                  $image_name = (isset($product_arr['product_images']['lifestyle_image']))? $product_arr['product_images']['lifestyle_image']:"";
                  $image_type = "product";
                  $is_resize = 0; 
                  $product_img_path = imagePath($image_name, $image_type, $is_resize);              
               @endphp 
               
               <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}">
               
            </div>
         </div>
      </div>
      
   
    <div class="white-box ">
          <label><h3>Product Styles And Dimensions</h3></label>
          <div class="table-responsive">
            <table class="table table-striped table-bordered view-porifile-table">
              <tr>
                <th>Product Images</th>
               <!--  <th>
                  @if(isset($product_arr['product_details'][0]['option_type']))
                    @if($product_arr['product_details'][0]['option_type']==0)
                          Color
                         @elseif($product_arr['product_details'][0]['option_type']==1)
                          Scent
                         @elseif($product_arr['product_details'][0]['option_type']==2)
                          Size
                         @elseif($product_arr['product_details'][0]['option_type']==3)
                          Material
                    @endif   
                  @endif   
                </th> -->
                <th>SKU Number</th>
                <th>Min Quantity</th>
                <!-- <th>Weight</th>
                <th>Length</th>
                <th>Width</th>
                <th>Height</th> -->
                <th>Inventory</th>
                <th >Size & Inventory</th>
                <th>Description</th>
                
              </tr>

              @if(isset($product_arr['product_details']) && count($product_arr['product_details'])>0)
                @foreach($product_arr['product_details'] as $product_detail)
                  <tr>
                    <td> 
                     @php
                     
                        $product_img_path = ""; 
                        $image_name = (isset($product_detail['image']))? $product_detail['image']:"";
                        $image_type = "product";
                        $is_resize = 0; 
                        $product_img_path = imagePath($image_name, $image_type, $is_resize);              
                     @endphp 
                   
                     <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}">
               

                    </td>                         
                    <!-- <td>{{ $product_detail['option'] or '-'}}</td>    -->

                    <td>{{ $product_detail['sku'] or '-'}}</td>  
                    <td>{{ $product_detail['product_min_qty'] or '-'}}</td>               
                    <!-- <td> {{ $product_detail['weight'] or '-'}}</td>
                    <td> {{ $product_detail['length'] or '-'}}</td>
                    <td> {{ $product_detail['width'] or '-'}}</td>                         
                    <td> {{ $product_detail['height'] or '-'}}</td> -->
                    <td> {{ $product_detail['inventory_details']['quantity'] or '-'}}</td>
                   {{--  <td>
                     @if(isset($product_detail['sku_product_description']) && strlen($product_detail['sku_product_description']) > 300)
                        <!-- {{ $product_detail['sku_product_description'] or ''}} -->
                        <span>
                           <div class="truncate"> 
                              {!! $product_detail['sku_product_description'] or '-' !!}
                           </div>
                        </span>
                     @else
                        <span> {!! $product_detail['sku_product_description'] or '-' !!}</span>
                     @endif
                  </td> --}}
                  <td><a onclick="openSizeInventory('{{ $product_detail['sku']}}','{{$product_detail['color'] }}' )" style="cursor: pointer;"  id="get-size-inventory">Details</a></td>
                  <td>{{-- <div class="truncate">{!! $quote['product_details']['description'] or 'N/A'!!}</div> --}}

                    @if(isset($product_detail['sku_product_description']) && strlen($product_detail['sku_product_description']) > 100 && $product_detail['sku_product_description']!='' )   
                        @php
                        $desc_html = html_entity_decode($product_detail['sku_product_description']);
                        $desc = str_limit($desc_html,100);
                        @endphp               
                       <p class="prod-desc"> {!! $desc !!}
                          <a class="readmorebtn" message="{{$product_detail['sku_product_description']}}" style="cursor:pointer">
                              <b>Read more</b>
                          </a>
                      </p>                  
                    @else
                       
                        {!!$product_detail['sku_product_description']!!}
                    @endif
                  </td>
                  
                  {{-- <td><a href="{{$module_url_path}}/size_inventory/{{ $product_detail['sku']}}" onclick="openSizeInventory(this)" class="eye-button" id="get-size-inventory"><i class="fa fa-eye" aria-hidden="true"></i></a></td> --}}
                  </tr>
                   @if(isset($product_detail['product_multiple_images']) && count($product_detail['product_multiple_images'])>0)
                  <tr class="tr-multiple-images">
                    <td><b>Multiple Images</b></td>
                    <td colspan="4" class="summmarytdsprice">

                        
                        <div class="col-lg-3 col-md-3 col-sm-3 style-box">

                        @foreach($product_detail['product_multiple_images'] as $product_mul_detail)
                          @php
                          $default_mul_img = url('/').'/storage/app/'.$product_mul_detail['product_image'];
                          @endphp

                          
                         {{--  <img src="{{$default_mul_img}}" height=""> --}}
                          <img class="zoom-img" height="100px" width="100px" src="{{$default_mul_img}}">
                        
                        @endforeach
                      </div>
                    </td>

                  </tr>
                    @endif
                @endforeach
              @else
                <tr>
                  <td colspan="6">No Data Found</td>
                </tr>
              @endif
            </table>
          </div>
          </div>
          </div>
       
   <div class="col-md-12">
      <div class="form-group row">
         <div class="col-md-12 ">
            <div class="text-center">
               <a class="btn btn-inverse waves-effect waves-light pull-left" href="{{$module_url_path}}"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
         </div>
      </div>
   </div>
</div>
<!-- END Main Content -->
@include('product_description_popup')
<!-- Get a size & inventory Modal -->
<div class="modal fade vendor-Modal" id="size_inventory" tabindex="-1" role="dialog" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered titlecommission modal-lg" role="document">
     <div class="modal-content">
       <div class="modal-header">
         <h3 class="modal-title" id="VendorPaymentModalLongTitle">Color & Inventory</h3>
         
         <button type="button" class="close" data-dismiss="modal" aria-label="Close">
           <span aria-hidden="true">&times;</span>
         </button>
       </div>
       
       <div class="modal-body" id="size_table">
         
       </div>
     </div>
   </div>
 </div>
<script type="text/javascript">
   function openSizeInventory(sku,color){
     
   red_url = "{{$module_url_path}}/size_inventory";
   var csrf_token = "{{ csrf_token()}}";
   $.ajax({
            url: red_url,
            type:"POST",
            data:{sku:sku,color:color,_token:csrf_token},         
            dataType:'json',
            
            success:function(response)
            {
                  $("#size_table").html(response.html);
                  $("#size_inventory").modal('show');
            }
         });
}
</script>
@stop