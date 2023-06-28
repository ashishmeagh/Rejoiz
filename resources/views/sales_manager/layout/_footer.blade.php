     <!-- /.container-fluid -->
     <?php
        $site_data = get_site_settings();
        $site_name = $site_data['site_name'];
     ?>

    <footer class="footer text-center"> <i class="fa fa-copyright"></i> <a href="{{url('/')}}" target="_blank">{{$site_name}}</a> {{date('Y')}}. All Rights Reserved. </footer>
            
<!-- jQuery -->

    {{-- <script src="{{url('/')}}/assets/plugins/bower_components/jquery/dist/jquery.min.js"></script> --}}
    <!-- Bootstrap Core JavaScript -->
    <script src="{{url('/')}}/assets/bootstrap/dist/js/tether.min.js"></script>
    <script src="{{url('/')}}/assets/bootstrap/dist/js/bootstrap.min.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-extension/js/bootstrap-extension.min.js"></script>
    <script src="{{url('/')}}/assets/js/text_less_more.min.js"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="{{url('/')}}/assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js"></script>
    <!--slimscroll JavaScript -->
    <script src="{{url('/')}}/assets/js/jquery.slimscroll.js"></script>
    <!--Wave Effects -->
    <script src="{{url('/')}}/assets/js/waves.js"></script>
    <!--Counter js -->
    <script src="{{url('/')}}/assets/plugins/bower_components/waypoints/lib/jquery.waypoints.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/counterup/jquery.counterup.min.js"></script>
    <!--Morris JavaScript -->
    <script src="{{url('/')}}/assets/plugins/bower_components/raphael/raphael-min.js"></script>


    <script type="text/javascript" src="{{url('/')}}/assets/loader/loadingoverlay.min.js"></script>
    <script type="text/javascript" src="{{url('/')}}/assets/loader/loader.js"></script>

    <!-- Custom Theme JavaScript -->
    <script src="{{url('/')}}/assets/js/custom.min.js"></script>
    <script src="{{url('/')}}/assets/js/dashboard1.js"></script>
    <!-- Sparkline chart JavaScript -->
    <script src="{{url('/')}}/assets/plugins/bower_components/jquery-sparkline/jquery.sparkline.min.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/jquery-sparkline/jquery.charts-sparkline.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/toast-master/js/jquery.toast.js"></script>

    <!-- data table js-->
    <script type="text/javascript" src="{{url('/')}}/assets/data-tables/latest/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="{{url('/')}}/assets/data-tables/latest/dataTables.bootstrap.min.js"></script>
    
    <script type="text/javascript" src="{{ url('/') }}/assets/js/jquery-validation/dist/jquery.validate.min.js"></script>
    <script type="text/javascript" src="{{ url('/') }}/assets/js/jquery-validation/dist/additional-methods.js"></script>
    <script src="{{ url('/') }}/assets/js/validation.js"></script>

    <script type="text/javascript" src="{{url('/')}}/assets/js/sweetalert_msg.js"></script>
    <script type="text/javascript" src="{{url('/')}}/assets/sweetalert/sweetalert.js"></script>

    <script type="text/javascript" src="{{url('/')}}/assets/js/Parsley/dist/parsley.min.js"></script>
    

    <script src="{{url('/')}}/assets/plugins/bower_components/dropify/dist/js/dropify.min.js"></script>

    <!--Switechery js-->
    <script src="{{url('/')}}/assets/plugins/bower_components/switchery/dist/switchery.min.js"></script>


    <!-- Clock Plugin JavaScript -->
    <script src="{{url('/')}}/assets/plugins/bower_components/clockpicker/dist/jquery-clockpicker.min.js"></script>
    <!-- Color Picker Plugin JavaScript -->
    <script src="{{url('/')}}/assets/plugins/bower_components/jquery-asColorPicker-master/libs/jquery-asColor.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/jquery-asColorPicker-master/libs/jquery-asGradient.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/jquery-asColorPicker-master/dist/jquery-asColorPicker.min.js"></script>

