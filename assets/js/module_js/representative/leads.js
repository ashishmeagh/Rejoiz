$("#search-customer").on('change', function() {
    var customer = $("#search-customer").val();

    var csrf_token = $("input[name=_token]").val();

    $.ajax({
        type: "POST",
        headers: {
            'X-CSRF-TOKEN': csrf_token
        },
        url: module_url_path + '/get_customer_detail',
        dataType: 'json',
        data: 'customer=' + customer,

        beforeSend: function() {
            showProcessingOverlay();
        },

        success: function(response) {
            hideProcessingOverlay();


            if (typeof(response) == 'object') {

                if (response.status == 'SUCCESS') {
                    //let address_details = response.customer_arr.address_details || {};
                    let retailer_details = response.customer_arr.retailer_details || {};
                    //console.log(retailer_details);
                    $('#bill_first_name').val(response.customer_arr.first_name || "");
                    $('#bill_last_name').val(response.customer_arr.last_name || "");
                    $('#bill_email').val(response.customer_arr.email || "");
                    $('#bill_mobile_no').val(retailer_details.bill_contact_no || "");

                    //$('#bill_complete_addr').val(response.customer_arr.billing_addr || "");
                    $('#billing_street_address').val(retailer_details.billing_address || "");
                    $('#billing_suite_apt').val(retailer_details.billing_suit_apt);
                    $('#bill_city').val(retailer_details.billing_city || "");
                    $('#bill_state').val(retailer_details.billing_state || "");
                    $('#bill_zip').val(retailer_details.billing_zip_postal_code || "");
                    $('#bill_zip_no').val(retailer_details.billing_zip_postal_code || "");


                    $('#ship_first_name').val(response.customer_arr.first_name || "");
                    $('#ship_last_name').val(response.customer_arr.last_name || "");
                    $('#ship_email').val(response.customer_arr.email || "");
                    $('#ship_mobile_no').val(retailer_details.ship_contact_no || "");
                    //$('#ship_complete_addr').val(response.customer_arr.billing_addr || "");
                    $('#shipping_street_address').val(retailer_details.shipping_addr || "");
                    $('#shipping_suite_apt').val(retailer_details.shipping_suit_apt || "");

                    $('#ship_city').val(retailer_details.shipping_city || "");
                    $('#ship_state').val(retailer_details.shipping_state || "");
                    $('#ship_zip_code').val(retailer_details.shipping_zip_postal_code || "");


                    $.each(response.country_arr, function(index, value) {
                        if (value.id == response.customer_arr.retailer_details.billing_country) {
                            $('select[name^="bill_country"] option[value="' + value.id + '"]').attr("selected", "selected");
                            $('#hide_bill_country').val(response.customer_arr.retailer_details.billing_country);

                        }

                        if (value.id == response.customer_arr.retailer_details.shipping_country) {
                            $('select[name^="ship_country"] option[value="' + value.id + '"]').attr("selected", "selected");
                            $('#hide_ship_country').val(response.customer_arr.retailer_details.shipping_country);

                        }

                    });

                } else {
                    $('#address-frm')[0].reset();
                }
            }

        }
    });

});


