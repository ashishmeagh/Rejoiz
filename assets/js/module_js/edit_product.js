







function edit_product(product_id,sku_no = false) {
    
    if (sku_no != 'undefined' && sku_no != null && sku_no != '') {
        var url = module_url_path + '/product_details/' + btoa(product_id) + '/' + btoa(sku_no);
    } else {
        var url = module_url_path + '/product_details/' + btoa(product_id);
    }


    $("#edit-sec-tabs").tabs({
        disabled: [2, 3]
    });

    $.ajax({
        url: url,
        type: "GET",
        dataType: 'json',
        success: function(response) {
            if (response.status == 'success') {
                var product_arr = response.product_arr;

                $('select[name^="brand"] option[value="' + response.product_arr.brand + '"]').attr("selected", "selected");

                $('[name="product_id"]').val(product_arr.id);
                // $("#edit-sec-tabs").tabs({// disabled: [1,2,3]

                // });
                if (response.product_arr.product_complete_status == 4)

                {
                    $("#edit-sec-tabs").tabs("enable", 3);
                    $("#edit-sec-tabs").tabs("enable", 2);
                    $("#edit-sec-tabs").tabs("enable", 1);
                    $("#edit-sec-tabs").tabs("option", "active", 4);

                } else if (response.product_arr.product_complete_status == 3) {

                    $("#edit-sec-tabs").tabs("enable", 2);
                    $("#edit-sec-tabs").tabs("enable", 1);
                    $("#edit-sec-tabs").tabs("enable", 3);
                    $("#edit-sec-tabs").tabs("option", "active", 3);
                } else if (response.product_arr.product_complete_status == 2) {

                    $("#edit-sec-tabs").tabs("enable", 1);
                    $("#edit-sec-tabs").tabs("enable", 2);
                    $("#edit-sec-tabs").tabs("option", "active", 2);
                } else if (response.product_arr.product_complete_status == 1) {

                    $("#edit-sec-tabs").tabs("enable", 0);
                    $("#edit-sec-tabs").tabs("enable", 1);
                    $("#edit-sec-tabs").tabs("option", "active", 1);
                }
                //assign values to perticular field

                if (product_arr.product_image_thumb != "null" && product_arr.product_image_thumb != '' && product_arr.product_image_thumb != "undefined") {

                    $('#old_product_image_thumb').val(product_arr.product_image_thumb);


                } else {

                    $('#old_product_image_thumb').val(product_arr.product_image);
                }



                $('#old_product_image').val(product_arr.product_image);
                $('#old_product_is_active').val(product_arr.is_active);

                // alert(product_arr.shipping_type);
                if (product_arr.product_image_thumb != null && product_arr.product_image_thumb != '') {


                    var pro_thumb_img = /[^/]*$/.exec(product_arr.product_image_thumb)[0];

                    $('#old_product_primary_image').attr('data-default-file', SITE_URL + '/storage/app/product_image/product_img_thumb/' + pro_thumb_img);
                } else {
                    $('#old_product_primary_image').attr('data-default-file', SITE_URL + '/storage/app/' + product_arr.product_images.product_image);
                }
                $('#old_product_name').val(product_arr.product_name);
                $('#old_shipping_charges').val(product_arr.shipping_charges);
                if (product_arr.shipping_type != "") {
                    $('#old_shipping_type').val(product_arr.shipping_type);
                }

                if (product_arr.prodduct_dis_type != "") {
                    $("#old_product_discount").val(product_arr.prodduct_dis_type);
                }

                if (product_arr.prodduct_dis_type == 1) {
                    $('#old_product_discount_min_amount').html(
                        '<input type="text" name="free_product_dis_min_amount" class="form-control" placeholder="Enter minimum product discount amount" id="old_product_discount_min_amount" min="0" data-parsley-trigger="keyup" data-parsley-type="number">'
                    )

                    $('#product_old_percent_off').html(
                        '<input type="text" class="form-control" placeholder="Enter Product % Off" name="product_old_percent_off" id="product_old_percent_off" data-parsley-required="true" data-parsley-type="number" min="0" data-parsley-trigger="keyup" data-parsley-type="number">')

                    $("#old_product_discount").trigger("change");

                    $('#old_product_discount_min_amount').val((+product_arr.product_dis_min_amt).toFixed(2));
                    $('#product_old_percent_off').val((+product_arr.product_discount).toFixed(2));

                } else if (product_arr.prodduct_dis_type == 2) {
                    $('#old_product_discount_min_amount').html(
                        '<input type="text" name="free_product_dis_min_amount" class="form-control" placeholder="Enter minimum product discount amount" id="old_product_discount_min_amount" min="0" data-parsley-trigger="keyup" data-parsley-type="number">'
                    )

                    $('#product_old_dollar_off').html('<input type="text" class="form-control" placeholder="Enter Product $ Off" name="product_old_dollar_off" id="product_old_dollar_off" data-parsley-required="true" data-parsley-type="number" min="0" data-parsley-trigger="keyup" data-parsley-type="number">')

                    $("#old_product_discount").trigger("change");
                    $('#old_product_discount_min_amount').val((+product_arr.product_dis_min_amt).toFixed(2));
                    $('#product_old_dollar_off').val((+product_arr.product_discount).toFixed(2));

                    // console.log(product_arr.product_dis_min_amt,product_arr.product_discount);

                }




                // if (product_arr.shipping_type == 1) {
                //     $('#shipping_amounts').html(
                //         '<div class="col-md-6"><label  for="old_free_ship_min_amount">Minimum amount on shipping ($)</label><i class="red"></i><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter minimum free shipping amount"  id="old_free_ship_min_amount" data-parsley-type="number" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div><div class="clearfix"></div>');
                // }

                // if (product_arr.shipping_type == 2) {


                //     $('#shipping_amounts').html(
                //         '<div class="col-xs-12 col-md-12 col-sm-12 col-lg-6 form-group"><label  for="old_free_ship_min_amount">Minimum amount on shipping ($)</label><div ><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter minimum free shipping amount"  id="old_free_ship_min_amount" data-parsley-type="number" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div></div></div>         <div class="col-xs-12 col-md-12 col-sm-12 col-lg-6"><label  for="old_percent_off">% Off <i class="red">*</i></label><div ><input type="text" class="form-control" placeholder="Enter % Off" name="old_%_off" id="old_percent_off" data-parsley-required="true" data-parsley-required="Please enter % Off"data-parsley-type="number" min="0" data-parsley-trigger="keyup" /></div></div></div><div class="clearfix">');
                //     $('#old_percent_off').val(product_arr.off_type_amount);
                // }

                // if (product_arr.shipping_type == 3) {



                //     $('#shipping_amounts').html(
                //         '<div class="col-md-6"><label  for="old_free_ship_min_amount">Minimum amount on shipping ($)</label><div ><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter minimum free shipping amount" id="old_free_ship_min_amount" data-parsley-type="number" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div></div></div>   <div class="col-md-6"><label  for="old_doller_off">$ Off <i class="red">*</i></label><div ><input type="text" class="form-control" placeholder="Enter $ Off" name="old_$_off" id="old_doller_off" data-parsley-required="true" data-parsley-required="Please enter $ Off" data-parsley-type="number" min="0" data-parsley-trigger="keyup" /></div></div></div><div class="clearfix">');
                //     $('#old_doller_off').val(product_arr.off_type_amount);
                // }
                $('#old_free_ship_min_amount').val(product_arr.minimum_amount_off);

                //append subcategories id to hiddden field this is only for show subcategories selected
                $('#subcat_id').val(product_arr.subcat_id);
                $('#third_subcat_id').val(product_arr.third_subcat_id);
                $('#fourth_subcat_id').val(product_arr.fourth_subcat_id);

                //add add product image
                if (product_arr.product_image_thumb != null && product_arr.product_image_thumb != '') {

                    let prod_primary_img = '';
                    prod_primary_img = `<input type="file" name="product_primary_image" 
                                        id="old_product_primary_image" 
                                        class="form-control dropify"  
                                        placeholder="Enter Product Name" 
                                        data-parsley-errors-container="#err_primary_product_img" 
                                        data-default-file="` + SITE_URL + '/storage/app/product_image/product_img_thumb/' + pro_thumb_img + `" 
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                        data-max-file-size="2M" 
                                        data-errors-position="outside" 
                                        data-show-remove="false"  
                                       /> `;


                    $('#product-primary-img').append(prod_primary_img);
                } else {

                    let prod_primary_img = '';

                    prod_primary_img = `<input type="file" name="product_primary_image" 
                                        id="old_product_primary_image" 
                                        class="form-control dropify"  
                                        placeholder="Enter Product Name" 
                                        data-parsley-errors-container="#err_primary_product_img" 
                                        data-default-file="` + SITE_URL + '/storage/app/' + product_arr.product_images.product_image + `" 
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                        data-max-file-size="2M" 
                                        data-errors-position="outside"
                                        data-show-remove="false" 
                                       />`;
                    $('#product-primary-img').append(prod_primary_img);

                }
                /*                $('#product-primary-img').append(prod_primary_img);
                 */

                if (product_arr.is_best_seller == 1) {
                    $('#old_is_best_seller').prop('checked', true);
                }

                //tinyMCE.get('old_product_ingrediants').setContent(product_arr.ingrediants);
                //$('#old_case_qty').val(product_arr.available_qty);
                $('#old_case_qty').val(product_arr.case_quantity);
                $('#old_unit_wholsale_price').val((+product_arr.unit_wholsale_price).toFixed(2));
                $('#old_retail_price').val((+product_arr.retail_price).toFixed(2));
                $('#old_product_ingrediants').val(product_arr.ingrediants);
                $('#old_restock_days').val(product_arr.restock_days);
                //tinymce.get('old_product_description').setContent(product_arr.description);


                // console.log(">>>>>>>>"+product_arr.description);


                // tinyMCE.get('old_product_description').setContent(product_arr.description);

                if (product_arr.is_tester_available == 1) {
                    $('#old_is_tester_available').prop('checked', true);
                }
                $('#old-item-details-frm').parsley().refresh();
                $('#edit_item_product_id').parsley().validate();

                // if()
                // {
                // $('#old_optionName').prop('checked',true);                  
                // }

                $('#old-item-details-frm').parsley().refresh();
                styleSection = '';

                var prod_min_qty = 0;
                var Quantity = 0;

                var row = $("#row").val();

                //add product style and dimension
                if (response.product_arr.product_details.length > 0) {

                    $(response.product_arr.product_details).each(function(index, style_details) {
                        row++;
                        //show selected option
                        if (style_details.inventory_details == null && style_details.inventory_details == undefined) { //alert("tru");
                            var Quantity = 0;
                        } else {
                            var Quantity = style_details.inventory_details.quantity;

                        }

                        if (style_details.product_min_qty == null && style_details.product_min_qty == undefined) {
                            var prod_min_qty = 0;
                        } else {
                            var prod_min_qty = style_details.product_min_qty;
                        }

                        var color_selected = scent_selected = size_selected = material_selected = '';
                        switch (style_details.option_type) {
                            /*  case 0:
                                color_selected = "selected";
                                break;*/
                            case 1:
                                scent_selected = "selected";
                                break;
                            case 2:
                                size_selected = "selected";
                                break;
                            case 3:
                                material_selected = "selected";
                                break;
                        }

                        $('#old_optionName').html(`
                                                 <option value="1" ` + scent_selected + `>Scent</option>
                                                 <option value="2" ` + size_selected + `>Size</option>
                                                 <option value="3" ` + material_selected + `>Material</option>`);

                        /* console.log(style_details.image);*/

                        if (typeof(style_details.image_thumb) == '' || typeof(style_details.image_thumb) == 'undefined') {
                            var default_img = SITE_URL + '/storage/app/product_image/' + style_details.image;
                        } else {
                            var default_img = SITE_URL + '/storage/app/' + style_details.image;
                        }
                        styleSection += `<tr class="pro-add-tr">
                                         <td class="pro_add_edit_style_dimension">
                                            <div class="img-shop-tbl nw-in-shp">
                                            <input type="hidden" name="db_product_image[` + style_details.id + `]" value="` + SITE_URL + '/storage/app/product_image/product_img_thumb/' + style_details.image_thumb + `">
                                               <input type="file" 
                                                  class="form-control dropify" 
                                                  name="old_product_image[` + style_details.id + `]"
                                                data-default-file="` + default_img + `"
                                                data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                                data-max-file-size="2M" 
                                                data-errors-position="outside"
                                                data-show-remove="false"
                                                id="product_img_` + row + `">
                                                <input type="hidden" name="db_product_image_original[` + style_details.id + `]" value="` + style_details.image + `">
                                               
                                            </div>
                                         </td>
                                        
                                         <td class="parsley_error_sku_inventory">
                                              <div class="row">
                                              <input type="text" class="form-control check-dup" placeholder="SKU" data-parsley-required="true" data-parsley-required-message="Please enter sku no" name="old_sku[` + style_details.id + `]" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="` + remote_url + '/' + style_details.product_id + `"
                                              data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "` + csrf_token + `" }}'   data-parsley-remote-message="SKU no already exists" value ="` + style_details.sku + `" id="sku_` + row + `"> 
                                          <input type="hidden" value = ` + style_details.product_id + ` id ="pro_id"> 
                                              </div>              
                                         </td>

                                          <td>
                                              <div class="row">
                                              <input type="text" class="form-control" placeholder="Min Quantity" name="old_min_quantity[` + style_details.id + `]" data-parsley-required="true" data-parsley-required-message="Please enter minimum quantity" data-parsley-type="digits" data-parsley-type-message="Please enter valid minimum quantity" value ="` + prod_min_qty + `" min="1" id="min_quantity_` + row + `" data-row="` + row + `" onkeyup="checkMinQuantity(this);">
                                              <span class="red" id="quantity_error_msg_` + row + `"></span>
                                              </div>
                                          </td>
                                         
                                         <td class="parsley_error_sku_inventory">
                                            <div class="row">
                                            <input type="text" class="form-control" placeholder="Inventory" name = "old_quantity[` + style_details.id + `]" data-parsley-required="true" data-parsley-type="digits" data-parsley-required-message="Please enter inventory" data-parsley-type-message="Please enter valid inventory" value="` + Quantity + `" id="quantity_` + row + `" data-row="` + row + `" onkeyup="checkInventory(this);" >
                                           <span class="red" id="inventory_error_msg_` + row + `"></span>
                                            </div>
                                         </td>
                                        
                                         <td>
                                            <div class="row">
                                            <textarea class="form-control custom-text-area" placeholder="Enter Description" rows="1" cols="20" name="old_sku_product_description[` + style_details.id + `]" id="sku_product_description_` + row + `" 
                                           >` + (style_details.sku_product_description != null ? style_details.sku_product_description : '') + `</textarea>
                                            </div>
                                         </td>
                                         <td>`;
                        if (index == 0) {
                            styleSection += `<button type="button" onclick="editMore()" class="btn btn-success btn-circle"><i class="fa fa-plus"></i> </button>`;
                        } else {
                            styleSection += `<a href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip"  onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>`;
                        }

                        styleSection += `</td>
                                       </tr>`;

                    });
                    $("#edit-style-frm").parsley().refresh();

                    row++;

                    $("#row").val(row);

                } 
                else
                {
                  $('#old_optionName').html(`<option value="0">Color</option>
                                             <option value="1">Scent</option>
                                             <option value="2">Size</option>
                                             <option value="3">Material</option>`);

                    var newRow = '';
                    newRow += `<tr class="pro-add-tr">
                                 <td class="pro_add_edit_style_dimension">
                                    <div class="img-shop-tbl nw-in-shp">
                                       <input type="file" 
                                        class="form-control dropify" 
                                        name="new_product_image[]"
                                        data-default-file=""
                                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                        data-max-file-size="2M" 
                                        data-errors-position="outside"
                                        data-parsley-required="true"
                                        data-parsley-required-message="Please select image"
                                        data-parsley-errors-container="#err_primary_product_img"
                                        data-show-remove="false"
                                        id="product_img_` + row + `">

                                        <span class="text-danger" id="err_primary_product_img"></span>
                                    </div>
                                   
                                 </td>                 

                                  <td class="parsley_error_sku_inventory">
                                    <div class="row">
                                    <input type="text" class="form-control" placeholder="SKU" data-parsley-required="true" data-parsley-required-message="Please enter sku no" name="new_sku[]" data-parsley-trigger="change" data-parsley-whitespace="trim" data-parsley-remote="` + edit_remote_url + '/' + pro_id + `"
                                    data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "` + csrf_token + `" }}'  data-parsley-remote-message="SKU no already exists" id="sku_` + row + `">
                                    </div>
                                  </td>

                                  <td>
                                    <div class="row">
                                    <input type="text" class="form-control" placeholder="Min Quantity" name="new_min_quantity[]" data-parsley-required="true" data-parsley-required-message="Please enter minimum quantity" data-parsley-type="digits" data-parsley-type-message="Please enter valid minimum quantity" min="1" id="min_quantity_` + row + `" data-row="` + row + `" onkeyup="checkMinQuantity(this);">
                                    <span class="red" id="quantity_error_msg_` + row + `"></span>
                                    </div>
                                  </td>
                                
                                  <td class="parsley_error_sku_inventory">
                                    <div class="row">
                                    <input type="text" class="form-control" placeholder="Inventory" name="new_quantity[]" data-parsley-required="true" data-parsley-type="digits" data-parsley-required-message="Please enter inventory" data-parsley-type-message="Please enter valid inventory" id="quantity_` + row + `" data-row="` + row + `" onkeyup="checkInventory(this);"></span>
                                    <span class="red" id="inventory_error_msg_` + row + `"></span>
                                    </div>
                                  </td>

                                  <td>
                                    <div class="row">
                                    <textarea class="form-control custom-text-area" placeholder="Enter Description" rows="1" name="new_sku_product_description[]" id="sku_product_description_` + row + `" ></textarea>
                                    </div>
                                  </td>
    
                                  <td>
                                    <button type="button" onclick="editMore()" class="btn btn-success btn-circle"><i class="fa fa-plus"></i> </button>
                                  </td>

                              </tr>`;

                    $('#style_sec_body').append(newRow);
                    //$("#edit-style-frm").parsley().refresh(); 


                    init_dropify();

                    /*tinymce.init({
                     selector: 'textarea',
                     relative_urls: false,
                     remove_script_host:false,
                     convert_urls:false,
                     plugins: [
                       'advlist autolink lists link image charmap print preview anchor',
                       'searchreplace visualblocks code fullscreen',
                       'insertdatetime media table contextmenu paste code'
                     ],
                     toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                     content_css: [
                       // '//www.tinymce.com/css/codepen.min.css'
                     ]
                   });    */
                }
                //$("#edit-style-frm").parsley().refresh(); 
                //add additional images
                if (product_arr.product_images != null) {


                    if (product_arr.product_images.product_image != '' ||
                        typeof product_arr.product_images.product_image != undefined) {

                        let prouct_img_html = '';
                        prouct_img_html += `
                                            <input type="hidden" name="old_product_image" value="` + product_arr.product_images.product_image + `">
                                            <input type="file" 
                                             id="edit_product_image"
                                             name="product_image" 
                                             class="dropify"
                                             data-default-file="` + SITE_URL + '/storage/app/' + product_arr.product_images.product_image + `"
                                             data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                             data-max-file-size="2M" 
                                             data-errors-position="outside"
                                             data-show-remove="false"/>`;

                        $('#prod-img').append(prouct_img_html);

                        $('#old_additional_prod_image').val(product_arr.product_images.product_image);
                    }
                } else {

                    let prouct_img_html = '';
                    prouct_img_html += `                                            
                                            <input type="file" 
                                             id="edit_product_image"
                                             name="product_image" 
                                             class="dropify"
                                             data-default-file=""
                                             data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                             data-max-file-size="2M" 
                                             data-errors-position="outside"
                                             data-show-remove="false"/>`;

                    $('#prod-img').append(prouct_img_html);
                    $('#old_additional_prod_image').val('');
                }

                if (product_arr.product_images != null) {
                    if (product_arr.product_images.lifestyle_image != '' || typeof product_arr.product_images.lifestyle_image != undefined) {
                        let lifestyle_img_html = '';
                        lifestyle_img_html += `
                                              <input type="hidden" name="old_lifestyle_image" value="` + product_arr.product_images.lifestyle_image + `">
                                              <input type="file" 
                                              id="edit_lifestyle_image" 
                                              name="lifestyle_image" 
                                              class="dropify"
                                              data-default-file="` + SITE_URL + '/storage/app/' + product_arr.product_images.lifestyle_image + `"
                                              data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                              data-max-file-size="2M" 
                                              data-errors-position="outside"
                                              data-show-remove="false"/>`;

                        $('#lifestyle-img').append(lifestyle_img_html);

                        $('#old_lifestyle_image').val(product_arr.product_images.lifestyle_image);
                    }
                } else {
                    let lifestyle_img_html = '';
                    lifestyle_img_html += `                                              
                                              <input type="file" 
                                              id="edit_lifestyle_image" 
                                              name="lifestyle_image" 
                                              class="dropify"
                                              data-default-file=""
                                              data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                              data-max-file-size="2M" 
                                              data-errors-position="outside"
                                              data-show-remove="false"/>`;

                    $('#lifestyle-img').append(lifestyle_img_html);
                    $('#old_lifestyle_image').val('');

                }

                if (product_arr.product_images != null) {
                    if (product_arr.product_images.packaging_image != '' || typeof product_arr.product_images.packaging_image != undefined) {
                        let packaging_img_html = '';
                        packaging_img_html = `
                                              <input type="hidden" name="old_packaging_image" value="` + product_arr.product_images.packaging_image + `">
                                              <input type="file" 
                                               id="edit_packaging_image" 
                                                name="packaging_image" 
                                                class="dropify"
                                                data-default-file="` + SITE_URL + '/storage/app/' + product_arr.product_images.packaging_image + `"
                                               data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                               data-max-file-size="2M" 
                                               data-errors-position="outside"
                                               data-show-remove="false"/>`;

                        $('#packaging-img').append(packaging_img_html);

                        $('#old_packaging_image').val(product_arr.product_images.packaging_image);
                    }
                } else {
                    let packaging_img_html = '';
                    packaging_img_html = `
                                              <input type="file" 
                                               id="edit_packaging_image" 
                                                name="packaging_image" 
                                                class="dropify"
                                                data-default-file=""
                                               data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                                               data-max-file-size="2M" 
                                               data-errors-position="outside"
                                               data-show-remove="false"/>`;

                    $('#packaging-img').append(packaging_img_html);

                    $('#old_packaging_image').val();
                }

                //add category and subcategory



                    $('#old_category_id').val(product_arr.category_id);
                
                // if (product_arr.product_sub_categories.length > 0) {

                //     $('#old_category_id').val(product_arr.product_sub_categories[0].category_id);
                // }

                //display subcategories
                //var category_id = 4;  
                //get_subcategory_for_edit(category_id);


                /*var sub_categories = '';
                if (product_arr.product_sub_categories != '') {
                    if (product_arr.product_sub_categories != null || typeof product_arr.product_sub_categories != undefined) {
                        $(product_arr.product_sub_categories).each(function(index, subcategory) {
                            if (subcategory.subcategory_details != undefined) {
                                sub_categories += `<option value="` + subcategory.subcategory_details.id + `" selected="selected" >` + subcategory.subcategory_details.subcategory_name + `</option>`;
                            }

                        });
                    }
                }*/



                //show_selected_subcategory(sub_categories);  

                //$('#edit_second_category').html(sub_categories);
                // $(".select2").select2();

                $('#edit_second_category').select2();

                $('#style_sec_body').append(styleSection);
                // $("#edit-style-frm").parsley().refresh(); 
                init_dropify();

                /* tinymce.init({
                  selector: 'textarea',
                  relative_urls: false,
                  remove_script_host:false,
                  convert_urls:false,
                  plugins: [
                    'advlist autolink lists link image charmap print preview anchor',
                    'searchreplace visualblocks code fullscreen',
                    'insertdatetime media table contextmenu paste code'
                  ],
                  toolbar: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
                  content_css: [
                    // '//www.tinymce.com/css/codepen.min.css'
                  ]
                });   */
            } else {
                swal('Error', response.description, 'error');
            }
        }
    });
}

