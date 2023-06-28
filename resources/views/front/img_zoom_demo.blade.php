<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>JavaScript increasing and decreasing image size</title>
<style>
    button{
        padding: 3px 6px;
    }
    button img{
        vertical-align: middle;
    }
</style>
    <p>
        <button type="button" onclick="zoomin()"><img src="/examples/images/zoom-in.png"> Zoom In</button>
        <button type="button" onclick="zoomout()"><img src="/examples/images/zoom-out.png"> Zoom Out</button>
    </p>

 @if(isset($product_arr['product_details']) && count($product_arr['product_details'])>0)
                  @foreach($product_arr['product_details'] as $prod_key =>$product_details) {{--              --}}           
                  @php
                  if(isset($product_details['image']) && $product_details['image']!='' && file_exists(base_path().'/storage/app/'.$product_details['image']))
                  {
                  $product_img2 = url('/storage/app/'.$product_details['image']);
                  if(isset($product_details['image_thumb']) && $product_details['image_thumb']!='' && file_exists(base_path().'/storage/app/product_image/product_img_thumb/'.$product_details['image_thumb']))
                  {
                  $product_thumb_img = url('/storage/app/product_image/product_img_thumb/'.$product_details['image_thumb']);
                  }
                  else
                  { 
                  $product_thumb_img = $product_img2;
                  }
                  }
                  else
                  {   
                  $product_img2 = url('/assets/images/no-product-img-found.jpg');
                  $product_thumb_img = url('/assets/images/no-product-img-found.jpg');
                  }
                  @endphp    
                  @if($prod_key==0)
                  <div class="img-thumbnail">
                     <img class="xzoom" id="sky" src="{{$product_thumb_img or ''}}" xoriginal="{{$product_img2 or ''}}"  />
                  </div>
                  @endif
                  @endforeach
                  @endif
<script>
    function zoomin(){
        var myImg = document.getElementById("sky");
        var currWidth = myImg.clientWidth;
        if(currWidth == 500){
            alert("Maximum zoom-in level reached.");
        } else{
                myImg.style.width = (currWidth + 50) + "px";
              } 
    }
    function zoomout(){
        var myImg = document.getElementById("sky");
        var currWidth = myImg.clientWidth;
        if(currWidth == 50){
            alert("Maximum zoom-out level reached.");
        } else{
            myImg.style.width = (currWidth - 50) + "px";
        }
    }

    $("#sky").hover(function()
    {
        
         var myImg = document.getElementById("sky");
        var currWidth = myImg.clientWidth;
        if(currWidth == 500){
            alert("Maximum zoom-in level reached.");
        } else{
                myImg.style.width = (currWidth + 50) + "px";
              } 
    });




</script>

</head>
<body>

    <img src="{{url('/')}}/assets/images/test_image.jpg" id="sky" width="250" alt="Cloudy Sky">
</body>
</html>                            