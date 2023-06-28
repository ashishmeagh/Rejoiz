<!-- Modal -->
<div class="modal fade" id="product_description_modal" tabindex="-1" role="dialog" aria-labelledby="VendorPaymentModalCenterTitle" aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered product_description_modal" role="document">
    <div class="modal-content">
      <div class="modal-header my-header">
        <h3 class="modal-title" id="VendorPaymentModalLongTitle">Description</h3>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body product_description_modal_body">
            <span id="showmessage"></span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
       {{--  <button type="button" class="btn btn-primary" onclick="payVendor()" >Pay</button> --}}
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
   $(document).on('click','.readmorebtn',function(){
   
     var reason = $(this).attr('message');
     if(reason)
     {
        $("#product_description_modal").modal('show');
        $("#showmessage").html(reason);      
     }

  });
</script>
<style type="text/css">
   #showmessage p {
    font-weight: normal !important;
   }
   .product_description_modal {
      max-width: 700px !important
    }
    .product_description_modal_body{
      max-height: 500px;
      overflow: auto;
    }
    .my-header {
      display: inherit !important
    }
</style>