function editMore() {
    const rowCount = $('#style_and_diemension_tbl tr').length;
    var pro_id = $('#pro_id').val();

    if (rowCount > 11) {
        swal('Warning', 'Limit exceed! you can add only 10 images ', 'warning');
        return false;
    }

    var row = $("#row").val();

    var row = row++;

    var newRow = '';
  
    newRow += `<tr class="pro-add-tr">

                  <td class="pro_add_edit_style_dimension ">
                    <div class="img-shop-tbl nw-in-shp">
                       <input type="file" 
                        class="form-control dropify" 
                        name="new_product_image[]"
                        data-default-file=""
                        data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                        data-max-file-size="2M" 
                        data-errors-position="outside"
                        data-parsley-required="true"
                        data-parsley-required-message="Please select image"
                        data-parsley-errors-container="#err_product_img"

                        id="product_img_` + row + `">  

                        <span class="text-danger" id="err_product_img"></span>
                     </div>
                  </td>                 
                 
                  <td class="parsley_error_sku_inventory">

                    <div class="row">
                    <input type="text" class="form-control check-dup" placeholder="SKU" data-parsley-required="true" data-parsley-required-message="Please enter sku no" name="new_sku[]" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="` + edit_remote_url + '/' + pro_id + `"
                                    data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "` + csrf_token + `" }}'   data-parsley-remote-message="SKU no already exists" id="sku_` + row + `">
                    </div>
                  </td>

                  <td>
                   <div class="row">
                    <input type="text" class="form-control" placeholder="Min Quantity" name="new_min_quantity[]" data-parsley-required="true" data-parsley-required-message="Please enter minimum quantity" data-parsley-type="digits" data-parsley-type-message="Please enter valid minimum quantity" min="1" id="min_quantity_` + row + `" data-row="` + row + `" onkeyup="checkMinQuantity(this);">
                    <span class="red" id="quantity_error_msg_` + row + `"></span>
                   </div>
                  </td>

                  <td class="parsley_error_sku_inventory">
                   <div class="row">
                    <input type="text" class="form-control" placeholder="Inventory" name="new_quantity[]" data-parsley-required="true" data-parsley-type="digits" data-parsley-required-message="Please enter inventory" data-parsley-type-message="Please enter valid inventory" id="quantity_` + row + `" data-row="` + row + `" onkeyup="checkInventory(this);">
                     <span class="red" id="inventory_error_msg_` + row + `"></span> 
                    </div>
                  </td>
                 
                  <td>
                   <div class="row">
                    <textarea class="form-control custom-text-area" placeholder="Enter Description" rows="1" name="new_sku_product_description[]" id="sku_product_description_` + row + `"></textarea>
                    </div>             
                  </td>  

                  <td>
                    <a href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip"  onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>
                  </td>

              </tr>`;

    $('#style_sec_body').append(newRow);
    //$("#edit-style-frm").parsley().refresh(); 


    init_dropify();



}




