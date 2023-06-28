{{-- {{dd($maker_details_arr)}}
 --}}
<div class="col-md-3">
   <div class="left-listing-pg-mn loaderElement">
      <div class="title-ordr">{{ $module_title or '' }}</div>
      <div class="main-order-create-left">
        <div class="mater-order">Master Leads 
          @if(isset($bucket_items_arr['leads_details']) && count($bucket_items_arr['leads_details'])>0)
          <a href="{{ $module_url_path.'/delete_all_products/'.base64_encode($bucket_items_arr['id'])}}" onclick="return confirm_action(this,event,'Are you sure? Do you want to delete all products from cart.');" class="close-clear"><i class="fa fa-trash"></i></a>
          @endif
        </div>
      </div>
      <div class="list-order-main-new">
         <div class="brand-detls-pn chat-list slimscroll" style="overflow: hidden;" tabindex="5005">
          @if(isset($bucket_items_arr['leads_details']) && count($bucket_items_arr['leads_details'])>0)
          @foreach($bucket_items_arr['leads_details'] as $bucket)
            <div class="brand-dtls-nw" class="product-bucket">
               <div class="img-brnd">
                @php
                if(isset($bucket['sku_images']['image']) && $bucket['sku_images']['image']!='' && file_exists(base_path().'/storage/app/'.$bucket['sku_images']['image']))
                {
                  $product_img = url('/storage/app/'.$bucket['sku_images']['image']);
                }
                else
                {                  
                  $product_img = url('/assets/images/no-product-img-found.jpg');                  
                }
                @endphp
                  <img src="{{$product_img or ''}}" alt="">
               </div>
               <div class="brnd-dtls-mn">
                  <div class="product-name-bnd">{{ $bucket['product_details']['product_name'] or ''}}</div>
                  <div class="inpt-selects">
                     
                    @if(isset($page_slug) && $page_slug=="edit")
                      <div class="qnty"><span>Qty:</span>
                        <input class="vertical-spin bucket_spin" readonly="" 
                        data-lead-id="{{base64_encode($bucket['representative_leads_id'])}}" 
                        data-product-lead-id="{{base64_encode($bucket['id'])}}" 
                        data-product-id="{{base64_encode($bucket['product_id'])}}"
                        data-comission="{{isset($bucket_items_arr['comission'])?$bucket_items_arr['comission']:""}}"
                        data-parsley-required="true" data-parsley-min="1" type="text" data-bts-button-down-class="btn btn-default btn-outline" data-bts-button-up-class="btn btn-default btn-outline" id="vertical_spin" name="item_qty" data-parsley-errors-container="#error_item_qty" value="{{$bucket['qty']}}">
                      </div>
                    @else
                      <div class="qnty"><span>Qty:</span> {{ $bucket['qty'] or ''}}</div>
                    @endif

                    <!-- <div class="qnty"><span>Qty:</span> {{ $bucket['qty'] or ''}}</div> -->
                    
                   
                     <div class="price-bnd-dtls"><span>Wholesale Price:</span>
                      <span>$</span>
                        <span class="pro-wholesale-price" id="wholesale_{{$bucket['id']}}">{{ isset($bucket['wholesale_price'])?num_format($bucket['wholesale_price']) : ''}}</span>
                      </div>
                     <div class="clearfix"></div>
                  </div>
               </div>
               <div class="clearfix"></div>

               <a href="{{$module_url_path.'/delete_product_from_bucket/'.base64_encode($bucket['id']) }}" onclick="confirm_action(this,event,'Are you sure? Do you want to delete this product.');"  class="close-brnd"><i class="fa fa-times"></i></a>
            </div>            
            @endforeach
            @else
            No Record Found
            @endif
         </div>
      </div>
      @if(isset($bucket_items_arr['leads_details']) && count($bucket_items_arr['leads_details'])>0)
      <div class="items-order">
         <div class="left-item-ordr">
            {{isset($bucket_items_arr['leads_details'])?count($bucket_items_arr['leads_details']): 0}} Items
         </div>
       
         <div class="clearfix"></div>
         <hr>
         <div class="left-item-ordr bucker-lst-nt">
            Retail Total
         </div>
         <div class="right-item-ordr bucker-lst-nt">
            $<span id="lead_retail_tot">{{ isset($bucket_items_arr['total_retail_price'])?num_format($bucket_items_arr['total_retail_price']) : 00}}</span>
         </div>
         <div class="clearfix"></div>
          <hr>
         <div class="left-item-ordr bucker-lst-nt">
            Sub wholesale Total
         </div>
         <div class="right-item-ordr bucker-lst-nt">
            $<span id="lead_wholesale_tot">{{ isset($bucket_items_arr['total_wholesale_price'])?num_format($bucket_items_arr['total_wholesale_price']) : 00}}</span>
         </div>

         <div class="left-item-ordr bucker-lst-nt">
          Commission
         </div>
         <div class="right-item-ordr bucker-lst-nt">
            -<span id="lead_comission">{{ $bucket_items_arr['comission'] or 00}}</span>%
         </div> 

         <div class="clearfix"></div>
          <div class="left-item-ordr bucker-lst-nt">
          Wholesale Total
         </div>
         <div class="right-item-ordr bucker-lst-nt">
            $<span id="lead_commi_less_wholesale">{{ $bucket_items_arr['tot_commi_less_wholesale'] or 00}}</span>
         </div> 

         <div class="clearfix"></div>
      </div>
      <div class="button-sv represetative-inline">
        @if($bucket_items_arr['total_wholesale_price']>$maker_details_arr['shop_settings']['first_order_minimum'])
           <a href="{{ $module_url_path.'/finalize_lead/'.base64_encode($lead_id).'?type=confirm' }}" onclick="return finalize_lead(this,event,'You want to save this lead as confirm lead, after confirming you will not able to modifiy anything.');" class="buttonsave-dv represemtative-btns" id = "lead_conf">Confirm</a> 
        @else    
           <a href="javascript:void(0);" onclick="return min_warning()" class="buttonsave-dv represemtative-btns">Confirm</a> 
    @endif
        </div>
      <div class="button-sv"></div>
      @endif
   </div>
