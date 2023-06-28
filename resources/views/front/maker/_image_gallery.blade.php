<style type="text/css">
   .not-found-data {
   text-align: center;
   font-size: 30px;
   padding: 100px 0;
   background-color: #efefef;
   color: #b1b1b1;
   }
</style>
<div class="">
  <div class="large-5 column zoomleftcolumn">
    <div id ="gallery-container">
      <div class="button-login-pb">
        <a class="btn btn-default" id="zoom-in"><i class="fa fa-search-plus"></i></a>
      </div>

      <div class="xzoom-container">
        @if(isset($product_arr['product_details']) && count($product_arr['product_details'])>0)
          @foreach($product_arr['product_details'] as $prod_key =>$product_details) {{--              --}}           
            @php
              if(isset($product_details['image']) && $product_details['image']!='' && file_exists(base_path().'/storage/app/'.$product_details['image']))
              {
                $product_img2 = url('/storage/app/'.$product_details['image']);
                if(isset($product_details['image_thumb']) && $product_details['image_thumb']!='' && file_exists(base_path().'/storage/app/product_image/product_img_thumb/'.$product_details['image_thumb']))
                {
                  $product_thumb_img = url('/storage/app/product_image/product_img_thumb/'.$product_details['image_thumb']);
                    $product_thumb_img = image_resize($product_thumb_img,400,400);


                }
                else
                { 
                  $product_thumb_img = $product_img2;
                    $product_thumb_img = image_resize($product_thumb_img,400,400);

                }
              }
              else
              {   
                $product_img2 = url('/assets/images/no-product-img-found.jpg');
                $product_thumb_img = url('/assets/images/no-product-img-found.jpg');
              }

              $login_user = Sentinel::check();
           
              $prod_user_id = isset($product_arr['user_id'])?$product_arr['user_id']:'';

         
              if($login_user != false)
              {
                
                if($prod_user_id == $login_user->id)
                {
                
                  $show_add_cart_btn_ajx = "none";
                }
                else
                {
                  
              
                  $show_add_cart_btn_ajx = "";
                }
              }
              else
              {
                
            
                $show_add_cart_btn_ajx = "";
              }

            @endphp    
            @if($prod_key==0)
              <div class="img-thumbnail">
                <img class="xzoom image" id="xzoom-default" src="{{$product_thumb_img or ''}}" xoriginal="{{$product_img2 or ''}}" ajx_cart_btn = {{ $show_add_cart_btn_ajx}} />
              </div>
            @endif
          @endforeach
        @endif 
        <div class="xzoom-thumbs">
           
          @if(isset($product_arr['product_details']) && count($product_arr['product_details'])>0)
            @foreach($product_arr['product_details'] as $prod_key =>$product_details) 
              @php
                if(isset($product_details['image']) && $product_details['image']!='' && file_exists(base_path().'/storage/app/'.$product_details['image']))
                {
                  $product_img2 = url('/storage/app/'.$product_details['image']);
                  if(isset($product_details['image_thumb']) && $product_details['image_thumb']!='' && file_exists(base_path().'/storage/app/product_image/product_img_thumb/'.$product_details['image_thumb']))
                  {
                    $product_thumb_img = url('/storage/app/product_image/product_img_thumb/'.$product_details['image_thumb']);

                    $product_thumb_img = image_resize($product_thumb_img,500,500);


                  }
                  else
                  { 
                    $product_thumb_img = $product_img2;
                    $product_thumb_img = image_resize($product_thumb_img,500,500);

                  }
                }
                else
                {                  
                  $product_img2 = url('/assets/images/no-product-img-found.jpg');
                  $product_thumb_img = url('/assets/images/no-product-img-found.jpg');
                }
              @endphp     
              <a href="{{$product_img2}}"><img class="xzoom-gallery imgsku" width="80" height="80" src="{{$product_img2}}"  xpreview="{{$product_thumb_img}}" imgsku="{{$product_details['sku']}}"
              pro-weight="{{$product_details['weight']}}"
              pro-height = "{{$product_details['height']}}"
              pro-width = "{{$product_details['width']}}"
              pro-length = "{{$product_details['length']}}"
              pro-qty = "{{$product_details['inventory_details']['quantity']}}"
              option_type ="{{$product_details['option_type']}}"
              option_value = "{{$product_details['option']}}"
              ></a>
            @endforeach
          @endif
        </div>
      </div>
    </div>
  </div>
  <div class="large-7 column"></div>