$('#old_save_items_details').click(function() {
    tinyMCE.triggerSave();
    /* var description = tinyMCE.editors[$('#old_product_description').attr('id')].getContent();
    if(description!="")
    { 
     if(description.length > 1000)
     {
        $("#err_product_desc_edit").html('Allowed only 1000 characters or fewer.');
        return;
     }

     else 
     {
       $("#err_product_desc_edit").html('');
     }*/


    /* var unit_wholsale_price = $('#old_unit_wholsale_price ').val(); 
     var unit_retail_price   = $('#old_retail_price').val();

     if(unit_wholsale_price!="" && unit_wholsale_price==0)
     {
        $("#err_unit_wholsale_price_edit").html('Unit wholesale price should not be 0.');
        return false;
     } 

      if(unit_retail_price!="" && unit_retail_price==0)
     {
        $("#err_unit_retail_price_edit").html('Unit retail price should not be 0.');
        return false;
     } 

     else
     {
       $("#err_unit_wholsale_price_edit").html('');$("#err_unit_retail_price_edit").html('');
     }*/




    if ($('#old-item-details-frm').parsley().validate() == false) return;


    var form_data = new FormData($("#old-item-details-frm")[0]);

    $(".old-item-details-frm #is_click_on_storeProduct").val('1');
    $(".old-item-details-frm #is_click_on_update_style_and_dimension").val($(".edit-style-frm #is_click_on_update_style_and_dimension").val());   
    $(".old-item-details-frm #is_click_on_store_additional_images").val($(".edit-additional-img-frm #is_click_on_store_additional_images").val());
    $(".old-item-details-frm #is_click_on_update_product_dategory").val($(".edit-category-frm #is_click_on_update_product_dategory").val());

    $.ajax({
        url: module_url_path + '/storeProduct',
        type: "POST",
        data: new FormData($("#old-item-details-frm")[0]),
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            showProcessingOverlay();
        },

        success: function(response) {
            hideProcessingOverlay();
            $("#edit-sec-tabs").removeClass('ui-widget');
            $("#edit-sec-tabs").removeClass('ui-widget-content');
            if (response.status == 'success') {
                //set product id to hidden field
                $('#style_product_id').val(response.product_id);

                //all all form fields
                //$('#item-details-frm')[0].reset();

                swal({
                        title: 'Success',
                        text: response.description,
                        type: 'success',
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(isConfirm, tmp) {
                        if (isConfirm == true) {
                            // $(".nav-link").removeClass('active');                         
                            // $("#style-sec-link").addClass('active');                         
                            // $('#home1').removeClass('in active show');
                            // $('#profile1').addClass('in active show');
                            // $("#edit-sec-tabs").tabs( "option", "active",2);

                            $("#edit-sec-tabs").tabs("enable", 1);
                            // $("#edit-sec-tabs").tabs("enable", 2);
                            $("#edit-sec-tabs").tabs("option", "active", 1);
                            //$('#style-frm').parsley();
                        }
                    });
            } else {
                swal('Error', response.description, 'error');
            }
        }
    });
});

