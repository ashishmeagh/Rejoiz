@extends('admin.layout.master')
@section('main_content')
<style>
.row{
     padding-bottom: 20px;
  }
 .tr-multiple-images {
        border-bottom: 1px solid #cccc;

   }
   #showmessage p {
    font-weight: normal !important;
   }

</style>
<!-- Page Content -->
<div id="page-wrapper" class="product_view_page_wrapper">
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
       <!-- /.col-lg-12 -->
    </div>

    <div class="row">

      @include('admin.layout._operation_status')
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
       <div class="col-sm-12 col-xs-12 mb-2">
          (<b><span class="show_star">*</span></b>) <i>Indicates that fields have been updated by the vendor</i>
       </div>
      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 left_box common_box">
        <div class="white-box">
          <h3>Product Details</h3>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>
                  @if(isset($product_arr['product_image']) && in_array('product_image',$updatedColumnsArray))
                <b><span class="show_star">*</span></b>
                @endif
              Image</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                @if(isset($product_arr['product_image']) && $product_arr['product_image']!="")
                  @php
                  // dd($product_arr);
                    $product_img_path = "";
                    $image_name = $product_arr['product_image'];
                    $image_type = "product";
                    $is_resize = 0;
                    $product_img_path = imagePath($image_name, $image_type, $is_resize);
                  @endphp
                   <span><img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}"
                    @if(isset($product_arr['product_image']) && in_array('product_image',$updatedColumnsArray)) title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                    style="cursor: pointer"

                     @endif
                    ></span>
                @else
                  <span><img class="zoom-img" height="100px" width="100px" src="{{ url('/assets/images/default_images/default-product.png')}}"></span>

                @endif
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">
                @if(isset($product_arr['product_name']) && in_array('product_name',$updatedColumnsArray))
                <b><span class="show_star">*</span></b>
                @endif
                <b>Name</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span
                @if(isset($product_arr['product_name']) && in_array('product_name',$updatedColumnsArray)) title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"

                @endif
                >{{ $product_arr['product_name'] or 'N/A'}}</span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">
                @if(isset($product_arr['category_id']) && in_array('category_id',$updatedColumnsArray))
                <b> <span class="show_star">*</span></b>
                @endif
                <b>Category</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span
                @if(isset($product_arr['category_id']) && in_array('category_id',$updatedColumnsArray)) title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                style="cursor: pointer"

                @endif
                >{{ $product_arr['category_details']['category_name'] or '-' }}</span>
              </div>
            </div>
             <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>
                @if(!empty($updatedColumnsSubCatArray))
                 <span class="show_star">*</span>
              @endif
              Subcategory</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
                  @php 
                  $i=1; 
                  $data = [];
                   @endphp
                    @if(isset($product_arr['product_sub_categories']) && sizeof($product_arr['product_sub_categories'])>0)
                      @foreach($product_arr['product_sub_categories'] as $sub_category)
                      @php
                      if(!in_array($sub_category['subcategory_details']['subcategory_name'],$data))
                          {
                            array_push($data,$sub_category['subcategory_details']['subcategory_name']);
                      @endphp
                        @if(in_array($sub_category['subcategory_details']['id'],$updatedColumnsSubCatArray))
                          <span
                            title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                            style="cursor: pointer"
                          ><span class="show_star">*</span>
                          @endif
                        @php

                            if($i==count($product_arr['product_sub_categories']))
                            {
                              if(isset($sub_category['subcategory_details']['subcategory_name']) && $sub_category['subcategory_details']['subcategory_name']!="")
                              {
                                rtrim($sub_category['subcategory_details']['subcategory_name'],',');
                                echo $sub_category['subcategory_details']['subcategory_name'];
                              }
                              else
                              {
                                echo"-";
                              }
                            }
                            else
                            {
                              echo $sub_category['subcategory_details']['subcategory_name'].',';
                            }
                          }
                         $i++;
                        @endphp
                        @if(in_array($sub_category['subcategory_details']['id'],$updatedColumnsSubCatArray))
                          </span>
                          @endif
                      @endforeach
                    @else
                      @php echo"-"; @endphp
                  @endif
                </span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>
                @if(!empty($updatedColumnsThirdSubCatArray))
                 <span class="show_star">*</span>
              @endif
              Third Level Category</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
                  @php 
                  $i=1; 
                  $data = [];
                   @endphp
                    @if(isset($product_arr['product_sub_categories']) && sizeof($product_arr['product_sub_categories'])>0)
                      @foreach($product_arr['product_sub_categories'] as $sub_category)
                      @php
                      if(!in_array($sub_category['third_subcategory_details']['third_sub_category_name'],$data))
                          {
                            array_push($data,$sub_category['third_subcategory_details']['third_sub_category_name']);
                      @endphp
                        @if(in_array($sub_category['third_subcategory_details']['id'],$updatedColumnsThirdSubCatArray))
                          <span
                            title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                            style="cursor: pointer"
                          ><span class="show_star">*</span>
                          @endif
                        @php

                            if($i==count($product_arr['product_sub_categories']))
                            {
                              if(isset($sub_category['third_subcategory_details']['third_sub_category_name']) && $sub_category['third_subcategory_details']['third_sub_category_name']!="")
                              {
                                rtrim($sub_category['third_subcategory_details']['third_sub_category_name'],',');
                                echo $sub_category['third_subcategory_details']['third_sub_category_name'];
                              }
                              else
                              {
                                echo"-";
                              }
                            }
                            else
                            {
                              echo $sub_category['third_subcategory_details']['third_sub_category_name'].',';
                            }
                          }
                         $i++;
                        @endphp
                        @if(in_array($sub_category['third_subcategory_details']['id'],$updatedColumnsThirdSubCatArray))
                          </span>
                          @endif
                      @endforeach
                    @else
                      @php echo"-"; @endphp
                  @endif
                </span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>
                @if(!empty($updatedColumnsFourthSubCatArray))
                 <span class="show_star">*</span>
              @endif
              Fourth Level Category</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>
                  @php 
                  $i=1; 
                  $data = [];
                   @endphp
                    @if(isset($product_arr['product_sub_categories']) && sizeof($product_arr['product_sub_categories'])>0)
                      @foreach($product_arr['product_sub_categories'] as $sub_category)
                      @php
                      if(!in_array($sub_category['fourth_subcategory_details']['fourth_sub_category_name'],$data))
                          {
                            array_push($data,$sub_category['fourth_subcategory_details']['fourth_sub_category_name']);
                      @endphp
                        @if(in_array($sub_category['fourth_subcategory_details']['id'],$updatedColumnsFourthSubCatArray))
                          <span
                            title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                            style="cursor: pointer"
                          ><span class="show_star">*</span>
                          @endif
                        @php

                            if($i==count($product_arr['product_sub_categories']))
                            {
                              if(isset($sub_category['fourth_subcategory_details']['fourth_sub_category_name']) && $sub_category['fourth_subcategory_details']['fourth_sub_category_name']!="")
                              {
                                rtrim($sub_category['fourth_subcategory_details']['fourth_sub_category_name'],',');
                                echo $sub_category['fourth_subcategory_details']['fourth_sub_category_name'];
                              }
                              else
                              {
                                echo"-";
                              }
                            }
                            else
                            {
                              echo $sub_category['fourth_subcategory_details']['fourth_sub_category_name'].',';
                            }
                          }
                         $i++;
                        @endphp
                        @if(in_array($sub_category['fourth_subcategory_details']['id'],$updatedColumnsFourthSubCatArray))
                          </span>
                          @endif
                      @endforeach
                    @else
                      @php echo"-"; @endphp
                  @endif
                </span>
              </div>
            </div>


         {{-- <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Second Sub Category</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span> 
                  @php
                  $show_sub_categories = "";
                     if(isset($product_arr['product_sub_categories']) && sizeof($product_arr['product_sub_categories'])>0)
                     {
                        foreach($product_arr['product_sub_categories'] as $sub_category)
                        {
                           $show_sub_categories .= $sub_category['third_subcategory_details']['third_sub_category_name'].',';
                        }
                     }
                     echo rtrim($show_sub_categories,',');
                  @endphp
               </span>
            </div>
         </div>

         <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><span class="semibold-mkr">Product Third Sub Category</span></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
               <span> 
                  @php
                  $show_sub_categories = "";
                     if(isset($product_arr['product_sub_categories']) && sizeof($product_arr['product_sub_categories'])>0)
                     {
                        foreach($product_arr['product_sub_categories'] as $sub_category)
                        {
                           $show_sub_categories .= $sub_category['fourth_subcategory_details']['fourth_sub_category_name'].',';
                        }
                     }
                     echo rtrim($show_sub_categories,',');
                  @endphp
               </span>
            </div>
         </div> --}}
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Vendor</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span>{{$product_arr['maker_details']['company_name'] or '-'}}</span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>
                  @if(isset($product_arr['brand']) && in_array('brand',$updatedColumnsArray))
                <span class="show_star">*</span>
                @endif
                Brand</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span
                @if(isset($product_arr['brand']) && in_array('brand',$updatedColumnsArray))
                title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"

                @endif
                >{{$product_arr['brand_details']['brand_name'] or '-'}}</span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>
                @if(isset($product_arr['available_qty']) && in_array('available_qty',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
                Case Quantity</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span  @if(isset($product_arr['available_qty']) && in_array('available_qty',$updatedColumnsArray))
                title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"

                @endif>{{ $product_arr['available_qty'] or '0'}}</span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>
                @if(isset($product_arr['restock_days']) && in_array('restock_days',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
                Restock Days</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span  @if(isset($product_arr['restock_days']) && in_array('restock_days',$updatedColumnsArray))
                title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"

                @endif>{{ $product_arr['restock_days'] or '0'}}</span>
              </div>
            </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>
                @if(isset($product_arr['unit_wholsale_price']) && in_array('unit_wholsale_price',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
                Price</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span  @if(isset($product_arr['unit_wholsale_price']) && in_array('unit_wholsale_price',$updatedColumnsArray))
                 title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                 style="cursor: pointer"

                @endif>${{ isset($product_arr['unit_wholsale_price'])?num_format($product_arr['unit_wholsale_price']) : '0'}}</span>
              </div>
            </div>

            {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>
                  @if(isset($product_arr['retail_price']) && in_array('retail_price',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
                Price (Retail)</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                <span @if(isset($product_arr['retail_price']) && in_array('retail_price',$updatedColumnsArray)) title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"

                @endif>${{ isset($product_arr['retail_price'])?num_format($product_arr['retail_price']) : '0'}}</span>
              </div>
            </div> --}}

          <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4">

              <b>
                @if(isset($product_arr['ingrediants']) && in_array('ingrediants',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
              Product Ingredients</b></label>

            <div class="col-sm-12 col-md-12 col-lg-8">
              <span
               @if(isset($product_arr['ingrediants']) && in_array('ingrediants',$updatedColumnsArray))
                title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"

                @endif
               >
               @if($product_arr['ingrediants']!='')
                <div class="truncate"> {{$product_arr['ingrediants'] or '-' }}</div>
               @else
              <div class="truncate">-</div>
               @endif
               </span>
            </div>
         </div>
            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>
                @if(isset($product_arr['description']) && in_array('description',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
                Description</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">
                {{-- @if(isset($product_arr['description']) && strlen($product_arr['description']) > 300 && $product_arr['description']!='' )

                  <input type="hidden" name="more_less_text_value" id="more_less_text_value" value="300">

                  <span @if(isset($product_arr['description']) && in_array('description',$updatedColumnsArray))
                 title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                 style="cursor: pointer"

                @endif><div class="truncate"> {!! $product_arr['description'] or '-' !!}</div></span>

                @else

                  <span @if(isset($product_arr['description']) && in_array('description',$updatedColumnsArray))
                 title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                 style="cursor: pointer"

                @endif>{{ strip_tags($product_arr['description'])}}</span>

                @endif --}}
                    @if(isset($product_arr['description']) && strlen($product_arr['description']) > 300 && $product_arr['description']!='' )   

                        @php
                        /*$desc_html = html_entity_decode($product_arr['description']);
                        $desc = str_limit($desc_html,300);*/
                        $desc_html = $desc = "";
                        $desc_html = ($product_arr['description']);
                        $desc = substr(strip_tags( $desc_html), 0, 70);
                        @endphp               
                       <p class="prod-desc"> {!!$desc!!}
                        <br>
                          <a class="readmorebtn" message="{{$product_arr['description']}}" style="cursor:pointer">
                              <b>Read more</b>
                          </a>
                      </p>
                  
                    @else
                       
                       {!!$product_arr['description']!!}
                    @endif

              </div>
            </div>

            <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>Status</b></label>

              <div class="col-sm-12 col-md-12 col-lg-8">
                @if(isset($product_arr['is_active']) && $product_arr['is_active']==0)
                  <span>Block</span>
                @else
                  <span>Active</span>
                @endif
              </div>

            </div>
              {{-- prodduct_dis_type product_dis_min_amt --}}
            {{-- {{dd($product_arr)}} --}}


           {{--  <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b> @if(isset($product_arr['prodduct_dis_type']) && in_array('product_discount_type',$updatedColumnsArray))
                <span class="show_star">*</span>
                @endif

              Product Discount Type</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">

                @if(isset($product_arr['prodduct_dis_type']) && in_array('product_discount_type',$updatedColumnsArray))
                <span
                 title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                 style="cursor: pointer"

                >
                @endif

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

                @if(isset($product_arr['product_discount_type']) && in_array('product_discount_type',$updatedColumnsArray))
                </span>
                @endif
              </div>
            </div> --}}

            {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4"><b>
                @if(isset($product_arr['product_discount']) && in_array('product_discount',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
              Product Discount</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">

                @if(isset($product_arr['product_discount']) && in_array('product_discount',$updatedColumnsArray))
                <span title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"
                 >
                @endif
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

                 @if(isset($product_arr['product_discount']) && in_array('product_discount',$updatedColumnsArray))
                </span>
                @endif
              </div>
            </div> --}}

            {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b> @if(isset($product_arr['product_dis_min_amt']) && in_array('product_minimum_amount_off',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif

              Min Order Amount to get product discount</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">

                 @if(isset($product_arr['product_dis_min_amt']) && in_array('product_minimum_amount_off',$updatedColumnsArray))
                  <span title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"
                 >
                @endif



                @if(isset($product_arr['product_dis_min_amt']) && $product_arr['product_dis_min_amt'] != "")
                  @if($product_arr['prodduct_dis_type']==1)

                    <span>${{ isset($product_arr['product_dis_min_amt'])?num_format($product_arr['product_dis_min_amt']) : '0'}} - {{ isset($product_arr['product_discount'])?$product_arr['product_discount'].'%' : 'N/A'}}</span>

                  @elseif($product_arr['prodduct_dis_type']==2)

                    <span>${{ isset($product_arr['product_dis_min_amt'])?num_format($product_arr['product_dis_min_amt']) : '0'}} - ${{ isset($product_arr['product_discount'])?$product_arr['product_discount'] : 'N/A'}}</span>

                  @else
                    <span>-</span>

                  @endif
                @else

                <span> - </span>
              @endif
              @if(isset($product_arr['product_dis_min_amt']) && in_array('product_minimum_amount_off',$updatedColumnsArray))
                  </span>
                @endif
              </div>
            </div> --}}

            {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>
                @if(isset($product_arr['shipping_charges']) && in_array('shipping_charges',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
                Shipping Charges</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">

                 @if(isset($product_arr['shipping_charges']) && in_array('shipping_charges',$updatedColumnsArray))
                 <span title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"
                 >
                @endif


                @if(isset($product_arr['shipping_charges']) && $product_arr['shipping_charges'] != '')
                 <span>${{ isset($product_arr['shipping_charges']) ?num_format($product_arr['shipping_charges']) : '-'}}</span>
                @else
                  <span> - </span>
                @endif


                @if(isset($product_arr['shipping_charges']) && in_array('shipping_charges',$updatedColumnsArray))
                 </span>
                @endif
              </div>
            </div> --}}
            {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>
                  @if(isset($product_arr['shipping_type']) && in_array('shipping_type',$updatedColumnsArray))
                 <span class="show_star">*</span>
                @endif
                Shipping Type</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">

                @if(isset($product_arr['shipping_type']) && in_array('shipping_type',$updatedColumnsArray))
                 <span title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"
                 >
                @endif

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


                @if(isset($product_arr['shipping_type']) && in_array('shipping_type',$updatedColumnsArray))
                 </span>
                @endif
              </div>
            </div> --}}
            {{-- <div class="row">
              <label class="col-sm-12 col-md-12 col-lg-4">

                <b>

                  @if(isset($product_arr['minimum_amount_off']) && in_array('shipping_minimum_amount_off',$updatedColumnsArray))
                  <span class="show_star">*</span>
                @endif

                Min Order Amount to get shipping discount</b></label>
              <div class="col-sm-12 col-md-12 col-lg-8">

                @if(isset($product_arr['minimum_amount_off']) && in_array('shipping_minimum_amount_off',$updatedColumnsArray))
                  <span title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"
                style="cursor: pointer"
                 >
                @endif


                @if(isset($product_arr['minimum_amount_off']) && $product_arr['minimum_amount_off'] != "")

                  @if($product_arr['shipping_type']==1)

                    <span>${{ isset($product_arr['minimum_amount_off'])?num_format($product_arr['minimum_amount_off']) : '0'}}</span>

                  @elseif($product_arr['shipping_type']==2)

                    <span>${{ isset($product_arr['minimum_amount_off'])?num_format($product_arr['minimum_amount_off']) : '0'}} - {{ isset($product_arr['off_type_amount'])?$product_arr['off_type_amount'].'%' : '-'}}</span>

                  @elseif($product_arr['shipping_type']==3)

                    <span>${{ isset($product_arr['minimum_amount_off'])?num_format($product_arr['minimum_amount_off']) : '0'}} - ${{ isset($product_arr['off_type_amount'])?$product_arr['off_type_amount'] : '-'}}</span>

                  @else
                    <span>-</span>

                  @endif
                @else

                <span> - </span>
              @endif
              @if(isset($product_arr['minimum_amount_off']) && in_array('shipping_minimum_amount_off',$updatedColumnsArray))
                </span>
                @endif
              </div>
            </div> --}}
        </div>
      </div>

      <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6 right_box common_box">
        <div class="white-box">
          <label><h3>Additional Images</h3></label>
          <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><b>
                    @php
                         if(isset($product_arr['product_images']['product_image']) && in_array('product_main_image',$updatedColumnsArray)){
                         @endphp
                         <span class="show_star">*</span>
                         @php
                        }

                    @endphp
            Product Image</b></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
              <span>
                @if(isset($product_arr['product_images']['product_image']) && $product_arr['product_images']['product_image']!="")
                  @php
                    $product_img_path = "";
                    $image_name = $product_arr['product_images']['product_image'];
                    $image_type = "product";
                    $is_resize = 0;
                    $product_img_path = imagePath($image_name, $image_type, $is_resize);
                  @endphp
                  <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}"
                    @php
                         if(isset($product_arr['product_images']['product_image']) && in_array('product_main_image',$updatedColumnsArray)){
                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"

                         @php
                        }

                    @endphp
                  >
                @else
                 <img class="zoom-img" height="100px" width="100px" src="{{ url('/assets/images/default_images/default-product.png')}}"


                 >

                @endif
              </span>
            </div>
          </div>

          <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><b>
                      @php
                         if(isset($product_arr['product_images']['packaging_image']) && in_array('packaging_image',$updatedColumnsArray)){
                         @endphp
                         <span class="show_star">*</span>
                         @php
                        }

                    @endphp
            Packaging Image</b></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
              <span>
                @if(isset($product_arr['product_images']['packaging_image']) && $product_arr['product_images']['packaging_image']!="")
                  @php
                    $product_img_path = "";
                    $image_name = $product_arr['product_images']['packaging_image'];
                    $image_type = "product";
                    $is_resize = 0;
                    $product_img_path = imagePath($image_name, $image_type, $is_resize);
                  @endphp
                  <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}"
                    @php
                         if(isset($product_arr['product_images']['packaging_image']) && in_array('packaging_image',$updatedColumnsArray)){
                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"
                         @php
                        }

                    @endphp
                  >
                @else
                  <img class="zoom-img" height="100px" width="100px" src="{{ url('/assets/images/default_images/default-product.png')}}">
                @endif

              </span>
            </div>
          </div>

          <div class="row">
            <label class="col-sm-12 col-md-12 col-lg-4"><b>
               @php
                         if(isset($product_arr['product_images']['lifestyle_image']) && in_array('lifestyle_image',$updatedColumnsArray)){
                         @endphp
                         <span class="show_star">*</span>
                         @php
                        }

                    @endphp

            Lifestyle Image</b></label>
            <div class="col-sm-12 col-md-12 col-lg-8">
              <span>

                @if(isset($product_arr['product_images']['lifestyle_image']) && $product_arr['product_images']['lifestyle_image']!="")
                  @php
                    $product_img_path = "";
                    $image_name = $product_arr['product_images']['lifestyle_image'];
                    $image_type = "product";
                    $is_resize = 0;
                    $product_img_path = imagePath($image_name, $image_type, $is_resize);
                  @endphp
                  <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}"

                   @php
                         if(isset($product_arr['product_images']['lifestyle_image']) && in_array('lifestyle_image',$updatedColumnsArray)){
                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"
                         @php
                        }

                    @endphp

                  >
                @else
                  <img class="zoom-img" height="100px" width="100px" src="{{ url('/assets/images/default_images/default-product.png')}}">
                @endif
              </span>
            </div>
          </div>
        </div>

        {{-- {{dd($product_arr)}} --}}



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
                      @if(isset($product_detail['image']) && $product_detail['image']!="")
                          @php
                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['image']) && in_array('product_detail_image',$updatedColumnsDetArray[$product_detail['id']])){
                         @endphp
                         <b> <span class="show_star">*</span></b>
                         @php
                        }
                      }
                    @endphp
                    @php
                      $product_img_path = "";
                      $image_name = $product_detail['image'];
                      $image_type = "product";
                      $is_resize = 0;
                      $product_img_path = imagePath($image_name, $image_type, $is_resize);
                    @endphp
                        <img class="zoom-img" height="100px" width="100px" src="{{ $product_img_path }}"

                         @php
                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['image']) && in_array('product_detail_image',$updatedColumnsDetArray[$product_detail['id']])){
                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"

                         @php
                        }
                      }
                    @endphp


                        >

                      @else

                       <img class="zoom-img" height="100px" width="100px" src="{{ url('/assets/images/default_images/default-product.png')}}">

                      @endif

                    </td>
                    <!-- <td>{{ $product_detail['option'] or '-'}}</td>    -->

                    <td
                    @php
                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['sku']) && in_array('sku',$updatedColumnsDetArray[$product_detail['id']])){
                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"

                         @php
                        }
                      }
                    @endphp
                    >

                    @php

                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['sku']) && in_array('sku',$updatedColumnsDetArray[$product_detail['id']])){

                         @endphp
                          <span class="show_star">*</span>
                         @php
                        }
                      }
                        @endphp

                    {{ $product_detail['sku'] or '-'}}</td>
                    <td @php
                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['product_min_qty']) && in_array('product_min_qty',$updatedColumnsDetArray[$product_detail['id']])){
                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"

                         @php
                        }
                      }
                    @endphp>

                     @php

                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['product_min_qty']) && in_array('product_min_qty',$updatedColumnsDetArray[$product_detail['id']])){

                         @endphp
                          <span class="show_star">*</span>
                         @php
                        }
                      }
                        @endphp


                    {{ $product_detail['product_min_qty'] or '-'}}</td>
                    <!-- <td> {{ $product_detail['weight'] or '-'}}</td>
                    <td> {{ $product_detail['length'] or '-'}}</td>
                    <td> {{ $product_detail['width'] or '-'}}</td>
                    <td> {{ $product_detail['height'] or '-'}}</td> -->
                    <td
                    @php

                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['inventory_details']['quantity']) && in_array('quantity',$updatedColumnsDetArray[$product_detail['id']])){

                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"

                         @php
                        }
                      }
                        @endphp
                    >
                    @php

                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['inventory_details']['quantity']) && in_array('quantity',$updatedColumnsDetArray[$product_detail['id']])){

                         @endphp
                          <span class="show_star">*</span>
                         @php
                        }
                      }
                        @endphp


                     {{ $product_detail['inventory_details']['quantity'] or '-'}}</td>
                     
                     <td><a href="#" onclick="openSizeInventory('{{ $product_detail['sku']}}','{{$product_detail['color'] }}' )"  id="get-size-inventory">Details</a></td>
                    <td>
                     @if(isset($product_detail['sku_product_description']) && strlen($product_detail['sku_product_description']) > 300)
                        <!-- {{ $product_detail['sku_product_description'] or '-'}} -->
                        <span

                        @php

                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['sku_product_description']) && in_array('sku_product_description',$updatedColumnsDetArray[$product_detail['id']])){

                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"

                         @php
                        }
                      }
                        @endphp
                        >
                           <div class="truncate">

                            @php

                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['sku_product_description']) && in_array('sku_product_description',$updatedColumnsDetArray[$product_detail['id']])){

                         @endphp
                         <b> <span class="show_star">*</span></b>
                         @php
                        }
                      }
                        @endphp
                              {{-- {!! $product_detail['sku_product_description'] or '-' !!} --}}

                        <!-- New code for show read more -->
                        @php
                       /* $desc_html = $desc = "";
                        $desc_html = html_entity_decode($product_detail['sku_product_description']);
                        $desc = str_limit($desc_html,200);*/
                        $desc_html = $desc = "";
                        $desc_html = ($product_detail['sku_product_description']);
                        $desc = substr(strip_tags( $desc_html), 0, 70);
                        @endphp               
                         <p class="prod-desc"> {!!$desc!!}
                            <a class="readmorebtn" message="{{$product_detail['sku_product_description']}}" style="cursor:pointer">
                                <b>Read more</b>
                            </a>
                        </p>
                        <!-- Ends --> 

                           </div>
                        </span>
                     @else
                        <span  @php

                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['sku_product_description']) && in_array('sku_product_description',$updatedColumnsDetArray[$product_detail['id']])){

                         @endphp
                         title="Updated By - {{$product_arr['maker_details']['company_name'] or '-'}}"

                         style="cursor: pointer"

                         @php
                        }
                      }
                        @endphp>

                        @php

                        if(array_key_exists($product_detail['id'] ,$updatedColumnsDetArray)){
                         if(isset($product_detail['sku_product_description']) && in_array('sku_product_description',$updatedColumnsDetArray[$product_detail['id']])){

                         @endphp
                         <b> <span class="show_star">*</span></b>
                         @php
                        }
                      }
                        @endphp

                         {!! $product_detail['sku_product_description'] or '-' !!}</span>
                     @endif
                  </td>
                  </tr>


                  @if(isset($product_detail['product_multiple_images']) && count($product_detail['product_multiple_images'])>0)
                  <tr class="tr-multiple-images">
                    <td><b>Multiple Images</b></td>
                    <td colspan="4" class="summmarytdsprice">

                        
                        <div class="col-lg-3 col-md-3 col-sm-3 style-box">

                        @foreach($product_detail['product_multiple_images'] as $product_detail)
                          @php
                          $default_mul_img = url('/').'/storage/app/'.$product_detail['product_image'];
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


      <div class="col-md-12 remark_form" style="padding:10px;">
         <form id="remark_form">
              <input type="hidden" name="csrfToken" id="csrfToken" value="{{csrf_token()}}">
              <label><h3>Product Confirmation</h3></label>
                <div class="form-group textare-new-frm">
                  <label>Remark</label>
                  <textarea row="4" id="remark" name="remark"></textarea>
                  <span id="remar_error"></span>
                </div>
                <div class="btn-cnt">

                </div>

              <input type="hidden" name="product_id" id="product_id" value="{{$product_arr['id'] or 0}}">
            </form>
      </div>
      <div class="col-md-12">
        <div class="form-group row">
          <div class="col-md-12 ">
            <div class="text-center pull-left">
             <a class="btn btn-success btn-outline waves-effect waves-light" href="{{$module_url_path or ''}}"><i class="fa fa-arrow-left"></i> Back</a>

             </div>
             <div class="text-center pull-right">

              @if(isset($product_arr['is_active']) && $product_arr['is_active']=='2')

                <button type="button" class="btn btn-success btn-outline" onclick="saveTinyMceContent();" id="is_approved" value="1">Approve</button>

                <button type="button" class="btn btn-success btn-outline waves-effect waves-light" onclick ="show_reject()" id = "reject_btn">Reject</button>

                <button type="button" class="btn btn-success btn-outline"  id="cancel_btn">Cancel</button>

                <button type="button" class="btn btn-success btn-outline waves-effect waves-light" onclick="saveTinyMceContent();" id="is_rejected" value="0">Reject</button>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>


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

