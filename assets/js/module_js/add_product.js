

var table_module = false;

  $(document).ready(function()
  {
    var vendor_commission = $("#commission_percent").val();

    let base_url = window.location.origin+"/storage/";
 
    $( "#add-sec-tabs" ).tabs();
     $("#add-sec-tabs").tabs({
        disabled: [1,2,3]
     });

      table_module = $('#table_module').DataTable({ 
        language: {
            'loadingRecords': '&nbsp;',
            'processing': '<div class="spinner"><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div></div>'
      },
      processing: true,
      serverSide: true,
      autoWidth: false,
      bFilter: false,
      /*"pagingType": "input",*/
      "order":[4,'Asc'],

      ajax: {
      'url': module_url_path+'/get_maker_products',
      'data': function(d)
        {
        
          d['column_filter[q_product_name]']    = $("input[name='q_product_name']").val()
          d['column_filter[q_brand_name]']      = $("input[name='q_brand_name']").val()
          d['column_filter[q_category_name]']   = $("input[name='q_category_name']").val()  
          d['column_filter[q_sku]']             = $("input[name='q_sku']").val()         
          d['column_filter[q_unit_wholsale_price]']= $("input[name='q_unit_wholsale_price']").val()
          // d['column_filter[q_retail_price]']    = $("input[name='q_retail_price']").val() 
          d['column_filter[q_comm_unit_wholsale_price]']= $("input[name='q_comm_unit_wholsale_price']").val()
          // d['column_filter[q_comm_retail_price]']    = $("input[name='q_comm_retail_price']").val()              
          d['column_filter[q_status]']          = $("select[name='q_status']").val()
          d['column_filter[q_product_status]']  = $("select[name='q_product_status']").val() 
          d['column_filter[q_product_sts]']     = $("select[name='q_product_sts']").val() 
        }
      },
       drawCallback:function(settings)
      {       
       $("#total_amt_whole").html("$ "+settings.json.total_amt_whole.toFixed(2));
       // $("#total_amt_retail").html("$ "+settings.json.total_amt_retail.toFixed(2));
       $("#total_amt_shipping").html("$ "+settings.json.total_amt_shipping.toFixed(2));
      },
      columns: [
      {
             render : function(data, type, row, meta) 
             {
                return '<div class="checkbox checkbox-success"><input type="checkbox" '+
                     ' name="checked_record[]" '+  
                     ' value="'+row.enc_id+'" id="checkbox'+row.id+'" class="case checkboxInput"/><label for="checkbox'+row.id+'">  </label></div>';
             },
             "orderable": false,
             "searchable":false
      },
      {
        render(data, type, row, meta)
        {   
            return `<img class="zoom-img" src="`+row.product_image+`" height="100px" width="100px">`;
        },
        "orderable": false, "searchable":false
      },  
      // {data: 'product_name', "orderable": true, "searchable":false}, 
      {
        render : function(data, type, row, meta) 
         {
                 return `<a href="`+module_url_path+`/view/`+btoa(row.id)+`" class="link_v">`+row.product_name+`</a>`;             
         },
         "orderable": false,
         "searchable":false
      },
      
      {data: 'brand_name', "orderable": true, "searchable":false}, 

      {data: 'category_name', "orderable": true, "searchable":false},                   
      {
        render(data, type, row, meta)
        {    
            if(row.product_sku_status == "Multiple")
            {
               return "Multiple Sku";
               // return row.sku;
            }
            else
            {
               return row.product_sku_status;
            }
        },
        "orderable": false, "searchable":false
      },

      {
        render(data, type, row, meta)
        {  
          try
          {
            if(row.product_sku_status!="Multiple")
            {
               return row.quantity[0]['quantity'];
            }
            else
            {            
              var innerBody = '';
              $.each(row.quantity, function( index, value ) 
              {
                 //test = getSkuImage(value.sku_no); 
                 //console.log(test,11111111111);
                innerBody+= "<tr><td>"+value.sku_no+"</td><td>"+value.quantity+"</td></tr>";
              });  
             
             /* return `<a href="javascript:void(0)" data-toggle="collapse" data-target="#demo`+row.id+`">Inventory</a>
                        <div id="demo`+row.id+`" class="collapse">
                        <table>
                          <thead>
                            <tr>
                              <th>Sku</th>
                              <th>Quantity</th>
                            </tr>
                          </thead>
                          <tbody>
                              ${innerBody}                        
                          </tbody>
                        </table>
                       </div>`*/
                 return `<a href="javascript:void(0)" data-target="#demo`+row.id+`" data-toggle="modal"><u>`+row.total_inventory+`</u></a>
                         <div id="demo`+row.id+`" class="modal fade" role="dialog">
                            <div class="modal-dialog modal-lg">

                              <!-- Modal content-->
                              <div class="modal-content">
                                <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                                  <h4 class="modal-title">Product Name - `+row.product_name+`</h4>
                                </div>
                                <div class="modal-body">
                                  <div class="viewProductsModelList">
                                    <table id="tbl_`+row.id+`">
                                        <td colspan="5">
                                          <table class="table table-bordered product-list">
                                              <thead>
                                                <tr>
                                                  <th>Sku</th>
                                                  <th>Quantity</th>
                                                </tr>
                                              </thead>
                                             <tbody>${innerBody}</tbody>
                                            </table>
                                        </td>
                                    </table>
                                  </div>  
                                </div>
                                <div class="modal-footer">
                                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                </div>
                              </div>

                            </div>
                          </div>`       
            }
          }  
          catch(_error)
          {
            console.warn(_error)
            return 'NA';
          } 
          
      },
        "orderable": false, "searchable":false
      },

      {                  
        render(data, type, row, meta)
        {
             return ''+(+vendor_commission);
        },
        "orderable": false, "searchable":false
      },
      
      {                  
        render(data, type, row, meta)
        {
             return '<i class="fa fa-dollar"></i>'+(+row.unit_wholsale_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },    

      {                  
        render(data, type, row, meta)
        {
            var total_price       = 0;
            total_price = (row.unit_wholsale_price - ( row.unit_wholsale_price * vendor_commission / 100 )).toFixed(2);
            return '<i class="fa fa-dollar"></i>'+(+total_price).toFixed(2);
        },
        "orderable": false, "searchable":false
      },             
      // {                  
      //   render(data, type, row, meta)
      //   {

      //        return '<i class="fa fa-dollar"></i>'+(+row.retail_price).toFixed(2);
      //   },
      //   "orderable": false, "searchable":false
      // },  

      // {                  
      //   render(data, type, row, meta)
      //   {
      //       var total_price       = 0;
      //       total_price = (row.retail_price - ( row.retail_price * vendor_commission / 100 )).toFixed(2);
      //       return '<i class="fa fa-dollar"></i>'+(+total_price).toFixed(2);
      //   },
      //   "orderable": false, "searchable":false
      // },   
      // {
      //   render(data, type, row, meta)
      //   {

      //       return '<i class="fa fa-dollar"></i>'+(+row.shipping_charges).toFixed(2);
          
      //   },
      //   "orderable": false, "searchable":false
        
      // },
      {data: 'product_complete_status',orderable: false, searchable: false,responsivePriority:4,
         render(data, type, row, meta)
         {
              if(row.product_complete_status == 4)
              {
                return `<span class="label label-success">Completed</span>`;             
              }
              else
              {
                return `<span class="label label-success">Incomplete</span>`;                         
              }
          }
      },   

    /*  {data: 'remark', "orderable": true, "searchable":false},*/ 
     
      {
        render(data, type, row, meta)
        {
             return '<b>'+row.is_active+'</b>';
        },
        "orderable": false, "searchable":false
      },

      {data: 'product_status',
        orderable: false, 
        searchable: false,
        responsivePriority:4,

      render(data, type, row, meta)
      {   
          if(row.product_status == 1)
          {
              return `<input type="checkbox" checked data-size="small"  data-enc_id="`+row.enc_id+`" id="status_'.$data->id.'"  class="js-switch toggleSwitch" data-type="deactivate" data-color="#99d683" data-secondary-color="#f96262" onchange="statusChange(this)"  action="deactivate"/>`
          }
          else
          {
              return `<input type="checkbox" data-size="small" data-enc_id="`+row.enc_id+`"  class="js-switch toggleSwitch" data-type="activate" data-color="#99d683" data-secondary-color="#f96262" onchange="statusChange(this)" action="activate"/>`
          }
      }
    },

      {
        render(data, type, row, meta)
        {
           // <a href="javascript:void(0)" data-id="`+row.id+`" onclick="edit_product($(this));" class="btn btn-circle btn-success btn-outline show-tooltip" data-toggle="modal" data-target="#editModel">Edit</a>
            return `           

              <a href="`+module_url_path+`/edit/`+btoa(row.id)+`" data-id="`+row.id+`"  class="btn btn-circle btn-success btn-outline show-tooltip">Edit</a>
              <a href="`+module_url_path+`/view/`+btoa(row.id)+`" data-toggle="tooltip"  data-size="small" title="View Product Details" class="btn btn-circle btn-success btn-outline show-tooltip">View</i></a>
             <a href="`+module_url_path+`/delete/`+btoa(row.id)+`" data-toggle="tooltip"  data-size="small" title="Delete" class="btn btn-outline btn-primary btn-circle show-tooltip btn-retailer-view" onclick="confirm_delete(this,event);">Delete</a>`;

        },
        "orderable": false, "searchable":false
      },

      ]
  });

  $('input.column_filter').on( 'keyup click', function () 
  {
      filterData();
  });




  $('#table_module').on('draw.dt',function(event)
  {
    var oTable = $('#table_module').dataTable();
    var recordLength = oTable.fnGetData().length;
    $('#record_count').html(recordLength);

    var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
      $('.js-switch').each(function() {
         new Switchery($(this)[0], $(this).data());
      });  
    toggleSelect();
    /*hideProcessingOverlay();*/

   
  });

  /*search box*/
  $("#table_module").find("thead").append(`<tr>
          <td></td><td></td>
          <td><input type="text" name="q_product_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>  

          <td><input type="text" name="q_brand_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 

         <td><input type="text" name="q_category_name" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>   
        <td><input type="text" name="q_sku" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td>
        <td></td>
        <td></td>
        <td><input type="text" name="q_unit_wholsale_price" placeholder="Search" class="search-block-new-table column_filter form-control-small" /></td> 
        <td></td>
          
          
          
          <td>
             <select class="search-block-new-table column_filter form-control-small" name="q_product_status" id="q_product_status" onchange="filterData();">

              <option value="">All</option>
              <option value="4">Completed</option>
              <option value="1">Incompleted</option>
                      
              </select>

          </td>
          <td>
             <select class="search-block-new-table column_filter form-control-small" name="q_status" id="q_status" onchange="filterData();">
              <option value="">All</option>
              <option value="2">Pending</option>
              <option value="1">Approved</option>
              <option value="0">Rejected</option>

              </select>
          </td>  


          <td>
             <select class="search-block-new-table column_filter form-control-small" name="q_product_sts" id="q_product_sts" onchange="filterData();">
              <option value="">All</option>
              <option value="1">Active</option>
              <option value="0">Deactive</option>
             

              </select>
          </td>  


         
        

            
      </tr>`);

  $('input.column_filter').on( 'keyup click', function () 
  {
       filterData();
  });
  });
  
  function getSkuImage(sku_id)
  {
    var test = "";
    $.ajax({
           method   : 'GET',
           dataType : "json",
           data     : {sku_id:sku_id},
           url      : module_url_path+'/getSkuImage',
           success  : function(data)
           { 
                mycallbackfunction(data);
           }
        }); 
    

  }

  function mycallbackfunction(data)
  {
      //console.log(data.sku_thumb_image);
      return data.sku_thumb_image;
  } 
  // table_module.page(1).draw(false);
  function redirect_to_page(){
     var page_no = Math.abs($("#search_page_no").val());       
      if(page_no != '0' && $.isNumeric( page_no )){
            page_no = page_no - 1;
            var page_no_new = parseInt(page_no);   
            /*showProcessingOverlay(); */
            table_module.page(page_no_new).draw(false);
      }  
  }

  function filterData()
  {
    table_module.draw();
  }

  function confirm_delete(ref,event)
  {
     confirm_action(ref,event,'Are you sure? Do you want to delete this record.');
  }
  


  function statusChange(ref)
  {
 
    var productStatus = '';
    var product_id = $(ref).attr('data-enc_id');

    var type = $(ref).attr('action');

      
    if($(ref).is(":checked"))
    {
        productStatus = '1';
    }
    else
    {
      productStatus = '0';
    }

    var msg = action_attribute = '';

    if(type == 'deactivate')
    {
        msg = 'Are you sure? Do you want to deactive this product.';
        action_attribute = "activate";
    }
    else if(type == 'activate')
    {
       msg = 'Are you sure? Do you want to activate this product.';
       action_attribute = "deactivate";
    }
    else
    {
      msg = '';
    }

     swal({
      title: "Need Confirmation",
      text:  msg,
      type:  "warning",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "OK",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: true
    },
    function(isConfirm) 
    {
      if (isConfirm) 
      {
          $.ajax({
           method   : 'GET',
           dataType : 'JSON',
           data     : {product_id:product_id,productStatus:productStatus},
           url      : module_url_path+'/changeProductStatus',
           success  : function(response)
           { 
           if(typeof response == 'object' && response.status == 'WARNING')
            {
              swal('Done', response.message, 'warning');
          
            }                        
            if(typeof response == 'object' && response.status == 'SUCCESS')
            {
              swal('Done', response.message, 'success');
              $(ref).attr('action',action_attribute);
              
            }
            else
            {
              swal('Oops...', response.message, 'error');
            }               
           }
          }); 
      }
      else
      {
        $(ref).trigger('click');
      }
     
    }); 
    
  }
/*
   function getStats() {
    var description = $("#product_description");    
    var body = tinymce.get(description).getBody(), text = tinymce.trim(body.innerText || body.textContent);
     
    return {
        chars: text.length,
        words: text.split(/[\w\u2019\'-]+/).length
    };
   }*/

$(function () {

    $('#unit_wholsale_price').on('keyup', function(){ 
       var elem       =  $('#unit_wholsale_price').parsley();
       var error_name = 'custom_error';
       var price_error = 'price_error';


       var retail_price = $('#retail_price').val(); 
       var unit_wholsale_price = $('#unit_wholsale_price').val();

       



        if ($(this).val()!="" && $(this).val() == 0)
        {
            elem.removeError(error_name);
            elem.addError(error_name, {message:'price should not be 0'});
        }
        else
        {
           elem.removeError(error_name);
        }

       
        
       //  if(unit_wholsale_price>retail_price && retail_price!='')
       // {    elem.removeError(price_error);
       //      elem.removeError(error_name);
       //      elem.addError(price_error, {message:'Unit wholesale price should be less than price'});
       // }
       // else
       // {
       //     elem.removeError(price_error);
       //     elem.removeError(error_name);
       // }

        

    });

    // $('#retail_price').on('keyup', function(){ 
    //    var elem       =  $('#retail_price').parsley();
    //    var wholesale_elem = $('#unit_wholsale_price').parsley();
    //    var error_name = 'custom_error';
    //    var price_error = 'price_error';


    //    var retail_price = $('#retail_price').val(); 
    //    var unit_wholsale_price = $('#unit_wholsale_price').val();

    //    retail_price =  parseFloat(retail_price);
    //    unit_wholsale_price = parseFloat(unit_wholsale_price);


    //     if ($(this).val()!="" && $(this).val() == 0)
    //     {
    //         elem.removeError(error_name);
    //         elem.addError(error_name, {message:'Unit retail price should not be 0'});
    //     }
    //     else
    //     {
    //        elem.removeError(error_name);
    //     }
        
        
    //      if(unit_wholsale_price>retail_price)
    //    {    wholesale_elem.removeError(price_error);
    //         wholesale_elem.removeError(error_name);
    //         wholesale_elem.addError(price_error, {message:'Unit wholesale price should be less than unit retail price'});
    //    }
    //    else
    //    {
    //     wholesale_elem.removeError(price_error);
    //         wholesale_elem.removeError(error_name);
    //    }
        
    // });
});

    $('.save_items_details').click(function(){

           let is_draft = $(this).attr('data-is-draft');
           let is_up1 = $("#is_up1").val();

          /* var description = tinyMCE.editors[$('#product_description').attr('id')].getContent();
          if(description!="")
          {  
           if(description.length > 1000)
           {
              $("#err_product_desc").html('Allowed only 1000 characters or fewer.');
              return;
           }
           else
           {
             $("#err_product_desc").html(''); 
           }
          } */


           $('#items_is_draft').val(is_draft);

       /*    var unit_wholsale_price = $('#unit_wholsale_price ').val(); 
           var unit_retail_price   = $('#retail_price').val();

           if(unit_wholsale_price!="" && unit_wholsale_price==0)
           {
              $("#err_unit_wholsale_price").html('Unit wholesale price should not be 0.');
              return false;
           } 

            if(unit_retail_price!="" && unit_retail_price==0)
           {
              $("#err_unit_retail_price").html('Unit retail price should not be 0.');
              return false;
           } 

           else
           {
             $('#err_unit_wholsale_price').html('');$('#err_unit_retail_price').html(''); 
           }*/



          /* var unit_wholsale_price = $("#unit_wholsale_price").val();  

           window.Parsley.addValidator('notequaltozero', {
            validateNumber: function(unit_wholsale_price!=0) {
              return false;
            },
            messages: {
              'unit wholesale price should not be 0.',
            }
          }); */

           if($('#item-details-frm').parsley().validate()==false) return;
           
           tinyMCE.triggerSave();
           $.ajax({
             url: module_url_path+'/storeProduct',
             type:"POST",
             data: new FormData($("#item-details-frm")[0]),
             contentType:false,
             processData:false,
             dataType:'json',
             beforeSend: function() 
             {
               showProcessingOverlay();                
             },
             success:function(response)
             {
                 hideProcessingOverlay();
                  $("#add-sec-tabs").removeClass('ui-widget');
                  $("#add-sec-tabs").removeClass('ui-widget-content');
                if(response.status == 'success')
                { 
                  //set product id to hidden field
               
                  $('#style_product_id').val(response.product_id);
                  $("#is_up1").val("true");
                  
                  $("#pro_id").val(response.product_id);
                  //all all form fields
                  //$('#item-details-frm')[0].reset();
     
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
                           $("#add-sec-tabs").tabs("enable", 1);
                           $("#add-sec-tabs").tabs( "option", "active", 1 );
                           $('#style-frm').parsley();
                          //window.location = response.next_url;  
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

    //save style details

    $('.save_style_details').click(function($this){

        var c_dup =[];
        var flag = $(this).attr("data-flag"); 

        $(".check-dup").each(function() {
          c_dup.push($(this).val());
        });
        var check = checkIfArrayIsUnique(c_dup);
        if(check ==false)
        {
          swal('Info',"SKU numbers must be unique",'info');
          return false;
        }

        if(flag == 1)
        {
           return false;
        }

        function checkIfArrayIsUnique(myArray) {
          return myArray.length === new Set(myArray).size;
        }

        if($('#style-frm').parsley().validate()==false) return;
        
        if($('.parsley-error').length>0) return;

        let is_draft = $(this).attr('data-is-draft');           
                       $('#style_is_draft').val(is_draft);      

        
        /*var description = tinyMCE.editors[$('#sku_product_description').attr('id')].getContent();

         if(description!="")
          {  
           if(description.length > 1000)
           {
              $("#err_product_desc_add_prod").html('Allowed only 1000 characters or fewer.');
              return;
           }
           else
           {
             $("#err_product_desc_add").html(''); 
           }
          }
                            
        tinyMCE.triggerSave();*/
         $.ajax({
           url: module_url_path+'/storeStyleAndDiemensions',
           type:"POST",
           data: new FormData($("#style-frm")[0]),
           contentType:false,
           processData:false,
           dataType:'json',
           beforeSend: function() 
            {
              showProcessingOverlay();                
            },

           success:function(response)
           {    
              hideProcessingOverlay();

              if(response.status == 'success')
              {  $("#is_up").val("true");
                $('#additional_img_product_id').val(response.product_id);
                //all all form fields
                  //$('#style-frm')[0].reset();   
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
                         if(isConfirm==true)
                        {
                           $("#add-sec-tabs").tabs("enable", 2);
                           $("#add-sec-tabs").tabs( "option", "active", 2 );
                          /* $("#is_up").val("true");*/
                           
                        }
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

    //save additional images
    $('.save_additional_img').click(function(){

        if($('#additional-img-frm').parsley().validate()==false) return; 

        let is_draft = $(this).attr('data-is-draft');           
                       $('#images_is_draft').val(is_draft); 

         $.ajax({
           url: module_url_path+'/storeAdditionalImages',
           type:"POST",
           data: new FormData($("#additional-img-frm")[0]),
           contentType:false,
           processData:false,
           dataType:'json',
           beforeSend: function() 
           {
              showProcessingOverlay();                
           },
           success:function(response)
           {  
              hideProcessingOverlay();
              if(response.status == 'SUCCESS')
              { 
                //set product id to hidden field
                $('#category_product_id').val(response.product_id);

                //all all form fields
                //$('#additional-img-frm')[0].reset();
   
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
                         if(isConfirm==true)
                        {
                           $("#add-sec-tabs").tabs("enable", 3);
                           $("#add-sec-tabs").tabs( "option", "active", 3 );
                        }
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

     //save categories
     $('.save_category').click(function(){

        if($('#category-frm').parsley().validate()==false) return;
          
          let is_draft = $(this).attr('data-is-draft');           
                       $('#category_is_draft').val(is_draft); 



        /*-----------------validation for shipping charges---------------------------------*/
          var flag = 0;
          var min_amt_free_shiping   = $('#free_ship_min_amount').val();
          var product_unit_price     = $('#unit_wholsale_price').val();
          var ship_percent_off_value = $(".ship_percent_off").val();

          var ship_dolar_off_value   = $(".ship_dollar_off").val();
   
          var shipping_charges       = $('#shipping_charges').val();

          // var prod_min_amount = $('#product_discount_min_amount').val();

          // var prod_percent_off_value = $("product_%_off").val();

          // var prod_dolar_off_value   = $("#product_$_off").val(); 

          if(parseInt(product_unit_price) > parseInt(min_amt_free_shiping))
          { 
            
            $('#error_min_ship_amt').text('Min amount for free shipping should be greater than price');

             flag = 1;
          }

          if(ship_percent_off_value >100)
          {
             $('#error_ship_per_off_amt').text('% Off amount for discount on shipping should be less than 100');
             flag = 1;
          }


          if(parseInt(shipping_charges) < parseInt(ship_dolar_off_value))
          { 
             $('#error_ship_per_off_amt').text('$ off amount for discount on shipping should be less than shipping charges');
             flag = 1;
          }
          /*------------------------------------------------------------------*/


          /*------------validation for product discount ------------------------*/

          var percent_off_product_dis = $('.product_percent_off').val(); 
          var dolar_off_product_dis = $('.product_dolar_off').val(); 
          var min_amt_product_dis     = $('#product_discount_min_amount').val();

          if(parseInt(product_unit_price) > parseInt(min_amt_product_dis))
          { 
             $('#error_min_product_amt').text('Min amount for product discount should be greater than price');
             flag = 1;
          }

          
          if(percent_off_product_dis > 100)
          {
             $('#error_per_off_product_amt').text('% off amount for product discount should be less than 100');
             flag = 1;
          }
          if (parseInt(product_unit_price) < parseInt(dolar_off_product_dis)) 
          {
            $('#error_per_off_product_amt').text('$ off amount for product discount should be less than price');
             flag = 1;
          }


          if(flag == 1)
          {
            return false;
          }
        /*-----------------------------------------------------------------------------------*/




                       
         $.ajax({
           url: module_url_path+'/storeCategories',
           type:"POST",
           data: new FormData($("#category-frm")[0]),
           contentType:false,
           processData:false,
           dataType:'json',
           beforeSend: function() 
           {
              showProcessingOverlay();                
           },
           success:function(response)
           {   hideProcessingOverlay();
              if(response.status == 'success')                
              { 
                //all all form fields
                  $('#category-frm')[0].reset();   
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
                        //location.reload();   
                        window.location = response.next_url;                    
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

  //add more rows to the table
  $('#addMore').click(function()
  {  
    
    const rowCount = $('#style_and_diemension_tbl tr').length;

    if(rowCount>11)
    {
      swal('Warning', 'Limit exceed! you can add only 10 images ','warning');
      return false;
    }

    var row = $("#row").val();


      var newRow = '';
      newRow += `<tr class="pro-add-tr">
                    <td class="pro_add_edit_style_dimension">
                      <div class="img-shop-tbl nw-in-shp">
                         <input type="file" 
                          class="form-control dropify" 
                          name="product_image[]"
                          data-default-file=""
                          data-allowed-file-extensions="png jpg JPG jpeg JPEG" 
                          data-max-file-size="2M" 
                          data-errors-position="outside"
                          data-parsley-required="true"
                          data-parsley-required-message = "Please select image"
                          id="product_img_`+row+`">           
                      </div>
                    </td>
                  
                    <td class="parsley_error_sku_inventory">
                        <div class="row">
                        <input type="text" class="form-control check-dup" placeholder="SKU" data-parsley-required="true" name="sku[]" data-parsley-trigger="change" data-parsley-whitespace="trim"  data-parsley-remote="`+remote_url+`"
                                    data-parsley-remote-options='{ "type": "POST", "dataType": "jsonp", "data": { "_token": "`+csrf_token+`" }}'   data-parsley-remote-message="SKU no already exists" id="sku_`+row+`">

                        </div>
                    </td>

                    <td>
                      <div class="row">
                      <input type="text" class="form-control" placeholder="Min Quantity" name="min_quantity[]" data-parsley-required="true" data-parsley-required-message="Please enter minimum quantity" data-parsley-type="digits" data-parsley-type-message="Please enter valid minimum quantity" min="1" id="min_quantity_`+row+`" data-row="`+row+`" onkeyup="checkMinQuantity(this);">
                       <span class="red" id="quantity_error_msg_`+row+`"></span>
                      </div>
                    </td>
                   
                    <td class="parsley_error_sku_inventory">
                       <div class="row">
                       <input type="text" class="form-control" placeholder="Inventory" name="quantity[]" data-parsley-required="true" data-parsley-required-message="Please enter inventory" data-parsley-type-message="Please enter valid inventory" id="quantity_`+row+`" data-row="`+row+`" onkeyup="checkInventory(this);" >
                       <span class="red" id="inventory_error_msg_`+row+`"></span>
                       </div>

                    </td>

                    <td>
                      <div class="row">
                      <textarea class="form-control custom-text-area" placeholder="Enter Description" rows="1" name="sku_product_description[]" id="sku_product_description_`+row+`" ></textarea>
                      </div>

                    </td>
               
                    <td>
                       <a href="javascript:void(0)" class="btn btn-outline btn-danger btn-circle show-tooltip minusbts"  onclick="javascript: removeRows(this);" title="Remove"><i class="fa fa-minus"></i> </a>
                    </td>


                </tr>`;


      $(this).parent().parent().parent().append(newRow);

      $('#style-frm').parsley().refresh();

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
     });    */
  });

  function removeRows(ref)
  {  
      swal({
      title: "Need Confirmation",
      text:  "Are you sure? Do you want to remove this row.",
      type:  "warning",
      showCancelButton: true,
      confirmButtonClass: "btn-danger",
      confirmButtonText: "OK",
      cancelButtonText: "Cancel",
      closeOnConfirm: true,
      closeOnCancel: true
    },
    function(isConfirm) 
    {
      if (isConfirm) 
      {

        $(ref).parent().parent().remove(); 
          
          var row = $("#row").val();
          row--;

          $("#row").val(row);

      }
     
    }); 
     
  };

  function init_dropify()
  {
    $('.dropify').each(function(index,elem){
        
        var tmpDropify = $(elem).data('dropify');

        if(tmpDropify == undefined)
        {             
          $(elem).dropify();              
        } 
    });
  }



  //get second level categories
 function get_subcategory(referance)
 {
  
   var category_id = referance.val();
   var url = SITE_URL+'/get_sub_categories/'+category_id;  
 
   $.ajax({
     url:url,          
     type:'GET',
     dataType:'json',
     success:function(response)
     {
       var sub_categories= '';
       if(response.status=='SUCCESS')
       {
          if(typeof(response.sub_categories_arr) == "object")
          {              
             $(response.sub_categories_arr).each(function(index,second_category)
             {
               sub_categories +='<option value="'+second_category.id+'">'+second_category.subcategory_name+'</option>';
             });
          }          
       }
       else
       {
         sub_categories += '';          
       }
       
       $('#second_category').html(sub_categories);
     }
   });
 }

 $('#shipping_type').on('change',function () {


  if($(this).val()=='')
  {
    $("#add_shipping_amounts").html('');

  }

  if ($(this).val() == 1) 
  {
    $('#add_shipping_amounts').html('<div class="col-md-6"><label  for="free_ship_min_amount">Min Order Amount to get shipping discount ($)<i class="red">*</i></label><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter Min Order Amount to get shipping discount"  id="free_ship_min_amount" data-parsley-required="true" data-parsley-required-message="Please enter Min Order Amount to get shipping discount" data-parsley-type="number" min="0"  data-parsley-trigger="keyup" data-parsley-type="number"  data-parsley-min-message="This value should not be less than 1"/><span class="red" id="error_min_ship_amt"></span></div><div class="clearfix"></div>')
  }
  if ($(this).val() == 2) {
    
    $('#add_shipping_amounts').html('<div class="col-md-6"><label  for="free_ship_min_amount">Min Order Amount to get shipping discount ($)</label><div ><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter Min Order Amount to get shipping discount"  id="free_ship_min_amount" min="0" data-parsley-trigger="keyup" data-parsley-type="number"   /><span class="red" id="error_min_ship_amt"></span></div></div></div><div class="col-md-6"><label  for="percent_off">% Off <i class="red">*</i></label><div ><input type="text" class="form-control ship_percent_off" placeholder="Enter % Off" name="%_off" id="%_off" data-parsley-required="true" data-parsley-required-message="Please enter % Off" data-parsley-type="number" data-parsley-type-message="Please enter valid % Off" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div><span class="red" id="error_ship_per_off_amt"></span></div></div><div class="clearfix">')
  }
  if ($(this).val() == 3) {
    
    $('#add_shipping_amounts').html('<div class="col-md-6"><label  for="free_ship_min_amount">Min Order Amount to get shipping discount ($)</label><div ><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter Min Order Amount to get shipping discount" id="free_ship_min_amount" min="0" data-parsley-trigger="keyup" data-parsley-type="number" data-parsley-type="number"  /><span class="red" id="error_min_ship_amt"></span></div></div></div>   <div class="col-md-6"><label  for="percent_off">$ Off <i class="red">*</i></label><div ><input type="text" class="form-control ship_dollar_off" placeholder="Enter $ Off" name="$_off" id="$_off" data-parsley-required="true" data-parsley-required-message="Please enter $ Off" data-parsley-type="number" data-parsley-type-message="Please enter valid $ Off" min="0" data-parsley-trigger="keyup" data-parsley-type="number"/></div><span class="red" id="error_ship_per_off_amt"></span></div></div><div class="clearfix">')
  }
}); 

 $('#product_discount').on('change',function () {
  //alert("ok")
  // if ($(this).val() == 1) {
  //   $('#product_dis_amt').html('<div class="col-md-6"><label  for="old_free_ship_min_amount">Minimum Amount ($) <i class="red">* </i></label><input type="text" name="free_ship_min_amount" class="form-control" placeholder="Enter minimum free shipping amount"  id="old_free_product_min_amount" data-parsley-required="true" data-parsley-type="number"/></div><div class="clearfix"></div>')
  // }
  if($(this).val() == ''){
     
    $('#add_product_dis_amt').html('');
  }
  if($(this).val() == 1){
     
    $('#add_product_dis_amt').html('<div class="col-md-6"><label  for="free_product_min_amount">Min Order Amount to get product discount ($)</label><div ><input type="text" name="free_product_dis_min_amount" class="form-control" placeholder="Enter Min Order Amount to get product discount" id="product_discount_min_amount" /></div><span class="red" id="error_min_product_amt"></span></div></div>  <div class="col-md-6"><label  for="product_percent_off">% Off <i class="red">*</i></label><div ><input type="text" class="form-control product_percent_off" placeholder="Enter Product % Off" name="product_%_off" id="product_%_off" data-parsley-required="true" data-parsley-required-message="Please enter % Off" data-parsley-type="number" data-parsley-type-message="Please enter valid % Off" min="0"/></div><span class="red" id="error_per_off_product_amt"></span></div></div><div class="clearfix">')
  }
  if($(this).val() == 2){
   
    $('#add_product_dis_amt').html('<div class="col-md-6"><label  for="free_product_min_amount">Min Order Amount to get product discount ($)</label><div ><input type="text" name="free_product_dis_min_amount" class="form-control" placeholder="Enter Min Order Amount to get product discount" id="product_discount_min_amount"/></div><span class="red" id="error_min_product_amt"></span></div></div> <div class="col-md-6"><label  for="product_percent_off">$ Off <i class="red">*</i></label><div ><input type="text" class="form-control product_dolar_off" placeholder="Enter Product $ Off" name="product_$_off" id="product_$_off" data-parsley-required="true" data-parsley-required-message="Please enter $ Off" data-parsley-type="number" data-parsley-type-message="Please enter valid $ Off"/></div><span class="red" id="error_per_off_product_amt"></span></div></div><div class="clearfix">')
  }
})

const saveData = (() => {
  const a = document.createElement('a');
  a.style = 'display: none';
  document.body.appendChild(a);

  return (data, fileName, type = 'application/csv') => {
    const blob = new Blob([data], { type });

    if (navigator.msSaveBlob) {
      return navigator.msSaveBlob(blob, fileName);
    }

    const url = URL.createObjectURL(blob);
    a.href = url;
    a.download = fileName;
    a.click();
    URL.revokeObjectURL(url);
    return true;
  };
})();

 function vendorProductExport(){


    var q_product_name      = $("input[name='q_product_name']").val();
    var q_brand_name        = $("input[name='q_brand_name']").val();
    var q_category_name     = $("input[name='q_category_name']").val();
    var q_sku               = $("input[name='q_sku']").val();
    var q_unit_wholsale_price = $("input[name='q_unit_wholsale_price']").val();
    var q_retail_price      = $("input[name='q_retail_price']").val();
    var q_status            = $("select[name='q_status']").val()
    var q_product_status    = $("select[name='q_product_status']").val() 
    var q_product_sts       = $("select[name='q_product_sts']").val() 
    var vendor_commission   = $("#commission_percent").val() 
  

    $.ajax({
          url: module_url_path+'/get_export_maker_products',
          data     : {q_product_name:q_product_name,q_brand_name:q_brand_name,q_category_name:q_category_name,
                      q_sku:q_sku,q_unit_wholsale_price:q_unit_wholsale_price,q_retail_price:q_retail_price,
                      q_status:q_status,q_product_status:q_product_status,q_product_sts:q_product_sts,vendor_commission : vendor_commission},
        
          type:"get",
          beforeSend: function() 
           {
             showProcessingOverlay();                
           },
          success:function(data)
          {
            hideProcessingOverlay();
            if(data.status != null && data.status == 'error')
            {
              swal('Error',data.message,'error');
            }
            else
            {
              saveData(data, 'vendor_products.csv');
            }
          }
        });
 }

 