$('#edit_save_style_details').click(function() {
    var c_dup = [];
    var flag = $(this).attr("data-flag");

    $(".check-dup").each(function() {
        if ($(this).val() != "") { c_dup.push($(this).val()); }
    });


    var check = checkIfArrayIsUnique(c_dup);
    if (check == false) {
        swal('Info', "SKU numbers must be unique", 'info');
        return false;
    }

    if(flag == 1) {
        return false;
    }

    function checkIfArrayIsUnique(myArray) {
        return myArray.length === new Set(myArray).size;
    }

  /* 
    var formData = new FormData($("#edit-style-frm")[0]);

    console.log($('input[name=old_product_multiple_image]').val());
    return*/
    if ($('#edit-style-frm').parsley().validate() == false) return;
    if ($('.parsley-error').length > 0) return;
     $(".edit-style-frm #is_click_on_update_style_and_dimension").val('1');
     $(".edit-style-frm #is_click_on_storeProduct").val($(".old-item-details-frm #is_click_on_storeProduct").val());
     $(".edit-style-frm #is_click_on_store_additional_images").val($(".edit-additional-img-frm #is_click_on_store_additional_images").val());
     $(".edit-style-frm #is_click_on_update_product_dategory").val($(".edit-category-frm #is_click_on_update_product_dategory").val());

    $.ajax({
        url: module_url_path + '/updateStyleAndDiemensions',
        type: "POST",
        data: new FormData($("#edit-style-frm")[0]),
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            showProcessingOverlay();
        },
        success: function(response) {
            hideProcessingOverlay();
            $("#edit-sec-tabs").removeClass('ui-widget');
            $("#edit-sec-tabs").removeClass('ui-widget-content');
            if (response.status == 'success') {
                $('#additional_img_product_id').val(response.product_id);
                //all all form fields
                //$('#edit-style-frm')[0].reset();   
                swal({
                        title: 'Success',
                        text: response.description,
                        type: 'success',
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(isConfirm, tmp) {
                        if (isConfirm == true) {
                            // $(".nav-link").removeClass('active');                         
                            // $("#additional-img-sec-link").addClass('active');
                            // $('#profile1').removeClass('in active show');
                            // $('#messages1').addClass('in active show');

                            $("#edit-sec-tabs").tabs("enable", 2);
                            $("#edit-sec-tabs").tabs("enable", 1);

                            $("#edit-sec-tabs").tabs("option", "active", 2);
                        }
                    });
            } else {
                swal('Error', response.description, 'error');
            }
        }
    });
});

