<style>
   #l4sr0we-1586349975069 {box-shadow:none !important;}
   .footer-content-block {clear:left;}
</style>

</div>

  <footer>
        <div id="footer">
            <div class="footer-main-block">
                <div class="container">
                    <div class="row">
                        <div class="footer-col-block">
                            <div class="col-sm-12 col-md-3 col-lg-3">
                                <div class=" footer-col-head logo-block">
                                     <a href="{{url('/')}}">

                                        @php
                                            $site_img = false;

                                            $site_base_img = isset($site_setting_arr['site_logo']) ? $site_setting_arr['site_logo'] : false;

                                            $site_image_base_path = base_path('storage/app/'.$site_base_img);

                                            $site_name = isset($site_setting_arr['site_name']) ? $site_setting_arr['site_name'] : false;
                                            
                                            $site_default_image = url('https://via.placeholder.com/160x53.png?text='.$site_name);
                                            $site_img = image_resize($site_image_base_path,211,75,$site_default_image);
                                        @endphp

                                        <img src="{{ $site_img }}" alt="{{ $site_name }}" />

                                       
                                  
                                     </a>
                                </div>
                                <div class="">
                                    <div class="footer-content-block">
                                        <p class="p-txt-footer">
                                        {!! $site_setting_arr['site_short_description'] or '' !!}</p>
                                        <a class="more-txt-block" href="{{ url('/')}}/about">About us</a>
                                    </div>
                                    <div class="faq-link"><a href="{{ url('/')}}/faq" @if(Request::segment(1)=='faq')class="active" @endif>FAQs</a></div>
                                </div>
                              {{--    <div class="footer_heading footer-col-head">
                                    <span>Learn More</span>
                                </div> --}}
                               {{--  <div class="menu_name points-footer">
                                    <ul>
                                        <li><a href="{{ url('/')}}/about">About</a></li>
            
                                    @if(isset($cms_pages_arr) && count($cms_pages_arr)>0)
                                    
                                    @foreach($cms_pages_arr as $cms_page)

                                    <li><a href="{{ isset($cms_page['page_slug'])?url('/page/'.$cms_page['page_slug']):''}}">{{ucfirst($cms_page['page_title'])}}</a>
                                    </li>
                                    @endforeach
                                    </ul>
                                </div> --}}
                            </div>

               

                            <div class="col-sm-12 col-md-6 col-lg-6 abc">


                           @if(isset($arr_category) && sizeof($arr_category)>0)        
                                
                                <div class="footer_heading footer-col-head txt-ctr category-footer-drop">

                                    <span>Category</span>
                                    <img src="{{url('/')}}/assets/front/images/chevron-down.svg" alt="">
                                </div>
                                <div class="menu_name points-footer">
                                    <ul class="footeruls-f">

                                        @foreach ($arr_category as $key =>$category)

                                            <li><a @if(isset($search_value['category-id']) && base64_decode($search_value['category-id'])==$category['id']) class="active" @endif href="{{url('/')}}/search?category_id={{isset($category['id'])?base64_encode($category['id']):""}}">{{isset($category['category_name'])?ucfirst($category['category_name']):""}}</a></li>
                                        @endforeach

                                    </ul>
                                </div>

                                @endif
                            </div>
                            <div class="col-sm-12 col-md-3 col-lg-3 abc">
                                <div class="footer_heading footer-col-head last-subscribe">
                                    <span>Join Our Email List</span>
                                </div>
                                <div class="menu_name points-footer">
                                    <div class="title-sub-hm">Sign up to receive updates and specials.</div>
                                <form class="form-horizontal" id="validation-form"> 
                                    
                                    {{ csrf_field() }}
                                    
                                    <div class="subscri-block-new subscri-new-block">
                                        <input type="text" name="email" placeholder="Your Email..." data-parsley-required="true" data-parsley-required-message="Please enter email address." data-parsley-type-message="Please enter valid email address." data-parsley-type="email" data-parsley-errors-container="#errorblock"/>
                                        <button type="button" class="sent-btns" id ="btn_add"><i class="fa fa-paper-plane"></i></button>
                                     <div id ="errorblock"></div>
                                    </div>
                                    <div class="footer-col-head  tile-joints">
                                        <span> Join Us</span>

                                        <ul class="social-footer">
                                            <li><a class="facebook" target="_blank" href="{{isset($site_setting_arr['fb_url'])?$site_setting_arr['fb_url']:""}}"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>

                                            <li><a class="insta" target="_blank" href="{{isset($site_setting_arr['instagram_url'])?$site_setting_arr['instagram_url']:""}}"><i class="fa fa-instagram" aria-hidden="true"></i></a></li>

                                            <li><a class="linkdin" target="_blank" href="{{isset($site_setting_arr['linkdin_url'])?$site_setting_arr['linkdin_url']:""}}"><i class="fa fa-linkedin"></i></a></li>

                                            <li><a class="twitter" target="_blank" href="{{isset($site_setting_arr['twitter_url'])?$site_setting_arr['twitter_url']:""}}"><i class="fa fa-twitter"></i></a></li>

                                        {{--     <li><a target="_blank" href="{{isset($site_setting_arr['whatsapp_url'])?$site_setting_arr['whatsapp_url']:""}}"><i class="fa fa-whatsapp"></i></a></li> --}}

                                            <li><a class="youtube" target="_blank" href="{{isset($site_setting_arr['youtube_url'])?$site_setting_arr['youtube_url']:""}}"><i class="fa fa-youtube"></i></a></li>
    
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </div>
                {{--  <a class="logi-link-block" data-toggle="modal" href="#CongratulationsModal">Login Now!</a> --}}
                <div class="copyright-block">

                    <i class="fa fa-copyright"></i> <a href="{{url('/')}}" target="_blank">{{isset($site_setting_arr['site_name'])?$site_setting_arr['site_name']:'Rwaltz'}}</a> {{date('Y')}}. All Rights Reserved.

                    
                    {{-- <div class="copyright-black-links">
                        <a href="#">Privacy Policy</a>
                        <a href="#">Terms & Conditions</a>
                    </div> --}}
                </div>
            </div>

            <!-- Modal Sign IN  Start -->
            <div id="myModal" class="modal fade login-popup" data-replace="true" style="display: none;">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal"><img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="" /> </button>
                            <div class="col-sm-6 col-md-6 col-lg-6">
                                <div class="login-form-block">
                                    <div class="forget-pwd"><a data-toggle="modal" href="#myModalForgot">Forgot Password?</a></div>
                                    <div class="login-content-block">
                                        <a class="logi-link-block" data-toggle="modal" href="#myModal-signup">Sign Up Now!</a>
                                    </div>
                                </div>
                            </div>
                            <div class="clr"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal Sign IN  End -->

            <!-- Modal Sign Up -->
            <div id="myModal-signup" class="modal fade login-popup" data-replace="true" style="display: none;">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content sign-up-popup">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal"><img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="" /> </button>
                            <div class="col-sm-6 col-md-6 col-lg-6">
                                <div class="login-form-block">
                                    <div class="login-content-block">
                                        <a class="logi-link-block" data-toggle="modal" href="#myModal">Login Now!</a>
                                    </div>

                                </div>
                            </div>
                            <div class="clr"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Forget Password  Start -->
            <div id="myModalForgot" class="modal fade login-popup" data-replace="true" style="display: none;">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-body">
                            <button type="button" class="close" data-dismiss="modal"><img src="{{url('/')}}/assets/front/images/popup-close-btn.png" alt="" /> </button>                
                            <div class="login-form-block">
                                <div class="form-row">
                                    <div class="login-content-block">
                                        <a class="logi-link-block" data-toggle="modal" href="#myModal">Login Now!</a>
                                    </div>
                                </div>
                            </div>
                            <div class="clr"></div>                
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal Forget Password  End -->

        </div>
    </footer>

    