</div>

<script type="text/javascript">
 


  $(document).ready(function()
  {
      
      $(".bucket_spin").TouchSpin({
            verticalbuttons: true,
            verticalupclass: 'ti-plus',
            verticaldownclass: 'ti-minus',
            min: 1,
            max: 5,

        }).on('touchspin.on.startspin', function (event) 
        {
            var qty              = $(this).val();
            var product_lead_id  = $(this).attr('data-product-lead-id');
            var lead_id          = $(this).attr('data-lead-id');
            var product_id       = $(this).attr('data-product-id');
            var comission        = $(this).attr('data-comission');

           /* var retail_price     = $(this).attr('data-retail-price');
            var wholesale_price  = $(this).attr('data-wholesale-price');

            var pro_total_wholesale_price = 0;
            var pro_total_retail_price = 0;

            pro_total_wholesale_price = parseInt(qty) * parseInt(wholesale_price);
            pro_total_retail_price    = parseInt(qty) * parseInt(retail_price);

           $('#retail_'+product_lead_id).text(pro_total_retail_price);
           $('#wholesale_'+product_lead_id).text(pro_total_wholesale_price);
            

           var previous_wholesale_price = $(this).attr('data-previous-wholesale-price');
           var previous_retail_price    = $(this).attr('data-previous-retail-price');

           var tot_whlosale = $("#lead_wholesale_tot").text();
           var tot_retail   = $("#lead_retail_tot").text();

           minus_previous_wholsale_price = parseInt(tot_whlosale) - parseInt(previous_wholesale_price);
           minus_previous_retail_price   = parseInt(tot_retail) - parseInt(previous_retail_price);
           
           var new_tot_wholesale = parseInt(tot_whlosale) + parseInt(pro_total_retail_price);
           var new_tot_retail    = parseInt(tot_retail) + parseInt(pro_total_retail_price);

           $("#lead_wholesale_tot").text(new_tot_wholesale);
           $("#lead_retail_tot").text(new_tot_retail);*/

           $.ajax({
                url: '{{$module_url_path}}/update_product_qty',
                  method   : 'GET',
                  dataType : 'JSON',
                  data: {product_lead_id:product_lead_id,
                        lead_id:lead_id,
                        qty:qty,product_id:product_id,
                        comission:comission
                        },
                  beforeSend : function()
                  {
                    showSingleElementLoader();                  
                  },
                  success:function(response)
                  {

                    hideSingleElementLoader();
                    if(response.status=="SUCCESS")
                    {
                        var arr = [];
                        arr = response.arr_responce;

                        var tot_commi_less_wholesale = arr.tot_commi_less_wholesale;
                        var tot_pro_retail    = arr.tot_pro_retail;
                        var tot_pro_wholesale = arr.tot_pro_whole;
                        var tot_retail        = arr.tot_retail;
                        var tot_whole         = arr.tot_whole;   
                        var id                = arr.id;
                        
                        $('#retail_'+id).text(tot_pro_retail);
                        $('#wholesale_'+id).text(tot_pro_wholesale);

                        $("#lead_wholesale_tot").text(tot_retail);
                        $("#lead_retail_tot").text(tot_whole);

                        $("#lead_commi_less_wholesale").text(tot_commi_less_wholesale);
                        
                    }  

                  } 

           });


           /*var minus_change_value = */

            //$("#total_wholesale_price").text(total_wholesale_price);
            
            
        });
  })

</script>

{{-- <script type="text/javascript">
  
  function delete_bucket_product(ref)
  {
    $.ajax({
          url: ref.attr('data-href'),
          type:"GET",
          dataType:'json',
          success:function(response)
          {
             if(response.status == 'SUCCESS')
             {
                 swal({
                         title: response.status,
                         text: data.description,
                         type: data.status,
                         confirmButtonText: "OK",
                         closeOnConfirm: false
                      },
                     function(isConfirm,tmp)
                     {
                       if(isConfirm==true)
                       {
                         $(ref).closest('.product-bucket').remove();
                       }
                     });
              }
              else
              {
                
                 swal('Error',response.description,'warning');
              }  
          }          
        });
  }
</script> --}}