$('#edit_additional_img').click(function() {

    if ($('#edit-additional-img-frm').parsley().validate() == false) return;
    $(".edit-additional-img-frm #is_click_on_store_additional_images").val('1');
    $(".edit-additional-img-frm #is_click_on_storeProduct").val($(".old-item-details-frm #is_click_on_storeProduct").val());
     //$(".edit-additional-img-frm #is_click_on_store_additional_images").val($(".edit-style-frm #is_click_on_update_style_and_dimension").val());
     $(".edit-additional-img-frm #is_click_on_update_product_dategory").val($(".edit-category-frm #is_click_on_update_product_dategory").val());
     $(".edit-additional-img-frm #is_click_on_update_style_and_dimension").val($(".edit-style-frm #is_click_on_update_style_and_dimension").val());
    $.ajax({
        url: module_url_path + '/storeAdditionalImages',
        type: "POST",
        data: new FormData($("#edit-additional-img-frm")[0]),
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            showProcessingOverlay();
        },
        success: function(response) {
            hideProcessingOverlay();
            $("#edit-sec-tabs").removeClass('ui-widget');
            $("#edit-sec-tabs").removeClass('ui-widget-content');
            if (response.status == 'SUCCESS') {
                $('#edit-additional-img-frm')[0].reset();

                swal({
                        title: 'Success',
                        text: response.description,
                        type: 'success',
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(isConfirm, tmp) {
                        if (isConfirm == true) {
                            $("#edit-sec-tabs").tabs("enable", 2);
                            $("#edit-sec-tabs").tabs("enable", 1);
                            $("#edit-sec-tabs").tabs("enable", 3);
                            $("#edit-sec-tabs").tabs("option", "active", 3);
                        }
                    });
            } else {
                swal('Error', response.description, 'error');
            }
        }

    });
});

