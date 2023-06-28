@extends('representative.layout.master') @section('main_content')
{{-- {{dd($maker_details_data)}} --}}
<link href="{{url('/')}}/assets/css/gallery.css" rel="stylesheet" type="text/css" />
<div id="page-wrapper">
   <div class="container-fluid">
      <div class="row bg-title">
         <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">{{$module_title or ''}}</h4>
         </div>
         <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
         </div>
         <!-- /.col-lg-12 -->
      </div>
      <!-- .row -->
      <div class="row">
         <div class="col-lg-12">
            @include('admin.layout._operation_status')
            <div class="white-box">
               <div class="row">
                  <div class="col-md-12">
                     <ul class="tb-links tab-find-link">
                        <li> <a href="{{ $module_url_path.'/create'}}">{{ $module_title or '' }}</a> </li>
                        <li class="active"> <a href="#">Find Products</a> </li>
                        
                        <li> <a href="{{$module_url_path}}/order_summary/{{$order_no }}">Order Summary</a> </li>
                        {{-- 
                        <li> <a href="#">Confirm Order</a> </li>
                        --}}
                     </ul>
                  </div>{{-- 
                  @include('representative.leads._bucket_list') --}}
                  <div class="col-md-12">
                    
                     <div class="title-billing-address">Product List</div>
                     <div class="maker-find-pro-main">
                      
                        @if(isset($maker_details_arr) && sizeof($maker_details_arr)>0)         
                        <div class="maker-left-fnd">
                           @php
                           if(isset($maker_details_arr['store_details']['store_profile_image']) && $maker_details_arr['store_details']['store_profile_image']!='' && file_exists(base_path().'/storage/app/'.$maker_details_arr['store_details']['store_profile_image']))
                           {
                              $maker_img = url('/storage/app/'.$maker_details_arr['store_details']['store_profile_image']);
                           }
                           else
                           {                  
                              $maker_img = url('/assets/images/no-product-img-found.jpg');                  
                           }
                           @endphp
                           <img src="{{ $maker_img or '' }}" alt="">
                        </div>
                        <div class="maker-right-fnd">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="title-mkr-rgt">
                                    {{ $maker_details_arr['maker_details']['brand_name'] or ''}}
                                    <div class="maker-name-gray">
                                       {{ isset($maker_details_arr['first_name'])?$maker_details_arr['first_name']:''}}
                                       {{ isset($maker_details_arr['last_name'])?$maker_details_arr['last_name']:'' }}
                                       
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="email-address-maker">Email : {{ $maker_details_arr['email'] or '' }}</div>
                              </div>
                           </div>
                           <div class="row">
                              Comission: {{isset($maker_details_arr['maker_comission']['commission'])?$maker_details_arr['maker_comission']['commission']:""}}%
                           </div>
                           
                           <div class ="row">
                           Minimum: ${{ isset($maker_details_arr['shop_settings']['first_order_minimum'])?$maker_details_arr['shop_settings']['first_order_minimum']:'' }}
                           </div>

                           {{--  
                           <p>
                              Lorem ipsum dolor sit amet, consectetur adipisicing elit. Obcaecati adipisci vero dolorum pariatur aut consectetur. Sit quisquam rerum corporis neque atque inventore nulla, quibusdam, ipsa suscipit aperiam reiciendis, ea odio?
                           </p>
                           --}}
                        </div>
                        @endif
                        <div class="clearfix"></div>
                     </div>
                     <div class="view-catalog-class">
                        {{-- <a href="#" class="view-catalog-a">View Catalog</a> --}}
                     </div>
                     <div class="row">
               
                     </div>
                   
                     <div class="table-responsive">
                        <table class="table" id="table_module">
                           <thead>
                              <tr>
                                 <th>Product Name</th>
                                 <th>Vendor Name</th>
                                 <th>Wholesale Price</th>
                                 {{-- <th>Retail Price</th> --}}
                                 <th>Action</th>
                              </tr>
                           </thead>
                           <tbody>
                           </tbody>
                        </table>
                     </div>


                     @if(isset($arr_result) && count($arr_result)>0)
                     <div class="clearall-butn">
                        
                        <a href="{{url('/representative/leads/delete_all_products/')}}/{{$order_no}}" class="cleaall" onclick="confirm_action(this,event,'Are you sure? Do you want to remove all products.');">Clear All</a>


                     </div>
                     <div class="clearfix"></div>
                      @foreach($arr_result as $company_name => $final_arr)
                        
                        @if(isset($final_arr) && count($final_arr)>0)

                        <div class="main-bx-product">

                          <div class="titlbrand">{{$company_name}}</div>

                          <div class="row">
          
                          @foreach($final_arr as $key_sku => $product_data)


                          <div data-brand="{{ $product_data['user_id'] or '' }}" data-brand-min="{{ $product_data['shop_settings']['first_order_minimum'] or '' }}" data-name="{{$company_name}}"></div>

                          <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
                          <div class="inners-lead">

                          <a href="{{url('/representative/leads/delete_product_from_bucket/')}}/{{$order_no}}/{{isset($product_data['id'])?base64_encode($product_data['id']):''}}" class="close-brnd" onclick="confirm_action(this,event,'Are you sure? Do you want to delete this product from cart.');"><i class="fa fa-times"></i>
                                {{-- <img src="{{url('/')}}/assets/front/images/close-txt.png" alt=""> --}}
                          </a>

                          <div class="prodtnm"> {{$product_data['product_name'] or ''}}</div> 
                         <div class="main-float">

                          
                           <div class="price-whols">Unit price : ${{isset($product_data['unit_wholsale_price'])?num_format($product_data['unit_wholsale_price']):'0.00'}}
                            <br>
                            <div>Min amount for product discount : $<span id="product_total_{{$product_data['sku']}}" data-brand-id="{{ $product_data['user_id'] or '' }}">{{isset($product_data['product_dis_min_amt'])?num_format($product_data['product_dis_min_amt']):0}}</span></div><br>

                            <div>Product Discount : $<span id="product_total_{{$product_data['sku']}}" data-brand-id="{{ $product_data['user_id'] or '' }}">{{isset($product_data['product_discount'])?num_format($product_data['product_discount']):0}}</span></div><br>

                            <div>Min shipping order amount : $<span id="product_total_{{$product_data['sku']}}" data-brand-id="{{ $product_data['user_id'] or '' }}">{{num_format($product_data['minimum_amount_off'])}}</span></div><br>

                            <div>Min order amount (Shipping) : $<span id="product_total_{{$product_data['sku']}}" data-brand-id="{{ $product_data['user_id'] or '' }}">{{isset($product_data['minimum_amount_off'])?num_format($product_data['minimum_amount_off']):0}}</span></div><br>

                            <div>Min order amount(Discount) : $<span id="product_total_{{$product_data['sku']}}" data-brand-id="{{ $product_data['user_id'] or '' }}">{{isset($product_data['off_type_amount'])?num_format($product_data['off_type_amount']):0}}</span></div><br>


                            <div>Total Price : $<span id="product_total_{{$product_data['sku']}}" data-brand-id="{{ $product_data['user_id'] or '' }}">{{isset($product_data['wholesale_price'])?num_format($product_data['wholesale_price']):0}}</span></div>

                           </div>
                          

                          <div class="main-qnt-bottom">
                              <div class="qty-shop-bg">Qty : </div>
                              <select name="pro_qty" class="pro_qty"  data-pro-id="{{isset($product_data['id'])?base64_encode($product_data['id']):''}}">
                                  @for($i=1;$i
                                  <=5000;$i++) <option @if(isset($product_data[ 'qty']) && $product_data[ 'qty']==$i) selected="" @endif value="{{$i}}">{{$i}}</option>
                                      @endfor
                              </select>   
                          </div>
                          <div class="clearfix"></div>
                         </div>
                          </div>  
                          </div>
                          @endforeach
                          </div>
                        </div>
                        
                          
                        @endif

                      @endforeach
                     @endif

                     @if(isset($final_arr) && count($final_arr) > 0)
                        <div class="btn-next-find-pro">
                             {{-- <a href="{{$module_url_path}}/order_summary/{{base64_encode($order_no) }}" class="btn btn-block btn-outline btn-rounded btn-success btn-blk btn-space">Next</a> --}}

                             <input type="button" class="btn btn-block btn-outline btn-rounded btn-success" name="" value="Next" onclick="return checkPriceSatisfaction();">
                             </div>
                        </div>
                     @endif
                  
                  
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>