<script type="text/javascript" src="{{url('/')}}/assets/loader/loadingoverlay.min.js"></script>
<script type="text/javascript" src="{{url('/')}}/assets/loader/loader.js"></script>
<script type="text/javascript" src="{{url('/')}}/assets/js/Parsley/dist/parsley.min.js"></script>
<!--[if lt IE 9]>-->


<!-- <script defer async src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js" ></script> -->

<script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>


<!--Datepicker js-->

<script  src="{{url('/')}}/assets/js/text_less_more.min.js"></script>


<!--     Modal Hide Show Start Here-->
<script type="text/javascript" language="javascript" src="{{url('/')}}/assets/front/js/bootstrap-modal.js"></script>
<script type="text/javascript" language="javascript" src="{{url('/')}}/assets/front/js/modalmanager.js"></script>
<!--     Modal Hide Show End Here-->

<script type="text/javascript" src="{{url('/')}}/assets/js/sweetalert_msg.js"></script>
<script type="text/javascript" src="{{url('/')}}/assets/sweetalert/sweetalert.js"></script>
<!-- <script type="text/javascript" src="{{url('/')}}/assets/js/after_login_common.js"></script> -->

<!-- <script src="{{url('/')}}/assets/front/js/jquery.flexisel.js" type="text/javascript" async></script> -->






    
<!--footer section end here-->
<a class="cd-top hidden-xs hidden-sm" href="#0">Top</a>
<script type="text/javascript" language="javascript" src="{{url('/')}}/assets/front/js/bootstrap.min.js" async></script>