$('#edit_category').click(function() {

    if ($('#edit-category-frm').parsley().validate() == false) return;


    /*-----------------validation for shipping charges---------------------------------*/
    var flag = 0;
    var min_amt_free_shiping = $('#old_free_ship_min_amount').val();
    var product_unit_price = $('#old_unit_wholsale_price').val();
    var ship_percent_off_value = $(".ship_percent_off").val();
    var ship_dolar_off_value = $(".ship_dollar_off").val();
    var shipping_charges = $('#old_shipping_charges').val();

    /* if(parseInt(product_unit_price) > parseInt(min_amt_free_shiping))
     { 
         
       $('#error_min_ship_amt').text('Min amount for getting free shipping should be greater than unit wholesale price');

       flag = 1;
     }*/

    if (ship_percent_off_value > 100) {
        $('#error_ship_per_off_amt').text('% Off amount for getting discount on shipping should be less than 100');
        flag = 1;
    }


    if (parseInt(shipping_charges) < parseInt(ship_dolar_off_value)) {
        $('#error_ship_per_off_amt').text('$ off amount for getting discount on shipping should be less than shipping charges');
        flag = 1;
    }
    /*------------------------------------------------------------------*/


    /*------------validation for product discount ------------------------*/

    var percent_off_product_dis = $('.product_percent_off').val();
    var dolar_off_product_dis = $('.product_dolar_off').val();
    var min_amt_product_dis = $('#old_product_discount_min_amount').val();



    if (parseInt(product_unit_price) > parseInt(min_amt_product_dis)) {

        $('#error_min_product_amt').text('Min amount for getting product discount should be greater than price');
        flag = 1;
    }


    if (percent_off_product_dis > 100) {
        $('#error_per_off_product_amt').text('% off amount for getting product discount should be less than 100');
        flag = 1;
    }

    if (parseInt(product_unit_price) < parseInt(dolar_off_product_dis)) {
        $('#error_per_off_product_amt').text('$ off amount for product discount should be less than price');
        flag = 1;
    }


    if (flag == 1) {
        return false;
    }
    /*-----------------------------------------------------------------------------------*/

     $(".edit-category-frm #is_click_on_update_product_dategory").val('1');

    $(".edit-category-frm #is_click_on_store_additional_images").val($(".edit-additional-img-frm #is_click_on_store_additional_images").val());
    $(".edit-category-frm #is_click_on_storeProduct").val($(".old-item-details-frm #is_click_on_storeProduct").val());
    $(".edit-category-frm #is_click_on_update_style_and_dimension").val($(".edit-style-frm #is_click_on_update_style_and_dimension").val());
     //$(".edit-category-frm #is_click_on_store_additional_images").val($(".edit-style-frm #is_click_on_update_style_and_dimension").val());
     //$(".edit-category-frm #is_click_on_update_product_dategory").val($(".edit-category-frm #is_click_on_update_product_dategory").val());

    $.ajax({
        url: module_url_path + '/update_product_category',
        type: "POST",
        data: new FormData($("#edit-category-frm")[0]),
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            showProcessingOverlay();
        },
        success: function(response) {
            hideProcessingOverlay();

            if (response.status == 'success') {
                $('#edit-category-frm')[0].reset();

                swal({
                        title: 'Success',
                        text: response.description,
                        type: 'success',
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(isConfirm, tmp) {
                        if (isConfirm == true) {
                            location.reload(true);
                        }
                    });
            } else {
                swal('Error', response.description, 'error');
            }
        }

    });
});

