<div class="vendor-profile-main-modal">
    <div class="modal-mask"></div>
<link href="{{url('/')}}/assets/front/css/just-got-to-have-it.css" rel="stylesheet" type="text/css" />
<style type="text/css">
  .suggested-price span{
    display: inline-block !important;
  }
  .maker-details-ul-ul li{
  background-color: #dcdcdc;
}

</style>
    <div class="modal-popup" id="mymodel">
        <a href="" class="closemodal">
            <img src="{{url('/')}}/assets/images/popup-close-btn.png" alt="" />
        </a>
        <div class="modal-bodys">
            <div class="main-modalbd">
                <div class="detail-page-gallery-div">
                    <div id="example1" class="rwaltz-gallery img300"></div>
                </div>
                <div class="details-of-list">  
                    <div class="subminimum-font">
                         
                      <a id="company_name" href="">
                      </a>
                      
                      <div class="pro-title-list" id="first_order_minimum"></div>

                  </div>

                  <a href ="" id ="cat_search">
                    <div class="suggested-price" id="category_name"></div>
                  </a>

                    <div class="title-detail-pg" id="popup_product_name"></div>
                   
                    <hr>
                    <div class="row">
                        {{-- <div class="col-md-6">
                      
                        </div> --}}
                        <div class="col-md-6">
                            <div class="suggested-price mkr-sub-pc">Wholsale Price: <br><span>$</span><span id="popup_wholesale_price"></span></div>
                        </div>
                     
                    </div>
                    <hr>

                    <form id="form_lead_product" onsubmit="return false;">
                      {{csrf_field()}}

                      <input type="hidden" id="maker_id" name="maker_id" value="">
                      <input type="hidden" name="lead_id" value="{{ $lead_id or 0 }}" placeholder="lead ID">
                      <input type="hidden" name="product_id" id="product_id" value="">
                      <input type="hidden" name="sku_num" id="sku_num" value="">
                      <input type="hidden"   name="commission" id="commission" value="">


                          {{--  <hr> --}}
       
              <div id="demo" class="maker-details-ul-ul">
                    
                    <ul>
                

            
               {{--  <li><div class="li-left">Weight :</div><div id ="weight" class="newoptionvalue"></div></li>
                
                <li><div class="li-left">Height :</div><div id = "height" class="newoptionvalue"></li>
                
                <li><div class="li-left">Length :</div><div id = "length" class="newoptionvalue"></li>
                
                <li><div class="li-left">Width :</div><div id = "width" class="newoptionvalue"></div></li> --}}

                <li style="display: none;"><div class="li-left" style="display: none;">Quantity :</div><div id = "quantity" class="newoptionvalue"></div></li>
              </ul>

            </div>



                     
                 <div class="form-group">
          <label class="form-lable">Item Quantity</label>                
            <input class="vertical-spin bucket_spin" 
               data-parsley-required="true" type="text" data-bts-button-down-class="btn btn-default btn-outline" data-bts-button-up-class="btn btn-default btn-outline" id="item_qty" name="item_qty" data-parsley-errors-container="#error_item_qty" value = 1 data-parsley-type="integer" data-parsley-trigger="change" data-parsley-min =1> 
            <input type="hidden" id = "prod_qty"> 
            <div id="error_item_qty" style="display: none;"></div>   
           </div>  

                    
                      <div class="button-login-pb">
                          <button type="submit" id="btn_submit" class="btn btn-block btn-success">Add to cart</button>
                          <div class="clearfix"></div>
                      </div>

                    <div class="out-of-stock-container">
                     <span class="outofstock">Out of stock</span>
                    </div>
                    </form>
                     <div class="total-retail-price">
                    
                    <div class="suggested-price case-right">Total Price<span>$</span><span id="total_wholesale_price"></span></div>
                    <span>Total price in wholesale</span>
                </div>

                </div>
                <div class="clearfix"></div>
            </div>

            <hr>
            <div class="about-product-title">About Product</div>
            <div class="product-about-p" id="popup_product_description">
               
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
</div>
<script type="text/javascript">
 

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

function checkQuantity()
{
    var quantity =  $('#prod_qty').val();

   if(quantity == 0 )
   {
     var out_of_stock = ` <span style="color:red;font-weight:bold">Out of stock</span>`;
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
      
     
     $("#prod_qty").val(quantity);
     //$('#frm-add-to-bag').parsley().reset();
     var max_qty = $("#item_qty").attr('data-parsley-max');
   /*  
     $(".vertical-spin").TouchSpin({
            
           min: 1,
           max: max_qty
          
       });
   */
      $(".vertical-spin").TouchSpin({
   
       min: 0,
       //max: 1000//max_qty
           
       }).on('touchspin.on.startspin blur change', function (event) 
       {   
           let qty = $('#item_qty').val();    
           let wholesale_price = $("#popup_wholesale_price").text();
                  
           var total_wholesale_price = 0;
          
           total_wholesale_price = parseFloat(qty) * parseFloat(wholesale_price);

           if (qty > 1000) {
            
              total_wholesale_price = 1000 * parseFloat(wholesale_price);
           }
           
           
           $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));
           
       });
      }
   }


</script>
<script src="{{url('/')}}/assets/js/gallery.min.js"></script>
<!-- Modal Script -->