<style type="text/css">
  .bold_label{
    font-weight: bold;
  }
  .show_star{
    color: #0179E0;
  }
</style>
<!-- END Main Content -->

<script type="text/javascript">

var module_url_path = '{{$module_url_path or ''}}';

var token =  $('input[name="csrfToken"]').attr('value');

var product_id = $('#product_id').val();


function saveTinyMceContent()
{
  tinyMCE.triggerSave();
}

$(document).ready(function(){

$(".remark_form").hide();
$("#is_rejected").hide();
$("#cancel_btn").hide();

$('#is_approved').click(function(){

      var status     = 1;
      var product_id = $('#product_id').val();
      var remark     = $('#remark').val();

     /* $('#remark').attr('data-parsley-required','true');

      if($('#remark_form').parsley().validate() == false) return false;*/

      swal({
              title: "Are you sure?",
              text: "You want to approve this product!",
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: '#DD6B55',
              cancelButtonText: "No, cancel it!",
              confirmButtonText: 'Yes, I am sure!',
              closeOnConfirm: false,
              closeOnCancel: true
          },
    function(isConfirm){
    if(isConfirm==true)
    {
    showProcessingOverlay();
      $.ajax({
                url: module_url_path+'/product_confirmation',
                type:"POST",
                data: {status:status,product_id:product_id,remark:remark,_token:"{{ csrf_token() }}"},
                dataType:'json',

                success:function(response)
                { hideProcessingOverlay();
                    if(response.status == 'success')
                    {

                        $('#remark_form')[0].reset();
                        swal({
                              title: 'Success',
                              text: response.description,
                              type: 'success',
                              confirmButtonText: "OK",
                              closeOnConfirm: false
                           },
                          function(isConfirm,tmp)
                          {
                            if(isConfirm==true)
                            {
                               window.location.reload();
                            }
                          });

                     }
                     else
                     {
                        swal('Error',response.description,'error');
                     }
                 }

        });
    }

});
    });


$('#is_rejected').click(function(){

    var remark     = $('#remark').val();
    status         = 0;
    var csrf_token = $("input[name=_token]").val();

    $('#remark').attr('data-parsley-required','true');
    $('#remark').attr('data-parsley-required-message','Please enter remark.');

    if($('#remark_form').parsley().validate() == false) return false;


    $.ajax({
             url: module_url_path+'/product_confirmation',
             type:"POST",
             data: {status:status,product_id:product_id,remark:remark,_token:"{{ csrf_token() }}"},
             dataType:'json',
             success:function(response)
             {
                if(response.status == 'success')
                {


                    $('#remark_form')[0].reset();


                      swal({
                            title: "Are you sure?",
                            text: "You want to reject this product!",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonColor: '#DD6B55',
                            cancelButtonText: "No, cancel it!",
                            confirmButtonText: 'Yes, I am sure!',
                            closeOnConfirm: false,
                            closeOnCancel: true
                      },
                      function(isConfirm){

                       if (isConfirm){
                         swal({
                              title: 'Success',
                              text: response.description,
                              type: 'success',
                              confirmButtonText: "OK",
                              closeOnConfirm: false
                           },
                          function(isConfirm,tmp)
                          {
                            if(isConfirm==true)
                            {
                               window.location.reload();
                            }
                          });

                        }
                      });

                 }
                 else
                 {
                    swal('Error',response.description,'error');
                 }
             }

      });
    });


});

    function show_reject()
    {
      $("#reject_btn").hide();
      $("#is_approved").hide();
      $("#is_rejected").show();
      $("#cancel_btn").show();

      $(".remark_form").fadeToggle();


    }

    $("#cancel_btn").click(function(){

      $("#is_approved").show();
      $("#reject_btn").show();
      $("#is_rejected").hide();
      $("#cancel_btn").hide();


      $(".remark_form").fadeToggle();
    })

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