/*  $( "#search-customer" ).autocomplete({

    source: function( request, response ) {
          $.getJSON( module_url_path+'/search_customer', {
            term: extractLast( request.term )
          }, response);
        },
    search: function() {
          // custom minLength
          var term = extractLast( this.value );
          if ( term.length < 2 ) {
            return false;
          }
        },
    focus: function() {
      // prevent value inserted on focus

      return false;
    },
    select: function( event, ui ) 
    {
    
     // console.log( "Selected: " + ui.item.value + " id " + ui.item.id );

      var customer   = ui.item.value;
      var csrf_token = $("input[name=_token]").val();
     
      $.ajax({
          type: "POST",
          headers: {
             'X-CSRF-TOKEN': csrf_token
           },
          url: module_url_path+'/get_customer_detail',
          dataType : 'json',
          data:'customer='+customer,
          
          beforeSend: function(){
            // $("#search-contact").css("background","#FFF url("+loader_url+") no-repeat 165px","float:right");
            showProcessingOverlay();
          },
          
          success: function(response){
            hideProcessingOverlay();
              // $("#search-contact").css("background");  
              if(typeof (response) == 'object')
              {
                //appends customers details to perticular fields          
                if(response.status=='SUCCESS')
                {   
                  let address_details = response.customer_arr.address_details || {};
                  
                  $('#bill_first_name').val(response.customer_arr.first_name || "");
                  $('#bill_last_name').val(response.customer_arr.last_name || "");
                  $('#bill_email').val(response.customer_arr.email || "");
                  $('#bill_mobile_no').val(response.customer_arr.contact_no || "");
                  $('#bill_complete_addr').val(response.customer_arr.billing_addr || "");
                  $('#bill_city').val(response.customer_arr.address_details.bill_city || "");
                  $('#bill_state').val(response.customer_arr.address_details.bill_state || "");
                  $('#bill_zip').val(response.customer_arr.billing_addr_zip_code || "");

                  $('#ship_first_name').val(response.customer_arr.first_name || "");
                  $('#ship_last_name').val(response.customer_arr.last_name || "");
                  $('#ship_email').val(response.customer_arr.email || "");
                  $('#ship_mobile_no').val(response.customer_arr.contact_no || "");
                  $('#ship_complete_addr').val(response.customer_arr.billing_addr || "");
                  $('#ship_city').val(response.customer_arr.address_details.ship_city || "");
                  $('#ship_state').val(response.customer_arr.address_details.ship_state || "");
                  $('#ship_zip_code').val(response.customer_arr.billing_addr_zip_code || "");
                }
                else
                {
                  $('#address-frm')[0].reset();
                }  
              }
              
            }
          });
    }
  });*/

function split(val) {
    return val.split(/,\s*/);
}

function extractLast(term) {
    return split(term).pop();
}

// sales_manager/leads/find_products/SjIyNDU0MjU5OA==