@include('representative.leads._model_product_detils')    

<script src="{{url('/')}}/assets/js/gallery.min.js"></script>
<script>

    $(function() {

        $(".closemodal").on("click", function() {
            $('.vendor-profile-main-modal').fadeOut();
        });
        $(".bigbutton").on("click", function() {
            $(".modal-mask, .modal-popup").fadeIn();
            $('.vendor-profile-main-modal').fadeIn();
            $(".modal-popup").animate({

                left: '10%'
            }, 'slow', function() {
                $(".modal-popup").animate({
                    'top': '5%'
                }, 200, "swing", function() {});
            });
        });
        $(document).on("keydown", function(event) 
        {
            if (event.keyCode === 27) {
                $(".modal-popup").animate({
                    width: '5%',
                    left: '50%'
                }, 'slow', function() {
                    $(".modal-mask, .modal-popup").fadeOut();
                });
            }

        });
    });

</script>
<script type="text/javascript">
    var module_url_path = "{{ $module_url_path or '' }}";
    var retailer_id     = "{{$retailer_id or '0'}}";
    var lead_id         = "{{ $order_no or 0}}";
    var table_module = false;
  
    $(document).ready(function() {

        table_module = $('#table_module').DataTable({language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },  
            processing: true,
            serverSide: true,
            autoWidth: false,
            bFilter: false,
            ajax: {
                'url': '{{ $module_url_path.'/get_product_list'}}',

                'data': function(d) {

                    d['column_filter[q_product_name]']    = $("input[name='q_product_name']").val()
                    d['column_filter[q_maker_name]']      = $("input[name='q_maker_name']").val()
                    d['column_filter[q_wholesale_price]'] = $("input[name='q_wholesale_price']").val()
                    d['column_filter[q_retail_price]']    = $("input[name='q_retail_price']").val()
                    d['column_filter[lead_id]']           = lead_id

                }
            },

            columns: [{
                    data: 'product_name',
                    "orderable": true,
                    "searchable": false
                }, {
                    data: 'user_name',
                    "orderable": true,
                    "searchable": false
                }, {
                    render(data, type, row, meta) {
                            return '<i class="fa fa-dollar"></i>'+(+row.unit_wholsale_price).toFixed(2);
                        },
                        "orderable": false, "searchable": false
                }, 
                /*{
                    render(data, type, row, meta) {
                            return '<i class="fa fa-dollar"></i>'+(+row.retail_price).toFixed(2);
                        },
                        "orderable": false, "searchable": false
                },*/

                /*{data: 'total_wholesale_price', "orderable": true, "searchable":false},      */
                // {data: 'created_at', "orderable": true, "searchable":false},      
                {
                    render(data, type, row, meta) {
                            return `<button type="button" data-produt-id="` + btoa(row.id) + `" onclick="product_details(this)" class="bigbutton btn btn-success btn-circle"><i class="fa fa-plus"></i> </button>`;
                        },
                        "orderable": false, "searchable": false
                }
            ]
        });

        $('input.column_filter').on('keyup click', function() {
            filterData();
        });

        $('#table_module').on('draw.dt', function(event) {
            var oTable = $('#table_module').dataTable();
            var recordLength = oTable.fnGetData().length;
            $('#record_count').html(recordLength);

            var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
            $('.js-switch').each(function() {
                new Switchery($(this)[0], $(this).data());
            });

            $("input.toggleSwitch").change(function() {
                statusChange($(this));
            });
        });

                  /*  <td><input type="text" name="q_retail_price" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>*/
        /*search box*/
        $("#table_module").find("thead").append(`<tr>
                    <td><input type="text" name="q_product_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_maker_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td><input type="text" name="q_wholesale_price" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
                    <td>

                    </td>  

                </tr>`);

        $('input.column_filter').on('keyup click', function() {
            filterData();
        });

    });

    function filterData() {
        table_module.draw();
    }

    function product_details(reff) 
    {
      
        var module_url_path = "{{$module_url_path}}";
        var product_id = $(reff).attr('data-produt-id');
        $.ajax({

            url: module_url_path + '/get_product_details/' + product_id,
            type: "GET",
            dataType: "json",
            success: function(response) 
            {
                if(response.status=="SUCCESS")
                {
                    var product_arr = response.arr_data;
                    
                    var product_id   = product_arr.id;
                    var product_name = product_arr.product_name;
                    var product_description = product_arr.description;
                    var product_retail_price = (+product_arr.retail_price).toFixed(2);
                    var product_unit_wholsale_price = (+product_arr.unit_wholsale_price).toFixed(2);
                    var maker_id = product_arr.user_id;
                    var first_sku_no = product_arr.product_details[0].sku;  
                    var commission   = response.comission;

                    $("#maker_id").val(maker_id);
                    $("#product_id").val(product_id);
                    $("#popup_product_name").text(product_name);
                    $("#popup_retail_price").text(product_retail_price);
                    $("#popup_wholesale_price").text(product_unit_wholsale_price);
                    $("#total_wholesale_price").text(product_unit_wholsale_price);
                    $("#popup_product_description").html(product_description);
                    $("#sku_num").val(first_sku_no);
                    $("#commission").val(commission);

                    /*-----------------*/
                     $('#first_order_minimum').html(product_arr.shop_settings.first_order_minimum+' Minimum');
                     $('#company_name').html(product_arr.maker_details.company_name);

              

                     $("#company_name").attr("href",'{{url('/')}}/vendor-details?vendor_id='+btoa(product_arr.maker_details.user_id));
                     

                     $("#cat_search").attr("href",'{{url('/')}}/search?search_type=category&category-id='+btoa(product_arr.category_details.id));


                     $('#category_name').html(product_arr.category_details.category_name);

                      if(product_arr.product_details[0].option)
                      {
                          var opt_value = product_arr.product_details[0].option;  
                             
                      }

                      html = '';

                     if(product_arr.product_details[0].option_type == 1)
                     {
                        html +=' <li><div class="li-left" id="color">Color :</div> <div class="option_value newoptionvalue">'+product_arr.product_details[0].color+'</div> <div class="clearfix"></div> </li>';
                       /* $('#scent').html(opt_value);*/
                     }
                     else if(product_arr.product_details[0].option_type == 2)
                     {
                        html+=' <li><div class="li-left" id="scent">Scent :</div><div class="option_value newoptionvalue">'+opt_value+'</div><div class="clearfix"></div></li>';

                        /* $('#size').html(opt_value);*/
                     }
                     else if(product_arr.product_details[0].option_type == 3)
                     {
                        html+='<li><div class="li-left" id="material">Material :</div><div class="option_value newoptionvalue"></div>'+opt_value+'<div class="clearfix"></div></li>';
                        //$('#material').html(opt_value);
                     }
                     else
                     {
                       $('#none_of_these').html('');
                     }

                     $('#weight').html(product_arr.product_details[0].weight);
                     $('#height').html(product_arr.product_details[0].height);
                     $('#length').html(product_arr.product_details[0].length);
                     $('#width').html(product_arr.product_details[0].width);


                    /*-----------------*/

                    show_product_model();
                    var gallery_html = "";
                    var image_html   = "";
                    $.each(product_arr.product_details, function( key, value ) 
                    {
                        image_html+=`<img src="`+SITE_URL+`/storage/app/`+value.image+`" data-medium-img="`+SITE_URL+`/storage/app/`+value.image+`" data-big-img="`+SITE_URL+`/storage/app/`+value.image+`" data-imgsku="`+value.sku+`" class="imgsku">`;
                        
                    });

                    gallery_html+=`<div class="prod-carousel">`+image_html+`</div>`;
                   
                    $("#example1").append(gallery_html);   
                    
                    // $('#example1').rwaltzGallery('reinit');  
                    // $("#example1").reload();
                  
                    $('#example1').rwaltzGallery({
                        openGalleryStyle: 'transform',
                        changeMediumStyle: true
                    });    

                }
                else
                {                
                  swal('Error','Something went wrong, Please try again','error');
                } 
            }
        });
    }

    function show_product_model()
    {
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
</script>


<script>
    $(document).ready(function() 
    {
        $("#btn_submit").click(function()
        {
            if($('#form_lead_product').parsley().validate()==false) return;

            var formdata              = new FormData($("#form_lead_product")[0]);
            var retail_price          = $("#popup_retail_price").text();            
            var wholesale_price       = $("#popup_wholesale_price").text();              
            var total_retail_price    = $("#total_retail_price").text();
            var total_wholesale_price = $("#total_wholesale_price").text();
            var comission             = $("#commission").val();

            formdata.append('retail_price',parseFloat(retail_price).toFixed(2));
            formdata.append('wholesale_price',parseFloat(wholesale_price).toFixed(2));
            formdata.append('total_wholesale_price',parseFloat(total_wholesale_price).toFixed(2));
            formdata.append('total_retail_price',parseFloat(total_retail_price).toFixed(2));
            formdata.append('commission',comission);
            formdata.append('order_no','{{$order_no or 0}}');

            $.ajax({
                  url: '{{$module_url_path}}/store_lead',
                  type:"POST",
                  data: formdata,
                  dataType:'json',
                  contentType:false,
                  processData:false,
                  beforeSend : function()
                  {
                    showProcessingOverlay();
                    $('#btn_submit').prop('disabled',true);
                    $('#btn_submit').html('Updating... <i class="fa fa-spinner fa-pulse fa-2x fa-fw"></i>');
                  },
                  success:function(response)
                  {
                      hideProcessingOverlay();
                      $(".modal-mask, .modal-popup").fadeIn();
                      $('#form_lead_product')[0].reset();
                      
                        if(response.status == 'SUCCESS')
                        { 
                          swal({
                                  title: 'Success',
                                  text: response.description,
                                  type: 'success',
                                  confirmButtonText: "OK",
                                  closeOnConfirm: true
                               },
                              function(isConfirm,tmp)
                              {    
                                if(isConfirm==true)
                                {
                                   window.location.href = response.next_url;
                                   // location.reload(true);
                                }
                              });
                        }
                        else
                        {                
                            swal({
                                  title: 'Error',
                                  text: response.description,
                                  type: 'error',
                                  confirmButtonText: "OK",
                                  closeOnConfirm: true
                               },
                              function(isConfirm,tmp)
                              {                       
                                if(isConfirm==true)
                                {
                                   window.location.href = response.next_url;
                                }
                              });
                        }  

                  }    
            });  
        })

        $(".vertical-spin").TouchSpin({
            verticalbuttons: true,
            verticalupclass: 'ti-plus',
            verticaldownclass: 'ti-minus',
            min: 1,
            max: 10,

        }).on('touchspin.on.startspin', function (event) 
        {

            var qty = $("#vertical_spin").val();
            var wholesale_price = $("#popup_wholesale_price").text();
            
            var retail_price    = $("#popup_retail_price").text();
            
            var total_wholesale_price = 0;
           
            var total_retail_price    = 0;
            
            total_wholesale_price = parseFloat(qty) * parseFloat(wholesale_price);
            total_retail_price    = parseFloat(qty) * parseFloat(retail_price);
            
            $("#total_retail_price").text(total_retail_price.toFixed(2));
            $("#total_wholesale_price").text(total_wholesale_price.toFixed(2));
            
        });
    });

    $('.pro_qty').on('change', function() 
    {
        var qty     = this.value;
        var pro_id  = $(this).attr("data-pro-id");
        var order_no = "{{ $order_no or 0}}";
        
        var url = '{{url('/')}}/representative/leads/update_product_qty';

        $.ajax({
            url: url,
            data: {
                    pro_id: pro_id,
                    qty: qty,
                    order_no:order_no
            },
            method: 'GET',
            beforeSend: function() {
                showProcessingOverlay();
            },
            success: function(response) {
                hideProcessingOverlay();

                window.location.reload();
              /*  if (response.status == "SUCCESS") {
                    var total_price = response.total_price.toFixed(2);
                    
                    var total_wholesale_price = response.total_wholesale_price.toFixed(2);
                    var subtotal = response.subtotal.toFixed(2);
                    var wholesale_subtotal = response.wholesale_subtotal.toFixed(2);

                    $("#product_total_" + atob(sku_no)).text(total_price);
                    $("#subtotal").text(subtotal);
                    $("#product_wholesale_total" + atob(sku_no)).text(total_wholesale_price);
                    $("#wholesale_subtotal").text(wholesale_subtotal);

                    $("#cart_total").text(subtotal);
                    $("#wholesale_total").text(wholesale_subtotal);

                    window.location.reload();
                }*/
            }
        })
    });


    function checkPriceSatisfaction()
    {

       /* Iterate All Products */
        var _arrProductRef = [];
        var _brandIdWiseSubTotal = {};
        var _brandIdWiseDetails = {};
        var _isValidForQuotes = true;
        var _minBrandMsg = "";

        if($('[id^=product_total_]').length > 0)
        {
            _arrProductRef = $('[id^=product_total_]');
        }
        else if($('[id^=product_wholesale_total]').length > 0)
        {
            _arrProductRef = $('[id^=product_wholesale_total]');   
        }


        /* Get Brand Details */
        if($('[data-brand]').length > 0)
        {
            var _arrBrand  = $('[data-brand]');

            $.each(_arrBrand,(_index,_elem) => {
                var _tmpBrandId = $(_elem).attr("data-brand");                
                var _tmpBrandMin = $(_elem).attr("data-brand-min");                
                var _tmpBrandName = $(_elem).attr('data-name');

                _brandIdWiseDetails[_tmpBrandId] = {};               
                _brandIdWiseDetails[_tmpBrandId]['min'] = _tmpBrandMin;  
                _brandIdWiseDetails[_tmpBrandId]['name'] = _tmpBrandName;               
            });
        }


        /* Brand ID wise SubTotal */
        $.each(_arrProductRef,(_index,_elem) => {
            var _tmpTotal = parseFloat($(_elem).html());

           
            var _tmpBrandId = $(_elem).attr("data-brand-id");
            if(_brandIdWiseSubTotal[_tmpBrandId] == undefined){
                _brandIdWiseSubTotal[_tmpBrandId] = 0;
            } 

            _brandIdWiseSubTotal[_tmpBrandId]+=_tmpTotal;
        });
    
        _minBrandMsg+='<ul>';     
        for(let _tmpBrandId in _brandIdWiseSubTotal)
        {
            if(_brandIdWiseDetails[_tmpBrandId] != undefined)
            {
            
               if(_brandIdWiseSubTotal[_tmpBrandId] < _brandIdWiseDetails[_tmpBrandId].min )   
               {
                    _isValidForQuotes = false;

                    _minBrandMsg+='<li> <b>'+_brandIdWiseDetails[_tmpBrandId].name+' </b>: Minimum Required $'+_brandIdWiseDetails[_tmpBrandId].min+', Current Subtotal: $ '+_brandIdWiseSubTotal[_tmpBrandId]+'  </li>'
               }
            }
        }
        _minBrandMsg+='</ul>';

        if(_isValidForQuotes == false)
        {
            swal({
                    title: "Note",
                    text: "Please make sure cart total for following brands satisfies with min. amount<br>"+_minBrandMsg,
                    type: "warning",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                });
            return; 
        }
        else
        {
          var url = '{{$module_url_path}}/order_summary/{{$order_no}}';
          location.href=url;
        }
    }
</script>

@stop