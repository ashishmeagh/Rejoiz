

{{-- {{dd("product list")}}
 --}}
@extends('maker.layout.master')  
@section('main_content')
<style type="text/css">
.img-shop-tbl.nw-in-shp { width: 140px;}
.img-shop-tbl.nw-in-shp .dropify-wrapper{ height: 250px;}
th {
    white-space: nowrap;
}
 
/*.btn.btn-circle.btn-danger.btn-outline{
  border: 1px solid #dfdfdf;
    background-color: #fff;
    color: #444;
    font-size: 14px;
    padding: 13px 30px 12px;
    border-radius: 4px;
    display: inline-block;
    height: auto;
}*/
.btn.btn-circle.btn-danger.btn-outline {border: 1px solid #666666;background-color: #fff;color: #444;font-size: 14px;padding: 6px 6px 6px;border-radius: 0;display: inline-block;height: auto;min-width: 32px;}

.dataTable > thead > tr > th[class*="sort"]:after{
    content: "" !important;
}

table.dataTable thead > tr > th.sorting_asc, table.dataTable thead > tr > th.sorting_desc, table.dataTable thead > tr > th.sorting, table.dataTable thead > tr > td.sorting_asc, table.dataTable thead > tr > td.sorting_desc, table.dataTable thead > tr > td.sorting {
    padding-right: inherit;
}

.commission-calc-filter input {width:100%;}
.commission-calc-filter .d-flex span {margin: 0px 6px 0px 10px;}



.goto-page .form-group {display:flex; align-items:center; white-space:nowrap; margin-bottom:0px;    justify-content: flex-end;}
.inner-goto {display:flex; align-items:center; margin-right:10px;}
.inner-goto input {width:80px; height:24px;}
.inner-goto .btn-go {padding: 7px 10px !important;}
.goto-page .form-group span {margin-right:10px;}
</style>

<!-- Page Content -->
<div id="page-wrapper" class="manage_products_products">
   <div class="container-fluid">
     <div class="bg-title overflow-vibl-error">
      <div style="width: 100%;">

         <div class="col-sm-12 top-bg-title">
            <h4 class="page-title">{{$module_title or ''}}</h4>
            <div class="right">
              <ol class="breadcrumb">
               
                <li><a href="{{url('/')}}/{{$maker_panel_slug or ''}}/dashboard">Dashboard</a></li>
                <li class="active">{{$module_title or ''}}</li>
              </ol>
          </div>  
         </div>

         <div class="col-lg-12 col-sm-12 col-md-12 col-xs-12">
           <div class="indext-jtsd vendor_pro_browse_btn_flex rw-vendor-mange-product-browse">
            
    
          <form id="impert_excel_sheet" enctype="multipart/form-data" method="POST" action="{{url('/')}}/{{$maker_panel_slug}}/products/importExcel" >
            {{csrf_field()}}

            <!-- <div class="nt-rxlcs">
              <div class="brse">
                <input type="file" class="idx-imprt" name="import_bulk_data" name="import_bulk_data" data-parsley-required="true" data-parsley-required-message="Please select excel file." data-parsley-trigger="change" data-default-file data-allowed-file-extensions="xlsx csv">
              <div class="red noteuploads">Note: Please upload only Excel file.</div>
              </div>
              <div class="import_excel">
                <input type="submit" name="import_excel" id="import_excel" value="Import Excel" class="btn btn-info" >
              </div>
              </div> -->

        <div class="col-sm-7 left">

        <div class="form-group error-smg-form" style="margin:0px;">
          <label for="file" class="sr-only">File</label>
          <div class="input-group">
            <input type="file" name="import_bulk_data" class="form-control" placeholder="No file selected" data-parsley-required="true" data-parsley-required-message="Please select excel file" data-parsley-trigger="change" data-default-file data-allowed-file-extensions="xlsx csv"
            pattern="^.+\.(xlsx)$" data-parsley-pattern-message="Only excel file allowed">
            <span class="input-group-btn" style="display:none;">
              <div class="btn btn-default  custom-file-uploader">
                <input type="file" name="file" onchange="this.form.filename.value = this.files.length ? this.files[0].name : ''" />
                Select a file
              </div>
            </span>
             <!-- <div class="red noteuploads">Note: Please upload only Excel file.</div> -->
          </div>
          <div class="import_excel">
            <input type="submit" name="import_excel" id="import_excel" value="Import Excel" class="btn btn-info" >
          </div>
        </div>

      </div>
      <div class="col-sm-5 right">
      <a href="{{url('/')}}/{{$maker_panel_slug}}/products/export_sheet"  class="btn btn-info exprt-butn samplebtn-file" title="Download sample excel file for uploading product" id="export_excel_sheet">Product File Sample</a>
     {{-- <div class="clearfix"></div> --}}
   
     <div class="addpro_div inlinebtns-dash">
     {{--  <a href="#" data-toggle="modal" data-target=".bs-example-modal-lg" class="btn btn-info exprt-butn samplebtn-file">Add Product</a> --}}
      <a href="{{url('/')}}/{{$maker_panel_slug}}/products/create"  class="btn btn-info exprt-butn samplebtn-file">Add Product</a>
    </div>
    <ol class="breadcrumb">
    </ol>
   </div>
  
  </form>
</div>
        
<div class="clearfix"></div>
</div>
         <!-- /.col-lg-12 -->
</div>


</div>



      <div class="col-sm-12 terms red">
              <ul>
                <h3 class="red">Note</h3>
               <!--  <li>All the mandatory data should be there in Excel file.</li> -->
                <li>Please fill all the mandatory fields data in Excel file.</li>
                <li>If data is missing then do upload mandatory data afterward for each record from Edit page.</li>
                <li>Products with incomplete data are not listed on marketplace.</li>
                <li>Mandatory fields are required to list the products to marketplace.</li>
               
              </ul>
            </div>
        <div class="clearfix"></div>
      <!-- .row -->
      <div>
        <div class="col-lg-12 pad0">
            <div class="white-box">
                <h4 class="page-title comm-title mb-5 text-center">Commission Calculation</h4>


                <div class="form-group row maker_data_filter_row commission-calc-filter">
                      <div class="maker_data_filter_input mr-2">
                        <label class="label-float" for="category">Price</label>
                        <div class="d-flex align-items-center">
                        <input type="text" name="" id="ret_whole_price" class="search-block-new-table column_filter form-control-small form-control" onkeyup="calc_commission()">
                        <span>-</span>
                      </div>
                      </div>
                     <div class="maker_data_filter_input mr-2">
                      <label class="label-float" for="category">Admin Commission(%)</label>
                      <div class="d-flex align-items-center">
                      <input type="text" name="" id="commission_percent" class="search-block-new-table column_filter form-control-small form-control" value="{{ num_format($vendor_commisssion_percent,2) }}" readonly>
                      <span>=</span>
                    </div>
                     </div>

                     <div class="maker_data_filter_input">
                      <label class="label-float" for="category">Total Price (Excluded Commission)</label>
                      <input type="text" name="" id="total_commission_price" class="search-block-new-table column_filter form-control-small form-control" readonly> 
                     </div>
                     <!-- <div class="col-sm-2 maker_data_filter_btn">
                           <a href="javascript:void(0)" onclick="" class="btn btn-outline btn-success btn-circle show-tooltip" title="Search" id="search"><i class="fa fa-search" onclick="filterData();"></i> </a>

                        <a href="{{$module_url_path}}" class="btn btn-outline btn-success btn-circle show-tooltip space-btn-cirlce" title="Refresh"><i class="fa fa-refresh"></i> </a>
                     </div> -->
                </div>









                <!-- <div class="calc_commission">
                  <input type="text" name="" id="ret_whole_price" class="search-block-new-table column_filter form-control-small" style="width: 10%;text-align:right" onkeyup="calc_commission()"> 
                  <span> - </span>
                  <input type="text" name="" id="commission_percent" class="search-block-new-table column_filter form-control-small" style="width: 10%" value="{{ num_format($vendor_commisssion_percent,2) }}" readonly>
                  <span> % </span>
                  <span> = </span>
                  <input type="text" name="" id="total_commission_price" class="search-block-new-table column_filter form-control-small" style="width: 10%;text-align:right" readonly> 
                </div> -->
            </div>
        </div>
         <div class="col-lg-12 pad0">
           @include('admin.layout._operation_status')
            <div class="white-box">
               <!--  <div class="pull-left mb-4 top_small_icon"> 
                
               </div> -->
               <div class="pull-right top_small_icon">    
               <button type="button" onclick="vendorProductExport()" title="Export Vendor Product as .csv" class="btn btn-outline btn-info btn-circle show-tooltip" value="export" id="vendorProductExport">Export</button>              

                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','activate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Activate"><i class="ti-unlock"></i></a> 
            
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','deactivate');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Deactive"><i class="ti-lock"></i> </a>
          
                 
                <a  href="javascript:void(0);" onclick="javascript : return check_multi_action('checked_record[]','frm_manage','delete');" class="btn btn-outline btn-info btn-circle show-tooltip" title="Multiple Delete"><i class="ti-trash"></i> </a> 

                <a href="javascript:void(0)" onclick="javascript:location.reload()" class="btn btn-outline btn-info btn-circle show-tooltip" title="Refresh"><i class="fa fa-refresh"></i></a>

            
              </div> 
               {{-- <h3 class="box-title m-b-0">Products</h3> --}}
               <div class="clearfix"></div>
              
               <div class="col-sm-12">
                <form class="form-horizontal" id="frm_manage" method="POST" action="{{ url($module_url_path.'/multi_action') }}">
              {{ csrf_field() }}
               
                  <table class="table" id="table_module">
                  <input type="hidden" name="multi_action" value="" />
                     <thead>
                        <tr>
                            <th>
                              <div class="checkbox checkbox-success">

                                <input class="checkItemAll" id="checkbox0" type="checkbox">
                                <label for="checkbox0">  </label>
                              </div>
                           </th>
                           <th> Image</th>
                           <th> Product Name</th>
                           <th> Brand Name</th>
                           <th> Category</th>                        
                           <th> SKU</th>
                           <th> Inventory</th>
                           <th> Admin Commission(%)</th>
                           <th> Price</th>
                           <th> Commission Price <br></th>
                           {{-- <th> Price (Retail)</th>
                           <th> Commission Price <br>(Retail)</th> --}}
                           {{-- <th> Shipping Charge</th> --}}
                           <th> Product Status</th>
                           <th> Admin Status</th>
                           <th> Status</th>
                           <th> Action</th>
                        </tr>
                     </thead>
                     <tbody></tbody>

                    <!--  <tfoot>
                          <tr style="vertical-align: middle;">
                              <th colspan="6" align="right"> &nbsp;</th>
                              <th style="text-align: center;"> Total: &nbsp;</th>
                              <th id="total_amt_whole"></th> 
                              <th id="total_amt_retail"></th> 
                              <th id="total_amt_shipping"></th>                             
                              <th colspan="4"></th>                             
                            </tr>
                          </tfoot> -->
                  </table>
                 
                </form> 
                 {{-- <table align="right">
                    <tr>
                      <td colspan="18">Go to <input type="text" class="form-control" id="search_page_no">Page <button onclick="redirect_to_page()">Go</button></td>
                    </tr>
                  </table> --}}
                <div class="goto-page">
                  <div class="form-group">
                      <div class="inner-goto">
                        <span>Go to</span>
                      <input type="text" name="search_page_no" id="search_page_no" class="form-control">
                      </div>
                      <div class="inner-goto">
                        <span>Page</span> 
                      <button class="btn btn-go" onclick="redirect_to_page()">Go</button>
                      </div>
                  </div>
                </div>  
               </div>
            </div>
         </div>
      </div>
      <!-- /.row -->
   </div>
</div>
<!-- /#page-wrapper -->

@include('maker.layout.modals')

<script type="text/javascript"> 



/* Calculate commission for display purpose */
function calc_commission(){

    var ret_whole_price   = $("#ret_whole_price").val();
    var vendor_commission = $("#commission_percent").val();
    var total_price       = 0;

    total_price = (ret_whole_price - ( ret_whole_price * vendor_commission / 100 )).toFixed(2);
    if (!isNaN(total_price)) {
      $("#total_commission_price").val(total_price);
    }
}


var module_url_path  = "{{ $module_url_path or '' }}";  

$('#import_excel').click(function(){
      
    if($('#impert_excel_sheet').parsley().validate()==false)
    {  
       hideProcessingOverlay();
       return;
    }
    else
    { 
      showProcessingOverlay();
       $("#impert_excel_sheet").submit();
    } 

 });  

   function confirm_delete(ref,event)
  {
    confirm_action(ref,event,'Are you sure? Do you want to delete this product.');
  }  

  
 $(function(){

    $("input.checkItemAll").click(function(){
         
        if($(this). prop("checked") == true){
          $("input.checkboxInput").prop('checked',true);
        }
        else{
          $("input.checkboxInput").prop('checked',false);
        }

    });

});


</script>



<script type="text/javascript" src="{{url('/assets/js/module_js/edit_product.js')}}"></script>
<script type="text/javascript" src="{{url('/assets/js/module_js/add_product.js')}}"></script>


<script type="text/javascript">
  $(document).ready(function(){
      /*Remove already init TinyMCE*/
      setTimeout(()=>{
        tinymce.remove();

        initalizedTinyMCE();
      },200);
      /*------------------------*/
  });

  function initalizedTinyMCE(){
    tinymce.init({
     selector: '#product_description,#old_product_description',
     relative_urls: false,
     remove_script_host:false,
     convert_urls:false,
     plugins: [
       'link',
       'fullscreen',
       'contextmenu '
     ],
     toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link',
     content_css: [
       // '//www.tinymce.com/css/codepen.min.css'
     ]
   });
  }
</script>




@stop