<script type="text/javascript" language="javascript" src="{{url('/')}}/assets/front/js/backtotop.js" async></script>
    


<script defer async  src="{{url('/')}}/assets/plugins/bower_components/bootstrap-datepicker/bootstrap-datepicker.min.js" async></script>
@if(Request::segment(2) != 'product_detail')
<script type="text/javascript" src="https://kenwheeler.github.io/slick/slick/slick.js"></script>
@endif
<!-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script> -->
    <script type="text/javascript" src="{{url('/')}}/assets/front/js/pinterest_grid.js"></script>



<!-- Demo Script Start -->
<script>
    $(document).ready(function(){
  $(".SlickCarousel").slick({
    rtl:false, // If RTL Make it true & .slick-slide{float:right;}
    autoplay:true, 
    autoplaySpeed:5000, //  Slide Delay
    speed:800, // Transition Speed
    slidesToShow:4, // Number Of Carousel
    slidesToScroll:1, // Slide To Move 
    pauseOnHover:false,
    appendArrows:$(".Container .Head .Arrows"), // Class For Arrows Buttons
    prevArrow:'<span class="Slick-Prev"></span>',
    nextArrow:'<span class="Slick-Next"></span>',
    easing:"linear",
    responsive:[
      {breakpoint:801,settings:{
        slidesToShow:3,
      }},
      {breakpoint:641,settings:{
        slidesToShow:3,
      }},
      {breakpoint:481,settings:{
        slidesToShow:1,
      }},
    ],
  })
})
</script>
<!-- Demo Script End -->
    
    <!--Sticky Menu-->
<script type="text/javascript">
    
var module_url = "{{url('/')}}";

$(document).ready(function() {


    update_user_active_time();

    $("body").click(function() {
        $('#user_info_dropdown').hide();
    });

    // $(".datepicker").datepicker({
    //     autoclose: true,
    //     todayHighlight: true
    // });

    var stickyNavTop = $('.header').offset().top;

    var stickyNav = function() {
        var scrollTop = $(window).scrollTop();

        if (scrollTop > stickyNavTop) {
            $('.header').addClass('sticky');
        } else {
            $('.header').removeClass('sticky');
        }

    };

    stickyNav();

    $(window).scroll(function() {
        stickyNav();
    });

    $("#validation-form").submit(function(e) {
        e.preventDefault();
        subscriptionCall();
    });

    $('#btn_add').click(function() {
        subscriptionCall();
    });

    var url_string = window.location.href;
    var url = new URL(url_string);
    var vendor_id = url.searchParams.get("vendor_id");
    if (vendor_id != null) {
        checkVendorStatus(vendor_id);
    }

    // <!--Footer Js Hide Show Start Here-->
    /*$(".footer_heading").on("click", function() {
        $(this).toggleClass("active");
        $(this).next(".menu_name").slideToggle("slow");
        $(this).parent(".abc").siblings().find(".menu_name").slideUp();
        $(this).parent(".abc").siblings().children().removeClass("active");
    });*/

    $("ul.pagination > li").each(function(index,elem){
        $(elem).on("click",function(e){
          // e.preventDefault();

          showProcessingOverlay();

          if($(elem).hasClass('active'))
          {
            hideProcessingOverlay();
          }
          
        })
      });
})


// Sticky Menu



function checkVendorStatus(vendor_id) {

    $.ajax({
        url: module_url + '/checkVendorStatus',
        type: "POST",
        data: {
            vendor_id: vendor_id,
            "_token": "{{ csrf_token() }}"
        },
        dataType: 'json',
        beforeSend: function() {},
        success: function(data) {

            if (data.status == 'error') {
                swal({
                        title: 'Success',
                        text: data.description,
                        type: data.status,
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(isConfirm) {

                        window.location.href = module_url + '/search_vendor';

                    });
            } else {

            }
        }
    });
}

function update_user_active_time()
{
    const SITE_URL = '{{ url('/')}}'; 
    $.ajax({
      url: SITE_URL+'/update_user_active_time',
      type:"GET",
      dataType:'json',
      beforeSend : function()
      {
        
      },
      success:function(response)
      {

      }    
    });     
}

function subscriptionCall() {
    if ($('#validation-form').parsley().validate() == false) return;
    var formdata = new FormData($("#validation-form")[0]);

    $.ajax({
        url: module_url + '/subscribe',
        type: "POST",
        data: formdata,
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            showProcessingOverlay();
            //$('#carousel-example-generic').html('Please Wait <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
        },
        success: function(data) {

            if ('success' == data.status) {
                hideProcessingOverlay();
                $('input[name=email]').removeClass('parsley-success');

                $('#validation-form')[0].reset();

                swal({
                    title: 'Success',
                    text: data.description,
                    type: data.status,
                    confirmButtonText: "OK",
                    closeOnConfirm: true
                });

            } else {
                hideProcessingOverlay();
                $('input[name=email]').removeClass('parsley-success');
                $('#validation-form')[0].reset();

                var status = data.status;
                status = status.charAt(0).toUpperCase() + status.slice(1);

                swal(status, data.description, data.status);
            }
        }

    });
}