<script src="{{url('/')}}/assets/plugins/bower_components/custom-select/custom-select.min.js" type="text/javascript"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-select/bootstrap-select.min.js" type="text/javascript"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
    <script src="{{url('/')}}/assets/plugins/bower_components/bootstrap-touchspin/dist/jquery.bootstrap-touchspin.min.js" type="text/javascript"></script>
    <script type="text/javascript" src="{{url('/')}}/assets/plugins/bower_components/multiselect/js/jquery.multi-select.js"></script>

<script type="text/javascript">
    $(document).ready(function() {
     // For select 2
        $(".select2").select2();
        $('.selectpicker').selectpicker();
        //Bootstrap-TouchSpin
      
        var vspinTrue = $(".vertical-spin").TouchSpin({
            verticalbuttons: true
        });
        if (vspinTrue) {
            $('.vertical-spin').prev('.bootstrap-touchspin-prefix').remove();
        }
        $("input[name='tch1']").TouchSpin({
            min: 0,
            max: 100,
            step: 0.1,
            decimals: 2,
            boostat: 5,
            maxboostedstep: 10,
            postfix: '%'
        });
        $("input[name='tch2']").TouchSpin({
            min: -1000000000,
            max: 1000000000,
            stepinterval: 50,
            maxboostedstep: 10000000,
            prefix: '$'
        });
        $("input[name='tch3']").TouchSpin();
        $("input[name='tch3_22']").TouchSpin({
            initval: 40
        });
        $("input[name='tch5']").TouchSpin({
            prefix: "pre",
            postfix: "post"
        });
        
        // For multiselect
        $('#pre-selected-options').multiSelect();
        $('#optgroup').multiSelect({
            selectableOptgroup: true
        });
        $('#public-methods').multiSelect();
        $('#select-all').click(function() {
            $('#public-methods').multiSelect('select_all');
            return false;
        });
        $('#deselect-all').click(function() {
            $('#public-methods').multiSelect('deselect_all');
            return false;
        });
        $('#refresh').on('click', function() {
            $('#public-methods').multiSelect('refresh');
            return false;
        });
        $('#add-option').on('click', function() {
            $('#public-methods').multiSelect('addOption', {
                value: 42,
                text: 'test 42',
                index: 0
            });
            return false;
        });

    });