</div>


<script type="text/javascript">
//Handeling Escape keypress 

$(document).keydown(function(e) {
    if (e.keyCode == 27) {
     setTimeout(closePopup, 0);
    }

});

function fix_price()
{
  $(".vertical-spin").TouchSpin({ max: 1000});
}

function closePopup()
{  
  $('.vendor-profile-main-modal').css('display', 'none');
}

</script>

<script type="text/javascript" src="{{url('/')}}/assets/js/module_js/front/maker-details.js"></script>
<script src="{{url('/')}}/assets/front/js/xzoom.min.js"></script>
<script src="{{url('/')}}/assets/front/js/setup.js"></script>
<script type="text/javascript">
   $(document).ready(function() 
   {    
     $("#item_qty").keyup(function(){
      var check_qty = $("#item_qty").val();
        if(check_qty>1000)
        {
          swal('Warning','Purchase limit 1000 units.','warning');
          flag ="false";
          $("#item_qty").val(1000);
          return
        }
      });
 $('#item_qty').on('touchspin.on.startspin', function ()
   {
      var check_qty = $("#item_qty").val();
      if(check_qty>1000)
      {
       swal('Warning','Purchase limit 1000 units.','warning');
       flag ="false";
       $("#item_qty").val(1000);
       return
      }
   });
   
      check_quantity();
     function check_quantity()
     {
       var quantity =  $('#quantity').text();
       
       if(quantity==0){
       
       var out_of_stock = ` <span class="outofstock">Out of stock</span>`;
       $(".button-login-pb").hide();
       $(".out-of-stock-container").show();
   
       
       $("#item_qty").prop('disabled', true);
       }
       else
       {
         var in_stock = `<a href="javascript:void(0)" class="gt-button" id="add-to-bag">Add to Cart</a>
                    <div class="clearfix"></div>`;
         $("#item_qty").prop('disabled', false);
         $(".button-login-pb").show();
         $(".out-of-stock-container").hide();
   
         //$("#item_qty").attr('data-parsley-max',quantity);
         $("#prod_qty").val(quantity);
         $('#frm-add-to-bag').parsley().reset();
   
         var max_qty = $("#item_qty").val();
         $(".vertical-spin").TouchSpin({
                
               min: 1,
               max: max_qty
              
           });
       }
     }
   
       $('.imgsku').click(function()
       {    
           var imgsku = $(this).attr('imgsku');
           var img_option_type = $(this).attr('option_type');
           var img_option_value = $(this).attr('option_value');
           var img_height = $(this).attr('pro-height');
           var img_width = $(this).attr('pro-width');
           var img_length = $(this).attr('pro-length');
           var img_weight = $(this).attr('pro-weight');
           var img_qty = $(this).attr('pro-qty');
   
          
           $("#option_type").text(img_option_type);
   
           $("#weight").text(img_weight);
           $("#height").text(img_height); 
           $("#width").text(img_width); 
           $("#length").text(img_length);
           $("#quantity").text(img_qty);
   
           $("#prod_qty").val(img_qty);
           $('#frm-add-to-bag').parsley().reset();
   
           var max_qty = $("#quantity").clone().children().remove().end().text();

           check_quantity();
   
           var opt_val = img_option_value;
                         opt_val = opt_val.toLowerCase().replace(/\b[a-z]/g, function(letter) {
                         return letter.toUpperCase();
                       });
   
           $('.option_value').text(opt_val);
           $("#sku_num").val(imgsku);
   
           var a = $("#item_qty").attr('data-parsley-max');
         
           
       });   

      $("#zoom-in").click(function()
      {
         $("#gallery-container").addClass('removeclass-img-hover');
         $(".xzoom-container").addClass('pointeventon');
         $("#gallery-container").addClass('extrazoom');
        });


      });
   
    var show_cart_btn = $('#xzoom-default').attr('ajx_cart_btn');
 
   //Handeling Escape keypress 

</script>