$('#btn-save-addr').click(function() {
    if ($('#address-frm').parsley().validate() == false) return;
    $('#ship_country').attr('disabled', false);

    var bill_country = $("#bill_country").val();
    var ship_country = $("#ship_country").val();
    var bill_zip = $("#bill_zip").val();
    var ship_zip = $("#ship_zip_code").val();
    var sameAsShip = jQuery('#same-as-billing').is(":checked");

    if (ship_country != "" && ship_zip == "") {
        $("#err_shipping_zip_code").html("");
    } else if (ship_country == "" && ship_zip == "") {
        $("#err_shipping_zip_code").html("");
    } else if (ship_country == "" && ship_zip != "") {
        $("#err_shipping_zip_code").html("Invalid zip/postal code.");
    }

    if (bill_country != "" && bill_zip == "") {
        $("#err_billing_zip_code").html("");
    } else if (bill_country == "" && bill_zip == "") {
        $("#err_billing_zip_code").html("");
    } else if (bill_country == "" && bill_zip != "") {
        $("#err_billing_zip_code").html("Invalid zip/postal code.");
    }

    // Validate shipping address
    var phone_code = $('#ship_country option:selected').attr('phone_code');
    var zipcode_length = $('#ship_country option:selected').attr('zipcode_length');
    var countryName = $('#ship_country option:selected').attr('country_name');

    var phcode = '+' + phone_code;
    var phcodelength = 10 + phcode.length;
    $('#ship_mobile_no').attr('data-parsley-minlength', phcodelength);

    if (zipcode_length == 8) {
        $('#ship_zip_code').attr('parsley-maxlength', true);
        $('#ship_zip_code').removeAttr('data-parsley-length');
        $('#ship_zip_code').attr('data-parsley-length-message', "");
        $("#ship_zip_code").attr({
            "data-parsley-maxlength": zipcode_length,
            "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
                zipcode_length +
                '  characters.',
        });
    } else {
        $('#ship_zip_code').attr('parsley-maxlength', false);
        $('#ship_zip_code').attr('data-parsley-maxlength-message', "");
        $("#ship_zip_code").attr({
            "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
            "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                zipcode_length +
                '  digits.'
        });
    }

    // Validate Billing address
    if (sameAsShip == false) {
        var phone_code = $('#bill_country option:selected').attr('phone_code');
        var zipcode_length = $('#bill_country option:selected').attr('zipcode_length');
        var countryName = $('#bill_country option:selected').attr('country_name');

        var phcode = '+' + phone_code;
        var phcodelength = 10 + phcode.length;
        $('#bill_mobile_no').attr('data-parsley-minlength', phcodelength);

        if (zipcode_length == 8) {
            $('#bill_zip').attr('parsley-maxlength', true);
            $('#bill_zip').removeAttr('data-parsley-length');
            $('#bill_zip').attr('data-parsley-length-message', "");
            $("#bill_zip").attr({
                "data-parsley-maxlength": zipcode_length,
                "data-parsley-maxlength-message": 'The ' + countryName + ' zipcode max upto ' +
                    zipcode_length +
                    '  characters.',
            });
        } else {
            $('#bill_zip').attr('parsley-maxlength', false);
            $('#bill_zip').attr('data-parsley-maxlength-message', "");
            $("#bill_zip").attr({
                "data-parsley-length": '[' + zipcode_length + ',' + zipcode_length + ']',
                "data-parsley-length-message": 'The ' + countryName + ' zipcode must contain ' +
                    zipcode_length +
                    '  digits.'
            });
        }
    } else {
        $('#address-frm').append('<input type="hidden" name="bill_country" id="bill_country" value="' + bill_country + '" /> ');
    }

    if ($('#address-frm').parsley().validate() == false) return;

    $.ajax({
        url: module_url_path + '/save_customer_address',
        type: "POST",
        data: new FormData($("#address-frm")[0]),
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            showProcessingOverlay();
        },
        success: function(response) {
            hideProcessingOverlay();
            if (response.status == 'SUCCESS') {
                //all all form fields
                $('#address-frm')[0].reset();
                swal({
                        title: 'Success',
                        text: response.description,
                        type: 'success',
                        confirmButtonText: "OK",
                        closeOnConfirm: true
                    },
                    function(isConfirm, tmp) {
                        if (isConfirm == true) {
                            window.location.href = response.next_url;
                        }
                    });
            } else {
                swal('Error', response.description, 'error');
            }
        }
    });
});

$('#same-as-billing').click(function() {
    makeReadonly();
});