// <!-- Min Top Menu Start Here  -->

var doc_width = $(window).width();
if (doc_width < 1180) {
    function openNav() {
        document.getElementById("mySidenav").style.width = "250px";
        $("body").css({
            "margin-left": "250px",
            "overflow-x": "hidden",
            "transition": "margin-left .5s",
            "position": "fixed"
        });
        $("#main").addClass("overlay");
    }

    function closeNav() {
        document.getElementById("mySidenav").style.width = "0";
        $("body").css({
            "margin-left": "0px",
            "transition": "margin-left .5s",
            "position": "relative"
        });
        $("#main").removeClass("overlay");
    }
}

if(doc_width <= 991)
{
    $(".footer_heading").on("click", function() {
        $(this).toggleClass("active");
        $(this).next(".menu_name").slideToggle("slow");
        $(this).parent(".abc").siblings().find(".menu_name").slideUp();
        $(this).parent(".abc").siblings().children().removeClass("active");
    });
}

$(".min-menu > li > .drop-block").click(function() {
    if (false == $(this).next().hasClass('menu-active')) {
        $('.sub-menu > ul').removeClass('menu-active');
    }
    $(this).next().toggleClass('menu-active');
    return false;
});
$("body").click(function() {
    $('.sub-menu > ul').removeClass('menu-active');
});

function isNumber(evt) {
    evt = (evt) ? evt : window.event;
    var charCode = (evt.which) ? evt.which : evt.keyCode;
    if (charCode > 31 && (charCode < 48 || charCode > 57 || charCode == 8)) {
        return false;
    }
    return true;
}
</script>


<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/5de5f72343be710e1d204300/default';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>






<!-- Category Script Start -->

<script>
    $(document).ready(function() {
        var $window = $(window);
        var windowsize = $window.width(); 

        if(windowsize <= 1100)
        {
            $('#blog-landing').removeClass('ul-main-left-hedr');
        }

        $('#blog-landing').pinterest_grid({
            no_columns: 4,
            padding_x: 10,
            padding_y: 10,
            margin_bottom: 50,
            single_column_breakpoint: 700
        });

});
</script>


<!--  <script type="text/javascript">
var requestUrl = "http://ip-api.com/json";

    $.ajax({
      url: requestUrl,
      type: 'GET',
      success: function(json)
      {
        console.log("My country is: " + json.country);

            //if (json.country == 'United States') 
            {
            //if (json.country == 'India') {

                $.ajax({                              //Cookies Status Ajax
                    url: module_url + '/daily_popup',
                    type: "GET",
                    success: function(data) {
                        if (data.status == true) {
                            sweetAlert("Warning!", "This site is in under working..! ", "warning"); 
                        }
                    }
                }); 
            }
        },
        error: function(err) {

            console.log("Request failed, error = " + err);
        }
    });
</script> --> 

 <script type="text/javascript">
    var requestUrl = module_url + '/daily_popup';
    $.ajax({                              //Cookies Status Ajax
        url: requestUrl,
        type: "GET",
        success: function(data) {

            if (data.status == true) 
            {
                // sweetAlert("", "Launching Soon... ", "");   
                sweetAlert("Launching Soon...")
            }
        }
    });   
</script>

<!-- <script>
    $(document).ready(function() {

        $.ajax({                              //Cookies Status Ajax
            url: module_url + '/daily_popup',
            type: "GET",
            success: function(data) {

                if (data.status == true) {
                    sweetAlert("", "This site is in under Testing..! ", "warning"); 
                }
            },
            error: function(err) {

                console.log("Request failed, error = " + err);
            }
        });

    });
</script> -->

<script defer async type="text/javascript" src="{{url('/')}}/assets/js/sweetalert_msg.js"></script>
<script defer async type="text/javascript" src="{{url('/')}}/assets/sweetalert/sweetalert.js"></script>

</body>

</html>