//get second level categories
function get_subcategory_for_edit(referance) {
    $("#edit_second_category").select2('val', '');
    $("#edit_third_category").select2('val', '');
    $("#edit_fourth_category").select2('val', '');
    var category_id = referance.val();

    // var category_id = referance;
    var url = SITE_URL + '/get_sub_categories/' + category_id;

    $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var sub_categories = '';
            if (response.status == 'SUCCESS') {
                if (typeof(response.sub_categories_arr) == "object") {
                    let subcat_data = $('#subcat_id').val();

                    let already_selected_subcategories = $.parseJSON(subcat_data);

                    $(response.sub_categories_arr).each(function(index, second_category) {
                        var is_selected = (already_selected_subcategories.indexOf(second_category.id) > -1);

                        if (is_selected == true) {
                            sub_categories += '<option selected="selected" value="' + second_category.id + '">' + second_category.subcategory_name + '</option>';
                        } else {
                            sub_categories += '<option value="' + second_category.id + '">' + second_category.subcategory_name + '</option>';
                        }
                    });
                }
            } else {
                sub_categories += '';
            }

            $('#edit_second_category').html(sub_categories);

            $('#edit_second_category').select2();
        }
    });
}

$('#old_shipping_type').on('change', function() {

    if ($(this).val() == '') {
        $("#shipping_amounts").html('');
    }

    if ($(this).val() == 1) {
        $('#shipping_amounts').html('<div class="col-md-6"><label  for="old_free_ship_min_amount">Min Order Amount to get shipping discount ($)<i class="red">* </i></label><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter Min Order Amount to get shipping discount"  id="old_free_ship_min_amount" data-parsley-required="true" data-parsley-required-message="Please enter Min Order Amount to get shipping discount" data-parsley-type="number" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/><span class="red" id="error_min_ship_amt"></span></div><div class="clearfix"></div>')
    }
    if ($(this).val() == 2) {
        $('#shipping_amounts').html('<div class="col-md-6"><label  for="old_free_ship_min_amount">Min Order Amount to get shipping discount ($)</label><div ><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter Min Order Amount to get shipping discount"  id="old_free_ship_min_amount"   min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div><span class="red" id="error_min_ship_amt"></span></div></div>         <div class="col-md-6"><label  for="old_percent_off">% Off <i class="red">*</i></label><div ><input type="text" class="form-control ship_percent_off" placeholder="Enter % Off" name="old_%_off" id="old_%_off" data-parsley-required="true" data-parsley-required-message="Please enter % Off" data-parsley-type="number" data-parsley-type-message="Please enter valid % Off" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/><span class="red" id="error_ship_per_off_amt"></span></div></div></div><div class="clearfix">')
    }
    if ($(this).val() == 3) {
        $('#shipping_amounts').html('<div class="col-md-6"><label  for="old_free_ship_min_amount">Min Order Amount to get shipping discount ($)</label><div ><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter Min Order Amount to get shipping discount" id="old_free_ship_min_amount"  min="0" data-parsley-trigger="keyup" data-parsley-type="number"/><span class="red" id="error_min_ship_amt"></span></div></div></div>   <div class="col-md-6"><label  for="old_percent_off">$ Off <i class="red">*</i></label><div ><input type="text" class="form-control ship_dollar_off" placeholder="Enter $ Off" name="old_$_off" id="old_$_off" data-parsley-required="true" data-parsley-required-message="Please enter $ Off" data-parsley-type="number" data-parsley-type-message="Please enter valid $ Off" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/><span class="red" id="error_ship_per_off_amt"></span></div></div></div><div class="clearfix">')
    }
})