</script>
    
    <!-- Tinymce editor js-->
    <script src="https://cdn.tiny.cloud/1/{{$site_setting_arr['tinymce_api_key'] or ''}}/tinymce/5/tinymce.min.js" referrerpolicy="origin"></script>
    <script type="text/javascript" src="{{url('/assets/js/tinyMCE.js')}}"></script>
    <script>
    $(document).ready(function() {
        $( ".datepicker" ).datepicker({
            autoclose: true,
            todayHighlight: true,
            format:'mm-dd-yyyy'
        });

        // Basic
        $('.dropify').dropify();

        

        // Translated
        $('.dropify-fr').dropify({
            messages: {
                default: 'Glissez-déposez un fichier ici ou cliquez',
                replace: 'Glissez-déposez un fichier ou cliquez pour remplacer',
                remove: 'Supprimer',
                error: 'Désolé, le fichier trop volumineux'
            }
        });
        // Used events
        var drEvent = $('#input-file-events').dropify();
        drEvent.on('dropify.beforeClear', function(event, element) {
            return confirm("Do you really want to delete \"" + element.file.name + "\" ?");
        });
        drEvent.on('dropify.afterClear', function(event, element) {
            alert('File deleted');
        });
        drEvent.on('dropify.errors', function(event, element) {
            console.log('Has Errors');
        });
        var drDestroy = $('#input-file-to-destroy').dropify();
        drDestroy = drDestroy.data('dropify')
        $('#toggleDropify').on('click', function(e) {
            e.preventDefault();
            if (drDestroy.isDropified()) {
                drDestroy.destroy();
            } else {
                drDestroy.init();
            }
        });

    });
    </script>
    
    <script type="text/javascript">
          $(document).ready(function(){
            update_user_active_time();

            $('#validation-form').validate();
            $('#validation-form').parsley();
            
          });

        var elems = Array.prototype.slice.call(document.querySelectorAll('.js-switch'));
        $('.js-switch').each(function() {
         new Switchery($(this)[0], $(this).data());
        });

        $("input.toggleSwitch").change(function(){
        statusChange($(this));
        });
        

       //make slug from string
      var slug = function(str) {
        var $slug = '';
        var trimmed = $.trim(str);
        $slug = trimmed.replace(/[^a-z0-9-]/gi, '-').
        replace(/-+/g, '-').
        replace(/^-|-$/g, '');
        return $slug.toLowerCase();
      }

      function topHeadsearch() {
          var input, filter, ul, li, a, i;
          input = document.getElementById("mySearch");
          filter = input.value.toUpperCase();
          ul = document.getElementById("menuList");
          li = $(".menuList");
          for (i = 0; i < li.length; i++) {
            a = li[i].getElementsByTagName("a")[0];

            if (a.innerHTML.toUpperCase().indexOf(filter) > -1) {
              li[i].style.display = "";
            } else {
              li[i].style.display = "none";
            }
          }
        }

      

      function finalize_lead(ref,evt,msg)
      {
        if($('#validation-form').parsley().validate() == false)
        {
          return false;
        }
        var msg = msg || false;
      
        evt.preventDefault();  
        swal({

              title:"Need Confirmation",
              text: msg,
              type: "warning",
              showCancelButton: true,
              confirmButtonColor: "#DD6B55",
              confirmButtonText: "OK",
              closeOnConfirm: false
            },
            function(isConfirm)
            {
              if(isConfirm)
              {
                showProcessingOverlay();
                swal("Success", "Your order saved successfully.", "success");
                window.location = $(ref).attr('href');
              }
            });
      }

      function min_warning()
      {
        swal({
                    title: "Note",
                    text: "Please make sure Cart Total for following brands, satisfies min. amount<br><b>{{isset($maker_details_arr['maker_details']['brand_name'])?$maker_details_arr['maker_details']['brand_name']:''}}</b> Minimum Required:${{isset($maker_details_arr['shop_settings']['first_order_minimum'])?$maker_details_arr['shop_settings']['first_order_minimum']:''}} Current Subtotal:${{isset($bucket_items_arr['total_wholesale_price'])?$bucket_items_arr['total_wholesale_price']:''}}",
                    type: "warning",
                    confirmButtonText: "OK",
                    closeOnConfirm: false
                });
        return;
      }

        function toggleSelect()
        {
             $("input.checkItem").click(function()
            { 
                var checked_checkbox_length = $('input:checked[name="checked_record[]"]').map(function (){ return $(this).val(); } ).get();

                var allBoxes                = $("input.checkItem").length;
                var checkedBoxes            = $('input:checked[name="checked_record[]"]').length;

                // if(checked_checkbox_length.length < 10){
                if(allBoxes != checkedBoxes){
                     
                    $("input.checkItemAll").prop('checked',false);
                }
                else
                {
                   $("input.checkItemAll").prop('checked',true);
                }
            });    
        }

        function update_user_active_time()
        {

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

    </script>

     <!--Style Switcher -->
    <script src="{{url('/')}}/assets/plugins/bower_components/styleswitcher/jQuery.style.switcher.js"></script>    
    <!--Parsley Js-->
    <script type="text/javascript" src="{{url('/')}}/assets/js/Parsley/dist/parsley.min.js"></script>

    <!--Common JS -->
    <!-- @if(Sentinel::check()==true)
        <script type="text/javascript" src="{{url('/')}}/assets/js/after_login_common.js"></script>
    @endif -->

    <!-- Sticky Header Script Start -->
<script>
    $(window).scroll(function () {
    if ($(window).scrollTop() >=5) {
        $('nav').addClass('fixed-header');
        $('nav div').addClass('visible-title');
    } else {
        $('nav').removeClass('fixed-header');
        $('nav div').removeClass('visible-title');
    }
});
</script>
 <!-- Sticky Header Script End -->  
</body>

</html>