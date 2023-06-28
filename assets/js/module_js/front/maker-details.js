    
    var product_id     = $("#product_id").val();
    var retail_price    = $("#retail_price").val();
    var wholesale_price = $("#wholesale_price").val();
    var product_price = $("#product_price").val();

    $(document).ready(function()
    {
        //show product details module if product id is set
        if(typeof (product_id) == 'string' && product_id!='')
        {
            $(".modal-mask").fadeIn();
            $(".modal-mask, .modal-popup").fadeIn();
            $('.vendor-profile-main-modal').fadeIn();
            $(".modal-popup").animate({        
                left: '10%'
            }, 'slow', function () {
                $(".modal-popup").animate({
                        'top': '5%'
                }, 200, "swing", function () {});
            });
        }

       
        var vspinTrue = $(".vertical-spin").TouchSpin({
            // verticalbuttons: true,
           
           // min: 1,
           // max: 5000
           
        }).on('touchspin.on.startspin', function (event) 
        {
            let qty  = $(this).val();
            
            if(qty>0)
            {
               calculate_total_retail_price(qty);            
            }
        });              
    });

    function calculate_total_retail_price(qty)
    {
        if(qty>0)
        {
            let total_retail_price = qty*retail_price;
            let total_wholesale_price = qty*wholesale_price;
            let total_product_price = qty*product_price;

            /*$('#total_retail_price').html('$'+total_retail_price);
            $('#total_wholesale_price').html('$'+total_wholesale_price);*/
           
            //$('#total_retail_price').html(total_retail_price.toFixed(2));
            if(qty>1000)
            {
             total_wholesale_price = 1000*total_product_price;
            }

           
            $('#total_wholesale_price').html(total_product_price.toFixed(2));
        
        }
        else
        {   total_product_price = 0;
            $('#total_wholesale_price').html(total_product_price.toFixed(2));
        }
    }


    $('#item_qty').keyup(function(){
        
        let qty = $(this).val();
        calculate_total_retail_price(qty);
    });

    $(".closemodal").on("click", function (){
        $('.vendor-profile-main-modal').fadeOut();
    });

    $(".bigbutton").on("click", function () {
        $(".modal-mask, .modal-popup").fadeIn();
        $('.vendor-profile-main-modal').fadeIn();
        $(".modal-popup").animate({        
            left: '10%'
        }, 'slow', function () {
            $(".modal-popup").animate({
                    'top': '5%'
            }, 200, "swing", function () {});
        });
    });

    $(document).on("keydown", function (event) {
        if (event.keyCode === 27) {
            $(".modal-popup").animate({
                width: '5%',
                left: '50%'
            }, 'slow', function () {
                $(".modal-mask, .modal-popup").fadeOut();
            });
        }
    });

    