$(document).ready(function()
{
 tinymce.init({
   selector: 'textarea',
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
});

/*OLD paid plugins for tinyMCE*/
/*$(document).ready(function()
{
 tinymce.init({
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
 });
});*/