$('#old_product_discount').on('change', function() {
    //alert("ok")
    // if ($(this).val() == 1) {
    //   $('#product_dis_amt').html('<div class="col-md-6"><label  for="old_free_ship_min_amount">Minimum Amount ($) <i class="red">* </i></label><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter minimum free shipping amount"  id="old_free_product_min_amount" data-parsley-required="true" data-parsley-type="number"/></div><div class="clearfix"></div>')
    // }
    if ($(this).val() == '') {
        $('#product_dis_amt').html('');
    }
    if ($(this).val() == 1) {
        $('#product_dis_amt').html('<div class="col-xs-12 col-md-12 col-sm-12 col-lg-6 form-group"><label  for="old_free_product_min_amount">Min Order Amount to get product discount ($)</label><div ><input type="text" name="free_product_dis_min_amount" class="form-control" placeholder="Enter Min Order Amount to get product discount" id="old_product_discount_min_amount" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div><span class="red" id="error_min_product_amt"></span></div></div>  <div class="col-xs-12 col-md-12 col-sm-12 col-lg-6"><label  for="old_percent_off">% Off <i class="red">*</i></label><div ><input type="text" class="form-control product_percent_off" placeholder="Enter Product % Off" data-parsley-required-message="Please enter % Off" name="product_old_percent_off" id="product_old_percent_off" data-parsley-required="true" data-parsley-type="number" data-parsley-type-message="Please enter valid % Off" min="0" data-parsley-trigger="keyup" data-parsley-type="number" /></div><span class="red" id="error_per_off_product_amt"></span></div></div><div class="clearfix">')
    }
    if ($(this).val() == 2) {
        $('#product_dis_amt').html('<div class="col-md-6"><label  for="old_free_product_min_amount">Min Order Amount to get product discount ($)</label><div ><input type="text" name="free_product_dis_min_amount" class="form-control" placeholder="Enter Min Order Amount to get product discount" id="old_product_discount_min_amount" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div><span class="red" id="error_min_product_amt"></span></div></div> <div class="col-md-6"><label  for="old_percent_off">$ Off <i class="red">*</i></label><div ><input type="text" class="form-control product_dolar_off" placeholder="Enter Product $ Off" name="product_old_dollar_off" id="product_old_dollar_off" data-parsley-required="true" data-parsley-required-message="Please enter $ Off" data-parsley-type="number" data-parsley-type-message="Please enter valid $ Off" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div><span class="red" id="error_per_off_product_amt"></span></div></div><div class="clearfix">')
    }
})

$(document).ready(function() {
    tinymce.remove('textarea');
});