function makeReadonly() {
    if ($('#same-as-billing').prop("checked") == true) {
        /*let bill_first_name = $('#bill_first_name').val();
        let bill_last_name  = $('#bill_last_name').val();
        let bill_email      = $('#bill_email').val();
        let bill_mobile_no  = $('#bill_mobile_no').val();
        let bill_complete_addr = $('#bill_complete_addr').val();
        let bill_city       = $('#bill_city').val();
        let bill_state      = $('#bill_state').val();
        let bill_zip        = $('#bill_zip').val();
        let bill_country    = $('#bill_country').val();*/

        let ship_first_name = $('#ship_first_name').val();
        let ship_last_name = $('#ship_last_name').val();
        let ship_email = $('#ship_email').val();
        let ship_mobile_no = $('#ship_mobile_no').val();
        let ship_city = $('#ship_city').val();
        let ship_state = $('#ship_state').val();
        let ship_country = $('#ship_country').val();
        let ship_zip = $('#ship_zip_code').val();
        let ship_street_addr = $('#shipping_street_address').val();
        let ship_suit_apt = $('#shipping_suite_apt').val();
        let ship_zip_code = $('#ship_zip_code').val();

        /*$('#ship_first_name').val(bill_first_name).prop('readonly',true);
    $('#ship_last_name').val(bill_last_name).prop('readonly',true);
    $('#ship_email').val(bill_email).prop('readonly',true);
    $('#ship_mobile_no').val(bill_mobile_no).prop('readonly',true);
    $('#ship_complete_addr').val(bill_complete_addr).prop('readonly',true);
    $('#ship_city').val(bill_city).prop('readonly',true);
    $('#ship_state').val(bill_state).prop('readonly',true);    
    $('#ship_zip_code').val(bill_zip).prop('readonly',true);  
    $('#ship_country').val(bill_country).attr('disabled',true);
*/

        $('#bill_first_name').val(ship_first_name).prop('readonly', true);
        $('#bill_last_name').val(ship_last_name).prop('readonly', true);
        $('#bill_email').val(ship_email).prop('readonly', true);
        $('#bill_mobile_no').val(ship_mobile_no).prop('readonly', true);
        $('#bill_city').val(ship_city).prop('readonly', true);
        $('#bill_state').val(ship_state).prop('readonly', true);
        $('#bill_zip').val(ship_zip).prop('readonly', true);
        $('#bill_country').val(ship_country).attr('disabled', true);
        $('#billing_street_address').val(ship_street_addr).prop('readonly', true);
        $('#billing_suite_apt').val(ship_suit_apt).prop('readonly', true);
        $('#bill_zip_no').val(ship_zip_code).prop('readonly', true);

    } else if ($('#same-as-billing').prop("checked") == false) {
        /*$('#ship_first_name').prop('readonly',false);
        $('#ship_last_name').prop('readonly',false);
        $('#ship_email').prop('readonly',false);
        $('#ship_mobile_no').prop('readonly',false);
        $('#ship_complete_addr').prop('readonly',false);
        $('#ship_city').prop('readonly',false);
        $('#ship_state').prop('readonly',false);
        // $('#ship_zip').val('').prop('readonly',false);
        $('#ship_zip_code').prop('readonly',false);
        $('#ship_country').attr('disabled',false);*/

        $('#bill_first_name').prop('readonly', false);
        $('#bill_last_name').prop('readonly', false);
        $('#bill_email').prop('readonly', false);
        $('#bill_mobile_no').prop('readonly', false);
        $('#bill_city').prop('readonly', false);
        $('#bill_state').prop('readonly', false);
        $('#bill_zip').prop('readonly', false);
        $('#bill_country').attr('disabled', false);
        $('#billing_street_address').prop('readonly', false);
        $('#billing_suite_apt').prop('readonly', false);

        $('#bill_first_name').val('');
        $('#bill_last_name').val('');
        $('#bill_email').val('');
        $('#bill_mobile_no').val('');
        $('#bill_city').val('');
        $('#bill_state').val('');
        $('#bill_zip').val('');
        $('#bill_country').attr('disabled', false);
        $('#bill_country').prop('selectedIndex', 0);
        $('#billing_street_address').val('');
        $('#billing_suite_apt').val('');
        $('#bill_zip_no').val('');  

    }
}

function saveOrderSummaryAddress() {
    var sameAsShip = jQuery('#same-as-billing').is(":checked");
    var bill_country = $("#bill_country").val();
    var ship_country = $("#ship_country").val();

    if (sameAsShip == true) {
        $('#validation-form').append('<input type="hidden" name="bill_country" id="bill_country" value="' + bill_country + '" /> ');
    }

    $.ajax({
        url: module_url_path + '/save_customer_address',
        type: "POST",
        data: new FormData($("#validation-form")[0]),
        contentType: false,
        processData: false,
        dataType: 'json',
        beforeSend: function() {
            showProcessingOverlay();
        },
        success: function(response) {
            hideProcessingOverlay();
            if (response.status == 'SUCCESS') {
                //all all form fields
                $('#validation-form')[0].reset();
            } else {
                swal('Error', response.description, 'error');
            }
        }
    });
}